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

<<<<<<< HEAD
// -------------------------------------------------------------
// --- ÚJ: ADATOK MÓDOSÍTÁSÁNAK KÉRELMEZÉSE (E-MAIL KÜLDÉS) ---
// -------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['setting_action'])) {
    $action = $_POST['setting_action'];
    $token = bin2hex(random_bytes(32));
    
    // Lekérjük a jelenlegi email-t, hogy tudjuk hova küldeni a levelet
    $emStmt = $conn->prepare("SELECT email FROM users WHERE username=?");
    $emStmt->bind_param("s", $username);
    $emStmt->execute();
    $currentEmail = $emStmt->get_result()->fetch_assoc()['email'];
    $emStmt->close();

    if ($action == 'change_password') {
        $new_pass = $_POST['new_password'];
        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d\w\W]{8,}$/', $new_pass)) {
            $message = "Hiba: A jelszónak legalább 8 karakter hosszúnak kell lennie, és tartalmaznia kell kisbetűt, nagybetűt és számot!";
        } else {
            $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO user_changes (username, change_type, new_value, token) VALUES (?, 'password', ?, ?)");
            $stmt->bind_param("sss", $username, $hashed, $token);
            $stmt->execute();
            
            $link = "http://f1fanclub.hu/f1fanclub/profile/verify_change.php?token=" . urlencode($token);
            @mail($currentEmail, "F1 Fan Club - Jelszo modositas", "Szia!\n\nJelszó módosítást kértél. Hagyd jóvá ezen a linken (1 óráig érvényes):\n$link", "From: noreply@f1fanclub.hu\r\n");
            $message = "Megerősítő e-mail elküldve a jelenlegi címedre!";
        }
    }
    elseif ($action == 'change_email') {
        $new_email = trim($_POST['new_email']);
        $chk = $conn->query("SELECT id FROM users WHERE email='$new_email'");
        if ($chk->num_rows > 0) {
            $message = "Hiba: Ez az e-mail cím már foglalt!";
        } else {
            $stmt = $conn->prepare("INSERT INTO user_changes (username, change_type, new_value, token) VALUES (?, 'email', ?, ?)");
            $stmt->bind_param("sss", $username, $new_email, $token);
            $stmt->execute();
            
            $link = "http://f1fanclub.hu/f1fanclub/profile/verify_change.php?token=" . urlencode($token);
            @mail($new_email, "F1 Fan Club - Uj E-mail megerositese", "Szia!\n\nEzt az e-mail címet adtad meg új címként. Hagyd jóvá ezen a linken:\n$link", "From: noreply@f1fanclub.hu\r\n");
            $message = "Megerősítő e-mail elküldve az ÚJ e-mail címedre!";
        }
    }
    elseif ($action == 'change_username') {
        $new_uname = trim($_POST['new_username']);
        $chk = $conn->query("SELECT id FROM users WHERE username='$new_uname'");
        if ($chk->num_rows > 0) {
            $message = "Hiba: Ez a név már foglalt!";
        } else {
            $stmt = $conn->prepare("INSERT INTO user_changes (username, change_type, new_value, token) VALUES (?, 'username', ?, ?)");
            $stmt->bind_param("sss", $username, $new_uname, $token);
            $stmt->execute();
            
            $link = "http://f1fanclub.hu/f1fanclub/profile/verify_change.php?token=" . urlencode($token);
            @mail($currentEmail, "F1 Fan Club - Nevvaltoztatas", "Szia!\n\nNevváltoztatást kértél (Új név: $new_uname). Hagyd jóvá ezen a linken:\n$link", "From: noreply@f1fanclub.hu\r\n");
            $message = "Megerősítő e-mail elküldve a jelenlegi címedre!";
        }
    }
}
// -------------------------------------------------------------

// Get user ID from database
$userIdQuery = $conn->prepare("SELECT id FROM users WHERE username=?");
$userIdQuery->bind_param("s", $username);
$userIdQuery->execute();
$userIdResult = $userIdQuery->get_result();
$userId = $userIdResult->fetch_assoc()['id'] ?? 0;
$userIdQuery->close();

=======
// Get user ID from database
$userIdQuery = $conn->prepare("SELECT id FROM users WHERE username=?");
$userIdQuery->bind_param("s", $username);
$userIdQuery->execute();
$userIdResult = $userIdQuery->get_result();
$userId = $userIdResult->fetch_assoc()['id'] ?? 0;
$userIdQuery->close();

>>>>>>> f81424192996985be2da559c0f9a2c1f13f5eb7f
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
<<<<<<< HEAD
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes, viewport-fit=cover">
    <title>F1FC Super Licence - <?php echo htmlspecialchars($username); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
=======
    <title>F1FC Super Licence - <?php echo htmlspecialchars($username); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/f1fanclub/css/style.css">
>>>>>>> f81424192996985be2da559c0f9a2c1f13f5eb7f
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

<<<<<<< HEAD
        .bg-lines {
            position: fixed;
            width: 200%;
            height: 200%;
            background: repeating-linear-gradient(60deg, rgba(225, 6, 0, 0.03) 0px, rgba(225, 6, 0, 0.03) 2px, transparent 2px, transparent 10px);
            animation: slide 10s linear infinite;
            opacity: 0.3;
            z-index: -1;
            top: 0;
            left: 0;
        }

        @keyframes slide { from { transform: translateX(0); } to { transform: translateX(-200px); } }

        /* Header */
        header {
            background-color: #0a0a0a;
            border-bottom: 2px solid rgba(225, 6, 0, 0.3);
            padding: 0 40px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            box-shadow: 0 4px 20px rgba(0,0,0,0.5);
        }

        .left-header { display: flex; align-items: center; }

        .logo-title {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 1.5rem;
            font-weight: 800;
            color: #fff;
            text-transform: uppercase;
            letter-spacing: 1px;
            text-decoration: none;
        }

        .logo-title img { width: 40px; height: auto; filter: brightness(0) invert(1); }

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

        .hamburger:hover { color: #e10600; }

        nav { display: flex; gap: 5px; margin: 0 20px; }

        nav a {
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            padding: 8px 16px;
            border-radius: 4px;
            color: #ffffff !important;
            text-decoration: none;
            transition: all 0.2s ease;
            letter-spacing: 0.5px;
            opacity: 0.9;
            white-space: nowrap;
        }

        nav a:hover { color: #e10600 !important; opacity: 1; background: rgba(225, 6, 0, 0.1); }
        nav a.active { color: #e10600 !important; opacity: 1; font-weight: 700; background: rgba(225, 6, 0, 0.15); }

        /* Dropdown */
        .dropdown-container { position: relative; display: inline-block; }

        .welcome {
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.9rem;
            padding: 5px 12px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 30px;
            border: 1px solid rgba(225, 6, 0, 0.2);
            transition: all 0.2s ease;
        }

        .welcome:hover { background: rgba(225, 6, 0, 0.15); border-color: #e10600; }

        .welcome img.avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #e10600;
        }

        .welcome-text { color: #ccc; }
        .welcome-text span { font-weight: 700; }

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
            transition: all 0.2s;
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
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .dropdown-menu-modern a:last-child { border-bottom: none; }
        .dropdown-menu-modern a:hover { background: rgba(225, 6, 0, 0.2); color: white; padding-left: 24px; }
        .dropdown-menu-modern i { width: 24px; color: #e10600; }
        .dropdown-arrow-icon { margin-left: 6px; font-size: 0.7rem; color: #e10600; }
        .dropdown-container.open .dropdown-arrow-icon { transform: rotate(180deg); }

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

        .auth .btn {
            display: inline-block;
            padding: 8px 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            color: #fff;
            background-color: transparent;
            border: 1px solid rgba(225, 6, 0, 0.5);
            border-radius: 30px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            letter-spacing: 0.5px;
        }

        .auth .btn:hover {
            background-color: #e10600;
            border-color: #e10600;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(225, 6, 0, 0.4);
        }

        /* SUPER LICENCE CARD */
        .super-licence {
            max-width: 1000px;
            width: 90%;
            margin: 40px auto 30px;
            background: linear-gradient(145deg, #111111 0%, #0a0a0a 100%);
            border-radius: 24px;
            border: 2px solid;
            position: relative;
            overflow: hidden;
            box-shadow: 0 30px 60px rgba(0,0,0,0.8);
            transition: all 0.3s ease;
        }

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

        .fia-logo i { font-size: 2.5rem; }

=======
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

>>>>>>> f81424192996985be2da559c0f9a2c1f13f5eb7f
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

<<<<<<< HEAD
=======
        /* Main Content */
>>>>>>> f81424192996985be2da559c0f9a2c1f13f5eb7f
        .licence-content {
            padding: 30px;
            display: flex;
            gap: 40px;
            flex-wrap: wrap;
        }

<<<<<<< HEAD
=======
        /* Photo Section */
>>>>>>> f81424192996985be2da559c0f9a2c1f13f5eb7f
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

<<<<<<< HEAD
        .photo-frame:hover .photo-overlay { opacity: 1; }
=======
        .photo-frame:hover .photo-overlay {
            opacity: 1;
        }
>>>>>>> f81424192996985be2da559c0f9a2c1f13f5eb7f

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

<<<<<<< HEAD
        .info-section { flex: 1; }
=======
        /* Info Section */
        .info-section {
            flex: 1;
        }
>>>>>>> f81424192996985be2da559c0f9a2c1f13f5eb7f

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

<<<<<<< HEAD
=======
        /* Points Section - YELLOW (exception) */
>>>>>>> f81424192996985be2da559c0f9a2c1f13f5eb7f
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

<<<<<<< HEAD
=======
        /* Rank styling */
>>>>>>> f81424192996985be2da559c0f9a2c1f13f5eb7f
        .rank-value {
            font-size: 1.8rem;
            font-weight: 800;
        }

<<<<<<< HEAD
=======
        /* Security Features */
>>>>>>> f81424192996985be2da559c0f9a2c1f13f5eb7f
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

<<<<<<< HEAD
        .signature { font-size: 0.65rem; }

=======
        .signature {
            font-size: 0.65rem;
        }

        /* Buttons */
>>>>>>> f81424192996985be2da559c0f9a2c1f13f5eb7f
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
<<<<<<< HEAD
=======
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
>>>>>>> f81424192996985be2da559c0f9a2c1f13f5eb7f
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

        .alert {
            margin: 20px 30px 0;
            padding: 12px 20px;
            background: rgba(225, 6, 0, 0.15);
            border-left: 3px solid #e10600;
            color: #ff6b6b;
            font-size: 0.85rem;
            border-radius: 8px;
        }

        #fileInput { display: none; }

        .settings-card {
            max-width: 1000px;
            width: 90%;
            margin: 0 auto 60px;
            background: linear-gradient(145deg, #111, #0a0a0a);
            border-radius: 24px;
            border: 2px solid;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }

        .settings-card h3 {
            color: #fff;
            font-size: 1.5rem;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .settings-desc {
            color: #888;
            font-size: 0.85rem;
            margin-bottom: 20px;
        }

        .settings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }

        .setting-box {
            background: rgba(0,0,0,0.3);
            padding: 20px;
            border-radius: 12px;
            border: 1px solid rgba(255,255,255,0.05);
        }

        .setting-box h4 {
            color: #fff;
            margin-bottom: 15px;
            font-size: 1rem;
        }

        .setting-box input {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            background: #0a0a0a;
            border: 1px solid #333;
            color: white;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            outline: none;
            transition: 0.3s;
        }

        .setting-box input:focus { border-color: #e10600; }

        .btn-set {
            width: 100%;
            background: #1a1a1a;
            color: white;
            border: 1px solid #555;
            padding: 12px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            transition: 0.3s;
            font-family: 'Poppins', sans-serif;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-set:hover {
            background: #e10600;
            border-color: #e10600;
            color: white;
        }

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

        .btn-add-friend { background: #333; color: white; }
        .btn-add-friend:hover { background: #444; }
        .btn-send-msg { background: #e10600; color: white; }
        .btn-send-msg:hover { background: #b00500; }
        .clickable-user { cursor: pointer; }

        /* Mobile Navigation */
        @media (max-width: 992px) {
            .hamburger { display: block; }
            .left-header { display: none; }

            nav {
                display: none;
                position: fixed;
                top: 80px;
                left: 0;
                right: 0;
                background: #0a0a0a;
                border-bottom: 2px solid #e10600;
                flex-direction: column;
                gap: 0;
                margin: 0;
                padding: 10px 0;
                z-index: 1000;
            }

            nav.open { display: flex; }

            nav a {
                padding: 15px 20px;
                text-align: center;
                border-bottom: 1px solid rgba(255,255,255,0.1);
                width: 100%;
            }

            header { padding: 0 20px; }
        }

        /* Mobile responsive */
        @media (max-width: 700px) {
            body { padding-top: 80px; }

            .super-licence, .settings-card {
                width: 95%;
                margin-top: 15px;
                margin-bottom: 20px;
            }

            .licence-content {
                flex-direction: column;
                align-items: center;
                padding: 20px;
                gap: 25px;
            }

            .photo-section {
                flex: 0 0 auto;
                width: 160px;
            }

            .info-row {
                flex-direction: column;
                gap: 4px;
                padding: 10px 0;
            }

            .info-label {
                width: auto;
                font-size: 0.7rem;
            }

            .info-value { font-size: 0.95rem; }

            .fia-header {
                flex-direction: column;
                text-align: center;
                padding: 15px 20px;
            }

            .fia-logo h1 { font-size: 1.5rem; }

            .points-section {
                flex-direction: column;
                text-align: center;
                padding: 20px;
                gap: 20px;
            }

            .points-value { font-size: 2rem; }
            .rank-value { font-size: 1.5rem; }

            .action-buttons { padding: 15px 20px 20px; }

            .btn {
                padding: 12px 20px;
                font-size: 0.8rem;
                width: 100%;
                text-align: center;
            }

            .settings-card { padding: 20px; }
            .settings-card h3 { font-size: 1.2rem; }

            .settings-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .setting-box { padding: 15px; }

            .setting-box input {
                padding: 14px;
                font-size: 16px;
            }

            .btn-set {
                padding: 14px;
                font-size: 0.9rem;
            }

            .security-features { padding: 10px 20px; }

            .auth .welcome { padding: 3px 8px; }
            .welcome-text { font-size: 0.8rem; }
            .welcome img.avatar {
                width: 30px !important;
                height: 30px !important;
            }
        }

        @media (max-width: 480px) {
            .fia-logo h1 { font-size: 1.3rem; }
            .licence-type span { font-size: 0.7rem; }
            .photo-section { width: 140px; }
            .points-value { font-size: 1.8rem; }
            .rank-value { font-size: 1.3rem; }

            .welcome-text span {
                max-width: 70px;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .dropdown-menu-modern {
                position: fixed;
                top: 70px;
                right: 10px;
                left: 10px;
                min-width: auto;
            }
        }

        @media (max-width: 360px) {
            .btn {
                font-size: 0.7rem;
                padding: 10px 15px;
            }
        }
        /* Webkit browsers (Chrome, Safari, Edge) */
::-webkit-scrollbar {
    width: 6px;
    height: 6px; /* For horizontal scrollbars too */
}

::-webkit-scrollbar-track {
    background: #1a1a1a;
    border-radius: 3px;
}

::-webkit-scrollbar-thumb {
    background: #e10600;
    border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
    background: #ff2b2b; /* Lighter red on hover */
}

/* Firefox */
* {
    scrollbar-width: thin;
    scrollbar-color: #e10600 #1a1a1a;
}
    </style>
</head>
<body>

<div class="bg-lines"></div>

<header>
    <div class="left-header">
<<<<<<< HEAD
        <a href="/f1fanclub/index.php" class="logo-title">
            <img src="https://upload.wikimedia.org/wikipedia/commons/3/33/F1.svg" alt="F1 Logo">
            <span>Fan Club</span>
        </a>
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

    <?php if (isset($_SESSION['username'])): ?>
        <div class="dropdown-container" id="userDropdownContainer">
            <div class="auth">
                <div class="welcome">
                    <?php if ($user['profile_image']): ?>
                        <img src="/f1fanclub/uploads/<?php echo htmlspecialchars($user['profile_image']); ?>" class="avatar clickable-user"
                            alt="Profilkép" onclick="openUserProfile('<?php echo htmlspecialchars(addslashes($username)); ?>')"
                            style="border: 2px solid <?php echo htmlspecialchars($teamColor); ?>;">
=======
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
>>>>>>> f81424192996985be2da559c0f9a2c1f13f5eb7f
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

<<<<<<< HEAD
=======
<!-- SUPER LICENCE CARD WITH TEAM COLOR -->
>>>>>>> f81424192996985be2da559c0f9a2c1f13f5eb7f
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
<<<<<<< HEAD

=======
            <div class="info-row">
                <div class="info-label" style="color: <?php echo $teamColor; ?>;">LEJÁRAT DÁTUMA</div>
                <div class="info-value"><?php echo date('Y. m. d.', strtotime('+5 years')); ?></div>
            </div>

            <!-- Points Section - YELLOW (exception from team color) -->
>>>>>>> f81424192996985be2da559c0f9a2c1f13f5eb7f
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

<<<<<<< HEAD
<div class="settings-card" style="border-color: <?php echo $teamColor; ?>;">
    <h3><i class="fas fa-cog"></i> Biztonság és Adatmódosítás</h3>
    <p class="settings-desc">A változtatások megerősítéséhez e-mailt küldünk a fiókodhoz tartozó címre!</p>
    
    <div class="settings-grid">
        <div class="setting-box">
            <h4><i class="fas fa-user-edit"></i> Névváltoztatás</h4>
            <form method="POST">
                <input type="hidden" name="setting_action" value="change_username">
                <input type="text" name="new_username" placeholder="Új felhasználónév" required>
                <button type="submit" class="btn-set" style="border-color: <?php echo $teamColor; ?>;">Kérelmezés</button>
            </form>
        </div>
        
        <div class="setting-box">
            <h4><i class="fas fa-envelope"></i> E-mail módosítás</h4>
            <form method="POST">
                <input type="hidden" name="setting_action" value="change_email">
                <input type="email" name="new_email" placeholder="Új e-mail cím" required>
                <button type="submit" class="btn-set" style="border-color: <?php echo $teamColor; ?>;">Kérelmezés</button>
            </form>
        </div>
        
        <div class="setting-box">
            <h4><i class="fas fa-lock"></i> Jelszó módosítás</h4>
            <form method="POST">
                <input type="hidden" name="setting_action" value="change_password">
                <input type="password" name="new_password" placeholder="Új jelszó (min 8 kar., kis/nagybetű, szám)" required>
                <button type="submit" class="btn-set" style="border-color: <?php echo $teamColor; ?>;">Kérelmezés</button>
            </form>
        </div>
    </div>
</div>

<script>
    // Hamburger menu
    document.addEventListener('DOMContentLoaded', function() {
        const hamburgerBtn = document.getElementById('hamburgerBtn');
        const mainNav = document.getElementById('mainNav');
        
        if (hamburgerBtn && mainNav) {
            hamburgerBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                mainNav.classList.toggle('open');
            });
            
            document.addEventListener('click', function(e) {
                if (mainNav.classList.contains('open') && !mainNav.contains(e.target) && !hamburgerBtn.contains(e.target)) {
                    mainNav.classList.remove('open');
                }
            });
            
            mainNav.querySelectorAll('a').forEach(link => {
                link.addEventListener('click', () => mainNav.classList.remove('open'));
            });
        }

        // Dropdown menu
=======
<script>
    // DROPDOWN MENU TOGGLE
    document.addEventListener('DOMContentLoaded', function() {
>>>>>>> f81424192996985be2da559c0f9a2c1f13f5eb7f
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

<<<<<<< HEAD
=======
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

>>>>>>> f81424192996985be2da559c0f9a2c1f13f5eb7f
</body>
</html>