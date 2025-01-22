<?php
include '../connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contentType = $_SERVER["CONTENT_TYPE"] ?? '';
    $data = [];

    if (stripos($contentType, 'application/json') !== false) {
        $data = json_decode(file_get_contents('php://input'), true);
    } else {
        $data = $_POST;
    }

    $token = $data['token'] ?? null;

    if (!$token) {
        echo json_encode(['status' => 'error', 'message' => 'Token is required']);
        exit;
    }

    // Define the fields that can be updated
    $allowedFields = ['student_name', 'student_number', 'balance', 'is_active'];
    $updateFields = [];

    // Gather fields that are present in the request data
    foreach ($allowedFields as $field) {
        if (isset($data[$field])) {
            $updateFields[$field] = $data[$field];
        }
    }

    // If no fields are provided for updating, return an error
    if (empty($updateFields)) {
        echo json_encode(['status' => 'error', 'message' => 'No valid fields to update']);
        exit;
    }

    // Build the update query dynamically
    $setClause = implode(", ", array_map(function ($field) {
        return "$field = ?";
    }, array_keys($updateFields)));

    $updateStmt = $pdo->prepare("UPDATE student SET $setClause WHERE token = ?");
    $executeParams = array_values($updateFields);
    $executeParams[] = $token;

    // Execute the update and provide feedback
    if ($updateStmt->execute($executeParams)) {
        echo json_encode(['status' => 'success', 'message' => 'Student information updated successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update student information']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
