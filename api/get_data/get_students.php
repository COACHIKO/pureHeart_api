<?php
include '../connect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        // Retrieve the 'is_active' filter from the query parameters and sanitize it
        $isActiveFilter = isset($_GET['is_active']) ? intval($_GET['is_active']) : null;

        // Base queries
        $studentQuery = "SELECT id, student_name, student_number,rank,student_image, student_stage,balance, token, created_at, is_active FROM student";
 
        // Add filter conditions if 'is_active' is set
        if ($isActiveFilter === 1 || $isActiveFilter === 0) {
            $studentQuery .= " WHERE is_active = :is_active";
         }

        // Prepare and execute the student query
        $studentsStmt = $pdo->prepare($studentQuery);
        if ($isActiveFilter === 1 || $isActiveFilter === 0) {
            $studentsStmt->bindParam(':is_active', $isActiveFilter, PDO::PARAM_INT);
        }
        $studentsStmt->execute();
        $students = $studentsStmt->fetchAll(PDO::FETCH_ASSOC);
 
         echo json_encode([
            'status' => 'success',
            'students' => $students,
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
