<?php
include '../connect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
         $statusFilter = isset($_GET['status']) ? intval($_GET['status']) : null;

        // Base query for sessions
        $sessionsQuery = "SELECT 
            s.id, 
            s.teacher_id, 
            s.student_id,
            s.created_at,
            s.subjet AS subject, 
            s.cost, 
            s.status, 
            t.teacher_name, 
            st.student_name 
        FROM sessions s
        LEFT JOIN teacher t ON s.teacher_id = t.id
        LEFT JOIN student st ON s.student_id = st.id";

        // Add conditions if a status filter is provided
        if ($statusFilter !== null) {
            $sessionsQuery .= " WHERE s.status = :status";
        }

        // Prepare and execute the query
        $sessionsStmt = $pdo->prepare($sessionsQuery);
        if ($statusFilter !== null) {
            $sessionsStmt->bindParam(':status', $statusFilter, PDO::PARAM_INT);
        }
        $sessionsStmt->execute();
        $sessions = $sessionsStmt->fetchAll(PDO::FETCH_ASSOC);

        // Return the sessions as JSON
        echo json_encode([
            'status' => 'success',
            'sessions' => $sessions,
        ]);
    } catch (PDOException $e) {
        // Log and return an error
        error_log("Database Error: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Database error occurred']);
    } catch (Exception $e) {
        error_log("General Error: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'An error occurred']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
