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
      return '#1b1b1b';
    default:
      return '#ffffff';
  }
}

$profile_image = null;
$fav_team = null;
$teamColor = '#ffffff';

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

/* ==== PILÓTÁK ÉS CSAPATOK LEKÉRDEZÉSE ==== */
// Összekapcsoljuk a pilotak táblát a csapatok táblával a team_id alapján
$sql = "SELECT p.*, c.team_name 
        FROM pilotak p 
        LEFT JOIN csapatok c ON p.`team id` = c.team_id 
        ORDER BY p.points DESC";
$result = $conn->query($sql);

$driversData = []; // Ezt a tömböt adjuk majd át a JavaScriptnek!

// Zászló fallback (Ha nincs mentve zászló emoji, a nemzetiség alapján megadjuk)
$flagMap = [
  'NED' => '🇳🇱',
  'GBR' => '🇬🇧',
  'AUS' => '🇦🇺',
  'MON' => '🇲🇨',
  'ITA' => '🇮🇹',
  'THA' => '🇹🇭',
  'ESP' => '🇪🇸',
  'FRA' => '🇫🇷',
  'GER' => '🇩🇪',
  'NZL' => '🇳🇿',
  'CAN' => '🇨🇦',
  'BRA' => '🇧🇷',
  'ARG' => '🇦🇷',
  'FIN' => '🇫🇮',
  'MEX' => '🇲🇽'
];

// Csapat CSS osztály térkép (A HTML attribútumokhoz)
$teamCssMap = [
  'Red Bull' => 'redbull',
  'Ferrari' => 'ferrari',
  'Mercedes' => 'mercedes',
  'McLaren' => 'mclaren',
  'Aston Martin' => 'astonmartin',
  'Alpine' => 'alpine',
  'Williams' => 'williams',
  'RB' => 'racingbulls',
  'Audi' => 'audi', // Ideiglenesen a CSS-ed miatt
  'Haas' => 'haas',
  'Cadillac' => 'cadillac'
];
?>
<!DOCTYPE html>
<html lang="hu">

<head>
  <meta charset="UTF-8">
  <title>Drivers – F1 Fan Club</title>
  <link rel="stylesheet" href="/f1fanclub/css/style.css">
  <link rel="stylesheet" href="drivers_style.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;800&display=swap" rel="stylesheet">
</head>

<body style="padding-top: 80px !important; margin: 0 !important; height: 100vh !important; overflow: hidden !important;">

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
      <a href="/f1fanclub/news/feed.php" style="color:white; margin:0 10px;">Paddock</a>
    </nav>
    <?php if ($isLoggedIn): ?>
      <div class="auth">
        <a href="/f1fanclub/profile/profile.php" style="text-decoration: none;">
          <div class="welcome">
            <?php if ($profile_image): ?>
              <img src="/f1fanclub/uploads/<?php echo htmlspecialchars($profile_image); ?>" class="avatar" alt="Profile"
                style="width:30px; height:30px; border-radius:50%; vertical-align:middle; object-fit: cover;">
            <?php endif; ?>
            <span class="welcome-text">
              Welcome, <span
                style="color: <?php echo htmlspecialchars($teamColor); ?>;"><?php echo htmlspecialchars($username); ?></span>!
            </span>
          </div>
        </a>
        <a href="/f1fanclub/logout/logout.php" class="btn">Log out</a>
      </div>
    <?php else: ?>
      <div class="auth">
        <a href="/f1fanclub/register/register.html" class="btn">Register</a>
        <a href="/f1fanclub/login/login.html" class="btn">Login</a>
      </div>
    <?php endif; ?>
  </header>

  <section id="drivers">
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

      <div class="stats-toggle">
        <button class="toggle-btn active" data-period="current"><span class="toggle-text">THIS SEASON</span><span
            class="toggle-glow"></span></button>
        <button class="toggle-btn" data-period="career"><span class="toggle-text">CAREER</span><span
            class="toggle-glow"></span></button>
      </div>

      <div class="statistics-content">
        <div class="stats-grid" id="current-stats">
          <div class="stat-item"><span class="stat-label">POSITION</span><span class="stat-value"
              id="current-position"></span>
            <div class="stat-glow"></div>
          </div>
          <div class="stat-item"><span class="stat-label">POINTS</span><span class="stat-value"
              id="current-points"></span>
            <div class="stat-glow"></div>
          </div>
          <div class="stat-item"><span class="stat-label">WINS</span><span class="stat-value" id="current-wins"></span>
            <div class="stat-glow"></div>
          </div>
          <div class="stat-item"><span class="stat-label">PODIUMS</span><span class="stat-value"
              id="current-podiums"></span>
            <div class="stat-glow"></div>
          </div>
          <div class="stat-item"><span class="stat-label">POLES</span><span class="stat-value"
              id="current-poles"></span>
            <div class="stat-glow"></div>
          </div>
          <div class="stat-item"><span class="stat-label">FASTEST LAPS</span><span class="stat-value"
              id="current-fastest-laps"></span>
            <div class="stat-glow"></div>
          </div>
        </div>

        <div class="stats-grid" id="career-stats" style="display: none;">
          <div class="stat-item"><span class="stat-label">GRAND PRIX</span><span class="stat-value"
              id="career-races"></span>
            <div class="stat-glow"></div>
          </div>
          <div class="stat-item"><span class="stat-label">WINS</span><span class="stat-value" id="career-wins"></span>
            <div class="stat-glow"></div>
          </div>
          <div class="stat-item"><span class="stat-label">PODIUMS</span><span class="stat-value"
              id="career-podiums"></span>
            <div class="stat-glow"></div>
          </div>
          <div class="stat-item"><span class="stat-label">POLES</span><span class="stat-value" id="career-poles"></span>
            <div class="stat-glow"></div>
          </div>
          <div class="stat-item"><span class="stat-label">FASTEST LAPS</span><span class="stat-value"
              id="career-fastest-laps"></span>
            <div class="stat-glow"></div>
          </div>
          <div class="stat-item"><span class="stat-label">WORLD TITLES</span><span class="stat-value"
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

          // Adatok előkészítése a HTML-hez és a JS-hez
          $keyId = $driver['abbreviation']; // Ezt használjuk egyedi azonosítónak (pl. VER, HAM)
          $teamName = $driver['team_name'] ?? 'Unknown Team';
          $cssClass = $teamCssMap[$teamName] ?? 'redbull';
          $flag = $flagMap[$driver['nationality']] ?? '🏁';

          // Kép elérési út javítása (A DB-ben 'drivers/...' van, de lehet, hogy neked a 'kép/' mappa kell)
          // Itt használjuk a loading="lazy" taget, hogy rohadt gyors legyen az oldal!
          $imgSrc = $driver['image'];
          if (strpos($imgSrc, 'kép/') === false && strpos($imgSrc, 'drivers/') === 0) {
            $imgSrc = 'kép/' . str_replace('drivers/', '', $imgSrc); // Fallback konverzió ha kell
          }

          // Feltöltjük a PHP tömböt a JS számára
          $driversData[$keyId] = [
            'name' => strtoupper($driver['name']),
            'team' => strtoupper($teamName),
            'nationality' => strtoupper($driver['nationality']),
            'flag' => $flag,
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
                <p class="driver-team"><?= strtoupper(htmlspecialchars($teamName)) ?></p>
                <div class="driver-nationality">
                  <span class="flag"><?= $flag ?></span>
                  <?= strtoupper(htmlspecialchars($driver['nationality'])) ?>
                </div>
              </div>
            </div>
          </div>

        <?php endwhile; ?>

      </div>
    </div>
  </section>

  <script>
    // WOW Factor: A PHP generálja le az adatbázisból a JavaScript számára a teljes statisztikát!
    window.driverStatsFromDB = <?= json_encode($driversData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT); ?>;
  </script>

  <script src="drivers_script.js"></script>
</body>

</html>