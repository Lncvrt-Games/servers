<?php
require __DIR__ . '/../incl/util.php';
setJsonHeader();
checkClientDatabaseVersion();
$conn = newConnection();

$post = getPostData();
$request_type = $post['type'] ?? '';

$request_value = "";
if ($request_type === "0") {
    $request_value = "highScore";
} else if ($request_type === "1")  {
    $request_value = match((int)$post['showType'] ?? 0) {
        1 => "totalPoisonBerries",
        2 => "totalSlowBerries",
        3 => "totalUltraBerries",
        4 => "totalSpeedyBerries",
        default => "totalNormalBerries"
    };
} else {
    exitWithMessage(json_encode([]));
}

$stmt = $conn->prepare("SELECT username, `$request_value`, icon, overlay, id, birdColor, overlayColor FROM users WHERE `$request_value` != 0 AND banned = 0 AND leaderboardsBanned = 0 ORDER BY `$request_value` DESC LIMIT 500");
$stmt->execute();

$result = $stmt->get_result();

echo encrypt(json_encode(array_map(fn($row) => ['username' => $row['username'], 'userid' => $row['id'], 'value' => $row[$request_value], 'icon' => $row['icon'], 'overlay' => $row['overlay'], 'birdColor' => json_decode($row['birdColor']), 'overlayColor' => json_decode($row['overlayColor'])], $result->fetch_all(MYSQLI_ASSOC)), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

$conn->close();