<?php
require __DIR__ . '/../incl/util.php';
setJsonHeader();
$conn = newConnection();

$stmt = $conn->prepare("SELECT username, highScore FROM users WHERE highScore != 0 AND banned = 0 AND leaderboardsBanned = 0 ORDER BY highScore DESC LIMIT 500");
$stmt->execute();

$result = $stmt->get_result();

echo json_encode(array_map(fn($row) => ['username' => $row['username'], 'score' => $row['highScore'], 'scoreFormatted' => number_format($row['highScore'])], $result->fetch_all(MYSQLI_ASSOC)), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

$conn->close();