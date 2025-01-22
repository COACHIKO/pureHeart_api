<?php
header('Content-Type: application/json');
include '../connect.php';

try {
    // Check if the request method is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method', 400);
    }

    // Get JSON input data
    $data = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON input', 400);
    }

    // Get transaction_id
    $transactionId = $data['transaction_id'] ?? null;

    if (empty($transactionId)) {
        echo json_encode(['status' => 'error', 'message' => 'transaction_id is required']);
        http_response_code(422);
        exit;
    }

    // Start transaction to ensure data consistency
    $pdo->beginTransaction();

    // Fetch the transaction data
    $transactionStmt = $pdo->prepare("SELECT * FROM app_transaction WHERE id = ?");
    $transactionStmt->execute([$transactionId]);
    $transaction = $transactionStmt->fetch();

    if (!$transaction) {
        throw new Exception('Transaction not found', 404);
    }

    // Check if the transaction is already processed
    if ($transaction['status'] == 1) {
        echo json_encode(['status' => 'error', 'message' => 'Transaction already processed']);
        $pdo->rollBack(); // Ensure rollback only if a transaction has started
        exit;
    }

    // Update transaction status to 1 (processed)
    $updateStmt = $pdo->prepare("UPDATE app_transaction SET status = 1 WHERE id = ?");
    $updateStmt->execute([$transactionId]);

    // Add the amount to the student's balance
    $studentStmt = $pdo->prepare("SELECT * FROM student WHERE token = ?");
    $studentStmt->execute([$transaction['token']]);
    $student = $studentStmt->fetch();

    if (!$student) {
        throw new Exception('Student not found', 404);
    }

    // Update student balance
    $newBalance = $student['balance'] + $transaction['amount'];
    $updateBalanceStmt = $pdo->prepare("UPDATE student SET balance = ? WHERE id = ?");
    $updateBalanceStmt->execute([$newBalance, $student['id']]);

    // Commit the transaction
    $pdo->commit();

    // Return success response
    echo json_encode([
        'status' => 'success',
        'message' => 'Transaction updated and amount added to student balance successfully'
    ]);
} catch (Exception $e) {
    // Rollback transaction in case of error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
} catch (PDOException $e) {
    // Rollback in case of database error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
