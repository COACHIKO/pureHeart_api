<?php
header('Content-Type: application/json');
include '../connect.php';
include '../auth/generateToken.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check content type to handle JSON data
    $contentType = $_SERVER["CONTENT_TYPE"] ?? '';
    if (stripos($contentType, 'application/json') !== false) {
        // Read JSON data from the request body
        $data = json_decode(file_get_contents('php://input'), true);
        $studentNumber = $data['student_number'] ?? null;
        $studentName = $data['student_name'] ?? null;
        $studentStage = $data['student_stage'] ?? null;
        $rank = $data['rank'] ?? 0;  // Default to 0 if no rank is provided
    } else {
        // Handle form data if not in JSON format
        $studentNumber = $_POST['student_number'] ?? null;
        $studentName = $_POST['student_name'] ?? null;
        $studentStage = $_POST['student_stage'] ?? null;
        $rank = $_POST['rank'] ?? 0;  // Default to 0 if no rank is provided
    }

    // Validate required fields
    if (!$studentNumber || !$studentName || !$studentStage) {
        echo json_encode(['status' => 'error', 'message' => 'Student number, name, and stage are required']);
        exit;
    }

    // Generate a unique token for the student
    $token = generateUniqueToken($pdo);

    // Set the active status to 1 (indicating admin-level creation)
    $isActive = 1;

    try {
        // Prepare and execute the insert query
        $insertStmt = $pdo->prepare("INSERT INTO student (student_name, student_number, student_stage, rank, token, is_active) 
                                     VALUES (?, ?, ?, ?, ?, ?)");
        $insertSuccess = $insertStmt->execute([$studentName, $studentNumber, $studentStage, $rank, $token, $isActive]);

        if ($insertSuccess) {
            // Get the newly inserted student ID
            $newStudentId = $pdo->lastInsertId();

            // Return the newly created student's data
            echo json_encode([
                'status' => 'success',
                'message' => 'Student created successfully with admin privileges',
                'student_id' => $newStudentId,
                'student_name' => $studentName,
                'student_number' => $studentNumber,
                'rank' => $rank,
                'token' => $token,
                'student_stage' => $studentStage,
                'is_active' => $isActive
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to create student']);
        }
    } catch (PDOException $e) {
        // Catch any database-related errors and return a message
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    // Handle invalid request method
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
