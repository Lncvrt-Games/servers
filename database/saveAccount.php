<?php
require __DIR__ . '/../incl/util.php';
setPlainHeader();
checkClientDatabaseVersion();

$post = getPostData();
$token = $post['token'] ?? '';
$username = $post['username'] ?? '';
$highScore = (string)$post['highScore'] ?? '0';
$icon = (int)$post['icon'] ?? 1;
$overlay = (int)$post['overlay'] ?? 0;
$totalNormalBerries = (string)$post['totalNormalBerries'] ?? '0';
$totalPoisonBerries = (string)$post['totalPoisonBerries'] ?? '0';
$totalSlowBerries = (string)$post['totalSlowBerries'] ?? '0';
$totalUltraBerries = (string)$post['totalUltraBerries'] ?? '0';
$totalSpeedyBerries = (string)$post['totalSpeedyBerries'] ?? '0';
$totalCoinBerries = (string)$post['totalCoinBerries'] ?? '0';
$totalAttempts = (string)$post['totalAttempts'] ?? '0';
$birdColor = (string)$post['birdColor'] ?? '[255,255,255]';
$overlayColor = (string)$post['overlayColor'] ?? '[255,255,255]';
$marketplaceData = (string)$post['marketplaceData'] ?? '[]';

$conn = newConnection();

$stmt = $conn->prepare("SELECT * FROM users WHERE token = ? AND username = ?");
$stmt->bind_param("ss", $token, $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $updateStmt = $conn->prepare("UPDATE users SET highScore = ?, icon = ?, overlay = ?, totalNormalBerries = ?, totalPoisonBerries = ?, totalSlowBerries = ?, totalUltraBerries = ?, totalSpeedyBerries = ?, totalCoinBerries = ?, totalAttempts = ?, birdColor = ?, overlayColor = ?, marketplaceData = ? WHERE token = ? AND username = ?");
    $updateStmt->bind_param("iiiiiiiiiisssss", 
        $highScore, 
        $icon, 
        $overlay, 
        $totalNormalBerries, 
        $totalPoisonBerries, 
        $totalSlowBerries, 
        $totalUltraBerries, 
        $totalSpeedyBerries, 
        $totalCoinBerries, 
        $totalAttempts, 
        $birdColor, 
        $overlayColor, 
        $marketplaceData, 
        $token, 
        $username
    );
    $updateStmt->execute();
    $updateStmt->close();
    echo encrypt(json_encode(["success" => true]));
} else {
    echo encrypt(json_encode(["success" => false, "message" => "Invalid session token or username, please refresh login"]));
}

$stmt->close();
$conn->close();