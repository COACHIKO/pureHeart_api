<?php

include '../connect.php';

header('Content-Type: application/json');
 header("Access-Control-Allow-Headers: Content-Type, Authorization");

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
        exit();
    }

    $headers = getallheaders();
    $token = isset($headers['Authorization']) ? trim($headers['Authorization']) : null;

    $subjectsQuery = "SELECT id, name, icon FROM subjects";
    $subjectsStmt = $pdo->prepare($subjectsQuery);

    if ($token) {
        $teacherQuery = "SELECT teacher_subject FROM teacher WHERE token = :token AND is_active = 1";
        $teacherStmt = $pdo->prepare($teacherQuery);
        $teacherStmt->bindParam(':token', $token, PDO::PARAM_STR);
        $teacherStmt->execute();
        $teacher = $teacherStmt->fetch(PDO::FETCH_ASSOC);

        if ($teacher) {
            $subjectIds = explode(',', $teacher['teacher_subject']);

            // Handle empty teacher_subject
            if (empty($subjectIds)) {
                http_response_code(200);
                echo json_encode([
                    'status' => 'success',
                    'message' => 'No subjects found.',
                    'subjects' => []
                ]);
                exit();
            }

            // Use placeholders for the IN query
            $placeholders = implode(',', array_fill(0, count($subjectIds), '?'));
            $subjectsQuery = "SELECT id, name, icon FROM subjects WHERE id IN ($placeholders)";
            $subjectsStmt = $pdo->prepare($subjectsQuery);
            $subjectsStmt->execute($subjectIds);
        } else {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Invalid token or teacher not active']);
            exit();
        }
    }

    $subjectsStmt->execute();
    $subjects = $subjectsStmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($subjects)) {
        http_response_code(200);
        echo json_encode([
            'status' => 'success',
            'message' => 'No subjects found.',
            'subjects' => []
        ]);
        exit();
    }

    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'subjects' => $subjects
    ]);

} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'A database error occurred.']);
} catch (Exception $e) {
    error_log("General Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'An error occurred.']);
}
