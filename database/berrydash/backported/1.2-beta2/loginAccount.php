<?php
$conn = newConnection();

$request_username = $_POST['username'];
$request_password = $_POST['password'];

$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $request_username);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        if (password_verify($request_password, $row["password"])) {
            $login_ip = getIPAddress();
            $login_time = time();
            $uid = $row['id'];
            $username = $row['username'];
            $highscore = $row['legacy_high_score'];
            $token = $row['token'];

            $stmt = $conn->prepare("UPDATE users SET latest_ip = ? WHERE id = ?");
            $stmt->bind_param("si", $login_ip, $uid);
            $stmt->execute();

            echo "$token:$uid:$highscore";
        } else {
            $stmt->close();
            $conn->close();
            exit("-2");
        }
    }
} else {
    $stmt->close();
    $conn->close();
    exit("-2");
}

$stmt->close();
$conn->close();

?>