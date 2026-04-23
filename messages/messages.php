<?php
// /f1fanclub/messages/messages.php
session_start();

$DB_HOST = "localhost";
$DB_USER = "swmjndga_swmjndga";
$DB_PASS = "Teszt1234!";
$DB_NAME = "swmjndga_f1adat";

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) { die("Adatbázis hiba!"); }

$isLoggedIn = isset($_SESSION['username']);
$username = $isLoggedIn ? $_SESSION['username'] : null;

if (!$isLoggedIn) {
    header("Location: ../login/login.html");
    exit;
}

function getTeamColor($team) {
    switch ($team) {
        case 'Red Bull': return '#1E41FF'; case 'Ferrari': return '#DC0000'; case 'Mercedes': return '#00D2BE';
        case 'McLaren': return '#FF8700'; case 'Aston Martin': return '#006F62'; case 'Alpine': return '#0090FF';
        case 'Williams': return '#00A0DE'; case 'RB': return '#2b2bff'; case 'Audi': return '#e3000f';
        case 'Haas F1 Team': return '#B6BABD'; case 'Cadillac': return '#B6BABD'; default: return '#ffffff';
    }
}

$profile_image = null; $teamColor = '#ffffff'; $isAdmin = false;
$stmt = $conn->prepare("SELECT profile_image, fav_team, role FROM users WHERE username=?");
$stmt->bind_param("s", $username); $stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$profile_image = $row['profile_image'] ?? null; 
$teamColor = getTeamColor($row['fav_team'] ?? null);
$isAdmin = !empty($row['role']) && $row['role'] === 'admin';
$stmt->close();
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
<<<<<<< HEAD
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes, maximum-scale=1.0, viewport-fit=cover">
    <title>Üzenetek - F1 Fan Club</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/svg+xml" href="https://upload.wikimedia.org/wikipedia/commons/3/33/F1.svg">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            background: #0a0a0a;
            color: white;
            font-family: 'Poppins', sans-serif;
            height: 100vh;
            overflow: hidden;
            padding-top: 80px;
        }
        
        body::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 20% 50%, rgba(225, 6, 0, 0.05) 0%, transparent 50%),
                        radial-gradient(circle at 80% 80%, rgba(225, 6, 0, 0.05) 0%, transparent 50%);
            pointer-events: none;
            z-index: -1;
        }
        
        .bg-lines {
            position: fixed;
            width: 200%;
            height: 200%;
            background: repeating-linear-gradient(60deg, rgba(225, 6, 0, 0.03) 0px, rgba(225, 6, 0, 0.03) 2px, transparent 2px, transparent 10px);
            animation: slide 10s linear infinite;
            opacity: 0.3;
            z-index: -1;
            top: 0;
            left: 0;
        }
        
        @keyframes slide { from { transform: translateX(0); } to { transform: translateX(-200px); } }
        
        /* ===== HEADER ===== */
        header {
            background-color: #0a0a0a;
            border-bottom: 2px solid rgba(225, 6, 0, 0.3);
            padding: 0 40px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            box-shadow: 0 4px 20px rgba(0,0,0,0.5);
        }
        
        .left-header { display: flex; align-items: center; }
        
        .logo-title {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 1.5rem;
            font-weight: 800;
            color: #fff;
            text-transform: uppercase;
            letter-spacing: 1px;
            text-decoration: none;
        }
        
        .logo-title img { width: 40px; height: auto; filter: brightness(0) invert(1); }
        
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
        }
        
        .hamburger:hover { color: #e10600; }
        
        nav {
            display: flex;
            gap: 5px;
            margin: 0 20px;
            align-items: center;
        }
        
        nav a {
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            padding: 8px 16px;
            border-radius: 4px;
            color: #ffffff !important;
            text-decoration: none;
            transition: all 0.2s ease;
            letter-spacing: 0.5px;
            opacity: 0.9;
            white-space: nowrap;
        }
        
        nav a:hover { color: #e10600 !important; opacity: 1; background: rgba(225, 6, 0, 0.1); }
        nav a.active { color: #e10600 !important; opacity: 1; font-weight: 700; background: rgba(225, 6, 0, 0.15); }
        
        /* Auth / Dropdown */
        .auth .btn {
            display: inline-block;
            padding: 8px 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            color: #fff;
            background-color: transparent;
            border: 1px solid rgba(225, 6, 0, 0.5);
            border-radius: 30px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            letter-spacing: 0.5px;
        }
        
        .auth .btn:hover {
            background-color: #e10600;
            border-color: #e10600;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(225, 6, 0, 0.4);
        }
        
        .dropdown-container { position: relative; display: inline-block; }
        
        .welcome {
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.9rem;
            padding: 5px 12px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 30px;
            border: 1px solid rgba(225, 6, 0, 0.2);
            transition: all 0.2s ease;
        }
        
        .welcome:hover { background: rgba(225, 6, 0, 0.15); border-color: #e10600; }
        
        .welcome img.avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #e10600;
        }
        
        .welcome-text { color: #ccc; }
        .welcome-text span { font-weight: 700; }
        
=======
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Üzenetek - F1 Fan Club</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/svg+xml" href="https://upload.wikimedia.org/wikipedia/commons/3/33/F1.svg">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/messages.css?v=<?= time() ?>">
    <style>
        html, body { margin: 0; padding: 0; height: 100vh; overflow: hidden; }
        .app-container { display: flex; flex-direction: column; height: 100vh; }
        header { flex-shrink: 0; }
        
        /* ATOMBIZTOS KERESŐ STÍLUS BELEÉGETVE A HTML-BE */
        .sidebar-header-safe {
            padding: 20px;
            background: #111118;
            border-bottom: 1px solid #2a2a35;
            min-height: 85px; 
            flex-shrink: 0; 
            position: relative;
            display: block;
            width: 100%;
            box-sizing: border-box;
        }
        .sidebar-header-safe input {
            width: 100%;
            background: #202028;
            border: 1px solid #333;
            color: #fff;
            padding: 10px 15px;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            outline: none;
            box-sizing: border-box;
            height: 45px;
        }
        .sidebar-header-safe input:focus { border-color: #e10600; }

        /* Dropdown menu styles */
        .dropdown-container {
            position: relative;
            display: inline-block;
        }
        
        .welcome {
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .welcome:hover {
            background: rgba(225, 6, 0, 0.15);
            border-color: #e10600;
        }
        
>>>>>>> f81424192996985be2da559c0f9a2c1f13f5eb7f
        .dropdown-menu-modern {
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            background: linear-gradient(145deg, #111111, #1a1a1f);
            backdrop-filter: blur(12px);
            border-radius: 16px;
            border: 1px solid rgba(225, 6, 0, 0.4);
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.6);
            min-width: 240px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-8px);
<<<<<<< HEAD
            transition: all 0.2s;
=======
            transition: all 0.2s cubic-bezier(0.2, 0.9, 0.4, 1.1);
>>>>>>> f81424192996985be2da559c0f9a2c1f13f5eb7f
            z-index: 1050;
        }
        
        .dropdown-container.open .dropdown-menu-modern {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
        .dropdown-menu-modern a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            color: #eee;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
<<<<<<< HEAD
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        
        .dropdown-menu-modern a:last-child { border-bottom: none; }
        .dropdown-menu-modern a:hover { background: rgba(225, 6, 0, 0.2); color: white; padding-left: 24px; }
        .dropdown-menu-modern i { width: 24px; color: #e10600; }
        .dropdown-arrow-icon { margin-left: 6px; font-size: 0.7rem; color: #e10600; }
        .dropdown-container.open .dropdown-arrow-icon { transform: rotate(180deg); }
=======
            transition: all 0.2s;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        
        .dropdown-menu-modern a:last-child {
            border-bottom: none;
        }
        
        .dropdown-menu-modern a:hover {
            background: rgba(225, 6, 0, 0.2);
            color: white;
            padding-left: 24px;
        }
        
        .dropdown-menu-modern i {
            width: 24px;
            color: #e10600;
            font-size: 1.1rem;
        }
        
        .dropdown-divider {
            height: 1px;
            background: rgba(255, 255, 255, 0.1);
            margin: 6px 0;
        }
        
        .dropdown-arrow-icon {
            margin-left: 6px;
            font-size: 0.7rem;
            transition: transform 0.2s;
            color: #e10600;
        }
        
        .dropdown-container.open .dropdown-arrow-icon {
            transform: rotate(180deg);
        }
>>>>>>> f81424192996985be2da559c0f9a2c1f13f5eb7f
        
        .admin-badge {
            position: absolute;
            right: 15px;
            background: #e10600;
            color: white;
            font-size: 0.65rem;
            padding: 2px 8px;
            border-radius: 20px;
            font-weight: 600;
        }
<<<<<<< HEAD
        
        .clickable-user { cursor: pointer; }
        .clickable-user:hover { opacity: 0.8; }
        
        /* ===== MESSENGER LAYOUT ===== */
        .messenger-container {
            display: flex;
            height: calc(100vh - 80px);
            overflow: hidden;
        }
        
        .msg-sidebar {
            width: 320px;
            background: #111118;
            border-right: 1px solid #2a2a35;
            display: flex;
            flex-direction: column;
        }
        
        .sidebar-header {
            padding: 20px;
            background: #111118;
            border-bottom: 1px solid #2a2a35;
        }
        
        .sidebar-header input {
            width: 100%;
            background: #202028;
            border: 1px solid #333;
            color: #fff;
            padding: 12px 15px;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 0.9rem;
            outline: none;
        }
        
        .sidebar-header input:focus { border-color: #e10600; }
        
        .search-wrapper {
            position: relative;
            padding: 15px 20px;
            border-bottom: 1px solid #2a2a35;
        }
        
        .search-wrapper input {
            width: 100%;
            background: #202028;
            border: 1px solid #333;
            color: #fff;
            padding: 12px 15px;
            border-radius: 20px;
            font-family: 'Poppins', sans-serif;
            font-size: 0.9rem;
            outline: none;
        }
        
        .search-wrapper input:focus { border-color: #e10600; }
        
        .search-results {
            position: absolute;
            top: calc(100% - 5px);
            left: 20px;
            right: 20px;
            background: #1a1a1f;
            border: 1px solid #e10600;
            border-radius: 12px;
            max-height: 250px;
            overflow-y: auto;
            z-index: 100;
            display: none;
            box-shadow: 0 10px 20px rgba(0,0,0,0.5);
        }
        
        .search-result-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 15px;
            cursor: pointer;
            transition: background 0.2s;
            border-bottom: 1px solid #2a2a35;
        }
        
        .search-result-item:last-child { border-bottom: none; }
        .search-result-item:hover { background: #252530; }
        .search-result-item img { width: 35px; height: 35px; border-radius: 50%; border: 2px solid; }
        
        .friend-list-header {
            padding: 15px 20px 5px;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #888;
        }
        
        .friend-list {
            flex: 1;
            overflow-y: auto;
            padding: 0 10px;
        }
        
        .friend-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 10px;
            cursor: pointer;
            transition: background 0.2s;
            border-radius: 8px;
            margin-bottom: 2px;
        }
        
        .friend-item:hover { background: #1a1a22; }
        .friend-item.active { background: rgba(225, 6, 0, 0.15); border-left: 3px solid #e10600; }
        
        .friend-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 2px solid;
            object-fit: cover;
        }
        
        .friend-info { flex: 1; }
        .friend-name { font-weight: 600; font-size: 0.95rem; }
        
        /* Chat area */
        .chat-area {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: #0a0a0a;
        }
        
        .chat-header {
            padding: 15px 25px;
            background: #111118;
            border-bottom: 1px solid #2a2a35;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .back-btn {
            display: none;
            background: none;
            border: none;
            color: #e10600;
            font-size: 1.3rem;
            cursor: pointer;
            padding: 5px;
            margin-right: 5px;
        }
        
        .chat-header h2 { font-size: 1.1rem; font-weight: 600; }
        
        .chat-box {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
        }
        
        .empty-chat {
            margin: auto;
            text-align: center;
            color: #666;
        }
        
        .empty-chat i { font-size: 3.5rem; margin-bottom: 20px; color: #e10600; opacity: 0.5; }
        .empty-chat h3 { font-size: 1.3rem; margin-bottom: 10px; color: #888; }
        .empty-chat p { font-size: 0.9rem; }
        
        .message-row { display: flex; flex-direction: column; margin-bottom: 15px; }
        .message-row.me { align-items: flex-end; }
        .message-row.them { align-items: flex-start; }
        
        .message-bubble {
            max-width: 60%;
            padding: 12px 16px;
            border-radius: 18px;
            font-size: 0.9rem;
            word-break: break-word;
        }
        
        .message-row.me .message-bubble {
            background: #e10600;
            color: white;
            border-bottom-right-radius: 4px;
        }
        
        .message-row.them .message-bubble {
            background: #202028;
            color: white;
            border-bottom-left-radius: 4px;
        }
        
        .message-time {
            font-size: 0.7rem;
            color: #888;
            margin-top: 4px;
        }
        
        .chat-input-area {
            padding: 15px 25px;
            background: #111118;
            border-top: 1px solid #2a2a35;
            display: flex;
            gap: 10px;
        }
        
        .chat-input-container {
            flex: 1;
            display: flex;
            align-items: center;
            background: #202028;
            border-radius: 25px;
            padding: 5px 15px;
        }
        
        .icon-btn {
            background: none;
            border: none;
            color: #888;
            font-size: 1.2rem;
            cursor: pointer;
            padding: 8px;
            transition: color 0.2s;
        }
        
        .icon-btn:hover { color: #e10600; }
        
        #msgInput {
            flex: 1;
            background: none;
            border: none;
            color: white;
            padding: 10px;
            font-family: 'Poppins', sans-serif;
            font-size: 0.95rem;
            outline: none;
        }
        
        .send-btn {
            background: #e10600;
            border: none;
            color: white;
            width: 48px;
            height: 48px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 1.2rem;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .send-btn:hover { background: #ff1a00; transform: scale(1.05); }
        
        /* Modal */
=======

        .clickable-user {
            cursor: pointer;
            transition: opacity 0.2s;
        }
        
        .clickable-user:hover {
            opacity: 0.8;
        }

        /* User Modal Styles */
>>>>>>> f81424192996985be2da559c0f9a2c1f13f5eb7f
        .user-modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
<<<<<<< HEAD
            background: rgba(0,0,0,0.85);
=======
            background: rgba(0, 0, 0, 0.85);
>>>>>>> f81424192996985be2da559c0f9a2c1f13f5eb7f
            backdrop-filter: blur(8px);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }
<<<<<<< HEAD
        
=======

>>>>>>> f81424192996985be2da559c0f9a2c1f13f5eb7f
        .user-modal-content {
            background: linear-gradient(145deg, #111, #1a1a1a);
            width: 320px;
            border-radius: 24px;
            border: 1px solid #e10600;
            padding: 20px;
            position: relative;
<<<<<<< HEAD
            text-align: center;
        }
        
=======
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.6);
            animation: popIn 0.3s ease;
            text-align: center;
        }

        @keyframes popIn {
            from { transform: scale(0.8); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

>>>>>>> f81424192996985be2da559c0f9a2c1f13f5eb7f
        .user-modal-close {
            position: absolute;
            top: 12px;
            right: 15px;
            background: none;
            border: none;
            color: #888;
            font-size: 1.3rem;
            cursor: pointer;
        }
<<<<<<< HEAD
        
        .user-modal-close:hover { color: #e10600; }
        
=======

        .user-modal-close:hover {
            color: #e10600;
        }

>>>>>>> f81424192996985be2da559c0f9a2c1f13f5eb7f
        .user-modal-header img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            border: 3px solid #e10600;
            object-fit: cover;
            margin-bottom: 10px;
        }
<<<<<<< HEAD
        
        .user-modal-header h3 { margin-bottom: 5px; }
        
        .modal-role {
            display: inline-block;
            font-size: 0.7rem;
            background: rgba(255,255,255,0.1);
            padding: 2px 10px;
            border-radius: 20px;
            color: #aaa;
        }
        
        .user-modal-body {
            margin: 15px 0;
            background: rgba(0,0,0,0.3);
=======

        .modal-role {
            display: inline-block;
            font-size: 0.7rem;
            background: rgba(255, 255, 255, 0.1);
            padding: 2px 10px;
            border-radius: 20px;
            margin-top: 5px;
            color: #aaa;
        }

        .user-modal-body {
            margin: 15px 0;
            background: rgba(0, 0, 0, 0.3);
>>>>>>> f81424192996985be2da559c0f9a2c1f13f5eb7f
            padding: 12px;
            border-radius: 16px;
            text-align: left;
        }
<<<<<<< HEAD
        
        .user-modal-body p { margin: 8px 0; color: #ccc; font-size: 0.85rem; }
        .user-modal-body i { width: 20px; color: #888; }
        
        .user-modal-footer { display: flex; gap: 10px; }
        
=======

        .user-modal-footer {
            display: flex;
            gap: 10px;
        }

>>>>>>> f81424192996985be2da559c0f9a2c1f13f5eb7f
        .user-modal-footer button {
            flex: 1;
            padding: 10px;
            border: none;
            border-radius: 40px;
            cursor: pointer;
            font-weight: 600;
<<<<<<< HEAD
            font-size: 0.8rem;
=======
            transition: all 0.2s;
>>>>>>> f81424192996985be2da559c0f9a2c1f13f5eb7f
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }
<<<<<<< HEAD
        
        .btn-add-friend { background: #333; color: white; }
        .btn-send-msg { background: #e10600; color: white; }
        
        /* ===== MOBILE ===== */
        @media (max-width: 992px) {
            .hamburger { display: block; }
            .left-header { display: none; }
            
            nav {
                display: none;
                position: fixed;
                top: 80px;
                left: 0;
                right: 0;
                background: #0a0a0a;
                border-bottom: 2px solid #e10600;
                flex-direction: column;
                gap: 0;
                margin: 0;
                padding: 10px 0;
                z-index: 1000;
            }
            
            nav.open { display: flex; }
            
            nav a {
                padding: 15px 20px;
                text-align: center;
                border-bottom: 1px solid rgba(255,255,255,0.1);
                width: 100%;
            }
            
            header { padding: 0 20px; }
        }
        
        @media (max-width: 768px) {
            body { overflow-y: auto; }
            
            .messenger-container {
                flex-direction: column;
                height: auto;
                min-height: calc(100vh - 80px);
            }
            
            .msg-sidebar {
                width: 100%;
                max-height: 45vh;
                border-right: none;
                border-bottom: 2px solid #e10600;
            }
            
            .search-wrapper { padding: 12px 15px; }
            
            .friend-list-header { padding: 12px 15px 5px; }
            
            .friend-list { padding: 0 5px; }
            
            .friend-item { padding: 10px; }
            
            .chat-area {
                height: 55vh;
            }
            
            .back-btn { display: block; }
            
            .chat-header { padding: 12px 15px; }
            
            .chat-header h2 { font-size: 1rem; }
            
            .chat-box { padding: 15px; }
            
            .message-bubble {
                max-width: 85% !important;
                padding: 10px 12px;
                font-size: 0.9rem;
            }
            
            .chat-input-area { padding: 12px 15px; }
            
            #msgInput { font-size: 16px; }
            
            .send-btn { width: 44px; height: 44px; }
            
            .empty-chat i { font-size: 3rem; }
            .empty-chat h3 { font-size: 1.1rem; }
            
            .auth { margin-left: 0; }
            .welcome { padding: 3px 8px; }
            .welcome-text { font-size: 0.8rem; }
            
            .search-results {
                left: 15px;
                right: 15px;
            }
        }
        
        @media (max-width: 480px) {
            .welcome-text span {
                max-width: 70px;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
            
            .welcome img.avatar { width: 28px !important; height: 28px !important; }
            
            .friend-avatar { width: 36px; height: 36px; }
            .friend-name { font-size: 0.9rem; }
            
            .message-bubble { max-width: 90% !important; }
            
            .dropdown-menu-modern {
                position: fixed;
                top: 70px;
                right: 10px;
                left: 10px;
                min-width: auto;
            }
        }
        
        @media (max-width: 360px) {
            .auth .btn { padding: 5px 10px; font-size: 0.65rem; }
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
    <div class="bg-lines"></div>

    <header>
        <div class="left-header">
            <a href="/f1fanclub/index.php" class="logo-title">
                <img src="https://upload.wikimedia.org/wikipedia/commons/3/33/F1.svg" alt="F1 Logo">
                <span>Fan Club</span>
            </a>
        </div>

        <button class="hamburger" id="hamburgerBtn">
            <i class="fas fa-bars"></i>
        </button>

        <nav id="mainNav">
            <a href="/f1fanclub/index.php">Kezdőlap</a>
            <a href="/f1fanclub/Championship/championship.php">Bajnokság</a>
            <a href="/f1fanclub/teams/teams.php">Csapatok</a>
            <a href="/f1fanclub/drivers/drivers.php">Versenyzők</a>
            <a href="/f1fanclub/news/feed.php">Paddock</a>
            <a href="/f1fanclub/pitwall/pitwall.php"><i class="fas fa-trophy" style="margin-right: 5px;"></i> A Fal</a>
        </nav>

        <?php if ($isLoggedIn): ?>
            <div class="dropdown-container" id="userDropdownContainer">
                <div class="auth">
                    <div class="welcome">
                        <?php if ($profile_image): ?>
                            <img src="/f1fanclub/uploads/<?php echo htmlspecialchars($profile_image); ?>" class="avatar clickable-user"
                                alt="Profilkép" onclick="openUserProfile('<?php echo htmlspecialchars(addslashes($username)); ?>')"
                                style="border-color: <?php echo htmlspecialchars($teamColor); ?>;">
=======

        .btn-add-friend {
            background: #333;
            color: white;
        }

        .btn-add-friend:hover {
            background: #444;
        }

        .btn-send-msg {
            background: #e10600;
            color: white;
        }

        .btn-send-msg:hover {
            background: #b00500;
        }
    </style>
</head>
<body>

<div class="app-container">
    <header>
        <div class="left-header">
            <div class="logo-title">
                <img src="https://upload.wikimedia.org/wikipedia/commons/3/33/F1.svg" alt="F1 Logo" style="height:40px; filter: brightness(0) invert(1);">
                <span>Fan Club</span>
            </div>
        </div>

        <nav style="margin: 20px 0; display: flex; align-items: center; gap: 20px;">
            <a href="/f1fanclub/index.php" style="color:white; margin:0 10px;">Vissza a Főoldalra</a>
            
            <div style="position: relative;">
                <input type="text" id="userSearchInput" placeholder="Felhasználó keresése..." autocomplete="off" oninput="searchUsers(this.value)" 
                       style="width: 250px; background: #202028; border: 1px solid #333; color: #fff; padding: 8px 15px; border-radius: 20px; font-family: 'Poppins', sans-serif; outline: none;">
                <div id="searchResults" class="search-results" style="top: calc(100% + 10px); left: 0; right: 0; min-width: 250px;"></div>
            </div>
        </nav>

        <!-- DROPDOWN MENU - Updated from index.php -->
        <?php if ($isLoggedIn): ?>
            <div class="dropdown-container" id="userDropdownContainer">
                <div class="auth">
                    <div class="welcome" style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                        <?php if ($profile_image): ?>
                            <img src="/f1fanclub/uploads/<?php echo htmlspecialchars($profile_image); ?>" class="avatar clickable-user"
                                alt="Profilkép" onclick="openUserProfile('<?php echo htmlspecialchars(addslashes($username)); ?>')"
                                style="width:35px; height:35px; border-radius:50%; object-fit: cover; border: 2px solid <?php echo htmlspecialchars($teamColor); ?>;">
>>>>>>> f81424192996985be2da559c0f9a2c1f13f5eb7f
                        <?php endif; ?>
                        <span class="welcome-text">
                            <span class="clickable-user" onclick="openUserProfile('<?php echo htmlspecialchars(addslashes($username)); ?>')"
                                style="color: <?php echo htmlspecialchars($teamColor); ?>; font-weight:bold;"><?php echo htmlspecialchars($username); ?></span>
                        </span>
                        <i class="fas fa-chevron-down dropdown-arrow-icon"></i>
                    </div>
                </div>
                
                <div class="dropdown-menu-modern">
<<<<<<< HEAD
                    <a href="/f1fanclub/profile/profile.php"><i class="fas fa-user-circle"></i> Profilom</a>
                    <a href="/f1fanclub/messages/messages.php"><i class="fas fa-envelope"></i> Üzenetek</a>
=======
                    <a href="/f1fanclub/profile/profile.php">
                        <i class="fas fa-user-circle"></i> Profilom
                    </a>
                    <a href="/f1fanclub/messages/messages.php">
                        <i class="fas fa-envelope"></i> Üzenetek
                    </a>
>>>>>>> f81424192996985be2da559c0f9a2c1f13f5eb7f
                    <?php if ($isAdmin): ?>
                        <a href="/f1fanclub/admin/admin.php" style="position: relative;">
                            <i class="fas fa-shield-alt"></i> Admin Panel
                            <span class="admin-badge">ADMIN</span>
                        </a>
                    <?php endif; ?>
                    <div class="dropdown-divider"></div>
<<<<<<< HEAD
                    <a href="/f1fanclub/logout/logout.php"><i class="fas fa-sign-out-alt"></i> Kijelentkezés</a>
=======
                    <a href="/f1fanclub/logout/logout.php">
                        <i class="fas fa-sign-out-alt"></i> Kijelentkezés
                    </a>
>>>>>>> f81424192996985be2da559c0f9a2c1f13f5eb7f
                </div>
            </div>
        <?php else: ?>
            <div class="auth">
                <a href="/f1fanclub/register/register.html" class="btn">Regisztráció</a>
                <a href="/f1fanclub/login/login.html" class="btn">Bejelentkezés</a>
            </div>
        <?php endif; ?>
    </header>

<<<<<<< HEAD
    <div class="messenger-container">
        <div class="msg-sidebar">
            <!-- User search above friend list -->
            <div class="search-wrapper">
                <input type="text" id="userSearchInput" placeholder="Felhasználó keresése..." autocomplete="off" oninput="searchUsers(this.value)">
                <div id="searchResults" class="search-results"></div>
            </div>
            
            <div class="sidebar-header">
                <input type="text" id="sidebarSearchInput" placeholder="Barát keresése..." oninput="filterFriends(this.value)">
            </div>
            
            <div class="friend-list-header">
                <span>BESZÉLGETÉSEK</span>
            </div>
            
            <div class="friend-list" id="friendList">
                <div style="color:#666; text-align:center; padding:20px;">Betöltés...</div>
            </div>
        </div>

        <div class="chat-area">
            <div class="chat-header" id="chatHeader" style="display: none;">
                <button class="back-btn" onclick="backToFriends()"><i class="fas fa-arrow-left"></i></button>
=======
    <div class="messenger-wrapper">
        
        <!-- BAL OLDALI SÁV -->
        <div class="msg-sidebar">
            
            
            <div class="friend-list" id="friendList">
                <div style="color:#666; text-align:center; margin-top:20px;">Betöltés...</div>
            </div>
            
        </div>

        <!-- JOBB OLDALI CHAT SÁV -->
        <div class="chat-area">
            <div class="chat-header" id="chatHeader" style="display: none;">
>>>>>>> f81424192996985be2da559c0f9a2c1f13f5eb7f
                <img src="" id="activeFriendImg" style="width:40px; height:40px; border-radius:50%; object-fit:cover;">
                <h2 id="activeFriendName">Válassz partnert</h2>
            </div>
            
            <div class="chat-box" id="chatBox">
                <div class="empty-chat">
                    <i class="fas fa-comments"></i>
                    <h3>Nincs kiválasztott beszélgetés</h3>
<<<<<<< HEAD
                    <p>Válassz egy barátot a bal oldali listából!</p>
=======
                    <p>Válassz egy barátot a bal oldali listából, hogy elkezdj csevegni!</p>
>>>>>>> f81424192996985be2da559c0f9a2c1f13f5eb7f
                </div>
            </div>

            <div class="chat-input-area" id="chatInputArea" style="display: none;">
                <div class="chat-input-container">
                    <button class="icon-btn" onclick="alert('Képfeltöltés funkció hamarosan...')"><i class="fas fa-plus-circle"></i></button>
                    <input type="text" id="msgInput" placeholder="Írj egy üzenetet..." autocomplete="off" onkeypress="if(event.key === 'Enter') sendMessage()">
                    <button class="icon-btn" onclick="alert('Emojik hamarosan...')"><i class="fas fa-smile"></i></button>
                </div>
                <button class="send-btn" onclick="sendMessage()"><i class="fas fa-paper-plane"></i></button>
            </div>
        </div>
    </div>
<<<<<<< HEAD

    <!-- User Profile Modal -->
    <div id="userProfileModal" class="user-modal-overlay" onclick="closeUserProfile(event)">
        <div class="user-modal-content" onclick="event.stopPropagation()">
            <button class="user-modal-close" onclick="closeUserProfile(event)">&times;</button>
            <div class="user-modal-header">
                <img id="modalProfileImg" src="" alt="Avatar">
                <h3 id="modalUsername">Felhasználónév</h3>
                <span id="modalRole" class="modal-role">Szerepkör</span>
            </div>
            <div class="user-modal-body">
                <p><i class="fas fa-flag-checkered"></i> <strong>Csapat:</strong> <span id="modalTeam">Csapat</span></p>
                <p><i class="far fa-calendar-alt"></i> <strong>Regisztrált:</strong> <span id="modalRegDate">Dátum</span></p>
            </div>
            <div class="user-modal-footer">
                <button id="modalFriendBtn" class="btn-add-friend" onclick="handleFriendAction()"><i class="fas fa-user-plus"></i> Barátnak jelölés</button>
                <button class="btn-send-msg" onclick="startChatFromModal()"><i class="fas fa-comment"></i> Üzenet</button>
            </div>
        </div>
    </div>

    <script>
        let activePartner = null;
        let messageCount = 0;
        let allFriends = [];
        const currentUser = <?= json_encode($username) ?>;

        function makeSafeStr(str) {
            return str.replace(/\\/g, '\\\\').replace(/'/g, "\\'");
        }

        function loadFriends() {
            fetch('pm_api.php?action=get_friends')
            .then(r => r.json())
            .then(data => {
                if(data.success) {
                    allFriends = data.friends;
                    renderFriendList(allFriends);
                }
            });
        }
        
        function renderFriendList(friends) {
            const list = document.getElementById('friendList');
            if(friends.length === 0) {
                list.innerHTML = '<div style="color:#666; text-align:center; padding:20px;">Még nincsenek barátaid.</div>';
                return;
            }
            list.innerHTML = '';
            friends.forEach(f => {
                const isActive = f.friend_name === activePartner ? 'active' : '';
                list.innerHTML += `
                    <div class="friend-item ${isActive}" onclick="openChat('${makeSafeStr(f.friend_name)}', '${f.profile_image}', '${f.color}')">
                        <img src="${f.profile_image}" class="friend-avatar" style="border-color: ${f.color}">
                        <div class="friend-info">
                            <div class="friend-name" style="color: ${f.color}">${f.friend_name}</div>
                        </div>
                    </div>
                `;
            });
        }
        
        function filterFriends(query) {
            const filtered = allFriends.filter(f => 
                f.friend_name.toLowerCase().includes(query.toLowerCase())
            );
            renderFriendList(filtered);
        }

        function openChat(partnerName, partnerImg, partnerColor) {
            activePartner = partnerName;
            messageCount = 0;
            
            document.getElementById('chatHeader').style.display = 'flex';
            document.getElementById('chatInputArea').style.display = 'flex';
            document.getElementById('activeFriendName').innerText = partnerName;
            document.getElementById('activeFriendName').style.color = partnerColor;
            document.getElementById('activeFriendImg').src = partnerImg;
            document.getElementById('activeFriendImg').style.border = `2px solid ${partnerColor}`;

            renderFriendList(allFriends);
            loadMessages(true);
            
            // On mobile, hide sidebar when chat is open
            if (window.innerWidth <= 768) {
                document.querySelector('.msg-sidebar').style.display = 'none';
                document.querySelector('.chat-area').style.display = 'flex';
            }
        }
        
        function backToFriends() {
            if (window.innerWidth <= 768) {
                document.querySelector('.msg-sidebar').style.display = 'block';
                document.querySelector('.chat-area').style.display = 'flex';
            }
        }

        function loadMessages() {
            if (!activePartner) return;
            fetch('pm_api.php?action=get_messages&partner=' + encodeURIComponent(activePartner))
            .then(r => r.json())
            .then(data => {
                if(data.success) {
                    if (data.messages.length !== messageCount) {
                        const box = document.getElementById('chatBox');
                        box.innerHTML = '';
                        if(data.messages.length === 0) {
                            box.innerHTML = '<div style="margin:auto; color:#666;">Itt kezdődik a beszélgetés. Integess! 👋</div>';
                        } else {
                            data.messages.forEach(m => {
                                const isMe = m.sender === currentUser;
                                const rowClass = isMe ? 'me' : 'them';
                                box.innerHTML += `
                                    <div class="message-row ${rowClass}">
                                        <div class="message-bubble">${m.message}</div>
                                        <div class="message-time">${m.time}</div>
                                    </div>
                                `;
                            });
                        }
                        messageCount = data.messages.length;
                        box.scrollTop = box.scrollHeight;
                    }
                }
            });
        }

        function sendMessage() {
            const input = document.getElementById('msgInput');
            const msg = input.value.trim();
            if (!msg || !activePartner) return;
            input.value = '';

            fetch('pm_api.php?action=send', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ receiver: activePartner, message: msg })
            }).then(() => loadMessages());
        }

        function searchUsers(query) {
            const resultsDiv = document.getElementById('searchResults');
            if (query.length < 3) {
                resultsDiv.style.display = 'none';
                return;
            }
            
            fetch('pm_api.php?action=search_users&term=' + encodeURIComponent(query))
            .then(r => r.json())
            .then(data => {
                if(data.success && data.users.length > 0) {
                    resultsDiv.innerHTML = '';
                    data.users.forEach(u => {
                        resultsDiv.innerHTML += `
                            <div class="search-result-item" onclick="openUserProfile('${makeSafeStr(u.username)}')">
                                <img src="${u.profile_image}" style="border-color: ${u.color}">
                                <span style="color: ${u.color}; font-weight: 600;">${u.username}</span>
                            </div>
                        `;
                    });
                    resultsDiv.style.display = 'block';
                } else {
                    resultsDiv.innerHTML = '<div style="padding:10px; color:#888; text-align:center;">Nincs találat</div>';
                    resultsDiv.style.display = 'block';
                }
            });
        }

        document.addEventListener('click', function(e) {
            if(!e.target.closest('.search-wrapper')) {
                document.getElementById('searchResults').style.display = 'none';
            }
        });

        let currentModalUser = "", currentFriendStatus = "";

        function openUserProfile(username) {
            document.getElementById('searchResults').style.display = 'none';
            document.getElementById('userSearchInput').value = '';

            fetch('/f1fanclub/profile/user_profile_api.php?username=' + encodeURIComponent(username))
            .then(r => r.json())
            .then(data => {
                if(data.success) {
                    currentModalUser = data.user.username;
                    currentFriendStatus = data.user.friendship_status;

                    document.getElementById('modalProfileImg').src = data.user.profile_image;
                    document.getElementById('modalProfileImg').style.borderColor = data.user.team_color;
                    document.getElementById('modalUsername').innerText = data.user.username;
                    document.getElementById('modalRole').innerText = data.user.role_name;
                    document.getElementById('modalTeam').innerText = data.user.fav_team || 'Nincs megadva';
                    document.getElementById('modalRegDate').innerText = data.user.reg_date;
                    
                    updateFriendButton(data.user.friendship_status);
                    document.getElementById('userProfileModal').style.display = 'flex';
                }
            });
        }

        function closeUserProfile(e) {
            if(e) e.stopPropagation();
            document.getElementById('userProfileModal').style.display = 'none';
        }

        function updateFriendButton(status) {
            const btn = document.getElementById('modalFriendBtn');
            if(!btn) return;
            btn.style.display = 'flex';
            
            if (status === 'self') btn.style.display = 'none';
            else if (status === 'none') btn.innerHTML = '<i class="fas fa-user-plus"></i> Barátnak jelölés';
            else if (status === 'pending_sent') btn.innerHTML = '<i class="fas fa-clock"></i> Elküldve';
            else if (status === 'pending_received') btn.innerHTML = '<i class="fas fa-check"></i> Elfogadás';
            else if (status === 'accepted') btn.innerHTML = '<i class="fas fa-user-minus"></i> Törlés';
        }

        function handleFriendAction() {
            let action = '';
            if (currentFriendStatus === 'none') action = 'add';
            else if (currentFriendStatus === 'pending_sent' || currentFriendStatus === 'accepted') action = 'remove';
            else if (currentFriendStatus === 'pending_received') action = 'accept';

            if(!action) return;

            fetch('/f1fanclub/profile/friend_api.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ action: action, target_user: currentModalUser })
            }).then(r => r.json()).then(data => {
                if(data.success) {
                    openUserProfile(currentModalUser);
                    loadFriends();
                }
            });
        }

        function startChatFromModal() {
            closeUserProfile();
            let img = document.getElementById('modalProfileImg').src;
            let color = document.getElementById('modalProfileImg').style.borderColor;
            openChat(currentModalUser, img, color);
        }

        // Hamburger menu
        document.addEventListener('DOMContentLoaded', function() {
            const hamburgerBtn = document.getElementById('hamburgerBtn');
            const mainNav = document.getElementById('mainNav');
            
            if (hamburgerBtn && mainNav) {
                hamburgerBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    mainNav.classList.toggle('open');
                });
                
                document.addEventListener('click', function(e) {
                    if (mainNav.classList.contains('open') && !mainNav.contains(e.target) && !hamburgerBtn.contains(e.target)) {
                        mainNav.classList.remove('open');
                    }
                });
                
                mainNav.querySelectorAll('a').forEach(link => {
                    link.addEventListener('click', () => mainNav.classList.remove('open'));
                });
            }
            
            // Dropdown menu
            const dropdownContainer = document.getElementById('userDropdownContainer');
            if (dropdownContainer) {
                const welcomeDiv = dropdownContainer.querySelector('.welcome');
                if (welcomeDiv) {
                    welcomeDiv.addEventListener('click', function(e) {
                        e.stopPropagation();
                        dropdownContainer.classList.toggle('open');
                    });
                }
                
                document.addEventListener('click', function(e) {
                    if (!dropdownContainer.contains(e.target)) {
                        dropdownContainer.classList.remove('open');
                    }
                });
            }
        });

        loadFriends();
        setInterval(() => {
            loadMessages();
            if(!activePartner) loadFriends();
        }, 2000);
    </script>
=======
</div>

<!-- POP-UP ABLAK -->
<div id="userProfileModal" class="user-modal-overlay" onclick="closeUserProfile(event)">
    <div class="user-modal-content" onclick="event.stopPropagation()">
        <button class="user-modal-close" onclick="closeUserProfile(event)">&times;</button>
        <div class="user-modal-header">
            <img id="modalProfileImg" src="" alt="Avatar">
            <h3 id="modalUsername">Felhasználónév</h3>
            <span id="modalRole" class="modal-role">Szerepkör</span>
        </div>
        <div class="user-modal-body">
            <p><i class="fas fa-flag-checkered" style="color:#888; width:20px;"></i> <strong>Csapat:</strong> <span id="modalTeam">Csapat</span></p>
            <p><i class="far fa-calendar-alt" style="color:#888; width:20px;"></i> <strong>Regisztrált:</strong> <span id="modalRegDate">Dátum</span></p>
        </div>
        <div class="user-modal-footer">
            <button id="modalFriendBtn" class="btn-add-friend" onclick="handleFriendAction()"><i class="fas fa-user-plus"></i> Barátnak jelölés</button>
            <button class="btn-send-msg" onclick="startChatFromModal()"><i class="fas fa-comment"></i> Üzenet küldése</button>
        </div>
    </div>
</div>

<script>
    let activePartner = null;
    let messageCount = 0;
    const currentUser = <?= json_encode($username) ?>;

    function makeSafeStr(str) {
        return str.replace(/\\/g, '\\\\').replace(/'/g, "\\'");
    }

    function loadFriends() {
        fetch('pm_api.php?action=get_friends')
        .then(r => r.json())
        .then(data => {
            if(data.success) {
                const list = document.getElementById('friendList');
                if(data.friends.length === 0) {
                    list.innerHTML = '<div style="color:#666; text-align:center; padding:20px; font-size:0.9rem;">Még nincsenek felvett barátaid. Keress egy felhasználót a fenti mezőben!</div>';
                    return;
                }
                list.innerHTML = '';
                data.friends.forEach(f => {
                    const isActive = f.friend_name === activePartner ? 'active' : '';
                    list.innerHTML += `
                        <div class="friend-item ${isActive}" onclick="openChat('${makeSafeStr(f.friend_name)}', '${f.profile_image}', '${f.color}')">
                            <img src="${f.profile_image}" class="friend-avatar" style="border-color: ${f.color}">
                            <div class="friend-info">
                                <div class="friend-name" style="color: ${f.color}">${f.friend_name}</div>
                            </div>
                        </div>
                    `;
                });
            }
        });
    }

    function openChat(partnerName, partnerImg, partnerColor) {
        activePartner = partnerName;
        messageCount = 0; 
        document.getElementById('chatHeader').style.display = 'flex';
        document.getElementById('chatInputArea').style.display = 'flex';
        document.getElementById('activeFriendName').innerText = partnerName;
        document.getElementById('activeFriendName').style.color = partnerColor;
        document.getElementById('activeFriendImg').src = partnerImg;
        document.getElementById('activeFriendImg').style.border = `2px solid ${partnerColor}`;

        loadFriends(); 
        loadMessages(true); 
    }

    function loadMessages() {
        if (!activePartner) return;
        fetch('pm_api.php?action=get_messages&partner=' + encodeURIComponent(activePartner))
        .then(r => r.json())
        .then(data => {
            if(data.success) {
                if (data.messages.length !== messageCount) {
                    const box = document.getElementById('chatBox');
                    box.innerHTML = '';
                    if(data.messages.length === 0) {
                        box.innerHTML = '<div style="margin:auto; color:#666;">Itt kezdődik a beszélgetésed. Integess! 👋</div>';
                    } else {
                        data.messages.forEach(m => {
                            const isMe = m.sender === currentUser;
                            const rowClass = isMe ? 'me' : 'them';
                            box.innerHTML += `
                                <div class="message-row ${rowClass}">
                                    <div class="message-bubble">${m.message}</div>
                                    <div class="message-time">${m.time}</div>
                                </div>
                            `;
                        });
                    }
                    messageCount = data.messages.length;
                    box.scrollTop = box.scrollHeight;
                }
            }
        });
    }

    function sendMessage() {
        const input = document.getElementById('msgInput');
        const msg = input.value.trim();
        if (!msg || !activePartner) return;
        input.value = '';

        fetch('pm_api.php?action=send', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ receiver: activePartner, message: msg })
        }).then(() => {
            loadMessages(); 
        });
    }

    function searchUsers(query) {
        const resultsDiv = document.getElementById('searchResults');
        if (query.length < 3) {
            resultsDiv.style.display = 'none';
            return;
        }
        
        fetch('pm_api.php?action=search_users&term=' + encodeURIComponent(query))
        .then(r => r.json())
        .then(data => {
            if(data.success && data.users.length > 0) {
                resultsDiv.innerHTML = '';
                data.users.forEach(u => {
                    resultsDiv.innerHTML += `
                        <div class="search-result-item" onclick="openUserProfile('${makeSafeStr(u.username)}')">
                            <img src="${u.profile_image}" style="border-color: ${u.color}">
                            <span style="color: ${u.color}; font-weight: 600;">${u.username}</span>
                        </div>
                    `;
                });
                resultsDiv.style.display = 'block';
            } else {
                resultsDiv.innerHTML = '<div style="padding: 10px; color: #888; text-align: center; font-size:0.9rem;">Nincs találat...</div>';
                resultsDiv.style.display = 'block';
            }
        });
    }

    document.addEventListener('click', function(e) {
        if(!e.target.closest('nav')) {
            const resDiv = document.getElementById('searchResults');
            if(resDiv) resDiv.style.display = 'none';
        }
    });

    let currentModalUser = "";
    let currentFriendStatus = "";

    function openUserProfile(username) {
        document.getElementById('searchResults').style.display = 'none';
        document.getElementById('userSearchInput').value = '';

        fetch('/f1fanclub/profile/user_profile_api.php?username=' + encodeURIComponent(username))
        .then(r => r.json())
        .then(data => {
            if(data.success) {
                currentModalUser = data.user.username;
                currentFriendStatus = data.user.friendship_status;

                document.getElementById('modalProfileImg').src = data.user.profile_image;
                document.getElementById('modalProfileImg').style.borderColor = data.user.team_color;
                document.getElementById('modalUsername').innerText = data.user.username;
                document.getElementById('modalRole').innerText = data.user.role_name;
                document.getElementById('modalTeam').innerText = data.user.fav_team || 'Nincs megadva';
                document.getElementById('modalRegDate').innerText = data.user.reg_date;
                
                updateFriendButton(data.user.friendship_status);
                document.getElementById('userProfileModal').style.display = 'flex';
            } else {
                alert("Hiba: " + data.error);
            }
        });
    }

    function closeUserProfile(e) {
        if(e) e.stopPropagation();
        document.getElementById('userProfileModal').style.display = 'none';
    }

    function updateFriendButton(status) {
        const btn = document.getElementById('modalFriendBtn');
        if(!btn) return;
        btn.style.display = 'flex';
        
        if (status === 'self') {
            btn.style.display = 'none'; 
        } else if (status === 'none') {
            btn.innerHTML = '<i class="fas fa-user-plus"></i> Barátnak jelölés';
            btn.style.background = '#333';
        } else if (status === 'pending_sent') {
            btn.innerHTML = '<i class="fas fa-clock"></i> Elküldve (Visszavonás)';
            btn.style.background = '#888';
        } else if (status === 'pending_received') {
            btn.innerHTML = '<i class="fas fa-check"></i> Jelölés elfogadása';
            btn.style.background = '#28a745'; 
        } else if (status === 'accepted') {
            btn.innerHTML = '<i class="fas fa-user-minus"></i> Barát törlése';
            btn.style.background = '#e10600'; 
        }
    }

    function handleFriendAction() {
        let action = '';
        if (currentFriendStatus === 'none') action = 'add';
        else if (currentFriendStatus === 'pending_sent' || currentFriendStatus === 'accepted') action = 'remove';
        else if (currentFriendStatus === 'pending_received') action = 'accept';

        if(!action) return;

        fetch('/f1fanclub/profile/friend_api.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ action: action, target_user: currentModalUser })
        }).then(r => r.json()).then(data => {
            if(data.success) {
                openUserProfile(currentModalUser);
                loadFriends(); 
            }
        });
    }

    function startChatFromModal() {
        closeUserProfile(); 
        let img = document.getElementById('modalProfileImg').src;
        let color = document.getElementById('modalProfileImg').style.borderColor;
        openChat(currentModalUser, img, color);
    }

    // DROPDOWN MENU TOGGLE
    document.addEventListener('DOMContentLoaded', function() {
        const dropdownContainer = document.getElementById('userDropdownContainer');
        if (dropdownContainer) {
            const welcomeDiv = dropdownContainer.querySelector('.welcome');
            if (welcomeDiv) {
                welcomeDiv.addEventListener('click', function(e) {
                    e.stopPropagation();
                    dropdownContainer.classList.toggle('open');
                });
            }
            
            document.addEventListener('click', function(e) {
                if (!dropdownContainer.contains(e.target)) {
                    dropdownContainer.classList.remove('open');
                }
            });
        }
    });

    loadFriends();
    setInterval(() => {
        loadMessages();
        if(!activePartner) loadFriends(); 
    }, 2000);
</script>
>>>>>>> f81424192996985be2da559c0f9a2c1f13f5eb7f
</body>
</html>