<?php
$conn = newConnection();

$request_showamount = isset($_POST['showAmount']) ? $_POST['showAmount'] : 0;

switch ($request_showamount) {
    case 1:
        $request_limit = 100;
        break;
    case 2:
        $request_limit = 250;
        break;
    case 3:
        $request_limit = 500;
        break;
    default:
        $request_limit = 50;
        break;
}

$stmt = $conn->prepare("SELECT username, legacy_high_score, id, save_data FROM users WHERE legacy_high_score > 0 AND banned = 0 AND leaderboardsBanned = 0 ORDER BY legacy_high_score DESC LIMIT ?");
$stmt->bind_param("i", $request_limit);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $topPlayers = [];
    
    while ($row = $result->fetch_assoc()) {
        $savedata = json_decode($row['save_data'], true);
        $icon = $savedata['bird']['icon'] ?? 1;
        $overlay = $savedata['bird']['overlay'] ?? 0;
        $birdColor = $savedata['settings']['colors']['icon'] ?? [255,255,255];
        $overlayColor = $savedata['settings']['colors']['overlay'] ?? [255,255,255];
        $topPlayers[] = base64_encode($row["username"]) . ":" . $row["legacy_high_score"] . ":" . $icon . ":" . $overlay . ":" . $row["id"] . ":" . $birdColor[0] . ":" . $birdColor[1] . ":" . $birdColor[2] . ":" . $overlayColor[0] . ":" . $overlayColor[1] . ":" . $overlayColor[2];
    }
    
    $output = implode(";", $topPlayers);
    
    echo encrypt($output);
} else {
    echo encrypt("-1");
}

$conn->close();
?>