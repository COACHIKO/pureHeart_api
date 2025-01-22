<?php
include '../connect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        // Retrieve the 'is_active' filter from the query parameters and sanitize it
        $isActiveFilter = isset($_GET['is_active']) ? intval($_GET['is_active']) : null;

        // Base queries
        $studentQuery = "SELECT id, student_name, student_number,student_image, student_stage, token, created_at, is_active FROM student";
        $teacherQuery = "SELECT id, teacher_name, teacher_number,teacher_image, teacher_subject, token, followers, rank, price, created_at,balance, is_active FROM teacher";

        // Add filter conditions if 'is_active' is set
        if ($isActiveFilter === 1 || $isActiveFilter === 0) {
            $studentQuery .= " WHERE is_active = :is_active";
            $teacherQuery .= " WHERE is_active = :is_active";
        }

        // Prepare and execute the student query
        $studentsStmt = $pdo->prepare($studentQuery);
        if ($isActiveFilter === 1 || $isActiveFilter === 0) {
            $studentsStmt->bindParam(':is_active', $isActiveFilter, PDO::PARAM_INT);
        }
        $studentsStmt->execute();
        $students = $studentsStmt->fetchAll(PDO::FETCH_ASSOC);

        // Prepare and execute the teacher query
        $teachersStmt = $pdo->prepare($teacherQuery);
        if ($isActiveFilter === 1 || $isActiveFilter === 0) {
            $teachersStmt->bindParam(':is_active', $isActiveFilter, PDO::PARAM_INT);
        }
        $teachersStmt->execute();
        $teachers = $teachersStmt->fetchAll(PDO::FETCH_ASSOC);

        // Return the combined response as JSON
        echo json_encode([
            'status' => 'success',
            'students' => $students,
            'teachers' => $teachers
        ]);

    } catch (PDOException $e) {
        // Log error (in production, avoid outputting detailed errors to users)
        error_log("Database Error: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Database error occurred']);
    } catch (Exception $e) {
        error_log("General Error: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'An error occurred']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
