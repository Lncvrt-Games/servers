<?php
require __DIR__ . '/../../incl/util.php';
setJsonHeader();
if (getClientVersion() != "1.1.1" && $_SERVER['HTTP_REQUESTER'] != "BerryDashLauncher") {
    checkClientDatabaseVersion();
}

$request_type = $_POST['type'] ?? '';
$conn = newConnection();

$request_value = "";
if ($request_type === "0") {
    $request_value = "highScore";
} else if ($request_type === "1")  {
    $request_value = match((int)$_POST['showType'] ?? 0) {
        1 => "totalPoisonBerries",
        2 => "totalSlowBerries",
        3 => "totalUltraBerries",
        4 => "totalSpeedyBerries",
        5 => "totalCoinBerries",
        6 => "totalRandomBerries",
        7 => "totalAntiBerries",
        default => "totalNormalBerries"
    };
} else if ($request_type !== "2" && $request_type !== "3" && $request_type !== "4")  {
    exitWithMessage(jsonEncode([]));
}

$stmt = $conn->prepare("SELECT username, id, save_data, legacy_high_score 
  FROM users 
  WHERE banned = 0 AND leaderboardsBanned = 0");
$stmt->execute();

$result = $stmt->get_result();
$rows = $result->fetch_all(MYSQLI_ASSOC);

$mapped = [];
$icons = [];
foreach ($rows as $row) {
    $savedata = json_decode($row['save_data'], true);
    if (!$savedata) continue;

    if ($request_type == "4") {
        $berries = ["totalNormalBerries", "totalPoisonBerries", "totalSlowBerries", "totalUltraBerries", "totalSpeedyBerries", "totalCoinBerries", "totalRandomBerries", "totalAntiBerries"];
        $value = 0;
        foreach ($berries as $b) $value += (int)($savedata['gameStore'][$b] ?? 0);
    } else {
        $value = $request_type != 2 ? $request_type != 3 ? ($savedata['gameStore'][$request_value] ?? 0) : ($row['legacy_high_score'] ?? 0) : ($savedata['bird']['customIcon']['balance'] ?? 0);
    }
    if ($value <= 0) continue;

    $customIcon = $savedata['bird']['customIcon']['selected'] ?? null;

    if ($customIcon != null && strlen($customIcon) == 36 && $icons[$customIcon] == null) {
        $stmt = $conn->prepare("SELECT data FROM marketplaceicons WHERE uuid = ?");
        $stmt->bind_param("s", $customIcon);
        $stmt->execute();
        $result = $stmt->get_result();
        $rowData = $result->fetch_assoc();
        if ($rowData) {
            $stmt->close();
            $icons[$customIcon] = $rowData["data"];
        }
    }

    $mapped[] = [
        'username' => $row['username'],
        'userid' => $row['id'],
        'value' => $value,
        'icon' => $savedata['bird']['icon'] ?? 1,
        'overlay' => $savedata['bird']['overlay'] ?? 0,
        'birdColor' => $savedata['settings']['colors']['icon'] ?? [255,255,255],
        'overlayColor' => $savedata['settings']['colors']['overlay'] ?? [255,255,255],
        'customIcon' => $customIcon
    ];
}

usort($mapped, fn($a,$b) => $b['value'] <=> $a['value']);
$limited = array_slice($mapped, 0, 500);

if (getClientVersion() == "1.6" || (getClientVersion() == "1.6.1" && $request_type == "1")) {
    echo jsonEncode($limited, true);
} else {
    echo jsonEncode(["entries" => $limited, "customIcons" => $icons == [] ? new stdClass() : $icons], true);
}

$conn->close();