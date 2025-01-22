<?php
header('Content-Type: application/json');
include '../connect.php';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON input', 400);
        }

        // Reading input values
        $teacherToken = $data['teacher_token'] ?? null;
        $teacherPrice = $data['teacher_price'] ?? null;
        $subjectId = $data['subject_id'] ?? null;  // Accepting subject_id
        $days = $data['days'] ?? '0000000';  // Default value for days

        // Validate input
        if (empty($teacherToken) || empty($teacherPrice) || empty($subjectId)) {
            echo json_encode(['status' => 'error', 'message' => 'teacher_token, teacher_price, and subject_id are required']);
            http_response_code(422); // Send 422 status code explicitly
            exit;
        }

        if (!is_numeric($teacherPrice) || (int)$teacherPrice <= 0) {
            throw new Exception('Invalid teacher_price. It must be a positive number', 422);
        }

        // Check if the teacher exists
        $teacherStmt = $pdo->prepare("SELECT * FROM teacher WHERE token = ?");
        $teacherStmt->execute([$teacherToken]);
        $teacher = $teacherStmt->fetch();
        if (!$teacher) {
            throw new Exception('Teacher not found', 404);
        }

        // Get teacher name
        $teacherName = $teacher['teacher_name'];

        // Insert the advertisement into the database
        $adStmt = $pdo->prepare("INSERT INTO teacher_ad (teacher_name, teacher_price, subject_id, days) 
                                 VALUES (?, ?, ?, ?)");
        $adStmt->execute([ 
            $teacherName, 
            (int)$teacherPrice, 
            (int)$subjectId,  // Using subject_id here
            $days 
        ]);

        // Get the new ad ID
        $newAdId = $pdo->lastInsertId();

        echo json_encode([
            'status' => 'success',
            'message' => 'Advertisement created successfully',
            'ad_id' => $newAdId,
            'teacher_name' => $teacherName,
            'teacher_price' => (int)$teacherPrice,
            'subject_id' => (int)$subjectId,
            'days' => getDaysFromBinary($days)
        ], JSON_UNESCAPED_UNICODE);

    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Fetch subject_id from the query parameters if passed
        $subjectId = $_GET['subject_id'] ?? null;

        // Modify query to filter by subject_id if provided
        if ($subjectId) {
            $adStmt = $pdo->prepare("
            SELECT 
                ta.id AS ad_id,
                ta.teacher_id,
                ta.teacher_price,
                ta.description,
                ta.date,
                ta.status,
                ta.created_at,
                t.teacher_name,
                CASE 
                    WHEN t.gender = 1 THEN 'male'
                    ELSE 'female'
                END AS gender,
                s.name AS subject_name
            FROM 
                teacher_ad ta
            INNER JOIN 
                teacher t ON ta.teacher_id = t.id
            INNER JOIN 
                subjects s ON ta.subject_id = s.id
            WHERE 
                ta.subject_id = ?
        ");
        
            $adStmt->execute([$subjectId]);
        } else {
            // Fetch all ads if no subject_id filter is passed
            $adStmt = $pdo->prepare("
                SELECT 
                    ta.id AS ad_id,
                    ta.teacher_id,
                    ta.teacher_price,
                    ta.description,
                    ta.date,
                    ta.status,
                    ta.created_at,
                    t.teacher_name,
                    s.name AS subject_name
                FROM 
                    teacher_ad ta
                INNER JOIN 
                    teacher t ON ta.teacher_id = t.id
                INNER JOIN 
                    subjects s ON ta.subject_id = s.id
            ");
            $adStmt->execute();
        }

        $ads = $adStmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'status' => 'success',
            'message' => 'Ads retrieved successfully',
            'data' => $ads
        ], JSON_UNESCAPED_UNICODE);

    } else {
        throw new Exception('Invalid request method', 400);
    }

} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
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
