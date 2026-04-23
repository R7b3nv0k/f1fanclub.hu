<?php
session_start();

$DB_HOST = "localhost";
$DB_USER = "swmjndga_swmjndga";
$DB_PASS = "Teszt1234!";
$DB_NAME = "swmjndga_f1adat";

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) { die("Adatbázis csatlakozási hiba: " . $conn->connect_error); }

$isLoggedIn = isset($_SESSION['username']);
if (!$isLoggedIn) { header("Location: ../login/login.html"); exit; }

$username = $_SESSION['username'];

// 1. Felhasználó adatainak (pontok és email) lekérése
$stmt = $conn->prepare("SELECT email, pitwall_points, profile_image, fav_team, role FROM users WHERE username=?");
$stmt->bind_param("s", $username);
$stmt->execute();
$userData = $stmt->get_result()->fetch_assoc();
$stmt->close();

$userPoints = (int)($userData['pitwall_points'] ?? 0);
$userEmail = $userData['email'];
$isAdmin = !empty($userData['role']) && $userData['role'] === 'admin';

function getTeamColor($team) {
  switch ($team) {
    case 'Red Bull': return '#1E41FF'; case 'Ferrari': return '#DC0000'; case 'Mercedes': return '#00D2BE';
    case 'McLaren': return '#FF8700'; case 'Aston Martin': return '#006F62'; case 'Alpine': return '#0090FF';
    case 'Williams': return '#00A0DE'; case 'RB': return '#2b2bff'; case 'Audi': return '#e3000f';
    case 'Haas F1 Team': return '#B6BABD'; case 'Cadillac': return '#B6BABD'; default: return '#ffffff';
  }
}
$teamColor = getTeamColor($userData['fav_team'] ?? null);
$profile_image = $userData['profile_image'] ?? 'default_avatar.png';

// 2. A "Webshop" termékei
$rewards = [
    1 => ['points' => 150, 'title' => 'Pitwall Stratéga Jelvény', 'desc' => 'Exkluzív digitális profil jelvény, ami megjelenik a neved mellett.', 'icon' => 'fa-id-badge'],
    2 => ['points' => 300, 'title' => 'VIP Rang & Chat Szín', 'desc' => 'Kiemelt VIP rang az oldalon és egyedi szín a rajongói chaten.', 'icon' => 'fa-crown'],
    3 => ['points' => 500, 'title' => '10% F1 Webshop Kupon', 'desc' => 'Valódi 10% kedvezmény kupon a hivatalos F1 Webshopba.', 'icon' => 'fa-ticket-alt'],
    4 => ['points' => 800, 'title' => 'Ajándékcsomag Sorsolás', 'desc' => 'Garantált részvételi jegy a havi F1 Fan Club ajándékcsomag sorsoláson!', 'icon' => 'fa-box-open']
];

$message = "";
$msgType = ""; // 'success' vagy 'error'

// 3. Vásárlás feldolgozása (Ha a felhasználó rányomott a beváltás gombra)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reward_id'])) {
    $reward_id = (int)$_POST['reward_id'];
    
    if (isset($rewards[$reward_id])) {
        $cost = $rewards[$reward_id]['points'];
        $title = $rewards[$reward_id]['title'];
        
        if ($userPoints >= $cost) {
            // A) Levonjuk a pontokat
            $newPoints = $userPoints - $cost;
            $updateStmt = $conn->prepare("UPDATE users SET pitwall_points = ? WHERE username = ?");
            $updateStmt->bind_param("is", $newPoints, $username);
            
            if ($updateStmt->execute()) {
                $userPoints = $newPoints; // Frissítjük a kijelzőt is
                
                // B) E-mail küldése a felhasználónak
                $to = $userEmail;
                $subject = "Sikeres beváltás: $title - F1 Fan Club";
                $emailBody = "Kedves $username!\n\n"
                           . "Gratulálunk! Sikeresen beváltottál $cost Pitwall pontot a következő nyereményre:\n\n"
                           . "--- $title ---\n\n"
                           . "A nyereményeddel (vagy a kuponkódoddal) kapcsolatban az adminisztrátoraink hamarosan felveszik veled a kapcsolatot ezen az e-mail címen.\n\n"
                           . "Aktuális pontegyenleged: $userPoints PONT.\n\n"
                           . "Üdvözlettel,\nAz F1 Fan Club Csapata";
                
                $headers = "From: noreply@f1fanclub.hu\r\n";
                $headers .= "Reply-To: info@f1fanclub.hu\r\n";
                $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
                
                // PHP mail függvény (a tárhelyed beállításaitól függően küldi ki)
                @mail($to, $subject, $emailBody, $headers);
                
                $message = "Sikeres beváltás! Levontunk $cost pontot. A visszaigazolást elküldtük az e-mail címedre!";
                $msgType = "success";
            }
            $updateStmt->close();
        } else {
            $message = "Nincs elég pontod ehhez a nyereményhez!";
            $msgType = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes, maximum-scale=1.0">
  <title>Pitwall Shop - F1 Fan Club</title>
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
        overflow-x: hidden; 
    }
    
    /* --- HEADER STYLES --- */
    header { 
        background-color: #0a0a0a; 
        border-bottom: 2px solid rgba(225, 6, 0, 0.3); 
        padding: 0 40px; 
        height: 80px; 
        display: flex; 
        align-items: center; 
        justify-content: space-between; 
        position: sticky; 
        top: 0; 
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
    }
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
        position: relative; 
        transition: all 0.2s ease; 
        letter-spacing: 0.5px; 
        opacity: 0.9; 
    }
    nav a:hover { 
        color: #e10600 !important; 
        opacity: 1; 
        background: rgba(225, 6, 0, 0.1); 
    }
    nav a.active { 
        color: #e10600 !important; 
        opacity: 1; 
        font-weight: 700; 
        background: rgba(225, 6, 0, 0.15); 
    }
    
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

    .welcome img.avatar:hover { transform: scale(1.1); }

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

    .dropdown-menu-modern a:last-child { border-bottom: none; }

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

    .dropdown-container.open .dropdown-arrow-icon { transform: rotate(180deg); }

    .clickable-user { cursor: pointer; }

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

    /* --- RESPONSIVE HEADER --- */
    @media (min-width: 993px) {
        nav { display: flex !important; }
    }

    @media (max-width: 992px) {
        .hamburger { display: block; }
        .left-header { display: none; }
        
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
        
        nav.open { display: flex; }
        
        nav a {
            padding: 15px 20px;
            margin: 0;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            text-align: center;
            font-size: 1rem;
        }
        
        nav a:last-child { border-bottom: none; }
        
        header {
            position: sticky;
            top: 0;
            flex-wrap: nowrap;
            justify-content: flex-start;
            gap: 15px;
            padding: 0 20px;
        }
        
        .hamburger { margin-right: auto; }
        .auth { margin-left: 0; }
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
        header { padding: 0 15px; }
        nav a {
            padding: 12px 15px;
            font-size: 0.85rem;
        }
    }

    /* --- SHOP SPECIFIC STYLES --- */
    .shop-container { 
        max-width: 1200px; 
        margin: 40px auto; 
        padding: 0 20px;
        width: 100%;
        box-sizing: border-box;
    }
    
    /* PÉNZTÁRCA (PONTOK) SZEKCIÓ */
    .wallet-banner {
        background: linear-gradient(145deg, #111, #1a1a24);
        border: 2px solid #d4af37;
        border-radius: 15px;
        padding: 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 40px;
        box-shadow: 0 10px 30px rgba(212, 175, 55, 0.15);
        width: 100%;
        box-sizing: border-box;
    }
    
    .wallet-left h1 { 
        font-size: 2rem; 
        color: #fff; 
        text-transform: uppercase; 
        font-weight: 900; 
        letter-spacing: 1px; 
        margin-bottom: 5px;
    }
    
    .wallet-left p { color: #aaa; }
    
    .wallet-points {
        font-size: 3.5rem;
        font-weight: 900;
        color: #d4af37;
        text-shadow: 0 0 20px rgba(212, 175, 55, 0.4);
        display: flex;
        align-items: center;
        gap: 15px;
        flex-wrap: wrap;
    }
    
    .wallet-points i { font-size: 2.5rem; }

    /* WEBSHOP RÁCS */
    .shop-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 30px;
        width: 100%;
    }
    
    .product-card {
        background: #15151e;
        border-radius: 15px;
        border: 1px solid #333;
        padding: 30px 20px;
        text-align: center;
        transition: 0.3s;
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        width: 100%;
        box-sizing: border-box;
    }
    
    .product-card:hover {
        transform: translateY(-10px);
        border-color: #d4af37;
        box-shadow: 0 15px 30px rgba(0,0,0,0.8), 0 0 20px rgba(212, 175, 55, 0.2);
    }
    
    .product-icon {
        font-size: 3.5rem;
        color: #d4af37;
        margin-bottom: 20px;
        filter: drop-shadow(0 5px 10px rgba(0,0,0,0.5));
    }
    
    .product-title { 
        font-size: 1.3rem; 
        font-weight: 800; 
        margin-bottom: 10px; 
        color: #fff; 
    }
    
    .product-desc { 
        color: #aaa; 
        font-size: 0.9rem; 
        line-height: 1.5; 
        margin-bottom: 25px; 
        flex-grow: 1; 
    }
    
    .product-price {
        font-size: 1.5rem;
        font-weight: 900;
        color: #fff;
        background: #222;
        padding: 10px;
        border-radius: 8px;
        margin-bottom: 20px;
        border: 1px solid #444;
    }
    
    .product-price span { color: #d4af37; }

    .btn-buy {
        width: 100%;
        padding: 12px;
        border: none;
        border-radius: 8px;
        font-size: 1.1rem;
        font-weight: 800;
        text-transform: uppercase;
        cursor: pointer;
        transition: 0.3s;
    }
    
    .btn-buy.active {
        background: linear-gradient(145deg, #d4af37, #aa8c2c);
        color: #000;
        box-shadow: 0 5px 15px rgba(212, 175, 55, 0.3);
    }
    
    .btn-buy.active:hover { 
        background: linear-gradient(145deg, #f5cc47, #c4a132); 
        transform: translateY(-2px); 
    }
    
    .btn-buy.disabled {
        background: #333;
        color: #666;
        cursor: not-allowed;
    }

    /* ÜZENETEK */
    .alert { 
        padding: 20px; 
        border-radius: 10px; 
        margin-bottom: 30px; 
        font-weight: 600; 
        text-align: center; 
        font-size: 1.1rem; 
        animation: popIn 0.5s; 
    }
    
    .alert.success { 
        background: rgba(40, 167, 69, 0.1); 
        border: 1px solid #28a745; 
        color: #28a745; 
    }
    
    .alert.error { 
        background: rgba(225, 6, 0, 0.1); 
        border: 1px solid #e10600; 
        color: #ff4a4a; 
    }
    
    @keyframes popIn { 
        from { opacity: 0; transform: scale(0.9); } 
        to { opacity: 1; transform: scale(1); } 
    }

    /* --- RESPONSIVE SHOP STYLES --- */
    @media (max-width: 992px) {
        .shop-container { margin: 30px auto; }
        
        .shop-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
    }

    @media (max-width: 768px) {
        .shop-container { 
            margin: 20px auto; 
            padding: 0 12px;
        }
        
        .wallet-banner { 
            flex-direction: column; 
            text-align: center; 
            gap: 20px; 
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .wallet-left h1 { font-size: 1.5rem; }
        .wallet-left p { font-size: 0.85rem; }
        
        .wallet-points { 
            font-size: 2.5rem; 
            justify-content: center;
        }
        
        .wallet-points i { font-size: 2rem; }
        
        .shop-grid {
            grid-template-columns: 1fr;
            gap: 20px;
        }
        
        .product-card { padding: 25px 15px; }
        .product-icon { font-size: 3rem; }
        .product-title { font-size: 1.2rem; }
        .product-desc { font-size: 0.85rem; }
        .product-price { font-size: 1.3rem; }
        .btn-buy { font-size: 1rem; padding: 10px; }
        
        .alert { 
            padding: 15px; 
            font-size: 1rem; 
            margin-bottom: 20px;
        }
    }

    @media (max-width: 576px) {
        .shop-container { 
            margin: 15px auto; 
            padding: 0 10px;
        }
        
        .wallet-banner { 
            padding: 15px; 
            margin-bottom: 25px;
        }
        
        .wallet-left h1 { font-size: 1.3rem; }
        .wallet-left p { font-size: 0.8rem; }
        .wallet-points { font-size: 2rem; gap: 10px; }
        .wallet-points i { font-size: 1.5rem; }
        
        .product-card { padding: 20px 12px; }
        .product-icon { font-size: 2.5rem; margin-bottom: 15px; }
        .product-title { font-size: 1.1rem; }
        .product-desc { font-size: 0.8rem; margin-bottom: 20px; }
        .product-price { font-size: 1.2rem; padding: 8px; margin-bottom: 15px; }
        .btn-buy { font-size: 0.9rem; padding: 10px; }
    }

    @media (max-width: 480px) {
        .shop-container { 
            margin: 12px auto; 
            padding: 0 8px;
        }
        
        .wallet-banner { 
            padding: 12px; 
            margin-bottom: 20px;
            border-radius: 12px;
        }
        
        .wallet-left h1 { font-size: 1.2rem; }
        .wallet-left p { font-size: 0.75rem; }
        .wallet-points { font-size: 1.8rem; }
        
        .product-card { padding: 18px 10px; }
        .product-icon { font-size: 2.2rem; }
        .product-title { font-size: 1rem; }
        .product-desc { font-size: 0.75rem; }
        .product-price { font-size: 1.1rem; }
    }

    @media (max-width: 360px) {
        .wallet-left h1 { font-size: 1.1rem; }
        .wallet-points { font-size: 1.5rem; }
        .product-card { padding: 15px 8px; }
        .product-title { font-size: 0.95rem; }
        .btn-buy { font-size: 0.85rem; padding: 8px; }
    }

    /* Touch optimizations */
    .btn-buy.active, .welcome, nav a {
        -webkit-tap-highlight-color: transparent;
    }
    
    @media (hover: none) {
        .product-card:hover { transform: none; }
        .btn-buy.active:hover { transform: none; }
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
        <a href="/f1fanclub/pitwall/pitwall.php"><i class="fas fa-trophy" style="margin-right: 5px;"></i> A Fal</a>
    </nav>
    
    <div class="dropdown-container" id="userDropdownContainer">
        <div class="auth">
            <div class="welcome" style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                <img src="/f1fanclub/uploads/<?= htmlspecialchars($profile_image); ?>" 
                     class="avatar clickable-user"
                     alt="Profilkép" 
                     onclick="openUserProfile('<?= htmlspecialchars(addslashes($username)); ?>')"
                     style="width:35px; height:35px; border-radius:50%; object-fit: cover; border: 2px solid <?= htmlspecialchars($teamColor); ?>;">
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

<div class="shop-container">
    
    <?php if($message): ?>
        <div class="alert <?= $msgType ?>">
            <i class="fas <?= $msgType == 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle' ?>"></i> 
            <?= $message ?>
        </div>
    <?php endif; ?>

    <div class="wallet-banner">
        <div class="wallet-left">
            <h1>The Pitwall Shop</h1>
            <p>Váltsd be a futamok során szerzett pontjaidat exkluzív nyereményekre!</p>
            <a href="pitwall.php" style="color: #d4af37; text-decoration: none; display: inline-block; margin-top: 10px; font-size: 0.9rem;">
                <i class="fas fa-arrow-left"></i> Vissza a tippeléshez
            </a>
        </div>
        <div class="wallet-points">
            <?= number_format($userPoints) ?> <span>PONT</span>
            <i class="fas fa-wallet" style="color: #555;"></i>
        </div>
    </div>

    <div class="shop-grid">
        <?php foreach($rewards as $id => $reward): ?>
            <?php $canAfford = ($userPoints >= $reward['points']); ?>
            <div class="product-card">
                <i class="fas <?= $reward['icon'] ?> product-icon"></i>
                <h3 class="product-title"><?= $reward['title'] ?></h3>
                <p class="product-desc"><?= $reward['desc'] ?></p>
                
                <div class="product-price">
                    Ár: <span><?= $reward['points'] ?> PONT</span>
                </div>
                
                <form method="POST" onsubmit="return confirm('Biztosan beváltod a pontjaidat erre a nyereményre?');">
                    <input type="hidden" name="reward_id" value="<?= $id ?>">
                    <?php if($canAfford): ?>
                        <button type="submit" class="btn-buy active">
                            <i class="fas fa-shopping-cart"></i> Beváltom
                        </button>
                    <?php else: ?>
                        <button type="button" class="btn-buy disabled" disabled>
                            <i class="fas fa-lock"></i> Nincs elég pontod
                        </button>
                    <?php endif; ?>
                </form>
            </div>
        <?php endforeach; ?>
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

    // Dropdown menu toggle functionality
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

    // User profile function
    function openUserProfile(username) {
        window.location.href = '/f1fanclub/profile/profile.php';
    }
</script>

</body>
</html>