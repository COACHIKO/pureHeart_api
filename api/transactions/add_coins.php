<?php
header('Content-Type: application/json');
include '../connect.php';

try {
    // Check if the request method is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method', 400);
    }

    // Get JSON input data
    $data = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON input', 400);
    }

    // Get required fields
    $token = $data['token'] ?? null;
    $amount = $data['amount'] ?? null;
    $reverse_code = $data['reverse_code'] ?? null;

    // Validate required fields
    if (empty($token) || empty($amount) || empty($reverse_code)) {
        echo json_encode(['status' => 'error', 'message' => 'token, amount, and reverse_code are required']);
        http_response_code(422);
        exit;
    }

    // Fetch student data based on token
    $studentStmt = $pdo->prepare("SELECT * FROM student WHERE token = ?");
    $studentStmt->execute([$token]);
    $student = $studentStmt->fetch();

    // Check if student exists
    if (!$student) {
        throw new Exception('Student not found', 404);
    }

    // Ensure the student has the required fields (student_name and student_number)
    $studentName = isset($student['student_name']) ? $student['student_name'] : null;
    $studentNumber = isset($student['student_number']) ? $student['student_number'] : null;

    // If name or number is missing, throw an error
    if (empty($studentName) || empty($studentNumber)) {
        throw new Exception('Student does not have a valid name or number', 400);
    }

    // Insert the transaction record
    $stmt = $pdo->prepare("INSERT INTO app_transaction (name, number, amount, token,reverse_code, date_time, status) 
                           VALUES (?, ?,?,?, ?, NOW(), 0)");
    $stmt->execute([$studentName, $studentNumber, $amount,$token, $reverse_code]);

    // Return success response
    echo json_encode([
        'status' => 'success',
        'message' => 'Transaction added successfully',
        'token' => $token,
        'amount' => $amount,
        'reverse_code' => $reverse_code,
        'student_name' => $studentName,
        'student_number' => $studentNumber
    ]);

} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
