<?php
$post = getPostData();
$token = $post['token'] ?? '';
$username = $post['username'] ?? '';
$savedata = $post['saveData'] ?? 'e30=';

try {
    $savedata = json_decode(base64_decode($savedata), true);
    $savedata['account']['id'] = null;
    $savedata['account']['name'] = null;
    $savedata['account']['session'] = null;
    $savedata = jsonEncode($savedata);
} catch (Exception $e) {
    echo encrypt(jsonEncode(["success" => false, "message" => "Couldn't parse save data"]));
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
    echo encrypt(jsonEncode(["success" => true]));
} else {
    echo encrypt(jsonEncode(["success" => false, "message" => "Invalid session token or username, please refresh login"]));
}

$stmt->close();
$conn->close();