<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
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
    $topPlayers = new stdClass();
    
    while ($row = $result->fetch_assoc()) {
        $savedata = json_decode($row['save_data'], true);
        $icon = $savedata['bird']['icon'] ?? 1;
        $overlay = $savedata['bird']['overlay'] ?? 0;
        $topPlayers[] = $row["username"] . ":" . $row["legacy_high_score"] . ":" . $icon . ":" . $overlay . ":" . $row["id"];
    }
    
    $output = implode("::", $topPlayers);
    
    echo $output;
} else {
    echo -2;
}

$conn->close();
?>