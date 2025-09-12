<?php
require __DIR__ . '/../incl/util.php';
setPlainHeader();
if (isAllowedDatabaseVersion(getClientVersion())) {
    if (getClientVersion() == "1.5.1" || getClientVersion() == "1.5.2") {
        require __DIR__ . '/backported/1.5.1/getChatroomMessages.php';
        exit;
    }
}
checkClientDatabaseVersion();
$conn = newConnection();

$stmt = $conn->prepare("
    SELECT c.id AS chat_id, c.content, c.deleted_at, u.id AS user_id, u.username, u.save_data
    FROM chats c
    JOIN users u ON c.userId = u.id
    WHERE u.banned = 0 AND c.deleted_at = 0 
    ORDER BY c.id DESC
    LIMIT 50
");
$stmt->execute();

$result = $stmt->get_result();
$rows = $result->fetch_all(MYSQLI_ASSOC);

$mapped = [];
$icons = [];
foreach ($rows as $row) {
    $savedata = json_decode($row['save_data'], true);

    $customIcon = $savedata['bird']['customIcon']['selected'] ?? null;

    if ($customIcon != null && strlen($customIcon) == 36 && $icons[$customIcon] == null) {
        $stmt = $conn->prepare("SELECT data FROM marketplaceicons WHERE uuid = ?");
        $stmt->bind_param("s", $customIcon);
        $stmt->execute();
        $result = $stmt->get_result();
        $rowData = $result->fetch_assoc();
        if ($rowData) {
            $stmt->close();
            $icons[$customIcon] = $rowData["data"];
        }
    }

    $mapped[] = [
        'username' => $row['username'],
        'userid' => $row['user_id'],
        'content' => (int)$row['deleted_at'] == 0 ? $row['content'] : null,
        'deleted' => (int)$row['deleted_at'] != 0,
        'id' => $row['chat_id'],
        'icon' => $savedata['bird']['icon'] ?? 1,
        'overlay' => $savedata['bird']['overlay'] ?? 0,
        'birdColor' => $savedata['settings']['colors']['icon'] ?? [255,255,255],
        'overlayColor' => $savedata['settings']['colors']['overlay'] ?? [255,255,255],
        'customIcon' => $customIcon,
    ];
}


if (getClientVersion() == "1.6") {
    echo encrypt(json_encode($mapped));
} else {
    echo encrypt(json_encode(["messages" => array_reverse($mapped), "customIcons" => $icons]));
}

$conn->close();