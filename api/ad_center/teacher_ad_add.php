<?php
header('Content-Type: application/json');
include '../connect.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method', 400);
    }
    $data = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON input', 400);
    }

    $teacherToken = $data['teacher_token'] ?? null;
    $teacherPrice = $data['teacher_price'] ?? null;
    $subjectId = $data['subject_id'] ?? null;
    $unitNum = $data['unit_num'] ?? null;
    $description = $data['description'] ?? null;
    $date = $data['date'] ?? null;

    if (empty($teacherToken) || empty($teacherPrice) || empty($subjectId) || empty($unitNum) || empty($description) || empty($date)) {
        echo json_encode(['status' => 'error', 'message' => 'teacher_token, teacher_price, subject_id, unit_num, description, and date are required']);
        http_response_code(422);
        exit;
    }

    if (!is_numeric($teacherPrice) || (int)$teacherPrice <= 0) {
        throw new Exception('Invalid teacher_price. It must be a positive number', 422);
    }

    if (!is_numeric($unitNum) || (int)$unitNum <= 0) {
        throw new Exception('Invalid unit_num. It must be a positive number', 422);
    }

    $teacherStmt = $pdo->prepare("SELECT * FROM teacher WHERE token = ?");
    $teacherStmt->execute([$teacherToken]);
    $teacher = $teacherStmt->fetch();

    if (!$teacher) {
        throw new Exception('Teacher not found', 404);
    }

    $teacherId = $teacher['id'];

    $adStmt = $pdo->prepare("INSERT INTO teacher_ad (teacher_id, teacher_price, subject_id, unit_num, description, date, status, created_at) 
                             VALUES (?, ?, ?, ?, ?, ?, 1, NOW())");
    $adStmt->execute([
        $teacherId,
        (int)$teacherPrice,
        (int)$subjectId,
        (int)$unitNum,
        $description,
        $date
    ]);

     $newAdId = $pdo->lastInsertId();

    echo json_encode([
        'status' => 'success',
        'message' => 'Advertisement created successfully',
        'ad_id' => $newAdId,
        'teacher_id' => $teacherId,
        'teacher_price' => (int)$teacherPrice,
        'subject_id' => (int)$subjectId,
        'unit_num' => (int)$unitNum,
        'description' => $description,
        'date' => $date,
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    // Handle general errors
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
} catch (PDOException $e) {
    // Handle database errors
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
