<?php
session_start();
header('Content-Type: application/json');

/* ==== ADATBÁZIS ==== */
$DB_HOST = "localhost";
$DB_USER = "swmjndga_swmjndga";
$DB_PASS = "Teszt1234!";
$DB_NAME = "swmjndga_f1adat";

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'DB error']);
    exit;
}

// Csapatszín segédfüggvény (JSON válaszhoz)
function getTeamColor($team) {
    switch ($team) {
        case 'Red Bull': return '#1E41FF';
        case 'Ferrari': return '#DC0000';
        case 'Mercedes': return '#00D2BE';
        case 'McLaren': return '#FF8700';
        case 'Aston Martin': return '#006F62';
        case 'Alpine': return '#0090FF';
        case 'Williams': return '#00A0DE';
        case 'RB': return '#2b2bff';
        case 'Kick Sauber': return '#52E252';
        case 'Haas F1 Team': return '#B6BABD';
        default: return '#ffffff';
    }
}

$method = $_SERVER['REQUEST_METHOD'];

// --- KOMMENTEK LEKÉRÉSE (GET) ---
if ($method === 'GET') {
    $news_id = isset($_GET['news_id']) ? (int)$_GET['news_id'] : 0;
    
    // JOIN-oljuk a users táblát a profilkép és kedvenc csapat miatt
    $stmt = $conn->prepare("
        SELECT c.id, c.username, c.comment, c.created_at, 
               u.profile_image, u.fav_team, u.role
        FROM news_comments c
        LEFT JOIN users u ON c.username = u.username
        WHERE c.news_id = ?
        ORDER BY c.created_at ASC
    ");
    $stmt->bind_param("i", $news_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $comments = [];
    while ($row = $result->fetch_assoc()) {
        $row['team_color'] = getTeamColor($row['fav_team']);
        // Formázott dátum
        $row['date_formatted'] = date('M d, H:i', strtotime($row['created_at']));
        $comments[] = $row;
    }
    
    echo json_encode(['success' => true, 'comments' => $comments]);
    exit;
}

// --- ÚJ KOMMENT BEKÜLDÉSE (POST) ---
if ($method === 'POST') {
    if (!isset($_SESSION['username'])) {
        echo json_encode(['success' => false, 'error' => 'Nincs bejelentkezve']);
        exit;
    }

    $news_id = isset($_POST['news_id']) ? (int)$_POST['news_id'] : 0;
    $comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';
    $username = $_SESSION['username'];

    if ($news_id <= 0 || empty($comment)) {
        echo json_encode(['success' => false, 'error' => 'Üres komment']);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO news_comments (news_id, username, comment) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $news_id, $username, $comment);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'SQL hiba']);
    }
    exit;
}
?>