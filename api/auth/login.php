<?php
header('Content-Type: application/json');
include '../connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contentType = $_SERVER["CONTENT_TYPE"] ?? '';
    if (stripos($contentType, 'application/json') !== false) {
        $data = json_decode(file_get_contents('php://input'), true);
        $token = $data['token'] ?? null;
    } else {
        $token = $_POST['token'] ?? null;
    }

    if (!$token) {
        echo json_encode(['status' => 'error', 'message' => 'Token is required']);
        exit;
    }

    // Check if the token matches any teacher
    $teacherStmt = $pdo->prepare("SELECT * FROM teacher WHERE token = ?");
    $teacherStmt->execute([$token]);
    $teacher = $teacherStmt->fetch();

    if ($teacher) {
         echo json_encode([
            'status' => 'success',
            'user_type' => 'teacher',
            'data' => [
                'teacher_id' => $teacher['id'],
                'teacher_name' => $teacher['teacher_name'],
                'teacher_number' => $teacher['teacher_number'],
                'teacher_subject' => $teacher['teacher_subject'],
                'followers' => $teacher['followers'],
                'rank' => $teacher['rank'],
                'price' => $teacher['price'],
                'is_active' => $teacher['is_active']
            ]
        ]);
        exit;
    }

    // Check if the token matches any student
    $studentStmt = $pdo->prepare("SELECT * FROM student WHERE token = ?");
    $studentStmt->execute([$token]);
    $student = $studentStmt->fetch();

    if ($student) {
        // Fetch student ratings with associated teacher details
        $ratingStmt = $pdo->prepare("
            SELECT sr.subject_name, sr.rating, sr.rank,
                   t.teacher_name, t.teacher_number, t.token AS teacher_token
            FROM student_rating sr
            JOIN teacher t ON sr.teacher_id = t.id
            WHERE sr.student_id = ?
        ");
        $ratingStmt->execute([$student['id']]);
        $ratings = $ratingStmt->fetchAll(PDO::FETCH_ASSOC);

        // Student found with the token
        echo json_encode([
            'status' => 'success',
            'user_type' => 'student',
            'data' => [
                'student_id' => $student['id'],
                'student_name' => $student['student_name'],
                'student_number' => $student['student_number'],
                'student_stage' => $student['student_stage'],
                'is_active' => $student['is_active'],
                'ratings' => $ratings // Including ratings with teacher details
            ]
        ]);
    } else {
        // No user found with the token
        echo json_encode(['status' => 'error', 'message' => 'Invalid token or user not found']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
