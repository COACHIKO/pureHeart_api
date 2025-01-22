<?php
header('Content-Type: application/json');
include '../connect.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method', 405);
    }

    $data = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON input', 400);
    }

    $teacherToken = $data['teacher_token'] ?? null;
    $studentAd = $data['student_ad'] ?? null;

    if (empty($teacherToken) || empty($studentAd)) {
        throw new Exception('teacher_token and student_ad are required', 422);
    }

    $teacherStmt = $pdo->prepare("SELECT id FROM teacher WHERE token = ?");
    $teacherStmt->execute([$teacherToken]);
    $teacher = $teacherStmt->fetch(PDO::FETCH_ASSOC);

    if (!$teacher) {
        throw new Exception('Invalid teacher_token', 404);
    }

    $teacherId = $teacher['id'];

    $offerStmt = $pdo->prepare("INSERT INTO offers (student_ad, teacher_id) VALUES (?, ?)");
    $offerStmt->execute([$studentAd, $teacherId]);

    $offerId = $pdo->lastInsertId();

    echo json_encode([
        'status' => 'success',
        'message' => 'Offer created successfully',
        'offer_id' => $offerId,
        'student_ad' => $studentAd,
        'teacher_id' => $teacherId,
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    // Ensure HTTP status code is always an integer
    $httpCode = is_int($e->getCode()) && $e->getCode() >= 100 && $e->getCode() <= 599 ? $e->getCode() : 500;
    http_response_code($httpCode);

    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
} catch (PDOException $e) {
    http_response_code(500); // Internal Server Error for database issues
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
