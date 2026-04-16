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

/**
 * ============================================================================
 * USER DATA FETCHING
 * ============================================================================
 */
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
  <title>Csapatok – F1 Fan Club</title>
  <link rel="stylesheet" href="/f1fanclub/css/style.css">
  <link rel="stylesheet" href="team_style.css">
  <link rel="icon" type="image/svg+xml" href="https://upload.wikimedia.org/wikipedia/commons/3/33/F1.svg">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
    
    /* Override global body padding for teams page */
    body {
        padding-top: 80px !important;
        min-height: 100vh;
        height: auto;
        margin: 0;
        overflow-x: hidden;
        overflow-y: hidden;
    }
    
    #teams {
        width: 100%;
        height: calc(100vh - 80px) !important;
        display: flex;
        flex-direction: column;
        position: relative;
        margin: 0;
        padding: 0;
    }
    
    .teams-container {
        width: 100%;
        height: 100% !important;
        overflow-x: auto;
        overflow-y: hidden;
        background: #0a0a0a;
        position: relative;
        display: flex;
        align-items: flex-end;
        margin: 0;
        padding: 0;
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
    
    /* Fix for team container when panel is active */
    .teams-container.panel-active {
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
      <a href="/f1fanclub/teams/teams.php" style="color:#e10600; margin:0 10px; font-weight:bold;">Csapatok</a>
      <a href="/f1fanclub/drivers/drivers.php" style="color:white; margin:0 10px;">Versenyzők</a>
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
  
  <section id="teams">

    <!-- Statisztikai Panel (alapból rejtett) -->
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

      <!-- Statisztika Váltógombok -->
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

      <!-- Statisztika Tartalom - 2x3 Rács -->
      <div class="statistics-content">
        <div class="stats-grid" id="current-stats">
          <div class="stat-item">
            <span class="stat-label">HELYEZÉS</span>
            <span class="stat-value" id="current-position">1.</span>
            <div class="stat-glow"></div>
          </div>
          <div class="stat-item">
            <span class="stat-label">PONTOK</span>
            <span class="stat-value" id="current-points">654</span>
            <div class="stat-glow"></div>
          </div>
          <div class="stat-item">
            <span class="stat-label">GYŐZELMEK</span>
            <span class="stat-value" id="current-wins">21</span>
            <div class="stat-glow"></div>
          </div>
          <div class="stat-item">
            <span class="stat-label">DOBOGÓK</span>
            <span class="stat-value" id="current-podiums">32</span>
            <div class="stat-glow"></div>
          </div>
          <div class="stat-item">
            <span class="stat-label">POLE POZÍCIÓK</span>
            <span class="stat-value" id="current-poles">14</span>
            <div class="stat-glow"></div>
          </div>
          <div class="stat-item">
            <span class="stat-label">LEGGYORSABB KÖRÖK</span>
            <span class="stat-value" id="current-fastest-laps">12</span>
            <div class="stat-glow"></div>
          </div>
        </div>

        <div class="stats-grid" id="career-stats" style="display: none;">
          <div class="stat-item">
            <span class="stat-label">NAGYDÍJAK</span>
            <span class="stat-value" id="career-races">385</span>
            <div class="stat-glow"></div>
          </div>
          <div class="stat-item">
            <span class="stat-label">GYŐZELMEK</span>
            <span class="stat-value" id="career-wins">124</span>
            <div class="stat-glow"></div>
          </div>
          <div class="stat-item">
            <span class="stat-label">DOBOGÓK</span>
            <span class="stat-value" id="career-podiums">298</span>
            <div class="stat-glow"></div>
          </div>
          <div class="stat-item">
            <span class="stat-label">POLE POZÍCIÓK</span>
            <span class="stat-value" id="career-poles">133</span>
            <div class="stat-glow"></div>
          </div>
          <div class="stat-item">
            <span class="stat-label">LEGGYORSABB KÖRÖK</span>
            <span class="stat-value" id="career-fastest-laps">128</span>
            <div class="stat-glow"></div>
          </div>
          <div class="stat-item">
            <span class="stat-label">KONSTRUKTŐRI CÍMEK</span>
            <span class="stat-value" id="career-titles">8</span>
            <div class="stat-glow"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- F1 Görgető Gombok -->
    <div class="scroll-button left" id="scroll-left">
      <div class="accent-line"></div>
    </div>

    <div class="scroll-button right" id="scroll-right">
      <div class="accent-line"></div>
    </div>

    <div class="teams-container">
      <div class="teams-wrapper" id="teams-wrapper">
        <!-- ========== RED BULL (AUSZTRIA) ========== -->
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

        <!-- ========== FERRARI (OLASZORSZÁG) ========== -->
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

        <!-- ========== MERCEDES (NÉMETORSZÁG) ========== -->
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

        <!-- ========== McLAREN (EGYESÜLT KIRÁLYSÁG) ========== -->
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

        <!-- ========== ASTON MARTIN (EGYESÜLT KIRÁLYSÁG) ========== -->
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

        <!-- ========== ALPINE (FRANCIAORSZÁG) ========== -->
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

        <!-- ========== WILLIAMS (EGYESÜLT KIRÁLYSÁG) ========== -->
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

        <!-- ========== RACING BULLS (OLASZORSZÁG) ========== -->
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

        <!-- ========== HAAS (USA) ========== -->
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

        <!-- ========== AUDI (NÉMETORSZÁG) ========== -->
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

        <!-- ========== CADILLAC (USA) ========== -->
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
  
  <script src="team_script.js"></script>
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
    // CSAPAT STATISZTIKAI PANEL LOGÓ MEGJELENÍTÉSSEL
    // =========================================
    document.addEventListener('DOMContentLoaded', function() {
        // Összes csapatkártya lekérése
        const teamCards = document.querySelectorAll('.team-card');
        const statsPanel = document.getElementById('statistics-panel');
        const closePanelBtn = document.getElementById('close-panel');
        const teamsContainer = document.querySelector('.teams-container');
        const scrollRightBtn = document.getElementById('scroll-right');
        
        // Statisztikai panel elemek
        const statsTeamImage = document.getElementById('stats-team-image');
        const statsTeamName = document.getElementById('stats-team-name');
        const statsTeamBase = document.getElementById('stats-team-base');
        const statsFlag = document.getElementById('stats-flag');
        const statsCountry = document.getElementById('stats-country');
        
        // Funkció a csapat logó fájlnév lekéréséhez az adat attribútumból
        function getTeamLogoFilename(teamCard) {
            return teamCard.getAttribute('data-team-logo') || '';
        }
        
        // Funkció a statisztikai panel frissítéséhez a csapat adataival
        function updateStatsPanel(teamCard) {
            // Csapat adatok lekérése a kártyáról
            const teamLogo = getTeamLogoFilename(teamCard);
            const teamNameElem = teamCard.querySelector('.team-name');
            const teamPrincipalElem = teamCard.querySelector('.team-principal');
            const teamDetailsElem = teamCard.querySelector('.team-details');
            
            // Csapat adatok kinyerése
            let fullTeamName = teamNameElem ? teamNameElem.textContent : 'CSAPAT NÉV';
            let teamBase = teamPrincipalElem ? teamPrincipalElem.textContent : 'SZÉKHELY';
            let flagHtml = '';
            let countryText = '';
            
            if (teamDetailsElem) {
                // A zászló kép lekérése - pontosabb módszer
                const flagSpan = teamDetailsElem.querySelector('.flag');
                if (flagSpan) {
                    flagHtml = flagSpan.innerHTML;
                }
                // Az ország szöveg lekérése - a zászló után
                const detailsText = teamDetailsElem.cloneNode(true);
                const flagSpanClone = detailsText.querySelector('.flag');
                if (flagSpanClone) {
                    flagSpanClone.remove();
                }
                countryText = detailsText.textContent.trim();
            }
            
            // Alap csapat adatok frissítése
            if (statsTeamName) statsTeamName.textContent = fullTeamName;
            if (statsTeamBase) statsTeamBase.textContent = teamBase;
            if (statsFlag) statsFlag.innerHTML = flagHtml || '🏁';
            if (statsCountry) statsCountry.textContent = countryText || 'ORSZÁG';
            
            // Csapat logó frissítése
            if (statsTeamImage) {
                if (teamLogo) {
                    statsTeamImage.src = teamLogo;
                    statsTeamImage.alt = `${fullTeamName} logó`;
                    statsTeamImage.style.display = 'block';
                } else {
                    statsTeamImage.style.display = 'none';
                }
            }
        }
        
        // Kattintás esemény hozzáadása minden csapatkártyához
        teamCards.forEach(card => {
            card.addEventListener('click', function(e) {
                // Statisztikai panel frissítése a kattintott csapat adataival
                updateStatsPanel(this);
                
                // Statisztikai panel megnyitása
                statsPanel.classList.add('active');
                teamsContainer.classList.add('panel-active');
                if (scrollRightBtn) scrollRightBtn.classList.add('panel-active');
            });
        });
        
        // Panel bezárás funkció
        if (closePanelBtn) {
            closePanelBtn.addEventListener('click', function() {
                statsPanel.classList.remove('active');
                teamsContainer.classList.remove('panel-active');
                if (scrollRightBtn) scrollRightBtn.classList.remove('panel-active');
            });
        }
    });
  </script>
</body>

</html>