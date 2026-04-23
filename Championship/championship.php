<?php
session_start();

$DB_HOST = "localhost";
$DB_USER = "swmjndga_swmjndga";
$DB_PASS = "Teszt1234!";
$DB_NAME = "swmjndga_f1adat";

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) {
    die("Adatbázis hiba: " . $conn->connect_error);
}

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
?>
<!DOCTYPE html>
<html lang="hu">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Bajnokság Állás - F1 Fan Club</title>
    <link rel="icon" type="image/svg+xml" href="https://upload.wikimedia.org/wikipedia/commons/3/33/F1.svg">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">
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

        /* Championship specific styles */
        .championship-layout {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
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

        .championship-toggle {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
        }

        .toggle-btn {
            flex: 1;
            padding: 15px 30px;
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid rgba(225, 6, 0, 0.2);
            border-radius: 15px;
            color: #aaa;
            font-size: 1.1rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            position: relative;
            overflow: hidden;
        }

        .toggle-btn:hover {
            background: rgba(225, 6, 0, 0.1);
            border-color: #e10600;
            color: white;
        }

        .toggle-btn.active {
            background: #e10600;
            border-color: #e10600;
            color: white;
            box-shadow: 0 5px 20px rgba(225, 6, 0, 0.4);
        }

        .toggle-text {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .year-grid {
            display: grid;
            grid-template-columns: repeat(8, 1fr);
            gap: 15px;
            margin-bottom: 30px;
        }

        .year-item {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 15px 10px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .year-item:hover {
            background: rgba(225, 6, 0, 0.1);
            border-color: #e10600;
            transform: translateY(-3px);
        }

        .year-item.active {
            background: #e10600;
            border-color: #e10600;
            box-shadow: 0 5px 20px rgba(225, 6, 0, 0.4);
        }

        .year-value {
            font-size: 1.3rem;
            font-weight: 800;
            color: white;
            display: block;
            margin-bottom: 5px;
        }

        .year-label {
            font-size: 0.7rem;
            color: #aaa;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .year-item.active .year-label {
            color: rgba(255, 255, 255, 0.8);
        }

        .year-nav {
            background: rgba(225, 6, 0, 0.1);
            border-color: rgba(225, 6, 0, 0.3);
        }

        .year-nav i {
            font-size: 1.2rem;
            color: #e10600;
        }

        .current-selection {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            background: rgba(0, 0, 0, 0.3);
            border-radius: 15px;
            border: 1px solid rgba(225, 6, 0, 0.2);
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
            transition: all 0.3s ease;
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

        .f1-points {
            text-align: right;
            font-weight: 700;
            color: #e10600;
            font-size: 1.1rem;
        }

        .f1-champion {
            background: linear-gradient(90deg, rgba(212, 175, 55, 0.15) 0%, rgba(212, 175, 55, 0.05) 40%, transparent 100%) !important;
            border-left: 4px solid #d4af37;
            position: relative;
        }

        /* Gradient fade effect for each row based on team color */
        .f1-row-gradient {
            transition: all 0.3s ease;
        }

        .loading-message {
            text-align: center;
            padding: 40px !important;
            color: #666;
            font-size: 1.1rem;
        }

        .loading-message i {
            margin-right: 10px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
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

        /* Championship responsive styles */
        @media (max-width: 992px) {
            .year-grid {
                grid-template-columns: repeat(4, 1fr) !important;
                gap: 8px;
                padding: 15px;
            }
            
            .year-value {
                font-size: 1rem;
            }
            
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
                min-width: 600px;
            }
            
            th, td {
                padding: 8px 10px;
                font-size: 0.75rem;
            }
        }
        
        @media (max-width: 768px) {
            .year-grid {
                grid-template-columns: repeat(3, 1fr) !important;
            }
            
            .selection-year {
                font-size: 1.5rem;
            }
            
            .season-badge {
                font-size: 0.7rem;
                padding: 4px 10px;
            }
            
            .championship-toggle {
                flex-direction: column;
            }
            
            .toggle-btn {
                padding: 12px 20px;
                font-size: 0.9rem;
            }
        }
        
        @media (max-width: 576px) {
            .year-nav .year-label {
                display: none;
            }
            
            .toggle-text {
                font-size: 0.65rem;
            }
            
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
            
            .year-grid {
                grid-template-columns: repeat(2, 1fr) !important;
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
            <a href="../index.php">Kezdőlap</a>
            <a href="championship.php" class="active">Bajnokság</a>
            <a href="../teams/teams.php">Csapatok</a>
            <a href="../drivers/drivers.php">Versenyzők</a>
            <a href="../news/feed.php">Paddock</a>
            <a href="../pitwall/pitwall.php"><i class="fas fa-trophy" style="margin-right: 5px;"></i> A Fal</a>
        </nav>

        <!-- DROPDOWN MENU -->
        <?php if ($isLoggedIn): ?>
            <div class="dropdown-container" id="userDropdownContainer">
                <div class="auth">
                    <div class="welcome" style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                        <?php if ($profile_image): ?>
                            <img src="../uploads/<?php echo htmlspecialchars($profile_image); ?>" class="avatar clickable-user"
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
                    <a href="../profile/profile.php">
                        <i class="fas fa-user-circle"></i> Profilom
                    </a>
                    <a href="../messages/messages.php">
                        <i class="fas fa-envelope"></i> Üzenetek
                    </a>
                    <?php if ($isAdmin): ?>
                        <a href="../admin/admin.php" style="position: relative;">
                            <i class="fas fa-shield-alt"></i> Admin Panel
                            <span class="admin-badge">ADMIN</span>
                        </a>
                    <?php endif; ?>
                    <div class="dropdown-divider"></div>
                    <a href="../logout/logout.php">
                        <i class="fas fa-sign-out-alt"></i> Kijelentkezés
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="auth">
                <a href="../register/register.html" class="btn">Regisztráció</a>
                <a href="../login/login.html" class="btn">Bejelentkezés</a>
            </div>
        <?php endif; ?>
    </header>

    <!-- Full Width Championship Layout -->
    <div class="championship-layout full-width">
        <!-- Championship Content -->
        <div class="championship-content full-width">

            <!-- Control Panel -->
            <div class="control-panel">
                <div class="panel-header">
                    <div class="panel-icon">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <h2>BAJNOKSÁG <span>VEZÉRLŐPULT</span></h2>
                </div>

                <!-- Driver/Constructor Toggle -->
                <div class="championship-toggle" id="championshipToggle">
                    <button class="toggle-btn active" data-type="drivers">
                        <span class="toggle-text">
                            <i class="fas fa-user"></i> VERSENYZŐK
                        </span>
                        <span class="toggle-glow"></span>
                    </button>
                    <button class="toggle-btn" data-type="constructors">
                        <span class="toggle-text">
                            <i class="fas fa-building"></i> KONSTRUKTŐRÖK
                        </span>
                        <span class="toggle-glow"></span>
                    </button>
                </div>

                <!-- Year Grid -->
                <div class="year-grid" id="yearGrid">
                    <!-- Years will be populated by JavaScript -->
                </div>

                <!-- Current Selection Display -->
                <div class="current-selection">
                    <div class="selection-info">
                        <span class="selection-year" id="selectedYear">2025</span>
                        <span class="selection-type">
                            <span id="selectedType">VERSENYZŐK</span> BAJNOKSÁG
                        </span>
                    </div>
                    <button class="update-btn" id="updateBtn">
                        <i class="fas fa-sync-alt"></i> FRISSÍTÉS
                    </button>
                </div>
            </div>

            <!-- Standings Table -->
            <div class="standings-card">
                <div class="table-header">
                    <h2>
                        <i class="fas fa-flag-checkered"></i>
                        <span id="tableTitle">VERSENYZŐI BAJNOKSÁG 2025</span>
                    </h2>
                    <div class="season-badge" id="seasonBadge">2025-ÖS SZEZON</div>
                </div>

                <div class="f1-table-wrapper">
                    <table class="f1-table">
                        <thead>
                            <tr>
                                <th>HELY</th>
                                <th id="col1Header">VERSENYZŐ</th>
                                <th id="col2Header">CSAPAT</th>
                                <th style="text-align:right;">PONT</th>
                            </tr>
                        </thead>
                        <tbody id="standingsBody">
                            <tr>
                                <td colspan="4" class="loading-message">
                                    <i class="fas fa-circle-notch"></i> Bajnoki adatok betöltése...
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
                <button class="btn-send-msg" onclick="window.location.href='../messages/messages.php'"><i class="fas fa-comment"></i> Üzenet küldése</button>
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
            fetch('../profile/user_profile_api.php?username=' + encodeURIComponent(username))
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

            fetch('../profile/friend_api.php', {
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

        // Championship functionality
        document.addEventListener('DOMContentLoaded', () => {
            // State management
            let currentYear = new Date().getFullYear();
            let currentType = 'drivers';
            
            const yearGrid = document.getElementById('yearGrid');
            const selectedYearSpan = document.getElementById('selectedYear');
            const selectedTypeSpan = document.getElementById('selectedType');
            const tableTitle = document.getElementById('tableTitle');
            const seasonBadge = document.getElementById('seasonBadge');
            const standingsBody = document.getElementById('standingsBody');
            const col1Header = document.getElementById('col1Header');
            const col2Header = document.getElementById('col2Header');
            
            const toggleBtns = document.querySelectorAll('.championship-toggle .toggle-btn');
            const updateBtn = document.getElementById('updateBtn');

            // COMPLETE TEAM COLOR MAPPING - ALL TEAMS
            function getTeamColorJS(teamName) {
                const teamColors = {
                    // Current Teams (2025)
                    'Red Bull': '#1E41FF',
                    'Ferrari': '#DC0000',
                    'Mercedes': '#00D2BE',
                    'McLaren': '#FF8700',
                    'Aston Martin': '#006F62',
                    'Alpine': '#0090FF',
                    'Williams': '#00A0DE',
                    'RB': '#2b2bff',
                    'Racing Bulls': '#2b2bff',
                    'Audi': '#e3000f',
                    'Haas F1 Team': '#B6BABD',
                    'Haas': '#B6BABD',
                    'Cadillac': '#B6BABD',
                    
                    // Historic Teams - 1950s
                    'Alfa Romeo': '#900000',
                    'Maserati': '#0047AB',
                    'Talbot-Lago': '#C0C0C0',
                    'Simca-Gordini': '#0047AB',
                    'Gordini': '#0047AB',
                    'ERA': '#800000',
                    'Alta': '#800080',
                    'Cooper': '#004225',
                    'Vanwall': '#006633',
                    'BRM': '#800000',
                    'Connaught': '#004225',
                    'HWM': '#0066CC',
                    'Frazer Nash': '#0066CC',
                    'OSCA': '#FF4500',
                    'Lancia': '#0000FF',
                    'Bugatti': '#002366',
                    'Aston Butterworth': '#006F62',
                    'Kurtis Kraft': '#8B4513',
                    'Kuzma': '#CD7F32',
                    'Epperly': '#CD7F32',
                    'Watson': '#4682B4',
                    'Phillips': '#708090',
                    'Lesovsky': '#8B4513',
                    'Trevis': '#2F4F4F',
                    'Sutton': '#DAA520',
                    'Blanchard': '#708090',
                    'Langley': '#696969',
                    'Pankratz': '#8B4513',
                    'Adams': '#708090',
                    'JBW': '#800020',
                    'Stebro': '#708090',
                    'Scirocco': '#808000',
                    'Roe': '#800080',
                    'Fry': '#DAA520',
                    'Gilby': '#708090',
                    'EMW': '#003399',
                    
                    // 1960s-1970s
                    'Lotus': '#FFB800',
                    'Brabham': '#006633',
                    'Eagle': '#FFD700',
                    'Honda': '#CC0000',
                    'Matra': '#0033FF',
                    'March': '#FF4500',
                    'Surtees': '#FF0000',
                    'Tyrrell': '#004586',
                    'Shadow': '#2F4F4F',
                    'Penske': '#C0C0C0',
                    'Ensign': '#0000FF',
                    'Wolf': '#FFD700',
                    'Renault': '#FFD800',
                    'Ligier': '#0033FF',
                    'Arrows': '#FFD700',
                    'Fittipaldi': '#FFD700',
                    'Copersucar': '#FFD700',
                    'ATS': '#FF0000',
                    'Theodore': '#FFD700',
                    'Osella': '#FF0000',
                    'Toleman': '#0033FF',
                    'Spirit': '#FFD700',
                    'RAM': '#0000FF',
                    'Zakspeed': '#FFD700',
                    
                    // 1980s-1990s
                    'Benetton': '#00A65E',
                    'Minardi': '#FFFF00',
                    'Dallara': '#DC143C',
                    'Larrousse': '#0000FF',
                    'Coloni': '#006400',
                    'EuroBrun': '#FFD700',
                    'Onyx': '#C0C0C0',
                    'Life': '#800080',
                    'Jordan': '#FFFF00',
                    'Modena': '#0033FF',
                    'Fondmetal': '#FFD700',
                    'Footwork': '#0000FF',
                    'Venturi': '#FF0000',
                    'Pacific': '#0066FF',
                    'Simtek': '#FF0000',
                    'Forti': '#0066FF',
                    'Sauber': '#0066FF',
                    'Stewart': '#003366',
                    'Prost': '#0000FF',
                    'BAR': '#0000FF',
                    'Jaguar': '#006400',
                    'Toyota': '#CC0000',
                    'BMW Sauber': '#0066FF',
                    'BMW': '#0066FF',
                    'Super Aguri': '#DC143C',
                    'Spyker': '#FFA500',
                    'Force India': '#FF4F00',
                    'HRT': '#A9A9A9',
                    'Caterham': '#008000',
                    'Marussia': '#800000',
                    'Manor': '#2F4F4F',
                    'Virgin': '#FF6600',
                    'Lotus F1': '#FFB800',
                    'Toro Rosso': '#0033FF',
                    'AlphaTauri': '#2b2b2b',
                    'Racing Point': '#F596C8',
                    'Brawn GP': '#C0C0C0',
                    
                    // Additional variations
                    'Red Bull Racing': '#1E41FF',
                    'Scuderia Ferrari': '#DC0000',
                    'Mercedes AMG': '#00D2BE',
                    'McLaren Mercedes': '#FF8700',
                    'Williams Racing': '#00A0DE',
                    'Alpine F1 Team': '#0090FF',
                    'Aston Martin Aramco': '#006F62',
                    'Haas F1': '#B6BABD',
                    'RB F1 Team': '#2b2bff',
                    'Visa Cash App RB': '#2b2bff',
                    'Stake F1 Team': '#00D2BE',
                    'Kick Sauber': '#00D2BE'
                };
                
                // Exact match
                if (teamColors[teamName]) {
                    return teamColors[teamName];
                }
                
                // Case-insensitive match
                const lowerTeamName = teamName.toLowerCase();
                for (let key in teamColors) {
                    if (key.toLowerCase() === lowerTeamName) {
                        return teamColors[key];
                    }
                }
                
                // Partial match
                for (let key in teamColors) {
                    if (lowerTeamName.includes(key.toLowerCase()) || 
                        key.toLowerCase().includes(lowerTeamName)) {
                        return teamColors[key];
                    }
                }
                
                // Generate consistent color for unknown teams
                let hash = 0;
                for (let i = 0; i < teamName.length; i++) {
                    hash = teamName.charCodeAt(i) + ((hash << 5) - hash);
                }
                const hue = Math.abs(hash) % 360;
                return `hsl(${hue}, 70%, 50%)`;
            }

            const currentYearNum = new Date().getFullYear();
            const years = [];
            for (let year = currentYearNum; year >= 1950; year--) {
                years.push(year);
            }

            const yearChunks = [];
            for (let i = 0; i < years.length; i += 6) {
                yearChunks.push(years.slice(i, i + 6));
            }
            let currentChunkIndex = 0;

            function renderYearGrid(chunkIndex) {
                const chunk = yearChunks[chunkIndex];
                if (!chunk) return;

                let html = '';
                
                html += `
                    <div class="year-item year-nav prev-year">
                        <i class="fas fa-chevron-left"></i>
                        <span class="year-label">ELŐZŐ</span>
                    </div>
                `;
                
                chunk.forEach(year => {
                    const isActive = year === currentYear;
                    html += `
                        <div class="year-item ${isActive ? 'active' : ''}" data-year="${year}">
                            <span class="year-value">${year}</span>
                            <span class="year-label">SZEZON</span>
                        </div>
                    `;
                });
                
                html += `
                    <div class="year-item year-nav next-year">
                        <i class="fas fa-chevron-right"></i>
                        <span class="year-label">KÖVETKEZŐ</span>
                    </div>
                `;
                
                yearGrid.innerHTML = html;

                document.querySelectorAll('.year-item:not(.year-nav)').forEach(item => {
                    item.addEventListener('click', () => {
                        const year = parseInt(item.getAttribute('data-year'));
                        if (year !== currentYear) {
                            document.querySelectorAll('.year-item').forEach(y => y.classList.remove('active'));
                            item.classList.add('active');
                            currentYear = year;
                            updateDisplay();
                        }
                    });
                });

                const prevBtn = document.querySelector('.prev-year');
                const nextBtn = document.querySelector('.next-year');
                
                if (prevBtn) {
                    prevBtn.addEventListener('click', () => {
                        if (currentChunkIndex > 0) {
                            currentChunkIndex--;
                            renderYearGrid(currentChunkIndex);
                        }
                    });
                }
                
                if (nextBtn) {
                    nextBtn.addEventListener('click', () => {
                        if (currentChunkIndex < yearChunks.length - 1) {
                            currentChunkIndex++;
                            renderYearGrid(currentChunkIndex);
                        }
                    });
                }
            }

            toggleBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const type = this.getAttribute('data-type');
                    toggleBtns.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    currentType = type;
                    updateDisplay();
                });
            });

            updateBtn.addEventListener('click', () => {
                loadStandings(currentYear, currentType);
            });

            function updateDisplay() {
                selectedYearSpan.textContent = currentYear;
                selectedTypeSpan.textContent = currentType === 'drivers' ? 'VERSENYZŐK' : 'KONSTRUKTŐRÖK';
                seasonBadge.textContent = `${currentYear}-ÖS SZEZON`;
                loadStandings(currentYear, currentType);
            }

            function loadStandings(year, type) {
                if (type === 'drivers') {
                    tableTitle.textContent = `VERSENYZŐI BAJNOKSÁG ${year}`;
                    col1Header.textContent = 'VERSENYZŐ';
                    col2Header.textContent = 'CSAPAT';
                } else {
                    tableTitle.textContent = `KONSTRUKTŐRI BAJNOKSÁG ${year}`;
                    col1Header.textContent = 'KONSTRUKTŐR';
                    col2Header.textContent = 'NEMZETISÉG';
                }

                standingsBody.innerHTML = `
                    <tr>
                        <td colspan="4" class="loading-message">
                            <i class="fas fa-circle-notch"></i> ${year} ${type === 'drivers' ? 'versenyzői' : 'konstruktőri'} bajnoki adatok betöltése...
                        </td>
                    </tr>
                `;

                const apiUrl = type === 'drivers' 
                    ? `https://api.jolpi.ca/ergast/f1/${year}/driverStandings.json`
                    : `https://api.jolpi.ca/ergast/f1/${year}/constructorStandings.json`;

                if (type === 'constructors' && year < 1958) {
                    standingsBody.innerHTML = '<tr><td colspan="4" style="text-align:center; padding:40px; color:#666;">A konstruktőri bajnokságot 1958 előtt nem osztották ki.</td></tr>';
                    return;
                }

                fetch(apiUrl)
                    .then(res => res.json())
                    .then(data => {
                        const standingsList = data.MRData.StandingsTable.StandingsLists[0];
                        const list = type === 'drivers' 
                            ? standingsList?.DriverStandings 
                            : standingsList?.ConstructorStandings;
                        
                        if (!list || list.length === 0) {
                            standingsBody.innerHTML = '<tr><td colspan="4" style="text-align:center; padding:40px; color:#666;">Nincs elérhető adat ehhez a szezonhoz.</td></tr>';
                            return;
                        }

                        let html = '';
                        list.forEach(item => {
                            const isChampion = item.position === "1";
                            const rowClass = isChampion ? 'f1-champion' : '';
                            const trophy = isChampion ? '<i class="fas fa-trophy" style="color:#d4af37; margin-right:8px;"></i>' : '';
                            
                            if (type === 'drivers') {
                                const teamName = item.Constructors?.[0]?.name || "Privát";
                                const teamColor = getTeamColorJS(teamName);
                                // Add gradient fade effect similar to idomero.php
                                const gradientStyle = `linear-gradient(90deg, ${teamColor}20 0%, transparent 40%)`;
                                html += `
                                    <tr class="${rowClass}" style="background: ${gradientStyle};">
                                        <td class="f1-pos">${item.position}</td>
                                        <td class="f1-name">${trophy}${item.Driver.givenName} <span>${item.Driver.familyName}</span></td>
                                        <td class="f1-team" style="color: ${teamColor};">${teamName}</td>
                                        <td class="f1-points">${item.points}</td>
                                    </tr>
                                `;
                            } else {
                                const constName = item.Constructor?.name || "Ismeretlen";
                                const constNat = item.Constructor?.nationality || "";
                                const teamColor = getTeamColorJS(constName);
                                // Add gradient fade effect for constructors too
                                const gradientStyle = `linear-gradient(90deg, ${teamColor}20 0%, transparent 40%)`;
                                html += `
                                    <tr class="${rowClass}" style="background: ${gradientStyle};">
                                        <td class="f1-pos">${item.position}</td>
                                        <td class="f1-name">${trophy}<span style="color: ${teamColor};">${constName}</span></td>
                                        <td class="f1-team">${constNat}</td>
                                        <td class="f1-points">${item.points}</td>
                                    </tr>
                                `;
                            }
                        });
                        standingsBody.innerHTML = html;
                    })
                    .catch(error => {
                        standingsBody.innerHTML = '<tr><td colspan="4" style="text-align:center; padding:40px; color:#e10600;">Hiba történt az adatok betöltésekor.</td></tr>';
                    });
            }

            renderYearGrid(0);
            loadStandings(currentYear, currentType);
        });
    </script>
</body>
</html>