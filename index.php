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

function getTeamColor($team)
{
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
    case 'Cadillac': return '#1b1b1b';
    default: return '#ffffff';
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

/**
 * ============================================================================
 * NEXT RACE FETCHING & SESSION TIME CALCULATION
 * ============================================================================
 */
$sqlNextRace = "SELECT * FROM f1_races WHERE race_date > NOW() ORDER BY race_date ASC LIMIT 1";
$resultRace = $conn->query($sqlNextRace);

$nextRace = null;
$fp1_time_str = "TBA";
$fp2_time_str = "TBA";
$fp3_time_str = "TBA";
$quali_time_str = "TBA";
$race_time_str = "TBA";

if ($resultRace && $resultRace->num_rows > 0) {
  $nextRace = $resultRace->fetch_assoc();
  
  $hu_days = ['Sun'=>'Vas', 'Mon'=>'Hét', 'Tue'=>'Ked', 'Wed'=>'Sze', 'Thu'=>'Csü', 'Fri'=>'Pén', 'Sat'=>'Szo'];

  // Futam ideje
  if (!empty($nextRace['race_date'])) {
      $ts = strtotime($nextRace['race_date']);
      $race_time_str = $hu_days[date('D', $ts)] . ' ' . date('H:i', $ts);
  }
  
  // FP1 ideje az adatbázisból
  if (!empty($nextRace['fp1_date'])) {
      $ts = strtotime($nextRace['fp1_date']);
      $fp1_time_str = $hu_days[date('D', $ts)] . ' ' . date('H:i', $ts);
  }
  
  // FP2 ideje az adatbázisból
  if (!empty($nextRace['fp2_date'])) {
      $ts = strtotime($nextRace['fp2_date']);
      $fp2_time_str = $hu_days[date('D', $ts)] . ' ' . date('H:i', $ts);
  }
  
  // FP3 ideje az adatbázisból
  if (!empty($nextRace['fp3_date'])) {
      $ts = strtotime($nextRace['fp3_date']);
      $fp3_time_str = $hu_days[date('D', $ts)] . ' ' . date('H:i', $ts);
  }
  
  // Időmérő ideje az adatbázisból
  if (!empty($nextRace['quali_date'])) {
      $ts = strtotime($nextRace['quali_date']);
      $quali_time_str = $hu_days[date('D', $ts)] . ' ' . date('H:i', $ts);
  }
}

/**
 * ============================================================================
 * LIVE RACE STATUS FETCHING
 * ============================================================================
 */
$sqlLive = "SELECT status FROM race_control WHERE race_id = 25 LIMIT 1";
$resLive = $conn->query($sqlLive);
$liveStatus = ($resLive && $resLive->num_rows > 0) ? $resLive->fetch_assoc()['status'] : 'stopped';

?>
<!DOCTYPE html>
<html lang="hu">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>F1 Fan Club - Főoldal</title>
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

    nav { display: flex; gap: 5px; margin: 0 20px; }
    nav a { font-weight: 600; font-size: 0.9rem; text-transform: uppercase; padding: 8px 16px; border-radius: 4px; color: #ffffff !important; text-decoration: none; position: relative; transition: all 0.2s ease; letter-spacing: 0.5px; opacity: 0.9; }
    nav a:hover { color: #e10600 !important; opacity: 1; background: rgba(225, 6, 0, 0.1); }
    nav a.active { color: #e10600 !important; opacity: 1; font-weight: 700; background: rgba(225, 6, 0, 0.15); }
    nav a[style*="color"] { color: #ffffff !important; }
    nav a[style*="color"]:hover, nav a[style*="color"].active { color: #e10600 !important; }

    .auth { display: flex; align-items: center; gap: 10px; }
    .welcome { display: flex; align-items: center; gap: 10px; font-size: 0.9rem; margin-right: 10px; padding: 5px 12px; background: rgba(255, 255, 255, 0.05); border-radius: 30px; border: 1px solid rgba(225, 6, 0, 0.2); }
    .welcome img.avatar { width: 32px; height: 32px; border-radius: 50%; object-fit: cover; border: 2px solid #e10600; transition: transform 0.3s; }
    .welcome img.avatar:hover { transform: scale(1.1); }
    .welcome-text { color: #ccc; }
    .welcome-text span { font-weight: 700; }

    .auth .btn { display: inline-block; padding: 8px 20px; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; color: #fff; background-color: transparent; border: 1px solid rgba(225, 6, 0, 0.5); border-radius: 30px; cursor: pointer; transition: all 0.3s ease; text-align: center; text-decoration: none; letter-spacing: 0.5px; }
    .auth .btn:hover { background-color: #e10600; border-color: #e10600; transform: translateY(-2px); box-shadow: 0 4px 15px rgba(225, 6, 0, 0.4); color: #fff; }
    .auth .btn:first-child { background-color: rgba(225, 6, 0, 0.15); border-color: #e10600; }
    .auth .btn:first-child:hover { background-color: #e10600; }
    .auth .btn:not(:last-child) { border-color: rgba(255, 255, 255, 0.2); }
    .auth .btn:not(:last-child):hover { border-color: #e10600; background-color: #e10600; }
    .auth .btn:last-child { border-color: rgba(225, 6, 0, 0.5); }
    .auth .btn:last-child:hover { background-color: #e10600; }

    @media (max-width: 1200px) { header { padding: 0 20px; } nav { gap: 2px; } nav a { padding: 8px 12px; font-size: 0.8rem; } }
    @media (max-width: 992px) { header { flex-wrap: wrap; height: auto; padding: 15px 20px; gap: 15px; } .logo-title { font-size: 1.2rem; } .logo-title img { width: 30px; } nav { order: 3; width: 100%; justify-content: center; flex-wrap: wrap; margin: 0; } .auth { margin-left: auto; } }
    @media (max-width: 768px) { .auth { flex-wrap: wrap; justify-content: flex-end; } .welcome { width: 100%; justify-content: center; margin-right: 0; margin-bottom: 5px; } }
    @media (max-width: 576px) { header { flex-direction: column; text-align: center; } .logo-title { justify-content: center; } .auth { justify-content: center; width: 100%; } nav a { padding: 6px 8px; font-size: 0.75rem; } }

    .live-banner { display: flex; align-items: center; justify-content: center; background: linear-gradient(90deg, #b30000, #e10600, #b30000); color: #fff; text-decoration: none; padding: 15px 20px; font-size: 1.2rem; font-weight: 800; text-transform: uppercase; letter-spacing: 3px; box-shadow: 0 4px 20px rgba(225, 6, 0, 0.4); transition: all 0.3s ease; border-bottom: 2px solid #ff4a4a; margin-bottom: 40px; }
    .live-banner:hover { background: linear-gradient(90deg, #e10600, #ff1a1a, #e10600); color: #fff; letter-spacing: 5px; }
    .live-pulse { display: inline-block; width: 15px; height: 15px; background-color: #fff; border-radius: 50%; margin-right: 15px; box-shadow: 0 0 15px #fff; animation: blinker 1s linear infinite; }
    @keyframes blinker { 50% { opacity: 0.2; transform: scale(0.8); } }

    .container { max-width: 1400px; margin: 0 auto; padding: 0 30px; }
    .section-header { text-align: center; margin-bottom: 25px; position: relative; }
    .section-title { font-size: 2rem; color: #e10600; font-weight: 800; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 15px; display: inline-block; position: relative; }
    .section-title::after { content: ""; position: absolute; bottom: -8px; left: 0; width: 100%; height: 3px; background: linear-gradient(90deg, transparent 0%, #e10600 20%, #e10600 80%, transparent 100%); border-radius: 2px; }
    .countdown-timer { display: flex; justify-content: center; gap: 15px; margin-top: 15px; }
    .countdown-unit { background: linear-gradient(145deg, #111111 0%, #1a1a1a 100%); padding: 12px 20px; border-radius: 15px; border: 1px solid rgba(225, 6, 0, 0.3); box-shadow: 0 5px 15px rgba(0, 0, 0, 0.5); text-align: center; min-width: 100px; }
    .countdown-value { font-size: 2.2rem; font-weight: 800; color: #e10600; display: block; line-height: 1; text-shadow: 0 0 15px rgba(225, 6, 0, 0.3); }
    .countdown-label { font-size: 0.75rem; color: #aaa; text-transform: uppercase; letter-spacing: 1px; font-weight: 500; }

    .race-card { background: linear-gradient(145deg, #111111 0%, #1a1a1a 100%); border-radius: 20px; padding: 25px; margin: 25px 0 30px; border: 1px solid rgba(225, 6, 0, 0.3); box-shadow: 0 20px 40px rgba(0, 0, 0, 0.8), 0 0 30px rgba(225, 6, 0, 0.1); position: relative; overflow: hidden; }
    .race-card::before { content: ""; position: absolute; top: 0; left: 0; right: 0; height: 3px; background: linear-gradient(90deg, transparent 0%, #e10600 20%, #e10600 80%, transparent 100%); z-index: 2; }
    .race-location { display: grid; grid-template-columns: 1.5fr 1fr; gap: 25px; margin-bottom: 20px; }
    .country-info { display: flex; flex-direction: column; gap: 5px; }
    .race-name { font-size: 1.6rem; color: #e10600; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px; }
    .circuit-name { font-size: 1rem; color: #ccc; font-weight: 400; }
    .race-date { font-size: 0.9rem; color: #aaa; display: flex; align-items: center; gap: 8px; margin-top: 10px; padding: 8px 0; border-top: 1px solid #333; border-bottom: 1px solid #333; }
    
    .circuit-visual { position: relative; border-radius: 15px; overflow: hidden; background: #0a0a0a; padding: 10px; }
    .circuit-map { width: 100%; height: auto; max-height: 130px; object-fit: contain; filter: invert(1) brightness(0.8); opacity: 0.8; transition: all 0.3s ease; }
    .circuit-map:hover { opacity: 1; transform: scale(1.02); }
    .circuit-overlay { position: absolute; bottom: 10px; right: 10px; background: rgba(225, 6, 0, 0.9); padding: 5px 10px; border-radius: 8px; font-weight: 500; font-size: 0.8rem; display: flex; gap: 10px; }

    .race-schedule { margin-top: 20px; }
    .schedule-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
    .schedule-header h4 { font-size: 1.2rem; color: #e10600; font-weight: 600; text-transform: uppercase; }
    .timezone { color: #aaa; font-size: 0.8rem; font-weight: 400; }
    .schedule-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 12px; }
    .session-card { background: linear-gradient(145deg, #1a1a1a 0%, #222 100%); padding: 12px 8px; border-radius: 12px; text-align: center; border: 1px solid #333; transition: all 0.3s ease; }
    .session-card:hover { transform: translateY(-3px); border-color: #e10600; box-shadow: 0 5px 15px rgba(225, 6, 0, 0.2); }
    .session-icon { font-size: 1.4rem; color: #e10600; margin-bottom: 8px; }
    .session-card h5 { font-size: 0.9rem; color: white; font-weight: 600; margin-bottom: 5px; }
    .session-time { color: #ccc; font-size: 0.85rem; font-weight: bold; }
    
    .highlight-session { background: linear-gradient(145deg, #2a1a1a 0%, #332020 100%); border-color: #e10600; }
    .main-session { background: linear-gradient(145deg, #331a1a 0%, #442020 100%); border: 2px solid #e10600; position: relative; overflow: hidden; }
    .main-session::before { content: "★"; position: absolute; top: -8px; right: -8px; font-size: 1.5rem; color: #e10600; opacity: 0.2; transform: rotate(15deg); }

    .featured-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin: 30px 0 20px; }
    .featured-card { background: linear-gradient(145deg, #111111 0%, #1a1a1a 100%); border-radius: 18px; padding: 20px; border: 1px solid rgba(225, 6, 0, 0.2); box-shadow: 0 10px 20px rgba(0, 0, 0, 0.5); transition: all 0.3s ease; text-align: center; position: relative; overflow: hidden; }
    .featured-card:hover { transform: translateY(-5px); border-color: #e10600; box-shadow: 0 20px 30px rgba(225, 6, 0, 0.15); }
    .featured-icon { font-size: 2rem; color: #e10600; margin-bottom: 12px; }
    .featured-card h3 { font-size: 1.2rem; color: white; font-weight: 600; margin-bottom: 8px; text-transform: uppercase; }
    .featured-card p { color: #aaa; margin-bottom: 15px; line-height: 1.4; font-size: 0.9rem; }
    .featured-btn { display: inline-block; padding: 8px 20px; background: transparent; border: 2px solid #e10600; color: #e10600; text-decoration: none; border-radius: 25px; font-weight: 500; text-transform: uppercase; letter-spacing: 1px; font-size: 0.8rem; transition: all 0.3s ease; }
    .featured-btn:hover { background: #e10600; color: white; }

    .site-footer { background: linear-gradient(145deg, #0a0a0a 0%, #111 100%); padding: 40px 40px 20px; border-top: 4px solid #e10600; margin-top: 40px; position: relative; }
    .site-footer::before { content: ""; position: absolute; top: -4px; left: 0; right: 0; height: 4px; background: linear-gradient(90deg, transparent 0%, #e10600 20%, #e10600 80%, transparent 100%); }
    .footer-container { max-width: 1400px; margin: 0 auto; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 30px; }
    .footer-section { display: flex; flex-direction: column; gap: 8px; }
    .footer-logo { display: flex; align-items: center; gap: 8px; font-size: 1.2rem; font-weight: 700; color: white; text-transform: uppercase; }
    .footer-logo img { width: 30px; filter: drop-shadow(0 0 8px #e10600); }
    .footer-section h3 { color: #e10600; font-size: 1rem; font-weight: 600; text-transform: uppercase; margin-bottom: 5px; }
    .footer-section p { font-size: 0.85rem; color: #aaa; line-height: 1.4; }
    .footer-section a { color: #aaa; text-decoration: none; font-size: 0.85rem; transition: all 0.3s ease; }
    .footer-section a:hover { color: #e10600; transform: translateX(3px); }
    .social-links { display: flex; gap: 10px; margin-top: 5px; }
    .social-icon { width: 20px; height: 20px; fill: #aaa; transition: all 0.3s ease; }
    .social-icon:hover { fill: #e10600; transform: scale(1.1); }
    .copyright { text-align: center; color: #555; font-size: 0.8rem; border-top: 1px solid #222; padding-top: 20px; margin-top: 20px; }

    @media (max-width: 1200px) { .featured-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 900px) { .race-location { grid-template-columns: 1fr; } .countdown-timer { flex-wrap: wrap; } .countdown-unit { min-width: 80px; padding: 8px 12px; } .countdown-value { font-size: 1.8rem; } }
    @media (max-width: 600px) { .featured-grid { grid-template-columns: 1fr; } .schedule-grid { grid-template-columns: 1fr; } .section-title { font-size: 1.5rem; } .race-name { font-size: 1.3rem; } }
  </style>
</head>

<body>
  <div class="bg-lines"></div>

  <header>
    <div class="logo-title">
      <img src="https://upload.wikimedia.org/wikipedia/commons/3/33/F1.svg" alt="F1 Logo">
      <span>Fan Club</span>
    </div>

    <nav>
      <a href="/f1fanclub/index.php">Home</a>
      <a href="/f1fanclub/Championship/championship.php">Championship</a>
      <a href="/f1fanclub/teams/teams.php">Teams</a>
      <a href="/f1fanclub/drivers/drivers.php">Drivers</a>
      <a href="/f1fanclub/news/feed.php">Paddock</a>
    </nav>

    <?php if ($isLoggedIn): ?>
      <div class="auth">
        <a href="/f1fanclub/profile/profile.php" style="text-decoration: none;">
          <div class="welcome">
            <?php if ($profile_image): ?>
              <img src="/f1fanclub/uploads/<?php echo htmlspecialchars($profile_image); ?>" class="avatar" alt="Profile">
            <?php endif; ?>
            <span class="welcome-text">
              Welcome,
              <span style="color: <?php echo htmlspecialchars($teamColor); ?>;">
                <?php echo htmlspecialchars($username); ?>
              </span>!
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

  <?php if ($liveStatus === 'running'): ?>
    <a href="/f1fanclub/race/live.php" class="live-banner">
      <span class="live-pulse"></span> ÉLŐBEN MOST: KANADAI NAGYDÍJ
    </a>
  <?php endif; ?>

  <section class="next-race-section">
    <div class="container">
      <div class="section-header">
        <h2 class="section-title">Next Grand Prix</h2>

        <?php if ($nextRace): ?>
          <div class="countdown-timer">
            <div class="countdown-unit">
              <span class="countdown-value" id="days">00</span>
              <span class="countdown-label">Days</span>
            </div>
            <div class="countdown-unit">
              <span class="countdown-value" id="hours">00</span>
              <span class="countdown-label">Hours</span>
            </div>
            <div class="countdown-unit">
              <span class="countdown-value" id="minutes">00</span>
              <span class="countdown-label">Minutes</span>
            </div>
          </div>
        <?php else: ?>
          <div class="countdown-timer">
            <div class="countdown-unit">
              <span class="countdown-value" style="font-size:1.5rem;">SEASON FINISHED</span>
            </div>
          </div>
        <?php endif; ?>
      </div>

      <?php if ($nextRace): ?>
        <div class="race-card">
          <div class="race-location">
            <div class="country-info">
              <h3 class="race-name"><?php echo htmlspecialchars($nextRace['race_name']); ?></h3>
              <p class="circuit-name"><?php echo htmlspecialchars($nextRace['circuit_name'] ?? 'TBA'); ?></p>
              <p class="race-date"><i class="far fa-calendar"></i>
                <?php echo date('Y. m. d. - H:i', strtotime($nextRace['race_date'])); ?>
              </p>
            </div>
            <div class="circuit-visual">
              <img
                src="https://upload.wikimedia.org/wikipedia/commons/thumb/4/4b/Jeddah_Street_Circuit_2021.svg/1200px-Jeddah_Street_Circuit_2021.svg.png"
                class="circuit-map" alt="Circuit Map">
              <div class="circuit-overlay">
                <span>5.278 km</span>
                <span>58 laps</span>
              </div>
            </div>
          </div>

          <div class="race-schedule">
            <div class="schedule-header">
              <h4>Weekend Schedule</h4>
              <span class="timezone">(Magyar Idő)</span>
            </div>
            <div class="schedule-grid">
              <a href="/f1fanclub/szabadedzes/szabadedzes.php" class="session-card" style="text-decoration:none; color:inherit;">
                <div class="session-icon"><i class="fas fa-clock"></i></div>
                <h5>FP1</h5>
                <p class="session-time"><?php echo $fp1_time_str; ?></p>
              </a>
              <a href="/f1fanclub/szabadedzes/szabadedzes.php" class="session-card" style="text-decoration:none; color:inherit;">
                <div class="session-icon"><i class="fas fa-clock"></i></div>
                <h5>FP2</h5>
                <p class="session-time"><?php echo $fp2_time_str; ?></p>
              </a>
              <a href="/f1fanclub/szabadedzes/szabadedzes.php" class="session-card" style="text-decoration:none; color:inherit;">
                <div class="session-icon"><i class="fas fa-clock"></i></div>
                <h5>FP3</h5>
                <p class="session-time"><?php echo $fp3_time_str; ?></p>
              </a>
              <a href="/f1fanclub/idomero/idomero.php" class="session-card highlight-session" style="text-decoration:none; color:inherit;">
                <div class="session-icon"><i class="fas fa-tachometer-alt"></i></div>
                <h5>Quali</h5>
                <p class="session-time"><?php echo $quali_time_str; ?></p>
              </a>
              <a href="/f1fanclub/race/live.php" class="session-card main-session" style="text-decoration:none; color:inherit;">
                <div class="session-icon"><i class="fas fa-flag-checkered"></i></div>
                <h5>Race</h5>
                <p class="session-time"><?php echo $race_time_str; ?></p>
              </a>
            </div>  
            </div>
          </div>
        </div>
      <?php else: ?>
        <p style="text-align:center; color:#aaa; font-size:1.2rem; margin:50px 0;">Jelenleg nincs következő futam az adatbázisban.</p>
      <?php endif; ?>

      <div class="featured-grid">
        <div class="featured-card">
          <div class="featured-icon"><i class="fas fa-trophy"></i></div>
          <h3>Championship</h3>
          <p>Kövesd nyomon a világbajnokság állását, versenyzői és csapatpontokat.</p>
          <a href="/f1fanclub/Championship/championship.php" class="featured-btn">Megnézem</a>
        </div>
        <div class="featured-card">
          <div class="featured-icon"><i class="fas fa-users"></i></div>
          <h3>Csapatok</h3>
          <p>Ismerd meg a mezőny csapatait, technikai részleteket és versenyzőiket.</p>
          <a href="/f1fanclub/teams/teams.php" class="featured-btn">Felfedezés</a>
        </div>
        <div class="featured-card">
          <div class="featured-icon"><i class="fas fa-newspaper"></i></div>
          <h3>Paddock Hírek</h3>
          <p>Friss hírek, elemzések és érdekességek közvetlenül a paddockból.</p>
          <a href="/f1fanclub/news/feed.php" class="featured-btn">Olvasok</a>
        </div>
      </div>
    </div>
  </section>

  <footer class="site-footer">
    <div class="footer-container">
      <div class="footer-section">
        <div class="footer-logo">
          <img src="https://upload.wikimedia.org/wikipedia/commons/3/33/F1.svg" alt="F1 Logo">
          <span>Fan Club</span>
        </div>
        <p>A legnagyobb magyar F1 közösség. Hírek, futamok, csapatok és szenvedély egy helyen.</p>
      </div>

      <div class="footer-section">
        <h3>Navigáció</h3>
        <a href="/f1fanclub/index.php">Főoldal</a>
        <a href="/f1fanclub/news/feed.php">Paddock (Feed)</a>
        <a href="/f1fanclub/about/about.php">Rólunk & Működés</a>
        <a href="/f1fanclub/teams/teams.php">Csapatok</a>
      </div>

      <div class="footer-section">
        <h3>Kapcsolat</h3>
        <a href="mailto:info@f1fanclub.hu">📧 info@f1fanclub.hu</a>
        <div class="social-links">
          <a href="https://instagram.com" target="_blank" title="Instagram">
            <svg class="social-icon" viewBox="0 0 24 24">
              <path
                d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z" />
            </svg>
          </a>
        </div>
      </div>
    </div>
    <div class="copyright">
      &copy; <?php echo date("Y"); ?> F1 Fan Club. Minden jog fenntartva. | Nem hivatalos F1 oldal.
    </div>
  </footer>

  <script>
    // Countdown timer - Dinamikus PHP dátummal
    <?php if ($nextRace): ?>
      const raceDateStr = "<?php echo date('Y-m-d H:i:s', strtotime($nextRace['race_date'])); ?>";
      const raceDate = new Date(raceDateStr).getTime();

      function updateCountdown() {
        const now = new Date().getTime();
        const timeLeft = raceDate - now;

        if (timeLeft > 0) {
          const days = Math.floor(timeLeft / (1000 * 60 * 60 * 24));
          const hours = Math.floor((timeLeft % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
          const minutes = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));

          document.getElementById('days').textContent = days.toString().padStart(2, '0');
          document.getElementById('hours').textContent = hours.toString().padStart(2, '0');
          document.getElementById('minutes').textContent = minutes.toString().padStart(2, '0');
        } else {
          const timerContainer = document.querySelector('.countdown-timer');
          if (timerContainer) {
            timerContainer.innerHTML = '<div class="countdown-unit"><span class="countdown-value" style="font-size:1.5rem;">RACE WEEKEND!</span></div>';
          }
        }
      }
      updateCountdown();
      setInterval(updateCountdown, 60000); 
    <?php endif; ?>
  </script>
</body>

</html>