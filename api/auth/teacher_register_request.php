<?php
header('Content-Type: application/json');
include '../connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contentType = $_SERVER["CONTENT_TYPE"] ?? '';
    if (stripos($contentType, 'application/json') !== false) {
        $data = json_decode(file_get_contents('php://input'), true);
        $teacherNumber = $data['teacher_number'] ?? null; 
        $teacherName = $data['teacher_name'] ?? null; 
        $teacherSubject = $data['teacher_subject'] ?? null;
        $gender = $data['gender'] ?? null; // Add gender
    } else {
        $teacherNumber = $_POST['teacher_number'] ?? null;
        $teacherName = $_POST['teacher_name'] ?? null;
        $teacherSubject = $_POST['teacher_subject'] ?? null;
        $gender = $_POST['gender'] ?? null; // Add gender
    }

    if (!$teacherNumber || !$teacherName || !$teacherSubject || !isset($gender)) {
        echo json_encode(['status' => 'error', 'message' => 'Teacher number, name, subject, and gender are required']);
        exit;
    }

    // Ensure teacher_subject is formatted as a comma-separated list
    if (is_array($teacherSubject)) {
        $teacherSubject = implode(',', $teacherSubject);
    }

    $stmt = $pdo->prepare("SELECT * FROM teacher WHERE teacher_number = ?");
    $stmt->execute([$teacherNumber]);
    $teacher = $stmt->fetch();

    if ($teacher) {
        if ($teacher['is_active'] == 0) {
            echo json_encode([
                'status' => 'waiting to be verified',
                'message' => 'Please wait for your account to be verified',
            ]);
        } else {
            echo json_encode([
                'status' => 'user already exists',
                'message' => 'Please login using barcode',
            ]);
        }
    } else {
        $token = bin2hex(random_bytes(20));
        $insertStmt = $pdo->prepare("INSERT INTO teacher (teacher_name, teacher_number, teacher_subject, token, followers, price, is_active, gender) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $insertSuccess = $insertStmt->execute([$teacherName, $teacherNumber, $teacherSubject, $token, 0, 0, 0, $gender]);

        if ($insertSuccess) {
            $newTeacherId = $pdo->lastInsertId();
            echo json_encode([
                'status' => 'Account created',
                'message' => 'Please wait for your account to be verified',
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to insert new teacher']);
        }
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
