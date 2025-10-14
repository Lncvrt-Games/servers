<?php
require __DIR__ . '/../../incl/util.php';
setPlainHeader();
if (isAllowedDatabaseVersion(getClientVersion())) {
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
        require __DIR__ . '/backported/1.6/getAccountProfile.php';
        exit;
    }
}
setJsonHeader();
checkClientDatabaseVersion();

$uesrId = $_POST['uesrId'] ?? '';

$conn = newConnection();

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $uesrId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $savedata = json_decode($row['save_data'], true);
    $custom = null;
    if (isset($savedata['bird']['customIcon']['selected'])) {
        $selected = $savedata['bird']['customIcon']['selected'];
        foreach ($savedata['bird']['customIcon']['data'] as $entry) {
            if (isset($entry['uuid']) && $entry['uuid'] === $selected) {
                $custom = $entry['data'];
                break;
            }
        }
    }
    echo jsonEncode([
        "success" => true,
        "totalNormalBerries" => $savedata['gameStore']['totalNormalBerries'] ?? 0,
        "totalPoisonBerries" => $savedata['gameStore']['totalPoisonBerries'] ?? 0,
        "totalSlowBerries" => $savedata['gameStore']['totalSlowBerries'] ?? 0,
        "totalUltraBerries" => $savedata['gameStore']['totalUltraBerries'] ?? 0,
        "totalSpeedyBerries" => $savedata['gameStore']['totalSpeedyBerries'] ?? 0,
        "totalCoinBerries" => $savedata['gameStore']['totalCoinBerries'] ?? 0,
        "totalRandomBerries" => $savedata['gameStore']['totalRandomBerries'] ?? 0,
        "totalAntiBerries" => $savedata['gameStore']['totalAntiBerries'] ?? 0,
        "coins" => $savedata['bird']['customIcon']['balance'] ?? 0,
        "name" => $row['username'],
        "icon" => $savedata['bird']['icon'] ?? 1,
        "overlay" => $savedata['bird']['overlay'] ?? 0,
        "customIcon" => $custom,
        "playerIconColor" => $savedata['settings']['colors']['icon'] ?? [255,255,255],
        "playerOverlayColor" => $savedata['settings']['colors']['overlay'] ?? [255,255,255]
    ], true);
} else {
    echo jsonEncode(["success" => false], true);
}

$stmt->close();
$conn->close();