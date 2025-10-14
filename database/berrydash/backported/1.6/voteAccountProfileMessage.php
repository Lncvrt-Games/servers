<?php
$conn = newConnection();

$post = getPostData();
$targetId = (int)$post['targetId'] ?? 0;
$liked = (int)$post['liked'] ?? -1;
$token = $post['token'] ?? '';
$username = $post['username'] ?? '';
if ($liked !== 0 && $liked !== 1) {
    echo encrypt(jsonEncode(["success" => false, "message" => 'Invalid type']));
    exit;
}

$stmt = $conn->prepare("SELECT * FROM users WHERE token = ? AND username = ?");
$stmt->bind_param("ss", $token, $username);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
if (!$row) {
    echo encrypt(jsonEncode(["success" => false, "message" => 'User info not found']));
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
    echo encrypt(jsonEncode(["success" => false, "message" => 'Post info not found']));
    exit;
}
$stmt->close();

$votes = json_decode($row["votes"], true) ?? [];
$likes = (int)$row["likes"];
if (isset($votes[$user_id])) {
    echo encrypt(jsonEncode(["success" => false, "message" => 'You have already voted']));
    exit;
}

$votes[$user_id] = $liked === 0 ? false : true;
$likes += $liked ? 1 : -1;
$votes = jsonEncode($votes);

$stmt = $conn->prepare("UPDATE userposts SET likes = ?, votes = ? WHERE id = ?");
$stmt->bind_param("isi", $likes, $votes, $targetId);
$stmt->execute();
$stmt->close();

echo encrypt(jsonEncode(["success" => true, "likes" => $likes]));

$conn->close();