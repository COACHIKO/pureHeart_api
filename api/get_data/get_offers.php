<?php
header('Content-Type: application/json');
include '../connect.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method', 405);
    }

    // Get JSON input
    $data = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON input', 400);
    }

    // Get token from request
    $token = $data['token'] ?? null;

    if (empty($token)) {
        throw new Exception('Token is required', 422);
    }

    // Get the student ID using the token
    $studentStmt = $pdo->prepare("SELECT id FROM student WHERE token = ?");
    $studentStmt->execute([$token]);
    $student = $studentStmt->fetch(PDO::FETCH_ASSOC);

    if (!$student) {
        throw new Exception('Invalid token: student not found', 404);
    }

    $studentId = $student['id'];

    // Get all offers for the student ID
    $offersStmt = $pdo->prepare("
        SELECT 
            o.id AS offer_id,
            o.teacher_id,
            t.teacher_price,
            t.subject_id,
            t.description AS teacher_description,
            t.date AS teacher_date,
            s.*
        FROM 
            offers o
        INNER JOIN 
            teacher_ad t ON o.teacher_id = t.id
        INNER JOIN 
            student_ad s ON o.student_ad = s.id
        WHERE 
            o.student_ad IN (SELECT id FROM student_ad WHERE student_id = ?)
    ");
    $offersStmt->execute([$studentId]);
    $offers = $offersStmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($offers)) {
        throw new Exception('No offers found for this student', 404);
    }

    // Return all offers and associated data
    echo json_encode([
        'status' => 'success',
        'message' => 'Offers retrieved successfully',
        'data' => $offers
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    $httpCode = is_int($e->getCode()) && $e->getCode() >= 100 && $e->getCode() <= 599 ? $e->getCode() : 500;
    http_response_code($httpCode);

    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
