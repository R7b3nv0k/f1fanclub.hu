<?php
// live.php - HELYEZD A /race MAPPÁBA!
session_start();

$DB_HOST = "localhost";
$DB_USER = "swmjndga_swmjndga";
$DB_PASS = "Teszt1234!";
$DB_NAME = "swmjndga_f1adat";

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) { die("DB Error"); }

// --- BIZTONSÁGI KAPU: HA ARCHIVÁLT A FUTAM, KIDOBJUK A FŐOLDALRA! ---
$statusCheck = $conn->query("SELECT status FROM race_control WHERE race_id = 25 LIMIT 1")->fetch_assoc();
if (!$statusCheck || $statusCheck['status'] === 'archived') {
    header("Location: ../index.php");
    exit;
}
// ---------------------------------------------------------------------

$isLoggedIn = isset($_SESSION['username']);
$username = $isLoggedIn ? $_SESSION['username'] : null;

function getTeamColor($team) {
    switch ($team) {
<<<<<<< HEAD
        case 'Red Bull': return '#1E41FF'; 
        case 'Ferrari': return '#DC0000'; 
        case 'Mercedes': return '#00D2BE';
        case 'McLaren': return '#FF8700'; 
        case 'Aston Martin': return '#006F62'; 
        case 'Alpine': return '#0090FF';
        case 'Williams': return '#00A0DE'; 
        case 'RB': return '#2b2bff'; 
        case 'Racing Bulls': return '#2b2bff';
        case 'Audi': return '#e3000f';
        case 'Haas F1 Team': return '#B6BABD'; 
        case 'Haas': return '#B6BABD';
        case 'Cadillac': return '#B6BABD'; 
        default: return '#ffffff';
=======
        case 'Red Bull': return '#1E41FF'; case 'Ferrari': return '#DC0000'; case 'Mercedes': return '#00D2BE';
        case 'McLaren': return '#FF8700'; case 'Aston Martin': return '#006F62'; case 'Alpine': return '#0090FF';
        case 'Williams': return '#00A0DE'; case 'RB': return '#2b2bff'; case 'Audi': return '#e3000f';
        case 'Haas F1 Team': return '#B6BABD'; case 'Cadillac': return '#1b1b1b'; default: return '#ffffff';
>>>>>>> f81424192996985be2da559c0f9a2c1f13f5eb7f
    }
}

$profile_image = null; $fav_team = null; $teamColor = '#ffffff'; $isAdmin = false;
if ($isLoggedIn) {
    $stmt = $conn->prepare("SELECT profile_image, fav_team, role FROM users WHERE username=?");
    $stmt->bind_param("s", $username); $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $profile_image = $row['profile_image'] ?? null; 
    $fav_team = $row['fav_team'] ?? null;
    $teamColor = getTeamColor($fav_team);
    $isAdmin = !empty($row['role']) && $row['role'] === 'admin';
    $stmt->close();
}

$teamColorsJS = [
    'Red Bull' => '#1E41FF', 'Ferrari' => '#DC0000', 'Mercedes' => '#00D2BE',
    'McLaren' => '#FF8700', 'Aston Martin' => '#006F62', 'Alpine' => '#0090FF',
    'Williams' => '#00A0DE', 'RB' => '#2b2bff', 'Racing Bulls' => '#2b2bff',
    'Audi' => '#e3000f', 'Haas F1 Team' => '#B6BABD', 'Haas' => '#B6BABD', 'Cadillac' => '#B6BABD'
];
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes, viewport-fit=cover">
    <title>ÉLŐ: Kanadai Nagydíj 2026</title>
    <link rel="stylesheet" href="../css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/svg+xml" href="https://upload.wikimedia.org/wikipedia/commons/3/33/F1.svg">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,400;0,700;0,900;1,400&family=Roboto+Mono:wght@500;700&display=swap" rel="stylesheet">
    <style>
<<<<<<< HEAD
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body { 
            background-color: #050505; 
            background-image: radial-gradient(circle at 50% 0%, #1a0505 0%, #050505 60%); 
            background-attachment: fixed; 
            font-family: 'Montserrat', sans-serif; 
            color: #fff; 
            padding-top: 80px;
            min-height: 100vh;
        }
        
        /* Header */
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
            white-space: nowrap;
        }
        
        nav a:hover { color: #e10600 !important; opacity: 1; background: rgba(225, 6, 0, 0.1); }
        nav a.active { color: #e10600 !important; opacity: 1; font-weight: 700; background: rgba(225, 6, 0, 0.15); }
        
        /* Dropdown */
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
        
        .clickable-user { cursor: pointer; }
        .clickable-user:hover { opacity: 0.8; }

        /* Live wrapper - PC view with proper scrolling */
        .live-wrapper { 
            display: flex; 
            gap: 20px; 
            max-width: 1700px; 
            width: 98%; 
            margin: 20px auto; 
            align-items: stretch;
            height: calc(100vh - 120px);
        }
        
        .telemetry-panel { 
            flex: 3; 
            min-width: 0;
            overflow-y: auto;
            padding-right: 5px;
        }
        
        .telemetry-panel::-webkit-scrollbar { width: 6px; }
        .telemetry-panel::-webkit-scrollbar-thumb { background: #444; border-radius: 3px; }
        
        /* Chat panel */
        .chat-panel { 
            flex: 1.2; 
            background: #0a0a0a; 
            border: 1px solid #333; 
            border-radius: 12px; 
            display: flex; 
            flex-direction: column; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.8); 
            overflow: hidden;
            height: 100%;
        }
        
        .chat-header { 
            background: #111; 
            padding: 15px 20px; 
            font-weight: 900; 
            text-transform: uppercase; 
            color: #fff; 
            border-bottom: 2px solid #e10600; 
            display: flex; 
            align-items: center; 
            gap: 10px;
            flex-shrink: 0;
        }
        
        .chat-messages { 
            flex: 1; 
            overflow-y: auto; 
            padding: 15px; 
            display: flex; 
            flex-direction: column; 
            gap: 15px; 
        }
        
=======
        body { background-color: #050505; background-image: radial-gradient(circle at 50% 0%, #1a0505 0%, #050505 60%); background-attachment: fixed; font-family: 'Montserrat', sans-serif; color: #fff; }
        
        .live-wrapper { display: flex; gap: 20px; max-width: 1700px; width: 98%; margin: 30px auto; align-items: flex-start; }
        .telemetry-panel { flex: 3; min-width: 0; } 
        
        .chat-panel { 
            flex: 1; 
            background: #0a0a0a; 
            border: 1px solid #333; 
            border-radius: 12px; 
            height: calc(100vh - 120px); 
            display: flex; 
            flex-direction: column; 
            position: sticky; 
            top: 100px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.8); 
            overflow: hidden; 
        }
        .chat-header { background: #111; padding: 15px 20px; font-weight: 900; text-transform: uppercase; color: #fff; border-bottom: 2px solid #e10600; display: flex; align-items: center; gap: 10px; }
        .chat-messages { flex: 1; overflow-y: auto; padding: 15px; display: flex; flex-direction: column; gap: 15px; }
>>>>>>> f81424192996985be2da559c0f9a2c1f13f5eb7f
        .chat-messages::-webkit-scrollbar { width: 6px; }
        .chat-messages::-webkit-scrollbar-thumb { background: #444; border-radius: 3px; }

        .chat-msg { display: flex; gap: 12px; animation: fadeIn 0.3s ease; }
<<<<<<< HEAD
        .chat-msg img { width: 35px; height: 35px; border-radius: 50%; object-fit: cover; border: 2px solid transparent; flex-shrink: 0; }
        .chat-msg-body { 
            background: #1a1a1a; 
            padding: 10px 14px; 
            border-radius: 0 12px 12px 12px; 
            font-size: 0.9rem; 
            color: #ddd; 
            width: 100%; 
            border: 1px solid #222; 
        }
        
=======
        .chat-msg img { width: 35px; height: 35px; border-radius: 50%; object-fit: cover; border: 2px solid transparent; }
        .chat-msg-body { background: #1a1a1a; padding: 10px 14px; border-radius: 0 12px 12px 12px; font-size: 0.9rem; color: #ddd; width: 100%; border: 1px solid #222; }
>>>>>>> f81424192996985be2da559c0f9a2c1f13f5eb7f
        .chat-user-info { display: flex; justify-content: space-between; margin-bottom: 5px; font-size: 0.75rem; }
        .chat-user-name { font-weight: 800; text-transform: uppercase; }
        .chat-time { color: #666; }
        
<<<<<<< HEAD
        .chat-input-area { 
            display: flex; 
            padding: 15px; 
            background: #111; 
            border-top: 1px solid #333; 
            flex-shrink: 0;
        }
        .chat-input-area input { 
            flex: 1; 
            padding: 12px 15px; 
            border-radius: 25px; 
            border: 1px solid #444; 
            background: #000; 
            color: #fff; 
            font-family: 'Poppins', sans-serif; 
            outline: none; 
            transition: border 0.3s; 
            font-size: 16px;
        }
        .chat-input-area input:focus { border-color: #e10600; }
        .chat-input-area button { 
            background: #e10600; 
            color: #fff; 
            border: none; 
            border-radius: 50%; 
            width: 45px; 
            height: 45px; 
            margin-left: 10px; 
            cursor: pointer; 
            transition: 0.2s; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            flex-shrink: 0;
        }
        .chat-input-area button:hover { background: #ff1a1a; transform: scale(1.05); }
        
        .chat-guest-msg { 
            padding: 20px; 
            text-align: center; 
            color: #888; 
            font-size: 0.9rem; 
            background: #111; 
            border-top: 1px solid #333; 
            flex-shrink: 0;
        }
        .chat-guest-msg a { color: #e10600; text-decoration: underline; }

        .race-header { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            background: rgba(15, 15, 20, 0.8); 
            backdrop-filter: blur(10px); 
            padding: 25px 30px; 
            border-radius: 12px; 
            border-bottom: 4px solid #e10600; 
            margin-bottom: 30px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.5); 
            flex-shrink: 0;
        }
        
        .race-header h1 { 
            margin: 0; 
            font-size: 2.2rem; 
            font-weight: 900; 
            text-transform: uppercase; 
            letter-spacing: 2px; 
        }
        
        .lap-counter { font-size: 2.8rem; font-weight: 900; color: #fff; font-family: 'Roboto Mono', monospace; line-height: 1; }
        .lap-label { font-size: 1rem; color: #888; text-transform: uppercase; font-weight: 700; letter-spacing: 1px; margin-bottom: 5px; text-align:right; }
        
        .indicators { display: flex; gap: 15px; margin-top: 10px; flex-wrap: wrap; }
        .indicator-badge { 
            padding: 5px 15px; 
            border-radius: 6px; 
            font-weight: 800; 
            font-size: 0.9rem; 
            text-transform: uppercase; 
            display: flex; 
            align-items: center; 
            gap: 8px; 
        }
        .weather-sunny { background: rgba(255, 204, 0, 0.1); color: #ffcc00; border: 1px solid #ffcc00; }
        .weather-rain { background: rgba(0, 122, 255, 0.1); color: #007aff; border: 1px solid #007aff; }
        .sc-active { background: rgba(255, 204, 0, 0.2); color: #ffcc00; border: 2px solid #ffcc00; animation: blink 1s infinite; }
        
        .leaderboard-header { 
            display: grid; 
            grid-template-columns: 60px 3fr 2.5fr 180px 120px 100px; 
            padding: 10px 20px; 
            color: #888; 
            text-transform: uppercase; 
            font-size: 0.8rem; 
            font-weight: 700; 
            letter-spacing: 1px; 
            border-bottom: 2px solid #333; 
            margin-bottom: 10px; 
            flex-shrink: 0;
        }
        
        .leaderboard-grid { 
            position: relative; 
            width: 100%; 
            transition: height 0.5s; 
            min-height: 200px;
        }
        
        .telemetry-row { 
            position: absolute; 
            left: 0; 
            right: 0; 
            height: 70px; 
            display: grid; 
            grid-template-columns: 60px 3fr 2.5fr 180px 120px 100px; 
            align-items: center; 
            background: linear-gradient(90deg, #151515, #0f0f0f); 
            border-radius: 8px; 
            border-left: 4px solid transparent; 
            padding: 0 15px; 
            box-shadow: 0 4px 10px rgba(0,0,0,0.3); 
            transition: transform 0.6s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.4s ease; 
        }
        
        .telemetry-row.leader { background: linear-gradient(90deg, rgba(225,6,0,0.15), #0f0f0f); }
        .telemetry-row.sc-mode { background: linear-gradient(90deg, rgba(255,204,0,0.1), #0f0f0f); border-left-color: #ffcc00; }
        
        .pos-num { font-weight: 900; font-size: 1.5rem; width: 40px; text-align: center; }
        .driver-info { display: flex; align-items: center; gap: 12px; }
        .driver-pic-box { 
            width: 45px; 
            height: 45px; 
            flex-shrink: 0; 
            border-radius: 50%; 
            border: 2px solid #333; 
            background: rgba(255,255,255,0.05); 
            overflow: hidden; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
        }
        .driver-portrait { width: 100%; height: 100%; object-fit: cover; object-position: top center; transform-origin: top center; transform: scale(1.3); }
        .team-logo-box { width: 35px; height: 35px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; }
        .team-logo-small { max-width: 100%; max-height: 100%; object-fit: contain; }
        .driver-name { font-weight: 800; color: #fff; font-size: 1.1rem; }
        .driver-abbr { color: #888; font-size: 0.8rem; font-weight: 700; }
        .team-name { font-weight: 600; font-size: 0.95rem; }
        .gap-text { font-family: 'Roboto Mono', monospace; color: #e10600; font-weight: 700; font-size: 1.1rem; }
        .gap-sc { color: #ffcc00 !important; }
        
        .tyre-container { display: flex; align-items: center; gap: 8px; }
        .tyre-badge { width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 900; color: #000; font-size: 0.85rem; }
=======
        .chat-input-area { display: flex; padding: 15px; background: #111; border-top: 1px solid #333; }
        .chat-input-area input { flex: 1; padding: 12px 15px; border-radius: 25px; border: 1px solid #444; background: #000; color: #fff; font-family: 'Poppins', sans-serif; outline: none; transition: border 0.3s; }
        .chat-input-area input:focus { border-color: #e10600; }
        .chat-input-area button { background: #e10600; color: #fff; border: none; border-radius: 50%; width: 45px; height: 45px; margin-left: 10px; cursor: pointer; transition: 0.2s; display: flex; align-items: center; justify-content: center; }
        .chat-input-area button:hover { background: #ff1a1a; transform: scale(1.05); }
        .chat-guest-msg { padding: 20px; text-align: center; color: #888; font-size: 0.9rem; background: #111; border-top: 1px solid #333; }
        .chat-guest-msg a { color: #e10600; text-decoration: underline; }

        .race-header { display: flex; justify-content: space-between; align-items: center; background: rgba(15, 15, 20, 0.8); backdrop-filter: blur(10px); padding: 25px 30px; border-radius: 12px; border-bottom: 4px solid #e10600; margin-bottom: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        .race-header h1 { margin: 0; font-size: 2.2rem; font-weight: 900; text-transform: uppercase; letter-spacing: 2px; }
        .lap-counter { font-size: 2.8rem; font-weight: 900; color: #fff; font-family: 'Roboto Mono', monospace; line-height: 1; }
        .lap-label { font-size: 1rem; color: #888; text-transform: uppercase; font-weight: 700; letter-spacing: 1px; margin-bottom: 5px; text-align:right;}
        .indicators { display: flex; gap: 15px; margin-top: 10px; }
        .indicator-badge { padding: 5px 15px; border-radius: 6px; font-weight: 800; font-size: 0.9rem; text-transform: uppercase; display: flex; align-items: center; gap: 8px; }
        .weather-sunny { background: rgba(255, 204, 0, 0.1); color: #ffcc00; border: 1px solid #ffcc00; }
        .weather-rain { background: rgba(0, 122, 255, 0.1); color: #007aff; border: 1px solid #007aff; }
        .sc-active { background: rgba(255, 204, 0, 0.2); color: #ffcc00; border: 2px solid #ffcc00; animation: blink 1s infinite; }
        .leaderboard-header { display: grid; grid-template-columns: 80px 3.5fr 2.5fr 200px 150px 120px; padding: 10px 20px; color: #888; text-transform: uppercase; font-size: 0.85rem; font-weight: 700; letter-spacing: 1px; border-bottom: 2px solid #333; margin-bottom: 10px; }
        .leaderboard-grid { position: relative; width: 100%; transition: height 0.5s; }
        .telemetry-row { position: absolute; left: 0; right: 0; height: 70px; display: grid; grid-template-columns: 80px 3.5fr 2.5fr 200px 150px 120px; align-items: center; background: linear-gradient(90deg, #151515, #0f0f0f); border-radius: 8px; border-left: 4px solid transparent; padding: 0 20px; box-shadow: 0 4px 10px rgba(0,0,0,0.3); transition: transform 0.6s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.4s ease; }
        .telemetry-row.leader { border-left-color: #e10600; background: linear-gradient(90deg, rgba(225,6,0,0.1), #0f0f0f); }
        .telemetry-row.sc-mode { background: linear-gradient(90deg, rgba(255,204,0,0.1), #0f0f0f); border-left-color: #ffcc00; }
        .pos-num { font-weight: 900; font-size: 1.6rem; width: 40px; text-align: center; }
        .driver-info { display: flex; align-items: center; gap: 15px; }
        .driver-pic-box { width: 50px; height: 50px; flex-shrink: 0; border-radius: 50%; border: 2px solid #333; background: rgba(255,255,255,0.05); overflow: hidden; display: flex; align-items: center; justify-content: center; }
        .driver-portrait { width: 100%; height: 100%; object-fit: cover; object-position: top center; transform-origin: top center; transform: scale(1.3); }
        .team-logo-box { width: 40px; height: 40px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; }
        .team-logo-small { max-width: 100%; max-height: 100%; object-fit: contain; }
        .driver-name { font-weight: 800; color: #fff; font-size: 1.2rem; }
        .driver-abbr { color: #888; font-size: 0.9rem; font-weight: 700; }
        .team-name { font-weight: 600; color: #ccc; font-size: 1rem; }
        .gap-text { font-family: 'Roboto Mono', monospace; color: #e10600; font-weight: 700; font-size: 1.2rem; }
        .gap-sc { color: #ffcc00 !important; }
        .tyre-container { display: flex; align-items: center; gap: 10px; }
        .tyre-badge { width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 900; color: #000; font-size: 0.9rem; }
>>>>>>> f81424192996985be2da559c0f9a2c1f13f5eb7f
        .tyre-S { background-color: #ff3b30; box-shadow: 0 0 8px rgba(255,59,48,0.5); } 
        .tyre-M { background-color: #ffcc00; box-shadow: 0 0 8px rgba(255,204,0,0.5); } 
        .tyre-H { background-color: #fff; box-shadow: 0 0 8px rgba(255,255,255,0.5); } 
        .tyre-I { background-color: #34c759; box-shadow: 0 0 8px rgba(52,199,89,0.5); color: #fff;} 
        .tyre-W { background-color: #007aff; box-shadow: 0 0 8px rgba(0,122,255,0.5); color: #fff;} 
<<<<<<< HEAD
        .wear-bar-bg { width: 60px; height: 6px; background: #222; border-radius: 3px; overflow: hidden; }
        .wear-bar-fill { height: 100%; background: #00D2BE; transition: width 0.5s; }
        .wear-high { background: #e10600; }
        
        .status-dnf { color: #e10600; font-weight: 800; background: rgba(225,6,0,0.1); padding: 4px 10px; border-radius: 4px; font-size: 0.85rem; }
        .status-pit { color: #ffcc00; font-weight: 800; animation: blink 1.5s infinite; background: rgba(255,204,0,0.1); padding: 4px 10px; border-radius: 4px; font-size: 0.85rem; }
        
        @keyframes blink { 0%, 100% { opacity: 1; } 50% { opacity: 0.4; } }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        
        #postRaceStandings { margin-top: 40px; animation: fadeIn 1s; }
        .standings-table { width: 100%; border-collapse: collapse; background: #0d0d0d; border-radius: 12px; overflow: hidden; box-shadow: 0 15px 35px rgba(0,0,0,0.8); }
        .standings-table th { background: #151515; color: #666; padding: 12px; text-transform: uppercase; text-align: left; font-size: 0.8rem; }
        .standings-table td { padding: 12px; border-bottom: 1px solid #1a1a1a; }
        .standings-table tr:hover { background: #161616; }
        .champ-gold { color: #d4af37; font-weight: 900; }
=======
        .wear-bar-bg { width: 80px; height: 6px; background: #222; border-radius: 3px; overflow: hidden; }
        .wear-bar-fill { height: 100%; background: #00D2BE; transition: width 0.5s; }
        .wear-high { background: #e10600; }
        .status-dnf { color: #e10600; font-weight: 800; background: rgba(225,6,0,0.1); padding: 5px 12px; border-radius: 4px; }
        .status-pit { color: #ffcc00; font-weight: 800; animation: blink 1.5s infinite; background: rgba(255,204,0,0.1); padding: 5px 12px; border-radius: 4px; }
        @keyframes blink { 0%, 100% { opacity: 1; } 50% { opacity: 0.4; } }
        #postRaceStandings { display: none; margin-top: 60px; animation: fadeIn 1s; }
        .standings-table { width: 100%; border-collapse: collapse; background: #0d0d0d; border-radius: 12px; overflow: hidden; box-shadow: 0 15px 35px rgba(0,0,0,0.8); }
        .standings-table th { background: #151515; color: #666; padding: 15px; text-transform: uppercase; text-align: left; }
        .standings-table td { padding: 15px; border-bottom: 1px solid #1a1a1a; }
        .standings-table tr:hover { background: #161616; }
        .champ-gold { color: #d4af37; font-weight: 900; }
        
        @media(max-width: 1200px) { .live-wrapper { flex-direction: column; } .chat-panel { width: 100%; height: 500px; position: static; } }

        /* Dropdown menu styles */
        .dropdown-container {
            position: relative;
            display: inline-block;
        }

        .welcome {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            transition: all 0.2s ease;
            padding: 5px 12px;
            border-radius: 30px;
            border: 1px solid rgba(225, 6, 0, 0.2);
            background: rgba(255, 255, 255, 0.05);
        }

        .welcome:hover {
            background: rgba(225, 6, 0, 0.15);
            border-color: #e10600;
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

        .clickable-user {
            cursor: pointer;
            transition: opacity 0.2s;
        }

        .clickable-user:hover {
            opacity: 0.8;
        }
>>>>>>> f81424192996985be2da559c0f9a2c1f13f5eb7f

        /* Modal Styles */
        .user-modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.85);
            backdrop-filter: blur(8px);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }
        .user-modal-content {
            background: linear-gradient(145deg, #111, #1a1a1a);
            width: 320px;
<<<<<<< HEAD
            max-width: 90%;
=======
>>>>>>> f81424192996985be2da559c0f9a2c1f13f5eb7f
            border-radius: 24px;
            border: 1px solid #e10600;
            padding: 20px;
            position: relative;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.6);
            animation: popIn 0.3s ease;
            text-align: center;
        }
        @keyframes popIn {
            from { transform: scale(0.8); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
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
            padding: 12px;
            border-radius: 16px;
            text-align: left;
        }
        .user-modal-footer {
            display: flex;
            gap: 10px;
<<<<<<< HEAD
            flex-wrap: wrap;
        }
        .user-modal-footer button {
            flex: 1;
            min-width: 120px;
=======
        }
        .user-modal-footer button {
            flex: 1;
>>>>>>> f81424192996985be2da559c0f9a2c1f13f5eb7f
            padding: 10px;
            border: none;
            border-radius: 40px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
<<<<<<< HEAD
            font-size: 0.8rem;
        }
        .btn-add-friend { background: #333; color: white; }
        .btn-add-friend:hover { background: #444; }
        .btn-send-msg { background: #e10600; color: white; }
        .btn-send-msg:hover { background: #b00500; }

        /* ===== MOBILE RESPONSIVE ===== */
        @media (max-width: 1200px) {
            .leaderboard-header { grid-template-columns: 50px 2.5fr 2fr 150px 100px 80px; }
            .telemetry-row { grid-template-columns: 50px 2.5fr 2fr 150px 100px 80px; }
        }
        
        /* Mobile Navigation Fixes */
        @media (max-width: 992px) {
            .hamburger { 
                display: block;
                margin-right: 0;
            }
            
            .left-header { display: none; }
            
            header {
                padding: 0 15px;
                justify-content: space-between;
            }
            
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
                padding: 0;
                z-index: 999;
                max-height: calc(100vh - 80px);
                overflow-y: auto;
                box-shadow: 0 10px 20px rgba(0,0,0,0.5);
            }
            
            nav.open { 
                display: flex; 
            }
            
            nav a {
                padding: 18px 20px;
                text-align: left;
                border-bottom: 1px solid rgba(255,255,255,0.08);
                width: 100%;
                font-size: 1rem;
                white-space: normal;
            }
            
            nav a:last-child {
                border-bottom: none;
            }
            
            nav a i {
                margin-right: 10px;
                width: 20px;
            }
            
            /* Auth/Dropdown fixes for mobile */
            .dropdown-container {
                position: relative;
            }
            
            .welcome {
                padding: 8px 15px;
            }
            
            .dropdown-menu-modern {
                position: absolute;
                top: calc(100% + 8px);
                right: 0;
                left: auto;
                min-width: 240px;
                max-width: calc(100vw - 20px);
                border-radius: 16px;
            }
            
            .dropdown-menu-modern a {
                padding: 15px 20px;
                font-size: 1rem;
            }
        }
        
        @media (max-width: 768px) {
            body { 
                font-size: 14px; 
                padding-top: 70px; 
                padding-bottom: 60px;
            }
            
            header { 
                height: 70px; 
                padding: 0 15px;
            }
            
            nav { 
                top: 70px;
                max-height: calc(100vh - 70px);
            }
            
            nav a {
                padding: 16px 20px;
            }
            
            .welcome img.avatar {
                width: 35px !important;
                height: 35px !important;
            }
            
            .welcome-text {
                font-size: 0.9rem;
            }
            
            .dropdown-menu-modern {
                top: calc(100% + 8px);
                right: 0;
                left: auto;
            }
            
            .live-wrapper { 
                flex-direction: column; 
                width: 100%; 
                padding: 0 10px; 
                margin: 10px auto; 
                height: auto;
                min-height: auto;
                gap: 15px;
            }
            
            .telemetry-panel { 
                width: 100%; 
                overflow-y: visible;
                padding-bottom: 10px;
            }
            
            /* Chat as bottom drawer - always visible handle */
            .chat-panel {
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                width: 100%;
                height: auto;
                max-height: 60px;
                border-radius: 20px 20px 0 0;
                transition: max-height 0.3s ease;
                z-index: 998;
                border-bottom: none;
            }
            
            .chat-panel.open {
                max-height: 70vh;
                z-index: 1000;
            }
            
            .chat-header {
                padding: 15px 20px;
                cursor: pointer;
                border-radius: 20px 20px 0 0;
            }
            
            .chat-messages {
                max-height: calc(70vh - 130px);
            }
            
            .race-header {
                flex-direction: column;
                text-align: center;
                padding: 18px 15px;
                gap: 15px;
            }
            
            .race-header h1 { font-size: 1.4rem; }
            .lap-label { text-align: center; }
            .lap-counter { font-size: 2rem; }
            .indicators { justify-content: center; }
            .indicator-badge { font-size: 0.7rem; padding: 4px 10px; }
            
            .leaderboard-header { display: none; }
            
            .leaderboard-grid {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                padding-bottom: 10px;
            }
            
            .telemetry-row {
                grid-template-columns: 45px 2fr 1.5fr 120px 80px 60px;
                padding: 0 8px;
                height: 60px;
                font-size: 0.8rem;
            }
            
            .pos-num { font-size: 1.2rem; width: 30px; }
            .driver-name { font-size: 0.85rem; }
            .driver-abbr { font-size: 0.65rem; }
            .team-name { font-size: 0.75rem; }
            .driver-pic-box { width: 35px; height: 35px; }
            .team-logo-box { width: 25px; height: 25px; }
            .tyre-badge { width: 22px; height: 22px; font-size: 0.7rem; }
            .wear-bar-bg { width: 40px; }
            .gap-text { font-size: 0.85rem; }
            .status-dnf, .status-pit { padding: 3px 6px; font-size: 0.7rem; }
            
            .chat-messages { padding: 10px; }
            .chat-msg { gap: 8px; }
            .chat-msg img { width: 30px; height: 30px; }
            .chat-msg-body { font-size: 0.8rem; padding: 8px 10px; }
            .chat-input-area { padding: 10px; }
            .chat-input-area input { padding: 10px 12px; font-size: 16px; }
            .chat-input-area button { width: 40px; height: 40px; }
            
            .auth .welcome { padding: 3px 8px; }
            .welcome-text { font-size: 0.75rem; }
            .welcome img.avatar { width: 28px !important; height: 28px !important; }
            
            #postRaceStandings h2 { font-size: 1.3rem; }
            .standings-table { font-size: 0.75rem; }
            .standings-table th, .standings-table td { padding: 8px 6px; }
        }
        
        @media (max-width: 480px) {
            .welcome-text span {
                max-width: none;
                overflow: visible;
                white-space: normal;
            }
            
            .auth .btn {
                padding: 8px 15px;
                font-size: 0.8rem;
            }
            
            .race-header h1 { font-size: 1.2rem; }
            .lap-counter { font-size: 1.6rem; }
            
            .telemetry-row {
                grid-template-columns: 40px 2fr 1.2fr 100px 70px 50px;
            }
            
            .driver-name { font-size: 0.75rem; }
            .team-name { display: none; }
            .team-logo-box { width: 22px; height: 22px; }
            .driver-info { gap: 6px; }
            .tyre-container { gap: 5px; }
            .wear-bar-bg { width: 30px; }
            .gap-text { font-size: 0.75rem; }
            
            .dropdown-menu-modern {
                position: absolute;
                top: calc(100% + 8px);
                right: 0;
                left: auto;
                min-width: 200px;
            }
            
            .chat-header { padding: 12px 15px; font-size: 0.9rem; }
            .chat-panel.open { max-height: 80vh; }
            .chat-messages { max-height: calc(80vh - 130px); }
        }
        
        @media (max-width: 360px) {
            .telemetry-row {
                grid-template-columns: 35px 2fr 1fr 90px 60px 45px;
            }
            
            .driver-pic-box { width: 30px; height: 30px; }
            .tyre-badge { width: 20px; height: 20px; font-size: 0.6rem; }
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
=======
        }
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
>>>>>>> f81424192996985be2da559c0f9a2c1f13f5eb7f
    </style>
</head>
<body>

<header>
<<<<<<< HEAD
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
                            style="border: 2px solid <?php echo htmlspecialchars($teamColor); ?>;">
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
=======
  <div class="left-header">
    <h1 class="logo-title">
      <img src="https://upload.wikimedia.org/wikipedia/commons/3/33/F1.svg" class="f1-logo" alt="F1 Logo" style="height: 40px; vertical-align: middle;">
      <span>Fan Club</span>
    </h1>
  </div>
  <nav style="margin: 20px 0;">
      <a href="/f1fanclub/index.php" style="color:white; margin:0 10px;">Kezdőlap</a>
      <a href="/f1fanclub/Championship/championship.php" style="color:white; margin:0 10px;">Bajnokság</a>
      <a href="/f1fanclub/teams/teams.php" style="color:white; margin:0 10px;">Csapatok</a>
      <a href="/f1fanclub/drivers/drivers.php" style="color:white; margin:0 10px;">Versenyzők</a>
      <a href="/f1fanclub/news/feed.php" style="color:white; margin:0 10px;">Paddock</a>
      <a href="/f1fanclub/pitwall/pitwall.php" style="color:white; margin:0 10px;"><i class="fas fa-trophy" style="margin-right: 5px;"></i> A Fal</a>
  </nav>
  
  <!-- DROPDOWN MENU - Same as other pages -->
  <?php if ($isLoggedIn): ?>
    <div class="dropdown-container" id="userDropdownContainer">
        <div class="auth">
            <div class="welcome" style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                <?php if ($profile_image): ?>
                    <img src="/f1fanclub/uploads/<?php echo htmlspecialchars($profile_image); ?>" class="avatar clickable-user"
                        alt="Profilkép" onclick="openUserProfile('<?php echo htmlspecialchars(addslashes($username)); ?>')"
                        style="width:35px; height:35px; border-radius:50%; object-fit: cover; border: 2px solid <?php echo htmlspecialchars($teamColor); ?>;">
                <?php endif; ?>
                <span class="welcome-text">
                    <span class="clickable-user" onclick="openUserProfile('<?php echo htmlspecialchars(addslashes($username)); ?>')"
                        style="color: <?php echo htmlspecialchars($teamColor); ?>; font-weight:bold;"><?php echo htmlspecialchars($username); ?></span>
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
  <?php else: ?>
    <div class="auth">
        <a href="/f1fanclub/register/register.html" class="btn">Regisztráció</a>
        <a href="/f1fanclub/login/login.html" class="btn">Bejelentkezés</a>
    </div>
  <?php endif; ?>
>>>>>>> f81424192996985be2da559c0f9a2c1f13f5eb7f
</header>

<div class="live-wrapper">
    <div class="telemetry-panel">
        <div class="race-header">
            <div>
                <h1>Kanadai Nagydíj <span style="color:#e10600;">2026</span></h1>
                <div class="indicators">
                    <div id="raceStatusText" class="indicator-badge" style="background: rgba(225,6,0,0.1); color:#e10600; border: 1px solid #e10600;">● ÉLŐ SZEZON</div>
                    <div id="weatherBadge" class="indicator-badge weather-sunny"><i class="fas fa-sun"></i> NAPOS</div>
                    <div id="scBadge" class="indicator-badge sc-active" style="display:none;"><i class="fas fa-car"></i> BIZTONSÁGI AUTÓ</div>
                </div>
            </div>
            <div>
                <div class="lap-label">Kör</div>
                <div class="lap-counter"><span id="currentLap">0</span><span style="color:#444;">/</span><span id="totalLaps" style="color:#666;">70</span></div>
            </div>
        </div>

        <div class="leaderboard-header">
            <div>Poz.</div><div>Versenyző</div><div>Csapat</div><div>Gumi</div><div>Különbség</div><div>Állapot</div>
        </div>

        <div id="raceGrid" class="leaderboard-grid">
            <p id="loadingMsg" style="text-align:center; color:#666; padding: 20px;">Adatok betöltése...</p>
        </div>

<<<<<<< HEAD
        <div id="postRaceStandings" style="display: none;">
=======
        <div id="postRaceStandings">
>>>>>>> f81424192996985be2da559c0f9a2c1f13f5eb7f
            <h2 style="color: #d4af37; text-align: center; font-size: 2rem; margin-bottom: 20px;"><i class="fas fa-trophy"></i> 2026 Versenyzői Bajnokság</h2>
            <table class="standings-table">
                <thead><tr><th>Poz.</th><th>Versenyző</th><th>Csapat</th><th style="text-align:right;">Pontok</th></tr></thead>
                <tbody id="standingsBody"></tbody>
            </table>
        </div>
    </div>
    
<<<<<<< HEAD
    <div class="chat-panel" id="chatPanel">
        <div class="chat-header" id="chatHeader">
=======
    <div class="chat-panel">
        <div class="chat-header">
>>>>>>> f81424192996985be2da559c0f9a2c1f13f5eb7f
            <i class="fas fa-comments"></i> Paddock Élő Chat
        </div>
        
        <div class="chat-messages" id="chatMessagesBox">
            </div>
        
        <?php if($isLoggedIn): ?>
        <div class="chat-input-area">
            <input type="text" id="chatInputMsg" placeholder="Szólj hozzá a futamhoz..." onkeypress="if(event.key === 'Enter') sendChat()">
            <button onclick="sendChat()"><i class="fas fa-paper-plane"></i></button>
        </div>
        <?php else: ?>
        <div class="chat-guest-msg">
            A chateléshez <a href="../login/login.html">jelentkezz be</a>!
        </div>
        <?php endif; ?>
    </div>
</div>

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
            <button class="btn-send-msg" onclick="window.location.href='/f1fanclub/messages/messages.php'"><i class="fas fa-comment"></i> Üzenet küldése</button>
        </div>
    </div>
</div>

<script>
<<<<<<< HEAD
    // Team colors from PHP
    const teamColors = <?php echo json_encode($teamColorsJS); ?>;
    
    const rowHeight = 70; 
    let isFetching = false;
    let chatMessageCount = 0; 

    // Hamburger menu
    document.addEventListener('DOMContentLoaded', function() {
        const hamburgerBtn = document.getElementById('hamburgerBtn');
        const mainNav = document.getElementById('mainNav');
        
        if (hamburgerBtn && mainNav) {
            hamburgerBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                mainNav.classList.toggle('open');
                // Toggle icon between bars and times
                const icon = this.querySelector('i');
                if (mainNav.classList.contains('open')) {
                    icon.classList.remove('fa-bars');
                    icon.classList.add('fa-times');
                } else {
                    icon.classList.remove('fa-times');
                    icon.classList.add('fa-bars');
                }
            });
            
            // Close menu when clicking outside
            document.addEventListener('click', function(e) {
                if (mainNav.classList.contains('open') && 
                    !mainNav.contains(e.target) && 
                    !hamburgerBtn.contains(e.target)) {
                    mainNav.classList.remove('open');
                    const icon = hamburgerBtn.querySelector('i');
                    icon.classList.remove('fa-times');
                    icon.classList.add('fa-bars');
                }
            });
            
            // Close menu when a nav link is clicked
            mainNav.querySelectorAll('a').forEach(link => {
                link.addEventListener('click', function() {
                    mainNav.classList.remove('open');
                    const icon = hamburgerBtn.querySelector('i');
                    icon.classList.remove('fa-times');
                    icon.classList.add('fa-bars');
                });
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
        
        // Mobile chat drawer toggle
        const chatHeader = document.getElementById('chatHeader');
        const chatPanel = document.getElementById('chatPanel');
        
        if (chatHeader && chatPanel && window.innerWidth <= 768) {
            chatHeader.addEventListener('click', function() {
                chatPanel.classList.toggle('open');
            });
        }
    });

=======
    const rowHeight = 80; 
    let isFetching = false;
    let chatMessageCount = 0; 

>>>>>>> f81424192996985be2da559c0f9a2c1f13f5eb7f
    // USER PROFILE MODAL FUNCTIONS
    let currentModalUser = "";
    let currentFriendStatus = "";

    function openUserProfile(username) {
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
        }).catch(err => console.error(err));
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
            btn.innerHTML = '<i class="fas fa-clock"></i> Jelölés elküldve';
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
        })
        .then(r => r.json())
        .then(data => {
            if(data.success) {
                openUserProfile(currentModalUser);
            }
        });
    }

    function makeSafeStr(str) {
        return str.replace(/\\/g, '\\\\').replace(/'/g, "\\'");
    }
<<<<<<< HEAD
    
    function getTeamColor(teamName) {
        return teamColors[teamName] || '#ffffff';
    }
=======

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
>>>>>>> f81424192996985be2da559c0f9a2c1f13f5eb7f

    // --- TELEMETRIA JS ---
    async function fetchData() {
        if (isFetching) return;
        isFetching = true;
        try {
            const response = await fetch('race_api.php?action=update');
            const text = await response.text(); 
            let data;
            try { data = JSON.parse(text); } 
            catch(e) { document.getElementById('loadingMsg').innerHTML = '<span style="color:#e10600;">Hiba a szerver kommunikációban!</span>'; isFetching = false; return; }

            if (data && data.race) {
<<<<<<< HEAD
                window.lastRaceData = data;
=======
>>>>>>> f81424192996985be2da559c0f9a2c1f13f5eb7f
                renderRace(data);
                if (data.race.status === 'running') {
                    let timeout = data.race.safety_car == "1" ? 15000 : 10000;
                    setTimeout(fetchData, timeout);
                } else if (data.race.status === 'finished') {
                    document.getElementById('raceStatusText').innerHTML = '<i class="fas fa-flag-checkered"></i> FUTAM VÉGE';
                    document.getElementById('raceStatusText').style.color = '#fff';
                    document.getElementById('raceStatusText').style.borderColor = '#fff';
                    document.getElementById('scBadge').style.display = 'none';
                    renderStandings(data.standings);
                } else {
                    document.getElementById('raceStatusText').innerHTML = '⚠️ FUTAM MEGÁLLÍTVA';
                    document.getElementById('raceStatusText').style.color = 'orange';
                    document.getElementById('raceStatusText').style.borderColor = 'orange';
                    setTimeout(fetchData, 5000);
                }
            }
        } catch (error) { console.error("Fetch Hiba:", error); } finally { isFetching = false; }
    }

    function renderRace(data) {
        document.getElementById('currentLap').innerText = data.race.current_lap;
        document.getElementById('totalLaps').innerText = data.race.total_laps;

        const weatherBadge = document.getElementById('weatherBadge');
        if (data.race.weather === 'Rain') {
            weatherBadge.className = 'indicator-badge weather-rain';
            weatherBadge.innerHTML = '<i class="fas fa-cloud-rain"></i> NEDVES PÁLYA';
        } else {
            weatherBadge.className = 'indicator-badge weather-sunny';
            weatherBadge.innerHTML = '<i class="fas fa-sun"></i> SZÁRAZ PÁLYA';
        }

        const scBadge = document.getElementById('scBadge');
        const isSC = data.race.safety_car == "1";
        scBadge.style.display = isSC ? 'flex' : 'none';

        const grid = document.getElementById('raceGrid');
        const loadingMsg = document.getElementById('loadingMsg');
        if (loadingMsg) loadingMsg.remove();

        const isMobile = window.innerWidth <= 768;
        const rowHeightDynamic = isMobile ? 60 : 70;
        grid.style.height = (data.grid.length * rowHeightDynamic) + 'px';

        data.grid.forEach((driver, index) => {
            let tyreClass = 'tyre-S'; let tyreLetter = 'S';
            if (driver.tyre_type === 'Medium') { tyreClass = 'tyre-M'; tyreLetter = 'M'; }
            if (driver.tyre_type === 'Hard') { tyreClass = 'tyre-H'; tyreLetter = 'H'; }
            if (driver.tyre_type === 'Inter') { tyreClass = 'tyre-I'; tyreLetter = 'I'; }
            if (driver.tyre_type === 'Wet') { tyreClass = 'tyre-W'; tyreLetter = 'W'; }

            let wearColorClass = driver.tyre_wear > 60 ? 'wear-high' : '';
            let gap = ''; let posDisplay = ''; let statusHtml = '';
<<<<<<< HEAD
            
            const teamColor = getTeamColor(driver.team_name);
=======
>>>>>>> f81424192996985be2da559c0f9a2c1f13f5eb7f

            if (driver.status === 'DNF') {
                posDisplay = '<span style="color:#e10600; font-size:1.3rem;">KI</span>';
                gap = driver.gap ? `<span style="color:#666; font-size:1rem;">${driver.gap}</span>` : 'Kiesett';
                statusHtml = '<span class="status-dnf">DNF</span>';
            } else {
                posDisplay = driver.position + '.';
                if (driver.status === 'Pit') {
                    gap = '<span style="color:#ffcc00;">BOX</span>';
                    statusHtml = '<span class="status-pit">BOX</span>';
                } else {
                    if (isSC) { gap = driver.position === 1 ? 'SC' : '<span class="gap-sc">SC SOR</span>'; } 
                    else { gap = driver.position === 1 ? 'Vezet' : '+' + (driver.position * 1.5 + Math.random()).toFixed(1) + 's'; }
                }
            }

            let targetY = index * rowHeightDynamic;
            let row = document.getElementById('driver-row-' + driver.driver_id);

            if (!row) {
                row = document.createElement('div');
                row.id = 'driver-row-' + driver.driver_id;
                row.className = 'telemetry-row';
                row.style.borderLeftColor = teamColor;
                grid.appendChild(row);
            }

            row.style.transform = `translateY(${targetY}px)`;
            row.style.opacity = driver.status === 'DNF' ? '0.4' : '1';
<<<<<<< HEAD
            row.style.borderLeftColor = teamColor;
=======
>>>>>>> f81424192996985be2da559c0f9a2c1f13f5eb7f

            row.classList.remove('leader', 'sc-mode');
            if (driver.status !== 'DNF') {
                if (isSC) { row.classList.add('sc-mode'); } 
                else if (driver.position === 1) { row.classList.add('leader'); }
            }

            let rawDriverImg = driver.driver_image ? driver.driver_image.trim() : '';
            let imgPath = '../drivers/default.png';
            if (rawDriverImg !== '') { imgPath = rawDriverImg.includes('/') ? `../${rawDriverImg}` : `../drivers/${rawDriverImg}`; }
            let rawLogo = driver.logo ? driver.logo.trim() : '';
            let logoPath = '';
            if (rawLogo !== '') { logoPath = rawLogo.includes('/') ? `../${rawLogo}` : `../logos/${rawLogo}`; }

            row.innerHTML = `
                <div class="pos-num">${posDisplay}</div>
                <div class="driver-info">
<<<<<<< HEAD
                    <div class="driver-pic-box" style="border-color: ${teamColor};"><img src="${imgPath}" class="driver-portrait" onerror="this.style.opacity=0;"></div>
                    <div style="display:flex; flex-direction:column; justify-content:center;">
                        <span class="driver-name" style="color: ${teamColor};">${driver.name}</span>
                        <span class="driver-abbr">${driver.abbreviation}</span>
=======
                    <div class="driver-pic-box"><img src="${imgPath}" class="driver-portrait" onerror="this.style.opacity=0;"></div>
                    <div style="display:flex; flex-direction:column; justify-content:center;">
                        <span class="driver-name">${driver.name}</span><span class="driver-abbr">${driver.abbreviation}</span>
>>>>>>> f81424192996985be2da559c0f9a2c1f13f5eb7f
                    </div>
                </div>
                <div class="driver-info">
                    <div class="team-logo-box"><img src="${logoPath}" class="team-logo-small" onerror="this.style.opacity=0;"></div>
<<<<<<< HEAD
                    <span class="team-name" style="color: ${teamColor};">${driver.team_name}</span>
=======
                    <span class="team-name">${driver.team_name}</span>
>>>>>>> f81424192996985be2da559c0f9a2c1f13f5eb7f
                </div>
                <div class="tyre-container">
                    <div class="tyre-badge ${tyreClass}">${tyreLetter}</div>
                    <div class="wear-bar-bg" title="Kopás: ${driver.tyre_wear}%"><div class="wear-bar-fill ${wearColorClass}" style="width: ${driver.tyre_wear}%"></div></div>
                </div>
                <div class="gap-text">${gap}</div><div>${statusHtml}</div>
            `;
        });
        
        const existingIds = data.grid.map(d => 'driver-row-' + d.driver_id);
        document.querySelectorAll('.telemetry-row').forEach(row => {
            if (!existingIds.includes(row.id)) {
                row.remove();
            }
        });
    }

    function renderStandings(standings) {
        if (!standings || standings.length === 0) return;
        document.getElementById('postRaceStandings').style.display = 'block';
        const tbody = document.getElementById('standingsBody');
        tbody.innerHTML = '';
        standings.forEach(s => {
            const teamColor = getTeamColor(s.team);
            let tr = document.createElement('tr');
            if(s.pos === 1) tr.style.background = 'linear-gradient(90deg, rgba(212,175,55,0.1), transparent)';
            tr.innerHTML = `<td class="${s.pos === 1 ? 'champ-gold' : ''}">${s.pos}.</td><td style="font-weight:bold;">${s.name}</td><td style="color: ${teamColor};">${s.team}</td><td style="text-align:right; font-family:monospace; font-size:1.2rem; font-weight:bold;" class="${s.pos === 1 ? 'champ-gold' : ''}">${s.points} PONT</td>`;
            tbody.appendChild(tr);
        });
    }

    // --- CHAT JS ---
    async function loadChat() {
        try {
            const res = await fetch('chat_api.php?_t=' + new Date().getTime());
            const msgs = await res.json();
            const box = document.getElementById('chatMessagesBox');
            
            if (msgs.length > chatMessageCount) {
                box.innerHTML = '';
                msgs.forEach(m => {
                    box.innerHTML += `
                        <div class="chat-msg">
                            <img src="${m.profile_image}" class="clickable-user" onclick="openUserProfile('${makeSafeStr(m.username)}')" onerror="this.src='../drivers/default.png'" style="border-color: ${m.color}; width:35px; height:35px; border-radius:50%; object-fit: cover; border: 2px solid transparent;">
                            <div class="chat-msg-body">
                                <div class="chat-user-info">
                                    <span class="chat-user-name clickable-user" onclick="openUserProfile('${makeSafeStr(m.username)}')" style="color:${m.color}; cursor:pointer;">${m.username}</span>
                                    <span class="chat-time">${m.time}</span>
                                </div>
                                <div style="word-wrap: break-word;">${m.message}</div>
                            </div>
                        </div>
                    `;
                });
                box.scrollTop = box.scrollHeight; 
                chatMessageCount = msgs.length;
            }
        } catch(e) { console.log("Chat error:", e); }
    }

    async function sendChat() {
        const input = document.getElementById('chatInputMsg');
        if(!input || input.value.trim() === '') return;
        
        let msg = input.value;
        input.value = '';
        
        await fetch('chat_api.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ message: msg })
        });
        loadChat(); 
    }

    function renderStandings(standings) {
        if (!standings || standings.length === 0) return;
        document.getElementById('postRaceStandings').style.display = 'block';
        const tbody = document.getElementById('standingsBody');
        tbody.innerHTML = '';
        standings.forEach(s => {
            let tr = document.createElement('tr');
            if(s.pos === 1) tr.style.background = 'linear-gradient(90deg, rgba(212,175,55,0.1), transparent)';
            tr.innerHTML = `<td class="${s.pos === 1 ? 'champ-gold' : ''}">${s.pos}.</td><td style="font-weight:bold;">${s.name}</td><td style="color:#888;">${s.team}</td><td style="text-align:right; font-family:monospace; font-size:1.2rem; font-weight:bold;" class="${s.pos === 1 ? 'champ-gold' : ''}">${s.points} PONT</td>`;
            tbody.appendChild(tr);
        });
    }

    // --- CHAT JS ---
    async function loadChat() {
        try {
            const res = await fetch('chat_api.php?_t=' + new Date().getTime());
            const msgs = await res.json();
            const box = document.getElementById('chatMessagesBox');
            
            if (msgs.length > chatMessageCount) {
                box.innerHTML = '';
                msgs.forEach(m => {
                    box.innerHTML += `
                        <div class="chat-msg">
                            <img src="${m.profile_image}" class="clickable-user" onclick="openUserProfile('${makeSafeStr(m.username)}')" onerror="this.src='../drivers/default.png'" style="border-color: ${m.color}; width:35px; height:35px; border-radius:50%; object-fit: cover; border: 2px solid transparent;">
                            <div class="chat-msg-body">
                                <div class="chat-user-info">
                                    <span class="chat-user-name clickable-user" onclick="openUserProfile('${makeSafeStr(m.username)}')" style="color:${m.color}; cursor:pointer;">${m.username}</span>
                                    <span class="chat-time">${m.time}</span>
                                </div>
                                <div style="word-wrap: break-word;">${m.message}</div>
                            </div>
                        </div>
                    `;
                });
                box.scrollTop = box.scrollHeight; 
                chatMessageCount = msgs.length;
            }
        } catch(e) { console.log("Chat error:", e); }
    }

    async function sendChat() {
        const input = document.getElementById('chatInputMsg');
        if(!input || input.value.trim() === '') return;
        
        let msg = input.value;
        input.value = '';
        
        await fetch('chat_api.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ message: msg })
        });
        loadChat(); 
    }

    fetchData();
    loadChat();
    setInterval(loadChat, 3000); 
<<<<<<< HEAD
    
    window.addEventListener('resize', function() {
        if (window.lastRaceData) {
            renderRace(window.lastRaceData);
        }
    });
=======
>>>>>>> f81424192996985be2da559c0f9a2c1f13f5eb7f
</script>
</body>
</html>