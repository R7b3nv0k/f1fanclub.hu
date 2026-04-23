<?php
session_start();

/**
 * ============================================================================
 * DATABASE CONNECTION
 * ============================================================================
 */
$DB_HOST = "localhost";
$DB_USER = "swmjndga_swmjndga";
$DB_PASS = "Teszt1234!";
$DB_NAME = "swmjndga_f1adat";

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) {
  die("Adatbázis hiba: " . $conn->connect_error);
}

/**
 * ============================================================================
 * SESSION DETAILS & USER AUTHENTICATION
 * ============================================================================
 */
$isLoggedIn = isset($_SESSION['username']);
$username = $isLoggedIn ? $_SESSION['username'] : null;
$userRole = isset($_SESSION['role']) ? $_SESSION['role'] : 'user';

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
?>
<!DOCTYPE html>
<html lang="hu">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes, maximum-scale=1.0, viewport-fit=cover">
  <title>Csapatok – F1 Fan Club</title>
  <link rel="icon" type="image/svg+xml" href="https://upload.wikimedia.org/wikipedia/commons/3/33/F1.svg">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="team_style.css">
  
  <style>
    /* Header and mobile responsive overrides */
    body {
        padding-top: 80px;
        margin: 0;
        overflow-x: hidden;
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

    /* Mobile responsive overrides - keeping team_style.css animations */
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
        body { overflow-y: auto; -webkit-overflow-scrolling: touch; }
        #teams { height: auto; min-height: calc(100vh - 80px); overflow: visible; }
        .teams-container { height: auto; overflow: visible; display: block; }
        .teams-wrapper {
            flex-direction: column;
            align-items: center;
            width: 100%;
            height: auto;
            padding: 20px 10px;
            gap: 20px;
        }
        .team-card {
            width: 90% !important;
            max-width: 350px !important;
            height: 400px !important;
            transform: scale(1) !important;
            filter: grayscale(0%) brightness(1) !important;
            margin: 0 auto !important;
        }
        .team-card:hover { transform: scale(1) !important; width: 90% !important; }
        .team-card.selected { transform: scale(1) !important; width: 90% !important; }
        
        .team-image {
            max-height: 65% !important;
            height: auto !important;
            width: 100% !important;
            object-fit: contain !important;
            object-position: center !important;
            transform: translate(-50%, -50%) !important;
            top: 50% !important;
            left: 50% !important;
            filter: grayscale(0%) brightness(1) !important;
        }
        .team-card:hover .team-image,
        .team-card.selected .team-image {
            transform: translate(-50%, -50%) scale(1.05) !important;
        }
        
        .team-image-container { overflow: hidden !important; align-items: center !important; }
        .team-info { transform: translateY(0) !important; background: linear-gradient(to top, rgba(0,0,0,0.9) 0%, rgba(0,0,0,0.4) 70%, transparent 100%) !important; }
        .nametag { background: transparent !important; padding: 20px 15px 15px !important; }
        .team-name { font-size: 1.4rem !important; }
        .team-principal { font-size: 0.9rem !important; }
        .team-name-logo { display: none !important; }
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
        .statistics-panel.active { right: 0 !important; bottom: 0 !important; }
        .teams-container.panel-active { width: 100% !important; }
        .statistics-header { padding: 15px 20px !important; min-height: 100px !important; }
        .stats-image-container { width: 70px !important; height: 70px !important; }
        .stats-team-info h2 { font-size: 1.2rem !important; }
        .stat-item { padding: 12px 8px !important; min-height: 80px !important; }
        .stat-value { font-size: 1.5rem !important; }
        
        .auth { flex-wrap: wrap; justify-content: flex-end; }
        .welcome { width: 100%; justify-content: center; margin-right: 0; margin-bottom: 5px; }
    }

    @media (max-width: 480px) {
        .team-card { max-width: 320px !important; height: 380px !important; }
        .team-name { font-size: 1.3rem !important; }
        .team-principal { font-size: 0.85rem !important; }
        .nametag { padding: 18px 12px 12px !important; }
        .statistics-panel { height: 85vh !important; max-height: 85vh !important; }
        .stats-image-container { width: 60px !important; height: 60px !important; }
        .stats-team-info h2 { font-size: 1rem !important; }
        .stat-value { font-size: 1.3rem !important; }
    }

    @media (max-width: 360px) {
        .team-card { max-width: 280px !important; height: 350px !important; }
        .team-name { font-size: 1.2rem !important; }
        .team-principal { font-size: 0.8rem !important; }
    }

    @media (max-width: 576px) {
        .hamburger { font-size: 24px; padding: 8px; }
        header { padding: 0 15px; }
        .auth .btn { padding: 6px 12px; font-size: 0.7rem; }
        nav a { padding: 12px 15px; font-size: 0.85rem; }
    }

    .clickable-user { cursor: pointer; }
    .clickable-user:hover { opacity: 0.8; }
    .flag-img { width: 24px; height: 16px; vertical-align: middle; margin-right: 5px; border-radius: 2px; }
    
        /* Fix bottom gap */
    #teams {
        height: calc(100vh - 80px) !important;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }
    
    .teams-container {
        height: 100% !important;
        flex: 1;
    }
    
    .teams-wrapper {
        height: 100% !important;
    }
    
    body {
        overflow: hidden;
    }
    
    /* Mobile override for body overflow */
    @media (max-width: 768px) {
        body {
            overflow-y: auto !important;
        }
        
        #teams {
            height: auto !important;
            min-height: calc(100vh - 80px) !important;
            overflow: visible !important;
        }
        
        .teams-container {
            height: auto !important;
        }
        
        .teams-wrapper {
            height: auto !important;
        }
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
      <a href="/f1fanclub/teams/teams.php" class="active">Csapatok</a>
      <a href="/f1fanclub/drivers/drivers.php">Versenyzők</a>
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
  
  <section id="teams">

    <div class="statistics-panel" id="statistics-panel">
      <button class="close-panel" id="close-panel">×</button>
      <div class="statistics-header">
        <div class="stats-image-container">
          <img id="stats-team-image" src="" alt="Csapat logó" class="stats-team-image">
          <div class="glow-effect"></div>
        </div>
        <div class="stats-team-info">
          <h2 id="stats-team-name">CSAPAT NÉV</h2>
          <p id="stats-team-base">SZÉKHELY</p>
          <div class="stats-team-nationality">
            <span id="stats-flag"></span>
            <span id="stats-country">ORSZÁG</span>
          </div>
        </div>
      </div>

      <div class="stats-toggle">
        <button class="toggle-btn active" data-period="current">
          <span class="toggle-text">2025-ÖS SZEZON</span>
          <span class="toggle-glow"></span>
        </button>
        <button class="toggle-btn" data-period="career">
          <span class="toggle-text">CSAPAT TÖRTÉNETE</span>
          <span class="toggle-glow"></span>
        </button>
      </div>

      <div class="statistics-content">
        <div class="stats-grid" id="current-stats">
          <div class="stat-item"><span class="stat-label">HELYEZÉS</span><span class="stat-value" id="current-position">1.</span><div class="stat-glow"></div></div>
          <div class="stat-item"><span class="stat-label">PONTOK</span><span class="stat-value" id="current-points">654</span><div class="stat-glow"></div></div>
          <div class="stat-item"><span class="stat-label">GYŐZELMEK</span><span class="stat-value" id="current-wins">21</span><div class="stat-glow"></div></div>
          <div class="stat-item"><span class="stat-label">DOBOGÓK</span><span class="stat-value" id="current-podiums">32</span><div class="stat-glow"></div></div>
          <div class="stat-item"><span class="stat-label">POLE POZÍCIÓK</span><span class="stat-value" id="current-poles">14</span><div class="stat-glow"></div></div>
          <div class="stat-item"><span class="stat-label">LEGGYORSABB KÖRÖK</span><span class="stat-value" id="current-fastest-laps">12</span><div class="stat-glow"></div></div>
        </div>

        <div class="stats-grid" id="career-stats" style="display: none;">
          <div class="stat-item"><span class="stat-label">NAGYDÍJAK</span><span class="stat-value" id="career-races">385</span><div class="stat-glow"></div></div>
          <div class="stat-item"><span class="stat-label">GYŐZELMEK</span><span class="stat-value" id="career-wins">124</span><div class="stat-glow"></div></div>
          <div class="stat-item"><span class="stat-label">DOBOGÓK</span><span class="stat-value" id="career-podiums">298</span><div class="stat-glow"></div></div>
          <div class="stat-item"><span class="stat-label">POLE POZÍCIÓK</span><span class="stat-value" id="career-poles">133</span><div class="stat-glow"></div></div>
          <div class="stat-item"><span class="stat-label">LEGGYORSABB KÖRÖK</span><span class="stat-value" id="career-fastest-laps">128</span><div class="stat-glow"></div></div>
          <div class="stat-item"><span class="stat-label">KONSTRUKTŐRI CÍMEK</span><span class="stat-value" id="career-titles">8</span><div class="stat-glow"></div></div>
        </div>
      </div>
    </div>

    <div class="scroll-button left" id="scroll-left"><div class="accent-line"></div></div>
    <div class="scroll-button right" id="scroll-right"><div class="accent-line"></div></div>

    <div class="teams-container">
      <div class="teams-wrapper" id="teams-wrapper">
        <!-- Red Bull -->
        <div class="team-card" data-team="redbull" id="RBR" data-team-id="red_bull" data-team-logo="red bull-logo-Photoroom.png">
          <div class="team-image-container">
            <img src="red bull-logo-Photoroom.png" alt="Red Bull Racing" class="team-image">
            <div class="team-name-logo">Red Bull Racing</div>
            <div class="team-glow"></div>
          </div>
          <div class="team-info">
            <div class="nametag" id="RBR_nametag">
              <h2 class="team-name">RED BULL RACING</h2>
              <p class="team-principal">Christian Horner</p>
              <div class="team-details">
                <span class="flag"><img src="https://upload.wikimedia.org/wikipedia/commons/4/41/Flag_of_Austria.svg" class="flag-img" alt="Ausztria"></span>
                MILTON KEYNES
              </div>
            </div>
          </div>
        </div>

        <!-- Ferrari -->
        <div class="team-card" data-team="ferrari" id="FER" data-team-id="ferrari" data-team-logo="ferrari-logo-removebg-preview.png">
          <div class="team-image-container">
            <img src="ferrari-logo-removebg-preview.png" alt="Ferrari" class="team-image">
            <div class="team-name-logo">Ferrari</div>
            <div class="team-glow"></div>
          </div>
          <div class="team-info">
            <div class="nametag" id="FER_nametag">
              <h2 class="team-name">SCUDERIA FERRARI</h2>
              <p class="team-principal">Frédéric Vasseur</p>
              <div class="team-details">
                <span class="flag"><img src="https://upload.wikimedia.org/wikipedia/en/0/03/Flag_of_Italy.svg" class="flag-img" alt="Olaszország"></span>
                MARANELLO
              </div>
            </div>
          </div>
        </div>

        <!-- Mercedes -->
        <div class="team-card" data-team="mercedes" id="MER" data-team-id="mercedes" data-team-logo="mercedes-logo.png">
          <div class="team-image-container">
            <img src="mercedes-logo.png" alt="Mercedes" class="team-image">
            <div class="team-name-logo">Mercedes</div>
            <div class="team-glow"></div>
          </div>
          <div class="team-info">
            <div class="nametag" id="MER_nametag">
              <h2 class="team-name">MERCEDES-AMG</h2>
              <p class="team-principal">Toto Wolff</p>
              <div class="team-details">
                <span class="flag"><img src="https://upload.wikimedia.org/wikipedia/en/b/ba/Flag_of_Germany.svg" class="flag-img" alt="Németország"></span>
                BRACKLEY
              </div>
            </div>
          </div>
        </div>

        <!-- McLaren -->
        <div class="team-card" data-team="mclaren" id="MCL" data-team-id="mclaren" data-team-logo="mclaren-logo.png">
          <div class="team-image-container">
            <img src="mclaren-logo.png" alt="McLaren" class="team-image">
            <div class="team-name-logo">McLaren</div>
            <div class="team-glow"></div>
          </div>
          <div class="team-info">
            <div class="nametag" id="MCL_nametag">
              <h2 class="team-name">McLAREN F1 TEAM</h2>
              <p class="team-principal">Andrea Stella</p>
              <div class="team-details">
                <span class="flag"><img src="https://upload.wikimedia.org/wikipedia/en/a/ae/Flag_of_the_United_Kingdom.svg" class="flag-img" alt="Egyesült Királyság"></span>
                WOKING
              </div>
            </div>
          </div>
        </div>

        <!-- Aston Martin -->
        <div class="team-card" data-team="astonmartin" id="AST" data-team-id="aston_martin" data-team-logo="Aston-Martin-Logo.png">
          <div class="team-image-container">
            <img src="Aston-Martin-Logo.png" alt="Aston Martin" class="team-image">
            <div class="team-name-logo">Aston Martin</div>
            <div class="team-glow"></div>
          </div>
          <div class="team-info">
            <div class="nametag" id="AST_nametag">
              <h2 class="team-name">ASTON MARTIN</h2>
              <p class="team-principal">Mike Krack</p>
              <div class="team-details">
                <span class="flag"><img src="https://upload.wikimedia.org/wikipedia/en/a/ae/Flag_of_the_United_Kingdom.svg" class="flag-img" alt="Egyesült Királyság"></span>
                SILVERSTONE
              </div>
            </div>
          </div>
        </div>

        <!-- Alpine -->
        <div class="team-card" data-team="alpine" id="ALP" data-team-id="alpine" data-team-logo="alpine-logo-Photoroom.png">
          <div class="team-image-container">
            <img src="alpine-logo-Photoroom.png" alt="Alpine" class="team-image">
            <div class="team-name-logo">Alpine</div>
            <div class="team-glow"></div>
          </div>
          <div class="team-info">
            <div class="nametag" id="ALP_nametag">
              <h2 class="team-name">ALPINE F1 TEAM</h2>
              <p class="team-principal">Oliver Oakes</p>
              <div class="team-details">
                <span class="flag"><img src="https://upload.wikimedia.org/wikipedia/en/c/c3/Flag_of_France.svg" class="flag-img" alt="Franciaország"></span>
                ENSTONE
              </div>
            </div>
          </div>
        </div>

        <!-- Williams -->
        <div class="team-card" data-team="williams" id="WIL" data-team-id="williams" data-team-logo="williams-logo-Photoroom.png">
          <div class="team-image-container">
            <img src="williams-logo-Photoroom.png" alt="Williams" class="team-image">
            <div class="team-name-logo">Williams</div>
            <div class="team-glow"></div>
          </div>
          <div class="team-info">
            <div class="nametag" id="WIL_nametag">
              <h2 class="team-name">WILLIAMS RACING</h2>
              <p class="team-principal">James Vowles</p>
              <div class="team-details">
                <span class="flag"><img src="https://upload.wikimedia.org/wikipedia/en/a/ae/Flag_of_the_United_Kingdom.svg" class="flag-img" alt="Egyesült Királyság"></span>
                GROVE
              </div>
            </div>
          </div>
        </div>

        <!-- Racing Bulls -->
        <div class="team-card" data-team="racingbulls" id="RB" data-team-id="racing_bulls" data-team-logo="racing_bulls-logo.png">
          <div class="team-image-container">
            <img src="racing_bulls-logo.png" alt="Racing Bulls" class="team-image">
            <div class="team-name-logo">Racing Bulls</div>
            <div class="team-glow"></div>
          </div>
          <div class="team-info">
            <div class="nametag" id="RB_nametag">
              <h2 class="team-name">RACING BULLS</h2>
              <p class="team-principal">Laurent Mekies</p>
              <div class="team-details">
                <span class="flag"><img src="https://upload.wikimedia.org/wikipedia/en/0/03/Flag_of_Italy.svg" class="flag-img" alt="Olaszország"></span>
                FAENZA
              </div>
            </div>
          </div>
        </div>

        <!-- Haas -->
        <div class="team-card" data-team="haas" id="HAA" data-team-id="haas" data-team-logo="haas-logo.png">
          <div class="team-image-container">
            <img src="haas-logo.png" alt="Haas" class="team-image">
            <div class="team-name-logo">Haas</div>
            <div class="team-glow"></div>
          </div>
          <div class="team-info">
            <div class="nametag" id="HAA_nametag">
              <h2 class="team-name">MONEYGRAM HAAS</h2>
              <p class="team-principal">Ayao Komatsu</p>
              <div class="team-details">
                <span class="flag"><img src="https://upload.wikimedia.org/wikipedia/en/a/a4/Flag_of_the_United_States.svg" class="flag-img" alt="USA"></span>
                KANNAPOLIS
              </div>
            </div>
          </div>
        </div>

        <!-- Audi -->
        <div class="team-card" data-team="audi" id="AUD" data-team-id="audi" data-team-logo="audi-white.png">
          <div class="team-image-container">
            <img src="audi-white.png" alt="Audi" class="team-image">
            <div class="team-name-logo">Audi</div>
            <div class="team-glow"></div>
          </div>
          <div class="team-info">
            <div class="nametag" id="AUD_nametag">
              <h2 class="team-name">AUDI F1 TEAM</h2>
              <p class="team-principal">Mattia Binotto</p>
              <div class="team-details">
                <span class="flag"><img src="https://upload.wikimedia.org/wikipedia/en/b/ba/Flag_of_Germany.svg" class="flag-img" alt="Németország"></span>
                HINWIL
              </div>
            </div>
          </div>
        </div>

        <!-- Cadillac -->
        <div class="team-card" data-team="cadillac" id="CAD" data-team-id="cadillac" data-team-logo="cadillac-black-logo.png">
          <div class="team-image-container">
            <img src="cadillac-black-logo.png" alt="Cadillac" class="team-image">
            <div class="team-name-logo">Cadillac</div>
            <div class="team-glow"></div>
          </div>
          <div class="team-info">
            <div class="nametag" id="CAD_nametag">
              <h2 class="team-name">CADILLAC RACING</h2>
              <p class="team-principal">Mario Andretti</p>
              <div class="team-details">
                <span class="flag"><img src="https://upload.wikimedia.org/wikipedia/en/a/a4/Flag_of_the_United_States.svg" class="flag-img" alt="USA"></span>
                CHARLOTTE
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </section>

  <script>
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
    });

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

    // Team gallery scroll
    document.addEventListener('DOMContentLoaded', function() {
        const scrollLeftBtn = document.getElementById('scroll-left');
        const scrollRightBtn = document.getElementById('scroll-right');
        const teamsContainer = document.querySelector('.teams-container');
        const scrollAmount = 840;

        if (scrollLeftBtn) {
            scrollLeftBtn.addEventListener('click', () => teamsContainer.scrollBy({ left: -scrollAmount, behavior: 'smooth' }));
        }
        if (scrollRightBtn) {
            scrollRightBtn.addEventListener('click', () => teamsContainer.scrollBy({ left: scrollAmount, behavior: 'smooth' }));
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'ArrowLeft') teamsContainer.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
            if (e.key === 'ArrowRight') teamsContainer.scrollBy({ left: scrollAmount, behavior: 'smooth' });
            if (e.key === 'Escape') closeStatisticsPanel();
        });
    });

    // Statistics panel
    document.addEventListener('DOMContentLoaded', function() {
        const teamCards = document.querySelectorAll('.team-card');
        const statsPanel = document.getElementById('statistics-panel');
        const closePanelBtn = document.getElementById('close-panel');
        const teamsContainer = document.querySelector('.teams-container');
        const scrollRightBtn = document.getElementById('scroll-right');
        const toggleBtns = document.querySelectorAll('.toggle-btn');
        
        function updateStatsPanel(teamCard) {
            const teamLogo = teamCard.getAttribute('data-team-logo') || '';
            const teamNameElem = teamCard.querySelector('.team-name');
            const teamPrincipalElem = teamCard.querySelector('.team-principal');
            const teamDetailsElem = teamCard.querySelector('.team-details');
            
            let fullTeamName = teamNameElem ? teamNameElem.textContent : 'CSAPAT NÉV';
            let teamBase = teamPrincipalElem ? teamPrincipalElem.textContent : 'SZÉKHELY';
            let flagHtml = '';
            let countryText = '';
            
            if (teamDetailsElem) {
                const flagSpan = teamDetailsElem.querySelector('.flag');
                if (flagSpan) flagHtml = flagSpan.innerHTML;
                const detailsText = teamDetailsElem.cloneNode(true);
                const flagSpanClone = detailsText.querySelector('.flag');
                if (flagSpanClone) flagSpanClone.remove();
                countryText = detailsText.textContent.trim();
            }
            
            document.getElementById('stats-team-name').textContent = fullTeamName;
            document.getElementById('stats-team-base').textContent = teamBase;
            document.getElementById('stats-flag').innerHTML = flagHtml || '🏁';
            document.getElementById('stats-country').textContent = countryText || 'ORSZÁG';
            
            const statsTeamImage = document.getElementById('stats-team-image');
            if (statsTeamImage) {
                if (teamLogo) {
                    statsTeamImage.src = teamLogo;
                    statsTeamImage.style.display = 'block';
                } else {
                    statsTeamImage.style.display = 'none';
                }
            }
        }
        
        teamCards.forEach(card => {
            card.addEventListener('click', function(e) {
                document.querySelectorAll('.team-card').forEach(c => c.classList.remove('selected'));
                this.classList.add('selected');
                updateStatsPanel(this);
                statsPanel.classList.add('active');
                teamsContainer.classList.add('panel-active');
                if (scrollRightBtn) scrollRightBtn.classList.add('panel-active');
                if (window.innerWidth > 768) document.body.style.overflow = 'hidden';
            });
        });
        
        window.closeStatisticsPanel = function() {
            document.querySelectorAll('.team-card').forEach(c => c.classList.remove('selected'));
            statsPanel.classList.remove('active');
            teamsContainer.classList.remove('panel-active');
            if (scrollRightBtn) scrollRightBtn.classList.remove('panel-active');
            document.body.style.overflow = '';
        };
        
        if (closePanelBtn) {
            closePanelBtn.addEventListener('click', (e) => { e.stopPropagation(); closeStatisticsPanel(); });
        }
        
        document.addEventListener('click', function(e) {
            if (statsPanel.classList.contains('active') && !statsPanel.contains(e.target) && !e.target.closest('.team-card') && !e.target.closest('.scroll-button')) {
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

    // User profile modal
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
                                <h3 id="modalUsername">Felhasználónév</h3>
                                <span id="modalRole" class="modal-role">Szerepkör</span>
                            </div>
                            <div class="user-modal-body">
                                <p><i class="fas fa-flag-checkered"></i> <strong>Csapat:</strong> <span id="modalTeam">Csapat</span></p>
                                <p><i class="far fa-calendar-alt"></i> <strong>Regisztrált:</strong> <span id="modalRegDate">Dátum</span></p>
                            </div>
                            <div class="user-modal-footer">
                                <button id="modalFriendBtn" class="btn-add-friend" onclick="handleFriendAction()"><i class="fas fa-user-plus"></i> Barátnak jelölés</button>
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