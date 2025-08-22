<?php
require __DIR__ . '/../incl/util.php';
setPlainHeader();
checkClientDatabaseVersion();
$conn = newConnection();

$stmt = $conn->prepare("
    SELECT c.data, u.username, u.id, c.price, c.name, c.uuid 
    FROM marketplaceicons c 
    JOIN users u ON c.userId = u.id 
    WHERE u.banned = 0 AND c.state = 1 
    ORDER BY c.id ASC
");
$stmt->execute();
$result = $stmt->get_result();

echo encrypt(json_encode(array_map(fn($row) => ['username' => $row['username'], 'userid' => $row['id'], 'data' => $row['data'], 'uuid' => $row['uuid'], 'price' => $row['price'], 'name' => base64_decode($row['name'])], $result->fetch_all(MYSQLI_ASSOC))));

$conn->close();