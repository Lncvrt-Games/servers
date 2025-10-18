<?php
require __DIR__ . '/../../incl/util.php';
setJsonHeader();
checkClientDatabaseVersion();

$token = $_POST['token'] ?? '';
$username = $_POST['username'] ?? '';
$savedata = $_POST['saveData'] ?? 'e30=';

try {
    $savedata = json_decode(base64_decode($savedata), true);
    $savedata['account']['id'] = null;
    $savedata['account']['name'] = null;
    $savedata['account']['session'] = null;
    $savedata = jsonEncode($savedata);
} catch (Exception $e) {
    echo jsonEncode(["success" => false, "message" => "Couldn't parse save data"], true);
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
    echo jsonEncode(["success" => true], true);
} else {
    echo jsonEncode(["success" => false, "message" => "Invalid session token or username, please refresh login"], true);
}

$stmt->close();
$conn->close();