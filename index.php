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



/* ==== LOGIN ADATOK ==== */

$isLoggedIn = isset($_SESSION['username']);

$username = $isLoggedIn ? $_SESSION['username'] : null;

$userRole = isset($_SESSION['role']) ? $_SESSION['role'] : 'user';



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



/* ==== KÖVETKEZŐ FUTAM LEKÉRÉSE ==== */

// Azt a futamot keressük, aminek a dátuma NAGYOBB mint a mostani pillanat

// és a legközelebb van időben (ORDER BY ASC LIMIT 1)

$sqlNextRace = "SELECT * FROM f1_races WHERE race_date > NOW() ORDER BY race_date ASC LIMIT 1";

$resultRace = $conn->query($sqlNextRace);



$nextRace = null;

if ($resultRace && $resultRace->num_rows > 0) {

  $nextRace = $resultRace->fetch_assoc();

} else {

  // Ha nincs jövőbeli futam (szezon vége), manuálisan beállíthatunk valamit vagy kiírhatjuk, hogy vége

  $nextRace = null;

}

?>

<!DOCTYPE html>

<html lang="hu">

<head>

  <meta charset="UTF-8">

  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>F1 Fan Club - Főoldal</title>

  <link rel="icon" type="image/svg+xml" href="https://upload.wikimedia.org/wikipedia/commons/3/33/F1.svg">

  <link rel="stylesheet" href="css/style.css">

  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap"
    rel="stylesheet">

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

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

      <a href="/f1fanclub/index.php" style="color:#e10600; margin:0 10px; font-weight:bold;">Home</a>

      <a href="/f1fanclub/Championship/championship.php" style="color:white; margin:0 10px;">Championship</a>

      <a href="/f1fanclub/teams/teams.php" style="color:white; margin:0 10px;">Teams</a>

      <a href="/f1fanclub/drivers/drivers.php" style="color:white; margin:0 10px;">Drivers</a>

      <a href="/f1fanclub/news/news.php" style="color:white; margin:0 10px;">Paddock</a>

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



        <?php if ($userRole === 'admin'): ?>

          <a href="/f1fanclub/admin/admin.php" class="btn"
            style="background-color: #333; border: 1px solid #e10600;">Admin</a>

        <?php endif; ?>

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

            <span style="font-size: 2rem; color: #e10600;">SEASON FINISHED</span>

          </div>

        <?php endif; ?>



      </div>



      <?php if ($nextRace): ?>

        <div class="race-card">

          <div class="race-location">

            <div class="country-info">

              <img src="" class="country-flag-large" alt="<?php echo htmlspecialchars($nextRace['country']); ?>">

              <div class="location-details">

                <h3 class="race-name"><?php echo htmlspecialchars($nextRace['race_name']); ?></h3>

                <p class="circuit-name"><?php echo htmlspecialchars($nextRace['circuit_name'] ?? 'TBA'); ?></p>



                <p class="race-date"><i class="far fa-calendar"></i>

                  <?php echo date('M d, Y - H:i', strtotime($nextRace['race_date'])); ?>

                </p>

              </div>

            </div>

            <div class="circuit-visual">

              <img
                src="https://upload.wikimedia.org/wikipedia/commons/thumb/4/4b/Jeddah_Street_Circuit_2021.svg/1200px-Jeddah_Street_Circuit_2021.svg.png"
                class="circuit-map" alt="Circuit Map" style="filter: invert(1);">

              <div class="circuit-overlay">

                <span class="circuit-length">5.278 km</span>

                <span class="circuit-laps">58 laps</span>

              </div>

            </div>

          </div>



          <div class="race-schedule">

            <div class="schedule-header">

              <h4>Weekend Schedule</h4>

              <span class="timezone">(Local Time)</span>

            </div>

            <div class="schedule-grid">

              <div class="session-card">
                <div class="session-icon"><i class="fas fa-clock"></i></div>
                <h5>FP1</h5>
                <p class="session-time">TBA</p>
              </div>

              <div class="session-card">
                <div class="session-icon"><i class="fas fa-clock"></i></div>
                <h5>FP2</h5>
                <p class="session-time">TBA</p>
              </div>

              <div class="session-card">
                <div class="session-icon"><i class="fas fa-clock"></i></div>
                <h5>FP3</h5>
                <p class="session-time">TBA</p>
              </div>

              <div class="session-card highlight-session">
                <div class="session-icon"><i class="fas fa-tachometer-alt"></i></div>
                <h5>Quali</h5>
                <p class="session-time">TBA</p>
              </div>

              <div class="session-card main-session">
                <div class="session-icon"><i class="fas fa-flag-checkered"></i></div>
                <h5>Race</h5>
                <p class="session-time"><?php echo date('H:i', strtotime($nextRace['race_date'])); ?></p>
              </div>

            </div>

          </div>

        </div>

      <?php else: ?>

        <p style="text-align:center;">Jelenleg nincs következő futam az adatbázisban.</p>

      <?php endif; ?>



    </div>

  </section>

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

            timerContainer.innerHTML = '<div class="race-live" style="font-size:2rem; color:#e10600;">RACE WEEKEND!</div>';

          }

        }

      }

      updateCountdown();

      setInterval(updateCountdown, 60000); // Percenként frissít

    <?php endif; ?>

  </script>

  <footer class="site-footer">

    <div class="footer-container">

      <div class="footer-section">

        <div class="footer-logo">

          <img src="https://upload.wikimedia.org/wikipedia/commons/3/33/F1.svg" alt="F1 Logo" style="height: 30px;">

          <span>Fan Club</span>

        </div>

        <p>A legnagyobb magyar F1 közösség. Hírek, futamok, csapatok és szenvedély egy helyen.</p>

      </div>



      <div class="footer-section">

        <h3>Navigáció</h3>

        <a href="/f1fanclub/index.php">Főoldal</a>

        <a href="/f1fanclub/news/news.php">Paddock (Feed)</a>

        <a href="/f1fanclub/about.php">Rólunk & Működés</a>

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



</body>

</html>