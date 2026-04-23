<?php
// /race/chat_api.php
session_start();
header('Content-Type: application/json');
// Gyorsítótár (Cache) teljes tiltása, hogy mindig frissüljön a chat
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$DB_HOST = "localhost";
$DB_USER = "swmjndga_swmjndga";
$DB_PASS = "Teszt1234!";
$DB_NAME = "swmjndga_f1adat";
$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

function getTeamColor($team) {
    switch ($team) {
        case 'Red Bull': return '#1E41FF'; case 'Ferrari': return '#DC0000'; case 'Mercedes': return '#00D2BE';
        case 'McLaren': return '#FF8700'; case 'Aston Martin': return '#006F62'; case 'Alpine': return '#0090FF';
        case 'Williams': return '#00A0DE'; case 'RB': return '#2b2bff'; case 'Audi': return '#e3000f';
        case 'Haas F1 Team': return '#B6BABD'; case 'Cadillac': return '#1b1b1b'; default: return '#ffffff';
    }
}

// Üzenet KÜLDÉSE (Biztonságos Prepared Statement)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $msg = trim($data['message'] ?? '');
    $user = $_SESSION['username'] ?? null;
    
    if($msg !== '' && $user) {
        // Így a "HELL\JUMPER" perjel sem tűnik el, és védi az adatbázist!
        $stmt = $conn->prepare("INSERT INTO race_live_chat (race_id, username, message) VALUES (25, ?, ?)");
        $stmt->bind_param("ss", $user, $msg);
        $stmt->execute();
        $stmt->close();
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit;
}

// Üzenetek LEKÉRÉSE (LEFT JOIN, hogy hibás névnél se tűnjön el a chat)
$sql = "SELECT c.id, c.username, c.message, c.sent_at, u.profile_image, u.fav_team 
        FROM race_live_chat c 
        LEFT JOIN users u ON c.username = u.username 
        WHERE c.race_id = 25 
        ORDER BY c.id ASC";
$res = $conn->query($sql);

$messages = [];
if ($res) {
    while($row = $res->fetch_assoc()) {
        $row['color'] = getTeamColor($row['fav_team'] ?? '');
        $row['profile_image'] = $row['profile_image'] ? '../uploads/' . $row['profile_image'] : '../drivers/default.png';
        $row['time'] = date('H:i', strtotime($row['sent_at']));
        $messages[] = $row;
    }
}

echo json_encode($messages);
$conn->close();
?>