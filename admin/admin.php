<?php
session_start();

// --- KONFIGURÁCIÓ & ADATBÁZIS ---
$DB_HOST = "localhost";
$DB_USER = "swmjndga_swmjndga";
$DB_PASS = "Teszt1234!";
$DB_NAME = "swmjndga_f1adat";

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) { die("Adatbázis hiba: " . $conn->connect_error); }

// --- JOGOSULTSÁG ELLENŐRZÉS ---
if (!isset($_SESSION['username'])) { header("Location: /f1fanclub/login/login.html"); exit; }
$currentUser = $_SESSION['username'];

// Admin jog ellenőrzése
$stmt = $conn->prepare("SELECT id, role, profile_image FROM users WHERE username=?");
$stmt->bind_param("s", $currentUser);
$stmt->execute();
$adminData = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$adminData || $adminData['role'] !== 'admin') {
    echo "<h2 style='color:red;text-align:center;margin-top:50px;'>Nincs jogosultságod!</h2>";
    exit;
}

// --- FÜGGVÉNY: NAPLÓZÁS ---
function logActivity($conn, $user, $action, $details) {
    $ip = $_SERVER['REMOTE_ADDR'];
    $stmt = $conn->prepare("INSERT INTO activity_logs (username, action, details, ip_address) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $user, $action, $details, $ip);
    $stmt->execute();
    $stmt->close();
}

// --- POST KÉRÉSEK KEZELÉSE ---
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'ban_user') {
        $userId = (int)$_POST['user_id'];
        $stmt = $conn->prepare("UPDATE users SET is_banned = 1 WHERE id = ?");
        $stmt->bind_param("i", $userId);
        if($stmt->execute()) {
            logActivity($conn, $currentUser, 'ban_user', "Felhasználó ID ($userId) kitiltva.");
            $message = "Felhasználó sikeresen kitiltva!";
        }
        $stmt->close();
    }

    if (isset($_POST['action']) && $_POST['action'] === 'unban_user') {
        $userId = (int)$_POST['user_id'];
        $stmt = $conn->prepare("UPDATE users SET is_banned = 0 WHERE id = ?");
        $stmt->bind_param("i", $userId);
        if($stmt->execute()) {
            logActivity($conn, $currentUser, 'unban_user', "Felhasználó ID ($userId) tiltása feloldva.");
            $message = "Tiltás feloldva!";
        }
        $stmt->close();
    }

    if (isset($_POST['action']) && $_POST['action'] === 'change_role') {
        $userId = (int)$_POST['user_id'];
        $newRole = $_POST['new_role'];
        if (in_array($newRole, ['user', 'admin'])) {
            $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
            $stmt->bind_param("si", $newRole, $userId);
            $stmt->execute();
            logActivity($conn, $currentUser, 'role_change', "Felhasználó ID ($userId) új szerepköre: $newRole");
            $message = "Szerepkör módosítva.";
        }
    }
}

// --- ADATOK LEKÉRÉSE A MEGJELENÍTÉSHEZ ---
$activeUsers = $conn->query("SELECT * FROM users WHERE is_banned = 0 ORDER BY reg_date DESC");
$bannedUsers = $conn->query("SELECT * FROM users WHERE is_banned = 1 ORDER BY reg_date DESC");
$logs = $conn->query("SELECT * FROM activity_logs ORDER BY created_at DESC LIMIT 50");

$raceStatusResult = $conn->query("SELECT status, current_lap, total_laps FROM race_control WHERE race_id=25 LIMIT 1");
$raceData = $raceStatusResult->fetch_assoc();
$raceStatus = $raceData['status'] ?? 'archived';

$chatArchives = $conn->query("SELECT a.*, u.profile_image, u.fav_team FROM race_chat_archives a LEFT JOIN users u ON a.username = u.username ORDER BY a.archived_at DESC, a.sent_at ASC LIMIT 200");

// ÚJ: Pitwall Ranglista lekérése (Csak azok, akik már tippeltek, pontok alapján csökkenve)
$pitwallUsers = $conn->query("
    SELECT DISTINCT u.id, u.username, u.email, u.profile_image, u.fav_team, u.pitwall_points 
    FROM users u 
    JOIN pitwall_predictions p ON u.username = p.username 
    ORDER BY u.pitwall_points DESC, u.username ASC
");

?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>F1 Admin Vezérlőpult</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/svg+xml" href="https://upload.wikimedia.org/wikipedia/commons/3/33/F1.svg">
    <style>
        :root { --primary: #e10600; --dark: #15151e; --darker: #0f0f15; --light: #f0f0f0; --sidebar-width: 260px; }
        body { font-family: 'Poppins', sans-serif; background-color: var(--darker); color: var(--light); margin: 0; display: flex; height: 100vh; overflow: hidden; }
        .sidebar { width: var(--sidebar-width); background-color: var(--dark); display: flex; flex-direction: column; border-right: 1px solid #333; padding: 20px; }
        .brand { display: flex; align-items: center; gap: 10px; font-size: 1.2rem; font-weight: 800; color: #fff; margin-bottom: 40px; text-transform: uppercase; }
        .brand img { height: 30px; }
        .menu { list-style: none; padding: 0; }
        .menu li { margin-bottom: 10px; }
        .menu-btn { width: 100%; background: transparent; border: none; color: #888; padding: 12px 15px; text-align: left; cursor: pointer; border-radius: 8px; font-size: 0.95rem; display: flex; align-items: center; gap: 12px; transition: 0.3s; font-family: inherit; text-decoration: none; }
        .menu-btn:hover, .menu-btn.active { background-color: var(--primary); color: white; box-shadow: 0 4px 15px rgba(225, 6, 0, 0.4); }
        .user-panel { margin-top: auto; border-top: 1px solid #333; padding-top: 20px; display: flex; align-items: center; gap: 10px; }
        .user-panel img { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid var(--primary); }
        .main-content { flex: 1; padding: 30px; overflow-y: auto; }
        header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        h2 { border-left: 4px solid var(--primary); padding-left: 15px; }
        .tab-content { display: none; animation: fadeIn 0.4s; }
        .tab-content.active { display: block; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .card { background: var(--dark); border-radius: 12px; padding: 25px; margin-bottom: 20px; border: 1px solid #333; box-shadow: 0 5px 20px rgba(0,0,0,0.5); }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #333; font-size: 0.9rem; }
        th { color: #888; font-weight: 600; text-transform: uppercase; font-size: 0.8rem; }
        tr:hover { background: rgba(255,255,255,0.02); }
        .btn { padding: 6px 12px; border-radius: 6px; border: none; cursor: pointer; font-size: 0.8rem; color: #fff; transition: 0.2s; }
        .btn-ban { background: #b30000; }
        .btn-ban:hover { background: #ff0000; }
        .btn-unban { background: #008f00; }
        .btn-unban:hover { background: #00b300; }
        .btn-save { background: #333; border: 1px solid #555; }
        .btn-save:hover { background: #555; }
        .race-status { font-size: 1.5rem; font-weight: 800; color: var(--primary); margin-bottom: 20px; display: block; }
        .control-btns { display: flex; gap: 15px; justify-content: center; }
        .btn-large { padding: 15px 30px; font-size: 1rem; font-weight: bold; border-radius: 8px; cursor: pointer; border: none; transition: 0.2s; display: flex; align-items: center; gap: 8px;}
        .btn-large:hover { transform: scale(1.05); }
        .start { background: #00d2be; color: #000; }
        .stop { background: var(--primary); color: #fff; }
        .hard-stop { background: #8b0000; color: #fff; border: 2px solid #ff4a4a; }
        .start:disabled, .stop:disabled, .hard-stop:disabled { opacity: 0.3; cursor: not-allowed; transform: none; }
        .log-item { padding: 10px 0; border-bottom: 1px solid #222; display: flex; justify-content: space-between; font-size: 0.85rem; }
        .log-action { font-weight: bold; color: var(--primary); }
        .log-time { color: #666; }
        .alert { padding: 15px; background: rgba(0, 210, 190, 0.1); border: 1px solid #00d2be; border-radius: 8px; margin-bottom: 20px; color: #00d2be; }
        .btn-return { color: var(--primary) !important; font-weight: 800; background: rgba(225, 6, 0, 0.08) !important; box-sizing: border-box; }
        .btn-return:hover { background-color: var(--primary) !important; color: #fff !important; box-shadow: 0 4px 15px rgba(225, 6, 0, 0.4); }

        /* =========================================
           ADDED STYLES TO MATCH GLOBAL DESIGN
           ========================================= */
        
        * {
            box-sizing: border-box;
<<<<<<< HEAD
        }

        /* Override body styles to match global */
        body {
            font-family: 'Poppins', sans-serif;
            background: radial-gradient(circle at 20% 50%, rgba(225, 6, 0, 0.05) 0%, transparent 50%),
                        radial-gradient(circle at 80% 80%, rgba(225, 6, 0, 0.05) 0%, transparent 50%);
            background-color: var(--darker);
        }

        /* Update sidebar to match global dark theme */
        .sidebar {
            background: linear-gradient(180deg, #0a0a0a 0%, #111 100%);
            border-right: 1px solid rgba(225, 6, 0, 0.3);
        }

        /* Update menu buttons */
        .menu-btn {
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .menu-btn i {
            width: 24px;
            font-size: 1rem;
        }

        .menu-btn:hover, .menu-btn.active {
            background: linear-gradient(135deg, #e10600, #ff1a00);
            color: white;
            box-shadow: 0 4px 15px rgba(225, 6, 0, 0.4);
        }

        /* Update user panel */
        .user-panel {
            border-top: 1px solid rgba(225, 6, 0, 0.3);
        }

        .user-panel img {
            border: 2px solid var(--primary);
        }

        /* Update cards to match global style */
        .card {
            background: linear-gradient(145deg, #111111 0%, #1a1a1a 100%);
            border: 1px solid rgba(225, 6, 0, 0.3);
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        }

        /* Update headers */
        h2 {
            color: var(--primary);
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-left: 4px solid var(--primary);
        }

=======
        }

        /* Override body styles to match global */
        body {
            font-family: 'Poppins', sans-serif;
            background: radial-gradient(circle at 20% 50%, rgba(225, 6, 0, 0.05) 0%, transparent 50%),
                        radial-gradient(circle at 80% 80%, rgba(225, 6, 0, 0.05) 0%, transparent 50%);
            background-color: var(--darker);
        }

        /* Update sidebar to match global dark theme */
        .sidebar {
            background: linear-gradient(180deg, #0a0a0a 0%, #111 100%);
            border-right: 1px solid rgba(225, 6, 0, 0.3);
        }

        /* Update menu buttons */
        .menu-btn {
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .menu-btn i {
            width: 24px;
            font-size: 1rem;
        }

        .menu-btn:hover, .menu-btn.active {
            background: linear-gradient(135deg, #e10600, #ff1a00);
            color: white;
            box-shadow: 0 4px 15px rgba(225, 6, 0, 0.4);
        }

        /* Update user panel */
        .user-panel {
            border-top: 1px solid rgba(225, 6, 0, 0.3);
        }

        .user-panel img {
            border: 2px solid var(--primary);
        }

        /* Update cards to match global style */
        .card {
            background: linear-gradient(145deg, #111111 0%, #1a1a1a 100%);
            border: 1px solid rgba(225, 6, 0, 0.3);
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        }

        /* Update headers */
        h2 {
            color: var(--primary);
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-left: 4px solid var(--primary);
        }

>>>>>>> f81424192996985be2da559c0f9a2c1f13f5eb7f
        /* Update buttons */
        .btn {
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
<<<<<<< HEAD
=======
        }

        .btn-ban {
            background: #b30000;
        }

        .btn-ban:hover {
            background: #ff0000;
            transform: translateY(-2px);
        }

        .btn-unban {
            background: #008f00;
        }

        .btn-unban:hover {
            background: #00b300;
            transform: translateY(-2px);
        }

        .btn-save {
            background: #333;
            border: 1px solid #555;
        }

        .btn-save:hover {
            background: #555;
            transform: translateY(-2px);
        }

        /* Update table styles */
        th {
            color: var(--primary);
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 1px;
        }

        td {
            color: #ddd;
        }

        tr:hover {
            background: rgba(225, 6, 0, 0.05);
        }

        /* Update race status */
        .race-status {
            font-size: 1.5rem;
            font-weight: 800;
            margin-bottom: 20px;
            display: block;
            text-shadow: 0 2px 10px rgba(225, 6, 0, 0.3);
        }

        /* Update large buttons */
        .btn-large {
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-radius: 50px;
        }

        .btn-large:hover {
            transform: translateY(-3px);
        }

        .start {
            background: #00d2be;
            color: #000;
        }

        .stop {
            background: var(--primary);
            color: #fff;
        }

        .hard-stop {
            background: #8b0000;
            color: #fff;
            border: 2px solid #ff4a4a;
        }

        /* Update alert styles */
        .alert {
            background: rgba(0, 210, 190, 0.1);
            border: 1px solid #00d2be;
            border-radius: 12px;
            color: #00d2be;
            font-weight: 600;
        }

        /* Update log items */
        .log-item {
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .log-action {
            color: var(--primary);
        }

        /* Update select dropdown */
        select {
            background: #222;
            color: white;
            border: 1px solid #444;
            border-radius: 6px;
            font-family: 'Poppins', sans-serif;
        }

        /* Update brand */
        .brand {
            color: #fff;
            font-weight: 800;
            letter-spacing: 2px;
        }

        /* Update hr */
        hr {
            border-top: 1px solid rgba(225, 6, 0, 0.2);
        }

        /* Avatar images */
        .user-panel img,
        .card img[style*="width:30px"] {
            border: 2px solid var(--primary);
        }

        /* Scrollbar styling */
        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: #1a1a1a;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--primary);
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #ff1a00;
        }

        /* =========================================
           RESPONSIVE STYLES - ADDED ONLY THESE
           ========================================= */
        
        /* Hamburger menu button */
        .hamburger {
            display: none;
            background: none;
            border: none;
            color: white;
            font-size: 28px;
            cursor: pointer;
            padding: 10px;
            z-index: 1001;
            transition: color 0.3s ease;
            position: fixed;
            top: 15px;
            right: 20px;
        }

        .hamburger:hover {
            color: #e10600;
        }

        /* Mobile responsive styles */
        @media (max-width: 992px) {
            .hamburger {
                display: block;
            }
            
            .sidebar {
                position: fixed;
                left: -260px;
                top: 0;
                bottom: 0;
                z-index: 1000;
                transition: left 0.3s ease;
            }
            
            .sidebar.open {
                left: 0;
            }
            
            .main-content {
                width: 100%;
                padding: 70px 15px 15px 15px;
            }
            
            .card {
                padding: 15px;
                overflow-x: auto;
            }
            
            table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }
            
            th, td {
                padding: 8px 10px;
                font-size: 0.75rem;
            }
            
            .btn-large {
                padding: 10px 20px;
                font-size: 0.8rem;
            }
            
            .control-btns {
                flex-direction: column;
                gap: 10px;
            }
            
            .control-btns .btn-large {
                width: 100%;
                justify-content: center;
            }
            
            .race-status {
                font-size: 1.2rem;
            }
            
            .log-item {
                flex-direction: column;
                gap: 5px;
            }
            
            .log-item > div:last-child {
                text-align: left;
            }
        }
        
        @media (max-width: 576px) {
            .hamburger {
                font-size: 24px;
                top: 12px;
                right: 15px;
            }
            
            .main-content {
                padding: 60px 10px 10px 10px;
            }
            
            .brand {
                font-size: 0.9rem;
            }
            
            .brand img {
                height: 22px;
            }
            
            .menu-btn {
                padding: 10px 12px;
                font-size: 0.7rem;
            }
            
            .menu-btn i {
                font-size: 0.8rem;
            }
            
            .card h3 {
                font-size: 1rem;
            }
>>>>>>> f81424192996985be2da559c0f9a2c1f13f5eb7f
        }

        .btn-ban {
            background: #b30000;
        }

        .btn-ban:hover {
            background: #ff0000;
            transform: translateY(-2px);
        }

        .btn-unban {
            background: #008f00;
        }

        .btn-unban:hover {
            background: #00b300;
            transform: translateY(-2px);
        }

        .btn-save {
            background: #333;
            border: 1px solid #555;
        }

        .btn-save:hover {
            background: #555;
            transform: translateY(-2px);
        }

        /* Update table styles */
        th {
            color: var(--primary);
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 1px;
        }

        td {
            color: #ddd;
        }

        tr:hover {
            background: rgba(225, 6, 0, 0.05);
        }

        /* Update race status */
        .race-status {
            font-size: 1.5rem;
            font-weight: 800;
            margin-bottom: 20px;
            display: block;
            text-shadow: 0 2px 10px rgba(225, 6, 0, 0.3);
        }

        /* Update large buttons */
        .btn-large {
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-radius: 50px;
        }

        .btn-large:hover {
            transform: translateY(-3px);
        }

        .start {
            background: #00d2be;
            color: #000;
        }

        .stop {
            background: var(--primary);
            color: #fff;
        }

        .hard-stop {
            background: #8b0000;
            color: #fff;
            border: 2px solid #ff4a4a;
        }

        /* Update alert styles */
        .alert {
            background: rgba(0, 210, 190, 0.1);
            border: 1px solid #00d2be;
            border-radius: 12px;
            color: #00d2be;
            font-weight: 600;
        }

        /* Update log items */
        .log-item {
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .log-action {
            color: var(--primary);
        }

        /* Update select dropdown */
        select {
            background: #222;
            color: white;
            border: 1px solid #444;
            border-radius: 6px;
            font-family: 'Poppins', sans-serif;
        }

        /* Update brand */
        .brand {
            color: #fff;
            font-weight: 800;
            letter-spacing: 2px;
        }

        /* Update hr */
        hr {
            border-top: 1px solid rgba(225, 6, 0, 0.2);
        }

        /* Avatar images */
        .user-panel img,
        .card img[style*="width:30px"] {
            border: 2px solid var(--primary);
        }

        /* Scrollbar styling */
        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: #1a1a1a;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--primary);
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #ff1a00;
        }

        /* =========================================
           RESPONSIVE STYLES - ADDED ONLY THESE
           ========================================= */
        
        /* Hamburger menu button */
        .hamburger {
            display: none;
            background: none;
            border: none;
            color: white;
            font-size: 28px;
            cursor: pointer;
            padding: 10px;
            z-index: 1001;
            transition: color 0.3s ease;
            position: fixed;
            top: 15px;
            right: 20px;
        }

        .hamburger:hover {
            color: #e10600;
        }

        /* Mobile responsive styles */
        @media (max-width: 992px) {
            .hamburger {
                display: block;
            }
            
            .sidebar {
                position: fixed;
                left: -260px;
                top: 0;
                bottom: 0;
                z-index: 1000;
                transition: left 0.3s ease;
            }
            
            .sidebar.open {
                left: 0;
            }
            
            .main-content {
                width: 100%;
                padding: 70px 15px 15px 15px;
            }
            
            .card {
                padding: 15px;
                overflow-x: auto;
            }
            
            table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }
            
            th, td {
                padding: 8px 10px;
                font-size: 0.75rem;
            }
            
            .btn-large {
                padding: 10px 20px;
                font-size: 0.8rem;
            }
            
            .control-btns {
                flex-direction: column;
                gap: 10px;
            }
            
            .control-btns .btn-large {
                width: 100%;
                justify-content: center;
            }
            
            .race-status {
                font-size: 1.2rem;
            }
            
            .log-item {
                flex-direction: column;
                gap: 5px;
            }
            
            .log-item > div:last-child {
                text-align: left;
            }
        }
        
        @media (max-width: 576px) {
            .hamburger {
                font-size: 24px;
                top: 12px;
                right: 15px;
            }
            
            .main-content {
                padding: 60px 10px 10px 10px;
            }
            
            .brand {
                font-size: 0.9rem;
            }
            
            .brand img {
                height: 22px;
            }
            
            .menu-btn {
                padding: 10px 12px;
                font-size: 0.7rem;
            }
            
            .menu-btn i {
                font-size: 0.8rem;
            }
            
            .card h3 {
                font-size: 1rem;
            }
        }
        /* Webkit browsers (Chrome, Safari, Edge) */
::-webkit-scrollbar {
    width: 6px;
    height: 6px; /* For horizontal scrollbars too */
}

::-webkit-scrollbar-track {
    background: #1a1a1a;
    border-radius: 3px;
}

::-webkit-scrollbar-thumb {
    background: #e10600;
    border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
    background: #ff2b2b; /* Lighter red on hover */
}

/* Firefox */
* {
    scrollbar-width: thin;
    scrollbar-color: #e10600 #1a1a1a;
}
    </style>
</head>
<body>

    <!-- Hamburger button -->
    <button class="hamburger" id="hamburgerBtn">
        <i class="fas fa-bars"></i>
    </button>

    <aside class="sidebar" id="sidebar">
        <div class="brand">
            <img src="https://upload.wikimedia.org/wikipedia/commons/3/33/F1.svg" alt="F1">
            <span>ADMIN</span>
        </div>

        <ul class="menu">
            <li>
                <a href="/f1fanclub/index.php" class="menu-btn btn-return">
                    <i class="fas fa-home"></i> Vissza a Főoldalra
                </a>
            </li>
            <hr style="border: 0; border-top: 1px solid #333; margin: 15px 0;">
            
            <li>
                <button class="menu-btn active" onclick="showTab('race')">
                    <i class="fas fa-flag-checkered"></i> Verseny Szimulálás
                </button>
            </li>
            <li>
                <button class="menu-btn" onclick="showTab('users')">
                    <i class="fas fa-users"></i> Felhasználók adatai
                </button>
            </li>
            <li>
                <button class="menu-btn" onclick="showTab('pitwall')">
                    <i class="fas fa-trophy"></i> Pontok követése
                </button>
            </li>
            <li>
                <button class="menu-btn" onclick="showTab('banned')">
                    <i class="fas fa-user-slash"></i> Kitiltott felhasználók
                </button>
            </li>
            <li>
                <button class="menu-btn" onclick="showTab('activity')">
                    <i class="fas fa-list-alt"></i> Aktivitás Napló
                </button>
            </li>
            <li>
                <button class="menu-btn" onclick="showTab('chat-archive')">
                    <i class="fas fa-archive"></i> Archivált Chat
                </button>
            </li>
        </ul>

        <div class="user-panel">
            <img src="/f1fanclub/uploads/<?= htmlspecialchars($adminData['profile_image'] ?? 'default.png') ?>" alt="Admin">
            <div>
                <div style="font-weight:bold;"><?= htmlspecialchars($currentUser) ?></div>
                <a href="/f1fanclub/logout/logout.php" style="color:#888; font-size:0.8rem; text-decoration:none;">Kijelentkezés</a>
            </div>
        </div>
    </aside>

    <main class="main-content">
        
        <?php if($message): ?>
            <div class="alert"><?= $message ?></div>
        <?php endif; ?>

        <div id="tab-race" class="tab-content active">
            <header><h2>Verseny Irányítás</h2></header>
            
            <div class="card" style="text-align: center;">
                <h3>Kanadai Nagydíj 2026</h3>
                
                <?php 
                    $displayStatus = 'ÉLŐ VERSENY';
                    $color = '#00d2be';
                    if ($raceStatus === 'stopped') { $displayStatus = 'SZÜNETEL'; $color = 'orange'; }
                    if ($raceStatus === 'archived') { $displayStatus = 'ARCHIVÁLVA (LEZÁRVA)'; $color = '#666'; }
                    if ($raceStatus === 'finished') { $displayStatus = 'BEFEJEZŐDÖTT'; $color = '#fff'; }
                ?>
                
                <span class="race-status" style="color: <?= $color ?>;">
                    Állapot: <?= $displayStatus ?> 
                    <br><span style="font-size: 1rem; color:#888;">(Kör: <?= $raceData['current_lap'] ?>/<?= $raceData['total_laps'] ?>)</span>
                </span>
                
                <div class="control-btns">
                    <button onclick="controlRace('start')" class="btn-large start" <?= $raceStatus === 'running' ? 'disabled' : '' ?>>
                        <i class="fas fa-play"></i> INDÍTÁS
                    </button>
                    <button onclick="controlRace('stop')" class="btn-large stop" <?= ($raceStatus !== 'running') ? 'disabled' : '' ?>>
                        <i class="fas fa-pause"></i> SZÜNET
                    </button>
                    <button onclick="controlRace('hard_stop')" class="btn-large hard-stop" <?= $raceStatus === 'archived' ? 'disabled' : '' ?>>
                        <i class="fas fa-trash-alt"></i> TELJES LEÁLLÍTÁS ÉS MENTÉS
                    </button>
                </div>
                
                <p style="margin-top:20px; color:#888; font-size:0.9rem;">
                    A <strong style="color:#ff4a4a;">Teljes Leállítás</strong> gomb végleg bezárja a verseny linkjét a felhasználók előtt, és lementi a Live Chatet az archívumba!
                </p>
                <a href="../race/live.php" target="_blank" class="btn btn-save" style="margin-top:10px; display:inline-block; text-decoration:none;">
                    Élő közvetítés megnyitása
                </a>
            </div>
        </div>

        <div id="tab-users" class="tab-content">
            <header><h2>Aktív Felhasználók</h2></header>
            <div class="card">
                <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr><th>Felhasználó</th><th>Email</th><th>IP Cím</th> <th>Szerepkör</th><th>Csapat</th><th>Regisztrált</th><th>Műveletek</th></tr>
                    </thead>
                    <tbody>
                        <?php while($u = $activeUsers->fetch_assoc()): ?>
                        <tr>
                            <td><div style="display:flex; align-items:center; gap:10px;"><img src="/f1fanclub/uploads/<?= htmlspecialchars($u['profile_image'] ?? 'default.png') ?>" style="width:30px;height:30px;border-radius:50%;object-fit:cover;"><?= htmlspecialchars($u['username']) ?></div></td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td style="font-family: monospace; color:#aaa;"><?= htmlspecialchars($u['ip_address'] ?? 'Ismeretlen') ?></td> 
                            <td>
                                <form method="POST" style="display:flex; gap:5px; flex-wrap:wrap;">
                                    <input type="hidden" name="action" value="change_role"><input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                    <select name="new_role" style="background:#222; color:#fff; border:1px solid #444; border-radius:4px;">
                                        <option value="user" <?= $u['role'] == 'user' ? 'selected' : '' ?>>Felhasználó</option><option value="admin" <?= $u['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                                    </select><button type="submit" class="btn btn-save">OK</button>
                                </form>
                            </td>
                            <td><?= htmlspecialchars($u['fav_team']) ?></td>
                            <td><?= date('Y.m.d', strtotime($u['reg_date'])) ?></td>
                            <td>
                                <?php if($u['username'] !== $currentUser): ?>
                                <form method="POST" onsubmit="return confirm('Biztosan ki akarod tiltani ezt a felhasználót?');"><input type="hidden" name="action" value="ban_user"><input type="hidden" name="user_id" value="<?= $u['id'] ?>"><button type="submit" class="btn btn-ban"><i class="fas fa-ban"></i> Kitiltás</button></form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                </div>
            </div>
        </div>

        <div id="tab-pitwall" class="tab-content">
            <header><h2>The Pitwall Ranglista</h2></header>
            <div class="card">
                <?php if($pitwallUsers && $pitwallUsers->num_rows > 0): ?>
                <div style="overflow-x: auto;">
                </table>
                    <thead>
                        <tr>
                            <th>Helyezés</th>
                            <th>Felhasználó</th>
                            <th>Email</th>
                            <th>Csapat</th>
                            <th style="text-align: right;">Pontszám</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $rank = 1; while($pUser = $pitwallUsers->fetch_assoc()): ?>
                        <tr>
                            <td style="font-weight: bold; color: var(--primary); font-size: 1.1rem;"><?= $rank++ ?>.</td>
                            <td>
                                <div style="display:flex; align-items:center; gap:10px;">
                                    <img src="/f1fanclub/uploads/<?= htmlspecialchars($pUser['profile_image'] ?? 'default.png') ?>" style="width:30px;height:30px;border-radius:50%;object-fit:cover;">
                                    <strong><?= htmlspecialchars($pUser['username']) ?></strong>
                                </div>
                            </td>
                            <td style="color:#aaa;"><?= htmlspecialchars($pUser['email']) ?></td>
                            <td><?= htmlspecialchars($pUser['fav_team'] ?? 'Nincs megadva') ?></td>
                            <td style="text-align: right; font-weight: 800; color: #d4af37; font-size: 1.1rem;">
                                <?= number_format($pUser['pitwall_points']) ?> PONT
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                </div>
                <?php else: ?>
                    <p style="text-align:center; color:#888; padding: 20px;">Még egyetlen felhasználó sem adott le tippet The Pitwall-on.</p>
                <?php endif; ?>
            </div>
        </div>

        <div id="tab-banned" class="tab-content">
            <header><h2>Kitiltott Felhasználók</h2></header>
            <div class="card">
                <?php if($bannedUsers->num_rows > 0): ?>
                <div style="overflow-x: auto;">
                <table>
                    <thead><tr><th>Felhasználó</th><th>Email</th><th>IP Cím</th> <th>Regisztrált</th><th>Műveletek</th></tr></thead>
                    <tbody>
                        <?php while($b = $bannedUsers->fetch_assoc()): ?>
                        <tr><td style="color:#ff4444;"><?= htmlspecialchars($b['username']) ?></td><td><?= htmlspecialchars($b['email']) ?></td><td style="font-family: monospace; color:#aaa;"><?= htmlspecialchars($b['ip_address'] ?? 'Ismeretlen') ?></td> <td><?= date('Y.m.d', strtotime($b['reg_date'])) ?></td>
                            <td><form method="POST"><input type="hidden" name="action" value="unban_user"><input type="hidden" name="user_id" value="<?= $b['id'] ?>"><button type="submit" class="btn btn-unban"><i class="fas fa-unlock"></i> Visszaengedés</button></form></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                </div>
                <?php else: ?><p style="text-align:center; color:#888;">Nincs kitiltott felhasználó.</p><?php endif; ?>
            </div>
        </div>

        <div id="tab-activity" class="tab-content">
            <header><h2>Oldal Aktivitás (Napló)</h2></header>
            <div class="card">
                <?php while($log = $logs->fetch_assoc()): ?>
                <div class="log-item">
                    <div><span style="color:#fff; font-weight:bold;"><?= htmlspecialchars($log['username']) ?></span><span style="color:#888; margin:0 5px;">&bull;</span><span class="log-action"><?= htmlspecialchars($log['action']) ?></span><div style="color:#aaa; margin-top:4px;"><?= htmlspecialchars($log['details']) ?></div></div>
                    <div style="text-align:right;"><div class="log-time"><?= date('H:i', strtotime($log['created_at'])) ?></div><div style="font-size:0.7rem; color:#444;"><?= date('M d', strtotime($log['created_at'])) ?></div></div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>

        <div id="tab-chat-archive" class="tab-content">
            <header><h2>Archivált Verseny Chatek</h2></header>
            <div class="card" style="max-height: 70vh; overflow-y: auto;">
                <?php if($chatArchives->num_rows > 0): ?>
                <div style="overflow-x: auto;">
                <table>
                    <thead><tr><th>Dátum</th><th>Felhasználó</th><th>Üzenet</th></tr></thead>
                    <tbody>
                        <?php while($c = $chatArchives->fetch_assoc()): ?>
                        <tr>
                            <td style="color:#888; font-size:0.8rem;"><?= date('Y.m.d H:i', strtotime($c['sent_at'])) ?></td>
                            <td><strong style="color: #fff;"><?= htmlspecialchars($c['username']) ?></strong> <span style="font-size:0.7rem; color:#666;">(<?= htmlspecialchars($c['fav_team']) ?>)</span></td>
                            <td style="color:#ddd;"><?= htmlspecialchars($c['message']) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                </div>
                <?php else: ?>
                    <p style="text-align:center; color:#888;">Nincsenek még lementett chatek.</p>
                <?php endif; ?>
            </div>
        </div>

    </main>

    <script>
        function showTab(tabName) {
            document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.menu-btn').forEach(el => el.classList.remove('active'));
            document.getElementById('tab-' + tabName).classList.add('active');
            event.currentTarget.classList.add('active');
            localStorage.setItem('activeAdminTab', tabName);
            
            // Close sidebar on mobile after selecting a tab
            if (window.innerWidth <= 992) {
                document.getElementById('sidebar').classList.remove('open');
            }
        }

        document.addEventListener("DOMContentLoaded", () => {
            const savedTab = localStorage.getItem('activeAdminTab') || 'race';
            const btn = document.querySelector(`button[onclick="showTab('${savedTab}')"]`);
            if(btn) btn.click();
        });

        async function controlRace(action) {
            let confirmMsg = "";
            if (action === 'start') confirmMsg = "Biztosan INDÍTOD a versenyt? A korábbi eredmények törlődnek!";
            else if (action === 'stop') confirmMsg = "Biztosan SZÜNETELTETED a versenyt?";
            else if (action === 'hard_stop') confirmMsg = "VIGYÁZAT: Ez végleg leállítja a versenyt, lementi a chatet, és kidobja az összes jelenlegi nézőt. Biztos folytatod?";
                
            if(confirm(confirmMsg)) {
                try {
                    const response = await fetch('../race/race_api.php?action=' + action);
                    const data = await response.json();
                    alert(data.msg);
                    location.reload(); 
                } catch (error) {
                    alert("Hiba történt: " + error);
                }
            }
        }
        
        // HAMBURGER MENU TOGGLE
        const hamburgerBtn = document.getElementById('hamburgerBtn');
        const sidebar = document.getElementById('sidebar');
        
        if (hamburgerBtn && sidebar) {
            hamburgerBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                sidebar.classList.toggle('open');
            });
            
            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', function(event) {
                if (window.innerWidth <= 992) {
                    if (!sidebar.contains(event.target) && !hamburgerBtn.contains(event.target)) {
                        sidebar.classList.remove('open');
                    }
                }
            });
            
            // Close sidebar when a menu item is clicked (on mobile)
            const menuBtns = document.querySelectorAll('.menu-btn');
            menuBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    if (window.innerWidth <= 992) {
                        sidebar.classList.remove('open');
                    }
                });
            });
        }
    </script>
</body>
</html>