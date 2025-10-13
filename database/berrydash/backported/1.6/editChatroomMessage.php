<?php
$post = getPostData();
$id = $post['id'] ?? '';
$content = $post['content'] ?? '';
$token = $post['token'] ?? '';
$username = $post['username'] ?? '';

if (!preg_match('/^[ a-zA-Z0-9!@#\$%\^&\*\(\)_\+\-=\[\]\{\};\':",\.<>\/\?\\\\|`~]+$/', $content)) exit;

$conn = newConnection();

$stmt = $conn->prepare("SELECT * FROM users WHERE token = ? AND username = ?");
$stmt->bind_param("ss", $token, $username);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
if (!$row) exit;
$stmt->close();

$user_id = $row["id"];
$content = base64_encode($content);

$stmt = $conn->prepare("UPDATE chats SET content = ? WHERE userId = ? AND id = ?");
$stmt->bind_param("sii", $content, $user_id, $id);
$stmt->execute();
$stmt->close();

$conn->close();