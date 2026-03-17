<?php
session_start();
header('Content-Type: application/json');

$DB_HOST = "localhost";
$DB_USER = "swmjndga_swmjndga";
$DB_PASS = "Teszt1234!";
$DB_NAME = "swmjndga_f1adat";

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) { die(json_encode(['success' => false, 'error' => 'DB error'])); }

$current_user = $_SESSION['username'] ?? null;

function getTeamColor($team) {
    switch ($team) {
        case 'Red Bull': return '#1E41FF'; case 'Ferrari': return '#DC0000'; case 'Mercedes': return '#00D2BE';
        case 'McLaren': return '#FF8700'; case 'Aston Martin': return '#006F62'; case 'Alpine': return '#0090FF';
        case 'Williams': return '#00A0DE'; case 'RB': return '#2b2bff'; case 'Audi': return '#e3000f';
        case 'Haas F1 Team': return '#B6BABD'; case 'Cadillac': return '#1b1b1b'; default: return '#777777';
    }
}

if (isset($_GET['username'])) {
    $target_user = trim($_GET['username']);
    
    $stmt = $conn->prepare("SELECT username, profile_image, fav_team, role, reg_date FROM users WHERE username = ?");
    $stmt->bind_param("s", $target_user);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        $user = $res->fetch_assoc();
        $user['profile_image'] = $user['profile_image'] ? '/f1fanclub/uploads/' . $user['profile_image'] : '/f1fanclub/drivers/default.png';
        $user['team_color'] = getTeamColor($user['fav_team']);
        $user['reg_date'] = date('Y. m. d.', strtotime($user['reg_date']));
        $user['role_name'] = ($user['role'] === 'admin') ? 'Adminisztrátor' : 'Felhasználó';
        
        // --- ÚJ: BARÁTSÁG STÁTUSZ VIZSGÁLATA ---
        $friendship_status = 'none';
        if ($current_user && $current_user !== $target_user) {
            $f_stmt = $conn->prepare("SELECT status, sender FROM friendships WHERE (sender=? AND receiver=?) OR (sender=? AND receiver=?)");
            $f_stmt->bind_param("ssss", $current_user, $target_user, $target_user, $current_user);
            $f_stmt->execute();
            $f_res = $f_stmt->get_result();
            
            if ($f_row = $f_res->fetch_assoc()) {
                if ($f_row['status'] === 'accepted') {
                    $friendship_status = 'accepted';
                } else {
                    // Ha pending, ki küldte kinek?
                    $friendship_status = ($f_row['sender'] === $current_user) ? 'pending_sent' : 'pending_received';
                }
            }
            $f_stmt->close();
        } elseif ($current_user === $target_user) {
            $friendship_status = 'self'; // Magát nézi
        }
        $user['friendship_status'] = $friendship_status;
        
        echo json_encode(['success' => true, 'user' => $user]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Nem található a felhasználó.']);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'error' => 'Nincs felhasználónév megadva.']);
}
$conn->close();
?>