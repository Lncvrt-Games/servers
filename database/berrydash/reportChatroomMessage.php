<?php
require __DIR__ . '/../../incl/util.php';
setPlainHeader();
if (isAllowedDatabaseVersion(getClientVersion())) {
    if (
        getClientVersion() == "1.6" ||
        getClientVersion() == "1.6.1" ||
        getClientVersion() == "1.6.2" ||
        getClientVersion() == "1.6.3" ||
        getClientVersion() == "1.7" ||
        getClientVersion() == "1.7.1" ||
        getClientVersion() == "1.8" ||
        getClientVersion() == "1.8.1" ||
        getClientVersion() == "1.8.2"
    ) {
        require __DIR__ . '/backported/1.6/reportChatroomMessage.php';
        exit;
    }
}
setJsonHeader();
checkClientDatabaseVersion();

$id = $_POST['id'] ?? '';
$reason = $_POST['reason'] ?? '';
$token = $_POST['token'] ?? '';
$username = $_POST['username'] ?? '';

if (!preg_match('/^[ a-zA-Z0-9!@#\$%\^&\*\(\)_\+\-=\[\]\{\};\':",\.<>\/\?\\\\|`~]+$/', $reason)) {
    exitWithMessage(jsonEncode(["success" => false], true), false);
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