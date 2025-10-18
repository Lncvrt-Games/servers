<?php
require __DIR__ . '/../../incl/util.php';
setJsonHeader();
checkClientDatabaseVersion();

$request_content = $_POST['content'] ?? '';
$token = $_POST['token'] ?? '';
$username = $_POST['username'] ?? '';

if (!preg_match('/^[ a-zA-Z0-9!@#\$%\^&\*\(\)_\+\-=\[\]\{\};\':",\.<>\/\?\\\\|`~]+$/', $request_content)) {
    exitWithMessage(jsonEncode(["success" => false], true), false);
}

$conn = newConnection();

$stmt = $conn->prepare("SELECT * FROM users WHERE token = ? AND username = ?");
$stmt->bind_param("ss", $token, $username);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
if (!$row) exitWithMessage(jsonEncode(["success" => false], true), false);
$stmt->close();

$id = $row["id"];
$content = base64_encode($request_content);
$time = time();

$stmt = $conn->prepare("INSERT INTO userposts (userId, content, timestamp) VALUES (?, ?, ?)");
$stmt->bind_param("isi", $id, $content, $time);
$stmt->execute();
$stmt->close();

echo jsonEncode(["success" => true], true);

$conn->close();