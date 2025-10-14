<?php
require __DIR__ . '/../incl/util.php';
setPlainHeader();
if (isAllowedDatabaseVersion(getClientVersion())) {
    if (
        getClientVersion() == "1.2-beta2" ||
        getClientVersion() == "1.2" ||
        getClientVersion() == "1.21" ||
        getClientVersion() == "1.3-beta1"
    ) {
        require __DIR__ . '/berrydash/backported/1.2-beta2/loginAccount.php';
        exit;
    }
    if (getClientVersion() == "1.3-beta2" || getClientVersion() == "1.3" || getClientVersion() == "1.33") {
        require __DIR__ . '/berrydash/backported/1.3-beta2/loginAccount.php';
        exit;
    }
    if (getClientVersion() == "1.4.0-beta1") {
        require __DIR__ . '/berrydash/backported/1.4.0-beta1/loginAccount.php';
        exit;
    }
    if (getClientVersion() == "1.5.0" || getClientVersion() == "1.5.1" || getClientVersion() == "1.5.2") {
        require __DIR__ . '/berrydash/backported/1.5/loginAccount.php';
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
        require __DIR__ . '/berrydash/backported/1.6/loginAccount.php';
        exit;
    }
}
setJsonHeader();
checkClientDatabaseVersion();
$conn = newConnection();

$username = $_POST['username'];
$password = $_POST['password'];

$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    exitWithMessage(jsonEncode(["success" => false, "message" => "Invalid username or password"], true), false);
}

$user = $result->fetch_assoc();

if (!password_verify($password, $user["password"])) {
    exitWithMessage(jsonEncode(["success" => false, "message" => "Invalid username or password"], true), false);
}

$id = $user['id'];
$token = $user['token'];
$ip = getIPAddress();

$stmt = $conn->prepare("UPDATE users SET latest_ip = ?, token = ? WHERE id = ?");
$stmt->bind_param("ssi", $ip, $token, $id);
$stmt->execute();

$data = ["session" => $token, "username" => $user['username'], "userid" => $id];

echo jsonEncode(["success" => true, "data" => $data], true);

$stmt->close();
$conn->close();
