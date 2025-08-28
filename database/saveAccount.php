<?php
require __DIR__ . '/../incl/util.php';
setPlainHeader();
if (isAllowedDatabaseVersion(getClientVersion())) {
    if (getClientVersion() == "1.2-beta2") {
        require __DIR__ . '/backported/1.2-beta2/syncAccount.php';
        exit;
    }
}
checkClientDatabaseVersion();

$post = getPostData();
$token = $post['token'] ?? '';
$username = $post['username'] ?? '';
$savedata = $post['saveData'] ?? 'e30=';

try {
    $savedata = json_decode(base64_decode($savedata), true);
    $savedata['account']['id'] = null;
    $savedata['account']['name'] = null;
    $savedata['account']['session'] = null;
    $savedata = json_encode($savedata);
} catch (Exception $e) {
    echo encrypt(json_encode(["success" => false, "message" => "Couldn't parse save data"]));
}

$conn = newConnection();

$stmt = $conn->prepare("SELECT * FROM users WHERE token = ? AND username = ?");
$stmt->bind_param("ss", $token, $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $updateStmt = $conn->prepare("UPDATE users SET save_data = ? WHERE token = ? AND username = ?");
    $updateStmt->bind_param("sss", $savedata, $token, $username);
    $updateStmt->execute();
    $updateStmt->close();
    echo encrypt(json_encode(["success" => true]));
} else {
    echo encrypt(json_encode(["success" => false, "message" => "Invalid session token or username, please refresh login"]));
}

$stmt->close();
$conn->close();