<?php
require __DIR__ . '/../incl/util.php';
setPlainHeader();
checkClientDatabaseVersion();
$conn = newConnection();

$stmt = $conn->prepare("
    SELECT c.id AS chat_id, c.content, u.username, u.icon, u.overlay, u.id AS user_id, u.birdColor, u.overlayColor 
    FROM chats c 
    JOIN users u ON c.userId = u.id 
    WHERE u.banned = 0 AND c.deleted = 0 
    ORDER BY c.id DESC LIMIT 50
");
$stmt->execute();
$result = $stmt->get_result();

echo encrypt(json_encode(array_reverse(array_map(fn($row) => ['username' => $row['username'], 'userid' => $row['user_id'], 'content' => $row['content'], 'id' => $row['chat_id'], 'icon' => $row['icon'], 'overlay' => $row['overlay'], 'birdColor' => json_decode($row['birdColor']), 'overlayColor' => json_decode($row['overlayColor'])], $result->fetch_all(MYSQLI_ASSOC)))));

$conn->close();