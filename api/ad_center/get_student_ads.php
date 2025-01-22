<?php
header('Content-Type: application/json');
include '../connect.php';

function getDaysFromBinary($daysBinary) {
    $dayNames = ['Friday', 'Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday'];
    $daysArray = [];

    for ($i = 0; $i < 7; $i++) {
        if ($daysBinary[$i] === '1') {
            $daysArray[] = $dayNames[$i];
        }
    }

    return $daysArray;
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Read the JSON input
        $data = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON input', 400);
        }

        // Get the inputs
        $studentToken = $data['student_token'] ?? null;
        $studentPrice = $data['student_price'] ?? null;
        $subjectId = $data['subject_id'] ?? null;
        $days = $data['days'] ?? null;
        $time = $data['time'] ?? null; // New input for time

        // Validate required fields
        if (empty($studentToken) || empty($studentPrice) || empty($subjectId) || empty($days) || empty($time)) {
            echo json_encode(['status' => 'error', 'message' => 'student_token, student_price, subject_id, days, and time are required']);
            http_response_code(422);
            exit;
        }

        // Validate student_price
        if (!is_numeric($studentPrice) || (int)$studentPrice <= 0) {
            throw new Exception('Invalid student_price. It must be a positive number', 422);
        }

        // Validate `days` format
        if (strlen($days) !== 7 || !preg_match('/^[01]{7}$/', $days)) {
            throw new Exception('Invalid days format. Must be a 7-character string with only 0s and 1s', 422);
        }

        // Validate `time` format (e.g., 24-hour format)
        if (!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $time)) {
            throw new Exception('Invalid time format. Must be in HH:MM (24-hour) format', 422);
        }

        // Search for the student using the token
        $studentStmt = $pdo->prepare("SELECT s.*, ss.step_name FROM student s 
                                      LEFT JOIN school_steps ss ON s.student_stage = ss.id 
                                      WHERE s.token = ?");
        $studentStmt->execute([$studentToken]);
        $student = $studentStmt->fetch();

        if (!$student) {
            throw new Exception('Student not found', 404);
        }

        $studentName = $student['student_name'];
        $studentRate = $student['rate']; // Get the student's rate
        $studentStage = $student['student_stage']; // Get the student's stage ID
        $studentStepName = $student['step_name']; // Get the step name

        // Validate subject_id and get subject name
        $subjectStmt = $pdo->prepare("SELECT * FROM subjects WHERE id = ?");
        $subjectStmt->execute([$subjectId]);
        $subject = $subjectStmt->fetch();

        if (!$subject) {
            throw new Exception('Subject not found', 404);
        }

        $subjectName = $subject['name'];

        // Insert student advertisement
        $adStmt = $pdo->prepare("INSERT INTO student_ad (student_id, student_price, subject_id, days, time) 
                                 VALUES (?, ?, ?, ?, ?)");
        $adStmt->execute([
            $student['id'],
            (int)$studentPrice,
            $subjectId,
            $days,
            $time // Insert time into the database
        ]);

        // Get the new ad's ID
        $newAdId = $pdo->lastInsertId();

        // Respond with success
        echo json_encode([
            'status' => 'success',
            'message' => 'Student advertisement created successfully',
            'ad_id' => $newAdId,
            'student_name' => $studentName,
            'student_price' => (int)$studentPrice,
            'student_rate' => $studentRate, // Include the student rate in the response
            'student_stage' => $studentStage, // Include the student stage in the response
            'student_step_name' => $studentStepName, // Include the student step name in the response
            'subject_name' => $subjectName,
            'days' => getDaysFromBinary($days),
            'time' => $time // Return the time to the client
        ], JSON_UNESCAPED_UNICODE);

    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Get subject_id from query parameters (optional)
        $subjectIdFilter = isset($_GET['subject_id']) ? (int)$_GET['subject_id'] : null;

        // Prepare the base query
        $sql = "SELECT sa.*, s.student_name, s.rate AS student_rate, sub.name AS subject_name, s.student_stage, ss.step_name AS student_step_name 
                FROM student_ad sa
                JOIN student s ON sa.student_id = s.id
                JOIN subjects sub ON sa.subject_id = sub.id
                LEFT JOIN school_steps ss ON s.student_stage = ss.id";

        // If subject_id filter is provided, add it to the WHERE clause
        if ($subjectIdFilter) {
            $sql .= " WHERE sa.subject_id = ?";
            $adStmt = $pdo->prepare($sql);
            $adStmt->execute([$subjectIdFilter]);
        } else {
            $adStmt = $pdo->prepare($sql);
            $adStmt->execute();
        }

        $ads = $adStmt->fetchAll(PDO::FETCH_ASSOC);

        // Map the binary 'days' string to readable days for each ad
        foreach ($ads as &$ad) {
            $ad['days'] = getDaysFromBinary($ad['days']);
        }

        // Respond with all ads
        echo json_encode([
            'status' => 'success',
            'message' => 'Student ads retrieved successfully',
            'data' => $ads
        ], JSON_UNESCAPED_UNICODE);
    } else {
        throw new Exception('Invalid request method', 400);
    }

} catch (Exception $e) {
    http_response_code((int) ($e->getCode() ?: 500));
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
