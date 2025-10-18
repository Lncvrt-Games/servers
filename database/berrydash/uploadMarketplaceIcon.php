<?php
require __DIR__ . '/../../incl/util.php';
setJsonHeader();
checkClientDatabaseVersion();

$token = $_POST['token'] ?? '';
$username = $_POST['username'] ?? '';
$price = (int)$_POST['price'] ?? 0;
$name = $_POST['name'] ?? '';
$name = base64_encode($name);
$filecontent = $_POST['filecontent'] ?? '';

if ($price < 10) exitWithMessage(jsonEncode(["success" => false, "message" => "Price cannot be be under 10 coins"], true), true);
if (!preg_match('/^[a-zA-Z0-9 ]+$/', base64_decode($name))) exitWithMessage(jsonEncode(["success" => false, "message" => "Name is invalid"], true), true);
if (!$filecontent) exitWithMessage(jsonEncode(["success" => false, "message" => "Invalid image uploaded"], true), true);
$decoded = base64_decode($filecontent, true);
if (!$decoded) exitWithMessage(jsonEncode(["success" => false, "message" => "Invalid image uploaded"], true), true);
if (strlen($decoded) > 1024 * 1024) exitWithMessage(jsonEncode(["success" => false, "message" => "File size exceeds 1 MB limit"], true), true);
$info = getimagesizefromstring($decoded);
if (!$info) exitWithMessage(jsonEncode(["success" => false, "message" => "Invalid image uploaded"], true), true);
if ($info[2] !== IMAGETYPE_PNG) exitWithMessage(jsonEncode(["success" => false, "message" => "Image must be a PNG"], true), true);
if ($info[0] !== 128 || $info[1] !== 128) exitWithMessage(jsonEncode(["success" => false, "message" => "Invalid has to be 128x128"], true), true);

$conn = newConnection();

$stmt = $conn->prepare("SELECT * FROM users WHERE token = ? AND username = ?");
$stmt->bind_param("ss", $token, $username);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
if (!$row) exitWithMessage(jsonEncode(["success" => false, "message" => "Invalid session token or username, please refresh login"], true), true);
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
    exitWithMessage(jsonEncode(["success" => false, "message" => "This icon already exists in the marketplace"], true), true);
}
$stmt->close();

$uuid = uuidv4();

$stmt = $conn->prepare("INSERT INTO marketplaceicons (uuid, userId, data, hash, price, name, timestamp) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sissisi", $uuid, $id, $filecontent, $hash, $price, $name, $time);
$stmt->execute();
$insertId = $conn->insert_id;
$stmt->close();

echo jsonEncode(["success" => true, "message" => "Icon uploaded successfully! It will be reviewed and accepted or denied soon"], true);

$conn->close();