<?php
require __DIR__ . '/../incl/util.php';
setPlainHeader();
checkClientDatabaseVersion();
$conn = newConnection();

$post = getPostData();
$type = (int)$post['type'] ?? -1;
$userId = (int)$post['userId'] ?? 0;
$sortBy = (int)$post['sortBy'] ?? 2;
$searchForEnabled = isset($post['searchForEnabled']) ? (string)$post['searchForEnabled'] == 'False' ? false : true : false;
$searchForValue = (string)$post['searchForValue'] ?? '';
$onlyShowEnabled = isset($post['onlyShowEnabled']) ? (string)$post['onlyShowEnabled'] == 'False' ? false : true : false;
$onlyShowValue = (string)$post['onlyShowValue'] ?? '';
$currentIcons = json_decode(base64_decode((string)($post['currentIcons'] ?? 'W10K')));

if ($type != 0 && $type != 1) {
    exit;
}

$where = ["u.banned = 0"];
$params = new stdClass();
$types = "";
$order = match($sortBy) {
    2 => "ORDER BY c.id ASC",
    3 => "ORDER BY c.id DESC",
    default => "",
};

if ($searchForEnabled && $searchForValue !== '') {
    $where[] = "FROM_BASE64(c.name) LIKE ?";
    $params[] = "%$searchForValue%";
    $types .= "s";
}

if ($onlyShowEnabled) {
    if ($onlyShowValue === '0') {
        $where[] = "c.userId = ?";
        $params[] = $userId;
        $types .= "i";
    } elseif ($onlyShowValue === '1') {
        $where[] = "c.userId != ?";
        $params[] = $userId;
        $types .= "i";
    } elseif ($onlyShowValue === '2') {
        $placeholders = implode(',', array_fill(0, count($currentIcons), '?'));
        $where[] = "c.uuid IN ($placeholders)";
        $params = array_merge($params, $currentIcons);
        $types .= str_repeat('s', count($currentIcons));
    } elseif ($onlyShowValue === '3') {
        $placeholders = implode(',', array_fill(0, count($currentIcons), '?'));
        $where[] = "c.uuid NOT IN ($placeholders)";
        $params = array_merge($params, $currentIcons);
        $types .= str_repeat('s', count($currentIcons));
    }
}

$sql = "
    SELECT c.data, u.username, u.id, c.name, c.uuid
    FROM presets c
    JOIN users u ON c.userId = u.id
    WHERE " . implode(" AND ", $where) . "
    $order
";

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

echo encrypt(json_encode(array_map(fn($row) => ['username' => $row['username'], 'userid' => $row['id'], 'data' => $row['data'], 'uuid' => $row['uuid'], 'name' => base64_decode($row['name'])], $result->fetch_all(MYSQLI_ASSOC))));

$conn->close();