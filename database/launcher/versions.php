<?php
require __DIR__ . '/../../incl/util.php';
setJsonHeader();
$conn = newConnection();

$stmt = $conn->prepare("SELECT * FROM launcherversions WHERE hidden = 0 ORDER BY id DESC");
$stmt->execute();

$result_versions = $stmt->get_result();
$versions = array_map(fn($row) => ['id' => $row['id'], 'version' => $row['version'], 'releaseDate' => $row['releaseDate'], 'displayName' => empty($row['displayName']) ? $row['version'] : $row['displayName'], 'platforms' => json_decode($row['platforms']), 'downloadUrls' => json_decode($row['downloadUrls']), 'executables' => json_decode($row['executables']), 'category' => $row['category']], $result_versions->fetch_all(MYSQLI_ASSOC));

$stmt = $conn->prepare("SELECT * FROM launchercategories ORDER BY id DESC");
$stmt->execute();

$result_categories = $stmt->get_result();
$rows = $result_categories->fetch_all(MYSQLI_ASSOC);

$categories = [];
foreach ($rows as $row) {
    $categories[$row['id']] = $row['name'];
}

echo jsonEncode(["versions" => $versions, "categories" => $categories], true);

$conn->close();