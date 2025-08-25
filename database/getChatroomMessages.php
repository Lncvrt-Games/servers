<?php
require __DIR__ . '/../incl/util.php';
setPlainHeader();
checkClientDatabaseVersion();
$conn = newConnection();

$stmt = $conn->prepare("
    SELECT c.id AS chat_id, c.content, c.deleted_at, u.id AS user_id, u.username, u.save_data
    FROM chats c
    JOIN users u ON c.userId = u.id
    WHERE u.banned = 0
    ORDER BY c.id DESC
    LIMIT 50
");
$stmt->execute();

$result = $stmt->get_result();
$rows = $result->fetch_all(MYSQLI_ASSOC);

$mapped = [];
foreach ($rows as $row) {
    $savedata = json_decode($row['save_data'], true);

    $mapped[] = [
        'username' => $row['username'],
        'userid' => $row['user_id'],
        'content' => (int)$row['deleted_at'] == 0 ? $row['content'] : null,
        'deleted' => (int)$row['deleted_at'] != 0,
        'id' => $row['chat_id'],
        'icon' => $savedata['bird']['icon'] ?? 1,
        'overlay' => $savedata['bird']['overlay'] ?? 0,
        'birdColor' => $savedata['settings']['colors']['icon'] ?? [255,255,255],
        'overlayColor' => $savedata['settings']['colors']['overlay'] ?? [255,255,255],
    ];
}

echo encrypt(json_encode(array_reverse($mapped)));

$conn->close();