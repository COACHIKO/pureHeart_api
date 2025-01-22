<?php
header('Content-Type: application/json');
include '../connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        // Query to select all transactions with sender and receiver details
        $stmt = $pdo->prepare("SELECT id,sender_name, sender_number, reciver_name, recevier_number, amount, status FROM transaction");
        $stmt->execute();

        // Fetch all results
        $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Return the transactions as JSON
        echo json_encode([
            'status' => 'success',
            'transactions' => $transactions
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
