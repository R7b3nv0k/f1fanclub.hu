<?php
session_start();

/* ==== ADATBÁZIS KAPCSOLAT ==== */
$DB_HOST = "localhost";
$DB_USER = "swmjndga_swmjndga";
$DB_PASS = "Teszt1234!";
$DB_NAME = "swmjndga_f1adat";

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
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
    case 'Kick Sauber':
      return '#52E252';
    case 'Haas F1 Team':
      return '#B6BABD';
    default:
      return '#ffffff';
  }
}

$profile_image = null;
$fav_team = null;
$teamColor = '#ffffff';

/* ==== FELHASZNÁLÓ ADATOK LEKÉRÉSE ==== */
if ($isLoggedIn) {
  $stmt = $conn->prepare("SELECT profile_image, fav_team FROM users WHERE username=?");
  $stmt->bind_param("s", $username);
  $stmt->execute();
  $row = $stmt->get_result()->fetch_assoc();

  $profile_image = $row['profile_image'] ?? null;
  $fav_team = $row['fav_team'] ?? null;
  $teamColor = getTeamColor($fav_team);

  $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="hu">

<head>
  <meta charset="UTF-8">
  <title>Drivers – F1 Fan Club</title>
  <link rel="stylesheet" href="/f1fanclub/css/style.css">
  <link rel="stylesheet" href="/f1fanclub/drivers/drivers_style.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;800&display=swap" rel="stylesheet">
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
      <a href="/f1fanclub/index.php" style="color:white; margin:0 10px;">Home</a>
      <a href="/f1fanclub/Championship/championship.php" style="color:white; margin:0 10px;">Championship</a>
      <a href="/f1fanclub/teams/teams.php" style="color:white; margin:0 10px;">Teams</a>
      <a href="/f1fanclub/drivers/drivers.php" style="color:#e10600; margin:0 10px; font-weight:bold;">Drivers</a>
      <a href="/f1fanclub/news/news.php" style="color:white; margin:0 10px;">News</a>
    </nav>

    <?php if ($isLoggedIn): ?>
      <div class="auth">
        <div class="welcome">
          <?php if ($profile_image): ?>
            <img src="/f1fanclub/uploads/<?php echo htmlspecialchars($profile_image); ?>" class="avatar" alt="Profile"
              style="width:30px; height:30px; border-radius:50%; vertical-align:middle; object-fit: cover;">
          <?php endif; ?>
          <span class="welcome-text">
            Welcome,
            <span style="color: <?php echo htmlspecialchars($teamColor); ?>;">
              <?php echo htmlspecialchars($username); ?>
            </span>!
          </span>
        </div>
        <a href="/f1fanclub/logout/logout.php" class="btn">Log out</a>
        <a href="/f1fanclub/profile/profile.php" class="btn">Profile</a>
      </div>
    <?php else: ?>
      <div class="auth">
        <a href="/f1fanclub/register/register.html" class="btn">Register</a>
        <a href="/f1fanclub/login/login.html" class="btn">Login</a>
      </div>
    <?php endif; ?>
  </header>
  <section id="drivers">
    <!-- Statistics Panel (hidden by default) -->
    <div class="statistics-panel" id="statistics-panel">
      <button class="close-panel" id="close-panel">×</button>
      <div class="statistics-header">
        <div class="stats-image-container">
          <img id="stats-driver-image" src="" alt="Driver" class="stats-driver-image">
          <div class="glow-effect"></div>
        </div>
        <div class="stats-driver-info">
          <h2 id="stats-driver-name">DRIVER NAME</h2>
          <p id="stats-driver-team">TEAM NAME</p>
          <div class="stats-driver-nationality">
            <span id="stats-flag">🏁</span>
            <span id="stats-nationality">NATIONALITY</span>
          </div>
        </div>
      </div>
      
      <!-- Stats Toggle -->
      <div class="stats-toggle">
        <button class="toggle-btn active" data-period="current">
          <span class="toggle-text">THIS SEASON</span>
          <span class="toggle-glow"></span>
        </button>
        <button class="toggle-btn" data-period="career">
          <span class="toggle-text">CAREER</span>
          <span class="toggle-glow"></span>
        </button>
      </div>
      
      <!-- Statistics Content - 2x3 Grid -->
      <div class="statistics-content">
        <div class="stats-grid" id="current-stats">
          <!-- Current Season Stats - Row 1 -->
          <div class="stat-item">
            <span class="stat-label">POSITION</span>
            <span class="stat-value" id="current-position">1st</span>
            <div class="stat-glow"></div>
          </div>
          <div class="stat-item">
            <span class="stat-label">POINTS</span>
            <span class="stat-value" id="current-points">454</span>
            <div class="stat-glow"></div>
          </div>
          
          <!-- Current Season Stats - Row 2 -->
          <div class="stat-item">
            <span class="stat-label">WINS</span>
            <span class="stat-value" id="current-wins">19</span>
            <div class="stat-glow"></div>
          </div>
          <div class="stat-item">
            <span class="stat-label">PODIUMS</span>
            <span class="stat-value" id="current-podiums">21</span>
            <div class="stat-glow"></div>
          </div>
          
          <!-- Current Season Stats - Row 3 -->
          <div class="stat-item">
            <span class="stat-label">POLES</span>
            <span class="stat-value" id="current-poles">12</span>
            <div class="stat-glow"></div>
          </div>
          <div class="stat-item">
            <span class="stat-label">FASTEST LAPS</span>
            <span class="stat-value" id="current-fastest-laps">9</span>
            <div class="stat-glow"></div>
          </div>
        </div>
        
        <div class="stats-grid" id="career-stats" style="display: none;">
          <!-- Career Stats - Row 1 -->
          <div class="stat-item">
            <span class="stat-label">GRAND PRIX</span>
            <span class="stat-value" id="career-races">185</span>
            <div class="stat-glow"></div>
          </div>
          <div class="stat-item">
            <span class="stat-label">WINS</span>
            <span class="stat-value" id="career-wins">54</span>
            <div class="stat-glow"></div>
          </div>
          
          <!-- Career Stats - Row 2 -->
          <div class="stat-item">
            <span class="stat-label">PODIUMS</span>
            <span class="stat-value" id="career-podiums">98</span>
            <div class="stat-glow"></div>
          </div>
          <div class="stat-item">
            <span class="stat-label">POLES</span>
            <span class="stat-value" id="career-poles">33</span>
            <div class="stat-glow"></div>
          </div>
          
          <!-- Career Stats - Row 3 -->
          <div class="stat-item">
            <span class="stat-label">FASTEST LAPS</span>
            <span class="stat-value" id="career-fastest-laps">28</span>
            <div class="stat-glow"></div>
          </div>
          <div class="stat-item">
            <span class="stat-label">WORLD TITLES</span>
            <span class="stat-value" id="career-titles">3</span>
            <div class="stat-glow"></div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- F1 Scroll Buttons -->
    <div class="scroll-button left" id="scroll-left">
      <div class="accent-line"></div>
    </div>
    
    <div class="scroll-button right" id="scroll-right">
      <div class="accent-line"></div>
    </div>
    
    <div class="drivers-container">
      <div class="drivers-wrapper" id="drivers-wrapper">
        <!-- ========== RED BULL ========== -->
        <!-- Driver 1: Max Verstappen -->
        <div class="driver-card" data-team="redbull" id="MAX_V" data-driver="max_verstappen">
          <div class="driver-image-container">
            <img src="kép/MAX_VERSTAPPEN.png" alt="Max Verstappen" class="driver-image">
            <div class="team-logo">Red Bull</div>
            <div class="driver-glow"></div>
          </div>
          <div class="driver-info">
            <div class="nametag" id="MAX_V_nametag">
              <h2 class="driver-name">MAX VERSTAPPEN</h2>
              <p class="driver-team">ORACLE RED BULL RACING</p>
              <div class="driver-nationality">
                <span class="flag">🇳🇱</span>
                NETHERLANDS
              </div>
            </div>
          </div>
        </div>
        
        <!-- Driver 2: Isack Hadjar -->
        <div class="driver-card" data-team="redbull" id="HAD" data-driver="isack_hadjar">
          <div class="driver-image-container">
            <img src="kép/ISACK_HADJAR.png" alt="Isack Hadjar" class="driver-image">
            <div class="team-logo">Red Bull</div>
            <div class="driver-glow"></div>
          </div>
          <div class="driver-info">
            <div class="nametag" id="HAD_nametag">
              <h2 class="driver-name">ISACK HADJAR</h2>
              <p class="driver-team">ORACLE RED BULL RACING</p>
              <div class="driver-nationality">
                <span class="flag">🇫🇷</span>
                FRANCE
              </div>
            </div>
          </div>
        </div>
        
        <!-- ========== FERRARI ========== -->
        <!-- Driver 3: Lewis Hamilton -->
        <div class="driver-card" data-team="ferrari" id="HAM" data-driver="lewis_hamilton">
          <div class="driver-image-container">
            <img src="kép/LEWIS_HAMILTON.png" alt="Lewis Hamilton" class="driver-image">
            <div class="team-logo">Ferrari</div>
            <div class="driver-glow"></div>
          </div>
          <div class="driver-info">
            <div class="nametag" id="HAM_nametag">
              <h2 class="driver-name">LEWIS HAMILTON</h2>
              <p class="driver-team">SCUDERIA FERRARI</p>
              <div class="driver-nationality">
                <span class="flag">🇬🇧</span>
                UNITED KINGDOM
              </div>
            </div>
          </div>
        </div>
        
        <!-- Driver 4: Charles Leclerc -->
        <div class="driver-card" data-team="ferrari" id="LEC" data-driver="charles_leclerc">
          <div class="driver-image-container">
            <img src="kép/CHARLES_LECLERC.png" alt="Charles Leclerc" class="driver-image">
            <div class="team-logo">Ferrari</div>
            <div class="driver-glow"></div>
          </div>
          <div class="driver-info">
            <div class="nametag" id="LEC_nametag">
              <h2 class="driver-name">CHARLES LECLERC</h2>
              <p class="driver-team">SCUDERIA FERRARI</p>
              <div class="driver-nationality">
                <span class="flag">🇲🇨</span>
                MONACO
              </div>
            </div>
          </div>
        </div>
        
        <!-- ========== MERCEDES ========== -->
        <!-- Driver 5: Kimi Antonelli -->
        <div class="driver-card" data-team="mercedes" id="ANT" data-driver="kimi_antonelli">
          <div class="driver-image-container">
            <img src="kép/KIMI_ANTONELLI.png" alt="Kimi Antonelli" class="driver-image">
            <div class="team-logo">Mercedes</div>
            <div class="driver-glow"></div>
          </div>
          <div class="driver-info">
            <div class="nametag" id="ANT_nametag">
              <h2 class="driver-name">KIMI ANTONELLI</h2>
              <p class="driver-team">MERCEDES-AMG PETRONAS</p>
              <div class="driver-nationality">
                <span class="flag">🇮🇹</span>
                ITALY
              </div>
            </div>
          </div>
        </div>
        
        <!-- Driver 6: George Russell -->
        <div class="driver-card" data-team="mercedes" id="RUS" data-driver="george_russell">
          <div class="driver-image-container">
            <img src="kép/GEORGE_RUSSEL.png" alt="George Russell" class="driver-image">
            <div class="team-logo">Mercedes</div>
            <div class="driver-glow"></div>
          </div>
          <div class="driver-info">
            <div class="nametag" id="RUS_nametag">
              <h2 class="driver-name">GEORGE RUSSELL</h2>
              <p class="driver-team">MERCEDES-AMG PETRONAS</p>
              <div class="driver-nationality">
                <span class="flag">🇬🇧</span>
                UNITED KINGDOM
              </div>
            </div>
          </div>
        </div>
        
        <!-- ========== RACING BULLS ========== -->
        <!-- Driver 7: Liam Lawson -->
        <div class="driver-card" data-team="racingbulls" id="LAW" data-driver="liam_lawson">
          <div class="driver-image-container">
            <img src="kép/LIAM_LAWSON.png" alt="Liam Lawson" class="driver-image">
            <div class="team-logo">Racing Bulls</div>
            <div class="driver-glow"></div>
          </div>
          <div class="driver-info">
            <div class="nametag" id="LAW_nametag">
              <h2 class="driver-name">LIAM LAWSON</h2>
              <p class="driver-team">RACING BULLS</p>
              <div class="driver-nationality">
                <span class="flag">🇳🇿</span>
                NEW ZEALAND
              </div>
            </div>
          </div>
        </div>
        
        <!-- Driver 8: Arvid Lindblad -->
        <div class="driver-card" data-team="racingbulls" id="LIN" data-driver="arvid_lindblad">
          <div class="driver-image-container">
            <img src="kép/ARVID_LINDBLAD.png" alt="Arvid Lindblad" class="driver-image">
            <div class="team-logo">Racing Bulls</div>
            <div class="driver-glow"></div>
          </div>
          <div class="driver-info">
            <div class="nametag" id="LIN_nametag">
              <h2 class="driver-name">ARVID LINDBLAD</h2>
              <p class="driver-team">RACING BULLS</p>
              <div class="driver-nationality">
                <span class="flag">🇬🇧</span>
                UNITED KINGDOM
              </div>
            </div>
          </div>
        </div>
        
        <!-- ========== McLAREN ========== -->
        <!-- Driver 9: Lando Norris -->
        <div class="driver-card" data-team="mclaren" id="NOR" data-driver="lando_norris">
          <div class="driver-image-container">
            <img src="kép/LANDO_NORRIS.png" alt="Lando Norris" class="driver-image">
            <div class="team-logo">McLaren</div>
            <div class="driver-glow"></div>
          </div>
          <div class="driver-info">
            <div class="nametag" id="NOR_nametag">
              <h2 class="driver-name">LANDO NORRIS</h2>
              <p class="driver-team">McLAREN F1 TEAM</p>
              <div class="driver-nationality">
                <span class="flag">🇬🇧</span>
                UNITED KINGDOM
              </div>
            </div>
          </div>
        </div>
        
        <!-- Driver 10: Oscar Piastri -->
        <div class="driver-card" data-team="mclaren" id="PIA" data-driver="oscar_piastri">
          <div class="driver-image-container">
            <img src="kép/OSCAR_PIASTRI.png" alt="Oscar Piastri" class="driver-image">
            <div class="team-logo">McLaren</div>
            <div class="driver-glow"></div>
          </div>
          <div class="driver-info">
            <div class="nametag" id="PIA_nametag">
              <h2 class="driver-name">OSCAR PIASTRI</h2>
              <p class="driver-team">McLAREN F1 TEAM</p>
              <div class="driver-nationality">
                <span class="flag">🇦🇺</span>
                AUSTRALIA
              </div>
            </div>
          </div>
        </div>
        
        <!-- ========== HAAS ========== -->
        <!-- Driver 11: Esteban Ocon -->
        <div class="driver-card" data-team="haas" id="OCO" data-driver="esteban_ocon">
          <div class="driver-image-container">
            <img src="kép/ESTEBAN_OCON.png" alt="Esteban Ocon" class="driver-image">
            <div class="team-logo">Haas</div>
            <div class="driver-glow"></div>
          </div>
          <div class="driver-info">
            <div class="nametag" id="OCO_nametag">
              <h2 class="driver-name">ESTEBAN OCON</h2>
              <p class="driver-team">MONEYGRAM HAAS F1 TEAM</p>
              <div class="driver-nationality">
                <span class="flag">🇫🇷</span>
                FRANCE
              </div>
            </div>
          </div>
        </div>
        
        <!-- Driver 12: Oliver Bearman -->
        <div class="driver-card" data-team="haas" id="BEA" data-driver="oliver_bearman">
          <div class="driver-image-container">
            <img src="kép/OLIVER_BEARMAN.png" alt="Oliver Bearman" class="driver-image">
            <div class="team-logo">Haas</div>
            <div class="driver-glow"></div>
          </div>
          <div class="driver-info">
            <div class="nametag" id="BEA_nametag">
              <h2 class="driver-name">OLIVER BEARMAN</h2>
              <p class="driver-team">MONEYGRAM HAAS F1 TEAM</p>
              <div class="driver-nationality">
                <span class="flag">🇬🇧</span>
                UNITED KINGDOM
              </div>
            </div>
          </div>
        </div>
        
        <!-- ========== CADILLAC ========== -->
        <!-- Driver 13: Sergio Perez -->
        <div class="driver-card" data-team="cadillac" id="PER" data-driver="sergio_perez">
          <div class="driver-image-container">
            <img src="kép/SERGIO_PEREZ.png" alt="Sergio Perez" class="driver-image">
            <div class="team-logo">Cadillac</div>
            <div class="driver-glow"></div>
          </div>
          <div class="driver-info">
            <div class="nametag" id="PER_nametag">
              <h2 class="driver-name">SERGIO PEREZ</h2>
              <p class="driver-team">CADILLAC RACING</p>
              <div class="driver-nationality">
                <span class="flag">🇲🇽</span>
                MEXICO
              </div>
            </div>
          </div>
        </div>
        
        <!-- Driver 14: Valtteri Bottas -->
        <div class="driver-card" data-team="cadillac" id="BOT" data-driver="valtteri_bottas">
          <div class="driver-image-container">
            <img src="kép/VALTTERI_BOTTAS.png" alt="Valtteri Bottas" class="driver-image">
            <div class="team-logo">Cadillac</div>
            <div class="driver-glow"></div>
          </div>
          <div class="driver-info">
            <div class="nametag" id="BOT_nametag">
              <h2 class="driver-name">VALTTERI BOTTAS</h2>
              <p class="driver-team">CADILLAC RACING</p>
              <div class="driver-nationality">
                <span class="flag">🇫🇮</span>
                FINLAND
              </div>
            </div>
          </div>
        </div>
        
        <!-- ========== WILLIAMS ========== -->
        <!-- Driver 15: Carlos Sainz -->
        <div class="driver-card" data-team="williams" id="SAI" data-driver="carlos_sainz">
          <div class="driver-image-container">
            <img src="kép/CARLOS_SAINZ.png" alt="Carlos Sainz" class="driver-image">
            <div class="team-logo">Williams</div>
            <div class="driver-glow"></div>
          </div>
          <div class="driver-info">
            <div class="nametag" id="SAI_nametag">
              <h2 class="driver-name">CARLOS SAINZ</h2>
              <p class="driver-team">WILLIAMS RACING</p>
              <div class="driver-nationality">
                <span class="flag">🇪🇸</span>
                SPAIN
              </div>
            </div>
          </div>
        </div>
        
        <!-- Driver 16: Alexander Albon -->
        <div class="driver-card" data-team="williams" id="ALB" data-driver="alexander_albon">
          <div class="driver-image-container">
            <img src="kép/ALEXANDER_ALBON.png" alt="Alexander Albon" class="driver-image">
            <div class="team-logo">Williams</div>
            <div class="driver-glow"></div>
          </div>
          <div class="driver-info">
            <div class="nametag" id="ALB_nametag">
              <h2 class="driver-name">ALEXANDER ALBON</h2>
              <p class="driver-team">WILLIAMS RACING</p>
              <div class="driver-nationality">
                <span class="flag">🇹🇭</span>
                THAILAND
              </div>
            </div>
          </div>
        </div>
        
        <!-- ========== AUDI ========== -->
        <!-- Driver 17: Nico Hülkenberg -->
        <div class="driver-card" data-team="audi" id="HUL" data-driver="nico_hulkenberg">
          <div class="driver-image-container">
            <img src="kép/NICO_HULKENBERG.png" alt="Nico Hülkenberg" class="driver-image">
            <div class="team-logo">Audi</div>
            <div class="driver-glow"></div>
          </div>
          <div class="driver-info">
            <div class="nametag" id="HUL_nametag">
              <h2 class="driver-name">NICO HÜLKENBERG</h2>
              <p class="driver-team">AUDI F1 TEAM</p>
              <div class="driver-nationality">
                <span class="flag">🇩🇪</span>
                GERMANY
              </div>
            </div>
          </div>
        </div>
        
        <!-- Driver 18: Gabriel Bortoleto -->
        <div class="driver-card" data-team="audi" id="BOR" data-driver="gabriel_bortoleto">
          <div class="driver-image-container">
            <img src="kép/GABRIEL_BORTOLETO.png" alt="Gabriel Bortoleto" class="driver-image">
            <div class="team-logo">Audi</div>
            <div class="driver-glow"></div>
          </div>
          <div class="driver-info">
            <div class="nametag" id="BOR_nametag">
              <h2 class="driver-name">GABRIEL BORTOLETO</h2>
              <p class="driver-team">AUDI F1 TEAM</p>
              <div class="driver-nationality">
                <span class="flag">🇧🇷</span>
                BRAZIL
              </div>
            </div>
          </div>
        </div>
        
        <!-- ========== ASTON MARTIN ========== -->
        <!-- Driver 19: Fernando Alonso -->
        <div class="driver-card" data-team="astonmartin" id="ALO" data-driver="fernando_alonso">
          <div class="driver-image-container">
            <img src="kép/FERNANDO_ALONSO.png" alt="Fernando Alonso" class="driver-image">
            <div class="team-logo">Aston Martin</div>
            <div class="driver-glow"></div>
          </div>
          <div class="driver-info">
            <div class="nametag" id="ALO_nametag">
              <h2 class="driver-name">FERNANDO ALONSO</h2>
              <p class="driver-team">ASTON MARTIN ARAMCO</p>
              <div class="driver-nationality">
                <span class="flag">🇪🇸</span>
                SPAIN
              </div>
            </div>
          </div>
        </div>
        
        <!-- Driver 20: Lance Stroll -->
        <div class="driver-card" data-team="astonmartin" id="STR" data-driver="lance_stroll">
          <div class="driver-image-container">
            <img src="kép/LANCE_STROLL.png" alt="Lance Stroll" class="driver-image">
            <div class="team-logo">Aston Martin</div>
            <div class="driver-glow"></div>
          </div>
          <div class="driver-info">
            <div class="nametag" id="STR_nametag">
              <h2 class="driver-name">LANCE STROLL</h2>
              <p class="driver-team">ASTON MARTIN ARAMCO</p>
              <div class="driver-nationality">
                <span class="flag">🇨🇦</span>
                CANADA
              </div>
            </div>
          </div>
        </div>
        
        <!-- ========== ALPINE ========== -->
        <!-- Driver 21: Pierre Gasly -->
        <div class="driver-card" data-team="alpine" id="GAS" data-driver="pierre_gasly">
          <div class="driver-image-container">
            <img src="kép/PIERRE_GASLY.png" alt="Pierre Gasly" class="driver-image">
            <div class="team-logo">Alpine</div>
            <div class="driver-glow"></div>
          </div>
          <div class="driver-info">
            <div class="nametag" id="GAS_nametag">
              <h2 class="driver-name">PIERRE GASLY</h2>
              <p class="driver-team">ALPINE F1 TEAM</p>
              <div class="driver-nationality">
                <span class="flag">🇫🇷</span>
                FRANCE
              </div>
            </div>
          </div>
        </div>
        
        <!-- Driver 22: Franco Colapinto -->
        <div class="driver-card" data-team="alpine" id="COL" data-driver="franco_colapinto">
          <div class="driver-image-container">
            <img src="kép/FRANCO_COLAPINTO.png" alt="Franco Colapinto" class="driver-image">
            <div class="team-logo">Alpine</div>
            <div class="driver-glow"></div>
          </div>
          <div class="driver-info">
            <div class="nametag" id="COL_nametag">
              <h2 class="driver-name">FRANCO COLAPINTO</h2>
              <p class="driver-team">ALPINE F1 TEAM</p>
              <div class="driver-nationality">
                <span class="flag">🇦🇷</span>
                ARGENTINA
              </div>
            </div>
          </div>
        </div>
        <!-- Add more drivers here following the same pattern -->
         
      </div>
    </div>
  </section>
  
  <?php if ($isLoggedIn): ?>
    <div class="profile-card" id="profileCard">
      <h3><?php echo htmlspecialchars($username); ?>'s Profile</h3>
      <?php if ($profile_image): ?>
        <img src="/uploads/<?php echo htmlspecialchars($profile_image); ?>" alt="Profile picture" style="max-width:100px;">
      <?php endif; ?>
      <form action="/f1fanclub/profile/upload_profile.php" method="post" enctype="multipart/form-data">
        <p><small>Upload new profile picture (max 250×250 px)</small></p>
        <input type="file" name="profile_image" required>
        <input type="submit" value="Upload" class="btn">
      </form>
    </div>
  <?php endif; ?>

  <h1 style="margin-top: 60px; text-align: center; color: white;">Drivers Page (Work in Progress)</h1>
  <script>
    // Profil kártya kapcsoló
    function toggleProfile() {
      const pc = document.getElementById("profileCard");
      if (!pc) return;
      pc.style.display = pc.style.display === "block" ? "none" : "block";
    }
  </script>
  <script src="/f1fanclub/drivers/drivers_style.css"></script>
</body>

</html>