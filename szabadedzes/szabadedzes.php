<?php
session_start();

$DB_HOST = "localhost";
$DB_USER = "swmjndga_swmjndga";
$DB_PASS = "Teszt1234!";
$DB_NAME = "swmjndga_f1adat";

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) { die("Adatbázis hiba: " . $conn->connect_error); }

$isLoggedIn = isset($_SESSION['username']);
$username = $isLoggedIn ? $_SESSION['username'] : null;

function getTeamColor($team)
{
    $teamColors = [
        // Current Teams (2025)
        'Red Bull' => '#1E41FF',
        'Ferrari' => '#DC0000',
        'Mercedes' => '#00D2BE',
        'McLaren' => '#FF8700',
        'Aston Martin' => '#006F62',
        'Alpine' => '#0090FF',
        'Williams' => '#00A0DE',
        'RB' => '#2b2bff',
        'Racing Bulls' => '#2b2bff',
        'Audi' => '#e3000f',
        'Haas F1 Team' => '#B6BABD',
        'Haas' => '#B6BABD',
        'Cadillac' => '#B6BABD',
        
        // Historic Teams - 1950s
        'Alfa Romeo' => '#900000',
        'Maserati' => '#0047AB',
        'Talbot-Lago' => '#C0C0C0',
        'Simca-Gordini' => '#0047AB',
        'Gordini' => '#0047AB',
        'ERA' => '#800000',
        'Alta' => '#800080',
        'Cooper' => '#004225',
        'Vanwall' => '#006633',
        'BRM' => '#800000',
        'Connaught' => '#004225',
        'HWM' => '#0066CC',
        'Frazer Nash' => '#0066CC',
        'OSCA' => '#FF4500',
        'Lancia' => '#0000FF',
        'Bugatti' => '#002366',
        'Aston Butterworth' => '#006F62',
        'Kurtis Kraft' => '#8B4513',
        'Kuzma' => '#CD7F32',
        'Epperly' => '#CD7F32',
        'Watson' => '#4682B4',
        'Phillips' => '#708090',
        'Lesovsky' => '#8B4513',
        'Trevis' => '#2F4F4F',
        'Sutton' => '#DAA520',
        'Blanchard' => '#708090',
        'Langley' => '#696969',
        'Pankratz' => '#8B4513',
        'Adams' => '#708090',
        'JBW' => '#800020',
        'Stebro' => '#708090',
        'Scirocco' => '#808000',
        'Roe' => '#800080',
        'Fry' => '#DAA520',
        'Gilby' => '#708090',
        'EMW' => '#003399',
        
        // 1960s-1970s
        'Lotus' => '#FFB800',
        'Brabham' => '#006633',
        'Eagle' => '#FFD700',
        'Honda' => '#CC0000',
        'Matra' => '#0033FF',
        'March' => '#FF4500',
        'Surtees' => '#FF0000',
        'Tyrrell' => '#004586',
        'Shadow' => '#2F4F4F',
        'Penske' => '#C0C0C0',
        'Ensign' => '#0000FF',
        'Wolf' => '#FFD700',
        'Renault' => '#FFD800',
        'Ligier' => '#0033FF',
        'Arrows' => '#FFD700',
        'Fittipaldi' => '#FFD700',
        'Copersucar' => '#FFD700',
        'ATS' => '#FF0000',
        'Theodore' => '#FFD700',
        'Osella' => '#FF0000',
        'Toleman' => '#0033FF',
        'Spirit' => '#FFD700',
        'RAM' => '#0000FF',
        'Zakspeed' => '#FFD700',
        
        // 1980s-1990s
        'Benetton' => '#00A65E',
        'Minardi' => '#FFFF00',
        'Dallara' => '#DC143C',
        'Larrousse' => '#0000FF',
        'Coloni' => '#006400',
        'EuroBrun' => '#FFD700',
        'Onyx' => '#C0C0C0',
        'Life' => '#800080',
        'Jordan' => '#FFFF00',
        'Modena' => '#0033FF',
        'Fondmetal' => '#FFD700',
        'Footwork' => '#0000FF',
        'Venturi' => '#FF0000',
        'Pacific' => '#0066FF',
        'Simtek' => '#FF0000',
        'Forti' => '#0066FF',
        'Sauber' => '#0066FF',
        'Stewart' => '#003366',
        'Prost' => '#0000FF',
        'BAR' => '#0000FF',
        'Jaguar' => '#006400',
        'Toyota' => '#CC0000',
        'BMW Sauber' => '#0066FF',
        'BMW' => '#0066FF',
        'Super Aguri' => '#DC143C',
        'Spyker' => '#FFA500',
        'Force India' => '#FF4F00',
        'HRT' => '#A9A9A9',
        'Caterham' => '#008000',
        'Marussia' => '#800000',
        'Manor' => '#2F4F4F',
        'Virgin' => '#FF6600',
        'Lotus F1' => '#FFB800',
        'Toro Rosso' => '#0033FF',
        'AlphaTauri' => '#2b2b2b',
        'Racing Point' => '#F596C8',
        'Brawn GP' => '#C0C0C0',
        
        // Additional variations
        'Red Bull Racing' => '#1E41FF',
        'Scuderia Ferrari' => '#DC0000',
        'Mercedes AMG' => '#00D2BE',
        'McLaren Mercedes' => '#FF8700',
        'Williams Racing' => '#00A0DE',
        'Alpine F1 Team' => '#0090FF',
        'Aston Martin Aramco' => '#006F62',
        'Haas F1' => '#B6BABD',
        'RB F1 Team' => '#2b2bff',
        'Visa Cash App RB' => '#2b2bff',
        'Stake F1 Team' => '#00D2BE',
        'Kick Sauber' => '#00D2BE'
    ];
    
    // Exact match
    if (isset($teamColors[$team])) {
        return $teamColors[$team];
    }
    
    // Case-insensitive match
    $lowerTeam = strtolower($team);
    foreach ($teamColors as $key => $color) {
        if (strtolower($key) === $lowerTeam) {
            return $color;
        }
    }
    
    // Partial match
    foreach ($teamColors as $key => $color) {
        if (strpos($lowerTeam, strtolower($key)) !== false || 
            strpos(strtolower($key), $lowerTeam) !== false) {
            return $color;
        }
    }
    
    return '#ffffff';
}

$profile_image = null; $fav_team = null; $teamColor = '#ffffff'; $isAdmin = false;

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
?>
<!DOCTYPE html>
<html lang="hu">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Szabadedzés - F1 Fan Club</title>
    <link rel="icon" type="image/svg+xml" href="https://upload.wikimedia.org/wikipedia/commons/3/33/F1.svg">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #0a0a0a; color: white; font-family: 'Poppins', sans-serif; min-height: 100vh; position: relative; overflow-x: hidden; margin: 0; padding: 0; }
        body::before { content: ""; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: radial-gradient(circle at 20% 50%, rgba(225, 6, 0, 0.05) 0%, transparent 50%), radial-gradient(circle at 80% 80%, rgba(225, 6, 0, 0.05) 0%, transparent 50%); pointer-events: none; z-index: -1; }
        .bg-lines { position: fixed; width: 200%; height: 200%; background: repeating-linear-gradient(60deg, rgba(225, 6, 0, 0.03) 0px, rgba(225, 6, 0, 0.03) 2px, transparent 2px, transparent 10px); animation: slide 10s linear infinite; opacity: 0.3; z-index: -1; top: 0; left: 0; }
        @keyframes slide { from { transform: translateX(0); } to { transform: translateX(-200px); } }
        
        header { background-color: #0a0a0a; border-bottom: 2px solid rgba(225, 6, 0, 0.3); padding: 0 40px; height: 80px; display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 1000; box-shadow: 0 4px 20px rgba(0,0,0,0.5); }
        .left-header { display: flex; align-items: center; }
        .logo-title { display: flex; align-items: center; gap: 12px; font-size: 1.5rem; font-weight: 800; color: #fff; text-transform: uppercase; letter-spacing: 1px; }
        .logo-title img { width: 40px; height: auto; filter: brightness(0) invert(1); }
        .logo-title span { display: block; margin-top: 4px; }

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
        }

        .hamburger:hover {
            color: #e10600;
        }

        nav { display: flex; gap: 5px; margin: 0 20px; }
        nav a { font-weight: 600; font-size: 0.9rem; text-transform: uppercase; padding: 8px 16px; border-radius: 4px; color: #ffffff !important; text-decoration: none; position: relative; transition: all 0.2s ease; letter-spacing: 0.5px; opacity: 0.9; }
        nav a:hover { color: #e10600 !important; opacity: 1; background: rgba(225, 6, 0, 0.1); }
        nav a.active { color: #e10600 !important; opacity: 1; font-weight: 700; background: rgba(225, 6, 0, 0.15); }
        nav a[style*="color"] { color: #ffffff !important; }
        nav a[style*="color"]:hover, nav a[style*="color"].active { color: #e10600 !important; }

        /* DROPDOWN MENU STYLES */
        .dropdown-container {
            position: relative;
            display: inline-block;
        }
        
        .welcome {
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.9rem;
            margin-right: 10px;
            padding: 5px 12px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 30px;
            border: 1px solid rgba(225, 6, 0, 0.2);
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

        .auth .btn { display: inline-block; padding: 8px 20px; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; color: #fff; background-color: transparent; border: 1px solid rgba(225, 6, 0, 0.5); border-radius: 30px; cursor: pointer; transition: all 0.3s ease; text-align: center; text-decoration: none; letter-spacing: 0.5px; }
        .auth .btn:hover { background-color: #e10600; border-color: #e10600; transform: translateY(-2px); box-shadow: 0 4px 15px rgba(225, 6, 0, 0.4); color: #fff; }
        .auth .btn:first-child { background-color: rgba(225, 6, 0, 0.15); border-color: #e10600; }
        .auth .btn:first-child:hover { background-color: #e10600; }
        .auth .btn:not(:last-child) { border-color: rgba(255, 255, 255, 0.2); }
        .auth .btn:not(:last-child):hover { border-color: #e10600; background-color: #e10600; }
        .auth .btn:last-child { border-color: rgba(225, 6, 0, 0.5); }
        .auth .btn:last-child:hover { background-color: #e10600; }

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

        /* Desktop navigation - always visible */
        @media (min-width: 993px) {
            nav {
                display: flex !important;
            }
        }

        /* Mobile navigation - hamburger mode */
        @media (max-width: 992px) {
            .hamburger {
                display: block;
            }
            
            /* Hide the entire logo on mobile */
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
            .auth .btn {
                padding: 6px 12px;
                font-size: 0.7rem;
            }
            nav a {
                padding: 12px 15px;
                font-size: 0.85rem;
            }
        }

        /* Practice specific styles - FULL WIDTH */
        .championship-layout.full-width {
            width: 100%;
            margin: 0;
            padding: 20px;
        }

        .championship-content.full-width {
            width: 100%;
            margin: 0;
            padding: 0;
            max-width: 1400px;
            margin: 0 auto;
        }

        .control-panel {
            background: linear-gradient(145deg, #111111 0%, #1a1a1a 100%);
            border-radius: 20px;
            padding: 30px;
            margin: 20px 0 30px;
            border: 1px solid rgba(225, 6, 0, 0.3);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.8);
        }

        .panel-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
        }

        .panel-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(145deg, #e10600, #b30000);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            box-shadow: 0 5px 15px rgba(225, 6, 0, 0.3);
        }

        .panel-header h2 {
            font-size: 1.5rem;
            color: white;
            font-weight: 700;
        }

        .panel-header h2 span {
            color: #e10600;
        }

        .race-select-container { 
            margin-top: 20px; 
            text-align: left; 
            padding: 0;
        }
        
        .race-select-label { 
            color: #888; 
            font-size: 0.8rem; 
            font-weight: 700; 
            letter-spacing: 1px; 
            display: block; 
            margin-bottom: 8px; 
            text-transform: uppercase; 
        }
        
        .race-select { 
            width: 100%; 
            padding: 12px 15px; 
            background: #111; 
            color: white; 
            border: 1px solid #333; 
            border-radius: 8px; 
            font-family: 'Poppins', sans-serif; 
            font-size: 0.95rem; 
            font-weight: 600; 
            outline: none; 
            transition: 0.3s; 
            cursor: pointer; 
            appearance: none; 
            background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%23e10600%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E'); 
            background-repeat: no-repeat; 
            background-position: right 15px center; 
            background-size: 12px; 
        }
        
        .race-select:focus, 
        .race-select:hover { 
            border-color: #e10600; 
            box-shadow: 0 0 15px rgba(225,6,0,0.2); 
        }

        .current-selection {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            background: rgba(0, 0, 0, 0.3);
            border-radius: 15px;
            border: 1px solid rgba(225, 6, 0, 0.2);
            margin-top: 20px;
        }

        .selection-info {
            display: flex;
            align-items: baseline;
            gap: 15px;
        }

        .selection-year {
            font-size: 2.5rem;
            font-weight: 800;
            color: #e10600;
            text-shadow: 0 0 20px rgba(225, 6, 0, 0.3);
        }

        .selection-type {
            font-size: 1.1rem;
            color: #aaa;
            text-transform: uppercase;
        }

        .selection-type span {
            color: white;
            font-weight: 700;
        }

        .update-btn {
            padding: 12px 30px;
            background: #e10600;
            border: none;
            border-radius: 30px;
            color: white;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1rem;
        }

        .update-btn:hover {
            background: #b30000;
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(225, 6, 0, 0.4);
        }

        .standings-card {
            background: linear-gradient(145deg, #111111 0%, #1a1a1a 100%);
            border-radius: 20px;
            padding: 30px;
            margin: 30px 0;
            border: 1px solid rgba(225, 6, 0, 0.3);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.8);
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid rgba(225, 6, 0, 0.3);
        }

        .table-header h2 {
            font-size: 1.8rem;
            color: white;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .table-header h2 i {
            color: #e10600;
        }

        .season-badge {
            background: rgba(225, 6, 0, 0.15);
            color: #e10600;
            padding: 8px 20px;
            border-radius: 30px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            border: 1px solid rgba(225, 6, 0, 0.3);
        }

        .f1-table-wrapper {
            overflow-x: auto;
        }

        .f1-table {
            width: 100%;
            border-collapse: collapse;
        }

        .f1-table thead tr {
            border-bottom: 2px solid #e10600;
        }

        .f1-table th {
            padding: 15px;
            text-align: left;
            color: #aaa;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 1px;
        }

        .f1-table td {
            padding: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .f1-table tbody tr {
            transition: all 0.3s ease;
        }

        .f1-table tbody tr:hover {
            background: rgba(225, 6, 0, 0.05) !important;
        }

        .f1-pos {
            font-weight: 700;
            color: #aaa;
        }

        .f1-name {
            font-weight: 600;
            color: white;
        }

        .f1-name span {
            font-weight: 400;
            color: #aaa;
        }

        .f1-team {
            color: #ccc;
        }

        .lap-time { 
            font-family: 'Roboto Mono', monospace; 
            font-weight: 600; 
            letter-spacing: -0.5px; 
        }
        
        .time-miss { 
            color: #555; 
        }

        .loading-message {
            text-align: center;
            padding: 60px 20px !important;
            color: #888;
        }

        .loading-message i {
            font-size: 2rem;
            margin-bottom: 15px;
            display: block;
            color: #e10600;
        }

        .info-message {
            text-align: center;
            padding: 60px 20px !important;
        }

        .info-message i {
            font-size: 3rem;
            margin-bottom: 20px;
            display: block;
            color: #e10600;
        }

        .info-message strong {
            color: #e10600;
        }

        /* User Modal Styles */
        .user-modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.85);
            backdrop-filter: blur(8px);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }

        .user-modal-content {
            background: linear-gradient(145deg, #111, #1a1a1a);
            width: 320px;
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

        .user-modal-close:hover {
            color: #e10600;
        }

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
        }

        .user-modal-footer button {
            flex: 1;
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

        .clickable-user {
            cursor: pointer;
            transition: opacity 0.2s;
        }

        .clickable-user:hover {
            opacity: 0.8;
        }

        /* Practice responsive styles */
        @media (max-width: 992px) {
            .current-selection {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
                padding: 15px;
            }
            
            .table-header {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
                padding: 15px;
            }
            
            .f1-table-wrapper {
                overflow-x: auto;
            }
            
            .f1-table {
                min-width: 700px;
            }
            
            th, td {
                padding: 8px 10px;
                font-size: 0.75rem;
            }
        }
        
        @media (max-width: 768px) {
            .selection-year {
                font-size: 1.5rem;
            }
            
            .season-badge {
                font-size: 0.7rem;
                padding: 4px 10px;
            }
            
            .race-select-container {
                padding: 0;
            }
        }
        
        @media (max-width: 576px) {
            .update-btn {
                padding: 8px 16px;
                font-size: 0.75rem;
            }
            
            .panel-header h2 {
                font-size: 1.2rem;
            }
            
            .table-header h2 {
                font-size: 1.3rem;
            }
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
    <div class="bg-lines"></div>

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
            <a href="/f1fanclub/pitwall/pitwall.php"><i class="fas fa-trophy" style="margin-right: 5px;"></i> A Fal</a>
        </nav>

        <!-- DROPDOWN MENU -->
        <?php if ($isLoggedIn): ?>
            <div class="dropdown-container" id="userDropdownContainer">
                <div class="auth">
                    <div class="welcome" style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                        <?php if ($profile_image): ?>
                            <img src="/f1fanclub/uploads/<?php echo htmlspecialchars($profile_image); ?>" class="avatar clickable-user"
                                alt="Profilkép" onclick="openUserProfile('<?php echo htmlspecialchars(addslashes($username)); ?>')"
                                style="width:35px; height:35px; border-radius:50%; object-fit: cover; border-color: <?php echo htmlspecialchars($teamColor); ?>;">
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
    </header>

    <div class="championship-layout full-width">
        <div class="championship-content full-width">
            <div class="control-panel">
                <div class="panel-header">
                    <div class="panel-icon">
                        <i class="fas fa-stopwatch"></i>
                    </div>
                    <h2>SZABADEDZÉS <span>EREDMÉNYEK</span></h2>
                </div>

                <div class="race-select-container">
                    <label class="race-select-label" for="yearSelect">Válaszd ki a szezont</label>
                    <select id="yearSelect" class="race-select"></select>
                </div>

                <div class="race-select-container">
                    <label class="race-select-label" for="raceSelect">Válaszd ki a futamot</label>
                    <select id="raceSelect" class="race-select" disabled>
                        <option>Szezon betöltése...</option>
                    </select>
                </div>

                <div class="current-selection">
                    <div class="selection-info">
                        <span class="selection-year" id="selectedYear">2026</span>
                        <span class="selection-type">
                            <span id="selectedType">SZABADEDZÉS</span> SZEZON
                        </span>
                    </div>
                    <button class="update-btn" id="updateBtn">
                        <i class="fas fa-sync-alt"></i> FRISSÍTÉS
                    </button>
                </div>
            </div>

            <div class="standings-card">
                <div class="table-header">
                    <h2>
                        <i class="fas fa-info-circle"></i>
                        <span id="tableTitle">SZABADEDZÉS EREDMÉNYEK</span>
                    </h2>
                    <div class="season-badge" id="seasonBadge">2026-OS SZEZON</div>
                </div>

                <div class="f1-table-wrapper">
                    <table class="f1-table">
                        <thead>
                            <tr>
                                <th style="width: 60px;">HELY</th>
                                <th>VERSENYZŐ</th>
                                <th>CSAPAT</th>
                                <th style="text-align:center;">LEGGYORSABB KÖR</th>
                                <th style="text-align:center;">KÜLÖNBSÉG</th>
                            </tr>
                        </thead>
                        <tbody id="standingsBody">
                            <tr>
                                <td colspan="5" class="loading-message">
                                    <i class="fas fa-circle-notch fa-spin"></i> Válassz egy futamot és nyomj a Frissítés-re...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

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
                <button class="btn-send-msg" onclick="window.location.href='/f1fanclub/messages/messages.php'"><i class="fas fa-comment"></i> Üzenet küldése</button>
            </div>
        </div>
    </div>

    <script>
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

        // HAMBURGER MENU TOGGLE
        const hamburgerBtn = document.getElementById('hamburgerBtn');
        const mainNav = document.getElementById('mainNav');
        
        if (hamburgerBtn && mainNav) {
            hamburgerBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                mainNav.classList.toggle('open');
            });
            
            // Close menu when clicking outside
            document.addEventListener('click', function(event) {
                if (mainNav.classList.contains('open') && 
                    !mainNav.contains(event.target) && 
                    !hamburgerBtn.contains(event.target)) {
                    mainNav.classList.remove('open');
                }
            });
            
            // Close menu when a nav link is clicked
            const navLinks = mainNav.querySelectorAll('a');
            navLinks.forEach(link => {
                link.addEventListener('click', function() {
                    mainNav.classList.remove('open');
                });
            });
        }

        // USER PROFILE POP-UP
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

        // FREE PRACTICE FUNCTIONALITY
        document.addEventListener('DOMContentLoaded', () => {
            let currentYear = new Date().getFullYear();
            let currentRound = "1"; 
            
            const yearSelect = document.getElementById('yearSelect');
            const raceSelect = document.getElementById('raceSelect');
            const selectedYearSpan = document.getElementById('selectedYear');
            const tableTitle = document.getElementById('tableTitle');
            const seasonBadge = document.getElementById('seasonBadge');
            const standingsBody = document.getElementById('standingsBody');
            const updateBtn = document.getElementById('updateBtn');

            const currentYearNum = new Date().getFullYear();
            for (let year = currentYearNum; year >= 1950; year--) {
                const option = document.createElement('option');
                option.value = year;
                option.textContent = year + " SZEZON";
                if (year === currentYear) option.selected = true;
                yearSelect.appendChild(option);
            }

            yearSelect.addEventListener('change', (e) => {
                currentYear = parseInt(e.target.value);
                selectedYearSpan.textContent = currentYear;
                seasonBadge.textContent = `${currentYear}-OS SZEZON`;
                fetchRacesForYear(currentYear);
            });

            function fetchRacesForYear(year) {
                raceSelect.innerHTML = '<option>Futamok betöltése...</option>';
                raceSelect.disabled = true;

                fetch(`https://api.jolpi.ca/ergast/f1/${year}.json`)
                    .then(res => res.json())
                    .then(data => {
                        const races = data.MRData.RaceTable.Races;
                        if (races && races.length > 0) {
                            raceSelect.innerHTML = '';
                            races.forEach(race => {
                                const option = document.createElement('option');
                                option.value = race.round;
                                option.textContent = `F${race.round} - ${race.raceName} (${race.Circuit.circuitName})`;
                                raceSelect.appendChild(option);
                            });
                            raceSelect.disabled = false;
                            currentRound = raceSelect.value;
                            loadPractice(raceSelect.options[raceSelect.selectedIndex].text);
                        } else {
                            raceSelect.innerHTML = '<option>Nincsenek futam adatok ehhez az évhez.</option>';
                            standingsBody.innerHTML = '<tr><td colspan="5" style="text-align:center; padding:40px; color:#666;">Nem találtunk futamokat.</td></tr>';
                        }
                    })
                    .catch(err => {
                        raceSelect.innerHTML = '<option>Hiba a betöltéskor.</option>';
                    });
            }

            updateBtn.addEventListener('click', () => {
                if(!raceSelect.disabled) {
                    currentRound = raceSelect.value;
                    const raceName = raceSelect.options[raceSelect.selectedIndex].text;
                    loadPractice(raceName);
                }
            });

            raceSelect.addEventListener('change', () => {
                currentRound = raceSelect.value;
                const raceName = raceSelect.options[raceSelect.selectedIndex].text;
                loadPractice(raceName);
            });

            function loadPractice(raceName) {
                const cleanRaceName = raceName.replace(/F\d+ - /, '');
                tableTitle.innerHTML = `${cleanRaceName} <span style="color:#666;">|</span> SZABADEDZÉS`;

                // Inform the user that free practice data is not available via public API
                standingsBody.innerHTML = `
                    <tr>
                        <td colspan="5" class="info-message">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>A szabadedzések adatai nem elérhetők</strong><br><br>
                            A hivatalos ingyenes Formula 1 API jelenleg <strong>nem rögzíti és nem szolgáltatja</strong> 
                            a szabadedzések (FP1, FP2, FP3) részletes köridejeit visszamenőlegesen.<br><br>
                            <span style="font-size:0.9rem; color:#666; display:inline-block; margin-top:10px;">
                                Kérjük, látogasd meg az <a href="/f1fanclub/idomero/idomero.php" style="color:#e10600; text-decoration:none;">Időmérő</a> 
                                vagy a <a href="/f1fanclub/Championship/championship.php" style="color:#e10600; text-decoration:none;">Bajnokság</a> 
                                menüpontokat az elérhető statisztikákért!
                            </span>
                        </td>
                    </tr>
                `;
            }

            selectedYearSpan.textContent = currentYear;
            seasonBadge.textContent = `${currentYear}-OS SZEZON`;
            fetchRacesForYear(currentYear); 
        });
    </script>
</body>
</html>