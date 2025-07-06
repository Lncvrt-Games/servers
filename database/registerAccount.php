<?php
require __DIR__ . '/../incl/util.php';
setJsonHeader();
checkClientDatabaseVersion();
$conn = newConnection();

$postData = getPostData();
$request_username = $postData['username'] ?? '';
$request_password = $postData['password'] ?? '';
$request_email = $postData['email'] ?? '';

if (strlen($request_username) < 3 || strlen($request_username) > 16) {
    exitWithMessage(json_encode(["success" => false, "message" => "Username must be 3-16 characters, letters and numbers only"]));
}

if (!preg_match('/^[a-zA-Z0-9]{3,16}$/', $request_username)) {
    exitWithMessage(json_encode(["success" => false, "message" => "Username must be 3-16 characters, letters and numbers only"]));
}

if (!filter_var($request_email, FILTER_VALIDATE_EMAIL)) {
    exitWithMessage(json_encode(["success" => false, "message" => "Email is invalid"]));
}

//if (!preg_match('/^[a-zA-Z0-9!@#$%^&*()_\-+=]{3,16}$/', $request_password)) {
//    exitWithMessage(json_encode(["success" => false, "message" => "Password must have 8 characters, one number and one letter"]));
//}

$hashed_password = password_hash($request_password, PASSWORD_DEFAULT);
$game_session_token = bin2hex(random_bytes(256));

$stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
$stmt->bind_param("ss", $request_username, $request_email);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows > 0) {
    exitWithMessage(json_encode(["success" => false, "message" => "Username or email already taken"]));
}

$register_ip = getIPAddress();
$register_time = time();

$stmt = $conn->prepare("INSERT INTO users (game_session_token, username, password, email, register_time, latest_ip) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssis", $game_session_token, $request_username, $hashed_password, $request_email, $register_time, $register_ip);

$stmt->execute();
$stmt->close();
$conn->close();

echo encrypt(json_encode(["success" => true]));