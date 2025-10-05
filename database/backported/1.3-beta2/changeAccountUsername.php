<?php
$conn = newConnection();

$username = isset($_POST['username']) ? $_POST['username'] : '';
$current_password = isset($_POST['currentPassword']) ? $_POST['currentPassword'] : '';
$new_username = isset($_POST['newUsername']) ? $_POST['newUsername'] : '';

if (empty($username) || empty($new_username) || empty($current_password)) {
    die("-2");
}

if (!preg_match('/^[a-zA-Z0-9]{3,16}$/', $new_username)) {
    die("-3");
}

$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $username);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    if (!password_verify($current_password, $user['password'])) {
        die("-6");
    }
} else {
    die("-7");
}

$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $new_username);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows > 0) {
    die("-8");
}

$stmt = $conn->prepare("UPDATE users SET username = ? WHERE username = ?");
$stmt->bind_param("ss", $new_username, $username);

$stmt->execute();
$stmt->close();
$conn->close();

echo '1';

?>