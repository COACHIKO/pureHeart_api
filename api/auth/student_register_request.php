<?php
header('Content-Type: application/json');
include '../connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contentType = $_SERVER["CONTENT_TYPE"] ?? '';
    if (stripos($contentType, 'application/json') !== false) {
        $data = json_decode(file_get_contents('php://input'), true);
        $studentNumber = $data['student_number'] ?? null;
        $studentName = $data['student_name'] ?? null;
        $studentStage = $data['student_stage'] ?? null;
    } else {
        $studentNumber = $_POST['student_number'] ?? null;
        $studentName = $_POST['student_name'] ?? null;
        $studentStage = $_POST['student_stage'] ?? null;
    }

    if (!$studentNumber || !$studentName || !$studentStage) {
        echo json_encode(['status' => 'error', 'message' => 'Student number, name, and stage are required']);
        exit;
    }

     $stmt = $pdo->prepare("SELECT * FROM student WHERE student_number = ?");
    $stmt->execute([$studentNumber]);
    $student = $stmt->fetch();

    if ($student) {
         if ($student['is_active'] == 0) {
             echo json_encode([
                'status' => 'waiting to be verified',
                'message' => 'Please wait for your account to be verified'
            ]);
        } else {
             echo json_encode([
                'status' => 'user already exists',
                'message' => 'Please login using barcode'
            ]);
        }
    } else {
        $token = bin2hex(random_bytes(20));
        $insertStmt = $pdo->prepare("INSERT INTO student (student_name, student_number, student_stage, token, is_active) VALUES (?, ?, ?, ?, 0)");
        $insertSuccess = $insertStmt->execute([$studentName, $studentNumber, $studentStage, $token]);

        if ($insertSuccess) {
            echo json_encode([
                'status' => 'Account created',
                'message' => 'Please wait for your account to be verified'
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to insert new student']);
        }
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
