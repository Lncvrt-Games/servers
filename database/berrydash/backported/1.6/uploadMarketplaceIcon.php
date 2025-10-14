<?php
$post = getPostData();
$token = $post['token'] ?? '';
$username = $post['username'] ?? '';
$price = (int)$post['price'] ?? 0;
$name = $post['name'] ?? '';
$name = base64_encode($name);
$filecontent = $post['filecontent'] ?? '';

if ($price < 10) exitWithMessage(jsonEncode(["success" => false, "message" => "Price cannot be be under 10 coins"]));
if (!preg_match('/^[a-zA-Z0-9 ]+$/', base64_decode($name))) exitWithMessage(jsonEncode(["success" => false, "message" => "Name is invalid"]));
if (!$filecontent) exitWithMessage(jsonEncode(["success" => false, "message" => "Invalid image uploaded"]));
$decoded = base64_decode($filecontent, true);
if (!$decoded) exitWithMessage(jsonEncode(["success" => false, "message" => "Invalid image uploaded"]));
if (strlen($decoded) > 1024 * 1024) exitWithMessage(jsonEncode(["success" => false, "message" => "File size exceeds 1 MB limit"]));
$info = getimagesizefromstring($decoded);
if (!$info) exitWithMessage(jsonEncode(["success" => false, "message" => "Invalid image uploaded"]));
if ($info[2] !== IMAGETYPE_PNG) exitWithMessage(jsonEncode(["success" => false, "message" => "Image must be a PNG"]));
if ($info[0] !== 128 || $info[1] !== 128) exitWithMessage(jsonEncode(["success" => false, "message" => "Invalid has to be 128x128"]));

$conn = newConnection();

$stmt = $conn->prepare("SELECT * FROM users WHERE token = ? AND username = ?");
$stmt->bind_param("ss", $token, $username);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
if (!$row) exitWithMessage(jsonEncode(["success" => false, "message" => "Invalid session token or username, please refresh login"]));
$stmt->close();

$id = $row["id"];
$time = time();
$hash = hash('sha512', base64_decode($filecontent));

$stmt = $conn->prepare("SELECT id FROM marketplaceicons WHERE hash = ?");
$stmt->bind_param("s", $hash);
$stmt->execute();
$result = $stmt->get_result();
if ($result->fetch_assoc()) {
    $stmt->close();
    exitWithMessage(jsonEncode(["success" => false, "message" => "This icon already exists in the marketplace"]));
}
$stmt->close();

$uuid = uuidv4();

$stmt = $conn->prepare("INSERT INTO marketplaceicons (uuid, userId, data, hash, price, name, timestamp) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sissisi", $uuid, $id, $filecontent, $hash, $price, $name, $time);
$stmt->execute();
$insertId = $conn->insert_id;
$stmt->close();

echo encrypt(jsonEncode(["success" => true, "message" => "Icon uploaded successfully! It will be reviewed and accepted or denied soon"]));

$conn->close();