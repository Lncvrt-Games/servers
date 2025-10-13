<?php
$token = $_POST['gameSession'] ?? '';
$username = $_POST['userName'] ?? '';

$conn = newConnection();

$stmt = $conn->prepare("SELECT * FROM users WHERE token = ? AND username = ?");
$stmt->bind_param("ss", $token, $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $savedata = json_decode($row['save_data'], true);
    $icon = $savedata['bird']['icon'] ?? 1; 
    $overlay = $savedata['bird']['overlay'] ?? 0;   
    echo "1:" . $row['legacy_high_score'] . ":" . $icon . ":" . $overlay;
} else {
    echo "-1";
}

$stmt->close();
$conn->close();