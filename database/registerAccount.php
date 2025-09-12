<?php
require __DIR__ . '/../incl/util.php';
setPlainHeader();
if (isAllowedDatabaseVersion(getClientVersion())) {
    if (getClientVersion() == "1.2-beta2" || getClientVersion() == "1.2") {
        require __DIR__ . '/backported/1.2-beta2/registerAccount.php';
        exit;
    }
    if (
        getClientVersion() == "1.21" ||
        getClientVersion() == "1.3-beta1" ||
        getClientVersion() == "1.3-beta2"
    ) {
        require __DIR__ . '/backported/1.21/registerAccount.php';
        exit;
    }
    if (getClientVersion() == "1.5.0" || getClientVersion() == "1.5.1" || getClientVersion() == "1.5.2") {
        require __DIR__ . '/backported/1.5/registerAccount.php';
        exit;
    }
}
checkClientDatabaseVersion();
$conn = newConnection();

$post = getPostData();
$username = $post['username'] ?? '';
$password = $post['password'] ?? '';
$email = $post['email'] ?? '';

if (!preg_match('/^[a-zA-Z0-9]{3,16}$/', $username)) {
    exitWithMessage(json_encode(["success" => false, "message" => "Username must be 3-16 characters, letters and numbers only"]));
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    exitWithMessage(json_encode(["success" => false, "message" => "Email is invalid"]));
}

if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d!@#$%^&*()_\-+=]{8,}$/', $password)) {
    exitWithMessage(json_encode(["success" => false, "message" => "Password must be at least 8 characters with at least one letter and one number"]));
}

$stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
$stmt->bind_param("ss", $username, $email);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows > 0) {
    exitWithMessage(json_encode(["success" => false, "message" => "Username or email already taken"]));
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

echo encrypt(json_encode(["success" => true]));
