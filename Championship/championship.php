<?php 
session_start();

$DB_HOST = "localhost";
$DB_USER = "swmjndga_swmjndga";
$DB_PASS = "Teszt1234!";
$DB_NAME = "swmjndga_f1adat";

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) { die("Adatbázis hiba: " . $conn->connect_error); }

$isLoggedIn = isset($_SESSION['username']);
$username   = $isLoggedIn ? $_SESSION['username'] : null;

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

$profile_image = null; $fav_team = null; $teamColor = '#ffffff';

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
    <title>Championship Standings - F1 Fan Club</title>
    <link rel="stylesheet" href="/f1fanclub/css/style.css?v=<?php echo time(); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
    <a href="/f1fanclub/Championship/championship.php" style="color:#e10600; margin:0 10px; font-weight:bold;">Championship</a>
    <a href="/f1fanclub/teams/teams.php" style="color:white; margin:0 10px;">Teams</a>
    <a href="/f1fanclub/drivers/drivers.php" style="color:white; margin:0 10px;">Drivers</a>
    <a href="/f1fanclub/news/news.php" style="color:white; margin:0 10px;">Paddock</a>
  </nav>

  <?php if ($isLoggedIn): ?>
    <div class="auth">
      <div class="welcome">
        <?php if ($profile_image): ?>
            <img src="/f1fanclub/uploads/<?php echo htmlspecialchars($profile_image); ?>" class="avatar" alt="Profile" style="width:30px; height:30px; border-radius:50%; vertical-align:middle; object-fit: cover;">
        <?php endif; ?>
        <span class="welcome-text">
          Welcome, <span style="color: <?php echo htmlspecialchars($teamColor); ?>;"><?php echo htmlspecialchars($username); ?></span>!
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

<main class="champ-main">
    <div class="season-selector-wrapper">
        <label for="seasonSelect" style="color:#888; font-size:0.9rem; font-weight:700; text-transform:uppercase; letter-spacing:1px; display:block; margin-bottom:10px;">Válassz szezont</label>
        <select id="seasonSelect" class="f1-select"></select>
    </div>

    <h2 class="champ-title" id="drivers-title">Drivers Championship</h2>
    <div class="f1-table-wrapper">
        <table class="f1-table">
            <thead>
                <tr>
                    <th style="width: 80px; text-align:center;">Poz.</th>
                    <th>Pilóta</th>
                    <th>Csapat</th>
                    <th style="text-align:right; padding-right:30px;">Pontok</th>
                </tr>
            </thead>
            <tbody id="drivers-body">
                <tr><td colspan="4" style="text-align:center; padding: 40px; color:#888;">Adatok betöltése...</td></tr>
            </tbody>
        </table>
    </div>

    <h2 class="champ-title" id="constructors-title">Constructors Championship</h2>
    <div class="f1-table-wrapper">
        <table class="f1-table">
            <thead>
                <tr>
                    <th style="width: 80px; text-align:center;">Poz.</th>
                    <th>Csapat</th>
                    <th>Ország</th>
                    <th style="text-align:right; padding-right:30px;">Pontok</th>
                </tr>
            </thead>
            <tbody id="constructors-body">
                <tr><td colspan="4" style="text-align:center; padding: 40px; color:#888;">Adatok betöltése...</td></tr>
            </tbody>
        </table>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const seasonSelect = document.getElementById('seasonSelect');
    const driversBody = document.getElementById('drivers-body');
    const constructorsBody = document.getElementById('constructors-body');
    const driversTitle = document.getElementById('drivers-title');
    const constructorsTitle = document.getElementById('constructors-title');

    const currentYear = new Date().getFullYear(); 
    
    for (let year = currentYear; year >= 1950; year--) {
        const option = document.createElement('option');
        option.value = year; option.textContent = year;
        seasonSelect.appendChild(option);
    }
    seasonSelect.value = currentYear;

    function loadStandings(year) {
        driversTitle.innerHTML = `<span style="color:#fff;">${year}</span> Drivers Championship`;
        constructorsTitle.innerHTML = `<span style="color:#fff;">${year}</span> Constructors Championship`;
        
        // --- DRIVERS ---
        fetch(`https://api.jolpi.ca/ergast/f1/${year}/driverStandings.json`)
            .then(res => res.json())
            .then(data => {
                const list = data.MRData.StandingsTable.StandingsLists[0]?.DriverStandings || [];
                driversBody.innerHTML = ''; 

                if (list.length === 0) {
                    driversBody.innerHTML = '<tr><td colspan="4" style="text-align:center; padding:30px; color:#666;">Még nincsenek adatok ehhez a szezonhoz.</td></tr>';
                    return;
                }

                list.forEach(d => {
                    const isChampion = d.position === "1";
                    const rowClass = isChampion ? 'f1-row f1-champion' : 'f1-row';
                    const nameColor = isChampion ? 'f1-gold' : 'f1-white';
                    const posColor = isChampion ? 'f1-gold' : 'f1-red-text';
                    const trophy = isChampion ? '<i class="fas fa-trophy" style="color:#d4af37; margin-right:10px;"></i>' : '';

                    let teamName = d.Constructors && d.Constructors.length > 0 ? d.Constructors[0].name : "Private";

                    driversBody.innerHTML += `
                        <tr class="${rowClass}">
                            <td class="f1-pos ${posColor}">${d.position}</td>
                            <td class="f1-name ${nameColor}">${trophy}${d.Driver.givenName} <span style="font-weight:900;">${d.Driver.familyName}</span></td>
                            <td class="f1-team">${teamName}</td>
                            <td class="f1-points">${d.points}</td>
                        </tr>
                    `;
                });
            });

        // --- CONSTRUCTORS ---
        if (year < 1958) {
            constructorsBody.innerHTML = '<tr><td colspan="4" style="text-align:center; padding:30px; color:#666;">A Konstruktőri VB-t 1958 előtt nem osztották ki.</td></tr>';
        } else {
            fetch(`https://api.jolpi.ca/ergast/f1/${year}/constructorStandings.json`)
                .then(res => res.json())
                .then(data => {
                    const list = data.MRData.StandingsTable.StandingsLists[0]?.ConstructorStandings || [];
                    constructorsBody.innerHTML = '';

                    if (list.length === 0) {
                        constructorsBody.innerHTML = '<tr><td colspan="4" style="text-align:center; padding:30px; color:#666;">Még nincsenek adatok ehhez a szezonhoz.</td></tr>';
                        return;
                    }

                    list.forEach(c => {
                        const isChampion = c.position === "1";
                        const rowClass = isChampion ? 'f1-row f1-champion' : 'f1-row';
                        const nameColor = isChampion ? 'f1-gold' : 'f1-white';
                        const posColor = isChampion ? 'f1-gold' : 'f1-red-text';
                        const trophy = isChampion ? '<i class="fas fa-trophy" style="color:#d4af37; margin-right:10px;"></i>' : '';

                        let constName = c.Constructor ? c.Constructor.name : "Unknown";
                        let constNat = c.Constructor ? c.Constructor.nationality : "";

                        constructorsBody.innerHTML += `
                            <tr class="${rowClass}">
                                <td class="f1-pos ${posColor}">${c.position}</td>
                                <td class="f1-name ${nameColor}">${trophy}<span style="font-weight:900;">${constName}</span></td>
                                <td class="f1-team">${constNat}</td>
                                <td class="f1-points">${c.points}</td>
                            </tr>
                        `;
                    });
                });
        }
    }

    seasonSelect.addEventListener('change', (e) => loadStandings(e.target.value));
    loadStandings(seasonSelect.value);
});
</script>

</body>
</html>