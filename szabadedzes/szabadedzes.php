<?php
session_start();

$DB_HOST = "localhost";
$DB_USER = "swmjndga_swmjndga";
$DB_PASS = "Teszt1234!";
$DB_NAME = "swmjndga_f1adat";

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) { die("Adatbázis hiba: " . $conn->connect_error); }

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

$profile_image = null; $fav_team = null; $teamColor = '#ffffff';

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
    <title>Free Practice - F1 Fan Club</title>
    <link rel="icon" type="image/svg+xml" href="https://upload.wikimedia.org/wikipedia/commons/3/33/F1.svg">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/f1fanclub/css/style.css?v=<?php echo time(); ?>">
    <style>
        .race-select-container { margin-top: 20px; text-align: left; }
        .race-select-label { color: #888; font-size: 0.8rem; font-weight: 700; letter-spacing: 1px; display: block; margin-bottom: 8px; text-transform: uppercase; }
        .race-select { width: 100%; padding: 12px 15px; background: #111; color: white; border: 1px solid #333; border-radius: 8px; font-family: 'Poppins', sans-serif; font-size: 0.95rem; font-weight: 600; outline: none; transition: 0.3s; cursor: pointer; appearance: none; background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%23e10600%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E'); background-repeat: no-repeat; background-position: right 15px center; background-size: 12px; }
        .race-select:focus, .race-select:hover { border-color: #e10600; box-shadow: 0 0 15px rgba(225,6,0,0.2); }
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
            <a href="/f1fanclub/idomero/idomero.php">Qualifying</a>
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

    <div class="championship-layout full-width">
        <div class="championship-content full-width">
            <div class="control-panel">
                <div class="panel-header">
                    <div class="panel-icon">
                        <i class="fas fa-stopwatch"></i>
                    </div>
                    <h2>FREE PRACTICE <span>RESULTS</span></h2>
                </div>

                <div class="race-select-container">
                    <label class="race-select-label" for="yearSelect">Válaszd ki a szezont</label>
                    <select id="yearSelect" class="race-select"></select>
                </div>

                <div class="race-select-container">
                    <label class="race-select-label" for="raceSelect">Válaszd ki a futamot</label>
                    <select id="raceSelect" class="race-select" disabled>
                        <option>Szezon betöltése...</option>
                    </select>
                </div>

                <div class="current-selection" style="margin-top: 20px;">
                    <div class="selection-info">
                        <span class="selection-year" id="selectedYear">2026</span>
                        <span class="selection-type">
                            <span id="selectedType">FREE PRACTICE</span> SESSION
                        </span>
                    </div>
                    <button class="update-btn" id="updateBtn">
                        <i class="fas fa-sync-alt"></i> UPDATE
                    </button>
                </div>
            </div>

            <div class="standings-card">
                <div class="table-header">
                    <h2>
                        <i class="fas fa-info-circle"></i>
                        <span id="tableTitle">FREE PRACTICE RESULTS</span>
                    </h2>
                    <div class="season-badge" id="seasonBadge">2026 SEASON</div>
                </div>

                <div class="f1-table-wrapper">
                    <table class="f1-table">
                        <thead>
                            <tr>
                                <th style="width: 60px;">POS</th>
                                <th>DRIVER</th>
                                <th>TEAM</th>
                                <th style="text-align:center;">BEST LAP</th>
                                <th style="text-align:center;">GAP</th>
                            </tr>
                        </thead>
                        <tbody id="standingsBody">
                            <tr>
                                <td colspan="5" class="loading-message">
                                    <i class="fas fa-circle-notch fa-spin"></i> Válassz egy futamot és nyomj az Update-re...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            let currentYear = new Date().getFullYear();
            let currentRound = "1"; 
            
            const yearSelect = document.getElementById('yearSelect');
            const raceSelect = document.getElementById('raceSelect');
            const selectedYearSpan = document.getElementById('selectedYear');
            const tableTitle = document.getElementById('tableTitle');
            const seasonBadge = document.getElementById('seasonBadge');
            const standingsBody = document.getElementById('standingsBody');
            const updateBtn = document.getElementById('updateBtn');

            const currentYearNum = new Date().getFullYear();
            for (let year = currentYearNum; year >= 1950; year--) {
                const option = document.createElement('option');
                option.value = year;
                option.textContent = year + " SEASON";
                if (year === currentYear) option.selected = true;
                yearSelect.appendChild(option);
            }

            yearSelect.addEventListener('change', (e) => {
                currentYear = parseInt(e.target.value);
                updateDisplay();
                fetchRacesForYear(currentYear);
            });

            function fetchRacesForYear(year) {
                raceSelect.innerHTML = '<option>Futamok betöltése...</option>';
                raceSelect.disabled = true;

                fetch(`https://api.jolpi.ca/ergast/f1/${year}.json`)
                    .then(res => res.json())
                    .then(data => {
                        const races = data.MRData.RaceTable.Races;
                        if (races && races.length > 0) {
                            raceSelect.innerHTML = '';
                            races.forEach(race => {
                                const option = document.createElement('option');
                                option.value = race.round;
                                option.textContent = `R${race.round} - ${race.raceName} (${race.Circuit.circuitName})`;
                                raceSelect.appendChild(option);
                            });
                            raceSelect.disabled = false;
                            currentRound = raceSelect.value;
                            loadPractice(raceSelect.options[raceSelect.selectedIndex].text);
                        } else {
                            raceSelect.innerHTML = '<option>Nincsenek futam adatok ehhez az évhez.</option>';
                        }
                    })
                    .catch(err => { raceSelect.innerHTML = '<option>Hiba a betöltéskor.</option>'; });
            }

            updateBtn.addEventListener('click', () => {
                if(!raceSelect.disabled) {
                    const raceName = raceSelect.options[raceSelect.selectedIndex].text;
                    loadPractice(raceName);
                }
            });

            raceSelect.addEventListener('change', () => {
                const raceName = raceSelect.options[raceSelect.selectedIndex].text;
                loadPractice(raceName);
            });

            function updateDisplay() {
                selectedYearSpan.textContent = currentYear;
                seasonBadge.textContent = `${currentYear} SEASON`;
            }

            function loadPractice(raceName) {
                const cleanRaceName = raceName.replace(/R\d+ - /, '');
                tableTitle.innerHTML = `${cleanRaceName} <span style="color:#666;">|</span> FREE PRACTICE`;

                // Itt informáljuk a felhasználót, hogy a szabad edzéshez nincs publikus API adat
                standingsBody.innerHTML = `
                    <tr>
                        <td colspan="5" style="text-align:center; padding: 60px 20px; color:#aaa; font-size:1.1rem; line-height:1.6;">
                            <i class="fas fa-exclamation-triangle" style="font-size:2rem; color:#e10600; margin-bottom:15px; display:block;"></i>
                            A hivatalos ingyenes Formula 1 API jelenleg <strong>nem rögzíti és nem szolgáltatja</strong> a szabadedzések (FP1, FP2, FP3) részletes köridejeit visszamenőlegesen.<br>
                            <span style="font-size:0.9rem; color:#666; margin-top:10px; display:block;">Kérjük, térj vissza az Időmérő vagy a Futam menüpontokhoz az élő és hivatalos statisztikákért!</span>
                        </td>
                    </tr>
                `;
            }

            updateDisplay();
            fetchRacesForYear(currentYear); 
        });
    </script>
</body>
</html>