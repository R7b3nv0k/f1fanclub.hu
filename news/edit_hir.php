<?php
session_start();

/* ==== LOGIN & ADMIN CHECK ==== */
if (!isset($_SESSION['username'])) {
    header("Location: /login/login.html");
    exit;
}

$username = $_SESSION['username'];

$DB_HOST = "localhost";
$DB_USER = "swmjndga_swmjndga";
$DB_PASS = "Teszt1234!";
$DB_NAME = "swmjndga_f1adat";

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) {
    die("Adatbázis hiba: " . $conn->connect_error);
}

// szerep ellenőrzés
$stmt = $conn->prepare("SELECT role FROM users WHERE username=?");
$stmt->bind_param("s", $username);
$stmt->execute();
$roleRow = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$roleRow || $roleRow['role'] !== 'admin') {
    die("Nincs jogosultságod ehhez a művelethez.");
}

/* ==== HÍR BETÖLTÉSE / MENTÉSE ==== */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // MENTÉS
    if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
        die("Hiányzó hír ID.");
    }

    $id      = (int)$_POST['id'];
    $title   = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $oldImg  = $_POST['old_image'] ?? null;
    $image   = $oldImg;

    // új kép feltöltése (ha van)
    if (!empty($_FILES['image']['name'])) {
        $target = "uploads/" . basename($_FILES['image']['name']);
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
            // régi kép törlése, ha volt
            if (!empty($oldImg) && file_exists($oldImg)) {
                @unlink($oldImg);
            }
            $image = $target;
        }
    }

    $stmt = $conn->prepare("UPDATE news SET title=?, content=?, image=? WHERE id=?");
    $stmt->bind_param("sssi", $title, $content, $image, $id);
    $stmt->execute();
    $stmt->close();

    $conn->close();
    header("Location: /news/hirszerkeszto.php");
    exit;

} else {
    // ŰRLAP BETÖLTÉSE (GET)
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        die("Hiányzó hír ID.");
    }

    $id = (int)$_GET['id'];

    $stmt = $conn->prepare("SELECT * FROM news WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $news = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$news) {
        die("A hír nem található.");
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
<meta charset="UTF-8">
<title>Hír szerkesztése – Admin</title>
<link rel="stylesheet" href="/css/style.css">
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
    textarea {
        height: 180px;
        resize: vertical;
    }
    input[type="text"]:focus,
    textarea:focus {
        outline: none;
        border-color: #e10600;
        box-shadow: 0 0 8px rgba(225,6,0,0.5);
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
    .current-image {
        margin-bottom: 15px;
        text-align: center;
    }
    .current-image img {
        max-width: 100%;
        max-height: 250px;
        border-radius: 10px;
        box-shadow: 0 0 12px rgba(0,0,0,0.8);
    }
</style>
</head>
<body>

<div class="container">
<form method="post" enctype="multipart/form-data">

    <h2>Hír szerkesztése</h2>

    <input type="hidden" name="id" value="<?= htmlspecialchars($news['id']); ?>">
    <input type="hidden" name="old_image" value="<?= htmlspecialchars($news['image'] ?? ''); ?>">

    <label for="title">Hír címe</label>
    <input type="text" id="title" name="title"
           value="<?= htmlspecialchars($news['title']); ?>" required>

    <label for="content">Hír tartalma</label>
    <textarea id="content" name="content" required><?= htmlspecialchars($news['content']); ?></textarea>

    <?php if (!empty($news['image'])): ?>
        <div class="current-image">
            <p>Jelenlegi kép:</p>
            <img src="<?= htmlspecialchars($news['image']); ?>" alt="Hír képe">
        </div>
    <?php endif; ?>

    <label for="image">Új kép feltöltése (ha cserélni szeretnéd)</label>
    <input type="file" id="image" name="image">

    <button type="submit" class="btn">Változtatások mentése</button>

    <a href="/news/hirszerkeszto.php" class="back-link">⬅ Vissza a hírszerkesztőhöz</a>

</form>
</div>

</body>
</html>
