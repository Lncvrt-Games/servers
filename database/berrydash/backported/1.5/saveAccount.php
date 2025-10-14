<?php
$post = getPostData();
$token = $post['gameSession'] ?? '';
$username = $post['userName'] ?? '';
$highScore = (int)$post['highScore'] ?? 0;
$icon = (int)$post['icon'] ?? 1;
$overlay = (int)$post['overlay'] ?? 0;
$birdR = (int)$post['birdR'] ?? 255;
$birdG = (int)$post['birdG'] ?? 255;
$birdB = (int)$post['birdB'] ?? 255;
$overlayR = (int)$post['overlayR'] ?? 255;
$overlayG = (int)$post['overlayG'] ?? 255;
$overlayB = (int)$post['overlayB'] ?? 255;
$birdColor = [$birdR, $birdG, $birdB];
$overlayColor = [$overlayR, $overlayG, $overlayB];

$conn = newConnection();

$stmt = $conn->prepare("SELECT * FROM users WHERE token = ? AND username = ?");
$stmt->bind_param("ss", $token, $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $savedata = json_decode($row['save_data'], true);
    $savedata['bird']['icon'] = $icon;
    $savedata['bird']['overlay'] = $overlay;
    $savedata['settings']['colors']['icon'] = $birdColor;
    $savedata['settings']['colors']['overlay'] = $overlayColor;
    $savedata = jsonEncode($savedata);
    $updateStmt = $conn->prepare("UPDATE users SET legacy_high_score = ?, save_data = ? WHERE token = ? AND username = ?");
    $updateStmt->bind_param("isss", 
        $highScore, 
        $savedata, 
        $token, 
        $username
    );
    $updateStmt->execute();
    $updateStmt->close();
    echo encrypt("1");
} else {
    echo encrypt("-1");
}

$stmt->close();
$conn->close();