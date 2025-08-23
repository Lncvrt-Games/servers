<?php
require __DIR__ . '/../incl/util.php';
setPlainHeader();
checkClientDatabaseVersion();
$conn = newConnection();

$post = getPostData();
$username = $post['username'];
$password = $post['password'];
$currentHighScore = $post['currentHighScore'] ?? 0;

$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    exitWithMessage(json_encode(["success" => false, "message" => "Invalid username or password"]));
}

$user = $result->fetch_assoc();

if (!password_verify($password, $user["password"])) {
    exitWithMessage(json_encode(["success" => false, "message" => "Invalid username or password"]));
}

$id = $user['id'];
$token = $user['token'];
$ip = getIPAddress();

$stmt = $conn->prepare("UPDATE users SET latest_ip = ?, token = ? WHERE id = ?");
$stmt->bind_param("ssi", $ip, $token, $id);
$stmt->execute();

if ($currentHighScore > $user['highScore']) {
    $stmt = $conn->prepare("UPDATE users SET highScore = ? WHERE id = ?");
    $stmt->bind_param("ii", $currentHighScore, $id);
    $stmt->execute();
    $user['highScore'] = $currentHighScore;
}

$data = ["session" => $token, "username" => $user['username'], "userid" => $id];

echo encrypt(json_encode(["success" => true, "data" => $data]));

$stmt->close();
$conn->close();
