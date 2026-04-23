<?php
session_start();

/* ==== ADATBÁZIS KAPCSOLAT ==== */
$DB_HOST = "localhost";
$DB_USER = "swmjndga_swmjndga";
$DB_PASS = "Teszt1234!";
$DB_NAME = "swmjndga_f1adat";

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
  die("Adatbázis hiba: " . $conn->connect_error);
}

/* ==== LOGIN ADATOK KEZELÉSE ==== */
$isLoggedIn = isset($_SESSION['username']);
$username = $isLoggedIn ? $_SESSION['username'] : null;
$userRole = isset($_SESSION['role']) ? $_SESSION['role'] : 'user';

/* === Csapatszín függvény === */
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
    case 'Audi': return '#e3000f';
    case 'Haas F1 Team': return '#B6BABD';
    case 'Cadillac': return '#B6BABD';
    default: return '#ffffff';
  }
}

$profile_image = null;
$fav_team = null;
$teamColor = '#ffffff';
$isAdmin = false;

if ($isLoggedIn) {
  $stmt = $conn->prepare("SELECT profile_image, fav_team, role FROM users WHERE username=?");
  $stmt->bind_param("s", $username);
  $stmt->execute();
  $row = $stmt->get_result()->fetch_assoc();
  $profile_image = $row['profile_image'] ?? null;
  $fav_team = $row['fav_team'] ?? null;
  $teamColor = getTeamColor($fav_team);
  $isAdmin = !empty($row['role']) && $row['role'] === 'admin';
  $stmt->close();
}

/* ==== PILÓTÁK ÉS CSAPATOK LEKÉRDEZÉSE ==== */
$sql = "SELECT p.*, c.team_name 
        FROM pilotak p 
        LEFT JOIN csapatok c ON p.`team id` = c.team_id 
        ORDER BY p.points DESC";
$result = $conn->query($sql);

$driversData = [];

$flagImageMap = [
  'NED' => 'https://upload.wikimedia.org/wikipedia/commons/2/20/Flag_of_the_Netherlands.svg',
  'GBR' => 'https://upload.wikimedia.org/wikipedia/en/a/ae/Flag_of_the_United_Kingdom.svg',
  'AUS' => 'https://upload.wikimedia.org/wikipedia/commons/8/88/Flag_of_Australia_%28converted%29.svg',
  'MON' => 'https://upload.wikimedia.org/wikipedia/commons/e/ea/Flag_of_Monaco.svg',
  'ITA' => 'https://upload.wikimedia.org/wikipedia/en/0/03/Flag_of_Italy.svg',
  'THA' => 'https://upload.wikimedia.org/wikipedia/commons/a/a9/Flag_of_Thailand.svg',
  'ESP' => 'https://upload.wikimedia.org/wikipedia/en/9/9a/Flag_of_Spain.svg',
  'FRA' => 'https://upload.wikimedia.org/wikipedia/en/c/c3/Flag_of_France.svg',
  'GER' => 'https://upload.wikimedia.org/wikipedia/en/b/ba/Flag_of_Germany.svg',
  'NZL' => 'https://upload.wikimedia.org/wikipedia/commons/3/3e/Flag_of_New_Zealand.svg',
  'CAN' => 'https://upload.wikimedia.org/wikipedia/en/c/cf/Flag_of_Canada.svg',
  'BRA' => 'https://upload.wikimedia.org/wikipedia/en/0/05/Flag_of_Brazil.svg',
  'ARG' => 'https://upload.wikimedia.org/wikipedia/commons/1/1a/Flag_of_Argentina.svg',
  'FIN' => 'https://upload.wikimedia.org/wikipedia/commons/b/bc/Flag_of_Finland.svg',
  'MEX' => 'https://upload.wikimedia.org/wikipedia/commons/f/fc/Flag_of_Mexico.svg'
];

$teamCssMap = [
  'Red Bull' => 'redbull',
  'Ferrari' => 'ferrari',
  'Mercedes' => 'mercedes',
  'McLaren' => 'mclaren',
  'Aston Martin' => 'astonmartin',
  'Alpine' => 'alpine',
  'Williams' => 'williams',
  'RB' => 'racingbulls',
  'Audi' => 'audi',
  'Haas' => 'haas',
  'Cadillac' => 'cadillac'
];
?>
<!DOCTYPE html>
<html lang="hu">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
  <title>Versenyzők – F1 Fan Club</title>
  <link rel="icon" type="image/svg+xml" href="https://upload.wikimedia.org/wikipedia/commons/3/33/F1.svg">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { 
      background: #0a0a0a; 
      color: white; 
      font-family: 'Poppins', sans-serif; 
      min-height: 100vh; 
      position: relative; 
      overflow-x: hidden; 
      margin: 0; 
      padding: 0;
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

    nav { display: flex; gap: 5px; margin: 0 20px; }
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
    }
    nav a:hover { color: #e10600 !important; opacity: 1; background: rgba(225, 6, 0, 0.1); }
    nav a.active { color: #e10600 !important; opacity: 1; font-weight: 700; background: rgba(225, 6, 0, 0.15); }

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
        transition: all 0.2s;
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
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    }
    .dropdown-menu-modern a:last-child { border-bottom: none; }
    .dropdown-menu-modern a:hover { background: rgba(225, 6, 0, 0.2); color: white; padding-left: 24px; }
    .dropdown-menu-modern i { width: 24px; color: #e10600; }
    .dropdown-arrow-icon { margin-left: 6px; font-size: 0.7rem; color: #e10600; }
    .dropdown-container.open .dropdown-arrow-icon { transform: rotate(180deg); }
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

    /* ===== DRIVERS SECTION ===== */
    #drivers {
        width: 100%;
        height: calc(100vh - 80px);
        display: flex;
        flex-direction: column;
        position: relative;
        margin: 0;
        padding: 0;
        overflow: hidden;
    }

    .drivers-container {
        width: 100%;
        height: 100%;
        overflow-x: auto;
        overflow-y: hidden;
        background: #0a0a0a;
        display: flex;
        align-items: flex-end;
        scrollbar-width: none;
        -ms-overflow-style: none;
    }
    .drivers-container::-webkit-scrollbar { display: none; }

    .drivers-wrapper {
        display: flex;
        height: 100%;
        align-items: flex-end;
        padding-left: 50px;
        padding-right: 50px;
        gap: 0;
        width: max-content;
    }

    .driver-card {
        position: relative;
        height: 85vh;
        width: 280px;
        overflow: hidden;
        transition: all 0.4s cubic-bezier(0.2, 0.8, 0.2, 1);
        flex-shrink: 0;
        background: transparent;
        transform: scale(0.9);
        filter: grayscale(40%) brightness(0.5);
        transform-origin: center bottom;
        cursor: pointer;
    }
    .driver-image-container {
        position: relative;
        width: 100%;
        height: 100%;
        display: flex;
        align-items: flex-end;
        justify-content: center;
    }
    .driver-image {
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 100%;
        height: auto;
        max-height: 90%;
        object-fit: contain;
        object-position: center bottom;
        transition: all 0.4s;
        filter: grayscale(30%) brightness(0.6);
        z-index: 2;
        pointer-events: none;
    }

    .driver-card:hover {
        transform: scale(0.95);
        filter: grayscale(0%) brightness(1);
        width: 300px;
        z-index: 100;
    }
    .driver-card:hover .driver-image {
        transform: translateX(-50%) scale(1.08);
        filter: grayscale(0%) brightness(1.1);
    }
    .driver-card:hover .team-logo { opacity: 1; transform: translateX(-50%) scale(1.15); }
    .driver-card:hover::before { opacity: 0.8; }
    .driver-card:hover .driver-info { transform: translateY(0); }

    .driver-card.selected {
        transform: scale(0.95) !important;
        filter: grayscale(0%) brightness(1) !important;
        width: 300px !important;
        z-index: 100 !important;
    }
    .driver-card.selected .driver-image {
        transform: translateX(-50%) scale(1.08) !important;
        filter: grayscale(0%) brightness(1.1) !important;
    }
    .driver-card.selected .team-logo { opacity: 1 !important; }
    .driver-card.selected::before { opacity: 0.8 !important; }
    .driver-card.selected .driver-info { transform: translateY(0) !important; }
    .driver-card.selected::after {
        content: "";
        position: absolute;
        top: -2px;
        left: -2px;
        right: -2px;
        bottom: -2px;
        border: 2px solid rgba(225, 6, 0, 0.6);
        border-radius: 4px;
        z-index: 5;
        pointer-events: none;
        animation: selectedBorderGlow 2s infinite alternate;
    }
    @keyframes selectedBorderGlow {
        0% { border-color: rgba(225, 6, 0, 0.4); box-shadow: 0 0 10px rgba(225, 6, 0, 0.2); }
        100% { border-color: rgba(225, 6, 0, 0.8); box-shadow: 0 0 20px rgba(225, 6, 0, 0.4); }
    }

    .team-logo {
        position: absolute;
        top: 30px;
        left: 50%;
        transform: translateX(-50%);
        font-size: 1.5rem;
        font-weight: bold;
        color: rgba(255, 255, 255, 0.8);
        z-index: 3;
        text-transform: uppercase;
        letter-spacing: 2px;
        opacity: 0.6;
        transition: all 0.4s;
        white-space: nowrap;
    }

    .driver-card::before {
        content: "";
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1;
        opacity: 0.4;
        transition: opacity 0.4s;
    }
    .driver-card[data-team="redbull"]::before { background: linear-gradient(to top, rgba(30, 65, 255, 0.8) 0%, rgba(30, 65, 255, 0.3) 60%, transparent 100%); }
    .driver-card[data-team="ferrari"]::before { background: linear-gradient(to top, rgba(220, 0, 0, 0.8) 0%, rgba(220, 0, 0, 0.3) 60%, transparent 100%); }
    .driver-card[data-team="mercedes"]::before { background: linear-gradient(to top, rgba(0, 210, 190, 0.8) 0%, rgba(0, 210, 190, 0.3) 60%, transparent 100%); }
    .driver-card[data-team="mclaren"]::before { background: linear-gradient(to top, rgba(255, 135, 0, 0.8) 0%, rgba(255, 135, 0, 0.3) 60%, transparent 100%); }
    .driver-card[data-team="astonmartin"]::before { background: linear-gradient(to top, rgba(0, 111, 98, 0.8) 0%, rgba(0, 111, 98, 0.3) 60%, transparent 100%); }
    .driver-card[data-team="alpine"]::before { background: linear-gradient(to top, rgba(0, 144, 255, 0.8) 0%, rgba(0, 144, 255, 0.3) 60%, transparent 100%); }
    .driver-card[data-team="williams"]::before { background: linear-gradient(to top, rgba(0, 160, 222, 0.8) 0%, rgba(0, 160, 222, 0.3) 60%, transparent 100%); }
    .driver-card[data-team="racingbulls"]::before { background: linear-gradient(to top, rgba(43, 43, 255, 0.8) 0%, rgba(43, 43, 255, 0.3) 60%, transparent 100%); }
    .driver-card[data-team="audi"]::before { background: linear-gradient(to top, rgba(227, 0, 15, 0.8) 0%, rgba(227, 0, 15, 0.3) 60%, transparent 100%); }
    .driver-card[data-team="haas"]::before { background: linear-gradient(to top, rgba(182, 186, 189, 0.8) 0%, rgba(182, 186, 189, 0.3) 60%, transparent 100%); }
    .driver-card[data-team="cadillac"]::before { background: linear-gradient(to top, rgba(182, 186, 189, 0.8) 0%, rgba(182, 186, 189, 0.3) 60%, transparent 100%); }

    .driver-info {
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        z-index: 3;
        transform: translateY(100%);
        transition: transform 0.4s;
    }
    .nametag {
        background: #000000;
        color: white;
        width: 100%;
        padding: 20px 15px;
    }
    .driver-name {
        font-size: 1.5rem;
        font-weight: 900;
        margin-bottom: 5px;
        text-transform: uppercase;
    }
    .driver-team {
        font-size: 1rem;
        opacity: 0.8;
        margin-bottom: 8px;
        text-transform: uppercase;
    }
    .driver-nationality {
        font-size: 0.9rem;
        opacity: 0.6;
        display: flex;
        align-items: center;
        gap: 8px;
        text-transform: uppercase;
    }
    .flag-img {
        width: 24px;
        height: 16px;
        vertical-align: middle;
        margin-right: 5px;
        border-radius: 2px;
    }

    /* Scroll Buttons */
    .scroll-button {
        position: fixed;
        top: calc(50% + 40px);
        transform: translateY(-50%);
        width: 60px;
        height: 100px;
        background: rgba(10, 10, 10, 0.9);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        z-index: 999;
        border: 2px solid #e10600;
        backdrop-filter: blur(8px);
    }
    .scroll-button.left { left: 0; border-left: none; border-radius: 0 30px 30px 0; }
    .scroll-button.right { right: 0; border-right: none; border-radius: 30px 0 0 30px; }
    .scroll-button::before {
        content: "";
        width: 20px;
        height: 20px;
        border-top: 3px solid white;
        border-right: 3px solid white;
    }
    .scroll-button.left::before { transform: rotate(-135deg); }
    .scroll-button.right::before { transform: rotate(45deg); }
    .scroll-button .accent-line {
        position: absolute;
        background: #e10600;
        transition: all 0.3s;
    }
    .scroll-button.left .accent-line { right: 0; top: 25%; bottom: 25%; width: 2px; }
    .scroll-button.right .accent-line { left: 0; top: 25%; bottom: 25%; width: 2px; }

    /* ===== STATISTICS PANEL - ORIGINAL STYLING ===== */
    .statistics-panel {
        position: fixed;
        top: 80px !important;
        right: -500px;
        width: 480px;
        height: calc(100vh - 80px) !important;
        max-height: calc(100vh - 80px) !important;
        background: linear-gradient(180deg, 
                    rgba(10, 10, 10, 0.98) 0%, 
                    rgba(5, 5, 5, 0.98) 100%);
        backdrop-filter: blur(20px);
        border-left: 3px solid #e10600;
        z-index: 10000;
        transition: right 0.3s ease-out;
        overflow-y: auto;
        overflow-x: hidden;
        box-shadow: -10px 0 50px rgba(0, 0, 0, 0.8),
                    -5px 0 30px rgba(225, 6, 0, 0.3);
        display: flex;
        flex-direction: column;
        font-family: 'Poppins', sans-serif;
        will-change: right;
    }
    .statistics-panel.active { 
        right: 0; 
    }

    .drivers-container.panel-active { 
        width: calc(100% - 480px) !important; 
        transition: width 0.3s ease-out;
        will-change: width;
    }
    .scroll-button.right.panel-active { 
        right: 480px !important; 
        transition: right 0.3s ease-out;
        will-change: right;
    }

    .close-panel {
        position: absolute;
        top: 15px;
        right: 15px;
        width: 36px;
        height: 36px;
        background: #e10600;
        color: white;
        border: none;
        border-radius: 50% !important;
        font-size: 20px;
        font-weight: bold;
        cursor: pointer;
        z-index: 10001;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 0 10px rgba(225, 6, 0, 0.8);
    }
    .close-panel:hover { background: #ff1a00; transform: scale(1.1); box-shadow: 0 0 20px rgba(255, 26, 0, 1); }

    .statistics-header {
        padding: 25px 25px 15px;
        background: linear-gradient(180deg, 
                    rgba(20, 20, 20, 0.95) 0%, 
                    rgba(15, 15, 15, 0.9) 100%);
        border-bottom: 1px solid rgba(225, 6, 0, 0.4);
        display: flex;
        align-items: center;
        gap: 15px;
        position: relative;
        flex-shrink: 0;
        min-height: 120px;
    }

    .stats-image-container {
        position: relative;
        width: 90px;
        height: 90px;
        flex-shrink: 0;
    }
    .glow-effect {
        position: absolute;
        top: -5px;
        left: -5px;
        right: -5px;
        bottom: -5px;
        background: radial-gradient(circle at center, rgba(225, 6, 0, 0.4) 0%, transparent 70%);
        z-index: 0;
        filter: blur(10px);
        animation: imageGlow 4s infinite alternate;
    }
    @keyframes imageGlow {
        0% { opacity: 0.5; transform: scale(1); }
        100% { opacity: 0.8; transform: scale(1.05); }
    }
    .stats-driver-image {
        width: 100%;
        height: 100%;
        object-fit: contain;
        filter: drop-shadow(0 0 20px rgba(225, 6, 0, 0.6));
        position: relative;
        z-index: 1;
    }

    .stats-driver-info { flex: 1; min-width: 0; }
    .stats-driver-info h2 {
        font-size: 1.5rem;
        font-weight: 900;
        margin-bottom: 4px;
        text-transform: uppercase;
        color: white;
        text-shadow: 0 2px 10px rgba(225, 6, 0, 0.3);
        letter-spacing: 0.5px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .stats-driver-info p {
        font-size: 0.85rem;
        opacity: 0.9;
        margin-bottom: 6px;
        color: #e10600;
        font-weight: 600;
        letter-spacing: 0.3px;
    }
    .stats-driver-nationality {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 0.8rem;
        opacity: 0.7;
        color: #ccc;
    }

    .stats-toggle {
        display: flex;
        padding: 12px 25px;
        background: rgba(15, 15, 15, 0.9);
        border-bottom: 1px solid rgba(225, 6, 0, 0.2);
        gap: 8px;
        flex-shrink: 0;
    }
    .toggle-btn {
        flex: 1;
        padding: 10px 12px;
        background: rgba(25, 25, 25, 0.9);
        color: #888;
        border: 1px solid rgba(225, 6, 0, 0.3);
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        cursor: pointer;
        transition: all 0.25s ease;
        position: relative;
        overflow: hidden;
        text-align: center;
    }
    .toggle-btn.active {
        background: linear-gradient(135deg, #e10600 0%, #ff1a00 100%);
        color: white;
        border-color: transparent;
        box-shadow: 0 0 15px rgba(225, 6, 0, 0.7), inset 0 1px 0 rgba(255, 255, 255, 0.2);
    }
    .toggle-btn:hover:not(.active) { background: rgba(225, 6, 0, 0.15); color: white; border-color: rgba(225, 6, 0, 0.5); }
    .toggle-btn .toggle-glow {
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: left 0.5s ease;
    }
    .toggle-btn:hover .toggle-glow { left: 100%; }
    .toggle-text { position: relative; z-index: 2; }

    .statistics-content {
        padding: 20px 25px;
        flex: 1;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        min-height: 0;
    }
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
        flex: 1;
        min-height: 0;
    }
    .stat-item {
        background: linear-gradient(145deg, 
                    rgba(20, 20, 20, 0.9) 0%, 
                    rgba(25, 25, 25, 0.9) 100%);
        padding: 18px 12px;
        border: 1px solid rgba(225, 6, 0, 0.2);
        border-radius: 6px !important;
        transition: all 0.3s cubic-bezier(0.2, 0.8, 0.2, 1);
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        box-shadow: 0 3px 8px rgba(0, 0, 0, 0.4);
        min-height: 100px;
    }
    .stat-item:hover {
        background: linear-gradient(145deg, rgba(30, 30, 30, 0.95) 0%, rgba(35, 35, 35, 0.95) 100%);
        transform: translateY(-3px);
        border-color: #ff1a00;
        box-shadow: 0 6px 20px rgba(225, 6, 0, 0.3), 0 0 15px rgba(225, 6, 0, 0.2);
    }
    .stat-glow {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(45deg, transparent 20%, rgba(225, 6, 0, 0.1) 50%, transparent 80%);
        opacity: 0;
        transition: opacity 0.3s ease;
        z-index: 1;
    }
    .stat-item:hover .stat-glow { opacity: 1; }
    .stat-label {
        display: block;
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #aaa;
        margin-bottom: 8px;
        position: relative;
        z-index: 2;
        text-align: center;
        width: 100%;
        font-weight: 600;
    }
    .stat-value {
        display: block;
        font-size: 2rem;
        font-weight: 900;
        color: white;
        position: relative;
        z-index: 2;
        text-align: center;
        width: 100%;
        text-shadow: 0 2px 6px rgba(0, 0, 0, 0.5);
        line-height: 1;
    }
    .stat-item:hover .stat-value {
        color: #fff;
        text-shadow: 0 0 8px rgba(225, 6, 0, 0.6), 0 0 15px rgba(225, 6, 0, 0.3);
    }

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
            margin: 0;
            z-index: 1000;
        }
        nav.open { display: flex; }
        nav a { padding: 15px 20px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); }
        header { padding: 0 20px; }
    }

    @media (max-width: 768px) {
        body { 
            overflow-y: auto; 
            -webkit-overflow-scrolling: touch;
        }
        #drivers { 
            height: auto; 
            min-height: calc(100vh - 80px); 
            overflow: visible; 
        }
        .drivers-container { 
            height: auto; 
            overflow: visible; 
            display: block; 
        }
        .drivers-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
            height: auto;
            padding: 20px 10px;
            gap: 20px;
        }
        
        /* Keep all driver cards bright on mobile */
        .driver-card {
            width: 90% !important;
            max-width: 350px !important;
            height: 400px !important;
            transform: scale(1) !important;
            filter: grayscale(0%) brightness(1) !important;
            margin: 0 auto !important;
        }
        .driver-card:hover { 
            transform: scale(1.01) !important; 
            width: 90% !important;
            max-width: 350px !important;
            filter: grayscale(0%) brightness(1) !important;
        }
        .driver-card.selected { 
            transform: scale(1) !important; 
            width: 90% !important;
            max-width: 350px !important;
            filter: grayscale(0%) brightness(1) !important;
        }
        
        /* Override panel-active grayscale on mobile */
        .drivers-wrapper.panel-active .driver-card:not(.selected) {
            filter: grayscale(0%) brightness(1) !important;
            opacity: 1 !important;
            transform: scale(1) !important;
        }
        
        /* Move image down to show head - not bigger, just positioned lower */
        .driver-image {
            max-height: 90% !important;
            height: auto !important;
            width: 100% !important;
            object-fit: contain !important;
            object-position: center 70% !important;
            transform: translateX(-50%) !important;
            bottom: 0 !important;
            top: auto !important;
            filter: grayscale(0%) brightness(1) !important;
        }
        .driver-card:hover .driver-image,
        .driver-card.selected .driver-image {
            transform: translateX(-50%) scale(1.05) !important;
            filter: grayscale(0%) brightness(1) !important;
        }
        
        .driver-image-container {
            overflow: hidden !important;
            align-items: flex-end !important;
        }
        
        .driver-info { 
            transform: translateY(0) !important; 
            background: linear-gradient(to top, rgba(0,0,0,0.9) 0%, rgba(0,0,0,0.4) 70%, transparent 100%) !important;
        }
        .nametag {
            background: transparent !important;
            padding: 20px 15px 15px !important;
        }
        .driver-name {
            font-size: 1.4rem !important;
        }
        .driver-team {
            font-size: 0.9rem !important;
        }
        .team-logo { display: none !important; }
        .scroll-button { display: none !important; }
        
        .statistics-panel {
            top: auto !important;
            bottom: -100% !important;
            left: 0 !important;
            right: 0 !important;
            width: 100% !important;
            height: 80vh !important;
            max-height: 80vh !important;
            border-left: none !important;
            border-top: 3px solid #e10600 !important;
            border-radius: 20px 20px 0 0 !important;
            transition: bottom 0.3s ease-out !important;
        }
        .statistics-panel.active { 
            right: 0 !important;
            bottom: 0 !important;
        }
        .drivers-container.panel-active { width: 100% !important; }
        .statistics-header { padding: 15px 20px !important; min-height: 100px !important; }
        .stats-image-container { width: 70px !important; height: 70px !important; }
        .stats-driver-info h2 { font-size: 1.2rem !important; }
        .stat-item { 
            padding: 12px 8px !important; 
            min-height: 80px !important; 
        }
        .stat-value { font-size: 1.5rem !important; }
    }

    @media (max-width: 480px) {
        .driver-card { 
            max-width: 320px !important; 
            height: 380px !important; 
        }
        .driver-card:hover,
        .driver-card.selected { 
            max-width: 320px !important; 
        }
        .driver-image {
            object-position: center 65% !important;
        }
        .driver-name { font-size: 1.3rem !important; }
        .driver-team { font-size: 0.85rem !important; }
        .nametag { padding: 18px 12px 12px !important; }
        .statistics-panel { height: 85vh !important; max-height: 85vh !important; }
        .stats-image-container { width: 60px !important; height: 60px !important; }
        .stats-driver-info h2 { font-size: 1rem !important; }
        .stat-value { font-size: 1.3rem !important; }
    }

    @media (max-width: 360px) {
        .driver-card { 
            max-width: 280px !important; 
            height: 350px !important; 
        }
        .driver-image {
            object-position: center 60% !important;
        }
        .driver-name { font-size: 1.2rem !important; }
        .driver-team { font-size: 0.8rem !important; }
    }

    .clickable-user { cursor: pointer; }
    .clickable-user:hover { opacity: 0.8; }
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
      <a href="/f1fanclub/drivers/drivers.php" class="active">Versenyzők</a>
      <a href="/f1fanclub/news/feed.php">Paddock</a>
      <a href="/f1fanclub/pitwall/pitwall.php"><i class="fas fa-trophy" style="margin-right: 5px;"></i> A Fal</a>
    </nav>

    <?php if ($isLoggedIn): ?>
      <div class="dropdown-container" id="userDropdownContainer">
        <div class="auth">
          <div class="welcome">
            <?php if ($profile_image): ?>
              <img src="/f1fanclub/uploads/<?php echo htmlspecialchars($profile_image); ?>" class="avatar clickable-user" alt="Profilkép"
                style="border-color: <?php echo htmlspecialchars($teamColor); ?>;"
                onclick="openUserProfile('<?php echo htmlspecialchars(addslashes($username)); ?>')">
            <?php endif; ?>
            <span class="welcome-text">
              <span class="clickable-user" onclick="openUserProfile('<?php echo htmlspecialchars(addslashes($username)); ?>')"
                style="color: <?php echo htmlspecialchars($teamColor); ?>; font-weight:bold;"><?php echo htmlspecialchars($username); ?></span>
            </span>
            <i class="fas fa-chevron-down dropdown-arrow-icon"></i>
          </div>
        </div>
        
        <div class="dropdown-menu-modern">
          <a href="/f1fanclub/profile/profile.php"><i class="fas fa-user-circle"></i> Profilom</a>
          <a href="/f1fanclub/messages/messages.php"><i class="fas fa-envelope"></i> Üzenetek</a>
          <?php if ($isAdmin): ?>
            <a href="/f1fanclub/admin/admin.php" style="position: relative;">
              <i class="fas fa-shield-alt"></i> Admin Panel
              <span class="admin-badge">ADMIN</span>
            </a>
          <?php endif; ?>
          <div class="dropdown-divider"></div>
          <a href="/f1fanclub/logout/logout.php"><i class="fas fa-sign-out-alt"></i> Kijelentkezés</a>
        </div>
      </div>
    <?php else: ?>
      <div class="auth">
        <a href="/f1fanclub/register/register.html" class="btn">Regisztráció</a>
        <a href="/f1fanclub/login/login.html" class="btn">Bejelentkezés</a>
      </div>
    <?php endif; ?>
  </header>

  <section id="drivers">
    <div class="statistics-panel" id="statistics-panel">
      <button class="close-panel" id="close-panel">×</button>
      <div class="statistics-header">
        <div class="stats-image-container">
          <img id="stats-driver-image" src="" alt="Versenyző" class="stats-driver-image">
          <div class="glow-effect"></div>
        </div>
        <div class="stats-driver-info">
          <h2 id="stats-driver-name">VERSENYZŐ NEVE</h2>
          <p id="stats-driver-team">CSAPAT NEVE</p>
          <div class="stats-driver-nationality">
            <span id="stats-flag"></span>
            <span id="stats-nationality">NEMZETISÉG</span>
          </div>
        </div>
      </div>

      <div class="stats-toggle">
        <button class="toggle-btn active" data-period="current"><span class="toggle-text">IDEI SZEZON</span><span class="toggle-glow"></span></button>
        <button class="toggle-btn" data-period="career"><span class="toggle-text">KARRIER</span><span class="toggle-glow"></span></button>
      </div>

      <div class="statistics-content">
        <div class="stats-grid" id="current-stats">
          <div class="stat-item"><span class="stat-label">HELYEZÉS</span><span class="stat-value" id="current-position"></span><div class="stat-glow"></div></div>
          <div class="stat-item"><span class="stat-label">PONTOK</span><span class="stat-value" id="current-points"></span><div class="stat-glow"></div></div>
          <div class="stat-item"><span class="stat-label">GYŐZELMEK</span><span class="stat-value" id="current-wins"></span><div class="stat-glow"></div></div>
          <div class="stat-item"><span class="stat-label">DOBOGÓK</span><span class="stat-value" id="current-podiums"></span><div class="stat-glow"></div></div>
          <div class="stat-item"><span class="stat-label">POLE POZÍCIÓK</span><span class="stat-value" id="current-poles"></span><div class="stat-glow"></div></div>
          <div class="stat-item"><span class="stat-label">LEGGYORSABB KÖRÖK</span><span class="stat-value" id="current-fastest-laps"></span><div class="stat-glow"></div></div>
        </div>

        <div class="stats-grid" id="career-stats" style="display: none;">
          <div class="stat-item"><span class="stat-label">NAGDÍJAK</span><span class="stat-value" id="career-races"></span><div class="stat-glow"></div></div>
          <div class="stat-item"><span class="stat-label">GYŐZELMEK</span><span class="stat-value" id="career-wins"></span><div class="stat-glow"></div></div>
          <div class="stat-item"><span class="stat-label">DOBOGÓK</span><span class="stat-value" id="career-podiums"></span><div class="stat-glow"></div></div>
          <div class="stat-item"><span class="stat-label">POLE POZÍCIÓK</span><span class="stat-value" id="career-poles"></span><div class="stat-glow"></div></div>
          <div class="stat-item"><span class="stat-label">LEGGYORSABB KÖRÖK</span><span class="stat-value" id="career-fastest-laps"></span><div class="stat-glow"></div></div>
          <div class="stat-item"><span class="stat-label">VILÁGBAJNOKI CÍMEK</span><span class="stat-value" id="career-titles"></span><div class="stat-glow"></div></div>
        </div>
      </div>
    </div>

    <div class="scroll-button left" id="scroll-left"><div class="accent-line"></div></div>
    <div class="scroll-button right" id="scroll-right"><div class="accent-line"></div></div>

    <div class="drivers-container">
      <div class="drivers-wrapper" id="drivers-wrapper">

        <?php while ($driver = $result->fetch_assoc()):
          $keyId = $driver['abbreviation'];
          $teamName = $driver['team_name'] ?? 'Ismeretlen Csapat';
          $cssClass = $teamCssMap[$teamName] ?? 'redbull';
          $nationalityCode = $driver['nationality'];
          $flagImageUrl = $flagImageMap[$nationalityCode] ?? 'https://upload.wikimedia.org/wikipedia/commons/3/32/Flag_of_unknown.svg';
          $nationalityName = strtoupper($driver['nationality']);

          $imgSrc = $driver['image'];
          if (strpos($imgSrc, 'kép/') === false && strpos($imgSrc, 'drivers/') === 0) {
            $imgSrc = 'kép/' . str_replace('drivers/', '', $imgSrc);
          }

          $driversData[$keyId] = [
            'name' => strtoupper($driver['name']),
            'team' => strtoupper($teamName),
            'nationality' => $nationalityName,
            'flag' => $flagImageUrl,
            'image' => $imgSrc,
            'current' => [
              'position' => $driver['current_position'] . '.',
              'points' => $driver['points'],
              'wins' => $driver['current_wins'],
              'podiums' => $driver['current_podiums'],
              'poles' => $driver['current_poles'],
              'fastestLaps' => $driver['current_fastest_laps']
            ],
            'career' => [
              'races' => $driver['career_races'],
              'wins' => $driver['career_wins'],
              'podiums' => $driver['career_podiums'],
              'poles' => $driver['career_poles'],
              'fastestLaps' => $driver['career_fastest_laps'],
              'titles' => $driver['career_titles']
            ]
          ];
          ?>

          <div class="driver-card" data-team="<?= $cssClass ?>" id="<?= $keyId ?>" data-driver="<?= $keyId ?>">
            <div class="driver-image-container">
              <img src="<?= htmlspecialchars($imgSrc) ?>" alt="<?= htmlspecialchars($driver['name']) ?>" class="driver-image" loading="lazy">
              <div class="team-logo"><?= htmlspecialchars($teamName) ?></div>
            </div>
            <div class="driver-info">
              <div class="nametag">
                <h2 class="driver-name"><?= htmlspecialchars($driver['name']) ?></h2>
                <div class="driver-team" style="color: <?= getTeamColor($teamName) ?>;"><?= strtoupper(htmlspecialchars($teamName)) ?></div>
                <div class="driver-nationality">
                  <span class="flag"><img src="<?= $flagImageUrl ?>" class="flag-img" alt="<?= $nationalityName ?>"></span>
                  <?= $nationalityName ?>
                </div>
              </div>
            </div>
          </div>

        <?php endwhile; ?>

      </div>
    </div>
  </section>

  <script>
    window.driverStatsFromDB = <?= json_encode($driversData, JSON_UNESCAPED_UNICODE); ?>;
  </script>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Hamburger menu
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

      // Driver gallery scroll
      const scrollLeftBtn = document.getElementById('scroll-left');
      const scrollRightBtn = document.getElementById('scroll-right');
      const driversContainer = document.querySelector('.drivers-container');
      const scrollAmount = 840;

      if (scrollLeftBtn) {
        scrollLeftBtn.addEventListener('click', () => driversContainer.scrollBy({ left: -scrollAmount, behavior: 'smooth' }));
      }
      if (scrollRightBtn) {
        scrollRightBtn.addEventListener('click', () => driversContainer.scrollBy({ left: scrollAmount, behavior: 'smooth' }));
      }

      document.addEventListener('keydown', function(e) {
        if (e.key === 'ArrowLeft') driversContainer.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
        if (e.key === 'ArrowRight') driversContainer.scrollBy({ left: scrollAmount, behavior: 'smooth' });
        if (e.key === 'Escape') closeStatisticsPanel();
      });

      // Statistics panel
      const statsPanel = document.getElementById('statistics-panel');
      const closePanelBtn = document.getElementById('close-panel');
      const toggleBtns = document.querySelectorAll('.toggle-btn');
      const driverStats = window.driverStatsFromDB || {};
      let isSwitchingDriver = false;

      function openStatisticsPanel(driverId) {
        if (isSwitchingDriver) return;
        isSwitchingDriver = true;

        const stats = driverStats[driverId];
        if (!stats) { isSwitchingDriver = false; return; }

        document.querySelectorAll('.driver-card').forEach(c => c.classList.remove('selected'));
        const selectedCard = document.querySelector(`[data-driver="${driverId}"]`);
        if (selectedCard) {
          selectedCard.classList.add('selected');
          if (window.innerWidth > 768) {
            const scrollPos = selectedCard.offsetLeft - (driversContainer.clientWidth / 2) + (selectedCard.clientWidth / 2);
            driversContainer.scrollTo({ left: Math.max(0, scrollPos), behavior: 'smooth' });
          } else {
            setTimeout(() => selectedCard.scrollIntoView({ behavior: 'smooth', block: 'center' }), 100);
          }
        }

        document.getElementById('stats-driver-name').textContent = stats.name;
        document.getElementById('stats-driver-team').textContent = stats.team;
        document.getElementById('stats-nationality').textContent = stats.nationality;
        document.getElementById('stats-flag').innerHTML = `<img src="${stats.flag}" class="flag-img" alt="${stats.nationality}" style="width:24px; height:16px;">`;
        document.getElementById('stats-driver-image').src = stats.image;

        document.getElementById('current-position').textContent = stats.current.position;
        document.getElementById('current-points').textContent = stats.current.points;
        document.getElementById('current-wins').textContent = stats.current.wins;
        document.getElementById('current-podiums').textContent = stats.current.podiums;
        document.getElementById('current-poles').textContent = stats.current.poles;
        document.getElementById('current-fastest-laps').textContent = stats.current.fastestLaps;

        document.getElementById('career-races').textContent = stats.career.races;
        document.getElementById('career-wins').textContent = stats.career.wins;
        document.getElementById('career-podiums').textContent = stats.career.podiums;
        document.getElementById('career-poles').textContent = stats.career.poles;
        document.getElementById('career-fastest-laps').textContent = stats.career.fastestLaps;
        document.getElementById('career-titles').textContent = stats.career.titles;

        statsPanel.classList.add('active');
        driversContainer.classList.add('panel-active');
        if (scrollRightBtn) scrollRightBtn.classList.add('panel-active');
        if (window.innerWidth > 768) document.body.style.overflow = 'hidden';

        setTimeout(() => { isSwitchingDriver = false; }, 300);
      }

      function closeStatisticsPanel() {
        document.querySelectorAll('.driver-card').forEach(c => c.classList.remove('selected'));
        statsPanel.classList.remove('active');
        driversContainer.classList.remove('panel-active');
        if (scrollRightBtn) scrollRightBtn.classList.remove('panel-active');
        document.body.style.overflow = '';
        isSwitchingDriver = false;
      }

      document.querySelectorAll('.driver-card').forEach(card => {
        card.addEventListener('click', function(e) {
          e.stopPropagation();
          const driverId = this.getAttribute('data-driver');
          if (statsPanel.classList.contains('active')) {
            const currentSelected = document.querySelector('.driver-card.selected');
            if (currentSelected && currentSelected.getAttribute('data-driver') !== driverId) {
              openStatisticsPanel(driverId);
              return;
            }
            closeStatisticsPanel();
          } else {
            openStatisticsPanel(driverId);
          }
        });
      });

      if (closePanelBtn) {
        closePanelBtn.addEventListener('click', (e) => { e.stopPropagation(); closeStatisticsPanel(); });
      }

      document.addEventListener('click', function(e) {
        if (statsPanel.classList.contains('active') && !statsPanel.contains(e.target) && !e.target.closest('.driver-card') && !e.target.closest('.scroll-button')) {
          closeStatisticsPanel();
        }
      });

      toggleBtns.forEach(btn => {
        btn.addEventListener('click', function() {
          const period = this.getAttribute('data-period');
          toggleBtns.forEach(b => b.classList.remove('active'));
          this.classList.add('active');
          document.getElementById('current-stats').style.display = period === 'current' ? 'grid' : 'none';
          document.getElementById('career-stats').style.display = period === 'career' ? 'grid' : 'none';
        });
      });
    });

    // User profile modal functions
    let currentModalUser = "", currentFriendStatus = "";

    function openUserProfile(username) {
      fetch('/f1fanclub/profile/user_profile_api.php?username=' + encodeURIComponent(username))
        .then(r => r.json())
        .then(data => {
          if(data.success) {
            currentModalUser = data.user.username;
            currentFriendStatus = data.user.friendship_status;

            let modal = document.getElementById('userProfileModal');
            if (!modal) {
              modal = document.createElement('div');
              modal.id = 'userProfileModal';
              modal.className = 'user-modal-overlay';
              modal.onclick = closeUserProfile;
              modal.innerHTML = `
                <div class="user-modal-content" onclick="event.stopPropagation()">
                  <button class="user-modal-close" onclick="closeUserProfile(event)">&times;</button>
                  <div class="user-modal-header">
                    <img id="modalProfileImg" src="" alt="Avatar">
                    <h3 id="modalUsername"></h3>
                    <span id="modalRole" class="modal-role"></span>
                  </div>
                  <div class="user-modal-body">
                    <p><i class="fas fa-flag-checkered"></i> <strong>Csapat:</strong> <span id="modalTeam"></span></p>
                    <p><i class="far fa-calendar-alt"></i> <strong>Regisztrált:</strong> <span id="modalRegDate"></span></p>
                  </div>
                  <div class="user-modal-footer">
                    <button id="modalFriendBtn" class="btn-add-friend" onclick="handleFriendAction()"></button>
                    <button class="btn-send-msg" onclick="window.location.href='/f1fanclub/messages/messages.php'"><i class="fas fa-comment"></i> Üzenet</button>
                  </div>
                </div>
              `;
              document.body.appendChild(modal);
              addModalStyles();
            }

            document.getElementById('modalProfileImg').src = data.user.profile_image;
            document.getElementById('modalProfileImg').style.borderColor = data.user.team_color;
            document.getElementById('modalUsername').innerText = data.user.username;
            document.getElementById('modalRole').innerText = data.user.role_name;
            document.getElementById('modalTeam').innerText = data.user.fav_team || 'Nincs megadva';
            document.getElementById('modalRegDate').innerText = data.user.reg_date;
            
            updateFriendButton(data.user.friendship_status);
            modal.style.display = 'flex';
          }
        });
    }

    function closeUserProfile(e) { if(e) e.stopPropagation(); const m = document.getElementById('userProfileModal'); if(m) m.style.display = 'none'; }
    
    function updateFriendButton(status) {
      const btn = document.getElementById('modalFriendBtn');
      if(!btn) return;
      if (status === 'self') btn.style.display = 'none';
      else if (status === 'none') btn.innerHTML = '<i class="fas fa-user-plus"></i> Barátnak jelölés';
      else if (status === 'pending_sent') btn.innerHTML = '<i class="fas fa-clock"></i> Jelölés elküldve';
      else if (status === 'pending_received') btn.innerHTML = '<i class="fas fa-check"></i> Jelölés elfogadása';
      else if (status === 'accepted') btn.innerHTML = '<i class="fas fa-user-minus"></i> Barát törlése';
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
        body: JSON.stringify({ action, target_user: currentModalUser })
      }).then(r => r.json()).then(data => { if(data.success) openUserProfile(currentModalUser); });
    }

    function addModalStyles() {
      if (document.getElementById('modal-styles')) return;
      const style = document.createElement('style');
      style.id = 'modal-styles';
      style.textContent = `
        .user-modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); backdrop-filter: blur(8px); z-index: 100000; justify-content: center; align-items: center; }
        .user-modal-content { background: linear-gradient(145deg, #111, #1a1a1a); width: 320px; border-radius: 24px; border: 1px solid #e10600; padding: 20px; position: relative; text-align: center; }
        .user-modal-close { position: absolute; top: 12px; right: 15px; background: none; border: none; color: #888; font-size: 1.3rem; cursor: pointer; }
        .user-modal-close:hover { color: #e10600; }
        .user-modal-header img { width: 80px; height: 80px; border-radius: 50%; border: 3px solid #e10600; object-fit: cover; margin-bottom: 10px; }
        .modal-role { display: inline-block; font-size: 0.7rem; background: rgba(255,255,255,0.1); padding: 2px 10px; border-radius: 20px; margin-top: 5px; color: #aaa; }
        .user-modal-body { margin: 15px 0; background: rgba(0,0,0,0.3); padding: 12px; border-radius: 16px; text-align: left; }
        .user-modal-footer { display: flex; gap: 10px; }
        .user-modal-footer button { flex: 1; padding: 10px; border: none; border-radius: 40px; cursor: pointer; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 6px; }
        .btn-add-friend { background: #333; color: white; }
        .btn-send-msg { background: #e10600; color: white; }
        .clickable-user { cursor: pointer; }
      `;
      document.head.appendChild(style);
    }
  </script>
</body>
</html>