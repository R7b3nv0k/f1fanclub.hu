<?php
// live.php - HELYEZD A /race MAPPÁBA!
session_start();

$DB_HOST = "localhost";
$DB_USER = "swmjndga_swmjndga";
$DB_PASS = "Teszt1234!";
$DB_NAME = "swmjndga_f1adat";

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) { die("DB Error"); }

// --- BIZTONSÁGI KAPU: HA ARCHIVÁLT A FUTAM, KIDOBJUK A FŐOLDALRA! ---
$statusCheck = $conn->query("SELECT status FROM race_control WHERE race_id = 25 LIMIT 1")->fetch_assoc();
if (!$statusCheck || $statusCheck['status'] === 'archived') {
    header("Location: ../index.php");
    exit;
}
// ---------------------------------------------------------------------

$isLoggedIn = isset($_SESSION['username']);
$username = $isLoggedIn ? $_SESSION['username'] : null;

function getTeamColor($team) {
    switch ($team) {
        case 'Red Bull': return '#1E41FF'; case 'Ferrari': return '#DC0000'; case 'Mercedes': return '#00D2BE';
        case 'McLaren': return '#FF8700'; case 'Aston Martin': return '#006F62'; case 'Alpine': return '#0090FF';
        case 'Williams': return '#00A0DE'; case 'RB': return '#2b2bff'; case 'Audi': return '#e3000f';
        case 'Haas F1 Team': return '#B6BABD'; case 'Cadillac': return '#1b1b1b'; default: return '#ffffff';
    }
}

$profile_image = null; $teamColor = '#ffffff';
if ($isLoggedIn) {
    $stmt = $conn->prepare("SELECT profile_image, fav_team FROM users WHERE username=?");
    $stmt->bind_param("s", $username); $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $profile_image = $row['profile_image'] ?? null; $teamColor = getTeamColor($row['fav_team'] ?? null);
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
        body { background-color: #050505; background-image: radial-gradient(circle at 50% 0%, #1a0505 0%, #050505 60%); background-attachment: fixed; font-family: 'Montserrat', sans-serif; color: #fff; }
        
        /* Fő elrendezés: Bal oldalt verseny, jobb oldalt chat */
        .live-wrapper { display: flex; gap: 20px; max-width: 1700px; width: 98%; margin: 30px auto; align-items: flex-start; }
        .telemetry-panel { flex: 3; min-width: 0; } /* Bal oldal */
        
        /* CHAT STÍLUSOK */
        .chat-panel { flex: 1; background: #0a0a0a; border: 1px solid #333; border-radius: 12px; height: 85vh; display: flex; flex-direction: column; position: sticky; top: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.8); overflow: hidden; }
        .chat-header { background: #111; padding: 15px 20px; font-weight: 900; text-transform: uppercase; color: #fff; border-bottom: 2px solid #e10600; display: flex; align-items: center; gap: 10px; }
        .chat-messages { flex: 1; overflow-y: auto; padding: 15px; display: flex; flex-direction: column; gap: 15px; }
        
        /* Gördítősáv a chathez */
        .chat-messages::-webkit-scrollbar { width: 6px; }
        .chat-messages::-webkit-scrollbar-thumb { background: #444; border-radius: 3px; }

        .chat-msg { display: flex; gap: 12px; animation: fadeIn 0.3s ease; }
        .chat-msg img { width: 35px; height: 35px; border-radius: 50%; object-fit: cover; border: 2px solid transparent; }
        .chat-msg-body { background: #1a1a1a; padding: 10px 14px; border-radius: 0 12px 12px 12px; font-size: 0.9rem; color: #ddd; width: 100%; border: 1px solid #222; }
        .chat-user-info { display: flex; justify-content: space-between; margin-bottom: 5px; font-size: 0.75rem; }
        .chat-user-name { font-weight: 800; text-transform: uppercase; }
        .chat-time { color: #666; }
        
        .chat-input-area { display: flex; padding: 15px; background: #111; border-top: 1px solid #333; }
        .chat-input-area input { flex: 1; padding: 12px 15px; border-radius: 25px; border: 1px solid #444; background: #000; color: #fff; font-family: 'Poppins', sans-serif; outline: none; transition: border 0.3s; }
        .chat-input-area input:focus { border-color: #e10600; }
        .chat-input-area button { background: #e10600; color: #fff; border: none; border-radius: 50%; width: 45px; height: 45px; margin-left: 10px; cursor: pointer; transition: 0.2s; display: flex; align-items: center; justify-content: center; }
        .chat-input-area button:hover { background: #ff1a1a; transform: scale(1.05); }
        .chat-guest-msg { padding: 20px; text-align: center; color: #888; font-size: 0.9rem; background: #111; border-top: 1px solid #333; }
        .chat-guest-msg a { color: #e10600; text-decoration: underline; }

        /* Visszamaradt telemetry stílusok */
        .race-header { display: flex; justify-content: space-between; align-items: center; background: rgba(15, 15, 20, 0.8); backdrop-filter: blur(10px); padding: 25px 30px; border-radius: 12px; border-bottom: 4px solid #e10600; margin-bottom: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        .race-header h1 { margin: 0; font-size: 2.2rem; font-weight: 900; text-transform: uppercase; letter-spacing: 2px; }
        .lap-counter { font-size: 2.8rem; font-weight: 900; color: #fff; font-family: 'Roboto Mono', monospace; line-height: 1; }
        .lap-label { font-size: 1rem; color: #888; text-transform: uppercase; font-weight: 700; letter-spacing: 1px; margin-bottom: 5px; text-align:right;}
        .indicators { display: flex; gap: 15px; margin-top: 10px; }
        .indicator-badge { padding: 5px 15px; border-radius: 6px; font-weight: 800; font-size: 0.9rem; text-transform: uppercase; display: flex; align-items: center; gap: 8px; }
        .weather-sunny { background: rgba(255, 204, 0, 0.1); color: #ffcc00; border: 1px solid #ffcc00; }
        .weather-rain { background: rgba(0, 122, 255, 0.1); color: #007aff; border: 1px solid #007aff; }
        .sc-active { background: rgba(255, 204, 0, 0.2); color: #ffcc00; border: 2px solid #ffcc00; animation: blink 1s infinite; }
        .leaderboard-header { display: grid; grid-template-columns: 80px 3.5fr 2.5fr 200px 150px 120px; padding: 10px 20px; color: #888; text-transform: uppercase; font-size: 0.85rem; font-weight: 700; letter-spacing: 1px; border-bottom: 2px solid #333; margin-bottom: 10px; }
        .leaderboard-grid { position: relative; width: 100%; transition: height 0.5s; }
        .telemetry-row { position: absolute; left: 0; right: 0; height: 70px; display: grid; grid-template-columns: 80px 3.5fr 2.5fr 200px 150px 120px; align-items: center; background: linear-gradient(90deg, #151515, #0f0f0f); border-radius: 8px; border-left: 4px solid transparent; padding: 0 20px; box-shadow: 0 4px 10px rgba(0,0,0,0.3); transition: transform 0.6s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.4s ease; }
        .telemetry-row.leader { border-left-color: #e10600; background: linear-gradient(90deg, rgba(225,6,0,0.1), #0f0f0f); }
        .telemetry-row.sc-mode { background: linear-gradient(90deg, rgba(255,204,0,0.1), #0f0f0f); border-left-color: #ffcc00; }
        .pos-num { font-weight: 900; font-size: 1.6rem; width: 40px; text-align: center; }
        .driver-info { display: flex; align-items: center; gap: 15px; }
        .driver-pic-box { width: 50px; height: 50px; flex-shrink: 0; border-radius: 50%; border: 2px solid #333; background: rgba(255,255,255,0.05); overflow: hidden; display: flex; align-items: center; justify-content: center; }
        .driver-portrait { width: 100%; height: 100%; object-fit: cover; object-position: top center; transform-origin: top center; transform: scale(1.3); }
        .team-logo-box { width: 40px; height: 40px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; }
        .team-logo-small { max-width: 100%; max-height: 100%; object-fit: contain; }
        .driver-name { font-weight: 800; color: #fff; font-size: 1.2rem; }
        .driver-abbr { color: #888; font-size: 0.9rem; font-weight: 700; }
        .team-name { font-weight: 600; color: #ccc; font-size: 1rem; }
        .gap-text { font-family: 'Roboto Mono', monospace; color: #e10600; font-weight: 700; font-size: 1.2rem; }
        .gap-sc { color: #ffcc00 !important; }
        .tyre-container { display: flex; align-items: center; gap: 10px; }
        .tyre-badge { width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 900; color: #000; font-size: 0.9rem; }
        .tyre-S { background-color: #ff3b30; box-shadow: 0 0 8px rgba(255,59,48,0.5); } 
        .tyre-M { background-color: #ffcc00; box-shadow: 0 0 8px rgba(255,204,0,0.5); } 
        .tyre-H { background-color: #fff; box-shadow: 0 0 8px rgba(255,255,255,0.5); } 
        .tyre-I { background-color: #34c759; box-shadow: 0 0 8px rgba(52,199,89,0.5); color: #fff;} 
        .tyre-W { background-color: #007aff; box-shadow: 0 0 8px rgba(0,122,255,0.5); color: #fff;} 
        .wear-bar-bg { width: 80px; height: 6px; background: #222; border-radius: 3px; overflow: hidden; }
        .wear-bar-fill { height: 100%; background: #00D2BE; transition: width 0.5s; }
        .wear-high { background: #e10600; }
        .status-dnf { color: #e10600; font-weight: 800; background: rgba(225,6,0,0.1); padding: 5px 12px; border-radius: 4px; }
        .status-pit { color: #ffcc00; font-weight: 800; animation: blink 1.5s infinite; background: rgba(255,204,0,0.1); padding: 5px 12px; border-radius: 4px; }
        @keyframes blink { 0%, 100% { opacity: 1; } 50% { opacity: 0.4; } }
        #postRaceStandings { display: none; margin-top: 60px; animation: fadeIn 1s; }
        .standings-table { width: 100%; border-collapse: collapse; background: #0d0d0d; border-radius: 12px; overflow: hidden; box-shadow: 0 15px 35px rgba(0,0,0,0.8); }
        .standings-table th { background: #151515; color: #666; padding: 15px; text-transform: uppercase; text-align: left; }
        .standings-table td { padding: 15px; border-bottom: 1px solid #1a1a1a; }
        .standings-table tr:hover { background: #161616; }
        .champ-gold { color: #d4af37; font-weight: 900; }
        
        /* Reszponzív tabletre */
        @media(max-width: 1200px) {
            .live-wrapper { flex-direction: column; }
            .chat-panel { width: 100%; height: 500px; }
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
      <a href="/f1fanclub/Championship/championship.php" style="color:white; margin:0 10px;">Championship</a>
      <a href="/f1fanclub/teams/teams.php" style="color:white; margin:0 10px;">Teams</a>
      <a href="/f1fanclub/drivers/drivers.php" style="color:white; margin:0 10px;">Drivers</a>
      <a href="/f1fanclub/news/feed.php" style="color:white; margin:0 10px;">Paddock</a>
  </nav>
  <div class="auth">
      <a href="../index.php" class="btn btn-ghost" style="color: #e10600; border-color: #e10600;">VISSZA A PADDOCKBA</a>
  </div>
</header>

<div class="live-wrapper">
    <div class="telemetry-panel">
        <div class="race-header">
            <div>
                <h1>Canadian Grand Prix <span style="color:#e10600;">2026</span></h1>
                <div class="indicators">
                    <div id="raceStatusText" class="indicator-badge" style="background: rgba(225,6,0,0.1); color:#e10600; border: 1px solid #e10600;">● LIVE SESSION</div>
                    <div id="weatherBadge" class="indicator-badge weather-sunny"><i class="fas fa-sun"></i> SUNNY</div>
                    <div id="scBadge" class="indicator-badge sc-active" style="display:none;"><i class="fas fa-car"></i> SAFETY CAR</div>
                </div>
            </div>
            <div>
                <div class="lap-label">Lap</div>
                <div class="lap-counter"><span id="currentLap">0</span><span style="color:#444;">/</span><span id="totalLaps" style="color:#666;">70</span></div>
            </div>
        </div>

        <div class="leaderboard-header">
            <div>Poz.</div><div>Pilóta</div><div>Csapat</div><div>Gumi</div><div>Interval</div><div>Státusz</div>
        </div>

        <div id="raceGrid" class="leaderboard-grid">
            <p id="loadingMsg" style="text-align:center; color:#666; padding: 20px;">Adatok betöltése...</p>
        </div>

        <div id="postRaceStandings">
            <h2 style="color: #d4af37; text-align: center; font-size: 2rem; margin-bottom: 20px;"><i class="fas fa-trophy"></i> 2026 Drivers Championship (Mock)</h2>
            <table class="standings-table">
                <thead><tr><th>Poz.</th><th>Pilóta</th><th>Csapat</th><th style="text-align:right;">Pontok</th></tr></thead>
                <tbody id="standingsBody"></tbody>
            </table>
        </div>
    </div>
    
    <div class="chat-panel">
        <div class="chat-header">
            <i class="fas fa-comments"></i> Paddock Live Chat
        </div>
        
        <div class="chat-messages" id="chatMessagesBox">
            </div>
        
        <?php if($isLoggedIn): ?>
        <div class="chat-input-area">
            <input type="text" id="chatInputMsg" placeholder="Szólj hozzá a futamhoz..." onkeypress="if(event.key === 'Enter') sendChat()">
            <button onclick="sendChat()"><i class="fas fa-paper-plane"></i></button>
        </div>
        <?php else: ?>
        <div class="chat-guest-msg">
            A chateléshez <a href="../login/login.html">jelentkezz be</a>!
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
    const rowHeight = 80; 
    let isFetching = false;
    let chatMessageCount = 0; // Figyeljük, van-e új üzenet, hogy le tudjunk pörgetni

    // --- TELEMETRIA JS ---
    async function fetchData() {
        if (isFetching) return;
        isFetching = true;
        
        try {
            const response = await fetch('race_api.php?action=update');
            const text = await response.text(); 
            
            let data;
            try { data = JSON.parse(text); } 
            catch(e) { document.getElementById('loadingMsg').innerHTML = '<span style="color:#e10600;">Hiba a szerver kommunikációban!</span>'; isFetching = false; return; }

            if (data && data.race) {
                renderRace(data);

                if (data.race.status === 'running') {
                    let timeout = data.race.safety_car == "1" ? 15000 : 10000;
                    setTimeout(fetchData, timeout);
                } else if (data.race.status === 'finished') {
                    document.getElementById('raceStatusText').innerHTML = '<i class="fas fa-flag-checkered"></i> RACE FINISHED';
                    document.getElementById('raceStatusText').style.color = '#fff';
                    document.getElementById('raceStatusText').style.borderColor = '#fff';
                    document.getElementById('scBadge').style.display = 'none';
                    renderStandings(data.standings);
                } else {
                    document.getElementById('raceStatusText').innerHTML = '⚠️ RACE STOPPED';
                    document.getElementById('raceStatusText').style.color = 'orange';
                    document.getElementById('raceStatusText').style.borderColor = 'orange';
                    setTimeout(fetchData, 5000);
                }
            }
        } catch (error) {
            console.error("Fetch Hiba:", error);
        } finally {
            isFetching = false;
        }
    }

    function renderRace(data) {
        document.getElementById('currentLap').innerText = data.race.current_lap;
        document.getElementById('totalLaps').innerText = data.race.total_laps;

        const weatherBadge = document.getElementById('weatherBadge');
        if (data.race.weather === 'Rain') {
            weatherBadge.className = 'indicator-badge weather-rain';
            weatherBadge.innerHTML = '<i class="fas fa-cloud-rain"></i> WET TRACK';
        } else {
            weatherBadge.className = 'indicator-badge weather-sunny';
            weatherBadge.innerHTML = '<i class="fas fa-sun"></i> DRY TRACK';
        }

        const scBadge = document.getElementById('scBadge');
        const isSC = data.race.safety_car == "1";
        scBadge.style.display = isSC ? 'flex' : 'none';

        const grid = document.getElementById('raceGrid');
        const loadingMsg = document.getElementById('loadingMsg');
        if (loadingMsg) loadingMsg.remove();

        grid.style.height = (data.grid.length * rowHeight) + 'px';

        data.grid.forEach((driver, index) => {
            let tyreClass = 'tyre-S'; let tyreLetter = 'S';
            if (driver.tyre_type === 'Medium') { tyreClass = 'tyre-M'; tyreLetter = 'M'; }
            if (driver.tyre_type === 'Hard') { tyreClass = 'tyre-H'; tyreLetter = 'H'; }
            if (driver.tyre_type === 'Inter') { tyreClass = 'tyre-I'; tyreLetter = 'I'; }
            if (driver.tyre_type === 'Wet') { tyreClass = 'tyre-W'; tyreLetter = 'W'; }

            let wearColorClass = driver.tyre_wear > 60 ? 'wear-high' : '';
            let gap = ''; let posDisplay = ''; let statusHtml = '';

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
                    if (isSC) {
                        gap = driver.position === 1 ? 'SC' : '<span class="gap-sc">SC QUEUE</span>';
                    } else {
                        gap = driver.position === 1 ? 'Leader' : '+' + (driver.position * 1.5 + Math.random()).toFixed(1) + 's';
                    }
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

            row.classList.remove('leader', 'sc-mode');
            if (driver.status !== 'DNF') {
                if (isSC) { row.classList.add('sc-mode'); } 
                else if (driver.position === 1) { row.classList.add('leader'); }
            }

            let rawDriverImg = driver.driver_image ? driver.driver_image.trim() : '';
            let imgPath = '../drivers/default.png';
            if (rawDriverImg !== '') {
                imgPath = rawDriverImg.includes('/') ? `../${rawDriverImg}` : `../drivers/${rawDriverImg}`;
            }

            let rawLogo = driver.logo ? driver.logo.trim() : '';
            let logoPath = '';
            if (rawLogo !== '') {
                logoPath = rawLogo.includes('/') ? `../${rawLogo}` : `../logos/${rawLogo}`;
            }

            row.innerHTML = `
                <div class="pos-num">${posDisplay}</div>
                <div class="driver-info">
                    <div class="driver-pic-box">
                        <img src="${imgPath}" class="driver-portrait" onerror="this.style.opacity=0;">
                    </div>
                    <div style="display:flex; flex-direction:column; justify-content:center;">
                        <span class="driver-name">${driver.name}</span>
                        <span class="driver-abbr">${driver.abbreviation}</span>
                    </div>
                </div>
                <div class="driver-info">
                    <div class="team-logo-box"><img src="${logoPath}" class="team-logo-small" onerror="this.style.opacity=0;"></div>
                    <span class="team-name">${driver.team_name}</span>
                </div>
                <div class="tyre-container">
                    <div class="tyre-badge ${tyreClass}">${tyreLetter}</div>
                    <div class="wear-bar-bg" title="Kopás: ${driver.tyre_wear}%"><div class="wear-bar-fill ${wearColorClass}" style="width: ${driver.tyre_wear}%"></div></div>
                </div>
                <div class="gap-text">${gap}</div>
                <div>${statusHtml}</div>
            `;
        });
    }

    function renderStandings(standings) {
        if (!standings || standings.length === 0) return;
        document.getElementById('postRaceStandings').style.display = 'block';
        const tbody = document.getElementById('standingsBody');
        tbody.innerHTML = '';
        standings.forEach(s => {
            let tr = document.createElement('tr');
            if(s.pos === 1) tr.style.background = 'linear-gradient(90deg, rgba(212,175,55,0.1), transparent)';
            tr.innerHTML = `<td class="${s.pos === 1 ? 'champ-gold' : ''}">${s.pos}.</td><td style="font-weight:bold;">${s.name}</td><td style="color:#888;">${s.team}</td><td style="text-align:right; font-family:monospace; font-size:1.2rem; font-weight:bold;" class="${s.pos === 1 ? 'champ-gold' : ''}">${s.points} PTS</td>`;
            tbody.appendChild(tr);
        });
    }

    // --- CHAT JS ---
    async function loadChat() {
        try {
            const res = await fetch('chat_api.php');
            const msgs = await res.json();
            const box = document.getElementById('chatMessagesBox');
            
            if (msgs.length > chatMessageCount) {
                box.innerHTML = '';
                msgs.forEach(m => {
                    box.innerHTML += `
                        <div class="chat-msg">
                            <img src="${m.profile_image}" onerror="this.src='../drivers/default.png'" style="border-color: ${m.color};">
                            <div class="chat-msg-body">
                                <div class="chat-user-info">
                                    <span class="chat-user-name" style="color:${m.color}">${m.username}</span>
                                    <span class="chat-time">${m.time}</span>
                                </div>
                                <div style="word-wrap: break-word;">${m.message}</div>
                            </div>
                        </div>
                    `;
                });
                box.scrollTop = box.scrollHeight; // Legörgetés az aljára
                chatMessageCount = msgs.length;
            }
        } catch(e) { console.log("Chat error:", e); }
    }

    async function sendChat() {
        const input = document.getElementById('chatInputMsg');
        if(!input || input.value.trim() === '') return;
        
        let msg = input.value;
        input.value = '';
        
        await fetch('chat_api.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ message: msg })
        });
        loadChat(); // Azonnali frissítés
    }
    // --- CHAT JS ---
    async function loadChat() {
        try {
            // A getTime() megakadályozza, hogy a böngésző "beragassza" az előző chatet (Cache buster)
            const res = await fetch('chat_api.php?_t=' + new Date().getTime());
            const msgs = await res.json();
            const box = document.getElementById('chatMessagesBox');
            
            if (msgs.length > chatMessageCount) {
                box.innerHTML = '';
                msgs.forEach(m => {
                    box.innerHTML += `
                        <div class="chat-msg">
                            <img src="${m.profile_image}" onerror="this.src='../drivers/default.png'" style="border-color: ${m.color};">
                            <div class="chat-msg-body">
                                <div class="chat-user-info">
                                    <span class="chat-user-name" style="color:${m.color}">${m.username}</span>
                                    <span class="chat-time">${m.time}</span>
                                </div>
                                <div style="word-wrap: break-word;">${m.message}</div>
                            </div>
                        </div>
                    `;
                });
                box.scrollTop = box.scrollHeight; // Legörgetés az aljára
                chatMessageCount = msgs.length;
            }
        } catch(e) { console.log("Chat error:", e); }
    }

    // Indítás
    fetchData();
    loadChat();
    setInterval(loadChat, 3000); // Chat frissítése 3 másodpercenként
    
    
</script>
</body>
</html>