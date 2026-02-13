<?php
// live.php - HELYEZD A /race MAPPÁBA!
session_start();

// --- ADATBÁZIS & LOGIN A FEJLÉCHEZ ---
$DB_HOST = "localhost";
$DB_USER = "swmjndga_swmjndga";
$DB_PASS = "Teszt1234!";
$DB_NAME = "swmjndga_f1adat";

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) { die("DB Error"); }

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ÉLŐ: Kanadai Nagydíj 2026</title>
    <link rel="stylesheet" href="../css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,400;0,700;0,900;1,400&family=Roboto+Mono:wght@500;700&display=swap" rel="stylesheet">
    <style>
        /* --- MODERN F1 ÉLŐ KÖZVETÍTÉS DIZÁJN --- */
        body {
            background-color: #050505;
            background-image: radial-gradient(circle at 50% 0%, #1a0505 0%, #050505 60%);
            background-attachment: fixed;
            font-family: 'Montserrat', sans-serif;
            color: #fff;
        }

        /* Szélesebb konténer a TV grafikához */
        .live-container { 
            max-width: 1500px; /* Szélesebb lett! */
            width: 95%; 
            margin: 40px auto; 
            padding: 20px; 
        }
        
        .race-header { 
            display: flex; justify-content: space-between; align-items: center; 
            background: rgba(15, 15, 20, 0.8); backdrop-filter: blur(10px);
            padding: 25px 30px; border-radius: 12px; 
            border-bottom: 4px solid #e10600; margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }
        .race-header h1 { margin: 0; font-size: 2.2rem; font-weight: 900; text-transform: uppercase; letter-spacing: 2px; }
        .lap-counter { font-size: 2.8rem; font-weight: 900; color: #fff; font-family: 'Roboto Mono', monospace; line-height: 1; }
        .lap-label { font-size: 1rem; color: #888; text-transform: uppercase; font-weight: 700; letter-spacing: 1px; margin-bottom: 5px; text-align:right;}
        
        /* --- ANIMÁLT TABELLA GRID --- */
        .leaderboard-header {
            display: grid;
            grid-template-columns: 80px 3.5fr 2.5fr 200px 150px 120px; /* Új oszlop arányok */
            padding: 10px 20px;
            color: #888;
            text-transform: uppercase;
            font-size: 0.85rem;
            font-weight: 700;
            letter-spacing: 1px;
            border-bottom: 2px solid #333;
            margin-bottom: 10px;
        }

        .leaderboard-grid {
            position: relative;
            width: 100%;
        }

        .telemetry-row {
            position: absolute;
            left: 0; right: 0;
            height: 70px; /* Magasabb sor a képeknek */
            display: grid;
            grid-template-columns: 80px 3.5fr 2.5fr 200px 150px 120px;
            align-items: center;
            background: linear-gradient(90deg, #151515, #0f0f0f);
            border-radius: 8px;
            border-left: 4px solid transparent;
            padding: 0 20px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
            transition: transform 0.6s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.4s ease;
        }

        .telemetry-row.leader {
            border-left-color: #e10600;
            background: linear-gradient(90deg, rgba(225,6,0,0.1), #0f0f0f);
        }

        .pos-num { font-weight: 900; font-size: 1.6rem; width: 40px; text-align: center; }
        
        .driver-info { display: flex; align-items: center; gap: 15px; }
        
        /* ÚJ: Pilóta profilképe */
        .driver-portrait { 
            width: 50px; 
            height: 50px; 
            object-fit: cover; 
            border-radius: 50%; 
            border: 2px solid #333; 
            background: rgba(255,255,255,0.05); 
        }
        
        .driver-name { font-weight: 800; color: #fff; font-size: 1.2rem; }
        .driver-abbr { color: #888; font-size: 0.9rem; font-weight: 700; }
        
        .team-logo-small { width: 40px; height: 40px; object-fit: contain; }
        .team-name { font-weight: 600; color: #ccc; font-size: 1rem; }
        
        .gap-text { font-family: 'Roboto Mono', monospace; color: #e10600; font-weight: 700; font-size: 1.2rem; }
        
        .tyre-container { display: flex; align-items: center; gap: 10px; }
        .tyre-badge { width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 900; color: #000; font-size: 0.9rem; }
        .tyre-S { background-color: #ff3b30; box-shadow: 0 0 8px rgba(255,59,48,0.5); } 
        .tyre-M { background-color: #ffcc00; box-shadow: 0 0 8px rgba(255,204,0,0.5); } 
        .tyre-H { background-color: #fff; box-shadow: 0 0 8px rgba(255,255,255,0.5); } 
        
        .wear-bar-bg { width: 80px; height: 6px; background: #222; border-radius: 3px; overflow: hidden; }
        .wear-bar-fill { height: 100%; background: #00D2BE; transition: width 0.5s; }
        .wear-high { background: #e10600; }
        
        .status-dnf { color: #e10600; font-weight: 800; background: rgba(225,6,0,0.1); padding: 5px 12px; border-radius: 4px; }
        .status-pit { color: #ffcc00; font-weight: 800; animation: blink 1.5s infinite; background: rgba(255,204,0,0.1); padding: 5px 12px; border-radius: 4px; }
        
        @keyframes blink { 0%, 100% { opacity: 1; } 50% { opacity: 0.4; } }
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
    <a href="/f1fanclub/Championship/championship.php" style="color:white; margin:0 10px;">Championship</a>
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

<div class="live-container">
    <div class="race-header">
        <div>
            <h1>Canadian Grand Prix <span style="color:#e10600;">2026</span></h1>
            <span id="raceStatusText" style="color:#e10600; font-weight:800; font-size: 1.1rem; display:inline-block; margin-top:5px;">● LIVE SESSION</span>
        </div>
        <div>
            <div class="lap-label">Lap</div>
            <div class="lap-counter"><span id="currentLap">0</span><span style="color:#444;">/</span><span id="totalLaps" style="color:#666;">70</span></div>
        </div>
    </div>

    <div class="leaderboard-header">
        <div>Poz.</div>
        <div>Pilóta</div>
        <div>Csapat</div>
        <div>Gumi</div>
        <div>Interval</div>
        <div>Státusz</div>
    </div>

    <div id="raceGrid" class="leaderboard-grid">
        <p id="loadingMsg" style="text-align:center; color:#666; padding: 20px;">Adatok betöltése...</p>
    </div>
</div>

<script>
    const rowHeight = 80; // 70px magasság + 10px rés a sorok között

    async function fetchData() {
        try {
            const response = await fetch('race_api.php?action=update');
            const data = await response.json();
            
            if (data.race) {
                renderRace(data);
                
                if (data.race.status === 'running') {
                    document.getElementById('raceStatusText').innerHTML = '● LIVE SESSION';
                    document.getElementById('raceStatusText').style.color = '#e10600';
                    setTimeout(fetchData, 2000); 
                } else if (data.race.status === 'finished') {
                    document.getElementById('raceStatusText').innerHTML = '<i class="fas fa-flag-checkered"></i> RACE FINISHED';
                    document.getElementById('raceStatusText').style.color = '#fff';
                } else {
                    document.getElementById('raceStatusText').innerHTML = '⚠️ RACE STOPPED';
                    document.getElementById('raceStatusText').style.color = 'orange';
                    setTimeout(fetchData, 5000);
                }
            }
        } catch (error) {
            console.error("Hiba:", error);
        }
    }

    function renderRace(data) {
        document.getElementById('currentLap').innerText = data.race.current_lap;
        document.getElementById('totalLaps').innerText = data.race.total_laps;

        const grid = document.getElementById('raceGrid');
        const loadingMsg = document.getElementById('loadingMsg');
        if(loadingMsg) loadingMsg.remove();

        grid.style.height = (data.grid.length * rowHeight) + 'px';

        data.grid.forEach((driver, index) => {
            let tyreClass = 'tyre-S'; let tyreLetter = 'S';
            if(driver.tyre_type === 'Medium') { tyreClass = 'tyre-M'; tyreLetter = 'M'; }
            if(driver.tyre_type === 'Hard')   { tyreClass = 'tyre-H'; tyreLetter = 'H'; }
            let wearColorClass = driver.tyre_wear > 60 ? 'wear-high' : '';
            
            let gap = '';
            let posDisplay = '';
            let statusHtml = '';
            
            if (driver.status === 'DNF') {
                posDisplay = '<span style="color:#e10600; font-size:1.3rem;">OUT</span>';
                gap = driver.gap ? `<span style="color:#666; font-size:1rem;">${driver.gap}</span>` : 'Kiesett';
                statusHtml = '<span class="status-dnf">DNF</span>';
            } else {
                posDisplay = driver.position + '.';
                if (driver.status === 'Pit') {
                    gap = '<span style="color:#ffcc00;">IN PIT</span>';
                    statusHtml = '<span class="status-pit">PIT</span>';
                } else {
                    gap = driver.position === 1 ? 'Leader' : '+' + (driver.position * 1.5 + Math.random()).toFixed(1) + 's';
                }
            }

            let targetY = index * rowHeight;
            let row = document.getElementById('driver-row-' + driver.driver_id);

            if (!row) {
                row = document.createElement('div');
                row.id = 'driver-row-' + driver.driver_id;
                row.className = 'telemetry-row';
                grid.appendChild(row);
            }

            row.style.transform = `translateY(${targetY}px)`;
            row.style.opacity = driver.status === 'DNF' ? '0.4' : '1';
            
            if(driver.position === 1 && driver.status !== 'DNF') {
                row.classList.add('leader');
            } else {
                row.classList.remove('leader');
            }

            // Kép betöltése, ha nincs kép, egy üres profilképet mutat
            let imgPath = driver.driver_image ? `/f1fanclub/${driver.driver_image}` : '/f1fanclub/drivers/default.png';

            row.innerHTML = `
                <div class="pos-num">${posDisplay}</div>
                <div class="driver-info">
                    <img src="${imgPath}" class="driver-portrait" onerror="this.style.display='none'">
                    <div style="display:flex; flex-direction:column; justify-content:center;">
                        <span class="driver-name">${driver.name}</span>
                        <span class="driver-abbr">${driver.abbreviation}</span>
                    </div>
                </div>
                <div class="driver-info">
                    <img src="/f1fanclub/${driver.logo}" class="team-logo-small">
                    <span class="team-name">${driver.team_name}</span>
                </div>
                <div class="tyre-container">
                    <div class="tyre-badge ${tyreClass}">${tyreLetter}</div>
                    <div class="wear-bar-bg" title="Kopás: ${driver.tyre_wear}%">
                        <div class="wear-bar-fill ${wearColorClass}" style="width: ${driver.tyre_wear}%"></div>
                    </div>
                </div>
                <div class="gap-text">${gap}</div>
                <div>${statusHtml}</div>
            `;
        });
    }

    fetchData();
</script>
</body>
</html>