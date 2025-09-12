<?php
$post = getPostData();
$token = $post['gameSession'] ?? '';
$username = $post['userName'] ?? '';

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
    $birdColor = $savedata['settings']['colors']['icon'] ?? [255,255,255];
    $overlayColor = $savedata['settings']['colors']['overlay'] ?? [255,255,255];
    echo encrypt("1" . ":" . $row['legacy_high_score'] . ":" . $icon . ":" . $overlay . ":0:0:0:0:0:0:" . ":" . $birdColor[0] . ":" . $birdColor[1] . ":" . $birdColor[2] . ":" . $overlayColor[0] . ":" . $overlayColor[1] . ":" . $overlayColor[2]);
} else {
    echo encrypt("-1");
}

$stmt->close();
$conn->close();