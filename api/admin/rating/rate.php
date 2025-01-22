<?php
header('Content-Type: application/json');
include '../connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contentType = $_SERVER["CONTENT_TYPE"] ?? '';
    if (stripos($contentType, 'application/json') !== false) {
        $data = json_decode(file_get_contents('php://input'), true);
        $raterId = $data['rater_id'] ?? null;
        $raterType = $data['rater_type'] ?? null;
        $rateeId = $data['ratee_id'] ?? null;
        $rateeType = $data['ratee_type'] ?? null;
        $rating = $data['rating'] ?? null;
        $subjectName = $data['subject_name'] ?? null;
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Request must be JSON']);
        exit;
    }

    // Validate input
    if (!$raterId || !$raterType || !$rateeId || !$rateeType || !$rating || $rating < 1 || $rating > 10) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
        exit;
    }

    // Check if rater and ratee exist in the appropriate tables
    $isRaterStudent = $raterType === 'student';
    $raterCheckStmt = $pdo->prepare("SELECT * FROM " . ($isRaterStudent ? 'student' : 'teacher') . " WHERE id = ?");
    $raterCheckStmt->execute([$raterId]);
    if (!$raterCheckStmt->fetch()) {
        echo json_encode(['status' => 'error', 'message' => 'Rater not found']);
        exit;
    }

    $isRateeStudent = $rateeType === 'student';
    $rateeCheckStmt = $pdo->prepare("SELECT * FROM " . ($isRateeStudent ? 'student' : 'teacher') . " WHERE id = ?");
    $rateeCheckStmt->execute([$rateeId]);
    if (!$rateeCheckStmt->fetch()) {
        echo json_encode(['status' => 'error', 'message' => 'Ratee not found']);
        exit;
    }

    // Insert the rating
    $insertStmt = $pdo->prepare("INSERT INTO rating (rater_id, rater_type, ratee_id, ratee_type, subject_name, rating) VALUES (?, ?, ?, ?, ?, ?)");
    $insertSuccess = $insertStmt->execute([$raterId, $raterType, $rateeId, $rateeType, $subjectName, $rating]);

    if ($insertSuccess) {
        echo json_encode(['status' => 'success', 'message' => 'Rating added successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to add rating']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
