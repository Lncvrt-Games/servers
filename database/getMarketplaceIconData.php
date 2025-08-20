<?php
require __DIR__ . '/../incl/util.php';
setJsonHeader();
checkClientDatabaseVersion();

$post = getPostData();
$birdicon = $post['birdicon'] ?? '';

$conn = newConnection();

$stmt = $conn->prepare("SELECT data FROM marketplaceicons WHERE birdicon = ?");
$stmt->bind_param("s", $birdicon);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo encrypt(json_encode(["success" => true, "data" => $row['data']]));
} else {
    echo encrypt(json_encode(["success" => false, "message" => "Icon not found"]));
}

$stmt->close();
$conn->close();