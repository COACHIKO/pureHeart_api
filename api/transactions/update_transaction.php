<?php
header('Content-Type: application/json');
include '../connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Parse JSON input
    $data = json_decode(file_get_contents('php://input'), true);
    $transactionId = $data['transaction_id'] ?? null;
    $amount = $data['amount'] ?? null;
    $status = $data['status'] ?? null;

    // Validate transaction_id (required)
    if (!$transactionId) {
        echo json_encode(['status' => 'error', 'message' => 'Transaction ID is required']);
        exit;
    }

    try {
        // Retrieve the transaction details by ID
        $stmt = $pdo->prepare("SELECT * FROM transaction WHERE id = ?");
        $stmt->execute([$transactionId]);
        $transaction = $stmt->fetch();

        // Check if the transaction exists
        if (!$transaction) {
            echo json_encode(['status' => 'error', 'message' => 'Transaction not found']);
            exit;
        }

        // Prepare the updates
        $updateFields = [];
        $updateValues = [];

        // If an amount is provided, validate and update it
        if ($amount !== null) {
            if ($amount <= 0) {
                echo json_encode(['status' => 'error', 'message' => 'Amount must be greater than zero']);
                exit;
            }
            $updateFields[] = "amount = ?";
            $updateValues[] = $amount;
        }

        // If a status is provided, validate and update it
        if ($status !== null) {
            if (!in_array($status, [0, 1,2])) {  // Only 0 or 1 are valid status values
                echo json_encode(['status' => 'error', 'message' => 'Invalid status value']);
                exit;
            }
            $updateFields[] = "status = ?";
            $updateValues[] = $status;
        }

        // If no updates are provided (amount or status), return an error
        if (empty($updateFields)) {
            echo json_encode(['status' => 'error', 'message' => 'No fields to update']);
            exit;
        }

        // Append the transaction ID to the update values
        $updateValues[] = $transactionId;

        // Prepare the UPDATE SQL query
        $updateQuery = "UPDATE transaction SET " . implode(", ", $updateFields) . " WHERE id = ?";
        $updateStmt = $pdo->prepare($updateQuery);
        $updateStmt->execute($updateValues);

        // Return success response
        echo json_encode([
            'status' => 'success',
            'message' => 'Transaction updated successfully',
            'transaction_id' => $transactionId,
            'updated_amount' => $amount ?? $transaction['amount'],  // If no new amount is provided, keep the old value
            'updated_status' => $status ?? $transaction['status']  // If no new status is provided, keep the old value
        ]);
    } catch (PDOException $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
