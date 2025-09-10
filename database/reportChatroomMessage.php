<?php
require __DIR__ . '/../incl/util.php';
setPlainHeader();
checkClientDatabaseVersion();

$post = getPostData();
$id = $post['id'] ?? '';
$reason = $post['reason'] ?? '';
$token = $post['token'] ?? '';
$username = $post['username'] ?? '';

if (!preg_match('/^[ a-zA-Z0-9!@#\$%\^&\*\(\)_\+\-=\[\]\{\};\':",\.<>\/\?\\\\|`~]+$/', $reason)) {
    exitWithMessage(json_encode(["success" => false]));
}

$conn = newConnection();

$stmt = $conn->prepare("SELECT * FROM users WHERE token = ? AND username = ?");
$stmt->bind_param("ss", $token, $username);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
if (!$row) exit;
$stmt->close();

$user_id = $row["id"];

$stmt = $conn->prepare("SELECT id FROM chats WHERE userId != ? AND deleted_at = 0 AND id = ? LIMIT 1");
$stmt->bind_param("ii", $user_id, $id);
$stmt->execute();
$res = $stmt->get_result();
$stmt->close();

if ($res->num_rows < 0) exit;

$stmt = $conn->prepare("SELECT id FROM chatroom_reports WHERE chatId = ? AND userId = ? LIMIT 1");
$stmt->bind_param("ii", $id, $user_id);
$stmt->execute();
$res = $stmt->get_result();
$stmt->close();

if ($res->num_rows > 0) exit;

$time = time();
$reason = base64_encode($reason);

$stmt = $conn->prepare("INSERT INTO chatroom_reports (chatid, userId, reason, timestamp) VALUES (?, ?, ?, ?)");
$stmt->bind_param("iisi", $id, $user_id, $reason, $time);
$stmt->execute();

$conn->close();