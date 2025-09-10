<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require __DIR__ . '/../incl/util.php';
setPlainHeader();
checkClientDatabaseVersion();
$conn = newConnection();

$post = getPostData();
$targetId = (int)$post['targetId'] ?? 0;
$token = $post['token'] ?? '';
$username = $post['username'] ?? '';

$stmt = $conn->prepare("SELECT * FROM users WHERE token = ? AND username = ?");
$stmt->bind_param("ss", $token, $username);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
if (!$row) {
    echo encrypt(json_encode(["success" => false, "message" => 'User info not found']));
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

echo encrypt(json_encode(["success" => $success]));

$conn->close();