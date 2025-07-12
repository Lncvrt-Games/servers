<?php
require __DIR__ . '/../incl/util.php';
setJsonHeader();
checkClientDatabaseVersion();
$conn = newConnection();

$post = getPostData();
$oldpassword = $post['oldpassword'] ?? '';
$newpassword = $post['newpassword'] ?? '';
$token = $post['token'] ?? '';
$username = $post['username'] ?? '';

if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d!@#$%^&*()_\-+=]{8,}$/', $newpassword)) {
    exitWithMessage(json_encode(["success" => false, "message" => "New password must be at least 8 characters with at least one letter and one number"]));
}

$stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND token = ?");
$stmt->bind_param("ss", $username, $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    if (!password_verify($oldpassword, $user['password'])) {
        exitWithMessage(json_encode(["success" => false, "message" => "Old password is incorrect"]));
    }
    if (password_verify($newpassword, $user['password'])) {
        exitWithMessage(json_encode(["success" => false, "message" => "New password cannot be the same as the old password"]));
    }
    $id = $user['id'];
} else {
    exitWithMessage(json_encode(["success" => false, "message" => "Invalid session token or username, please refresh login"]));
}

$hashednewpassword = password_hash($newpassword, PASSWORD_DEFAULT);
$token = bin2hex(random_bytes(256));

$stmt = $conn->prepare("UPDATE users SET token = ?, password = ? WHERE id = ?");
$stmt->bind_param("sss", $token, $hashednewpassword, $id);

$stmt->execute();
$stmt->close();
$conn->close();

echo encrypt(json_encode(["success" => true, "token" => $token]));