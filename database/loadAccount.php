<?php
require __DIR__ . '/../incl/util.php';
setPlainHeader();
checkClientDatabaseVersion();

$post = getPostData();
$token = $post['token'] ?? '';
$username = $post['username'] ?? '';

$conn = newConnection();

$stmt = $conn->prepare("SELECT * FROM users WHERE token = ? AND username = ?");
$stmt->bind_param("ss", $token, $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo encrypt(json_encode([
        "success" => true,
        "highscore" => (string)$row['highScore'],
        "icon" => (int)$row['icon'],
        "overlay" => (int)$row['overlay'],
        "totalNormalBerries" => (string)$row['totalNormalBerries'],
        "totalPoisonBerries" => (string)$row['totalPoisonBerries'],
        "totalSlowBerries" => (string)$row['totalSlowBerries'],
        "totalUltraBerries" => (string)$row['totalUltraBerries'],
        "totalSpeedyBerries" => (string)$row['totalSpeedyBerries'],
        "totalCoinBerries" => (string)$row['totalCoinBerries'],
        "totalAttempts" => (string)$row['totalAttempts'],
        "birdColor" => json_decode((string)$row['birdColor']),
        "overlayColor" => json_decode((string)$row['overlayColor']),
        "marketplaceData" => json_decode((string)$row['marketplaceData'])
    ]));
} else {
    echo encrypt(json_encode(["success" => false, "message" => "Invalid session token or username, please refresh login"]));
}

$stmt->close();
$conn->close();