<?php
function generateUniqueToken($pdo, $length = 25) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()';
    $token = '';

    do {
        $token = '';
        for ($i = 0; $i < $length; $i++) {
            $token .= $characters[random_int(0, strlen($characters) - 1)];
        }

        // Check if the token already exists in the database
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM student WHERE token = ?");
        $stmt->execute([$token]);
        $count = $stmt->fetchColumn();

    } while ($count > 0); // Repeat until we find a unique token

    return $token;
}

 
