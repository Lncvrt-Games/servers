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
        require __DIR__ . '/backported/1.2-beta2/loginAccount.php';
        exit;
    }
    if (getClientVersion() == "1.3-beta2" || getClientVersion() == "1.3" || getClientVersion() == "1.33") {
        require __DIR__ . '/backported/1.3-beta2/loginAccount.php';
        exit;
    }
    if (getClientVersion() == "1.5.0" || getClientVersion() == "1.5.1" || getClientVersion() == "1.5.2") {
        require __DIR__ . '/backported/1.5/loginAccount.php';
        exit;
    }
}
checkClientDatabaseVersion();
$conn = newConnection();

$post = getPostData();
$username = $post['username'];
$password = $post['password'];

$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    exitWithMessage(json_encode(["success" => false, "message" => "Invalid username or password"]));
}

$user = $result->fetch_assoc();

if (!password_verify($password, $user["password"])) {
    exitWithMessage(json_encode(["success" => false, "message" => "Invalid username or password"]));
}

$id = $user['id'];
$token = $user['token'];
$ip = getIPAddress();

$stmt = $conn->prepare("UPDATE users SET latest_ip = ?, token = ? WHERE id = ?");
$stmt->bind_param("ssi", $ip, $token, $id);
$stmt->execute();

$data = ["session" => $token, "username" => $user['username'], "userid" => $id];

echo encrypt(json_encode(["success" => true, "data" => $data]));

$stmt->close();
$conn->close();
