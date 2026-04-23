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
        case 'Red Bull': return '#1E41FF';
        case 'Ferrari': return '#DC0000';
        case 'Mercedes': return '#00D2BE';
        case 'McLaren': return '#FF8700';
        case 'Aston Martin': return '#006F62';
        case 'Alpine': return '#0090FF';
        case 'Williams': return '#00A0DE';
        case 'RB': return '#2b2bff';
        case 'Audi': return '#e3000f';
        case 'Haas F1 Team': return '#B6BABD';
        case 'Cadillac': return '#1b1b1b';
        default: return '#e10600';
    }
}

$profile_image = null;
$fav_team = null;
$teamColor = '#ffffff';
$isAdmin = false;

if ($isLoggedIn) {
    $stmt = $conn->prepare("SELECT profile_image, fav_team, role FROM users WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    $profile_image = $row['profile_image'] ?? null;
    $fav_team = $row['fav_team'] ?? null;
    $teamColor = getTeamColor($fav_team);
    $isAdmin = !empty($row['role']) && $row['role'] === 'admin';
    $stmt->close();
}

/* ==== ADMINOK LEKÉRÉSE ==== */
$sqlAdmins = "SELECT username, profile_image, fav_team FROM users WHERE role = 'admin'";
$resultAdmins = $conn->query($sqlAdmins);
$adminUsers = [];

if ($resultAdmins && $resultAdmins->num_rows > 0) {
    while ($adminRow = $resultAdmins->fetch_assoc()) {
        $adminUsers[] = $adminRow;
    }
}
?>
<!DOCTYPE html>
<html lang="hu">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Rólunk & Működés - F1 Fan Club</title>
    <link rel="icon" type="image/svg+xml" href="https://upload.wikimedia.org/wikipedia/commons/3/33/F1.svg">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* === ALAP BEÁLLÍTÁSOK === */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #0a0a0a; color: white; font-family: 'Poppins', sans-serif; min-height: 100vh; position: relative; overflow-x: hidden; }
        
        body::before {
            content: ""; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: radial-gradient(circle at 20% 50%, rgba(225, 6, 0, 0.05) 0%, transparent 50%),
                        radial-gradient(circle at 80% 80%, rgba(225, 6, 0, 0.05) 0%, transparent 50%);
            pointer-events: none; z-index: -1;
        }

        .bg-lines {
            position: fixed; width: 200%; height: 200%;
            background: repeating-linear-gradient(60deg, rgba(225, 6, 0, 0.03) 0px, rgba(225, 6, 0, 0.03) 2px, transparent 2px, transparent 10px);
            animation: slide 10s linear infinite; opacity: 0.3; z-index: -1; top: 0; left: 0;
        }
        @keyframes slide { from { transform: translateX(0); } to { transform: translateX(-200px); } }
        
        /* === HEADER === */
        header { background-color: #0a0a0a; border-bottom: 2px solid rgba(225, 6, 0, 0.3); padding: 0 40px; height: 80px; display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 1000; box-shadow: 0 4px 20px rgba(0,0,0,0.5); }
        .logo-title { display: flex; align-items: center; gap: 12px; font-size: 1.5rem; font-weight: 800; color: #fff; text-transform: uppercase; letter-spacing: 1px; }
        .logo-title img { width: 40px; height: auto; filter: brightness(0) invert(1); }
        .logo-title span { display: block; margin-top: 4px; }

        /* Hamburger menu button */
        .hamburger {
            display: none;
            background: none;
            border: none;
            color: white;
            font-size: 28px;
            cursor: pointer;
            padding: 10px;
            z-index: 1001;
            transition: color 0.3s ease;
        }

        .hamburger:hover {
            color: #e10600;
        }
        
        nav { display: flex; gap: 5px; margin: 0 20px; }
        nav a { font-weight: 600; font-size: 0.9rem; text-transform: uppercase; padding: 8px 16px; border-radius: 4px; color: #ffffff !important; text-decoration: none; transition: all 0.2s ease; letter-spacing: 0.5px; opacity: 0.9; }
        nav a:hover, nav a.active { color: #e10600 !important; opacity: 1; background: rgba(225, 6, 0, 0.1); }

        .auth { display: flex; align-items: center; gap: 10px; }
        
        /* Dropdown styles */
        .dropdown-container { position: relative; display: inline-block; }
        .welcome { display: flex; align-items: center; gap: 10px; font-size: 0.9rem; margin-right: 10px; padding: 5px 12px; background: rgba(255, 255, 255, 0.05); border-radius: 30px; border: 1px solid rgba(225, 6, 0, 0.2); cursor: pointer; transition: all 0.2s ease; }
        .welcome:hover { background: rgba(225, 6, 0, 0.15); border-color: #e10600; }
        .welcome img.avatar { width: 32px; height: 32px; border-radius: 50%; object-fit: cover; border: 2px solid #e10600; transition: transform 0.3s; }
        .welcome img.avatar:hover { transform: scale(1.1); }
        .welcome-text { color: #ccc; }
        .dropdown-arrow-icon { margin-left: 6px; font-size: 0.7rem; transition: transform 0.2s; color: #e10600; }
        .dropdown-container.open .dropdown-arrow-icon { transform: rotate(180deg); }
        
        .dropdown-menu-modern {
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            background: linear-gradient(145deg, #111111, #1a1a1f);
            backdrop-filter: blur(12px);
            border-radius: 16px;
            border: 1px solid rgba(225, 6, 0, 0.4);
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.6);
            min-width: 240px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-8px);
            transition: all 0.2s cubic-bezier(0.2, 0.9, 0.4, 1.1);
            z-index: 1050;
        }
        .dropdown-container.open .dropdown-menu-modern {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        .dropdown-menu-modern a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            color: #eee;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.2s;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        .dropdown-menu-modern a:last-child { border-bottom: none; }
        .dropdown-menu-modern a:hover {
            background: rgba(225, 6, 0, 0.2);
            color: white;
            padding-left: 24px;
        }
        .dropdown-menu-modern i { width: 24px; color: #e10600; font-size: 1.1rem; }
        .dropdown-divider { height: 1px; background: rgba(255, 255, 255, 0.1); margin: 6px 0; }
        .admin-badge {
            position: absolute;
            right: 15px;
            background: #e10600;
            color: white;
            font-size: 0.65rem;
            padding: 2px 8px;
            border-radius: 20px;
            font-weight: 600;
        }
        .clickable-user { cursor: pointer; transition: opacity 0.2s; }
        .clickable-user:hover { opacity: 0.8; }
        
        .auth .btn { display: inline-block; padding: 8px 20px; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; color: #fff; background-color: transparent; border: 1px solid rgba(225, 6, 0, 0.5); border-radius: 30px; cursor: pointer; transition: all 0.3s ease; text-decoration: none; }
        .auth .btn:hover { background-color: #e10600; border-color: #e10600; transform: translateY(-2px); box-shadow: 0 4px 15px rgba(225, 6, 0, 0.4); }

        /* Desktop navigation - always visible */
        @media (min-width: 993px) {
            nav {
                display: flex !important;
            }
        }

        /* Mobile navigation - hamburger mode */
        @media (max-width: 992px) {
            .hamburger {
                display: block;
            }
            
            /* Hide the logo on mobile */
            .logo-title {
                display: none;
            }
            
            nav {
                display: none;
                position: absolute;
                top: 80px;
                left: 0;
                right: 0;
                background: #0a0a0a;
                border-bottom: 2px solid #e10600;
                flex-direction: column;
                gap: 0;
                margin: 0;
                z-index: 1000;
                box-shadow: 0 10px 20px rgba(0,0,0,0.5);
            }
            
            nav.open {
                display: flex;
            }
            
            nav a {
                padding: 15px 20px;
                margin: 0;
                border-bottom: 1px solid rgba(255,255,255,0.1);
                text-align: center;
                font-size: 1rem;
            }
            
            nav a:last-child {
                border-bottom: none;
            }
            
            header {
                position: sticky;
                top: 0;
                flex-wrap: nowrap;
                justify-content: flex-start;
                gap: 15px;
                padding: 0 20px;
            }
            
            .hamburger {
                margin-right: auto;
            }
            
            .auth {
                margin-left: 0;
            }
        }

        @media (max-width: 768px) {
            .auth {
                flex-wrap: wrap;
                justify-content: flex-end;
            }
            .welcome {
                width: 100%;
                justify-content: center;
                margin-right: 0;
                margin-bottom: 5px;
            }
            .container {
                padding: 0 15px;
                margin: 30px auto;
            }
            .about-card {
                padding: 25px 20px;
            }
            .large-card h3 {
                font-size: 1.2rem;
            }
            .large-card p {
                font-size: 0.9rem;
            }
            .admin-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            .admin-img {
                width: 100px;
                height: 100px;
            }
            .admin-name {
                font-size: 1.1rem;
            }
            .admin-desc {
                font-size: 0.85rem;
            }
            .section-title {
                font-size: 1.6rem;
            }
        }
        
        @media (max-width: 576px) {
            .hamburger {
                font-size: 24px;
                padding: 8px;
            }
            header {
                padding: 0 15px;
            }
            .auth .btn {
                padding: 6px 12px;
                font-size: 0.7rem;
            }
            nav a {
                padding: 12px 15px;
                font-size: 0.85rem;
            }
            .section-title {
                font-size: 1.3rem;
            }
            .large-card h3 {
                font-size: 1rem;
            }
            .large-card h3 i {
                font-size: 1.2rem;
            }
            .large-card p {
                font-size: 0.85rem;
            }
        }
        
        /* === RÓLUNK OLDAL KINÉZET === */
        .container { max-width: 1200px; margin: 50px auto; padding: 0 20px; }
        
        .section-header { text-align: center; margin-bottom: 40px; }
        .section-title { font-size: 2.2rem; color: #e10600; font-weight: 900; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 15px; display: inline-block; position: relative; }
        .section-title::after { content: ""; position: absolute; bottom: -8px; left: 0; width: 100%; height: 3px; background: linear-gradient(90deg, transparent 0%, #e10600 20%, #e10600 80%, transparent 100%); }
        
        /* Alap kártya stílus */
        .about-card {
            background: linear-gradient(145deg, #111111 0%, #1a1a1a 100%);
            border-radius: 20px;
            padding: 35px;
            border: 1px solid rgba(225, 6, 0, 0.3);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.6), 0 0 20px rgba(225, 6, 0, 0.05);
            position: relative;
            overflow: hidden;
            transition: transform 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .about-card::before {
            content: ""; position: absolute; top: 0; left: 0; right: 0; height: 3px;
            background: linear-gradient(90deg, transparent 0%, var(--card-color, #e10600) 50%, transparent 100%);
            z-index: 2;
        }

        /* 1. Nagy kártya (Az oldalról) */
        .large-card { margin-bottom: 40px; }
        .large-card h3 { color: #fff; font-size: 1.5rem; margin-bottom: 20px; text-transform: uppercase; font-weight: 800; display: flex; align-items: center; gap: 10px; }
        .large-card h3 i { color: #e10600; }
        .large-card p { color: #ccc; line-height: 1.8; font-size: 1.05rem; margin-bottom: 15px; }

        /* 2. Adminok gridje */
        .admin-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }

        /* 3. Kis kártyák (Adminok) */
        .admin-card { text-align: center; border-width: 2px; }
        .admin-card:hover { transform: translateY(-5px); }
        
        .admin-img {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid var(--card-color, #e10600);
            padding: 4px;
            background: #111;
            margin: 0 auto 20px;
        }

        .admin-name { font-size: 1.4rem; color: #fff; font-weight: 800; margin-bottom: 5px; text-transform: uppercase; }
        
        .admin-role {
            display: inline-block;
            background: rgba(255, 255, 255, 0.05);
            color: var(--card-color, #e10600);
            padding: 4px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 20px;
            border: 1px solid var(--card-color, #e10600);
        }

        .admin-desc { color: #aaa; font-size: 0.95rem; line-height: 1.6; }
        
        /* === FOOTER === */
        .site-footer { background: linear-gradient(145deg, #0a0a0a 0%, #111 100%); padding: 40px 40px 20px; border-top: 4px solid #e10600; margin-top: 60px; position: relative; }
        .site-footer::before { content: ""; position: absolute; top: -4px; left: 0; right: 0; height: 4px; background: linear-gradient(90deg, transparent 0%, #e10600 20%, #e10600 80%, transparent 100%); }
        .footer-container { max-width: 1400px; margin: 0 auto; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 30px; }
        .footer-section { display: flex; flex-direction: column; gap: 8px; }
        .footer-logo { display: flex; align-items: center; gap: 8px; font-size: 1.2rem; font-weight: 700; color: white; text-transform: uppercase; }
        .footer-logo img { width: 30px; filter: drop-shadow(0 0 8px #e10600); }
        .footer-section h3 { color: #e10600; font-size: 1rem; font-weight: 600; text-transform: uppercase; margin-bottom: 5px; }
        .footer-section p, .footer-section a { font-size: 0.85rem; color: #aaa; text-decoration: none; transition: 0.3s; }
        .footer-section a:hover { color: #e10600; transform: translateX(3px); }
        .social-links { display: flex; gap: 10px; margin-top: 5px; }
        .social-icon { width: 20px; height: 20px; fill: #aaa; transition: 0.3s; }
        .social-icon:hover { fill: #e10600; transform: scale(1.1); }
        .copyright { text-align: center; color: #555; font-size: 0.8rem; border-top: 1px solid #222; padding-top: 20px; margin-top: 20px; }

        @media (max-width: 992px) { 
            .footer-container {
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 20px;
            }
        }
        @media (max-width: 768px) { 
            .footer-container {
                grid-template-columns: 1fr;
                text-align: center;
            }
            .footer-logo {
                justify-content: center;
            }
            .social-links {
                justify-content: center;
            }
        }
<<<<<<< HEAD
        ::-webkit-scrollbar {
    width: 6px;
}

::-webkit-scrollbar-track {
    background: #1a1a1a;
}

::-webkit-scrollbar-thumb {
    background: #e10600;
    border-radius: 3px;
}

=======
>>>>>>> f81424192996985be2da559c0f9a2c1f13f5eb7f
    </style>
</head>

<body>
    <div class="bg-lines"></div>

    <header>
        <div class="logo-title">
            <img src="https://upload.wikimedia.org/wikipedia/commons/3/33/F1.svg" alt="F1 Logo">
            <span>Fan Club</span>
        </div>

        <button class="hamburger" id="hamburgerBtn">
            <i class="fas fa-bars"></i>
        </button>

        <nav id="mainNav">
            <a href="/f1fanclub/index.php">Kezdőlap</a>
            <a href="/f1fanclub/Championship/championship.php">Bajnokság</a>
            <a href="/f1fanclub/teams/teams.php">Csapatok</a>
            <a href="/f1fanclub/drivers/drivers.php">Versenyzők</a>
            <a href="/f1fanclub/news/feed.php">Paddock</a>
            <a href="/f1fanclub/pitwall/pitwall.php"><i class="fas fa-trophy" style="margin-right: 5px;"></i> A Fal</a>
        </nav>

        <?php if ($isLoggedIn): ?>
          <div class="dropdown-container" id="userDropdownContainer">
            <div class="auth">
              <div class="welcome" style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                <?php if ($profile_image): ?>
                  <img src="/f1fanclub/uploads/<?php echo htmlspecialchars($profile_image); ?>" class="avatar clickable-user" alt="Profilkép"
                    style="width:35px; height:35px; border-radius:50%; object-fit: cover; border-color: <?php echo htmlspecialchars($teamColor); ?>;"
                    onclick="openUserProfile('<?php echo htmlspecialchars(addslashes($username)); ?>')">
                <?php endif; ?>
                <span class="welcome-text">
                  <span class="clickable-user" onclick="openUserProfile('<?php echo htmlspecialchars(addslashes($username)); ?>')"
                    style="color: <?php echo htmlspecialchars($teamColor); ?>; font-weight:bold;"><?php echo htmlspecialchars($username); ?></span>
                </span>
                <i class="fas fa-chevron-down dropdown-arrow-icon"></i>
              </div>
            </div>
            
            <div class="dropdown-menu-modern">
              <a href="/f1fanclub/profile/profile.php">
                <i class="fas fa-user-circle"></i> Profilom
              </a>
              <a href="/f1fanclub/messages/messages.php">
                <i class="fas fa-envelope"></i> Üzenetek
              </a>
              <?php if ($isAdmin): ?>
                <a href="/f1fanclub/admin/admin.php" style="position: relative;">
                  <i class="fas fa-shield-alt"></i> Admin Panel
                  <span class="admin-badge">ADMIN</span>
                </a>
              <?php endif; ?>
              <div class="dropdown-divider"></div>
              <a href="/f1fanclub/logout/logout.php">
                <i class="fas fa-sign-out-alt"></i> Kijelentkezés
              </a>
            </div>
          </div>
        <?php else: ?>
          <div class="auth">
            <a href="/f1fanclub/register/register.html" class="btn">Regisztráció</a>
            <a href="/f1fanclub/login/login.html" class="btn">Bejelentkezés</a>
          </div>
        <?php endif; ?>
    </header>

    <main class="container">
        <div class="section-header">
            <h2 class="section-title">Rólunk & Működés</h2>
        </div>

        <div class="about-card large-card">
            <h3><i class="fas fa-info-circle"></i> Az F1 Fan Club története és küldetése</h3>
            
            <p>
                Üdvözlünk az F1 Fan Club oldalán! Fontos rögtön az elején tisztáznunk: ez a platform egy <strong>iskolai vizsgaremekként (projektként) indult 2025 szeptemberében</strong>. Az alapötletet és a legnagyobb inspirációt az adta, hogy szerettük volna ötvözni a hivatalos <em>f1.com</em> adatalapú, professzionális világát egy <em>Discord</em> szerver pezsgő, interaktív közösségi élményével – mindezt egyetlen, letisztult magyar nyelvű felületen.
            </p>
            <p>
                Kiemelt célunk, hogy a hazai Forma-1 rajongók számára mindig friss, naprakész statisztikákat, csapat- és pilótaadatokat biztosítsunk. Emellett létrehoztunk egy teljesen egyedi élő versenyszimulációt is, hogy a futamok alatt együtt izgulhassuk végig a köridőket. A beépített "Paddock Feed" falunkon pedig mindenki szabadon megoszthatja a véleményét, reagálhat az eseményekre, és kibeszélheti a versenyhétvégék legforróbb pillanatait.
            </p>
            <p>
                Hisszük, hogy a Forma-1 nem csupán egy sport, hanem egy közös szenvedély. Éppen ezért mindent megteszünk azért, hogy az F1 Fan Club egy igazi, összetartó közösséggé épüljön ki az idő múlásával. Bár az oldal iskolai keretek között született, a lelkesedésünk a sport és a felhasználóink iránt teljesen valódi.
            </p>
            <p style="color: #e10600; font-weight: 600; margin-top: 20px;">
                Csatlakozz te is hozzánk, válaszd ki a kedvenc csapatodat, építsd a profilodat, és legyél részese a virtuális boxutcának!
            </p>
        </div>

        <div class="admin-grid">
            <?php foreach ($adminUsers as $admin): 
                $admImg = !empty($admin['profile_image']) ? "/f1fanclub/uploads/" . htmlspecialchars($admin['profile_image']) : "/f1fanclub/drivers/default.png";
                $admTeam = !empty($admin['fav_team']) ? htmlspecialchars($admin['fav_team']) : "Formula 1";
                $admColor = getTeamColor($admTeam);
                
                $roleTitle = "Adminisztrátor";
                $desc = "";

                if ($admin['username'] === 'rubenA') {
                    $roleTitle = "Admin/Backend Fejlesztő";
                    $desc = "A rendszer backend fejlesztője és fő mozgatórugója. Az ő nevéhez fűződik a teljes adatbázis felépítése, a Paddock feed moderálása, a komplex versenyszimuláció, valamint a bajnoki tabella és az élő eredmények hibátlan működése. Kedvenc csapata a(z) " . $admTeam . ".";
                } elseif ($admin['username'] === 'HELL\JUMPER') {
                    $roleTitle = "Admin/Frontend Fejlesztő";
                    $desc = "Az oldal frontend fejlesztője, akinek az F1 Fan Club lenyűgöző, modern és letisztult vizuális megjelenését köszönhetjük. Ő felel azért, hogy a felület minden eszközön átlátható és dizájnos legyen. Vérbeli " . $admTeam . " szurkoló.";
                } else {
                    $desc = "Az oldal egyik megbecsült adminisztrátora, aki felügyeli a közösséget. Kedvenc csapata a(z) " . $admTeam . ".";
                }
            ?>
            
            <div class="about-card admin-card" style="--card-color: <?= $admColor ?>; border-color: <?= $admColor ?>88; box-shadow: 0 10px 30px <?= $admColor ?>22;">
                <img src="<?= $admImg ?>" alt="<?= htmlspecialchars($admin['username']) ?>" class="admin-img" style="box-shadow: 0 0 20px <?= $admColor ?>66;">
                
                <h4 class="admin-name"><?= htmlspecialchars($admin['username']) ?></h4>
                
                <span class="admin-role" style="background: <?= $admColor ?>22;"><?= $roleTitle ?></span>
                
                <p class="admin-desc"><?= $desc ?></p>
            </div>
            
            <?php endforeach; ?>
            
            <?php if(empty($adminUsers)): ?>
                <p style="color:#aaa; text-align:center; width:100%;">Nincsenek megjeleníthető adminok az adatbázisban.</p>
            <?php endif; ?>
        </div>
    </main>

    <footer class="site-footer">
        <div class="footer-container">
            <div class="footer-section">
                <div class="footer-logo">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/3/33/F1.svg" alt="F1 Logo">
                    <span>Fan Club</span>
                </div>
                <p>A legnagyobb magyar F1 közösség. Hírek, futamok, csapatok és szenvedély egy helyen.</p>
            </div>

            <div class="footer-section">
                <h3>Navigáció</h3>
                <a href="/f1fanclub/index.php">Főoldal</a>
                <a href="/f1fanclub/news/feed.php">Paddock (Feed)</a>
                <a href="/f1fanclub/about.php">Rólunk & Működés</a>
                <a href="/f1fanclub/teams/teams.php">Csapatok</a>
                <a href="/f1fanclub/drivers/drivers.php">Versenyzők</a>
            </div>

            <div class="footer-section">
                <h3>Kapcsolat</h3>
                <a href="mailto:info@f1fanclub.hu">📧 info@f1fanclub.hu</a>
                <div class="social-links">
                    <a href="https://instagram.com" target="_blank" title="Instagram">
                        <svg class="social-icon" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z" /></svg>
                    </a>
                </div>
            </div>
        </div>
        <div class="copyright">
            &copy; <?php echo date("Y"); ?> F1 Fan Club. Minden jog fenntartva. | Nem hivatalos F1 oldal.
        </div>
    </footer>

    <script>
        // =========================================
        // DROPDOWN MENU FUNCTIONALITY
        // =========================================
        document.addEventListener('DOMContentLoaded', function() {
            const dropdownContainer = document.getElementById('userDropdownContainer');
            if (dropdownContainer) {
                const welcomeDiv = dropdownContainer.querySelector('.welcome');
                if (welcomeDiv) {
                    welcomeDiv.addEventListener('click', function(e) {
                        e.stopPropagation();
                        dropdownContainer.classList.toggle('open');
                    });
                }
                
                document.addEventListener('click', function(e) {
                    if (!dropdownContainer.contains(e.target)) {
                        dropdownContainer.classList.remove('open');
                    }
                });
            }
        });

        // =========================================
        // HAMBURGER MENU TOGGLE
        // =========================================
        const hamburgerBtn = document.getElementById('hamburgerBtn');
        const mainNav = document.getElementById('mainNav');
        
        if (hamburgerBtn && mainNav) {
            hamburgerBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                mainNav.classList.toggle('open');
            });
            
            // Close menu when clicking outside
            document.addEventListener('click', function(event) {
                if (mainNav.classList.contains('open') && 
                    !mainNav.contains(event.target) && 
                    !hamburgerBtn.contains(event.target)) {
                    mainNav.classList.remove('open');
                }
            });
            
            // Close menu when a nav link is clicked
            const navLinks = mainNav.querySelectorAll('a');
            navLinks.forEach(link => {
                link.addEventListener('click', function() {
                    mainNav.classList.remove('open');
                });
            });
        }

        // =========================================
        // USER PROFILE MODAL FUNCTIONS
        // =========================================
        let currentModalUser = "";
        let currentFriendStatus = "";

        function openUserProfile(username) {
            fetch('/f1fanclub/profile/user_profile_api.php?username=' + encodeURIComponent(username))
            .then(r => r.json())
            .then(data => {
                if(data.success) {
                    currentModalUser = data.user.username;
                    currentFriendStatus = data.user.friendship_status;

                    let modal = document.getElementById('userProfileModal');
                    if (!modal) {
                        modal = document.createElement('div');
                        modal.id = 'userProfileModal';
                        modal.className = 'user-modal-overlay';
                        modal.onclick = closeUserProfile;
                        modal.innerHTML = `
                            <div class="user-modal-content" onclick="event.stopPropagation()">
                                <button class="user-modal-close" onclick="closeUserProfile(event)">&times;</button>
                                <div class="user-modal-header">
                                    <img id="modalProfileImg" src="" alt="Avatar">
                                    <h3 id="modalUsername">Felhasználónév</h3>
                                    <span id="modalRole" class="modal-role">Szerepkör</span>
                                </div>
                                <div class="user-modal-body">
                                    <p><i class="fas fa-flag-checkered"></i> <strong>Csapat:</strong> <span id="modalTeam">Csapat</span></p>
                                    <p><i class="far fa-calendar-alt"></i> <strong>Regisztrált:</strong> <span id="modalRegDate">Dátum</span></p>
                                </div>
                                <div class="user-modal-footer">
                                    <button id="modalFriendBtn" class="btn-add-friend" onclick="handleFriendAction()">
                                        <i class="fas fa-user-plus"></i> Barátnak jelölés
                                    </button>
                                    <button class="btn-send-msg" onclick="window.location.href='/f1fanclub/messages/messages.php'">
                                        <i class="fas fa-comment"></i> Üzenet küldése
                                    </button>
                                </div>
                            </div>
                        `;
                        document.body.appendChild(modal);
                    }

                    const modalImg = document.getElementById('modalProfileImg');
                    if (modalImg) {
                        modalImg.src = data.user.profile_image;
                        modalImg.style.borderColor = data.user.team_color;
                    }
                    document.getElementById('modalUsername').innerText = data.user.username;
                    document.getElementById('modalRole').innerText = data.user.role_name;
                    document.getElementById('modalTeam').innerText = data.user.fav_team || 'Nincs megadva';
                    document.getElementById('modalRegDate').innerText = data.user.reg_date;
                    
                    updateFriendButton(data.user.friendship_status);
                    document.getElementById('userProfileModal').style.display = 'flex';
                } else {
                    alert("Hiba: " + data.error);
                }
            }).catch(err => console.error(err));
        }

        function closeUserProfile(e) {
            if(e) e.stopPropagation();
            const modal = document.getElementById('userProfileModal');
            if (modal) modal.style.display = 'none';
        }

        function updateFriendButton(status) {
            const btn = document.getElementById('modalFriendBtn');
            if(!btn) return;
            btn.style.display = 'flex';
            
            if (status === 'self') {
                btn.style.display = 'none';
            } else if (status === 'none') {
                btn.innerHTML = '<i class="fas fa-user-plus"></i> Barátnak jelölés';
                btn.style.background = '#333';
            } else if (status === 'pending_sent') {
                btn.innerHTML = '<i class="fas fa-clock"></i> Jelölés elküldve';
                btn.style.background = '#888';
            } else if (status === 'pending_received') {
                btn.innerHTML = '<i class="fas fa-check"></i> Jelölés elfogadása';
                btn.style.background = '#28a745';
            } else if (status === 'accepted') {
                btn.innerHTML = '<i class="fas fa-user-minus"></i> Barát törlése';
                btn.style.background = '#e10600';
            }
        }

        function handleFriendAction() {
            let action = '';
            if (currentFriendStatus === 'none') action = 'add';
            else if (currentFriendStatus === 'pending_sent' || currentFriendStatus === 'accepted') action = 'remove';
            else if (currentFriendStatus === 'pending_received') action = 'accept';

            if(!action) return;

            fetch('/f1fanclub/profile/friend_api.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ action: action, target_user: currentModalUser })
            })
            .then(r => r.json())
            .then(data => {
                if(data.success) {
                    openUserProfile(currentModalUser);
                }
            });
        }

        // =========================================
        // MODAL STYLES
        // =========================================
        (function addModalStyles() {
            if (document.getElementById('modal-styles')) return;
            const style = document.createElement('style');
            style.id = 'modal-styles';
            style.textContent = `
                .user-modal-overlay {
                    display: none;
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0, 0, 0, 0.85);
                    backdrop-filter: blur(8px);
                    z-index: 9999;
                    justify-content: center;
                    align-items: center;
                }
                .user-modal-content {
                    background: linear-gradient(145deg, #111, #1a1a1a);
                    width: 320px;
                    border-radius: 24px;
                    border: 1px solid #e10600;
                    padding: 20px;
                    position: relative;
                    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.6);
                    animation: popIn 0.3s ease;
                    text-align: center;
                }
                @keyframes popIn {
                    from { transform: scale(0.8); opacity: 0; }
                    to { transform: scale(1); opacity: 1; }
                }
                .user-modal-close {
                    position: absolute;
                    top: 12px;
                    right: 15px;
                    background: none;
                    border: none;
                    color: #888;
                    font-size: 1.3rem;
                    cursor: pointer;
                }
                .user-modal-close:hover { color: #e10600; }
                .user-modal-header img {
                    width: 80px;
                    height: 80px;
                    border-radius: 50%;
                    border: 3px solid #e10600;
                    object-fit: cover;
                    margin-bottom: 10px;
                }
                .modal-role {
                    display: inline-block;
                    font-size: 0.7rem;
                    background: rgba(255, 255, 255, 0.1);
                    padding: 2px 10px;
                    border-radius: 20px;
                    margin-top: 5px;
                    color: #aaa;
                }
                .user-modal-body {
                    margin: 15px 0;
                    background: rgba(0, 0, 0, 0.3);
                    padding: 12px;
                    border-radius: 16px;
                    text-align: left;
                }
                .user-modal-footer {
                    display: flex;
                    gap: 10px;
                }
                .user-modal-footer button {
                    flex: 1;
                    padding: 10px;
                    border: none;
                    border-radius: 40px;
                    cursor: pointer;
                    font-weight: 600;
                    transition: all 0.2s;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    gap: 6px;
                }
                .btn-add-friend {
                    background: #333;
                    color: white;
                }
                .btn-add-friend:hover { background: #444; }
                .btn-send-msg {
                    background: #e10600;
                    color: white;
                }
                .btn-send-msg:hover { background: #b00500; }
                .clickable-user { cursor: pointer; }
                .clickable-user:hover { opacity: 0.8; }
            `;
            document.head.appendChild(style);
        })();
    </script>
</body>
</html>