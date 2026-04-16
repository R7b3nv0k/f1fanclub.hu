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

/* === Csapatszín függvény === */
function getTeamColor($team)
{
  switch ($team) {
    case 'Red Bull':
      return '#1E41FF';
    case 'Ferrari':
      return '#DC0000';
    case 'Mercedes':
      return '#00D2BE';
    case 'McLaren':
      return '#FF8700';
    case 'Aston Martin':
      return '#006F62';
    case 'Alpine':
      return '#0090FF';
    case 'Williams':
      return '#00A0DE';
    case 'RB':
      return '#2b2bff';
    case 'Audi':
      return '#e3000f';
    case 'Haas F1 Team':
      return '#B6BABD';
    case 'Cadillac':
      return '#B6BABD';
    default:
      return '#ffffff';
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

// Zászló URL-ek a Wikipedia-ról (nemzetiség alapján)
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

// Csapat CSS osztály térkép
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
  <title>Versenyzők – F1 Fan Club</title>
  <link rel="stylesheet" href="/f1fanclub/css/style.css">
  <link rel="stylesheet" href="drivers_style.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="icon" type="image/svg+xml" href="https://upload.wikimedia.org/wikipedia/commons/3/33/F1.svg">
  <style>
    .flag-img {
        width: 24px;
        height: 16px;
        vertical-align: middle;
        margin-right: 5px;
        border-radius: 2px;
    }
  </style>
  <style>
    /* =========================================
       FIX FOR STATISTICS PANEL HEIGHT AND TABS
       ========================================= */
    
    /* Override global body padding for drivers page */
    body {
        padding-top: 80px !important;
        margin: 0 !important;
        height: 100vh !important;
        overflow: hidden !important;
    }
    
    #drivers {
        width: 100%;
        height: calc(100vh - 80px) !important;
        max-height: calc(100vh - 80px) !important;
        display: flex;
        flex-direction: column;
        position: relative;
        margin: 0;
        padding: 0;
        overflow: hidden !important;
    }
    
    .drivers-container {
        width: 100%;
        height: 100% !important;
        max-height: 100% !important;
        overflow-x: auto;
        overflow-y: hidden;
        background: #0a0a0a;
        position: relative;
        display: flex;
        align-items: flex-end;
        margin: 0;
        padding: 0;
        flex: 1 !important;
    }
    
    .drivers-wrapper {
        display: flex;
        height: 100% !important;
        align-items: flex-end;
        padding-left: 50px;
        padding-right: 50px;
        gap: 0;
        width: max-content;
        margin: 0;
    }
    
    /* Fix statistics panel height to account for header */
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
        transition: right 0.4s cubic-bezier(0.2, 0.8, 0.2, 1);
        overflow-y: hidden;
        overflow-x: hidden;
        box-shadow: -10px 0 50px rgba(0, 0, 0, 0.8),
                    -5px 0 30px rgba(225, 6, 0, 0.3);
        display: flex;
        flex-direction: column;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    
    .statistics-panel.active {
        right: 0;
    }
    
    /* Fix statistics header */
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
    
    /* Fix stats toggle */
    .stats-toggle {
        display: flex;
        padding: 12px 25px;
        background: rgba(15, 15, 15, 0.9);
        border-bottom: 1px solid rgba(225, 6, 0, 0.2);
        gap: 8px;
        flex-shrink: 0;
    }
    
    /* Fix statistics content scrolling */
    .statistics-content {
        padding: 20px 25px;
        flex: 1;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        min-height: 0;
    }
    
    /* Fix stats grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
        flex: 1;
        min-height: 0;
    }
    
    /* Fix stat items */
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
    
    /* Fix for driver container when panel is active */
    .drivers-container.panel-active {
        width: calc(100% - 480px) !important;
        transition: width 0.4s cubic-bezier(0.2, 0.8, 0.2, 1);
    }
    
    /* Fix for scroll buttons when panel is active */
    .scroll-button.right.panel-active {
        right: 480px !important;
    }
    
    /* Fix scroll buttons position */
    .scroll-button {
        position: fixed;
        top: calc(50% + 40px);
        transform: translateY(-50%);
        z-index: 1000;
    }
    
    /* Fix for auth buttons - rounded corners */
    .auth .btn,
    .welcome,
    .btn {
        border-radius: 30px !important;
    }
    
    .welcome .avatar {
        border-radius: 50% !important;
    }
    
    /* Driver card adjustments */
    .driver-card {
        height: 85vh;
        max-height: 85vh;
    }
    
    .driver-image {
        max-height: 90%;
    }

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
  </style>
</head>

<body>

  <header>
    <div class="left-header">
      <h1 class="logo-title">
        <img src="https://upload.wikimedia.org/wikipedia/commons/3/33/F1.svg" class="f1-logo" alt="F1 Logo"
          style="height: 40px; vertical-align: middle;">
        <span>Fan Club</span>
      </h1>
    </div>
    <nav style="margin: 20px 0;">
      <a href="/f1fanclub/index.php" style="color:white; margin:0 10px;">Kezdőlap</a>
      <a href="/f1fanclub/Championship/championship.php" style="color:white; margin:0 10px;">Bajnokság</a>
      <a href="/f1fanclub/teams/teams.php" style="color:white; margin:0 10px;">Csapatok</a>
      <a href="/f1fanclub/drivers/drivers.php" style="color:#e10600; margin:0 10px; font-weight:bold;">Versenyzők</a>
      <a href="/f1fanclub/news/feed.php" style="color:white; margin:0 10px;">Paddock</a>
      <a href="/f1fanclub/pitwall/pitwall.php" style="color:white; margin:0 10px;"><i class="fas fa-trophy" style="margin-right: 5px;"></i> A Fal</a>
    </nav>
    
    <?php if ($isLoggedIn): ?>
      <div class="dropdown-container" id="userDropdownContainer">
        <div class="auth">
          <div class="welcome" style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
            <?php if ($profile_image): ?>
              <img src="/f1fanclub/uploads/<?php echo htmlspecialchars($profile_image); ?>" class="avatar clickable-user" alt="Profilkép"
                style="width:35px; height:35px; border-radius:50%; object-fit: cover; border-color: <?php echo htmlspecialchars($teamColor); ?>;"
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
        <button class="toggle-btn active" data-period="current"><span class="toggle-text">IDEI SZEZON</span><span
            class="toggle-glow"></span></button>
        <button class="toggle-btn" data-period="career"><span class="toggle-text">KARRIER</span><span
            class="toggle-glow"></span></button>
      </div>

      <div class="statistics-content">
        <div class="stats-grid" id="current-stats">
          <div class="stat-item"><span class="stat-label">HELYEZÉS</span><span class="stat-value"
              id="current-position"></span>
            <div class="stat-glow"></div>
          </div>
          <div class="stat-item"><span class="stat-label">PONTOK</span><span class="stat-value"
              id="current-points"></span>
            <div class="stat-glow"></div>
          </div>
          <div class="stat-item"><span class="stat-label">GYŐZELMEK</span><span class="stat-value" id="current-wins"></span>
            <div class="stat-glow"></div>
          </div>
          <div class="stat-item"><span class="stat-label">DOBOGÓK</span><span class="stat-value"
              id="current-podiums"></span>
            <div class="stat-glow"></div>
          </div>
          <div class="stat-item"><span class="stat-label">POLE POZÍCIÓK</span><span class="stat-value"
              id="current-poles"></span>
            <div class="stat-glow"></div>
          </div>
          <div class="stat-item"><span class="stat-label">LEGGYORSABB KÖRÖK</span><span class="stat-value"
              id="current-fastest-laps"></span>
            <div class="stat-glow"></div>
          </div>
        </div>

        <div class="stats-grid" id="career-stats" style="display: none;">
          <div class="stat-item"><span class="stat-label">NAGDÍJAK</span><span class="stat-value"
              id="career-races"></span>
            <div class="stat-glow"></div>
          </div>
          <div class="stat-item"><span class="stat-label">GYŐZELMEK</span><span class="stat-value" id="career-wins"></span>
            <div class="stat-glow"></div>
          </div>
          <div class="stat-item"><span class="stat-label">DOBOGÓK</span><span class="stat-value"
              id="career-podiums"></span>
            <div class="stat-glow"></div>
          </div>
          <div class="stat-item"><span class="stat-label">POLE POZÍCIÓK</span><span class="stat-value" id="career-poles"></span>
            <div class="stat-glow"></div>
          </div>
          <div class="stat-item"><span class="stat-label">LEGGYORSABB KÖRÖK</span><span class="stat-value"
              id="career-fastest-laps"></span>
            <div class="stat-glow"></div>
          </div>
          <div class="stat-item"><span class="stat-label">VILÁGBAJNOKI CÍMEK</span><span class="stat-value"
              id="career-titles"></span>
            <div class="stat-glow"></div>
          </div>
        </div>
      </div>
    </div>

    <div class="scroll-button left" id="scroll-left">
      <div class="accent-line"></div>
    </div>
    <div class="scroll-button right" id="scroll-right">
      <div class="accent-line"></div>
    </div>

    <div class="drivers-container">
      <div class="drivers-wrapper" id="drivers-wrapper">

        <?php while ($driver = $result->fetch_assoc()):

          $keyId = $driver['abbreviation'];
          $teamName = $driver['team_name'] ?? 'Ismeretlen Csapat';
          $cssClass = $teamCssMap[$teamName] ?? 'redbull';
          $nationalityCode = $driver['nationality'];
          $flagImageUrl = $flagImageMap[$nationalityCode] ?? 'https://upload.wikimedia.org/wikipedia/commons/3/32/Flag_of_unknown.svg';
          $nationalityName = strtoupper($driver['nationality']);

          // Kép elérési út javítása
          $imgSrc = $driver['image'];
          if (strpos($imgSrc, 'kép/') === false && strpos($imgSrc, 'drivers/') === 0) {
            $imgSrc = 'kép/' . str_replace('drivers/', '', $imgSrc);
          }

          // Feltöltjük a PHP tömböt a JS számára
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
              <img src="<?= htmlspecialchars($imgSrc) ?>" alt="<?= htmlspecialchars($driver['name']) ?>"
                class="driver-image" loading="lazy">
              <div class="team-logo"><?= htmlspecialchars($teamName) ?></div>
              <div class="driver-glow"></div>
            </div>
            <div class="driver-info">
              <div class="nametag" id="<?= $keyId ?>_nametag">
                <h2 class="driver-name"><?= htmlspecialchars($driver['name']) ?></h2>
                <div class="driver-team-line" style="color: <?= getTeamColor($teamName) ?>;"><?= strtoupper(htmlspecialchars($teamName)) ?></div>
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
    window.driverStatsFromDB = <?= json_encode($driversData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT); ?>;
  </script>

  <script src="drivers_script.js"></script>
  <script>
    // =========================================
    // LEGÖRDÜLŐ MENÜ FUNKCIÓK
    // =========================================
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

    // =========================================
    // FELHASZNÁLÓI PROFIL MODÁLIS FUNKCIÓK
    // =========================================
    let currentModalUser = "";
    let currentFriendStatus = "";

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
                                <button id="modalFriendBtn" class="btn-add-friend" onclick="handleFriendAction()">
                                    <i class="fas fa-user-plus"></i> Barátnak jelölés
                                </button>
                                <button class="btn-send-msg" onclick="window.location.href='/f1fanclub/messages/messages.php'">
                                    <i class="fas fa-comment"></i> Üzenet küldése
                                </button>
                            </div>
                        </div>
                    `;
                    document.body.appendChild(modal);
                }

                const modalImg = document.getElementById('modalProfileImg');
                if (modalImg) {
                    modalImg.src = data.user.profile_image;
                    modalImg.style.borderColor = data.user.team_color;
                }
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
        const modal = document.getElementById('userProfileModal');
        if (modal) modal.style.display = 'none';
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

    // =========================================
    // VERSENYZŐ STATISZTIKAI PANEL
    // =========================================
    document.addEventListener('DOMContentLoaded', function() {
        // Összes versenyzőkártya lekérése
        const driverCards = document.querySelectorAll('.driver-card');
        const statsPanel = document.getElementById('statistics-panel');
        const closePanelBtn = document.getElementById('close-panel');
        const driversContainer = document.querySelector('.drivers-container');
        const scrollRightBtn = document.getElementById('scroll-right');
        
        // Statisztikai panel elemek
        const statsDriverImage = document.getElementById('stats-driver-image');
        const statsDriverName = document.getElementById('stats-driver-name');
        const statsDriverTeam = document.getElementById('stats-driver-team');
        const statsFlag = document.getElementById('stats-flag');
        const statsNationality = document.getElementById('stats-nationality');
        
        // Statisztika érték elemek
        const currentPosition = document.getElementById('current-position');
        const currentPoints = document.getElementById('current-points');
        const currentWins = document.getElementById('current-wins');
        const currentPodiums = document.getElementById('current-podiums');
        const currentPoles = document.getElementById('current-poles');
        const currentFastestLaps = document.getElementById('current-fastest-laps');
        
        const careerRaces = document.getElementById('career-races');
        const careerWins = document.getElementById('career-wins');
        const careerPodiums = document.getElementById('career-podiums');
        const careerPoles = document.getElementById('career-poles');
        const careerFastestLaps = document.getElementById('career-fastest-laps');
        const careerTitles = document.getElementById('career-titles');
        
        // Toggle funkciók
        const toggleBtns = document.querySelectorAll('.toggle-btn');
        const currentStatsGrid = document.getElementById('current-stats');
        const careerStatsGrid = document.getElementById('career-stats');
        
        // Funkció a versenyző statisztikák frissítéséhez
        function updateStatsPanel(driverCard) {
            const driverId = driverCard.getAttribute('id');
            const driverData = window.driverStatsFromDB[driverId];
            
            if (!driverData) {
                console.error('Nincs adat a versenyzőhöz:', driverId);
                return;
            }
            
            // Kép frissítése
            const driverImg = driverCard.querySelector('.driver-image');
            if (statsDriverImage && driverImg) {
                statsDriverImage.src = driverImg.src;
                statsDriverImage.alt = driverData.name;
                statsDriverImage.style.display = 'block';
            }
            
            // Név és csapat frissítése
            if (statsDriverName) statsDriverName.textContent = driverData.name;
            if (statsDriverTeam) statsDriverTeam.textContent = driverData.team;
            
            // Zászló és nemzetiség frissítése
            if (statsFlag) {
                statsFlag.innerHTML = `<img src="${driverData.flag}" class="flag-img" alt="${driverData.nationality}" style="width:24px; height:16px; vertical-align:middle; margin-right:5px;">`;
            }
            if (statsNationality) statsNationality.textContent = driverData.nationality;
            
            // Jelenlegi szezon statisztikák frissítése
            if (currentPosition) currentPosition.textContent = driverData.current.position;
            if (currentPoints) currentPoints.textContent = driverData.current.points;
            if (currentWins) currentWins.textContent = driverData.current.wins;
            if (currentPodiums) currentPodiums.textContent = driverData.current.podiums;
            if (currentPoles) currentPoles.textContent = driverData.current.poles;
            if (currentFastestLaps) currentFastestLaps.textContent = driverData.current.fastestLaps;
            
            // Karrier statisztikák frissítése
            if (careerRaces) careerRaces.textContent = driverData.career.races;
            if (careerWins) careerWins.textContent = driverData.career.wins;
            if (careerPodiums) careerPodiums.textContent = driverData.career.podiums;
            if (careerPoles) careerPoles.textContent = driverData.career.poles;
            if (careerFastestLaps) careerFastestLaps.textContent = driverData.career.fastestLaps;
            if (careerTitles) careerTitles.textContent = driverData.career.titles;
        }
        
        // Kattintás esemény hozzáadása minden versenyzőkártyához
        driverCards.forEach(card => {
            card.addEventListener('click', function(e) {
                // Statisztikai panel frissítése a kattintott versenyző adataival
                updateStatsPanel(this);
                
                // Statisztikai panel megnyitása
                statsPanel.classList.add('active');
                driversContainer.classList.add('panel-active');
                if (scrollRightBtn) scrollRightBtn.classList.add('panel-active');
            });
        });
        
        // Panel bezárás funkció
        if (closePanelBtn) {
            closePanelBtn.addEventListener('click', function() {
                statsPanel.classList.remove('active');
                driversContainer.classList.remove('panel-active');
                if (scrollRightBtn) scrollRightBtn.classList.remove('panel-active');
            });
        }
        
        // Toggle gombok kezelése
        toggleBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const period = this.getAttribute('data-period');
                
                // Aktív gomb stílus frissítése
                toggleBtns.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                // Megfelelő statisztikai rács mutatása
                if (period === 'current') {
                    currentStatsGrid.style.display = 'grid';
                    careerStatsGrid.style.display = 'none';
                } else {
                    currentStatsGrid.style.display = 'none';
                    careerStatsGrid.style.display = 'grid';
                }
            });
        });
    });

    // =========================================
    // TOVÁBBI MODÁLIS STÍLUSOK
    // =========================================
    (function addModalStyles() {
        if (document.getElementById('modal-styles')) return;
        const style = document.createElement('style');
        style.id = 'modal-styles';
        style.textContent = `
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
            }
            .clickable-user:hover {
                opacity: 0.8;
            }
        `;
        document.head.appendChild(style);
    })();
  </script>
</body>

</html>