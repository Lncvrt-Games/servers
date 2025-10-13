<?php
$post = getPostData();
$token = $post['token'] ?? '';
$username = $post['username'] ?? '';

$conn = newConnection();

$stmt = $conn->prepare("SELECT * FROM users WHERE token = ? AND username = ?");
$stmt->bind_param("ss", $token, $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $savedata = json_decode($row['save_data'], true);
    $savedata['account']['id'] = $row['id'];
    $savedata['account']['name'] = $row['username'];
    $savedata['account']['session'] = $row['token'];
    echo encrypt(json_encode([
        "success" => true,
        "data" => $savedata
    ]));
} else {
    echo encrypt(json_encode(["success" => false, "message" => "Invalid session token or username, please refresh login"]));
}

$stmt->close();
$conn->close();