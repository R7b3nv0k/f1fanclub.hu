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
        body {
            background: linear-gradient(135deg, #000 0%, #1a1a1a 40%, #111 100%);
            color: white;
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .profile-container {
            margin-top: 80px;
            background: #151515;
            padding: 40px;
            border-radius: 12px;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 0 20px rgba(225, 6, 0, 0.2);
            border-top: 3px solid #e10600;
            text-align: center;
        }

        /* --- ÚJ CSS A PROFILKÉPHEZ --- */
        .profile-pic-wrapper {
            position: relative;
            width: 150px;
            height: 150px;
            margin: 0 auto 20px auto;
            border-radius: 50%;
            cursor: pointer; /* Kéz ikon, ha ráviszed az egeret */
            overflow: hidden; /* Hogy a körön kívül ne lógjon ki semmi */
            border: 4px solid #e10600;
        }

        .profile-pic-large {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        /* A sötét réteg és a kamera ikon/szöveg */
        .profile-pic-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6); /* Félig átlátszó fekete */
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            opacity: 0; /* Alapból láthatatlan */
            transition: opacity 0.3s ease;
        }

        /* Ha a wrapper fölé viszed az egeret, megjelenik az overlay */
        .profile-pic-wrapper:hover .profile-pic-overlay {
            opacity: 1;
        }

        .camera-icon {
            font-size: 24px;
            margin-bottom: 5px;
        }

        .change-text {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        /* Elrejtjük az eredeti fájl inputot */
        #fileInput {
            display: none;
        }
        /* --- CSS VÉGE --- */

        .info-group {
            margin-bottom: 20px;
            text-align: left;
            background: #222;
            padding: 15px;
            border-radius: 8px;
        }
        .info-label {
            color: #888;
            font-size: 0.9em;
            display: block;
        }
        .info-value {
            font-size: 1.2em;
            font-weight: 600;
            color: #fff;
        }
        .back-btn {
            display: inline-block;
            margin-top: 20px;
            color: #ccc;
            text-decoration: none;
        }
        .back-btn:hover { color: white; }
        .alert {
            background: #333;
            color: #e10600;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            border-left: 4px solid #e10600;
        }
    </style>
</head>
<body>

    <div class="profile-container">
        
        <?php if($message): ?>
            <div class="alert"><?php echo $message; ?></div>
        <?php endif; ?>

        <h2>Profil Adatok</h2>

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

        <p style="font-size: 12px; color: #666; margin-top: 5px;">Kattints a képre a módosításhoz (Max 5MB).</p>

        <a href="/f1fanclub/index.php" class="back-btn">← Vissza a főoldalra</a>

    </div>

</body>
</html>