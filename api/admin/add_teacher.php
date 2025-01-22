<?php
include '../connect.php';
include '../auth/generateToken.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contentType = $_SERVER["CONTENT_TYPE"] ?? '';
    if (stripos($contentType, 'application/json') !== false) {
        $data = json_decode(file_get_contents('php://input'), true);
        $teacherNumber = $data['teacher_number'] ?? null;
        $teacherName = $data['teacher_name'] ?? null;
        $teacherSubject = $data['teacher_subject'] ?? null;
        $followers = $data['followers'] ?? 0;
        $rank = $data['rank'] ?? 0;
        $price = $data['price'] ?? 0;        $balance = $data['balance'] ?? 0;

    } else {
        $teacherNumber = $_POST['teacher_number'] ?? null;
        $teacherName = $_POST['teacher_name'] ?? null;
        $teacherSubject = $_POST['teacher_subject'] ?? null;
        $followers = $_POST['followers'] ?? 0;
        $rank = $_POST['rank'] ?? 0;
        $price = $_POST['price'] ?? 0;
        $balance = $_POST['balance'] ?? 0;


    }

    // Check if essential data is provided
    if ($teacherNumber === null || $teacherName === null || $teacherSubject === null) {
        echo json_encode(['status' => 'error', 'message' => 'teacher_name, teacher_number, teacher_subject, followers, rank, price and balance are required']);
        exit;
    }

    try {
        // Generate a unique token for the teacher
        $token = generateUniqueToken($pdo);
    
        // Set the active status to 1 (admin-level creation)
        $isActive = 1;
    
        // Insert new teacher record
        $insertStmt = $pdo->prepare("INSERT INTO teacher (teacher_name, teacher_number, teacher_subject, token, followers, rank, price, balance, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $insertSuccess = $insertStmt->execute([$teacherName, $teacherNumber, $teacherSubject, $token, $followers, $rank, $price, $balance, $isActive]);
    
        if ($insertSuccess) {
            // Get the newly inserted teacher ID
            $newTeacherId = $pdo->lastInsertId();
    
            // Return the newly created teacher's data
            echo json_encode([
                'status' => 'success',
                'message' => 'Teacher created successfully with admin privileges',
                'teacher_id' => $newTeacherId,
                'teacher_name' => $teacherName,
                'teacher_number' => $teacherNumber,
                'token' => $token,
                'teacher_subject' => $teacherSubject,
                'followers' => $followers,
                'rank' => $rank,
                'price' => $price,
                'balance' => $balance,
                'is_active' => $isActive
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to create teacher']);
        }
    } catch (PDOException $e) {
        // Check if the error code is for a duplicate entry
        if ($e->getCode() == 23000) {
            echo json_encode(['status' => 'error', 'message' => 'This teacher number is already signed up']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'An error occurred: ' . $e->getMessage()]);
        }
    }}