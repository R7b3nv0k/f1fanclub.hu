<?php
session_start();

// --- KONFIGURÁCIÓ & ADATBÁZIS ---
$DB_HOST = "localhost";
$DB_USER = "swmjndga_swmjndga";
$DB_PASS = "Teszt1234!";
$DB_NAME = "swmjndga_f1adat";

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) { die("Adatbázis hiba: " . $conn->connect_error); }

// --- JOGOSULTSÁG ELLENŐRZÉS ---
if (!isset($_SESSION['username'])) { header("Location: /f1fanclub/login/login.html"); exit; }
$currentUser = $_SESSION['username'];

// Admin jog ellenőrzése
$stmt = $conn->prepare("SELECT id, role, profile_image FROM users WHERE username=?");
$stmt->bind_param("s", $currentUser);
$stmt->execute();
$adminData = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$adminData || $adminData['role'] !== 'admin') {
    echo "<h2 style='color:red;text-align:center;margin-top:50px;'>Nincs jogosultságod!</h2>";
    exit;
}

// --- FÜGGVÉNY: LOGOLÁS ---
function logActivity($conn, $user, $action, $details) {
    $ip = $_SERVER['REMOTE_ADDR'];
    $stmt = $conn->prepare("INSERT INTO activity_logs (username, action, details, ip_address) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $user, $action, $details, $ip);
    $stmt->execute();
    $stmt->close();
}

// --- POST KÉRÉSEK KEZELÉSE ---
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. Felhasználó BANOLÁSA (Kirúgás)
    if (isset($_POST['action']) && $_POST['action'] === 'ban_user') {
        $userId = (int)$_POST['user_id'];
        $stmt = $conn->prepare("UPDATE users SET is_banned = 1 WHERE id = ?");
        $stmt->bind_param("i", $userId);
        if($stmt->execute()) {
            logActivity($conn, $currentUser, 'ban_user', "Felhasználó ID ($userId) kitiltva.");
            $message = "Felhasználó sikeresen kitiltva!";
        }
        $stmt->close();
    }

    // 2. Felhasználó VISSZAENGEDÉSE (Unban)
    if (isset($_POST['action']) && $_POST['action'] === 'unban_user') {
        $userId = (int)$_POST['user_id'];
        $stmt = $conn->prepare("UPDATE users SET is_banned = 0 WHERE id = ?");
        $stmt->bind_param("i", $userId);
        if($stmt->execute()) {
            logActivity($conn, $currentUser, 'unban_user', "Felhasználó ID ($userId) tiltása feloldva.");
            $message = "Tiltás feloldva!";
        }
        $stmt->close();
    }

    // 3. Szerepkör váltás
    if (isset($_POST['action']) && $_POST['action'] === 'change_role') {
        $userId = (int)$_POST['user_id'];
        $newRole = $_POST['new_role'];
        if (in_array($newRole, ['user', 'admin'])) {
            $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
            $stmt->bind_param("si", $newRole, $userId);
            $stmt->execute();
            logActivity($conn, $currentUser, 'role_change', "Felhasználó ID ($userId) új szerepköre: $newRole");
            $message = "Szerepkör módosítva.";
        }
    }
}

// --- ADATOK LEKÉRÉSE A MEGJELENÍTÉSHEZ ---

// 1. Aktív felhasználók (akik nincsenek bannolva)
$activeUsers = $conn->query("SELECT * FROM users WHERE is_banned = 0 ORDER BY reg_date DESC");

// 2. Kitiltott felhasználók (Kirúgottak)
$bannedUsers = $conn->query("SELECT * FROM users WHERE is_banned = 1 ORDER BY reg_date DESC");

// 3. Aktivitás napló (Utolsó 50 esemény)
$logs = $conn->query("SELECT * FROM activity_logs ORDER BY created_at DESC LIMIT 50");

// 4. Verseny Státusz
$raceStatusResult = $conn->query("SELECT status, current_lap, total_laps FROM race_control WHERE race_id=25 LIMIT 1");
$raceData = $raceStatusResult->fetch_assoc();
$raceStatus = $raceData['status'] ?? 'stopped';

?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>F1 Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #e10600;
            --dark: #15151e;
            --darker: #0f0f15;
            --light: #f0f0f0;
            --sidebar-width: 260px;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--darker);
            color: var(--light);
            margin: 0;
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        /* --- SIDEBAR --- */
        .sidebar {
            width: var(--sidebar-width);
            background-color: var(--dark);
            display: flex;
            flex-direction: column;
            border-right: 1px solid #333;
            padding: 20px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.2rem;
            font-weight: 800;
            color: #fff;
            margin-bottom: 40px;
            text-transform: uppercase;
        }
        .brand img { height: 30px; }

        .menu { list-style: none; padding: 0; }
        .menu li { margin-bottom: 10px; }
        
        .menu-btn {
            width: 100%;
            background: transparent;
            border: none;
            color: #888;
            padding: 12px 15px;
            text-align: left;
            cursor: pointer;
            border-radius: 8px;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: 0.3s;
            font-family: inherit;
            text-decoration: none;
        }

        .menu-btn:hover, .menu-btn.active {
            background-color: var(--primary);
            color: white;
            box-shadow: 0 4px 15px rgba(225, 6, 0, 0.4);
        }

        .user-panel {
            margin-top: auto;
            border-top: 1px solid #333;
            padding-top: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .user-panel img {
            width: 40px; height: 40px; border-radius: 50%; object-fit: cover;
            border: 2px solid var(--primary);
        }

        /* --- MAIN CONTENT --- */
        .main-content {
            flex: 1;
            padding: 30px;
            overflow-y: auto;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        h2 { border-left: 4px solid var(--primary); padding-left: 15px; }

        /* --- TABS SYSTEM --- */
        .tab-content { display: none; animation: fadeIn 0.4s; }
        .tab-content.active { display: block; }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        /* --- CARDS & TABLES --- */
        .card {
            background: var(--dark);
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
            border: 1px solid #333;
            box-shadow: 0 5px 20px rgba(0,0,0,0.5);
        }

        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #333; font-size: 0.9rem; }
        th { color: #888; font-weight: 600; text-transform: uppercase; font-size: 0.8rem; }
        tr:hover { background: rgba(255,255,255,0.02); }

        .btn { padding: 6px 12px; border-radius: 6px; border: none; cursor: pointer; font-size: 0.8rem; color: #fff; transition: 0.2s; }
        .btn-ban { background: #b30000; }
        .btn-ban:hover { background: #ff0000; }
        .btn-unban { background: #008f00; }
        .btn-unban:hover { background: #00b300; }
        .btn-save { background: #333; border: 1px solid #555; }
        .btn-save:hover { background: #555; }

        /* Race Control Specific */
        .race-status { font-size: 1.5rem; font-weight: 800; color: var(--primary); margin-bottom: 20px; display: block; }
        .control-btns { display: flex; gap: 15px; }
        .btn-large { padding: 15px 30px; font-size: 1rem; font-weight: bold; border-radius: 8px; cursor: pointer; border: none; }
        .start { background: #00d2be; color: #000; }
        .stop { background: var(--primary); color: #fff; }
        .start:disabled, .stop:disabled { opacity: 0.3; cursor: not-allowed; }

        /* Activity Log */
        .log-item { padding: 10px 0; border-bottom: 1px solid #222; display: flex; justify-content: space-between; font-size: 0.85rem; }
        .log-action { font-weight: bold; color: var(--primary); }
        .log-time { color: #666; }

        .alert { padding: 15px; background: rgba(0, 210, 190, 0.1); border: 1px solid #00d2be; border-radius: 8px; margin-bottom: 20px; color: #00d2be; }

/* Vissza a főoldalra gomb egyedi stílusa */
        .btn-return {
            color: var(--primary) !important;
            font-weight: 800;
            background: rgba(225, 6, 0, 0.08) !important;
            box-sizing: border-box; /* Megakadályozza, hogy kilógjon a keretből */
        }
        .btn-return:hover {
            background-color: var(--primary) !important;
            color: #fff !important;
            box-shadow: 0 4px 15px rgba(225, 6, 0, 0.4); /* Szép, a többivel megegyező F1-es árnyék */
        }
    </style>
</head>
<body>

    <aside class="sidebar">
        <div class="brand">
            <img src="https://upload.wikimedia.org/wikipedia/commons/3/33/F1.svg" alt="F1">
            <span>ADMIN PADDOCK</span>
        </div>

        <ul class="menu">
            <li>
                <a href="/f1fanclub/index.php" class="menu-btn btn-return">
                    <i class="fas fa-home"></i> Vissza a Főoldalra
                </a>
            </li>
            <hr style="border: 0; border-top: 1px solid #333; margin: 15px 0;">
            
            <li>
                <button class="menu-btn active" onclick="showTab('race')">
                    <i class="fas fa-flag-checkered"></i> Verseny Szimulálás
                </button>
            </li>
            <li>
                <button class="menu-btn" onclick="showTab('users')">
                    <i class="fas fa-users"></i> Felhasználók adatai
                </button>
            </li>
            <li>
                <button class="menu-btn" onclick="showTab('banned')">
                    <i class="fas fa-user-slash"></i> Kirúgottak (Ban)
                </button>
            </li>
            <li>
                <button class="menu-btn" onclick="showTab('activity')">
                    <i class="fas fa-list-alt"></i> Aktivitás Napló
                </button>
            </li>
        </ul>

        <div class="user-panel">
            <img src="/f1fanclub/uploads/<?= htmlspecialchars($adminData['profile_image'] ?? 'default.png') ?>" alt="Admin">
            <div>
                <div style="font-weight:bold;"><?= htmlspecialchars($currentUser) ?></div>
                <a href="/f1fanclub/logout/logout.php" style="color:#888; font-size:0.8rem; text-decoration:none;">Kijelentkezés</a>
            </div>
        </div>
    </aside>

    <main class="main-content">
        
        <?php if($message): ?>
            <div class="alert"><?= $message ?></div>
        <?php endif; ?>

        <div id="tab-race" class="tab-content active">
            <header><h2>Verseny Irányítás</h2></header>
            
            <div class="card" style="text-align: center;">
                <h3>Kanadai Nagydíj 2026</h3>
                <span class="race-status">
                    Állapot: <?= $raceStatus === 'running' ? 'ÉLŐ VERSENY' : 'LEÁLLÍTVA / VÉGE' ?> 
                    (Kör: <?= $raceData['current_lap'] ?>/<?= $raceData['total_laps'] ?>)
                </span>
                
                <div class="control-btns" style="justify-content: center;">
                    <button onclick="controlRace('start')" class="btn-large start" <?= $raceStatus === 'running' ? 'disabled' : '' ?>>
                        <i class="fas fa-play"></i> START
                    </button>
                    <button onclick="controlRace('stop')" class="btn-large stop" <?= $raceStatus !== 'running' ? 'disabled' : '' ?>>
                        <i class="fas fa-stop"></i> STOP
                    </button>
                </div>
                
                <p style="margin-top:20px; color:#888; font-size:0.9rem;">
                    A szimuláció csak akkor fut, ha legalább egy felhasználó nézi a "Live" oldalt.
                </p>
                <a href="../race/live.php" target="_blank" class="btn btn-save" style="margin-top:10px; display:inline-block; text-decoration:none;">
                    Élő közvetítés megnyitása
                </a>
            </div>
        </div>

        <div id="tab-users" class="tab-content">
            <header><h2>Aktív Felhasználók</h2></header>
            
            <div class="card">
                <table>
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Email</th>
                            <th>IP Cím</th> <th>Role</th>
                            <th>Csapat</th>
                            <th>Regisztrált</th>
                            <th>Műveletek</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($u = $activeUsers->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <div style="display:flex; align-items:center; gap:10px;">
                                    <img src="/f1fanclub/uploads/<?= htmlspecialchars($u['profile_image'] ?? 'default.png') ?>" style="width:30px;height:30px;border-radius:50%;">
                                    <?= htmlspecialchars($u['username']) ?>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td style="font-family: monospace; color:#aaa;"><?= htmlspecialchars($u['ip_address'] ?? 'Ismeretlen') ?></td> <td>
                                <form method="POST" style="display:flex; gap:5px;">
                                    <input type="hidden" name="action" value="change_role">
                                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                    <select name="new_role" style="background:#222; color:#fff; border:1px solid #444; border-radius:4px;">
                                        <option value="user" <?= $u['role'] == 'user' ? 'selected' : '' ?>>User</option>
                                        <option value="admin" <?= $u['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                                    </select>
                                    <button type="submit" class="btn btn-save">OK</button>
                                </form>
                            </td>
                            <td><?= htmlspecialchars($u['fav_team']) ?></td>
                            <td><?= date('Y.m.d', strtotime($u['reg_date'])) ?></td>
                            <td>
                                <?php if($u['username'] !== $currentUser): ?>
                                <form method="POST" onsubmit="return confirm('Biztos ki akarod rúgni (ban)?');">
                                    <input type="hidden" name="action" value="ban_user">
                                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                    <button type="submit" class="btn btn-ban"><i class="fas fa-ban"></i> Kirúgás</button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="tab-banned" class="tab-content">
            <header><h2>Kirúgott Felhasználók</h2></header>
            
            <div class="card">
                <?php if($bannedUsers->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Email</th>
                            <th>IP Cím</th> <th>Regisztrált</th>
                            <th>Műveletek</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($b = $bannedUsers->fetch_assoc()): ?>
                        <tr>
                            <td style="color:#ff4444;"><?= htmlspecialchars($b['username']) ?></td>
                            <td><?= htmlspecialchars($b['email']) ?></td>
                            <td style="font-family: monospace; color:#aaa;"><?= htmlspecialchars($b['ip_address'] ?? 'Ismeretlen') ?></td> <td><?= date('Y.m.d', strtotime($b['reg_date'])) ?></td>
                            <td>
                                <form method="POST">
                                    <input type="hidden" name="action" value="unban_user">
                                    <input type="hidden" name="user_id" value="<?= $b['id'] ?>">
                                    <button type="submit" class="btn btn-unban"><i class="fas fa-unlock"></i> Visszaengedés</button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <p style="text-align:center; color:#888;">Nincs kitiltott felhasználó.</p>
                <?php endif; ?>
            </div>
        </div>

        <div id="tab-activity" class="tab-content">
            <header><h2>Oldal Aktivitás (Log)</h2></header>
            
            <div class="card">
                <?php while($log = $logs->fetch_assoc()): ?>
                <div class="log-item">
                    <div>
                        <span style="color:#fff; font-weight:bold;"><?= htmlspecialchars($log['username']) ?></span>
                        <span style="color:#888; margin:0 5px;">&bull;</span>
                        <span class="log-action"><?= htmlspecialchars($log['action']) ?></span>
                        <div style="color:#aaa; margin-top:4px;"><?= htmlspecialchars($log['details']) ?></div>
                    </div>
                    <div style="text-align:right;">
                        <div class="log-time"><?= date('H:i', strtotime($log['created_at'])) ?></div>
                        <div style="font-size:0.7rem; color:#444;"><?= date('M d', strtotime($log['created_at'])) ?></div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>

    </main>

    <script>
        // TABS VÁLTÁS JS
        function showTab(tabName) {
            document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.menu-btn').forEach(el => el.classList.remove('active'));

            document.getElementById('tab-' + tabName).classList.add('active');
            event.currentTarget.classList.add('active');
            
            localStorage.setItem('activeAdminTab', tabName);
        }

        // Oldal betöltéskor utolsó fül megnyitása
        document.addEventListener("DOMContentLoaded", () => {
            const savedTab = localStorage.getItem('activeAdminTab') || 'race';
            // Megkeressük az adott gombot (kivéve a főoldalra visszadobót)
            const btn = document.querySelector(`button[onclick="showTab('${savedTab}')"]`);
            if(btn) btn.click();
        });

        // VERSENY VEZÉRLÉS (API Hívás)
        async function controlRace(action) {
            let confirmMsg = action === 'start' 
                ? "Biztosan INDÍTOD a versenyt? A korábbi eredmények törlődnek!" 
                : "Biztosan LEÁLLÍTOD a versenyt?";
                
            if(confirm(confirmMsg)) {
                try {
                    const response = await fetch('../race/race_api.php?action=' + action);
                    const data = await response.json();
                    
                    alert(data.msg);
                    location.reload(); 
                } catch (error) {
                    alert("Hiba történt: " + error);
                }
            }
        }
    </script>
</body>
</html>