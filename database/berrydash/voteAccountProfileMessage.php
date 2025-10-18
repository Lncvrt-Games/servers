<?php
require __DIR__ . '/../../incl/util.php';
setJsonHeader();
checkClientDatabaseVersion();
$conn = newConnection();

$targetId = (int)$_POST['targetId'] ?? 0;
$liked = (int)$_POST['liked'] ?? -1;
$token = $_POST['token'] ?? '';
$username = $_POST['username'] ?? '';
if ($liked !== 0 && $liked !== 1) {
    echo jsonEncode(["success" => false, "message" => 'Invalid type'], true);
    exit;
}

$stmt = $conn->prepare("SELECT * FROM users WHERE token = ? AND username = ?");
$stmt->bind_param("ss", $token, $username);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
if (!$row) {
    echo jsonEncode(["success" => false, "message" => 'User info not found'], true);
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
    echo jsonEncode(["success" => false, "message" => 'Post info not found'], true);
    exit;
}
$stmt->close();

$votes = json_decode($row["votes"], true) ?? [];
$likes = (int)$row["likes"];
if (isset($votes[$user_id])) {
    echo jsonEncode(["success" => false, "message" => 'You have already voted'], true);
    exit;
}

$votes[$user_id] = $liked === 0 ? false : true;
$likes += $liked ? 1 : -1;
$votes = jsonEncode($votes);

$stmt = $conn->prepare("UPDATE userposts SET likes = ?, votes = ? WHERE id = ?");
$stmt->bind_param("isi", $likes, $votes, $targetId);
$stmt->execute();
$stmt->close();

echo jsonEncode(["success" => true, "likes" => $likes], true);

$conn->close();