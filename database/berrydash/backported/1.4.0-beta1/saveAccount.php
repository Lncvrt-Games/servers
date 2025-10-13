<?php
$conn = newConnection();

$request_userName = $_POST['userName'] ?? 0;
$request_gameSession = $_POST['gameSession'] ?? '';
$request_highScore = $_POST['highScore'] ?? 0;
$request_icon = $_POST['icon'] ?? 0;
$request_overlay = $_POST['overlay'] ?? 0;

$stmt = $conn->prepare("SELECT * FROM users WHERE token = ? AND username = ?");
$stmt->bind_param("ss", $request_gameSession, $request_userName);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $savedata = json_decode($row['save_data'], true);
    $savedata['bird']['icon'] = $request_icon;
    $savedata['bird']['overlay'] = $request_overlay;
    $savedata = json_encode($savedata);

    $updateStmt = $conn->prepare("UPDATE users SET legacy_high_score = ?, save_data = ? WHERE token = ? AND username = ?");
    $updateStmt->bind_param("isss", $request_highScore, $savedata, $request_gameSession, $request_userName);
    $updateStmt->execute();
    echo "1";
    $updateStmt->close();
} else {
    echo "-3";
}

$stmt->close();
$conn->close();
?>