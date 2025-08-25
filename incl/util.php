<?php
function setPlainHeader() {
    header("Content-Type: text/plain");
}

function setJsonHeader() {
    header("Content-Type: application/json");
}

function getIPAddress() {
    return $_SERVER['HTTP_CF_CONNECTING_IP'];
}

function newConnection() {
    include __DIR__.'/../config/connection.php';
    try {
        $conn = new mysqli($db_host, $db_user, $db_pass, $db_name, $db_port);
    } catch (mysqli_sql_exception $e) {
        exitWithMessage("-999");
    }
    return $conn;
}

function encrypt($plainText) {
    include __DIR__.'/../config/encryption.php';
    $iv = random_bytes(16);
    $cipher = openssl_encrypt($plainText, 'aes-256-cbc', $SERVER_SEND_TRANSFER_KEY, OPENSSL_RAW_DATA, $iv);
    return base64_encode($iv . $cipher);
}

function decrypt($dataB64) {
    include __DIR__.'/../config/encryption.php';
    $data = base64_decode($dataB64);
    $iv = substr($data, 0, 16);
    $cipher = substr($data, 16);
    $decrypted = openssl_decrypt($cipher, 'aes-256-cbc', $SERVER_RECEIVE_TRANSFER_KEY, OPENSSL_RAW_DATA, $iv);
    if ($decrypted === false) {
        exit(encrypt('-997'));
    }
    return $decrypted;
}

function exitWithMessage($message, $encrypt = true) {
    if ($encrypt === true) {
        echo encrypt($message);
    } else {
        echo $message;
    }
    exit;
}

function isLatestVersion($version) {
    global $latestVersion;
    if (!isset($latestVersion)) require __DIR__ . '/../config/general.php';
    return $version === $latestVersion;
}

function isBetaVersion($version) {
    global $latestBetaVersion;
    if (!isset($latestBetaVersion)) require __DIR__ . '/../config/general.php';
    return $version === $latestBetaVersion;
}

function isAllowedVersion($version) {
    global $allowedVersions;
    if (!isset($allowedVersions)) require __DIR__ . '/../config/general.php';
    return in_array($version, $allowedVersions);
}

function isAllowedDatabaseVersion($version) {
    global $allowedDatabaseVersions;
    if (!isset($allowedDatabaseVersions)) require __DIR__ . '/../config/general.php';
    return in_array($version, $allowedDatabaseVersions);
}

function checkClientDatabaseVersion() {
    global $allowedDatabaseVersions;
    if (!isset($allowedDatabaseVersions)) require __DIR__ . '/../config/general.php';
    if (!isset($_SERVER['HTTP_REQUESTER'])) exitWithMessage("-998");
    if ($_SERVER['HTTP_REQUESTER'] != "BerryDashClient") exitWithMessage("-998");
    if (!in_array($_SERVER['HTTP_CLIENTVERSION'] ?? '', $allowedDatabaseVersions)) exitWithMessage("-998");
}

function getPostData() {
    $raw = file_get_contents("php://input");
    parse_str($raw, $postData);

    $decrypted = [];
    foreach ($postData as $k => $v) {
        $decKey = decrypt($k);
        $decValue = decrypt($v);
        $decrypted[$decKey] = $decValue;
    }
    return $decrypted;
}

function uuidv4() {
    $data = random_bytes(16);
    $data[6] = chr((ord($data[6]) & 0x0f) | 0x40); 
    $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}