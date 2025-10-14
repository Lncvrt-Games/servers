<?php
require __DIR__ . '/../../incl/util.php';
setPlainHeader();
if (isAllowedDatabaseVersion(getClientVersion())) {
    if (
        getClientVersion() == "1.2-beta2" ||
        getClientVersion() == "1.2" ||
        getClientVersion() == "1.21" ||
        getClientVersion() == "1.3-beta1" ||
        getClientVersion() == "1.3-beta2" ||
        getClientVersion() == "1.3" ||
        getClientVersion() == "1.33"
    ) {
        require __DIR__ . '/backported/1.2-beta2/syncAccount.php';
        exit;
    }
    if (getClientVersion() == "1.4.0-beta1") {
        require __DIR__ . '/backported/1.4.0-beta1/saveAccount.php';
        exit;
    }
    if (getClientVersion() == "1.5.0" || getClientVersion() == "1.5.1" || getClientVersion() == "1.5.2") {
        require __DIR__ . '/backported/1.5/saveAccount.php';
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
        require __DIR__ . '/backported/1.6/saveAccount.php';
        exit;
    }
}
setJsonHeader();
checkClientDatabaseVersion();

$token = $_POST['token'] ?? '';
$username = $_POST['username'] ?? '';
$savedata = $_POST['saveData'] ?? 'e30=';

try {
    $savedata = json_decode(base64_decode($savedata), true);
    $savedata['account']['id'] = null;
    $savedata['account']['name'] = null;
    $savedata['account']['session'] = null;
    $savedata = jsonEncode($savedata);
} catch (Exception $e) {
    echo jsonEncode(["success" => false, "message" => "Couldn't parse save data"], true);
}

$conn = newConnection();

$stmt = $conn->prepare("SELECT * FROM users WHERE token = ? AND username = ?");
$stmt->bind_param("ss", $token, $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $updateStmt = $conn->prepare("UPDATE users SET save_data = ? WHERE token = ? AND username = ?");
    $updateStmt->bind_param("sss", $savedata, $token, $username);
    $updateStmt->execute();
    $updateStmt->close();
    echo jsonEncode(["success" => true], true);
} else {
    echo jsonEncode(["success" => false, "message" => "Invalid session token or username, please refresh login"], true);
}

$stmt->close();
$conn->close();