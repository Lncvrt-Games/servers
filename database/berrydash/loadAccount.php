<?php
require __DIR__ . '/../../incl/util.php';
setJsonHeader();
checkClientDatabaseVersion();

$token = $_POST['token'] ?? '';
$username = $_POST['username'] ?? '';

$conn = newConnection();

$stmt = $conn->prepare("SELECT * FROM users WHERE token = ? AND username = ?");
$stmt->bind_param("ss", $token, $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $savedata = json_decode($row['save_data'], true);
    $savedata['account']['id'] = $row['id'];
    $savedata['account']['name'] = $row['username'];
    $savedata['account']['session'] = $row['token'];
    echo jsonEncode([
        "success" => true,
        "data" => $savedata
    ], true);
} else {
    echo jsonEncode(["success" => false, "message" => "Invalid session token or username, please refresh login"], true);
}

$stmt->close();
$conn->close();