<?php
header('Content-Type: application/json');
include '../connect.php';

try {
    // Validate request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method', 400);
    }

    // Parse JSON data from input
    $data = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON input', 400);
    }

    // Retrieve input values
    $studentToken = $data['student_token'] ?? null;
    $studentPrice = $data['student_price'] ?? null;
    $subjectName = $data['subject_name'] ?? null;
    $days = $data['days'] ?? '0000000';

    // Validate required fields
    if (empty($studentToken) || empty($studentPrice) || empty($days)) {
        echo json_encode(['status' => 'error', 'message' => 'student_token, student_price, subject_name, and days are required']);
        http_response_code(422); // Send 422 status code explicitly
        exit;
    }

    // Validate `student_price`
    if (!is_numeric($studentPrice) || (int)$studentPrice <= 0) {
        throw new Exception('Invalid student_price. It must be a positive number', 422);
    }

    // Validate `days` format
    if (strlen($days) !== 7 || !preg_match('/^[01]{7}$/', $days)) {
        throw new Exception('Invalid days format. Must be a 7-character string with only 0s and 1s', 422);
    }

    // Search for the student using the token
    $studentStmt = $pdo->prepare("SELECT * FROM student WHERE token = ?");
    $studentStmt->execute([$studentToken]);
    $student = $studentStmt->fetch();

    // Check if student exists
    if (!$student) {
        throw new Exception('Student not found', 404);
    }

    // Get student name
    $studentName = $student['student_name'];

    // Insert the student ad into the table
    $adStmt = $pdo->prepare("INSERT INTO student_ad (student_name, student_price, subject_name, days) 
                             VALUES (?, ?, ?, ?)");
    $adStmt->execute([ 
        $studentName, 
        (int)$studentPrice, 
        $subjectName, 
        $days 
    ]);

    // Get the new ad ID
    $newAdId = $pdo->lastInsertId();

    // Respond with success message
    echo json_encode([
        'status' => 'success',
        'message' => 'Student Advertisement created successfully',
        'ad_id' => $newAdId,
        'student_name' => $studentName,
        'student_price' => (int)$studentPrice,
        'subject_name' => $subjectName,
        'days' => $days
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    // General error handling
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
} catch (PDOException $e) {
    // Database error handling
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
