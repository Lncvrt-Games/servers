<?php
require __DIR__ . '/../incl/util.php';
setPlainHeader();
checkClientDatabaseVersion();

$post = getPostData();
$request_content = $post['content'] ?? '';
$token = $post['token'] ??  null;

if (!preg_match('/^[ a-zA-Z0-9!@#\$%\^&\*\(\)_\+\-=\[\]\{\};\':",\.<>\/\?\\\\|`~]+$/', $request_content)) {
    exitWithMessage(json_encode(["success" => false, "message" => "Invalid content recieved"]));
}

$conn = newConnection();

$stmt = $conn->prepare("SELECT * FROM users WHERE token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
if (!$row) exitWithMessage(json_encode(["success" => false, "message" => "Invalid session token or username, please refresh login"]));
$stmt->close();

$id = $row["id"];
$content = base64_encode($request_content);
$time = time();

$stmt = $conn->prepare("INSERT INTO chats (userId, content, timestamp) VALUES (?, ?, ?)");
$stmt->bind_param("isi", $id, $content, $time);
$stmt->execute();
$stmt->close();

echo encrypt(json_encode(["success" => true]));

$conn->close();