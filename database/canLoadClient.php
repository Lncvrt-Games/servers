<?php
require __DIR__ . '/../config/general.php';
require __DIR__ . '/../incl/util.php';
setPlainHeader();

if (isset($_SERVER['HTTP_REQUESTER']) && $_SERVER['HTTP_REQUESTER'] === "BerryDashGodotClient") {
    exitWithMessage("all;1.0.0", false);
}

$clientVersion = $_SERVER['HTTP_CLIENTVERSION'] ?? "0";
if (($_SERVER['HTTP_REQUESTER'] ?? "0") != "BerryDashClient" && ($_SERVER['HTTP_USER_AGENT'] ?? "0") != "BerryDashClient" && ($clientVersion == "1.4.1" || $clientVersion == "1.4.0" || $clientVersion == "1.4.0-beta1")) {
    exitWithMessage("-1", false);
}
if (isAllowedVersion($clientVersion) && $clientVersion == "1.4.0-beta1") {
    exitWithMessage("1", false);
}
if (isLatestVersion($clientVersion)) {
    echo "1";
} else if (isBetaVersion($clientVersion)) {
    echo "4";
} else if (isAllowedVersion($clientVersion) && isAllowedDatabaseVersion($clientVersion)) {
    echo "2";
} else if (isAllowedVersion($clientVersion)) {
    echo "3";
} else {
    echo "-1";
}