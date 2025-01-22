<?php
header('Content-Type: application/json');
include '../connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Parse JSON input
    $data = json_decode(file_get_contents('php://input'), true);
    $studentToken = $data['student_token'] ?? null;
    $teacherToken = $data['teacher_token'] ?? null;
    $amount = $data['amount'] ?? null;

    // Validate required fields
    if (!$studentToken || !$teacherToken || !$amount) {
        echo json_encode(['status' => 'error', 'message' => 'student_token, teacher_token, and amount are required']);
        exit;
    }

    try {
        // Retrieve student information based on token
        $studentStmt = $pdo->prepare("SELECT * FROM student WHERE token = ?");
        $studentStmt->execute([$studentToken]);
        $student = $studentStmt->fetch();

        // Check if student exists and has enough balance
        if (!$student) {
            echo json_encode(['status' => 'error', 'message' => 'Student not found']);
            exit;
        } elseif ($student['balance'] < $amount) {
            echo json_encode(['status' => 'error', 'message' => 'Insufficient balance']);
            exit;
        }

        // Retrieve teacher information based on token
        $teacherStmt = $pdo->prepare("SELECT * FROM teacher WHERE token = ?");
        $teacherStmt->execute([$teacherToken]);
        $teacher = $teacherStmt->fetch();

        // Check if teacher exists
        if (!$teacher) {
            echo json_encode(['status' => 'error', 'message' => 'Teacher not found']);
            exit;
        }

        // Deduct amount from student's balance
        $updateBalanceStmt = $pdo->prepare("UPDATE student SET balance = balance - ? WHERE id = ?");
        $updateBalanceStmt->execute([$amount, $student['id']]);

        // Insert transaction record
        $transactionStmt = $pdo->prepare("INSERT INTO transaction (sender_name, sender_number, reciver_name, recevier_number, amount, status) 
                                          VALUES (?, ?, ?, ?, ?, ?)");
        $transactionStmt->execute([
            $student['student_name'], $student['student_number'],
            $teacher['teacher_name'], $teacher['teacher_number'],
            $amount, 0 // Status is set to 0 (pending)
        ]);

        // Confirm transaction creation
        $newTransactionId = $pdo->lastInsertId();

        echo json_encode([
            'status' => 'success',
            'message' => 'Payment request created successfully',
            'transaction_id' => $newTransactionId,
            'amount' => $amount,
            'student_balance' => $student['balance'] - $amount, // Updated balance
            'transaction_status' => 0
        ]);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
