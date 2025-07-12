<?php
require __DIR__ . '/../incl/util.php';
setPlainHeader();
checkClientDatabaseVersion();
$conn = newConnection();

$stmt = $conn->prepare("
    SELECT c.id, c.content, u.username, u.icon, u.overlay, u.id, u.birdR, u.birdG, u.birdB, u.overlayR, u.overlayG, u.overlayB 
    FROM chats c 
    JOIN users u ON c.userId = u.id 
    WHERE u.banned = 0 AND c.deleted = 0 
    ORDER BY c.id ASC LIMIT 50
");
$stmt->execute();
$result = $stmt->get_result();

echo encrypt(json_encode(array_map(fn($row) => ['username' => $row['username'], 'userid' => $row['id'], 'content' => $row['content'], 'id' => $row['id'], 'icon' => $row['icon'], 'overlay' => $row['overlay'], 'birdColor' => [$row['birdR'], $row['birdG'], $row['birdB']], 'overlayColor' => [$row['overlayR'], $row['overlayG'], $row['overlayB']]], $result->fetch_all(MYSQLI_ASSOC)), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

$conn->close();