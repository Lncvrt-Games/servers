<?php
$conn = newConnection();

$post = getPostData();
$userId = (int)$post['userId'] ?? 0;
$sortBy = (int)$post['sortBy'] ?? 2;
$priceRangeEnabled = isset($post['priceRangeEnabled']) ? (string)$post['priceRangeEnabled'] == 'False' ? false : true : false;
$priceRangeMin = (int)$post['priceRangeMin'] ?? 10;
$priceRangeMax = (int)$post['priceRangeMax'] ?? 250;
$searchForEnabled = isset($post['searchForEnabled']) ? (string)$post['searchForEnabled'] == 'False' ? false : true : false;
$searchForValue = (string)$post['searchForValue'] ?? '';
$onlyShowEnabled = isset($post['onlyShowEnabled']) ? (string)$post['onlyShowEnabled'] == 'False' ? false : true : false;
$onlyShowValue = (string)$post['onlyShowValue'] ?? '';
$currentIcons = json_decode(base64_decode((string)($post['currentIcons'] ?? 'W10K')));

$where = ["u.banned = 0", "(c.state = 1 OR c.state = 2)"];
$params = [];
$types = "";
$order = match($sortBy) {
    1 => "ORDER BY c.price ASC",
    2 => "ORDER BY c.id ASC",
    3 => "ORDER BY c.id DESC",
    default => "ORDER BY c.price DESC",
};

if ($priceRangeEnabled) {
    $where[] = "c.price BETWEEN ? AND ?";
    $params[] = $priceRangeMin;
    $params[] = $priceRangeMax;
    $types .= "ii";
}

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
    SELECT c.data, u.username, u.id, c.price, c.name, c.uuid, c.state
    FROM marketplaceicons c
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

echo encrypt(jsonEncode(array_map(fn($row) => ['username' => $row['username'], 'userid' => $row['id'], 'data' => $row['data'], 'uuid' => $row['uuid'], 'price' => (int)$row['state'] == 2 ? 100000000 : $row['price'], 'name' => base64_decode($row['name'])], $result->fetch_all(MYSQLI_ASSOC))));

$conn->close();