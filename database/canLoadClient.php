<?php
require __DIR__ . '/../config/general.php';
require __DIR__ . '/../incl/util.php';
setPlainHeader();

function isSupportedVersion($version) {
    global $latestVersion;
    if (!isset($latestVersion)) require __DIR__ . '/../config/general.php';
    return $version === $latestVersion || $version === $latestBetaVersion;
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

$clientVersion = $_SERVER['HTTP_CLIENTVERSION'] ?? "0";
if (($_SERVER['HTTP_REQUESTER'] ?? "0") != "BerryDashClient") {
    exitWithMessage("-1", false);
}
if (isSupportedVersion($clientVersion)) {
    echo "1";
} else if (isAllowedVersion($clientVersion)) {
    echo "3";
} else if (isAllowedVersion($clientVersion) && isAllowedDatabaseVersion($clientVersion)) {
    echo "2";
} else {
    echo "-1";
}