<?php
session_start();

$DB_HOST = "localhost";
$DB_USER = "swmjndga_swmjndga";
$DB_PASS = "Teszt1234!";
$DB_NAME = "swmjndga_f1adat";

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) {
    die("Adatbįzis hiba: " . $conn->connect_error);
}

$isLoggedIn = isset($_SESSION['username']);
$username = $isLoggedIn ? $_SESSION['username'] : null;

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
?>
<!DOCTYPE html>
<html lang="hu">

<head>
    <meta charset="UTF-8">
    <title>Championship Standings - F1 Fan Club</title>
    <link rel="icon" type="image/svg+xml" href="https://upload.wikimedia.org/wikipedia/commons/3/33/F1.svg">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/f1fanclub/css/style.css?v=<?php echo time(); ?>">
    <style>
        /* Additional styles for team-colored backgrounds */
        .f1-row {
            transition: background 0.3s ease;
            background-position: left center;
            background-repeat: no-repeat;
        }

        .f1-row:hover {
            background: rgba(225, 6, 0, 0.1) !important;
        }

        .f1-champion {
            background: linear-gradient(90deg, rgba(212, 175, 55, 0.15) 0%, rgba(212, 175, 55, 0.05) 40%, transparent 100%) !important;
            border-left: 4px solid #d4af37;
        }
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
            <a href="/f1fanclub/Championship/championship.php" class="active">Championship</a>
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

    <!-- Full Width Championship Layout -->
    <div class="championship-layout full-width">
        <!-- Championship Content -->
        <div class="championship-content full-width">

            <!-- Control Panel -->
            <div class="control-panel">
                <div class="panel-header">
                    <div class="panel-icon">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <h2>CHAMPIONSHIP <span>CONTROL CENTER</span></h2>
                </div>

                <!-- Driver/Constructor Toggle -->
                <div class="championship-toggle" id="championshipToggle">
                    <button class="toggle-btn active" data-type="drivers">
                        <span class="toggle-text">
                            <i class="fas fa-user"></i> DRIVERS
                        </span>
                        <span class="toggle-glow"></span>
                    </button>
                    <button class="toggle-btn" data-type="constructors">
                        <span class="toggle-text">
                            <i class="fas fa-building"></i> CONSTRUCTORS
                        </span>
                        <span class="toggle-glow"></span>
                    </button>
                </div>

                <!-- Year Grid -->
                <div class="year-grid" id="yearGrid">
                    <!-- Years will be populated by JavaScript -->
                </div>

                <!-- Current Selection Display -->
                <div class="current-selection">
                    <div class="selection-info">
                        <span class="selection-year" id="selectedYear">2025</span>
                        <span class="selection-type">
                            <span id="selectedType">DRIVERS</span> CHAMPIONSHIP
                        </span>
                    </div>
                    <button class="update-btn" id="updateBtn">
                        <i class="fas fa-sync-alt"></i> UPDATE
                    </button>
                </div>
            </div>

            <!-- Standings Table -->
            <div class="standings-card">
                <div class="table-header">
                    <h2>
                        <i class="fas fa-flag-checkered"></i>
                        <span id="tableTitle">DRIVERS CHAMPIONSHIP 2025</span>
                    </h2>
                    <div class="season-badge" id="seasonBadge">2025 SEASON</div>
                </div>

                <div class="f1-table-wrapper">
                    <table class="f1-table">
                        <thead>
                            <tr>
                                <th>POS</th>
                                <th id="col1Header">DRIVER</th>
                                <th id="col2Header">TEAM</th>
                                <th style="text-align:right;">PTS</th>
                            </tr>
                        </thead>
                        <tbody id="standingsBody">
                            <tr>
                                <td colspan="4" class="loading-message">
                                    <i class="fas fa-circle-notch"></i> Loading championship data...
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
            // State management
            let currentYear = new Date().getFullYear();
            let currentType = 'drivers';
            
            const yearGrid = document.getElementById('yearGrid');
            const selectedYearSpan = document.getElementById('selectedYear');
            const selectedTypeSpan = document.getElementById('selectedType');
            const tableTitle = document.getElementById('tableTitle');
            const seasonBadge = document.getElementById('seasonBadge');
            const standingsBody = document.getElementById('standingsBody');
            const col1Header = document.getElementById('col1Header');
            const col2Header = document.getElementById('col2Header');
            
            const toggleBtns = document.querySelectorAll('.championship-toggle .toggle-btn');
            const updateBtn = document.getElementById('updateBtn');

            // Helper function to get team colors
            function getTeamColor(teamName) {
                const teamColors = {
                    'Red Bull': '#1E41FF',
                    'Ferrari': '#DC0000',
                    'Mercedes': '#00D2BE',
                    'McLaren': '#FF8700',
                    'Aston Martin': '#006F62',
                    'Alpine': '#0090FF',
                    'Williams': '#00A0DE',
                    'RB': '#2b2bff',
                    'Racing Bulls': '#2b2bff',
                    'Audi': '#e3000f',
                    'Haas F1 Team': '#B6BABD',
                    'Haas': '#B6BABD',
                    'Cadillac': '#B6BABD',
                    'Alfa Romeo': '#900000',
                    'AlphaTauri': '#2b2b2b',
                    'Lotus': '#FFB800',
                    'Renault': '#FFD800',
                    'BMW': '#0066FF',
                    'Toyota': '#CC0000',
                    'Brawn GP': '#C0C0C0',
                    'Benetton': '#00A65E',
                    'Tyrrell': '#004586',
                    'BRM': '#800000',
                    'Cooper': '#004225',
                    'Maserati': '#0047AB',
                    'Vanwall': '#006633',
                    'Kurtis Kraft': '#8B4513',
                    'Epperly': '#CD7F32',
                    'Watson': '#4682B4',
                    'Phillips': '#708090',
                    'Lesovsky': '#8B4513',
                    'Trevis': '#2F4F4F',
                    'Sutton': '#DAA520',
                    'Blanchard': '#708090',
                    'Langley': '#696969',
                    'Pankratz': '#8B4513',
                    'Adams': '#708090',
                    'JBW': '#800020',
                    'Stebro': '#708090',
                    'Scirocco': '#808000',
                    'Roe': '#800080',
                    'Fry': '#DAA520',
                    'Gilby': '#708090',
                    'EMW': '#003399',
                    'Gordini': '#0047AB',
                    'ERA': '#800000',
                    'HWM': '#0066CC',
                    'Connaught': '#004225',
                    'Alta': '#800080',
                    'OSCA': '#FF4500',
                    'Simca-Gordini': '#0047AB',
                    'Lancia': '#0000FF',
                    'Bugatti': '#002366',
                    'Aston Butterworth': '#006F62',
                    'Frazer Nash': '#0066CC',
                    'AAR': '#C0C0C0',
                    'Eagle': '#FFD700',
                    'Shadow': '#2F4F4F',
                    'March': '#FF4500',
                    'Ligier': '#0033FF',
                    'Minardi': '#FFFF00',
                    'Jaguar': '#006400',
                    'Super Aguri': '#DC143C',
                    'Spyker': '#FFA500',
                    'Force India': '#FF4F00',
                    'Racing Point': '#F596C8',
                    'Sauber': '#0066FF',
                    'HRT': '#A9A9A9',
                    'Caterham': '#008000',
                    'Marussia': '#800000',
                    'Manor': '#2F4F4F',
                    'Virgin': '#FF6600',
                    'Lotus F1': '#FFB800',
                    'Toro Rosso': '#0033FF',
                    'Stewart': '#003366',
                    'Prost': '#0000FF',
                    'Arrows': '#FFD700',
                    'Osella': '#FF0000',
                    'Ensign': '#0000FF',
                    'Theodore': '#FFD700',
                    'ATS': '#FF0000',
                    'RAM': '#0000FF',
                    'Zakspeed': '#FFD700',
                    'Dallara': '#DC143C',
                    'Coloni': '#006400',
                    'EuroBrun': '#FFD700',
                    'Life': '#800080',
                    'Onyx': '#C0C0C0',
                    'Scuderia Italia': '#0066FF',
                    'Andrea Moda': '#FF0000',
                    'Fondmetal': '#FFD700',
                    'Footwork': '#0000FF',
                    'Pacific': '#0066FF',
                    'Simtek': '#FF0000',
                    'Forti': '#0066FF',
                    'Lola': '#FF0000',
                    'Mastercard Lola': '#0000FF',
                    'Stewart Grand Prix': '#003366',
                    'BAR': '#0000FF',
                    'Jordan': '#FFFF00',
                    'MF1': '#FFA500',
                    'Midland': '#FFA500',
                    'Spyker MF1': '#FFA500',
                    'HRT F1': '#A9A9A9',
                    'Caterham F1': '#008000',
                    'Marussia F1': '#800000',
                    'Manor Marussia': '#2F4F4F',
                    'Racing Bulls F1': '#2b2bff',
                    'Cadillac F1': '#1b1b1b'
                };
                
                // Try exact match first
                if (teamColors[teamName]) {
                    return teamColors[teamName];
                }
                
                // Case-insensitive partial match
                for (let key in teamColors) {
                    if (teamName.toLowerCase().includes(key.toLowerCase()) || 
                        key.toLowerCase().includes(teamName.toLowerCase())) {
                        return teamColors[key];
                    }
                }
                
                // Default color if no match found
                return '#ffffff';
            }

            // Generate years from 1950 to current
            const currentYearNum = new Date().getFullYear();
            const years = [];
            for (let year = currentYearNum; year >= 1950; year--) {
                years.push(year);
            }

            // Split years into chunks of 6
            const yearChunks = [];
            for (let i = 0; i < years.length; i += 6) {
                yearChunks.push(years.slice(i, i + 6));
            }
            let currentChunkIndex = 0;

            // Render year grid
            function renderYearGrid(chunkIndex) {
                const chunk = yearChunks[chunkIndex];
                if (!chunk) return;

                let html = '';
                
                // Previous navigation
                html += `
                    <div class="year-item year-nav prev-year" ${chunkIndex === 0 ? 'style="opacity:0.5; cursor:not-allowed;"' : ''}>
                        <i class="fas fa-chevron-left"></i>
                        <span class="year-label">PREVIOUS</span>
                    </div>
                `;
                
                // Year items
                chunk.forEach(year => {
                    const isActive = year === currentYear;
                    html += `
                        <div class="year-item ${isActive ? 'active' : ''}" data-year="${year}">
                            <div class="year-glow"></div>
                            <span class="year-value">${year}</span>
                            <span class="year-label">SEASON</span>
                        </div>
                    `;
                });
                
                // Next navigation
                html += `
                    <div class="year-item year-nav next-year" ${chunkIndex === yearChunks.length - 1 ? 'style="opacity:0.5; cursor:not-allowed;"' : ''}>
                        <i class="fas fa-chevron-right"></i>
                        <span class="year-label">NEXT</span>
                    </div>
                `;
                
                yearGrid.innerHTML = html;

                // Add click handlers to year items
                document.querySelectorAll('.year-item:not(.year-nav)').forEach(item => {
                    item.addEventListener('click', () => {
                        const year = parseInt(item.getAttribute('data-year'));
                        if (year !== currentYear) {
                            document.querySelectorAll('.year-item').forEach(y => y.classList.remove('active'));
                            item.classList.add('active');
                            currentYear = year;
                            updateDisplay();
                        }
                    });
                });

                // Navigation handlers
                const prevBtn = document.querySelector('.year-nav.prev-year');
                const nextBtn = document.querySelector('.year-nav.next-year');
                
                if (prevBtn && chunkIndex > 0) {
                    prevBtn.addEventListener('click', () => {
                        if (chunkIndex > 0) {
                            currentChunkIndex--;
                            renderYearGrid(currentChunkIndex);
                        }
                    });
                }
                
                if (nextBtn && chunkIndex < yearChunks.length - 1) {
                    nextBtn.addEventListener('click', () => {
                        if (chunkIndex < yearChunks.length - 1) {
                            currentChunkIndex++;
                            renderYearGrid(currentChunkIndex);
                        }
                    });
                }
            }

            // Toggle handlers for championship
            toggleBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const type = this.getAttribute('data-type');
                    
                    toggleBtns.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    
                    currentType = type;
                    updateDisplay();
                });
            });

            // Update button
            updateBtn.addEventListener('click', () => {
                loadStandings(currentYear, currentType);
            });

            // Update display
            function updateDisplay() {
                selectedYearSpan.textContent = currentYear;
                selectedTypeSpan.textContent = currentType.toUpperCase();
                seasonBadge.textContent = `${currentYear} SEASON`;
                
                document.querySelectorAll('.year-item[data-year]').forEach(item => {
                    const year = parseInt(item.getAttribute('data-year'));
                    if (year === currentYear) {
                        item.classList.add('active');
                    } else {
                        item.classList.remove('active');
                    }
                });
                
                loadStandings(currentYear, currentType);
            }

            // Load standings
            function loadStandings(year, type) {
                if (type === 'drivers') {
                    tableTitle.textContent = `DRIVERS CHAMPIONSHIP ${year}`;
                    col1Header.textContent = 'DRIVER';
                    col2Header.textContent = 'TEAM';
                } else {
                    tableTitle.textContent = `CONSTRUCTORS CHAMPIONSHIP ${year}`;
                    col1Header.textContent = 'CONSTRUCTOR';
                    col2Header.textContent = 'NATIONALITY';
                }

                standingsBody.innerHTML = `
                    <tr>
                        <td colspan="4" class="loading-message">
                            <i class="fas fa-circle-notch"></i> Loading ${year} ${type} championship data...
                        </td>
                    </tr>
                `;

                if (type === 'drivers') {
                    fetch(`https://api.jolpi.ca/ergast/f1/${year}/driverStandings.json`)
                        .then(res => res.json())
                        .then(data => {
                            const list = data.MRData.StandingsTable.StandingsLists[0]?.DriverStandings || [];
                            
                            if (list.length === 0) {
                                standingsBody.innerHTML = '<tr><td colspan="4" style="text-align:center; padding:40px; color:#666;">No data available for this season.</td></tr>';
                                return;
                            }

                            let html = '';
                            list.forEach(d => {
                                const isChampion = d.position === "1";
                                const teamName = d.Constructors?.[0]?.name || "Private";
                                
                                // Get team color for background gradient
                                const teamColor = getTeamColor(teamName);
                                
                                // Create gradient based on team color (light fade from left)
                                const gradientStyle = `linear-gradient(90deg, ${teamColor}20 0%, transparent 40%)`;
                                
                                // Use champion class for gold border, team color for others
                                const rowClass = isChampion ? 'f1-row f1-champion' : 'f1-row';
                                const trophy = isChampion ? '<i class="fas fa-trophy" style="color:#d4af37; margin-right:8px;"></i>' : '';

                                html += `
                                    <tr class="${rowClass}" style="background: ${gradientStyle};">
                                        <td class="f1-pos">${d.position}</td>
                                        <td class="f1-name">${trophy}${d.Driver.givenName} <span>${d.Driver.familyName}</span></td>
                                        <td class="f1-team" style="color: ${teamColor};">${teamName}</td>
                                        <td class="f1-points">${d.points}</td>
                                    </tr>
                                `;
                            });
                            standingsBody.innerHTML = html;
                        })
                        .catch(error => {
                            standingsBody.innerHTML = '<tr><td colspan="4" style="text-align:center; padding:40px; color:#e10600;">Error loading data. Please try again.</td></tr>';
                        });
                } else {
                    if (year < 1958) {
                        standingsBody.innerHTML = '<tr><td colspan="4" style="text-align:center; padding:40px; color:#666;">Constructors Championship was not awarded before 1958.</td></tr>';
                        return;
                    }

                    fetch(`https://api.jolpi.ca/ergast/f1/${year}/constructorStandings.json`)
                        .then(res => res.json())
                        .then(data => {
                            const list = data.MRData.StandingsTable.StandingsLists[0]?.ConstructorStandings || [];
                            
                            if (list.length === 0) {
                                standingsBody.innerHTML = '<tr><td colspan="4" style="text-align:center; padding:40px; color:#666;">No data available for this season.</td></tr>';
                                return;
                            }

                            let html = '';
                            list.forEach(c => {
                                const isChampion = c.position === "1";
                                const constName = c.Constructor?.name || "Unknown";
                                const constNat = c.Constructor?.nationality || "";
                                
                                // Get team color for background gradient
                                const teamColor = getTeamColor(constName);
                                
                                // Create gradient based on team color (light fade from left)
                                const gradientStyle = `linear-gradient(90deg, ${teamColor}20 0%, transparent 40%)`;
                                
                                const rowClass = isChampion ? 'f1-row f1-champion' : 'f1-row';
                                const trophy = isChampion ? '<i class="fas fa-trophy" style="color:#d4af37; margin-right:8px;"></i>' : '';

                                html += `
                                    <tr class="${rowClass}" style="background: ${gradientStyle};">
                                        <td class="f1-pos">${c.position}</td>
                                        <td class="f1-name">${trophy}<span style="color: ${teamColor};">${constName}</span></td>
                                        <td class="f1-team">${constNat}</td>
                                        <td class="f1-points">${c.points}</td>
                                    </tr>
                                `;
                            });
                            standingsBody.innerHTML = html;
                        })
                        .catch(error => {
                            standingsBody.innerHTML = '<tr><td colspan="4" style="text-align:center; padding:40px; color:#e10600;">Error loading data. Please try again.</td></tr>';
                        });
                }
            }

            // Initial render
            renderYearGrid(0);
            loadStandings(currentYear, currentType);
        });
    </script>
</body>

</html>