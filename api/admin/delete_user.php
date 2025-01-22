<?php
include '../connect.php';

// Set header for JSON response
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contentType = $_SERVER["CONTENT_TYPE"] ?? '';
    $data = [];

    // Check for JSON content
    if (stripos($contentType, 'application/json') !== false) {
        $data = json_decode(file_get_contents('php://input'), true);
    } else {
        // Handle form data or other content types
        $data = $_POST;
    }

    $token = $data['token'] ?? null;

    if (!$token) {
        echo json_encode(['status' => 'error', 'message' => 'Token is required']);
        exit;
    }

    try {
        // Check if the token belongs to a student
        $findStmt = $pdo->prepare("SELECT id FROM student WHERE token = ?");
        $findStmt->execute([$token]);
        $student = $findStmt->fetch();

        if ($student) {
            // Delete student record if found
            $deleteStmt = $pdo->prepare("DELETE FROM student WHERE id = ?");
            if ($deleteStmt->execute([$student['id']])) {
                echo json_encode(['status' => 'success', 'message' => 'Student record deleted successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to delete student record']);
            }
            exit;
        }

        // Check if the token belongs to a teacher
        $findStmt = $pdo->prepare("SELECT id FROM teacher WHERE token = ?");
        $findStmt->execute([$token]);
        $teacher = $findStmt->fetch();

        if ($teacher) {
            // Delete related student ratings first
            $deleteRatingsStmt = $pdo->prepare("DELETE FROM student_rating WHERE teacher_id = ?");
            $deleteRatingsStmt->execute([$teacher['id']]);

            // Delete teacher record if found
            $deleteStmt = $pdo->prepare("DELETE FROM teacher WHERE id = ?");
            if ($deleteStmt->execute([$teacher['id']])) {
                echo json_encode(['status' => 'success', 'message' => 'Teacher record and related ratings deleted successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to delete teacher record']);
            }
            exit;
        }

        // If no student or teacher found
        echo json_encode(['status' => 'error', 'message' => 'No record found with the provided token']);
        
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
