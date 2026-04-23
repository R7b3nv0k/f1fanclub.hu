<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$DB_HOST = "localhost";
$DB_USER = "swmjndga_swmjndga";
$DB_PASS = "Teszt1234!";
$DB_NAME = "swmjndga_f1adat";

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) { die("Adatbázis csatlakozási hiba: " . $conn->connect_error); }

$isLoggedIn = isset($_SESSION['username']);
if (!$isLoggedIn) { header("Location: ../login/login.html"); exit; }

$username = $_SESSION['username'];

function getTeamColor($team) {
  switch ($team) {
    case 'Red Bull': return '#1E41FF'; case 'Ferrari': return '#DC0000'; case 'Mercedes': return '#00D2BE';
    case 'McLaren': return '#FF8700'; case 'Aston Martin': return '#006F62'; case 'Alpine': return '#0090FF';
    case 'Williams': return '#00A0DE'; case 'RB': return '#2b2bff'; case 'Audi': return '#e3000f';
    case 'Haas F1 Team': return '#B6BABD';  case 'Cadillac': return '#B6BABD'; default: return '#ffffff';
  }
}

$profile_image = null; $fav_team = null; $teamColor = '#ffffff'; $isAdmin = false;
$stmt = $conn->prepare("SELECT profile_image, fav_team, role FROM users WHERE username=?");
if ($stmt) {
    $stmt->bind_param("s", $username); $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    if ($row) {
        $profile_image = $row['profile_image'] ?? null;
        $teamColor = getTeamColor($row['fav_team'] ?? null);
        $isAdmin = !empty($row['role']) && $row['role'] === 'admin';
    }
    $stmt->close();
}

/** 1. KÖVETKEZŐ FUTAM ÉS HATÁRIDŐ **/
$nextRace = null;
$isDeadlinePassed = false;
$deadlineStr = "";

$sqlNextRace = "SELECT * FROM f1_races WHERE race_date > NOW() ORDER BY race_date ASC LIMIT 1";
$resultRace = $conn->query($sqlNextRace);

if ($resultRace && $resultRace->num_rows > 0) {
    $nextRace = $resultRace->fetch_assoc();
    
    $raceTimestamp = strtotime($nextRace['race_date']);
    $deadlineTimestamp = $raceTimestamp - 86400; 
    $deadlineStr = date('Y. m. d. H:i', $deadlineTimestamp);
    
    if (time() >= $deadlineTimestamp) {
        $isDeadlinePassed = true;
    }
}

/** 2. VAN-E MÁR TIPPJE A FELHASZNÁLÓNAK? **/
$hasPrediction = false;
$savedOrder = [];
if ($nextRace) {
    $stmt = $conn->prepare("SELECT predictions FROM pitwall_predictions WHERE username=? AND race_id=?");
    $stmt->bind_param("si", $username, $nextRace['race_id']);
    $stmt->execute();
    $predRes = $stmt->get_result();
    if ($predRes && $predRes->num_rows > 0) {
        $hasPrediction = true;
        $savedOrder = json_decode($predRes->fetch_assoc()['predictions'], true);
    }
    $stmt->close();
}

/** 3. PILÓTÁK LEKÉRDEZÉSE ÉS SORBARENDEZÉSE **/
$sqlDrivers = "SELECT driver_id, name, race_number, image, `team id` FROM pilotak ORDER BY points DESC";
$resultDrivers = $conn->query($sqlDrivers);

$rawDrivers = [];
if ($resultDrivers) {
    while($d = $resultDrivers->fetch_assoc()) { $rawDrivers[] = $d; }
}

$drivers = [];
if ($hasPrediction && !empty($savedOrder)) {
    foreach ($savedOrder as $savedId) {
        foreach ($rawDrivers as $key => $d) {
            if ($d['driver_id'] == $savedId) {
                $drivers[] = $d;
                unset($rawDrivers[$key]);
                break;
            }
        }
    }
    foreach ($rawDrivers as $d) { $drivers[] = $d; }
} else {
    $drivers = $rawDrivers;
}

?>
<!DOCTYPE html>
<html lang="hu">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes, maximum-scale=1.0">
  <title>The Pitwall - F1 Fan Club</title>
  <link rel="icon" type="image/svg+xml" href="https://upload.wikimedia.org/wikipedia/commons/3/33/F1.svg">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { background: #0a0a0a; color: white; font-family: 'Poppins', sans-serif; min-height: 100vh; overflow-x: hidden; }
    
    header { background-color: #0a0a0a; border-bottom: 2px solid rgba(225, 6, 0, 0.3); padding: 0 40px; height: 80px; display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 1000; box-shadow: 0 4px 20px rgba(0,0,0,0.5); }
    .left-header { display: flex; align-items: center; }
    .logo-title { display: flex; align-items: center; gap: 12px; font-size: 1.5rem; font-weight: 800; color: #fff; text-transform: uppercase; letter-spacing: 1px; }
    .logo-title img { width: 40px; height: auto; filter: brightness(0) invert(1); }
    .logo-title span { display: block; margin-top: 4px; }

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

    .hamburger:hover {
        color: #e10600;
    }

    nav { display: flex; gap: 5px; margin: 0 20px; }
    nav a { font-weight: 600; font-size: 0.9rem; text-transform: uppercase; padding: 8px 16px; border-radius: 4px; color: #ffffff !important; text-decoration: none; position: relative; transition: all 0.2s ease; letter-spacing: 0.5px; opacity: 0.9; }
    nav a:hover { color: #e10600 !important; opacity: 1; background: rgba(225, 6, 0, 0.1); }
    nav a.active { color: #e10600 !important; opacity: 1; font-weight: 700; background: rgba(225, 6, 0, 0.15); }
    
    .dropdown-container {
        position: relative;
        display: inline-block;
    }

    .auth {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .welcome {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 0.9rem;
        margin-right: 10px;
        padding: 5px 12px;
        background: rgba(255, 255, 255, 0.05);
        border-radius: 30px;
        border: 1px solid rgba(225, 6, 0, 0.2);
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .welcome:hover {
        background: rgba(225, 6, 0, 0.15);
        border-color: #e10600;
    }

    .welcome img.avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #e10600;
        transition: transform 0.3s;
    }

    .welcome img.avatar:hover {
        transform: scale(1.1);
    }

    .welcome-text {
        color: #ccc;
    }

    .welcome-text span {
        font-weight: 700;
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
        transition: all 0.2s cubic-bezier(0.2, 0.9, 0.4, 1.1);
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

    .clickable-user {
        cursor: pointer;
    }

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

    @media (min-width: 993px) {
        nav {
            display: flex !important;
        }
    }

    @media (max-width: 992px) {
        .hamburger {
            display: block;
        }
        
        .left-header {
            display: none;
        }
        
        nav {
            display: none;
            position: absolute;
            top: 80px;
            left: 0;
            right: 0;
            background: #0a0a0a;
            border-bottom: 2px solid #e10600;
            flex-direction: column;
            gap: 0;
            margin: 0;
            z-index: 1000;
            box-shadow: 0 10px 20px rgba(0,0,0,0.5);
        }
        
        nav.open {
            display: flex;
        }
        
        nav a {
            padding: 15px 20px;
            margin: 0;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            text-align: center;
            font-size: 1rem;
        }
        
        nav a:last-child {
            border-bottom: none;
        }
        
        header {
            position: sticky;
            top: 0;
            flex-wrap: nowrap;
            justify-content: flex-start;
            gap: 15px;
            padding: 0 20px;
        }
        
        .hamburger {
            margin-right: auto;
        }
        
        .auth {
            margin-left: 0;
        }
    }

    @media (max-width: 768px) {
        .auth {
            flex-wrap: wrap;
            justify-content: flex-end;
        }
        .welcome {
            width: 100%;
            justify-content: center;
            margin-right: 0;
            margin-bottom: 5px;
        }
    }
    
    @media (max-width: 576px) {
        .hamburger {
            font-size: 24px;
            padding: 8px;
        }
        header {
            padding: 0 15px;
        }
        nav a {
            padding: 12px 15px;
            font-size: 0.85rem;
        }
    }
    
    /* --- HEADER SECTION - MORE COMPACT --- */
    .pitwall-container { 
        max-width: 1200px;
        width: 100%;
        margin: 30px auto; 
        padding: 0 20px;
        box-sizing: border-box;
    }

    .pitwall-header { 
        text-align: center; 
        margin-bottom: 25px; 
        background: linear-gradient(145deg, #111, #1a1a1a); 
        padding: 20px 25px; 
        border-radius: 15px; 
        border: 1px solid rgba(225,6,0,0.3); 
        box-shadow: 0 8px 25px rgba(0,0,0,0.5); 
    }

    .pitwall-header h1 { 
        font-size: 2.2rem; 
        color: #e10600; 
        font-weight: 900; 
        text-transform: uppercase; 
        letter-spacing: 2px; 
        margin-bottom: 8px; 
        line-height: 1.2;
    }

    .pitwall-header p { 
        color: #aaa; 
        font-size: 1rem; 
        margin-bottom: 12px;
    }

    .race-badge { 
        display: inline-block; 
        background: rgba(225,6,0,0.12); 
        border: 1px solid #e10600; 
        color: #fff; 
        padding: 6px 18px; 
        border-radius: 30px; 
        font-weight: 600; 
        margin-top: 8px; 
        font-size: 1rem;
    }

    .race-badge i {
        margin-right: 6px;
    }

    .deadline-badge { 
        display: block; 
        margin-top: 10px; 
        font-size: 0.85rem; 
        color: #ff8700; 
    }

    .deadline-badge i {
        margin-right: 5px;
    }

    .deadline-badge.passed { 
        color: #DC0000; 
        font-weight: 600; 
    }

    /* Driver items - optimized for delay-based drag */
    .prediction-list { 
        list-style: none; 
        padding: 0; 
        margin: 0; 
        width: 100%;
        max-width: 100%;
        touch-action: pan-y;
    }

    .driver-item { 
        display: flex; 
        align-items: center; 
        background: #15151c; 
        border: 1px solid #2a2a35; 
        margin-bottom: 8px; 
        padding: 12px 15px; 
        border-radius: 10px; 
        cursor: grab; 
        transition: transform 0.2s, box-shadow 0.2s, opacity 0.2s; 
        gap: 10px;
        width: 100%;
        max-width: 100%;
        box-sizing: border-box;
        touch-action: pan-y;
        -webkit-touch-callout: none;
        -webkit-user-select: none;
        user-select: none;
        position: relative;
    }

    .driver-item:active { 
        cursor: grabbing; 
        transition: none;
    }

    .driver-item:hover { 
        border-color: #555; 
        background: #1a1a24; 
    }

    @media (min-width: 769px) {
        .driver-item:hover {
            cursor: grab;
        }
        
        .driver-item:active {
            cursor: grabbing;
        }
    }

    .locked-list .driver-item { 
        cursor: default; 
        opacity: 0.8; 
        touch-action: pan-y;
    }

    .locked-list .driver-item:hover { 
        border-color: #2a2a35; 
        background: #15151c; 
    }

    .locked-list .driver-item:active {
        cursor: default;
    }

    .locked-list .drag-handle { 
        display: none; 
    }

    .sortable-ghost { 
        opacity: 0.4; 
        background: rgba(225,6,0,0.2) !important; 
        border-color: #e10600 !important; 
    }

    .sortable-drag { 
        box-shadow: 0 15px 30px rgba(0,0,0,0.7); 
        transform: scale(1.02);
        opacity: 0.9 !important;
        cursor: grabbing !important;
    }

    .position-badge { 
        width: 36px; 
        height: 36px; 
        min-width: 36px;
        background: #222; 
        border-radius: 8px; 
        display: flex; 
        justify-content: center; 
        align-items: center; 
        font-size: 1.1rem; 
        font-weight: 800; 
        color: #e10600; 
        border: 1px solid #333; 
        flex-shrink: 0; 
        touch-action: pan-y;
    }

    .driver-img { 
        width: 48px; 
        height: 48px; 
        min-width: 48px;
        border-radius: 50%; 
        object-fit: cover; 
        object-position: top; 
        background: #111; 
        border: 2px solid transparent; 
        flex-shrink: 0; 
        touch-action: pan-y;
    }

    .driver-info { 
        flex: 1; 
        min-width: 0;
        overflow: hidden;
        touch-action: pan-y;
    }

    .driver-name { 
        font-size: 1rem; 
        font-weight: 600; 
        color: #fff; 
        display: block; 
        white-space: nowrap; 
        overflow: hidden; 
        text-overflow: ellipsis; 
    }

    .driver-number { 
        font-size: 0.75rem; 
        color: #888; 
        font-weight: 800; 
        display: block;
    }

    .drag-handle { 
        color: #555; 
        font-size: 1.3rem; 
        padding: 10px 8px;
        cursor: grab; 
        flex-shrink: 0; 
        transition: color 0.2s, opacity 0.2s;
        touch-action: none;
        -webkit-touch-callout: none;
    }

    .drag-handle:active {
        cursor: grabbing;
    }

    body.sortable-dragging {
        overflow: hidden !important;
        touch-action: none !important;
    }

    .btn-save { 
        display: block; 
        width: 100%; 
        background: #e10600; 
        color: #fff; 
        text-align: center; 
        padding: 14px; 
        border: none; 
        border-radius: 10px; 
        font-size: 1.1rem; 
        font-weight: 700; 
        text-transform: uppercase; 
        cursor: pointer; 
        transition: 0.3s; 
        margin-top: 25px; 
        box-shadow: 0 5px 15px rgba(225,6,0,0.3); 
    }

    .btn-save:hover { 
        background: #ff1a1a; 
        transform: translateY(-3px); 
        box-shadow: 0 10px 25px rgba(225,6,0,0.5); 
    }

    .btn-save.locked { 
        background: #333; 
        color: #666; 
        cursor: not-allowed; 
        box-shadow: none; 
    }

    .btn-save.locked:hover { 
        transform: none; 
    }

    /* --- REWARDS PANEL - OPTIMIZED FOR SCREEN FIT --- */
    .rewards-panel {
        background: linear-gradient(145deg, #15151e 0%, #0d0d14 100%);
        border-radius: 16px;
        padding: 18px;
        border: 1px solid rgba(212, 175, 55, 0.25);
        box-shadow: 0 8px 25px rgba(0,0,0,0.5);
        position: relative;
        overflow: hidden;
        max-height: calc(100vh - 120px);
        overflow-y: auto;
    }

    .rewards-panel::before {
        content: "";
        position: absolute;
        top: 0;
        right: 0;
        width: 100px;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(212, 175, 55, 0.03));
        transform: skewX(-15deg) translateX(30px);
        pointer-events: none;
    }

    .rewards-title {
        color: #d4af37;
        font-size: 1rem;
        font-weight: 700;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 8px;
        text-transform: uppercase;
        letter-spacing: 1px;
        padding-bottom: 10px;
        border-bottom: 1px solid rgba(212, 175, 55, 0.2);
    }

    .rewards-title i {
        font-size: 1.1rem;
    }

    .reward-items {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .reward-card {
        background: rgba(255, 255, 255, 0.03);
        padding: 10px 12px;
        border-radius: 8px;
        border-left: 3px solid #d4af37;
        display: flex;
        flex-direction: column;
        gap: 2px;
        transition: all 0.2s ease;
    }

    .reward-card:hover {
        background: rgba(212, 175, 55, 0.08);
        transform: translateX(3px);
    }

    .reward-pts {
        color: #d4af37;
        font-weight: 800;
        font-size: 0.9rem;
        letter-spacing: 0.5px;
    }

    .reward-desc {
        color: #bbb;
        font-size: 0.75rem;
        line-height: 1.3;
    }

    .info-text {
        font-size: 0.7rem;
        color: #777;
        margin-top: 12px;
        text-align: center;
        font-style: italic;
        margin-bottom: 12px;
        padding: 6px;
        background: rgba(255, 255, 255, 0.02);
        border-radius: 20px;
    }

    .btn-redeem {
        display: block;
        width: 100%;
        background: linear-gradient(145deg, #d4af37, #b8960c);
        color: #000;
        text-align: center;
        padding: 10px 12px;
        border: none;
        border-radius: 8px;
        font-size: 0.85rem;
        font-weight: 700;
        text-transform: uppercase;
        text-decoration: none;
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(212, 175, 55, 0.2);
        letter-spacing: 0.5px;
    }

    .btn-redeem:hover {
        background: linear-gradient(145deg, #e8c44a, #cca520);
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(212, 175, 55, 0.35);
    }

    .rewards-panel::-webkit-scrollbar {
        width: 4px;
    }

    .rewards-panel::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.05);
        border-radius: 4px;
    }

    .rewards-panel::-webkit-scrollbar-thumb {
        background: rgba(212, 175, 55, 0.5);
        border-radius: 4px;
    }

    .rewards-panel::-webkit-scrollbar-thumb:hover {
        background: rgba(212, 175, 55, 0.8);
    }

    /* Responsive styles - all breakpoints */
    @media (max-width: 992px) {
        .driver-item {
            padding: 11px 12px;
            gap: 10px;
            margin-bottom: 7px;
        }
        
        .position-badge {
            width: 34px;
            height: 34px;
            min-width: 34px;
            font-size: 1rem;
        }
        
        .driver-img {
            width: 45px;
            height: 45px;
            min-width: 45px;
        }
        
        .driver-name {
            font-size: 0.95rem;
        }
        
        .btn-save {
            padding: 13px;
            font-size: 1.05rem;
        }
    }

    @media (max-width: 768px) {
        .pitwall-container { 
            padding: 0 12px; 
            margin: 20px auto;
            width: 100%;
        }
        
        .pitwall-header {
            padding: 18px 20px;
            margin-bottom: 20px;
        }
        
        .pitwall-header h1 { 
            font-size: 1.8rem; 
        }
        
        .pitwall-header p { 
            font-size: 0.9rem; 
        }
        
        .race-badge { 
            font-size: 0.9rem; 
            padding: 5px 15px; 
        }
        
        .deadline-badge { 
            font-size: 0.8rem; 
        }
        
        .driver-item {
            padding: 10px 10px;
            gap: 8px;
            margin-bottom: 6px;
            border-radius: 8px;
        }
        
        .position-badge {
            width: 32px;
            height: 32px;
            min-width: 32px;
            font-size: 0.95rem;
            border-radius: 6px;
        }
        
        .driver-img {
            width: 42px;
            height: 42px;
            min-width: 42px;
            border-width: 2px;
        }
        
        .driver-name {
            font-size: 0.9rem;
        }
        
        .driver-number {
            font-size: 0.7rem;
        }
        
        .drag-handle {
            font-size: 1.2rem;
            padding: 12px 4px;
        }
        
        .btn-save {
            padding: 12px;
            font-size: 1rem;
            margin-top: 20px;
            border-radius: 8px;
        }
    }

    @media (max-width: 576px) {
        .pitwall-container { 
            margin: 15px auto; 
            padding: 0 12px;
        }
        
        .pitwall-header {
            padding: 15px 15px;
            margin-bottom: 18px;
            border-radius: 12px;
        }
        
        .pitwall-header h1 { 
            font-size: 1.6rem; 
            margin-bottom: 5px;
        }
        
        .pitwall-header p { 
            font-size: 0.85rem; 
            margin-bottom: 8px;
        }
        
        .race-badge { 
            font-size: 0.85rem; 
            padding: 5px 12px; 
            margin-top: 5px;
        }
        
        .deadline-badge { 
            font-size: 0.75rem; 
            margin-top: 8px;
        }
        
        .driver-item {
            padding: 8px 8px;
            gap: 6px;
            margin-bottom: 5px;
            border-radius: 7px;
        }
        
        .position-badge {
            width: 28px;
            height: 28px;
            min-width: 28px;
            font-size: 0.85rem;
            border-radius: 5px;
        }
        
        .driver-img {
            width: 38px;
            height: 38px;
            min-width: 38px;
            border-width: 1.5px;
        }
        
        .driver-name {
            font-size: 0.85rem;
        }
        
        .driver-number {
            font-size: 0.65rem;
        }
        
        .drag-handle {
            font-size: 1.1rem;
            padding: 10px 3px;
        }
        
        .btn-save {
            padding: 11px;
            font-size: 0.95rem;
            margin-top: 18px;
        }
    }

    @media (max-width: 480px) {
        .pitwall-container { 
            margin: 12px auto; 
            padding: 0 10px;
            width: 100%;
        }
        
        .pitwall-header {
            padding: 12px 12px;
            margin-bottom: 15px;
        }
        
        .pitwall-header h1 { 
            font-size: 1.4rem; 
        }
        
        .pitwall-header p { 
            font-size: 0.8rem; 
        }
        
        .race-badge { 
            font-size: 0.8rem; 
            padding: 4px 10px; 
        }
        
        .deadline-badge { 
            font-size: 0.7rem; 
        }
        
        .driver-item {
            padding: 7px 7px;
            gap: 5px;
            margin-bottom: 4px;
            border-radius: 6px;
        }
        
        .position-badge {
            width: 26px;
            height: 26px;
            min-width: 26px;
            font-size: 0.8rem;
            border-radius: 5px;
        }
        
        .driver-img {
            width: 35px;
            height: 35px;
            min-width: 35px;
            border-width: 1.5px;
        }
        
        .driver-name {
            font-size: 0.8rem;
        }
        
        .driver-number {
            font-size: 0.6rem;
        }
        
        .drag-handle {
            font-size: 1rem;
            padding: 8px 2px;
        }
        
        .btn-save {
            padding: 10px;
            font-size: 0.9rem;
            margin-top: 15px;
        }
    }

    @media (max-width: 400px) {
        .pitwall-container { 
            padding: 0 6px; 
        }
        
        .pitwall-header {
            padding: 10px 10px;
        }
        
        .pitwall-header h1 { 
            font-size: 1.3rem; 
        }
        
        .pitwall-header p { 
            font-size: 0.75rem; 
        }
        
        .race-badge { 
            font-size: 0.75rem; 
            padding: 4px 10px; 
        }
        
        .deadline-badge { 
            font-size: 0.65rem; 
        }
        
        .driver-item {
            padding: 6px 6px;
            gap: 5px;
            margin-bottom: 4px;
        }
        
        .position-badge {
            width: 24px;
            height: 24px;
            min-width: 24px;
            font-size: 0.75rem;
            border-radius: 4px;
        }
        
        .driver-img {
            width: 32px;
            height: 32px;
            min-width: 32px;
            border-width: 1px;
        }
        
        .driver-name {
            font-size: 0.75rem;
        }
        
        .driver-number {
            font-size: 0.55rem;
        }
        
        .drag-handle {
            font-size: 0.95rem;
            padding: 6px 2px;
        }
        
        .btn-save {
            padding: 9px;
            font-size: 0.85rem;
            margin-top: 12px;
        }
    }

    @media (max-width: 360px) {
        .pitwall-header h1 { 
            font-size: 1.2rem; 
        }
        
        .pitwall-header p { 
            font-size: 0.7rem; 
        }
        
        .race-badge { 
            font-size: 0.7rem; 
            padding: 3px 8px; 
        }
        
        .driver-item {
            padding: 5px 5px;
            gap: 4px;
        }
        
        .position-badge {
            width: 22px;
            height: 22px;
            min-width: 22px;
            font-size: 0.7rem;
            border-radius: 4px;
        }
        
        .driver-img {
            width: 30px;
            height: 30px;
            min-width: 30px;
        }
        
        .driver-name {
            font-size: 0.7rem;
        }
        
        .driver-number {
            font-size: 0.5rem;
        }
        
        .drag-handle {
            font-size: 0.9rem;
        }
    }

    @media (max-width: 340px) {
        .driver-item {
            padding: 5px 5px;
            gap: 4px;
        }
        
        .position-badge {
            width: 20px;
            height: 20px;
            min-width: 20px;
            font-size: 0.65rem;
        }
        
        .driver-img {
            width: 28px;
            height: 28px;
            min-width: 28px;
        }
        
        .driver-name {
            font-size: 0.65rem;
        }
        
        .drag-handle {
            display: none;
        }
        
        .btn-save {
            padding: 8px;
            font-size: 0.8rem;
        }
    }

    /* Layout responsive */
    .pitwall-layout { 
        display: flex; 
        gap: 25px; 
        align-items: flex-start; 
    }

    .pitwall-left { 
        flex: 1 1 auto;
        width: 100%;
        max-width: 100%;
        min-width: 0;
    }

    .pitwall-right { 
        flex: 0 0 300px;
        width: 300px;
        position: sticky; 
        top: 100px;
        align-self: flex-start;
        max-height: calc(100vh - 120px);
    }

    @media (max-width: 992px) {
        .pitwall-layout { 
            flex-direction: column; 
            gap: 20px;
        }
        
        .pitwall-left {
            width: 100%;
            max-width: 100%;
        }
        
        .pitwall-right { 
            flex: auto; 
            width: 100%; 
            max-width: 100%;
            position: static;
            max-height: none;
        }
        
        .rewards-panel {
            max-height: none;
            overflow-y: visible;
            padding: 16px;
        }
        
        .reward-items {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
        }
        
        .reward-card {
            padding: 10px;
        }
    }

    @media (max-width: 768px) {
        .pitwall-layout { 
            gap: 15px;
        }
        
        .rewards-panel {
            padding: 14px;
        }
        
        .reward-items {
            grid-template-columns: 1fr;
        }
        
        .rewards-title {
            font-size: 0.95rem;
            margin-bottom: 10px;
        }
        
        .reward-pts {
            font-size: 0.85rem;
        }
        
        .reward-desc {
            font-size: 0.7rem;
        }
        
        .info-text {
            font-size: 0.65rem;
            margin-top: 10px;
            margin-bottom: 10px;
        }
    }

    @media (max-width: 480px) {
        .rewards-panel {
            padding: 12px;
        }
        
        .reward-card {
            padding: 8px 10px;
        }
        
        .reward-pts { 
            font-size: 0.8rem; 
        }
        
        .reward-desc { 
            font-size: 0.65rem; 
        }
        
        .btn-redeem { 
            padding: 8px; 
            font-size: 0.75rem; 
        }
    }

    body.sortable-dragging .driver-item::after {
        display: none !important;
    }
<<<<<<< HEAD
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
=======
>>>>>>> f81424192996985be2da559c0f9a2c1f13f5eb7f
  </style>
</head>
<body>

<header>
    <div class="left-header">
      <div class="logo-title">
        <img src="https://upload.wikimedia.org/wikipedia/commons/3/33/F1.svg" alt="F1 Logo">
        <span>Fan Club</span>
      </div>
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
      <a href="/f1fanclub/pitwall/pitwall.php" class="active"><i class="fas fa-trophy"></i> A Fal</a>
    </nav>
    
    <div class="dropdown-container" id="userDropdownContainer">
        <div class="auth">
            <div class="welcome" style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                <img src="/f1fanclub/uploads/<?= htmlspecialchars($profile_image ?? 'default_avatar.png'); ?>" 
                     class="avatar clickable-user"
                     alt="Profilkép" 
                     onclick="openUserProfile('<?= htmlspecialchars(addslashes($username)); ?>')"
                     style="width:35px; height:35px; border-radius:50%;border: 2px solid;  object-fit: cover; border-color: <?= htmlspecialchars($teamColor); ?>;">
                <span class="welcome-text">
                    <span class="clickable-user" 
                          onclick="openUserProfile('<?= htmlspecialchars(addslashes($username)); ?>')"
                          style="color: <?= htmlspecialchars($teamColor); ?>; font-weight:bold;">
                        <?= htmlspecialchars($username); ?>
                    </span>
                </span>
                <i class="fas fa-chevron-down dropdown-arrow-icon"></i>
            </div>
        </div>
        
        <div class="dropdown-menu-modern">
            <a href="/f1fanclub/profile/profile.php">
                <i class="fas fa-user-circle"></i> Profilom
            </a>
            <a href="/f1fanclub/messages/messages.php">
                <i class="fas fa-envelope"></i> Üzenetek
            </a>
            <?php if ($isAdmin): ?>
                <a href="/f1fanclub/admin/admin.php" style="position: relative;">
                    <i class="fas fa-shield-alt"></i> Admin Panel
                    <span class="admin-badge">ADMIN</span>
                </a>
            <?php endif; ?>
            <div class="dropdown-divider"></div>
            <a href="/f1fanclub/logout/logout.php">
                <i class="fas fa-sign-out-alt"></i> Kijelentkezés
            </a>
        </div>
    </div>
</header>

<div class="pitwall-container">
    <div class="pitwall-header">
        <h1>A Fal</h1>
        <p>Állítsd be a pontos végeredményt, és gyűjts pontokat a nyereményekért!</p>
        
        <?php if($nextRace): ?>
            <div class="race-badge">
                <i class="fas fa-flag-checkered"></i> <?= htmlspecialchars($nextRace['race_name']) ?>
            </div>
            <?php if($isDeadlinePassed): ?>
                <span class="deadline-badge passed"><i class="fas fa-lock"></i> A tippek lezárultak! A futam elkezdődött.</span>
            <?php else: ?>
                <span class="deadline-badge"><i class="fas fa-hourglass-half"></i> Beküldési határidő: <?= $deadlineStr ?></span>
            <?php endif; ?>
        <?php else: ?>
            <div class="race-badge" style="background: #333; border-color: #555;">Jelenleg nincs aktív futam.</div>
        <?php endif; ?>
    </div>

    <div class="pitwall-layout">
        
        <div class="pitwall-left">
            <ul class="prediction-list <?= $isDeadlinePassed ? 'locked-list' : '' ?>" id="predictionList">
                <?php if(!empty($drivers) && $nextRace): ?>
                    <?php foreach($drivers as $index => $driver): 
                        $sqlTeam = "SELECT team_name FROM csapatok WHERE team_id = " . intval($driver['team id'] ?? 0);
                        $teamRes = $conn->query($sqlTeam);
                        $teamName = ($teamRes && $teamRes->num_rows > 0) ? $teamRes->fetch_assoc()['team_name'] : '';
                        $driverColor = getTeamColor($teamName);
                        
                        $imgFileName = trim(basename($driver['image'])); 
                        $finalImgPath = '../drivers/' . $imgFileName;
                    ?>
                        <li class="driver-item" data-driver-id="<?= htmlspecialchars($driver['driver_id']) ?>">
                            <div class="position-badge"><?= $index + 1 ?>.</div>
                            <img src="<?= htmlspecialchars($finalImgPath) ?>" class="driver-img" style="border-color: <?= htmlspecialchars($driverColor) ?>;" alt="<?= htmlspecialchars($driver['name']) ?>" onerror="this.src='../drivers/default.png'; this.onerror=null;">
                            
                            <div class="driver-info">
                                <span class="driver-name" style="color: <?= htmlspecialchars($driverColor) ?>;">
                                    <?= htmlspecialchars($driver['name']) ?>
                                </span>
                                <span class="driver-number">#<?= htmlspecialchars($driver['race_number']) ?></span>
                            </div>
                            
                            <i class="fas fa-grip-lines drag-handle"></i>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="text-align: center; color: #888; padding: 20px;">Nincs tippek leadására alkalmas futam vagy pilóta.</p>
                <?php endif; ?>
            </ul>

            <?php if($nextRace): ?>
                <?php if($isDeadlinePassed): ?>
                    <button class="btn-save locked" disabled><i class="fas fa-lock"></i> Tippelés Lezárva</button>
                <?php else: ?>
                    <button class="btn-save" onclick="savePrediction(<?= $nextRace['race_id'] ?>)" id="saveBtn">
                        <i class="fas fa-save"></i> <?= $hasPrediction ? 'Tipp módosításainak mentése' : 'Tippem mentése' ?>
                    </button>
                <?php endif; ?>
            <?php endif; ?>

            <a href="/f1fanclub/profile/profile.php" class="btn-save" style="background: #1a1a24; border: 1px solid #333; color: #aaa; margin-top: 15px; text-decoration: none; box-shadow: none; font-size: 1rem;">
                <i class="fas fa-medal"></i> Pontjaim számának megtekintése
            </a>
        </div>
        
        <div class="pitwall-right">
            <div class="rewards-panel">
                <div class="rewards-title">
                    <i class="fas fa-gift"></i> Beváltható Nyeremények
                </div>
                
                <div class="reward-items">
                    <div class="reward-card">
                        <span class="reward-pts">150 PONT</span>
                        <span class="reward-desc">Exkluzív "Pitwall Stratéga" digitális profil jelvény.</span>
                    </div>
                    <div class="reward-card">
                        <span class="reward-pts">300 PONT</span>
                        <span class="reward-desc">Kiemelt VIP rang és egyedi szín a rajongói chaten.</span>
                    </div>
                    <div class="reward-card">
                        <span class="reward-pts">500 PONT</span>
                        <span class="reward-desc">10% kedvezmény kupon a hivatalos F1 Webshopba.</span>
                    </div>
                    <div class="reward-card">
                        <span class="reward-pts">800 PONT</span>
                        <span class="reward-desc">Részvétel a havi F1 Fan Club ajándékcsomag sorsoláson!</span>
                    </div>
                </div>
                
                <div class="info-text">
                    *Minden telitalálatért 10 pont jár a futamok leintése után.
                </div>
                
                <a href="bevaltas.php" class="btn-redeem">
                    <i class="fas fa-shopping-cart" style="margin-right: 5px;"></i> Pontjaim beváltása
                </a>
            </div>
        </div>
        
    </div> 
</div>

<script>
    // Hamburger menu
    const hamburgerBtn = document.getElementById('hamburgerBtn');
    const mainNav = document.getElementById('mainNav');
    
    if (hamburgerBtn && mainNav) {
        hamburgerBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            mainNav.classList.toggle('open');
        });
        
        document.addEventListener('click', function(event) {
            if (mainNav.classList.contains('open') && 
                !mainNav.contains(event.target) && 
                !hamburgerBtn.contains(event.target)) {
                mainNav.classList.remove('open');
            }
        });
        
        const navLinks = mainNav.querySelectorAll('a');
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                mainNav.classList.remove('open');
            });
        });
    }

    // Sortable with delay-based drag
    const listElement = document.getElementById('predictionList');
    const isLocked = <?= $isDeadlinePassed ? 'true' : 'false' ?>;
    
    if(listElement && !isLocked) {
        const sortable = new Sortable(listElement, {
            animation: 150,
            ghostClass: 'sortable-ghost',
            dragClass: 'sortable-drag',
            handle: '.driver-item',
            draggable: '.driver-item',
            scroll: true,
            scrollSensitivity: 30,
            scrollSpeed: 10,
            bubbleScroll: true,
            delay: 200,
            touchStartThreshold: 5,
            disabled: false,
            
            onStart: function(evt) {
                document.body.style.overflow = 'hidden';
                document.body.classList.add('sortable-dragging');
                evt.item.style.opacity = '0.8';
                
                const dragHandles = listElement.querySelectorAll('.drag-handle');
                dragHandles.forEach(h => {
                    h.style.color = '#e10600';
                    h.style.opacity = '0.7';
                });
            },
            
            onEnd: function (evt) {
                document.body.style.overflow = '';
                document.body.classList.remove('sortable-dragging');
                evt.item.style.opacity = '';
                
                const dragHandles = listElement.querySelectorAll('.drag-handle');
                dragHandles.forEach(h => {
                    h.style.color = '';
                    h.style.opacity = '';
                });
                
                const badges = listElement.querySelectorAll('.position-badge');
                badges.forEach((badge, index) => {
                    badge.innerHTML = (index + 1) + '.';
                });
            },
            
            onMove: function(evt) {
                return true;
            }
        });
        
        // Desktop hover hint
        if (window.innerWidth > 768) {
            const driverItems = listElement.querySelectorAll('.driver-item');
            driverItems.forEach(item => {
                item.addEventListener('mouseenter', function() {
                    const handle = this.querySelector('.drag-handle');
                    if (handle) handle.style.color = '#888';
                });
                
                item.addEventListener('mouseleave', function() {
                    const handle = this.querySelector('.drag-handle');
                    if (handle) handle.style.color = '';
                });
            });
        }
    }

    function savePrediction(raceId) {
        const btn = document.getElementById('saveBtn');
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mentés...';
        btn.disabled = true;

        const items = document.querySelectorAll('.driver-item');
        let orderedDriverIds = [];
        
        items.forEach((item) => {
            orderedDriverIds.push(item.getAttribute('data-driver-id'));
        });

        fetch('pitwall_api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'save_prediction',
                race_id: raceId,
                predictions: orderedDriverIds
            })
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                btn.innerHTML = '<i class="fas fa-check"></i> Tipp módosításainak mentése';
                btn.disabled = false;
                btn.style.background = '#28a745';
                setTimeout(() => { btn.style.background = ''; }, 2000);
            } else {
                alert("Hiba mentés közben: " + data.error);
                btn.innerHTML = '<i class="fas fa-save"></i> Újrapróbálás';
                btn.disabled = false;
            }
        });
    }

    // Dropdown menu
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

    function openUserProfile(username) {
        window.location.href = '/f1fanclub/profile/profile.php';
    }
</script>
</body>
</html>