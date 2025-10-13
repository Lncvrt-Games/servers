<?php
require __DIR__ . '/../../incl/util.php';
setPlainHeader();
if (isAllowedDatabaseVersion(getClientVersion())) {
    if (
        getClientVersion() == "1.6" ||
        getClientVersion() == "1.6.1" ||
        getClientVersion() == "1.6.2" ||
        getClientVersion() == "1.6.3" ||
        getClientVersion() == "1.7" ||
        getClientVersion() == "1.7.1" ||
        getClientVersion() == "1.8" ||
        getClientVersion() == "1.8.1" ||
        getClientVersion() == "1.8.2"
    ) {
        require __DIR__ . '/backported/1.6/voteAccountProfileMessage.php';
        exit;
    }
}
checkClientDatabaseVersion();
$conn = newConnection();

$targetId = (int)$_POST['targetId'] ?? 0;
$liked = (int)$_POST['liked'] ?? -1;
$token = $_POST['token'] ?? '';
$username = $_POST['username'] ?? '';
if ($liked !== 0 && $liked !== 1) {
    echo json_encode(["success" => false, "message" => 'Invalid type']);
    exit;
}

$stmt = $conn->prepare("SELECT * FROM users WHERE token = ? AND username = ?");
$stmt->bind_param("ss", $token, $username);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
if (!$row) {
    echo json_encode(["success" => false, "message" => 'User info not found']);
    exit;
}
$stmt->close();

$user_id = $row["id"];

$stmt = $conn->prepare("SELECT votes, likes FROM userposts WHERE id = ?");
$stmt->bind_param("i", $targetId);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
if (!$row) {
    echo json_encode(["success" => false, "message" => 'Post info not found']);
    exit;
}
$stmt->close();

$votes = json_decode($row["votes"], true) ?? [];
$likes = (int)$row["likes"];
if (isset($votes[$user_id])) {
    echo json_encode(["success" => false, "message" => 'You have already voted']);
    exit;
}

$votes[$user_id] = $liked === 0 ? false : true;
$likes += $liked ? 1 : -1;
$votes = json_encode($votes);

$stmt = $conn->prepare("UPDATE userposts SET likes = ?, votes = ? WHERE id = ?");
$stmt->bind_param("isi", $likes, $votes, $targetId);
$stmt->execute();
$stmt->close();

echo json_encode(["success" => true, "likes" => $likes]);

$conn->close();