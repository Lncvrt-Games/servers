<?php
$conn = newConnection();

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';
$email = $_POST['email'] ?? '';

if (empty($username) || empty($password) || empty($email)) {
    exit("-2");
}

if (!preg_match('/^[a-zA-Z0-9]{3,16}$/', $username)) {
    exit("-3");
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    exit("-4");
}

if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d!@#$%^&*()_\-+=]{8,}$/', $password)) {
    exit("-5");
}

$stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
$stmt->bind_param("ss", $username, $email);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows > 0) {
    exit("-7");
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

echo '1';