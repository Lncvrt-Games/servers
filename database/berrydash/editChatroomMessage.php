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
        require __DIR__ . '/backported/1.6/editChatroomMessage.php';
        exit;
    }
}
setJsonHeader();
checkClientDatabaseVersion();

$id = $_POST['id'] ?? '';
$content = $_POST['content'] ?? '';
$token = $_POST['token'] ?? '';
$username = $_POST['username'] ?? '';

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