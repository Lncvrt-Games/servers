<?php
require __DIR__ . '/../incl/util.php';
setJsonHeader();
checkClientDatabaseVersion();
$conn = newConnection();

$postData = getPostData();
$request_username = $postData['username'];
$request_password = $postData['password'];
$request_currenthighscore = $postData['currentHighScore'] ?? 0;
$request_logintype = $postData['loginType'] ?? '0';

$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $request_username);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        if (password_verify($request_password, $row["password"])) {
            $login_ip = getIPAddress();
            $login_time = time();
            $username = $row['username'];
            $highscore = $row['highScore'];
            $icon = $row['icon'];
            $overlay = $row['overlay'];
            $uid = $row['uid'];
            $totalNormalBerries = $row['totalNormalBerries'];
            $totalPoisonBerries = $row['totalPoisonBerries'];
            $totalSlowBerries = $row['totalSlowBerries'];
            $totalUltraBerries = $row['totalUltraBerries'];
            $totalSpeedyBerries = $row['totalSpeedyBerries'];
            $birdR = $row['birdR'];
            $birdG = $row['birdG'];
            $birdB = $row['birdB'];
            $overlayR = $row['overlayR'];
            $overlayG = $row['overlayG'];
            $overlayB = $row['overlayB'];
            $totalAttempts = $row['totalAttempts'];
            $game_session_token = $row['game_session_token'];
            if ($game_session_token == null || strlen(trim($game_session_token)) != 512)  {
                $game_session_token = bin2hex(random_bytes(256));
            }

            if ($request_currenthighscore > $row['highScore']) {
                $stmt = $conn->prepare("UPDATE users SET highScore = ? WHERE uid = ?");
                $stmt->bind_param("ii", $request_currenthighscore, $uid);
                $stmt->execute();
                $row['highScore'] = $request_currenthighscore;
            }

            $stmt = $conn->prepare("UPDATE users SET latest_ip = ?, game_session_token = ? WHERE uid = ?");
            $stmt->bind_param("ssi", $login_ip, $game_session_token, $uid);
            $stmt->execute();
            
            if ($request_logintype == "0") {
                echo encrypt(json_encode(["success" => true, "data" => [
                    "session" => (string)$game_session_token,
                    "username" => (string)$username,
                    "userid" => (string)$uid,
                    "highscore" => (string)$highscore,
                    "icon" => (int)$icon,
                    "overlay" => (int)$overlay,
                    "totalNormalBerries" => (string)$totalNormalBerries,
                    "totalPoisonBerries" => (string)$totalPoisonBerries,
                    "totalSlowBerries" => (string)$totalSlowBerries,
                    "totalUltraBerries" => (string)$totalUltraBerries,
                    "totalSpeedyBerries" => (string)$totalSpeedyBerries,
                    "totalAttempts" => (string)$totalAttempts,
                    "birdColor" => [
                        (int)$birdR,
                        (int)$birdG,
                        (int)$birdB
                    ],
                    "overlayColor" => [
                        (int)$overlayR,
                        (int)$overlayG,
                        (int)$overlayB
                    ]
                ]]));
            } else if ($request_logintype == "1") {
                echo encrypt(json_encode(["success" => true, "data" => [
                    "session" => $game_session_token,
                    "username" => $username,
                    "userid" => $uid
                ]]));
            } else {
                echo encrypt(json_encode(["success" => true]));
            }
        } else {
            echo encrypt(json_encode(["success" => false, "message" => "Invalid username or password"]));
        }
    }
} else {
    echo encrypt(json_encode(["success" => false, "message" => "Invalid username or password"]));
}

$stmt->close();
$conn->close();