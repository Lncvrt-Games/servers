<?php
require __DIR__ . '/../config/general.php';
require __DIR__ . '/../incl/util.php';
setPlainHeader();

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