<?php
// /f1fanclub/messages/pm_api.php
session_start();
header('Content-Type: application/json');
error_reporting(0);

$DB_HOST = "localhost";
$DB_USER = "swmjndga_swmjndga";
$DB_PASS = "Teszt1234!";
$DB_NAME = "swmjndga_f1adat";

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) { die(json_encode(['success' => false, 'error' => 'DB error'])); }

$username = $_SESSION['username'] ?? null;
if (!$username) { die(json_encode(['success' => false, 'error' => 'Not logged in'])); }

// Mivel van POST (üzenetküldés) és GET (lekérdezés) is, mindkettőből olvassuk ki az action-t
$action = $_GET['action'] ?? '';
if (empty($action)) {
    $postData = json_decode(file_get_contents('php://input'), true);
    if ($postData && isset($postData['action'])) {
        $action = $postData['action'];
    }
}

function getTeamColor($team) {
    switch ($team) {
        case 'Red Bull': return '#1E41FF'; case 'Ferrari': return '#DC0000'; case 'Mercedes': return '#00D2BE';
        case 'McLaren': return '#FF8700'; case 'Aston Martin': return '#006F62'; case 'Alpine': return '#0090FF';
        case 'Williams': return '#00A0DE'; case 'RB': return '#2b2bff'; case 'Audi': return '#e3000f';
        case 'Haas F1 Team': return '#B6BABD'; case 'Cadillac': return '#1b1b1b'; default: return '#777777';
    }
}

// 1. Kapcsolatok (barátok/partnerek) lekérése - UTOLSÓ ÜZENET SZERINT RENDEZVE (ID alapján!)
if ($action === 'get_friends') {
    // Sokkal biztonságosabb SQL: Kigyűjti az egyedi partnereket és a legmagasabb üzenet ID-t
    $sql = "
        SELECT 
            p.friend_name,
            u.profile_image,
            u.fav_team,
            p.last_msg_id
        FROM (
            SELECT 
                CASE WHEN sender = ? THEN receiver ELSE sender END AS friend_name,
                MAX(id) as last_msg_id
            FROM private_messages
            WHERE sender = ? OR receiver = ?
            GROUP BY CASE WHEN sender = ? THEN receiver ELSE sender END
        ) p
        JOIN users u ON u.username = p.friend_name
        ORDER BY p.last_msg_id DESC
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $username, $username, $username, $username);
    $stmt->execute();
    $res = $stmt->get_result();

    $friends = [];
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $friends[] = [
                'friend_name' => $row['friend_name'],
                'profile_image' => $row['profile_image'] ? '../uploads/' . $row['profile_image'] : '../drivers/default.png',
                'color' => getTeamColor($row['fav_team'])
            ];
        }
    }
    
    echo json_encode(['success' => true, 'friends' => $friends]);
    exit;
}

// 2. Konkrét üzenetek lekérése egy partnerrel
if ($action === 'get_messages') {
    $partner = $_GET['partner'] ?? '';
    if (!$partner) die(json_encode(['success' => false]));

    $sql = "SELECT * FROM private_messages 
            WHERE (sender = ? AND receiver = ?) OR (sender = ? AND receiver = ?) 
            ORDER BY sent_at ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $username, $partner, $partner, $username);
    $stmt->execute();
    $res = $stmt->get_result();

    $messages = [];
    while ($row = $res->fetch_assoc()) {
        $row['time'] = date('H:i', strtotime($row['sent_at']));
        $messages[] = $row;
    }
    
    // Olvasottá tesszük azokat, amiket a partner küldött nekünk
    $update = $conn->prepare("UPDATE private_messages SET is_read = 1 WHERE sender = ? AND receiver = ? AND is_read = 0");
    $update->bind_param("ss", $partner, $username);
    $update->execute();

    echo json_encode(['success' => true, 'messages' => $messages]);
    exit;
}

// 3. Üzenet küldése
if ($action === 'send') {
    // Az action-t már fentebb kinyertük, most csak a maradékot szedjük ki
    $data = json_decode(file_get_contents('php://input'), true);
    $receiver = trim($data['receiver'] ?? '');
    $msg = trim($data['message'] ?? '');

    if ($receiver !== '' && $msg !== '') {
        $stmt = $conn->prepare("INSERT INTO private_messages (sender, receiver, message) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $receiver, $msg);
        $stmt->execute();
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Üres üzenet vagy címzett']);
    }
    exit;
}

// 4. Felhasználó kereső
if ($action === 'search_users') {
    $term = $_GET['term'] ?? '';
    
    if (strlen($term) < 3) {
        echo json_encode(['success' => true, 'users' => []]);
        exit;
    }
    
    $termStr = "%" . $term . "%";
    $sql = "SELECT username, profile_image, fav_team FROM users WHERE username LIKE ? LIMIT 10";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $termStr);
    $stmt->execute();
    $res = $stmt->get_result();

    $users = [];
    while ($row = $res->fetch_assoc()) {
        $users[] = [
            'username' => $row['username'],
            'profile_image' => $row['profile_image'] ? '../uploads/' . $row['profile_image'] : '../drivers/default.png',
            'color' => getTeamColor($row['fav_team'])
        ];
    }
    echo json_encode(['success' => true, 'users' => $users]);
    exit;
}

// Ha semmilyen érvényes action nem jött:
echo json_encode(['success' => false, 'error' => 'Invalid action']);
?>