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
$username   = $isLoggedIn ? $_SESSION['username'] : null;

/* === Csapatszín függvény === */
function getTeamColor($team) {
    switch ($team) {
        case 'Red Bull':      return '#1E41FF';
        case 'Ferrari':       return '#DC0000';
        case 'Mercedes':      return '#00D2BE';
        case 'McLaren':       return '#FF8700';
        case 'Aston Martin':  return '#006F62';
        case 'Alpine':        return '#0090FF';
        case 'Williams':      return '#00A0DE';
        case 'RB':            return '#2b2bff';
        case 'Kick Sauber':   return '#52E252';
        case 'Haas F1 Team':  return '#B6BABD';
        default:              return '#ffffff';
    }
}

$profile_image = null;
$fav_team      = null;
$teamColor     = '#ffffff';

/* ==== FELHASZNÁLÓ ADATOK LEKÉRÉSE ==== */
if ($isLoggedIn) {
    $stmt = $conn->prepare("SELECT profile_image, fav_team FROM users WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    $profile_image = $row['profile_image'] ?? null;
    $fav_team      = $row['fav_team'] ?? null;
    $teamColor     = getTeamColor($fav_team);

    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
<meta charset="UTF-8">
<title>Teams – F1 Fan Club</title>
<link rel="stylesheet" href="/f1fanclub/css/style.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;800&display=swap" rel="stylesheet">
</head>
<body>

<header>
  <div class="left-header">
    <h1 class="logo-title">
      <img src="https://upload.wikimedia.org/wikipedia/commons/3/33/F1.svg" class="f1-logo" alt="F1 Logo" style="height: 40px; vertical-align: middle;">
      <span>Fan Club</span>
    </h1>
  </div>

  <nav style="margin: 20px 0;">
    <a href="/f1fanclub/index.php" style="color:white; margin:0 10px;">Home</a>
    <a href="/f1fanclub/Championship/championship.php" style="color:white; margin:0 10px;">Championship</a>
    <a href="/f1fanclub/teams/teams.php" style="color:#e10600; margin:0 10px; font-weight:bold;">Teams</a>
    <a href="/f1fanclub/drivers/drivers.php" style="color:white; margin:0 10px;">Drivers</a>
    <a href="/f1fanclub/news/news.php" style="color:white; margin:0 10px;">News</a>
  </nav>

  <?php if ($isLoggedIn): ?>
    <div class="auth">
      <div class="welcome">
        <?php if ($profile_image): ?>
         <img src="/f1fanclub/uploads/<?php echo htmlspecialchars($profile_image); ?>" class="avatar" alt="Profile" style="width:30px; height:30px; border-radius:50%; vertical-align:middle; object-fit: cover;">
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

<h1 style="margin-top: 60px; text-align: center; color: white;">Teams Page (Work in Progress)</h1>
<script>
// Profil kártya kapcsoló
function toggleProfile() {
  const pc = document.getElementById("profileCard");
  if (!pc) return;
  pc.style.display = pc.style.display === "block" ? "none" : "block";
}
</script>

</body>
</html>