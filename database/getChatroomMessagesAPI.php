<?php
require __DIR__ . '/../incl/util.php';
setJsonHeader();
$conn = newConnection();

$stmt = $conn->prepare("
    SELECT c.id, c.content, u.username 
    FROM chats c 
    JOIN users u ON c.userId = u.id 
    WHERE u.banned = 0 AND c.deleted_at = 0 
    ORDER BY c.id ASC LIMIT 50
");
$stmt->execute();
$result = $stmt->get_result();

echo json_encode(array_map(fn($row) => ['username' => $row['username'], 'content' => base64_decode($row['content']), 'contentRaw' => $row['content'], 'messageId' => $row['id']], $result->fetch_all(MYSQLI_ASSOC)), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

$conn->close();