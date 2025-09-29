<?php
require __DIR__ . '/../incl/util.php';
setJsonHeader();
$conn = newConnection();

$stmt = $conn->prepare("SELECT username, id, save_data 
  FROM users 
  WHERE banned = 0 AND leaderboardsBanned = 0");
$stmt->execute();

$result = $stmt->get_result();
$rows = $result->fetch_all(MYSQLI_ASSOC);

$mapped = new stdClass();
foreach ($rows as $row) {
    $savedata = json_decode($row['save_data'], true);
    if (!$savedata) continue;

    $value = $savedata['gameStore']['highScore'] ?? 0;
    if ($value <= 0) continue;

    $mapped[] = [
        'username' => $row['username'],
        'score' => (int)$value,
        'scoreFormatted' => number_format($value)
    ];
}

usort($mapped, fn($a,$b) => $b['score'] <=> $a['score']);
$limited = array_slice($mapped, 0, 500);

echo json_encode($limited);

$conn->close();