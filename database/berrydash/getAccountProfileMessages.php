<?php
require __DIR__ . '/../../incl/util.php';
setJsonHeader();
checkClientDatabaseVersion();
$conn = newConnection();

$targetId = (int)$_POST['targetId'] ?? 0;

$stmt = $conn->prepare("
    SELECT p.id, p.content, p.timestamp, p.likes, u.id as userId 
    FROM userposts p 
    JOIN users u ON p.userId = u.id 
    WHERE u.id = ? AND p.deleted_at = 0 
    ORDER BY p.id DESC
");
$stmt->bind_param("i", $targetId);
$stmt->execute();
$result = $stmt->get_result();

echo jsonEncode(array_map(fn($row) => ['id' => $row['id'], 'userId' => $row['userId'], 'content' => $row['content'], 'timestamp' => genTimestamp($row['timestamp']) . " ago", 'likes' => $row['likes']], $result->fetch_all(MYSQLI_ASSOC)), true);

$conn->close();