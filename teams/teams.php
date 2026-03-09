<?php
session_start();

/**
 * ============================================================================
 * DATABASE CONNECTION
 * ============================================================================
 * Establishes the connection to the MySQL database.
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
 * Retrieves the current logged-in user details from the session variables.
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
      return '#1b1b1b';
    default:
      return '#ffffff';
  }
}

$profile_image = null;
$fav_team = null;
$teamColor = '#ffffff';

/**
 * ============================================================================
 * USER DATA FETCHING
 * ============================================================================
 * Retrieves the favorite team and other user details from the database.
 */
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
  <title>Teams – F1 Fan Club</title>
  <link rel="stylesheet" href="/f1fanclub/css/style.css">
  <link rel="stylesheet" href="team_style.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;800&display=swap" rel="stylesheet"
    href="/f1fanclub/teams/teams_style.css">
</head>

<body class="BODY_PADDING_FIX">

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
      <a href="/f1fanclub/teams/teams.php" style="color:#e10600; margin:0 10px; font-weight:bold;">Teams</a>
      <a href="/f1fanclub/drivers/drivers.php" style="color:white; margin:0 10px;">Drivers</a>
      <a href="/f1fanclub/news/feed.php" style="color:white; margin:0 10px;">Paddock</a>
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
  <section id="teams">

    <!-- Statistics Panel (hidden by default) -->
    <div class="statistics-panel" id="statistics-panel">
      <button class="close-panel" id="close-panel">×</button>
      <div class="statistics-header">
        <div class="stats-image-container">
          <img id="stats-team-image" src="" alt="Team" class="stats-team-image">
          <div class="glow-effect"></div>
        </div>
        <div class="stats-team-info">
          <h2 id="stats-team-name">TEAM NAME</h2>
          <p id="stats-team-base">BASE LOCATION</p>
          <div class="stats-team-nationality">
            <span id="stats-flag">🏁</span>
            <span id="stats-country">COUNTRY</span>
          </div>
        </div>
      </div>

      <!-- Stats Toggle -->
      <div class="stats-toggle">
        <button class="toggle-btn active" data-period="current">
          <span class="toggle-text">2025 SEASON</span>
          <span class="toggle-glow"></span>
        </button>
        <button class="toggle-btn" data-period="career">
          <span class="toggle-text">TEAM HISTORY</span>
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
            <span class="stat-value" id="current-points">654</span>
            <div class="stat-glow"></div>
          </div>

          <!-- Current Season Stats - Row 2 -->
          <div class="stat-item">
            <span class="stat-label">WINS</span>
            <span class="stat-value" id="current-wins">21</span>
            <div class="stat-glow"></div>
          </div>
          <div class="stat-item">
            <span class="stat-label">PODIUMS</span>
            <span class="stat-value" id="current-podiums">32</span>
            <div class="stat-glow"></div>
          </div>

          <!-- Current Season Stats - Row 3 -->
          <div class="stat-item">
            <span class="stat-label">POLES</span>
            <span class="stat-value" id="current-poles">14</span>
            <div class="stat-glow"></div>
          </div>
          <div class="stat-item">
            <span class="stat-label">FASTEST LAPS</span>
            <span class="stat-value" id="current-fastest-laps">12</span>
            <div class="stat-glow"></div>
          </div>
        </div>

        <div class="stats-grid" id="career-stats" style="display: none;">
          <!-- Career Stats - Row 1 -->
          <div class="stat-item">
            <span class="stat-label">GRAND PRIX</span>
            <span class="stat-value" id="career-races">385</span>
            <div class="stat-glow"></div>
          </div>
          <div class="stat-item">
            <span class="stat-label">WINS</span>
            <span class="stat-value" id="career-wins">124</span>
            <div class="stat-glow"></div>
          </div>

          <!-- Career Stats - Row 2 -->
          <div class="stat-item">
            <span class="stat-label">PODIUMS</span>
            <span class="stat-value" id="career-podiums">298</span>
            <div class="stat-glow"></div>
          </div>
          <div class="stat-item">
            <span class="stat-label">POLES</span>
            <span class="stat-value" id="career-poles">133</span>
            <div class="stat-glow"></div>
          </div>

          <!-- Career Stats - Row 3 -->
          <div class="stat-item">
            <span class="stat-label">FASTEST LAPS</span>
            <span class="stat-value" id="career-fastest-laps">128</span>
            <div class="stat-glow"></div>
          </div>
          <div class="stat-item">
            <span class="stat-label">CONSTRUCTORS</span>
            <span class="stat-value" id="career-titles">8</span>
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

    <div class="teams-container">
      <div class="teams-wrapper" id="teams-wrapper">
        <!-- ========== RED BULL ========== -->
        <div class="team-card" data-team="redbull" id="RBR" data-team-id="red_bull">
          <div class="team-image-container">
            <img src="kép/RED_BULL_CAR.png" alt="Red Bull Racing" class="team-image">
            <div class="team-name-logo">Red Bull Racing</div>
            <div class="team-glow"></div>
          </div>
          <div class="team-info">
            <div class="nametag" id="RBR_nametag">
              <h2 class="team-name">RED BULL RACING</h2>
              <p class="team-principal">Christian Horner</p>
              <div class="team-details">
                <span class="flag">🇦🇹</span>
                MILTON KEYNES
              </div>
            </div>
          </div>
        </div>

        <!-- ========== FERRARI ========== -->
        <div class="team-card" data-team="ferrari" id="FER" data-team-id="ferrari">
          <div class="team-image-container">
            <img src="kép/FERRARI_CAR.png" alt="Ferrari" class="team-image">
            <div class="team-name-logo">Ferrari</div>
            <div class="team-glow"></div>
          </div>
          <div class="team-info">
            <div class="nametag" id="FER_nametag">
              <h2 class="team-name">SCUDERIA FERRARI</h2>
              <p class="team-principal">Frédéric Vasseur</p>
              <div class="team-details">
                <span class="flag">🇮🇹</span>
                MARANELLO
              </div>
            </div>
          </div>
        </div>

        <!-- ========== MERCEDES ========== -->
        <div class="team-card" data-team="mercedes" id="MER" data-team-id="mercedes">
          <div class="team-image-container">
            <img src="kép/MERCEDES_CAR.png" alt="Mercedes" class="team-image">
            <div class="team-name-logo">Mercedes</div>
            <div class="team-glow"></div>
          </div>
          <div class="team-info">
            <div class="nametag" id="MER_nametag">
              <h2 class="team-name">MERCEDES-AMG</h2>
              <p class="team-principal">Toto Wolff</p>
              <div class="team-details">
                <span class="flag">🇩🇪</span>
                BRACKLEY
              </div>
            </div>
          </div>
        </div>

        <!-- ========== McLAREN ========== -->
        <div class="team-card" data-team="mclaren" id="MCL" data-team-id="mclaren">
          <div class="team-image-container">
            <img src="kép/MCLAREN_CAR.png" alt="McLaren" class="team-image">
            <div class="team-name-logo">McLaren</div>
            <div class="team-glow"></div>
          </div>
          <div class="team-info">
            <div class="nametag" id="MCL_nametag">
              <h2 class="team-name">McLAREN F1 TEAM</h2>
              <p class="team-principal">Andrea Stella</p>
              <div class="team-details">
                <span class="flag">🇬🇧</span>
                WOKING
              </div>
            </div>
          </div>
        </div>

        <!-- ========== ASTON MARTIN ========== -->
        <div class="team-card" data-team="astonmartin" id="AST" data-team-id="aston_martin">
          <div class="team-image-container">
            <img src="kép/ASTON_MARTIN_CAR.png" alt="Aston Martin" class="team-image">
            <div class="team-name-logo">Aston Martin</div>
            <div class="team-glow"></div>
          </div>
          <div class="team-info">
            <div class="nametag" id="AST_nametag">
              <h2 class="team-name">ASTON MARTIN</h2>
              <p class="team-principal">Mike Krack</p>
              <div class="team-details">
                <span class="flag">🇬🇧</span>
                SILVERSTONE
              </div>
            </div>
          </div>
        </div>

        <!-- ========== ALPINE ========== -->
        <div class="team-card" data-team="alpine" id="ALP" data-team-id="alpine">
          <div class="team-image-container">
            <img src="kép/ALPINE_CAR.png" alt="Alpine" class="team-image">
            <div class="team-name-logo">Alpine</div>
            <div class="team-glow"></div>
          </div>
          <div class="team-info">
            <div class="nametag" id="ALP_nametag">
              <h2 class="team-name">ALPINE F1 TEAM</h2>
              <p class="team-principal">Oliver Oakes</p>
              <div class="team-details">
                <span class="flag">🇫🇷</span>
                ENSTONE
              </div>
            </div>
          </div>
        </div>

        <!-- ========== WILLIAMS ========== -->
        <div class="team-card" data-team="williams" id="WIL" data-team-id="williams">
          <div class="team-image-container">
            <img src="kép/WILLIAMS_CAR.png" alt="Williams" class="team-image">
            <div class="team-name-logo">Williams</div>
            <div class="team-glow"></div>
          </div>
          <div class="team-info">
            <div class="nametag" id="WIL_nametag">
              <h2 class="team-name">WILLIAMS RACING</h2>
              <p class="team-principal">James Vowles</p>
              <div class="team-details">
                <span class="flag">🇬🇧</span>
                GROVE
              </div>
            </div>
          </div>
        </div>

        <!-- ========== RACING BULLS ========== -->
        <div class="team-card" data-team="racingbulls" id="RB" data-team-id="racing_bulls">
          <div class="team-image-container">
            <img src="kép/RACING_BULLS_CAR.png" alt="Racing Bulls" class="team-image">
            <div class="team-name-logo">Racing Bulls</div>
            <div class="team-glow"></div>
          </div>
          <div class="team-info">
            <div class="nametag" id="RB_nametag">
              <h2 class="team-name">RACING BULLS</h2>
              <p class="team-principal">Laurent Mekies</p>
              <div class="team-details">
                <span class="flag">🇮🇹</span>
                FAENZA
              </div>
            </div>
          </div>
        </div>

        <!-- ========== HAAS ========== -->
        <div class="team-card" data-team="haas" id="HAA" data-team-id="haas">
          <div class="team-image-container">
            <img src="kép/HAAS_CAR.png" alt="Haas" class="team-image">
            <div class="team-name-logo">Haas</div>
            <div class="team-glow"></div>
          </div>
          <div class="team-info">
            <div class="nametag" id="HAA_nametag">
              <h2 class="team-name">MONEYGRAM HAAS</h2>
              <p class="team-principal">Ayao Komatsu</p>
              <div class="team-details">
                <span class="flag">🇺🇸</span>
                KANNAPOLIS
              </div>
            </div>
          </div>
        </div>

        <!-- ========== AUDI ========== -->
        <div class="team-card" data-team="audi" id="AUD" data-team-id="audi">
          <div class="team-image-container">
            <img src="kép/AUDI_CAR.png" alt="Audi" class="team-image">
            <div class="team-name-logo">Audi</div>
            <div class="team-glow"></div>
          </div>
          <div class="team-info">
            <div class="nametag" id="AUD_nametag">
              <h2 class="team-name">AUDI F1 TEAM</h2>
              <p class="team-principal">Mattia Binotto</p>
              <div class="team-details">
                <span class="flag">🇩🇪</span>
                HINWIL
              </div>
            </div>
          </div>
        </div>

        <!-- ========== CADILLAC ========== -->
        <div class="team-card" data-team="cadillac" id="CAD" data-team-id="cadillac">
          <div class="team-image-container">
            <img src="kép/CADILLAC_CAR.png" alt="Cadillac" class="team-image">
            <div class="team-name-logo">Cadillac</div>
            <div class="team-glow"></div>
          </div>
          <div class="team-info">
            <div class="nametag" id="CAD_nametag">
              <h2 class="team-name">CADILLAC RACING</h2>
              <p class="team-principal">Mario Andretti</p>
              <div class="team-details">
                <span class="flag">🇺🇸</span>
                CHARLOTTE
              </div>
            </div>
          </div>
        </div>

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
  <script src="team_script.js">
    // Profil kártya kapcsoló
    function toggleProfile() {
      const pc = document.getElementById("profileCard");
      if (!pc) return;
      pc.style.display = pc.style.display === "block" ? "none" : "block";

    }
  </script>
  <script>
    // Immediate test to see if script is loading
    console.log('Inline script test');
    document.addEventListener('DOMContentLoaded', function () {
      console.log('DOM Content Loaded - checking team cards');
      const cards = document.querySelectorAll('.team-card');
      console.log('Team cards found on load:', cards.length);

      cards.forEach((card, index) => {
        console.log(`Card ${index}:`, card.getAttribute('data-team-id'));
      });
    });
  </script>
</body>

</html>