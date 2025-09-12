<?php
$conn = newConnection();

$post = getPostData();
$username = $post['username'];
$password = $post['password'];
$currentHighScore = $post['currentHighScore'] ?? 0;
$loginType = $post['loginType'] ?? '0';

$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    exitWithMessage("-1");
}

$user = $result->fetch_assoc();

if (!password_verify($password, $user["password"])) {
    exitWithMessage("-1");
}

$id = $user['id'];
$token = $user['token'];
$ip = getIPAddress();

$stmt = $conn->prepare("UPDATE users SET latest_ip = ?, token = ? WHERE id = ?");
$stmt->bind_param("ssi", $ip, $token, $id);
$stmt->execute();

if ($currentHighScore > $user['legacy_high_score']) {
    $stmt = $conn->prepare("UPDATE users SET legacy_high_score = ? WHERE id = ?");
    $stmt->bind_param("ii", $currentHighScore, $id);
    $stmt->execute();
    $user['legacy_high_score'] = $currentHighScore;
}

$savedata = json_decode($user['save_data'], true);
$birdColor = $savedata['settings']['colors']['icon'] ?? [255,255,255];
$overlayColor = $savedata['settings']['colors']['overlay'] ?? [255,255,255];

if ($loginType === "0") {
    echo encrypt("1" . ":" . $token . ":" . $user['username'] . ":" . $id . ":" . $user['legacy_high_score'] . ":" . ($savedata['bird']['icon'] ?? 1) . ":" . ($savedata['bird']['overlay'] ?? 0) . ":0:0:0:0:0:" . ":" . $birdColor[0] . ":" . $birdColor[1] . ":" . $birdColor[2] . ":" . $overlayColor[0] . ":" . $overlayColor[1] . ":" . $overlayColor[2]);
} elseif ($loginType === "1") {
    echo encrypt("1" . ":" . $token . ":" . $user['username'] . ":" . $id);
}
$stmt->close();
$conn->close();
