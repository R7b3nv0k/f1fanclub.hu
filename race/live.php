<?php
// live.php - HELYEZD A /race MAPPÁBA!
session_start();
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ÉLŐ: Kanadai Nagydíj 2026</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,400;0,700;0,900;1,400&family=Roboto+Mono:wght@500;700&display=swap" rel="stylesheet">
    <style>
        /* --- MODERN F1 ÉLŐ KÖZVETÍTÉS DIZÁJN --- */
        body {
            background-color: #050505;
            background-image: radial-gradient(circle at 50% 0%, #1a0505 0%, #050505 60%);
            background-attachment: fixed;
            font-family: 'Montserrat', sans-serif;
        }

        .live-container { max-width: 1200px; margin: 40px auto; padding: 20px; }
        
        /* Fejléc */
        .race-header { 
            display: flex; justify-content: space-between; align-items: center; 
            background: rgba(15, 15, 20, 0.8); backdrop-filter: blur(10px);
            padding: 25px 30px; border-radius: 12px; 
            border-bottom: 4px solid #e10600; margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }
        .race-header h1 { margin: 0; font-size: 2rem; font-weight: 900; text-transform: uppercase; letter-spacing: 2px; }
        .lap-counter { font-size: 2.8rem; font-weight: 900; color: #fff; font-family: 'Roboto Mono', monospace; line-height: 1; }
        .lap-label { font-size: 1rem; color: #888; text-transform: uppercase; font-weight: 700; letter-spacing: 1px; margin-bottom: 5px; }
        
        /* --- ANIMÁLT TABELLA (GRID/FLEX) --- */
        .leaderboard-header {
            display: grid;
            grid-template-columns: 80px 2fr 1.5fr 150px 120px 100px;
            padding: 10px 20px;
            color: #666;
            text-transform: uppercase;
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 1px;
            border-bottom: 2px solid #333;
            margin-bottom: 10px;
        }

        .leaderboard-grid {
            position: relative;
            /* A magasságot a JS fogja beállítani a pilóták száma alapján! */
            width: 100%;
        }

        /* AZ EGYES SOROK DIZÁJNJA ÉS AZ ANIMÁCIÓ */
        .telemetry-row {
            position: absolute;
            left: 0;
            right: 0;
            height: 60px; /* Egy sor fix magassága */
            display: grid;
            grid-template-columns: 80px 2fr 1.5fr 150px 120px 100px;
            align-items: center;
            background: linear-gradient(90deg, #151515, #0f0f0f);
            border-radius: 8px;
            border-left: 4px solid transparent;
            padding: 0 20px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
            /* EZ AZ ANIMÁCIÓ LELKE: Amikor a JS módosítja a Y pozíciót, simán átcsúszik */
            transition: transform 0.6s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.4s ease;
        }

        /* 1. Helyezett extra kiemelése */
        .telemetry-row.leader {
            border-left-color: #e10600;
            background: linear-gradient(90deg, rgba(225,6,0,0.1), #0f0f0f);
        }

        /* Cella tartalmak */
        .pos-num { font-weight: 900; font-size: 1.4rem; width: 40px; text-align: center; }
        .driver-info { display: flex; align-items: center; gap: 12px; }
        .driver-name { font-weight: 800; color: #fff; font-size: 1.1rem; }
        .driver-abbr { color: #888; font-size: 0.9rem; font-weight: 700; }
        .team-logo-small { width: 30px; height: 30px; object-fit: contain; }
        .team-name { font-weight: 600; color: #ccc; font-size: 0.9rem; }
        
        .gap-text { font-family: 'Roboto Mono', monospace; color: #e10600; font-weight: 700; font-size: 1.1rem; }
        
        /* Gumi és kopás */
        .tyre-container { display: flex; align-items: center; gap: 10px; }
        .tyre-badge { width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 900; color: #000; font-size: 0.85rem; }
        .tyre-S { background-color: #ff3b30; box-shadow: 0 0 8px rgba(255,59,48,0.5); } 
        .tyre-M { background-color: #ffcc00; box-shadow: 0 0 8px rgba(255,204,0,0.5); } 
        .tyre-H { background-color: #fff; box-shadow: 0 0 8px rgba(255,255,255,0.5); } 
        
        .wear-bar-bg { width: 60px; height: 6px; background: #222; border-radius: 3px; overflow: hidden; }
        .wear-bar-fill { height: 100%; background: #00D2BE; transition: width 0.5s; }
        .wear-high { background: #e10600; }
        
        .status-dnf { color: #e10600; font-weight: 800; background: rgba(225,6,0,0.1); padding: 4px 10px; border-radius: 4px; }
        .status-pit { color: #ffcc00; font-weight: 800; animation: blink 1.5s infinite; background: rgba(255,204,0,0.1); padding: 4px 10px; border-radius: 4px; }
        
        @keyframes blink { 0%, 100% { opacity: 1; } 50% { opacity: 0.4; } }
    </style>
</head>
<body>

<header>
    <div class="logo-title">
        <img src="https://upload.wikimedia.org/wikipedia/commons/3/33/F1.svg" class="f1-logo" alt="F1 Logo">
        <span>LIVE TIMING</span>
    </div>
    <a href="../index.php" class="btn btn-ghost">Vissza a Paddockba</a>
</header>

<div class="live-container">
    <div class="race-header">
        <div>
            <h1>Canadian Grand Prix <span style="color:#e10600;">2026</span></h1>
            <span id="raceStatusText" style="color:#e10600; font-weight:800; font-size: 1.1rem; display:inline-block; margin-top:5px;">● LIVE SESSION</span>
        </div>
        <div style="text-align:right;">
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
    const rowHeight = 70; // 60px magasság + 10px margó a sorok között

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
        
        // Eltüntetjük a betöltés üzenetet
        const loadingMsg = document.getElementById('loadingMsg');
        if(loadingMsg) loadingMsg.remove();

        // Beállítjuk a grid teljes magasságát (hogy ne lógjanak le az elemek)
        grid.style.height = (data.grid.length * rowHeight) + 'px';

        data.grid.forEach((driver, index) => {
            // Gumi stílusok
            let tyreClass = 'tyre-S'; let tyreLetter = 'S';
            if(driver.tyre_type === 'Medium') { tyreClass = 'tyre-M'; tyreLetter = 'M'; }
            if(driver.tyre_type === 'Hard')   { tyreClass = 'tyre-H'; tyreLetter = 'H'; }
            let wearColorClass = driver.tyre_wear > 60 ? 'wear-high' : '';
            
            // Státusz, Pozíció és Gap (Időkülönbség)
            let gap = '';
            let posDisplay = '';
            let statusHtml = '';
            
            if (driver.status === 'DNF') {
                posDisplay = '<span style="color:#e10600; font-size:1.1rem;">OUT</span>';
                gap = driver.gap ? `<span style="color:#666; font-size:0.9rem;">${driver.gap}</span>` : 'Kiesett';
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

            // Kiszámoljuk az új Y pozíciót a sorrend alapján
            let targetY = index * rowHeight;

            // Keresünk létező sort (hogy animálhassuk)
            let row = document.getElementById('driver-row-' + driver.driver_id);

            // Ha még nincs ilyen sor (első betöltés), létrehozzuk
            if (!row) {
                row = document.createElement('div');
                row.id = 'driver-row-' + driver.driver_id;
                row.className = 'telemetry-row';
                grid.appendChild(row);
            }

            // Alkalmazzuk az új Y pozíciót CSS Transformmal (Ettől fog simán fel-le úszni!)
            row.style.transform = `translateY(${targetY}px)`;
            
            // DNF áttetszőség és 1. helyezett kiemelése
            row.style.opacity = driver.status === 'DNF' ? '0.4' : '1';
            if(driver.position === 1 && driver.status !== 'DNF') {
                row.classList.add('leader');
            } else {
                row.classList.remove('leader');
            }

            // Tartalom frissítése a soron belül
            row.innerHTML = `
                <div class="pos-num">${posDisplay}</div>
                <div class="driver-info">
                    <span class="driver-name">${driver.name}</span>
                    <span class="driver-abbr">${driver.abbreviation}</span>
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

    // Indítás
    fetchData();
</script>
</body>
</html>