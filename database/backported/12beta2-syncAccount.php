<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$request_uid = $_POST['userID'] ?? 0;
$request_session = $_POST['gameSession'] ?? '';
$request_score = $_POST['highScore'] ?? 0;

$conn = newConnection();

$stmt = $conn->prepare("SELECT * FROM users WHERE token = ? AND id = ?");
$stmt->bind_param("ss", $request_session, $request_uid);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $updateStmt = $conn->prepare("UPDATE users SET legacy_high_score = ? WHERE token = ? AND id = ?");
    $updateStmt->bind_param("isi", $request_score, $request_session, $request_uid);
    $updateStmt->execute();
    echo 1;
    $updateStmt->close();
} else {
    echo "-2";
}

$stmt->close();
$conn->close();
?>