<?php
session_start();
// Hibaüzenetek bekapcsolása fejlesztés alatt
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Ha nincs bejelentkezve, dobja vissza a főoldalra
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

// --- FELTÖLTÉS KEZELÉSE ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profile_image'])) {

    $target_dir = "../uploads/";
    // Biztosítjuk, hogy egyedi neve legyen a fájlnak
    $file_extension = strtolower(pathinfo($_FILES["profile_image"]["name"], PATHINFO_EXTENSION));
    $new_filename = $username . "_" . time() . "." . $file_extension;
    $target_file = $target_dir . $new_filename;
    
    $uploadOk = 1;

    // 1. Ellenőrzés: Valódi kép-e?
    $check = getimagesize($_FILES["profile_image"]["tmp_name"]);
    if($check !== false) {
        $uploadOk = 1;
    } else {
        $message = "A feltöltött fájl nem kép.";
        $uploadOk = 0;
    }

    // 2. Fájlméret ellenőrzés (pl. max 5MB)
    if ($_FILES["profile_image"]["size"] > 5000000) {
        $message = "A kép túl nagy! (Max 5MB)";
        $uploadOk = 0;
    }

    // 3. Fájltípus ellenőrzés
    if($file_extension != "jpg" && $file_extension != "png" && $file_extension != "jpeg" && $file_extension != "gif" ) {
        $message = "Csak JPG, JPEG, PNG és GIF fájlok engedélyezettek.";
        $uploadOk = 0;
    }

    // Ha minden oké, feltöltjük
    if ($uploadOk == 1) {
        if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file)) {
            // Adatbázis frissítése
            $stmt = $conn->prepare("UPDATE users SET profile_image = ? WHERE username = ?");
            $stmt->bind_param("ss", $new_filename, $username);
            
            if ($stmt->execute()) {
                // Sikeres feltöltés után frissítjük az oldalt, hogy ne ragadjon be a POST adat
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

// --- ADATOK LEKÉRÉSE A MEGJELENÍTÉSHEZ ---
$stmt = $conn->prepare("SELECT email, profile_image, fav_team FROM users WHERE username=?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($username); ?> Profilja</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/f1fanclub/css/style.css"> 
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
        }

        /* F1-themed background with gradient */
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

        .profile-container {
            margin-top: 60px;
            margin-bottom: 40px;
            background: linear-gradient(145deg, #111111 0%, #1a1a1a 100%);
            padding: 50px;
            border-radius: 30px;
            width: 90%;
            max-width: 1400px; /* Increased to accommodate larger profile pic */
            border: 1px solid rgba(225, 6, 0, 0.3);
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.8),
                        0 0 50px rgba(225, 6, 0, 0.2);
            position: relative;
            overflow: hidden;
        }

        /* F1-inspired accent line */
        .profile-container::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, 
                        transparent 0%, 
                        #e10600 20%, 
                        #e10600 80%, 
                        transparent 100%);
            z-index: 2;
        }

        /* Glowing effect */
        .profile-container::after {
            content: "";
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(45deg, 
                        transparent 30%, 
                        rgba(225, 6, 0, 0.15) 50%, 
                        transparent 70%);
            border-radius: 32px;
            z-index: -1;
            animation: borderGlow 4s infinite;
        }

        @keyframes borderGlow {
            0%, 100% { opacity: 0.3; }
            50% { opacity: 0.8; }
        }

        /* Main layout - Flex container */
        .profile-content {
            display: flex;
            gap: 60px;
            align-items: flex-start;
            position: relative;
            z-index: 2;
        }

        /* Left section - Profile picture - EVEN BIGGER */
        .profile-left {
            flex: 0 0 550px; /* Increased from 450px to 550px */
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* Profile picture wrapper - F1 style */
        .profile-pic-wrapper {
            position: relative;
            width: 100%;
            aspect-ratio: 1;
            border-radius: 30px;
            cursor: pointer;
            overflow: hidden;
            border: 4px solid #e10600;
            box-shadow: 0 0 50px rgba(225, 6, 0, 0.5),
                        0 20px 40px rgba(0, 0, 0, 0.8);
            transition: all 0.5s cubic-bezier(0.2, 0.8, 0.2, 1);
        }

        .profile-pic-wrapper:hover {
            transform: translateY(-8px) scale(1.03);
            border-color: #ff1a00;
            box-shadow: 0 0 70px rgba(225, 6, 0, 0.7),
                        0 30px 60px rgba(0, 0, 0, 0.9);
        }

        .profile-pic-large {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            transition: transform 0.6s ease;
        }

        .profile-pic-wrapper:hover .profile-pic-large {
            transform: scale(1.15);
        }

        /* F1-style overlay */
        .profile-pic-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, 
                        rgba(225, 6, 0, 0.95) 0%, 
                        rgba(200, 5, 0, 0.95) 100%);
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            opacity: 0;
            transition: opacity 0.4s ease;
            backdrop-filter: blur(4px);
        }

        .profile-pic-wrapper:hover .profile-pic-overlay {
            opacity: 1;
        }

        .change-text {
            font-size: 28px; /* Larger text for bigger picture */
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 4px;
            text-shadow: 0 4px 8px rgba(0, 0, 0, 0.5);
            border: 3px solid white;
            padding: 15px 30px;
            border-radius: 50px;
            transform: translateY(30px);
            transition: transform 0.4s ease;
        }

        .profile-pic-wrapper:hover .change-text {
            transform: translateY(0);
        }

        /* Speed lines effect on hover */
        .profile-pic-wrapper::before {
            content: "";
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg,
                        transparent 30%,
                        rgba(255, 255, 255, 0.1) 50%,
                        transparent 70%);
            transform: rotate(45deg) translateY(100%);
            transition: transform 0.8s ease;
            z-index: 2;
            pointer-events: none;
        }

        .profile-pic-wrapper:hover::before {
            transform: rotate(45deg) translateY(-100%);
        }

        /* SIMPLE SMALL TEXT - No styling, just basic */
        .upload-hint-small {
            margin-top: 20px;
            font-size: 14px;
            color: #888;
            text-align: center;
        }

        /* Right section - Profile info */
        .profile-right {
            flex: 1;
            padding-left: 30px;
            border-left: 3px solid rgba(225, 6, 0, 0.3);
        }

        /* Heading */
        h2 {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 40px;
            background: linear-gradient(135deg, #fff 20%, #e10600 80%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-transform: uppercase;
            letter-spacing: 4px;
            position: relative;
            display: inline-block;
        }

        h2::after {
            content: "";
            position: absolute;
            bottom: -15px;
            left: 0;
            width: 120px;
            height: 4px;
            background: linear-gradient(90deg, 
                        #e10600 0%, 
                        #e10600 80%, 
                        transparent 100%);
            border-radius: 2px;
        }

        /* Info groups - F1 card style */
        .info-group {
            margin-bottom: 30px;
            text-align: left;
            background: linear-gradient(145deg, #1e1e1e 0%, #151515 100%);
            padding: 25px 30px;
            border-radius: 20px;
            border-left: 6px solid #e10600;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
        }

        .info-group:hover {
            transform: translateX(15px);
            border-left-width: 8px;
            border-left-color: #ff1a00;
            box-shadow: 0 15px 40px rgba(225, 6, 0, 0.3);
        }

        /* Subtle F1 gradient overlay on hover */
        .info-group:hover::after {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(90deg, 
                        rgba(225, 6, 0, 0.15) 0%, 
                        transparent 100%);
            pointer-events: none;
        }

        /* Info group speed lines */
        .info-group::before {
            content: "";
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(225, 6, 0, 0.05));
            transform: skewX(-15deg) translateX(100px);
            transition: transform 0.6s ease;
        }

        .info-group:hover::before {
            transform: skewX(-15deg) translateX(-20px);
        }

        .info-label {
            color: #e10600;
            font-size: 1rem;
            display: block;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-weight: 700;
        }

        .info-value {
            font-size: 1.8rem;
            font-weight: 700;
            color: #fff;
            display: block;
            text-shadow: 0 4px 8px rgba(0, 0, 0, 0.5);
            line-height: 1.4;
        }

        /* Alert message */
        .alert {
            background: linear-gradient(145deg, #2a1a1a 0%, #221515 100%);
            color: #ff6b6b;
            padding: 20px 25px;
            margin-bottom: 30px;
            border-radius: 15px;
            border-left: 6px solid #e10600;
            border-right: 6px solid #e10600;
            text-align: center;
            font-weight: 600;
            letter-spacing: 1px;
            box-shadow: 0 10px 25px rgba(225, 6, 0, 0.3);
            position: relative;
            font-size: 1.1rem;
            backdrop-filter: blur(5px);
        }

        .alert::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(90deg, 
                        transparent 30%, 
                        rgba(225, 6, 0, 0.15) 50%, 
                        transparent 70%);
            pointer-events: none;
            animation: alertPulse 2s infinite;
        }

        @keyframes alertPulse {
            0%, 100% { opacity: 0.3; }
            50% { opacity: 0.8; }
        }

        /* Back button - F1 style */
        .back-btn {
            display: inline-block;
            margin-top: 40px;
            color: #fff;
            text-decoration: none;
            font-size: 1.2rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 3px;
            padding: 18px 45px;
            background: linear-gradient(145deg, #222 0%, #1a1a1a 100%);
            border: 2px solid #e10600;
            border-radius: 60px;
            transition: all 0.4s cubic-bezier(0.2, 0.8, 0.2, 1);
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
        }

        .back-btn:hover {
            background: linear-gradient(145deg, #e10600 0%, #ff1a00 100%);
            color: white;
            transform: translateY(-5px);
            box-shadow: 0 25px 40px rgba(225, 6, 0, 0.5);
            border-color: transparent;
        }

        .back-btn:active {
            transform: translateY(0);
        }

        .back-btn i {
            margin-right: 10px;
            font-style: normal;
            display: inline-block;
            transition: transform 0.3s ease;
        }

        .back-btn:hover i {
            transform: translateX(-8px);
        }

        /* Hide file input */
        #fileInput {
            display: none;
        }

        /* F1 chequered flag pattern */
        .chequered-bg {
            position: absolute;
            bottom: 0;
            right: 0;
            width: 300px;
            height: 300px;
            background-image: 
                linear-gradient(45deg, #e10600 25%, transparent 25%),
                linear-gradient(-45deg, #e10600 25%, transparent 25%),
                linear-gradient(45deg, transparent 75%, #e10600 75%),
                linear-gradient(-45deg, transparent 75%, #e10600 75%);
            background-size: 30px 30px;
            background-position: 0 0, 0 15px, 15px -15px, -15px 0px;
            opacity: 0.05;
            pointer-events: none;
            z-index: 0;
        }

        /* Floating F1 car silhouette */
        .f1-silhouette {
            position: absolute;
            top: 30px;
            right: 30px;
            width: 180px;
            height: 70px;
            background: rgba(225, 6, 0, 0.1);
            clip-path: polygon(0% 0%, 100% 0%, 95% 100%, 5% 100%);
            transform: skewX(-10deg);
            z-index: 1;
            animation: floatCar 6s infinite ease-in-out;
        }

        @keyframes floatCar {
            0%, 100% { transform: skewX(-10deg) translateX(0); }
            50% { transform: skewX(-10deg) translateX(-15px); }
        }

        /* Responsive design */
        @media (max-width: 1200px) {
            .profile-left {
                flex: 0 0 450px; /* Still large but smaller on medium screens */
            }
        }

        @media (max-width: 968px) {
            .profile-container {
                padding: 35px;
            }
            
            .profile-content {
                gap: 40px;
            }
            
            .profile-left {
                flex: 0 0 380px;
            }
        }

        @media (max-width: 768px) {
            .profile-container {
                margin-top: 30px;
                padding: 30px;
            }
            
            .profile-content {
                flex-direction: column;
                gap: 40px;
            }
            
            .profile-left {
                flex: 0 0 auto;
                width: 380px;
                margin: 0 auto;
            }
            
            .profile-right {
                border-left: none;
                padding-left: 0;
                width: 100%;
            }
            
            h2 {
                font-size: 2.5rem;
                margin-bottom: 30px;
            }
            
            h2::after {
                left: 50%;
                transform: translateX(-50%);
            }
            
            h2, .info-group {
                text-align: center;
            }
            
            .info-group:hover {
                transform: translateY(-8px);
            }
            
            .back-btn {
                display: block;
                text-align: center;
            }
        }

        @media (max-width: 480px) {
            .profile-container {
                padding: 25px;
            }
            
            .profile-left {
                width: 280px;
            }
            
            .change-text {
                font-size: 18px;
                padding: 12px 20px;
            }
            
            h2 {
                font-size: 2rem;
            }
            
            .info-group {
                padding: 20px;
            }
            
            .info-value {
                font-size: 1.3rem;
            }
            
            .back-btn {
                font-size: 1rem;
                padding: 15px 30px;
            }
            
            .upload-hint-small {
                font-size: 12px;
            }
        }

        /* Animation for page load */
        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .profile-left {
            animation: slideInLeft 0.8s ease-out;
        }

        .profile-right {
            animation: slideInRight 0.8s ease-out;
        }
    </style>
</head>
<body>

    <div class="profile-container">
        
        <!-- F1 Decorative Elements -->
        <div class="chequered-bg"></div>
        <div class="f1-silhouette"></div>
        
        <?php if($message): ?>
            <div class="alert"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="profile-content">
            <!-- Left side - Profile picture (EVEN BIGGER) -->
            <div class="profile-left">
                <?php 
                $img_src = $user['profile_image'] ? "/f1fanclub/uploads/" . htmlspecialchars($user['profile_image']) : "https://upload.wikimedia.org/wikipedia/commons/7/7c/Profile_avatar_placeholder_large.png";
                ?>

                <form action="" method="post" enctype="multipart/form-data" id="profileForm">
                    <label for="fileInput">
                        <div class="profile-pic-wrapper">
                            <img src="<?php echo $img_src; ?>" class="profile-pic-large" alt="Profilkép">
                            <div class="profile-pic-overlay">
                                <span class="change-text">Csere</span>
                            </div>
                        </div>
                    </label>
                    <input type="file" name="profile_image" id="fileInput" onchange="document.getElementById('profileForm').submit();">
                </form>
                
                <!-- SIMPLE SMALL TEXT - No styling, just basic -->
                <div class="upload-hint-small">
                    ⚡ Kattints a képre a módosításhoz (Max 5MB) ⚡
                </div>
            </div>

            <!-- Right side - Profile info -->
            <div class="profile-right">
                <h2>Profil Adatok</h2>
                
                <div class="info-group">
                    <span class="info-label">Felhasználónév</span>
                    <span class="info-value"><?php echo htmlspecialchars($username); ?></span>
                </div>

                <div class="info-group">
                    <span class="info-label">Email cím</span>
                    <span class="info-value"><?php echo htmlspecialchars($user['email']); ?></span>
                </div>

                <div class="info-group">
                    <span class="info-label">Kedvenc Csapat</span>
                    <span class="info-value"><?php echo htmlspecialchars($user['fav_team'] ?? "Nincs kiválasztva"); ?></span>
                </div>

                <a href="/f1fanclub/index.php" class="back-btn"><i>←</i> Vissza a főoldalra</a>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <a href="/f1fanclub/admin/admin.php" class="back-btn" style="border-color: #ff9800; color: #ff9800; margin-left: 15px;"><i>⚙️</i> Admin Panel</a>
                <?php endif; ?>
            </div>
        </div>

    </div>

</body>
</html>