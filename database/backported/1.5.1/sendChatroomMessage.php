<?php
$post = getPostData();
$request_content = $post['content'] ?? '';
$token = $post['gameSession'] ?? '';

if (!preg_match('/^[ a-zA-Z0-9!@#\$%\^&\*\(\)_\+\-=\[\]\{\};\':",\.<>\/\?\\\\|`~]+$/', $request_content)) {
    exitWithMessage("-1");
}

$conn = newConnection();

$stmt = $conn->prepare("SELECT * FROM users WHERE token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
if (!$row) exitWithMessage("-1");
$stmt->close();

$id = $row["id"];
$content = base64_encode($request_content);
$time = time();

$stmt = $conn->prepare("INSERT INTO chats (userId, content, timestamp) VALUES (?, ?, ?)");
$stmt->bind_param("isi", $id, $content, $time);
$stmt->execute();
$stmt->close();

echo encrypt("1");

$conn->close();