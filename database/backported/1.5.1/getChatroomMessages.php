<?php
$conn = newConnection();

$stmt = $conn->prepare("
    SELECT c.id AS chat_id, c.content, u.username, u.id AS user_id, u.save_data 
    FROM chats c
    JOIN users u ON c.userId = u.id
    WHERE u.banned = 0 AND c.deleted_at = 0 
    ORDER BY c.id DESC LIMIT 50
");
$stmt->execute();
$result = $stmt->get_result();

$rows = new stdClass();
while ($row = $result->fetch_assoc()) {
    $savedata = json_decode($row['save_data'], true);
    $icon = $savedata['bird']['icon'] ?? 1; 
    $overlay = $savedata['bird']['overlay'] ?? 0;   
    $birdColor = $savedata['settings']['colors']['icon'] ?? [255,255,255];
    $overlayColor = $savedata['settings']['colors']['overlay'] ?? [255,255,255];

    $rows[] = implode(";", [
        $row['chat_id'],
        base64_encode($row['username']),
        $row['content'],
        $icon,
        $overlay,
        $row['user_id'],
        $birdColor[0],
        $birdColor[1],
        $birdColor[2],
        $overlayColor[0],
        $overlayColor[1],
        $overlayColor[2]
    ]);
}

echo encrypt("1" . ":" . implode("|", array_reverse($rows)));

$conn->close();