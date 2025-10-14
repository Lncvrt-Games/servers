<?php
$conn = newConnection();

$post = getPostData();
$username = $post['username'] ?? '';
$password = $post['password'] ?? '';
$email = $post['email'] ?? '';

if (!preg_match('/^[a-zA-Z0-9]{3,16}$/', $username)) {
    exitWithMessage(jsonEncode(["success" => false, "message" => "Username must be 3-16 characters, letters and numbers only"]));
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    exitWithMessage(jsonEncode(["success" => false, "message" => "Email is invalid"]));
}

if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d!@#$%^&*()_\-+=]{8,}$/', $password)) {
    exitWithMessage(jsonEncode(["success" => false, "message" => "Password must be at least 8 characters with at least one letter and one number"]));
}

$stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
$stmt->bind_param("ss", $username, $email);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows > 0) {
    exitWithMessage(jsonEncode(["success" => false, "message" => "Username or email already taken"]));
}

$hashed = password_hash($password, PASSWORD_DEFAULT);
$token = bin2hex(random_bytes(256));
$ip = getIPAddress();
$time = time();

$stmt = $conn->prepare("INSERT INTO users (token, username, password, email, register_time, latest_ip) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssis", $token, $username, $hashed, $email, $time, $ip);
$stmt->execute();

$stmt->close();
$conn->close();

echo encrypt(jsonEncode(["success" => true]));
