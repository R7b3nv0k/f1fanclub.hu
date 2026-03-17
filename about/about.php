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
        default: return '#e10600'; // Alapértelmezett piros
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
        
        nav { display: flex; gap: 5px; margin: 0 20px; }
        nav a { font-weight: 600; font-size: 0.9rem; text-transform: uppercase; padding: 8px 16px; border-radius: 4px; color: #ffffff !important; text-decoration: none; transition: all 0.2s ease; letter-spacing: 0.5px; opacity: 0.9; }
        nav a:hover, nav a.active { color: #e10600 !important; opacity: 1; background: rgba(225, 6, 0, 0.1); }

        .auth { display: flex; align-items: center; gap: 10px; }
        .welcome { display: flex; align-items: center; gap: 10px; font-size: 0.9rem; margin-right: 10px; padding: 5px 12px; background: rgba(255, 255, 255, 0.05); border-radius: 30px; border: 1px solid rgba(225, 6, 0, 0.2); }
        .welcome img.avatar { width: 32px; height: 32px; border-radius: 50%; object-fit: cover; border: 2px solid #e10600; }
        .auth .btn { display: inline-block; padding: 8px 20px; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; color: #fff; background-color: transparent; border: 1px solid rgba(225, 6, 0, 0.5); border-radius: 30px; cursor: pointer; transition: all 0.3s ease; text-decoration: none; }
        .auth .btn:hover { background-color: #e10600; border-color: #e10600; transform: translateY(-2px); box-shadow: 0 4px 15px rgba(225, 6, 0, 0.4); }
        
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

        @media (max-width: 992px) { header { flex-wrap: wrap; height: auto; padding: 15px 20px; gap: 15px; } nav { order: 3; width: 100%; justify-content: center; } .auth { margin-left: auto; } }
        @media (max-width: 768px) { .admin-grid { grid-template-columns: 1fr; } }
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
                // Kép, csapat, és szín beállítása az admin adatbázis adataiból
                $admImg = !empty($admin['profile_image']) ? "/f1fanclub/uploads/" . htmlspecialchars($admin['profile_image']) : "/f1fanclub/drivers/default.png";
                $admTeam = !empty($admin['fav_team']) ? htmlspecialchars($admin['fav_team']) : "Formula 1";
                $admColor = getTeamColor($admTeam);
                
                // Személyre szabott szövegek a felhasználónevek alapján
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

</body>
</html>