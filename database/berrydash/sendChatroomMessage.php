<?php
require __DIR__ . '/../../incl/util.php';
setPlainHeader();
if (isAllowedDatabaseVersion(getClientVersion())) {
    if (getClientVersion() == "1.5.1" || getClientVersion() == "1.5.2") {
        require __DIR__ . '/backported/1.5.1/sendChatroomMessage.php';
        exit;
    }
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
        require __DIR__ . '/backported/1.6/sendChatroomMessage.php';
        exit;
    }
}
checkClientDatabaseVersion();

$request_content = $_POST['content'] ?? '';
$token = $_POST['token'] ?? '';
$username = $_POST['username'] ?? '';

if (!preg_match('/^[ a-zA-Z0-9!@#\$%\^&\*\(\)_\+\-=\[\]\{\};\':",\.<>\/\?\\\\|`~]+$/', $request_content)) {
    exitWithMessage(json_encode(["success" => false, "message" => "Invalid content recieved"]), false);
}

$conn = newConnection();

$stmt = $conn->prepare("SELECT * FROM users WHERE token = ? AND username = ?");
$stmt->bind_param("ss", $token, $username);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
if (!$row) exitWithMessage(json_encode(["success" => false, "message" => "Invalid session token or username, please refresh login"]), false);
$stmt->close();

$id = $row["id"];
$content = base64_encode($request_content);
$time = time();

$stmt = $conn->prepare("INSERT INTO chats (userId, content, timestamp) VALUES (?, ?, ?)");
$stmt->bind_param("isi", $id, $content, $time);
$stmt->execute();
$stmt->close();

echo json_encode(["success" => true]);

$conn->close();