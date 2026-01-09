<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

/* ==== ADATBÁZIS KAPCSOLAT ==== */
$DB_HOST = "localhost";
$DB_USER = "swmjndga_swmjndga";
$DB_PASS = "Teszt1234!";
$DB_NAME = "swmjndga_f1adat";

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) {
    die("Adatbázis hiba: " . $conn->connect_error);
}

/* ==== LOGIN + ADMIN ELLENŐRZÉS ==== */
if (!isset($_SESSION['username'])) {
    die("Nincs jogosultság (nem vagy bejelentkezve).");
}
$username = $_SESSION['username'];

$stmt = $conn->prepare("SELECT role FROM users WHERE username=?");
$stmt->bind_param("s", $username);
$stmt->execute();
$r = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$r || $r['role'] !== 'admin') {
    die("Nincs jogosultság (nem admin).");
}

/* ==== HÍR MENTÉSE ==== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title   = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $image   = null;

    // fontos / általános hír
    $isFeatured = isset($_POST['is_featured']) && $_POST['is_featured'] == '1' ? 1 : 0;

    if ($title === '' || $content === '') {
        die("A cím és a tartalom kötelező.");
    }

    // kép feltöltés
    if (!empty($_FILES['image']['name'])) {
        $targetDir  = "uploads/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $target = $targetDir . basename($_FILES['image']['name']);

        if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
            $image = $target;
        }
    }

    $stmt = $conn->prepare(
        "INSERT INTO news (title, content, image, author, is_featured) 
         VALUES (?, ?, ?, ?, ?)"
    );
    $stmt->bind_param("ssssi", $title, $content, $image, $username, $isFeatured);
    $stmt->execute();
    $stmt->close();

    header("Location: /news/hirszerkeszto.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
<meta charset="UTF-8">
<title>Új hír létrehozása – Admin</title>

<style>
    body {
        margin: 0;
        padding: 0;
        background: linear-gradient(135deg, #0d0d0d, #1a1a1a);
        font-family: 'Poppins', sans-serif;
        color: #fff;
    }

    .container {
        width: min(700px, 90%);
        margin: 60px auto;
        background: #151515;
        padding: 30px 35px;
        border-radius: 14px;
        box-shadow: 0 0 25px rgba(0,0,0,0.7);
        border: 1px solid rgba(255,255,255,0.05);
    }

    h2 {
        text-align: center;
        font-size: 1.9rem;
        margin-bottom: 25px;
        text-transform: uppercase;
        color: #e10600;
        letter-spacing: 1px;
    }

    h2::after {
        content: "";
        display: block;
        width: 80px;
        height: 3px;
        margin: 10px auto 0;
        border-radius: 20px;
        background: linear-gradient(90deg, #e10600, #ff3b3b);
    }

    label {
        display: block;
        margin-bottom: 6px;
        font-weight: 600;
        color: #ddd;
    }

    input[type="text"],
    textarea,
    input[type="file"] {
        width: 100%;
        padding: 12px 14px;
        border-radius: 8px;
        border: 1px solid #333;
        background: #0f0f0f;
        color: white;
        margin-bottom: 18px;
        font-size: 1rem;
        transition: 0.2s;
    }

    input[type="text"]:focus,
    textarea:focus {
        outline: none;
        border-color: #e10600;
        box-shadow: 0 0 8px rgba(225,6,0,0.5);
    }

    textarea {
        height: 180px;
        resize: vertical;
    }

    .btn {
        width: 100%;
        padding: 12px;
        background: #e10600;
        border: none;
        border-radius: 8px;
        color: #fff;
        font-size: 1.1rem;
        font-weight: 700;
        cursor: pointer;
        transition: 0.2s;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }

    .btn:hover {
        background: #ff2c2c;
        box-shadow: 0 0 12px rgba(255,0,0,0.6);
    }

    .back-link {
        display: block;
        text-align: center;
        margin-top: 15px;
        color: #999;
        text-decoration: none;
        transition: 0.2s;
    }

    .back-link:hover {
        color: #fff;
        text-decoration: underline;
    }

    .radio-group {
        display: flex;
        gap: 16px;
        margin-bottom: 18px;
        align-items: center;
        flex-wrap: wrap;
    }

    .radio-group label {
        margin-bottom: 0;
        font-weight: 500;
    }
</style>

</head>
<body>

<div class="container">
<form method="post" enctype="multipart/form-data">

    <h2>Új hír létrehozása</h2>

    <label for="title">Hír címe</label>
    <input type="text" name="title" id="title"
           placeholder="Pl.: Verstappen új szerződést írt alá" required>

    <label for="content">Hír tartalma</label>
    <textarea name="content" id="content"
              placeholder="Írd le a hír teljes tartalmát..." required></textarea>

    <label>Hír típusa</label>
    <div class="radio-group">
        <label>
            <input type="radio" name="is_featured" value="1">
            Fontos hír (kiemelt kártya)
        </label>
        <label>
            <input type="radio" name="is_featured" value="0" checked>
            Általános hír
        </label>
    </div>

    <label for="image">Kép feltöltése (opcionális)</label>
    <input type="file" name="image" id="image">

    <button type="submit" class="btn">Hír publikálása</button>

    <a href="/news/hirszerkeszto.php" class="back-link">⬅ Vissza a hírszerkesztőhöz</a>

</form>
</div>

</body>
</html>
