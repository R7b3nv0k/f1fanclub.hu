<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit;
}

$DB_HOST = "localhost";
$DB_USER = "swmjndga_swmjndga";
$DB_PASS = "Teszt1234!";
$DB_NAME = "swmjndga_f1adat";

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) die("Hiba: " . $conn->connect_error);

$username = $_SESSION['username'];
$message = "";

// Get user ID from database
$userIdQuery = $conn->prepare("SELECT id FROM users WHERE username=?");
$userIdQuery->bind_param("s", $username);
$userIdQuery->execute();
$userIdResult = $userIdQuery->get_result();
$userId = $userIdResult->fetch_assoc()['id'] ?? 0;
$userIdQuery->close();

// Upload handling
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profile_image'])) {
    $target_dir = "../uploads/";
    $file_extension = strtolower(pathinfo($_FILES["profile_image"]["name"], PATHINFO_EXTENSION));
    $new_filename = $username . "_" . time() . "." . $file_extension;
    $target_file = $target_dir . $new_filename;
    
    $uploadOk = 1;

    $check = getimagesize($_FILES["profile_image"]["tmp_name"]);
    if($check !== false) {
        $uploadOk = 1;
    } else {
        $message = "A feltöltött fájl nem kép.";
        $uploadOk = 0;
    }

    if ($_FILES["profile_image"]["size"] > 5000000) {
        $message = "A kép túl nagy! (Max 5MB)";
        $uploadOk = 0;
    }

    if($file_extension != "jpg" && $file_extension != "png" && $file_extension != "jpeg" && $file_extension != "gif" ) {
        $message = "Csak JPG, JPEG, PNG és GIF fájlok engedélyezettek.";
        $uploadOk = 0;
    }

    if ($uploadOk == 1) {
        if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file)) {
            $stmt = $conn->prepare("UPDATE users SET profile_image = ? WHERE username = ?");
            $stmt->bind_param("ss", $new_filename, $username);
            if ($stmt->execute()) {
                header("Location: " . $_SERVER['PHP_SELF']); 
                exit;
            } else {
                $message = "Adatbázis hiba: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $message = "Hiba történt a feltöltés közben.";
        }
    }
}

$stmt = $conn->prepare("SELECT email, profile_image, fav_team, pitwall_points FROM users WHERE username=?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

function getTeamColor($team) {
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
        case 'Cadillac': return '#B6BABD';
        default: return '#e10600';
    }
}

function getTeamShortening($team) {
    switch ($team) {
        case 'Red Bull': return 'RB';
        case 'Ferrari': return 'FER';
        case 'Mercedes': return 'MER';
        case 'McLaren': return 'MCL';
        case 'Aston Martin': return 'AM';
        case 'Alpine': return 'ALP';
        case 'Williams': return 'WIL';
        case 'RB': return 'RB';
        case 'Audi': return 'AUD';
        case 'Haas F1 Team': return 'HAA';
        case 'Cadillac': return 'CAD';
        default: return 'F1FC';
    }
}

function getRankInfo($points) {
    // Rainbow Six Siege style ranks
    if ($points >= 1000000) {
        return ['name' => 'LEGENDARY', 'color' => '#8B0000', 'tier' => ''];
    } elseif ($points >= 900000) {
        return ['name' => 'DIAMOND', 'color' => '#00FFFF', 'tier' => 'I'];
    } elseif ($points >= 800000) {
        return ['name' => 'DIAMOND', 'color' => '#00FFFF', 'tier' => 'II'];
    } elseif ($points >= 700000) {
        return ['name' => 'DIAMOND', 'color' => '#00FFFF', 'tier' => 'III'];
    } elseif ($points >= 600000) {
        return ['name' => 'DIAMOND', 'color' => '#00FFFF', 'tier' => 'IV'];
    } elseif ($points >= 500000) {
        return ['name' => 'DIAMOND', 'color' => '#00FFFF', 'tier' => 'V'];
    } elseif ($points >= 450000) {
        return ['name' => 'PLATINUM', 'color' => '#20B2AA', 'tier' => 'I'];
    } elseif ($points >= 400000) {
        return ['name' => 'PLATINUM', 'color' => '#20B2AA', 'tier' => 'II'];
    } elseif ($points >= 350000) {
        return ['name' => 'PLATINUM', 'color' => '#20B2AA', 'tier' => 'III'];
    } elseif ($points >= 300000) {
        return ['name' => 'PLATINUM', 'color' => '#20B2AA', 'tier' => 'IV'];
    } elseif ($points >= 250000) {
        return ['name' => 'PLATINUM', 'color' => '#20B2AA', 'tier' => 'V'];
    } elseif ($points >= 200000) {
        return ['name' => 'GOLD', 'color' => '#FFD700', 'tier' => 'I'];
    } elseif ($points >= 175000) {
        return ['name' => 'GOLD', 'color' => '#FFD700', 'tier' => 'II'];
    } elseif ($points >= 150000) {
        return ['name' => 'GOLD', 'color' => '#FFD700', 'tier' => 'III'];
    } elseif ($points >= 125000) {
        return ['name' => 'GOLD', 'color' => '#FFD700', 'tier' => 'IV'];
    } elseif ($points >= 100000) {
        return ['name' => 'GOLD', 'color' => '#FFD700', 'tier' => 'V'];
    } elseif ($points >= 80000) {
        return ['name' => 'SILVER', 'color' => '#C0C0C0', 'tier' => 'I'];
    } elseif ($points >= 60000) {
        return ['name' => 'SILVER', 'color' => '#C0C0C0', 'tier' => 'II'];
    } elseif ($points >= 40000) {
        return ['name' => 'SILVER', 'color' => '#C0C0C0', 'tier' => 'III'];
    } elseif ($points >= 20000) {
        return ['name' => 'SILVER', 'color' => '#C0C0C0', 'tier' => 'IV'];
    } elseif ($points >= 10000) {
        return ['name' => 'SILVER', 'color' => '#C0C0C0', 'tier' => 'V'];
    } elseif ($points >= 8000) {
        return ['name' => 'BRONZE', 'color' => '#CD7F32', 'tier' => 'I'];
    } elseif ($points >= 6000) {
        return ['name' => 'BRONZE', 'color' => '#CD7F32', 'tier' => 'II'];
    } elseif ($points >= 4000) {
        return ['name' => 'BRONZE', 'color' => '#CD7F32', 'tier' => 'III'];
    } elseif ($points >= 2000) {
        return ['name' => 'BRONZE', 'color' => '#CD7F32', 'tier' => 'IV'];
    } elseif ($points >= 1000) {
        return ['name' => 'BRONZE', 'color' => '#CD7F32', 'tier' => 'V'];
    } elseif ($points >= 800) {
        return ['name' => 'COPPER', 'color' => '#B87333', 'tier' => 'I'];
    } elseif ($points >= 600) {
        return ['name' => 'COPPER', 'color' => '#B87333', 'tier' => 'II'];
    } elseif ($points >= 400) {
        return ['name' => 'COPPER', 'color' => '#B87333', 'tier' => 'III'];
    } elseif ($points >= 200) {
        return ['name' => 'COPPER', 'color' => '#B87333', 'tier' => 'IV'];
    } elseif ($points >= 100) {
        return ['name' => 'COPPER', 'color' => '#B87333', 'tier' => 'V'];
    } else {
        return ['name' => 'ROOKIE', 'color' => '#FFFFFF', 'tier' => ''];
    }
}

$teamColor = getTeamColor($user['fav_team'] ?? null);
$teamShortening = getTeamShortening($user['fav_team'] ?? null);
$rankInfo = getRankInfo($user['pitwall_points'] ?? 0);
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>F1FC Super Licence - <?php echo htmlspecialchars($username); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/f1fanclub/css/style.css">
    <link rel="icon" type="image/svg+xml" href="https://upload.wikimedia.org/wikipedia/commons/3/33/F1.svg">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #0a0a0a;
            color: white;
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            overflow-x: hidden;
            padding-top: 80px;
        }

        body::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 20% 50%, rgba(225, 6, 0, 0.05) 0%, transparent 50%),
                        radial-gradient(circle at 80% 80%, rgba(225, 6, 0, 0.05) 0%, transparent 50%);
            pointer-events: none;
            z-index: -1;
        }

        /* SUPER LICENCE CARD */
        .super-licence {
            max-width: 1000px;
            width: 90%;
            margin: 40px auto 60px;
            background: linear-gradient(145deg, #111111 0%, #0a0a0a 100%);
            border-radius: 24px;
            border: 2px solid;
            position: relative;
            overflow: hidden;
            box-shadow: 0 30px 60px rgba(0,0,0,0.8);
            transition: all 0.3s ease;
        }

        /* Holographic overlay */
        .super-licence::before {
            content: "";
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg,
                transparent 40%,
                rgba(225, 6, 0, 0.03) 50%,
                transparent 60%);
            transform: rotate(45deg);
            animation: hologram 8s infinite linear;
            pointer-events: none;
        }

        @keyframes hologram {
            0% { transform: rotate(45deg) translateX(-50%); }
            100% { transform: rotate(45deg) translateX(50%); }
        }

        /* Header */
        .fia-header {
            background: linear-gradient(135deg, #0a0a0a 0%, #151515 100%);
            padding: 20px 30px;
            border-bottom: 2px solid;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .fia-logo {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .fia-logo i {
            font-size: 2.5rem;
        }

        .fia-logo h1 {
            font-size: 1.8rem;
            font-weight: 800;
            letter-spacing: 2px;
            color: #fff;
            text-transform: uppercase;
        }

        .fia-logo p {
            font-size: 0.6rem;
            color: #888;
            letter-spacing: 1px;
        }

        .licence-type {
            padding: 6px 16px;
            border-radius: 30px;
        }

        .licence-type span {
            font-weight: 700;
            font-size: 0.9rem;
            letter-spacing: 1px;
        }

        /* Main Content */
        .licence-content {
            padding: 30px;
            display: flex;
            gap: 40px;
            flex-wrap: wrap;
        }

        /* Photo Section */
        .photo-section {
            flex: 0 0 220px;
            text-align: center;
        }

        .photo-frame {
            background: #0a0a0a;
            border: 2px solid;
            border-radius: 12px;
            padding: 6px;
            position: relative;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .photo-frame:hover {
            transform: scale(1.02);
            box-shadow: 0 0 20px rgba(225,6,0,0.3);
        }

        .profile-photo {
            width: 100%;
            aspect-ratio: 1;
            object-fit: cover;
            border-radius: 8px;
            display: block;
        }

        .photo-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s;
            border-radius: 8px;
        }

        .photo-frame:hover .photo-overlay {
            opacity: 1;
        }

        .photo-overlay span {
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            color: #fff;
        }

        .licence-number {
            margin-top: 12px;
            font-size: 0.7rem;
            color: #666;
            letter-spacing: 1px;
        }

        /* Info Section */
        .info-section {
            flex: 1;
        }

        .info-row {
            display: flex;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            padding: 12px 0;
        }

        .info-label {
            width: 130px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .info-value {
            flex: 1;
            font-size: 1rem;
            font-weight: 500;
            color: #fff;
        }

        .team-color-badge {
            display: inline-block;
            width: 14px;
            height: 14px;
            border-radius: 50%;
            margin-right: 10px;
            vertical-align: middle;
        }

        /* Points Section - YELLOW (exception) */
        .points-section {
            background: rgba(0,0,0,0.3);
            border: 1px solid rgba(212, 175, 55, 0.3);
            border-radius: 16px;
            padding: 25px 30px;
            margin-top: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 30px;
        }

        .points-label {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: #d4af37;
        }

        .points-value {
            font-size: 2.5rem;
            font-weight: 900;
            color: #d4af37;
        }

        .points-value small {
            font-size: 1rem;
            color: #d4af37;
            opacity: 0.7;
        }

        /* Rank styling */
        .rank-value {
            font-size: 1.8rem;
            font-weight: 800;
        }

        /* Security Features */
        .security-features {
            background: rgba(0,0,0,0.3);
            padding: 12px 30px;
            border-top: 1px solid rgba(255,255,255,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .hologram-strip {
            display: flex;
            gap: 4px;
        }

        .hologram-strip span {
            width: 25px;
            height: 15px;
            background: linear-gradient(45deg, #e10600, #ff4d4d, #990000);
            opacity: 0.4;
            border-radius: 2px;
        }

        .qr-code {
            width: 35px;
            height: 35px;
            background: repeating-linear-gradient(0deg, #fff 0px, #fff 3px, #000 3px, #000 6px);
            opacity: 0.2;
        }

        .signature {
            font-size: 0.65rem;
        }

        /* Buttons */
        .action-buttons {
            padding: 20px 30px 30px;
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            padding: 10px 25px;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            text-decoration: none;
            border-radius: 30px;
            transition: all 0.3s ease;
            font-size: 0.75rem;
        }

        .btn-primary {
            background: rgba(225, 6, 0, 0.15);
            border: 1px solid rgba(225, 6, 0, 0.5);
            color: #e10600;
        }

        .btn-primary:hover {
            background: #e10600;
            color: #fff;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #ccc;
        }

        .btn-secondary:hover {
            background: rgba(225, 6, 0, 0.2);
            border-color: #e10600;
            color: #fff;
            transform: translateY(-2px);
        }

        .btn-danger {
            background: rgba(225, 6, 0, 0.1);
            border: 1px solid rgba(225, 6, 0, 0.3);
            color: #ff6b6b;
        }

        .btn-danger:hover {
            background: #e10600;
            color: #fff;
            transform: translateY(-2px);
        }

        /* Alert */
        .alert {
            margin: 20px 30px 0;
            padding: 12px 20px;
            background: rgba(225, 6, 0, 0.15);
            border-left: 3px solid #e10600;
            color: #ff6b6b;
            font-size: 0.85rem;
            border-radius: 8px;
        }

        #fileInput {
            display: none;
        }

        /* Responsive */
        @media (max-width: 700px) {
            .licence-content { flex-direction: column; align-items: center; }
            .photo-section { flex: 0 0 auto; width: 180px; }
            .info-row { flex-direction: column; gap: 5px; }
            .info-label { width: auto; }
            .fia-header { flex-direction: column; text-align: center; }
            .super-licence { width: 95%; margin-top: 20px; }
            .points-section { flex-direction: column; text-align: center; }
            .rank-value { font-size: 1.3rem; }
        }
    </style>
</head>
<body>

<div class="bg-lines"></div>

<header>
    <div class="left-header">
        <div class="logo-title">
            <img src="https://upload.wikimedia.org/wikipedia/commons/3/33/F1.svg" alt="F1 Logo">
            <span>Fan Club</span>
        </div>
    </div>

    <nav>
        <a href="/f1fanclub/index.php">Kezdőlap</a>
        <a href="/f1fanclub/Championship/championship.php">Bajnokság</a>
        <a href="/f1fanclub/teams/teams.php">Csapatok</a>
        <a href="/f1fanclub/drivers/drivers.php">Versenyzők</a>
        <a href="/f1fanclub/news/feed.php">Paddock</a>
        <a href="/f1fanclub/pitwall/pitwall.php"><i class="fas fa-trophy" style="margin-right: 5px;"></i> A Fal</a>
    </nav>

    <!-- DROPDOWN MENU -->
    <?php if (isset($_SESSION['username'])): ?>
        <div class="dropdown-container" id="userDropdownContainer">
            <div class="auth">
                <div class="welcome" style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                    <?php if ($user['profile_image']): ?>
                        <img src="/f1fanclub/uploads/<?php echo htmlspecialchars($user['profile_image']); ?>" class="avatar clickable-user"
                            alt="Profilkép" onclick="openUserProfile('<?php echo htmlspecialchars(addslashes($username)); ?>')"
                            style="width:35px; height:35px; border-radius:50%; object-fit: cover; border: 2px solid <?php echo htmlspecialchars($teamColor); ?>;">
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

<!-- SUPER LICENCE CARD WITH TEAM COLOR -->
<div class="super-licence" style="border-color: <?php echo $teamColor; ?>; box-shadow: 0 30px 60px rgba(0,0,0,0.8), 0 0 20px <?php echo $teamColor; ?>20;">
    <div class="fia-header" style="border-bottom: 2px solid <?php echo $teamColor; ?>;">
        <div class="fia-logo">
            <i class="fas fa-flag-checkered" style="color: <?php echo $teamColor; ?>;"></i>
            <div>
                <h1>F1FC</h1>
                <p>F1 RAJONGÓI KLUB - SZUPER LICENC</p>
            </div>
        </div>
        <div class="licence-type" style="background: <?php echo $teamColor; ?>15; border: 1px solid <?php echo $teamColor; ?>40;">
            <span style="color: <?php echo $teamColor; ?>;">SZUPER LICENC</span>
        </div>
    </div>

    <?php if($message): ?>
        <div class="alert"><?php echo $message; ?></div>
    <?php endif; ?>

    <div class="licence-content">
        <div class="photo-section">
            <form action="" method="post" enctype="multipart/form-data" id="profileForm">
                <label for="fileInput">
                    <div class="photo-frame" style="border-color: <?php echo $teamColor; ?>;">
                        <?php 
                        $img_src = $user['profile_image'] ? "/f1fanclub/uploads/" . htmlspecialchars($user['profile_image']) : "https://upload.wikimedia.org/wikipedia/commons/7/7c/Profile_avatar_placeholder_large.png";
                        ?>
                        <img src="<?php echo $img_src; ?>" class="profile-photo" alt="Licenc Fotó">
                        <div class="photo-overlay">
                            <span><i class="fas fa-camera"></i> FRISSÍTÉS</span>
                        </div>
                    </div>
                </label>
                <input type="file" name="profile_image" id="fileInput" onchange="document.getElementById('profileForm').submit();">
            </form>
            <div class="licence-number">
                <i class="fas fa-id-card"></i> LICENC SZÁM: F1FC-<?php echo $teamShortening; ?>-<?php echo str_pad($userId, 8, '0', STR_PAD_LEFT); ?>
            </div>
        </div>

        <div class="info-section">
            <div class="info-row">
                <div class="info-label" style="color: <?php echo $teamColor; ?>;">TELJES NÉV</div>
                <div class="info-value"><?php echo strtoupper(htmlspecialchars($username)); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label" style="color: <?php echo $teamColor; ?>;">EMAIL</div>
                <div class="info-value"><?php echo htmlspecialchars($user['email']); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label" style="color: <?php echo $teamColor; ?>;">CSAPAT</div>
                <div class="info-value">
                    <span class="team-color-badge" style="background: <?php echo $teamColor; ?>;"></span>
                    <?php echo htmlspecialchars($user['fav_team'] ?? "Nincs kiválasztva"); ?>
                </div>
            </div>
            <div class="info-row">
                <div class="info-label" style="color: <?php echo $teamColor; ?>;">LICENC ÁLLAPOT</div>
                <div class="info-value" style="color: #4caf50;">✓ AKTÍV</div>
            </div>
            <div class="info-row">
                <div class="info-label" style="color: <?php echo $teamColor; ?>;">KIÁLLÍTÁS DÁTUMA</div>
                <div class="info-value"><?php echo date('Y. m. d.'); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label" style="color: <?php echo $teamColor; ?>;">LEJÁRAT DÁTUMA</div>
                <div class="info-value"><?php echo date('Y. m. d.', strtotime('+5 years')); ?></div>
            </div>

            <!-- Points Section - YELLOW (exception from team color) -->
            <div class="points-section">
                <div>
                    <div class="points-label"><i class="fas fa-trophy"></i> PITWALL PONTOK</div>
                    <div class="points-value"><?php echo number_format($user['pitwall_points'] ?? 0); ?> <small>PONT</small></div>
                </div>
                <div>
                    <div class="points-label"><i class="fas fa-star"></i> RAJONGÓI RANG</div>
                    <div class="rank-value" style="color: <?php echo $rankInfo['color']; ?>;">
                        <?php 
                        if ($rankInfo['name'] === 'LEGENDARY') {
                            echo 'LEGENDÁS';
                        } elseif ($rankInfo['name'] === 'ROOKIE') {
                            echo 'ÚJONC';
                        } else {
                            $rankNames = [
                                'DIAMOND' => 'GYÉMÁNT',
                                'PLATINUM' => 'PLATINA',
                                'GOLD' => 'ARANY',
                                'SILVER' => 'EZÜST',
                                'BRONZE' => 'BRONZ',
                                'COPPER' => 'RÉZ'
                            ];
                            $hungarianRank = $rankNames[$rankInfo['name']] ?? $rankInfo['name'];
                            echo $hungarianRank . ' ' . $rankInfo['tier'];
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="security-features">
        <div class="hologram-strip">
            <span></span><span></span><span></span><span></span><span></span>
        </div>
        <div class="signature" style="color: <?php echo $teamColor; ?>;">
            <i class="fas fa-signature"></i> F1FC Hitelesített Aláírás
        </div>
        <div class="qr-code"></div>
    </div>

    <div class="action-buttons">
        <a href="/f1fanclub/index.php" class="btn btn-primary">
            <i class="fas fa-home"></i> VISSZA A PADDOCKBA
        </a>
        <?php if ($isAdmin): ?>
            <a href="/f1fanclub/admin/admin.php" class="btn btn-danger">
                <i class="fas fa-shield-alt"></i> ADMIN PANEL
            </a>
        <?php endif; ?>
        <a href="/f1fanclub/messages/messages.php" class="btn btn-secondary">
            <i class="fas fa-envelope"></i> ÜZENETEK
        </a>
    </div>
</div>

<script>
    // DROPDOWN MENU TOGGLE
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

    // USER PROFILE POP-UP
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
</script>

<style>
    /* Modal Styles */
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
    .user-modal-close:hover {
        color: #e10600;
    }
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
    .btn-add-friend:hover {
        background: #444;
    }
    .btn-send-msg {
        background: #e10600;
        color: white;
    }
    .btn-send-msg:hover {
        background: #b00500;
    }
    .clickable-user {
        cursor: pointer;
    }
</style>

</body>
</html>