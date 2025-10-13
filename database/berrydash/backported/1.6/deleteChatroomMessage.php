<?php
$post = getPostData();
$id = $post['id'] ?? '';
$token = $post['token'] ?? '';
$username = $post['username'] ?? '';

$conn = newConnection();

$stmt = $conn->prepare("SELECT * FROM users WHERE token = ? AND username = ?");
$stmt->bind_param("ss", $token, $username);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
if (!$row) exit;
$stmt->close();

$user_id = $row["id"];
$time = time();

$stmt = $conn->prepare("UPDATE chats SET deleted_at = ? WHERE userId = ? AND id = ?");
$stmt->bind_param("iii", $time, $user_id, $id);
$stmt->execute();
$stmt->close();

$conn->close();