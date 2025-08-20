<?php
require __DIR__ . '/../../incl/util.php';
setJsonHeader();
$conn = newConnection();

$stmt = $conn->prepare("SELECT * FROM launcherversions ORDER BY id DESC");
$stmt->execute();

$result = $stmt->get_result();

echo json_encode(array_map(fn($row) => ['id' => $row['id'], 'version' => $row['version'], 'releaseDate' => $row['releaseDate'], 'displayName' => empty($row['displayName']) ? $row['version'] : $row['displayName'], 'platforms' => json_decode($row['platforms']), 'downloadUrls' => json_decode($row['downloadUrls']), 'executables' => json_decode($row['executables'])], $result->fetch_all(MYSQLI_ASSOC)));

$conn->close();