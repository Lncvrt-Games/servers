<?php
require __DIR__ . '/../incl/util.php';
setPlainHeader();
if ($_SERVER['HTTP_REQUESTER'] == 'BerryDashLauncher') {
    $request_type = '0';
} else {
    checkClientDatabaseVersion();
    $post = getPostData();
    $request_type = $post['type'] ?? '';
}
$conn = newConnection();

$request_value = "";
if ($request_type === "0") {
    $request_value = "highScore";
} else if ($request_type === "1")  {
    $request_value = match((int)$post['showType'] ?? 0) {
        1 => "totalPoisonBerries",
        2 => "totalSlowBerries",
        3 => "totalUltraBerries",
        4 => "totalSpeedyBerries",
        5 => "totalCoinBerries",
        default => "totalNormalBerries"
    };
} else {
    exitWithMessage(json_encode([]));
}

$stmt = $conn->prepare("SELECT username, id, save_data 
  FROM users 
  WHERE banned = 0 AND leaderboardsBanned = 0");
$stmt->execute();

$result = $stmt->get_result();
$rows = $result->fetch_all(MYSQLI_ASSOC);

$mapped = [];
foreach ($rows as $row) {
    $savedata = json_decode($row['save_data'], true);
    if (!$savedata) continue;

    $value = $savedata['gameStore'][$request_value] ?? 0;
    if ($value <= 0) continue;

    $mapped[] = [
        'username' => $row['username'],
        'userid' => $row['id'],
        'value' => $value,
        'icon' => $savedata['bird']['icon'] ?? 1,
        'overlay' => $savedata['bird']['overlay'] ?? 0,
        'birdColor' => $savedata['settings']['colors']['icon'] ?? [255,255,255],
        'overlayColor' => $savedata['settings']['colors']['overlay'] ?? [255,255,255],
    ];
}

usort($mapped, fn($a,$b) => $b['value'] <=> $a['value']);
$limited = array_slice($mapped, 0, 500);

echo encrypt(json_encode($limited));

$conn->close();