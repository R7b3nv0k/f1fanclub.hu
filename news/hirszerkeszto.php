<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: /login/login.html");
    exit;
}

$DB_HOST = "localhost";
$DB_USER = "swmjndga_swmjndga";
$DB_PASS = "Teszt1234!";
$DB_NAME = "swmjndga_f1adat";

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

$username = $_SESSION['username'];

$stmt = $conn->prepare("SELECT role FROM users WHERE username=?");
$stmt->bind_param("s", $username);
$stmt->execute();
$roleRow = $stmt->get_result()->fetch_assoc();

if ($roleRow['role'] !== 'admin') {
    die("<h1>Nincs jogosultság!</h1>");
}

/* ==== HÍREK LEKÉRDEZÉSE ==== */
$news = $conn->query("SELECT * FROM news ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="hu">
<head>
<meta charset="UTF-8">
<title>Hírkezelő – Admin</title>
<link rel="stylesheet" href="/f1fanclub/css/style.css">
<style>
.admin-container {
    width: 90%;
    max-width: 900px;
    margin: 40px auto;
    background: #111;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 0 15px black;
}
.news-item {
    background: #1a1a1a;
    padding: 15px;
    border-radius: 10px;
    margin-bottom: 15px;
    box-shadow: 0 0 10px #000;
}
.actions a {
    margin-right: 10px;
    text-decoration: none;
    font-weight: bold;
}
</style>
</head>

<body>

<header>
  <div class="left-header">
    <h1 class="logo-title">
      <img src="https://upload.wikimedia.org/wikipedia/commons/3/33/F1.svg" class="f1-logo">
      <span>Hírkezelő</span>
    </h1>
  </div>

  <nav>
    <a href="/f1fanclub/index.php">Home</a>
    <a href="/f1fanclub/Championship/championship.php">Championship</a>
    <a href="/f1fanclub/news/news.php">News</a>
  </nav>
</header>

<div class="admin-container">
    <h2>Hírek kezelése</h2>

    <a href="/f1fanclub/news/ujhir.php" class="btn" style="margin-bottom:20px; display:inline-block;">+ Új hír</a>

    <?php while ($n = $news->fetch_assoc()): ?>
        <div class="news-item">
            <h3><?= htmlspecialchars($n['title']); ?></h3>
            <p><small><?= $n['created_at']; ?></small></p>

            <div class="actions">
                <a href="f1fanclub/news/edit_hir.php?id=<?= $n['id']; ?>" class="btn">✏️ Szerkesztés</a>
                <a href="/f1fanclub/news/torol_hir.php?id=<?= $n['id']; ?>" class="btn" style="background:#c40000;"
                   onclick="return confirm('Biztos törlöd?');">🗑️ Törlés</a>
            </div>
        </div>
    <?php endwhile; ?>
</div>

</body>
</html>
