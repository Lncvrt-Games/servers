<?php
$post = getPostData();
$uesrId = $post['uesrId'] ?? '';

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
    echo encrypt(json_encode([
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
    ]));
} else {
    echo encrypt(json_encode(["success" => false]));
}

$stmt->close();
$conn->close();