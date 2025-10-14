<?php
require __DIR__ . '/../incl/util.php';
setPlainHeader();
if (isAllowedDatabaseVersion(getClientVersion())) {
    if (getClientVersion() == "1.2-beta2" || getClientVersion() == "1.2") {
        require __DIR__ . '/berrydash/backported/1.2-beta2/registerAccount.php';
        exit;
    }
    if (
        getClientVersion() == "1.21" ||
        getClientVersion() == "1.3-beta1" ||
        getClientVersion() == "1.3-beta2" ||
        getClientVersion() == "1.3"
    ) {
        require __DIR__ . '/berrydash/backported/1.21/registerAccount.php';
        exit;
    }
    if (getClientVersion() == "1.5.0" || getClientVersion() == "1.5.1" || getClientVersion() == "1.5.2") {
        require __DIR__ . '/berrydash/backported/1.5/registerAccount.php';
        exit;
    }
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
        require __DIR__ . '/berrydash/backported/1.6/registerAccount.php';
        exit;
    }
}
setJsonHeader();
checkClientDatabaseVersion();
$conn = newConnection();

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';
$email = $_POST['email'] ?? '';

if (!preg_match('/^[a-zA-Z0-9]{3,16}$/', $username)) {
    exitWithMessage(jsonEncode(["success" => false, "message" => "Username must be 3-16 characters, letters and numbers only"], true), false);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    exitWithMessage(jsonEncode(["success" => false, "message" => "Email is invalid"], true), false);
}

if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d!@#$%^&*()_\-+=]{8,}$/', $password)) {
    exitWithMessage(jsonEncode(["success" => false, "message" => "Password must be at least 8 characters with at least one letter and one number"], true), false);
}

$stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
$stmt->bind_param("ss", $username, $email);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows > 0) {
    exitWithMessage(jsonEncode(["success" => false, "message" => "Username or email already taken"], true), false);
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

echo jsonEncode(["success" => true], true);
