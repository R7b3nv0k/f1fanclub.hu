<?php 
session_start();

/* ==== ADATBÁZIS KAPCSOLAT (CSAK A USER LOGIN MIATT KELL) ==== */
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

/* MEGJEGYZÉS: A manuális F1 lekérdezéseket (SELECT * FROM pilotak...) TÖRÖLTÜK. 
   Helyette a lenti JavaScript tölti be az adatokat. */
?>
<!DOCTYPE html>
<html lang="hu">
<head>
<meta charset="UTF-8">
<title>Championship Standings</title>
<link rel="stylesheet" href="/f1fanclub/css/style.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;800&display=swap" rel="stylesheet">

<style>
/* ===== ALAP OLDAL STÍLUS ===== */
body {
    margin: 0;
    font-family: 'Poppins', sans-serif;
    background:
      radial-gradient(circle at 0% 0%, rgba(225, 6, 0, 0.35), transparent 55%),
      radial-gradient(circle at 100% 100%, rgba(30, 65, 255, 0.35), transparent 55%),
      linear-gradient(135deg, #020202, #111);
    color: #f5f5f5;
    text-align: center;

/* ===== BAJNOK CSAPAT KIEMELÉS ===== */
.champion-row {
    background: linear-gradient(90deg, #d4af37, #f6e27a);
    color: #000;
    font-weight: 700;
    box-shadow: 0 0 18px rgba(255, 215, 0, 0.7);
    position: relative;
}
.champion-row:hover { background: linear-gradient(90deg, #e6c45a, #fff3aa); }

.champion-row td:nth-child(2) { font-weight: 800; }
.champion-row td:nth-child(2)::before { content: "🏆 "; margin-right: 4px; }

/* ===== BAJNOK PILÓTA KIEMELÉS ===== */
.driver-champion {
    background: linear-gradient(90deg, #d4af37, #fff3a1);
    color: #000;
    font-weight: 700;
    box-shadow: 0 0 16px rgba(255, 215, 0, 0.7);
    position: relative;
}
.driver-champion:hover { background: linear-gradient(90deg, #e6c45a, #fff8c6); }
.driver-champion td:nth-child(2) { font-weight: 800; }
.driver-champion td:nth-child(2)::before { content: "🏆 "; margin-right: 4px; }

.table-container { border: 1px solid rgba(255, 255, 255, 0.05); }

/*======================================
   SEASON SELECTOR (CHAMPIONSHIP)
======================================*/
.season-selector-container {
    margin: 30px auto 10px;
    text-align: center;
    position: relative;
    z-index: 10;
}

.season-select {
    appearance: none; /* Alap böngésző stílus eltüntetése */
    -webkit-appearance: none;
    background: linear-gradient(135deg, #181818, #000);
    color: #fff;
    font-family: 'Poppins', sans-serif;
    font-size: 1.1rem;
    font-weight: 600;
    padding: 12px 50px 12px 25px;
    border: 2px solid #333;
    border-radius: 999px;
    cursor: pointer;
    box-shadow: 0 0 15px rgba(0, 0, 0, 0.8);
    transition: all 0.3s ease;
    outline: none;
    /* Egyedi nyíl a jobb oldalon */
    background-image: url("data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%23e10600%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E");
    background-repeat: no-repeat;
    background-position: right 20px center;
    background-size: 12px;
}

.season-select:hover {
    border-color: #e10600;
    box-shadow: 0 0 20px rgba(225, 6, 0, 0.4);
}

.season-select:focus {
    border-color: #e10600;
    box-shadow: 0 0 25px rgba(225, 6, 0, 0.6);
}

.season-select option {
    background-color: #111;
    color: #fff;
}
}
</style>
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
    <img src="/f1fanclub/uploads/<?php echo htmlspecialchars($profile_image); ?>" alt="Profile picture" style="max-width:100px;">
  <?php endif; ?>
  <form action="/f1fanclub/profile/upload_profile.php" method="post" enctype="multipart/form-data">
    <p><small>Upload new profile picture (max 250×250 px)</small></p>
    <input type="file" name="profile_image" required>
    <input type="submit" value="Upload" class="btn">
  </form>
</div>
<?php endif; ?>

<div class="season-selector-container">
    <select id="seasonSelect" class="season-select">
        </select>
</div>
<div class="table-container">
    <h2 id="drivers-title">Drivers Championship</h2>
    <table>
        <thead>
            <tr>
                <th>Position</th>
                <th>Driver</th>
                <th>Team</th>
                <th>Points</th>
            </tr>
        </thead>
        <tbody id="drivers-body">
            <tr class="placeholder-row"><td colspan="4">Loading data...</td></tr>
        </tbody>
    </table>
</div>

<div class="table-container">
    <h2 id="constructors-title">Constructors Championship</h2>
    <table>
        <thead>
            <tr>
                <th>Position</th>
                <th>Team</th>
                <th>Country</th>
                <th>Points</th>
            </tr>
        </thead>
        <tbody id="constructors-body">
             <tr class="placeholder-row"><td colspan="4">Loading data...</td></tr>
        </tbody>
    </table>
</div>

<script>
// Profil kártya kapcsoló (Marad a régi)
function toggleProfile() {
  const pc = document.getElementById("profileCard");
  if (!pc) return;
  pc.style.display = pc.style.display === "block" ? "none" : "block";
}

document.addEventListener('DOMContentLoaded', () => {
    const seasonSelect = document.getElementById('seasonSelect');
    const driversBody = document.getElementById('drivers-body');
    const constructorsBody = document.getElementById('constructors-body');
    const driversTitle = document.getElementById('drivers-title');
    const constructorsTitle = document.getElementById('constructors-title');

    // 1. Évek feltöltése a legördülő menübe (2026-tól 1950-ig)
    const currentYear = new Date().getFullYear(); // 2026
    const startYear = 1950;
    
    for (let year = currentYear; year >= startYear; year--) {
        const option = document.createElement('option');
        option.value = year;
        option.textContent = year;
        seasonSelect.appendChild(option);
    }

    // Alapértelmezésben a jelenlegi évet választjuk ki
    // HA 2026-ban vagyunk, de még nincs adat, az API üreset ad vissza, ami helyes.
    // Ha azt szeretnéd, hogy alapból a tavalyi (2025) jelenjen meg, írd át: seasonSelect.value = currentYear - 1;
    seasonSelect.value = currentYear;

  // 2. Adatok betöltése funkció
    function loadStandings(year) {
        // UI frissítése betöltés közben
        driversTitle.innerText = `${year} Drivers Championship`;
        constructorsTitle.innerText = `${year} Constructors Championship`;
        driversBody.innerHTML = '<tr class="placeholder-row"><td colspan="4">Loading...</td></tr>';
        constructorsBody.innerHTML = '<tr class="placeholder-row"><td colspan="4">Loading...</td></tr>';

        // --- PILÓTÁK ---
        fetch(`https://api.jolpi.ca/ergast/f1/${year}/driverStandings.json`)
            .then(res => res.json())
            .then(data => {
                const list = data.MRData.StandingsTable.StandingsLists[0]?.DriverStandings || [];
                driversBody.innerHTML = ''; 

                if (list.length === 0) {
                    driversBody.innerHTML = '<tr class="placeholder-row"><td colspan="4">No data available yet for this season.</td></tr>';
                } else {
                    list.forEach(d => {
                        const isChampion = d.position === "1" ? 'class="driver-champion"' : '';
                        
                        // === JAVÍTÁS ITT ===
                        // 1. Csapat név kezelése: Ha nincs csapat (üres tömb), akkor "Private / Independent"
                        let teamName = "Private / Independent";
                        if (d.Constructors && d.Constructors.length > 0) {
                            teamName = d.Constructors[0].name;
                        }

                        // 2. Pozíció kezelése: Néha a "positionText" pontosabb régi éveknél (pl. "R" mint retired, vagy szám)
                        // Ha d.position undefined, akkor "-" lesz.
                        let positionDisplay = d.position || "-";
                        
                        const row = `
                            <tr ${isChampion}>
                                <td>${positionDisplay}</td>
                                <td>${d.Driver.givenName} ${d.Driver.familyName}</td>
                                <td>${teamName}</td>
                                <td>${d.points}</td>
                            </tr>
                        `;
                        driversBody.innerHTML += row;
                    });
                }
            })
            .catch(err => {
                console.error(err);
                driversBody.innerHTML = '<tr><td colspan="4" style="color:red;">Error loading data!</td></tr>';
            });

        // --- KONSTRUKTŐRÖK ---
        if (year < 1958) {
            constructorsBody.innerHTML = '<tr class="placeholder-row"><td colspan="4">The Constructors Championship was not awarded before 1958.</td></tr>';
        } else {
            fetch(`https://api.jolpi.ca/ergast/f1/${year}/constructorStandings.json`)
                .then(res => res.json())
                .then(data => {
                    const list = data.MRData.StandingsTable.StandingsLists[0]?.ConstructorStandings || [];
                    constructorsBody.innerHTML = '';

                    if (list.length === 0) {
                        constructorsBody.innerHTML = '<tr class="placeholder-row"><td colspan="4">No data available yet for this season.</td></tr>';
                    } else {
                        list.forEach(c => {
                            const isChampion = c.position === "1" ? 'class="champion-row"' : '';
                            
                            // Itt is egy kis biztonsági ellenőrzés, bár a csapatoknál ritkább a hiba
                            let constName = c.Constructor ? c.Constructor.name : "Unknown Team";
                            let constNat = c.Constructor ? c.Constructor.nationality : "";

                            const row = `
                                <tr ${isChampion}>
                                    <td>${c.position}</td>
                                    <td>${constName}</td>
                                    <td>${constNat}</td>
                                    <td>${c.points}</td>
                                </tr>
                            `;
                            constructorsBody.innerHTML += row;
                        });
                    }
                })
                .catch(err => {
                    console.error(err);
                    constructorsBody.innerHTML = '<tr><td colspan="4" style="color:red;">Error loading data!</td></tr>';
                });
        }
    }

    // 3. Eseményfigyelő: ha a felhasználó vált az évek között
    seasonSelect.addEventListener('change', (e) => {
        loadStandings(e.target.value);
    });

    // 4. Első betöltés az oldal megnyitásakor
    loadStandings(seasonSelect.value);
});
</script>

</body>
</html>