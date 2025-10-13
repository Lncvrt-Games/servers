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
        require __DIR__ . '/backported/1.6/deleteAccountProfileMessage.php';
        exit;
    }
}
checkClientDatabaseVersion();
$conn = newConnection();

$targetId = (int)$_POST['targetId'] ?? 0;
$token = $_POST['token'] ?? '';
$username = $_POST['username'] ?? '';

$stmt = $conn->prepare("SELECT * FROM users WHERE token = ? AND username = ?");
$stmt->bind_param("ss", $token, $username);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
if (!$row) {
    echo json_encode(["success" => false, "message" => 'User info not found']);
    exit;
}
$stmt->close();

$user_id = $row["id"];
$time = time();

$stmt = $conn->prepare("UPDATE userposts SET deleted_at = ? WHERE id = ? AND userId = ? AND deleted_at = 0");
$stmt->bind_param("iii", $time, $targetId, $user_id);
$stmt->execute();

$success = $stmt->affected_rows > 0;
$stmt->close();

echo json_encode(["success" => $success]);

$conn->close();