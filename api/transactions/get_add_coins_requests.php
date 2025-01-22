<?php
header('Content-Type: application/json');
include '../connect.php';  // Include your database connection file

try {
    // Prepare SQL statement to fetch all transactions
    $stmt = $pdo->prepare("SELECT * FROM app_transaction");
    
    // Execute the query
    $stmt->execute();
    
    // Fetch all results as an associative array
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Check if there are any transactions
    if (count($transactions) > 0) {
        // Return the transactions in JSON format
        echo json_encode([
            'status' => 'success',
            'transactions' => $transactions
        ]);
    } else {
        // If no transactions found, return a message
        echo json_encode([
            'status' => 'success',
            'message' => 'No transactions found'
        ]);
    }

} catch (PDOException $e) {
    // Handle any database errors
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    // Handle other exceptions
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
