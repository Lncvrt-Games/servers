<?php
require __DIR__ . '/../../incl/util.php';
setJsonHeader();
$conn = newConnection();

$stmt = $conn->prepare("SELECT * FROM launcherversions WHERE hidden = 0 ORDER BY id DESC");
$stmt->execute();
$result = $stmt->get_result();
$version_rows = $result->fetch_all(MYSQLI_ASSOC);
$stmt = $conn->prepare("SELECT * FROM launchercategories ORDER BY id DESC");
$stmt->execute();
$result = $stmt->get_result();
$category_rows = $result->fetch_all(MYSQLI_ASSOC);

$versions = [];
foreach ($version_rows as $row) {
    $versions[$row['id']] = ['version' => $row['version'], 'releaseDate' => $row['releaseDate'], 'displayName' => empty($row['displayName']) ? $row['version'] : $row['displayName'], 'platforms' => json_decode($row['platforms']), 'downloadUrls' => json_decode($row['downloadUrls']), 'executables' => json_decode($row['executables'])];
}

$categories = [];
foreach ($category_rows as $row) {
    $categories[$row['id']] = $row['name'];
}

echo json_encode(["version" => $versions, "categories" => $categories]);

$conn->close();