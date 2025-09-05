<?php
require __DIR__ . '/../incl/util.php';
setPlainHeader();
if (isAllowedDatabaseVersion(getClientVersion())) {
    if (getClientVersion() == "1.3-beta1") {
        require __DIR__ . '/backported/1.3-beta1/changeAccountUsername.php';
        exit;
    }
}
checkClientDatabaseVersion();
$conn = newConnection();

$post = getPostData();
$oldusername = $post['oldusername'] ?? '';
$newusername = $post['newusername'] ?? '';
$token = $post['token'] ?? '';
$username = $post['username'] ?? '';

if (!preg_match('/^[a-zA-Z0-9]{3,16}$/', $newusername)) {
    exitWithMessage(json_encode(["success" => false, "message" => "New username must be 3-16 characters, letters and numbers only"]));
}

$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $newusername);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    exitWithMessage(json_encode(["success" => false, "message" => "New username already exists"]));
}

$stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND token = ?");
$stmt->bind_param("ss", $oldusername, $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    exitWithMessage(json_encode(["success" => false, "message" => "Invalid old username"]));
}

$stmt = $conn->prepare("UPDATE users SET username = ? WHERE username = ? AND token = ?");
$stmt->bind_param("sss", $newusername, $username, $token);
$stmt->execute();

if ($stmt->affected_rows === 0) {
    exitWithMessage(json_encode(["success" => false, "message" => "Invalid session token or username, please refresh login"]));
}

echo encrypt(json_encode(["success" => true]));