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

function getClientVersion() {
    return $_SERVER['HTTP_CLIENTVERSION'];
}

function exitWithMessage($message) {
    echo $message;
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

function uuidv4() {
    $data = random_bytes(16);
    $data[6] = chr((ord($data[6]) & 0x0f) | 0x40); 
    $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

function genTimestamp($time) {
    $time = time() - $time;
    $time = ($time < 1) ? 1 : $time;
    $tokens = array (31536000 => 'year', 2592000 => 'month', 604800 => 'week', 86400 => 'day', 3600 => 'hour', 60 => 'minute', 1 => 'second');
    foreach($tokens as $unit => $text) {
        if($time < $unit) continue;
        $numberOfUnits = floor($time / $unit);
        return $numberOfUnits . ' ' . $text . (($numberOfUnits > 1) ? 's' : '');
    }
}

function jsonEncode($data, $format = false)
{
    return json_encode($data, $format ? JSON_PRETTY_PRINT : 0);
}
