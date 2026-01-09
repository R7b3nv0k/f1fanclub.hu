<?php
// Hibák megjelenítése, hogy lássuk, ha valami baj van
error_reporting(E_ALL);
ini_set('display_errors', 1);

$DB_HOST = "localhost";
$DB_USER = "swmjndga_swmjndga";
$DB_PASS = "Teszt1234!";
$DB_NAME = "swmjndga_f1adat";

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) {
    die("Adatbázis hiba: " . $conn->connect_error);
}

$message = "";
$messageType = ""; // success vagy error

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    // 1. Megnézzük, létezik-e ez a token, és nincs-e még aktiválva
    $stmt = $conn->prepare("SELECT id, username FROM users WHERE verification_token = ? AND is_verified = 0");
    
    if ($stmt === false) {
        die("SQL Hiba (SELECT): " . $conn->error);
    }

    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $username = $user['username'];

        // 2. Ha megvan, aktiváljuk a fiókot (is_verified = 1) és töröljük a tokent
        $update = $conn->prepare("UPDATE users SET is_verified = 1, verification_token = NULL WHERE id = ?");
        
        if ($update === false) {
            die("SQL Hiba (UPDATE): " . $conn->error);
        }

        $update->bind_param("i", $user['id']);
        
        if ($update->execute()) {
            $message = "Szia $username! <br> A fiókodat sikeresen aktiváltuk!";
            $messageType = "success";
        } else {
            $message = "Hiba történt az adatbázis frissítésekor: " . $stmt->error;
            $messageType = "error";
        }
        $update->close();
    } else {
        // Lehet, hogy már aktiválva van? Nézzük meg.
        $checkActive = $conn->prepare("SELECT id FROM users WHERE verification_token = ? OR (verification_token IS NULL AND is_verified = 1)");
        // Ez egy egyszerűsített ellenőrzés, de a lényeg:
        // Ha a token nincs a DB-ben a 'is_verified=0' sorok között, akkor vagy rossz a token, vagy már aktivált.
        
        $message = "Ez az aktiváló link érvénytelen vagy már felhasználtad.";
        $messageType = "error";
    }
    $stmt->close();
} else {
    $message = "Hiányzó aktiváló kód (token)!";
    $messageType = "error";
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="hu">
<head>
  <meta charset="UTF-8">
  <title>Fiók Aktiválás – F1 Fan Club</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #000 0%, #1a1a1a 40%, #111 100%);
      color: #eee;
      height: 100vh;
      margin: 0;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .card {
      background: #151515;
      padding: 40px;
      border-radius: 12px;
      box-shadow: 0 0 20px rgba(225, 6, 0, 0.3);
      width: 400px;
      text-align: center;
      border-top: 3px solid #e10600;
    }
    h2 {
      margin-top: 0;
      color: #fff;
    }
    .message {
      margin: 20px 0;
      font-size: 16px;
      line-height: 1.5;
    }
    .success { color: #52E252; }
    .error { color: #e10600; }
    
    .btn {
      display: inline-block;
      background: #e10600;
      color: white;
      padding: 10px 20px;
      text-decoration: none;
      border-radius: 5px;
      font-weight: bold;
      transition: 0.3s;
      margin-top: 20px;
    }
    .btn:hover {
      background: #ff2a2a;
      transform: scale(1.05);
    }
  </style>
</head>
<body>

<div class="card">
  <?php if ($messageType == 'success'): ?>
      <h2 style="color: #52E252;">Sikeres Aktiválás! <span style="font-size:30px;">✔</span></h2>
      <div class="message success"><?php echo $message; ?></div>
      <a href="/login/login.html" class="btn">Bejelentkezés</a>
  <?php else: ?>
      <h2 style="color: #e10600;">Hiba történt! <span style="font-size:30px;">✖</span></h2>
      <div class="message error"><?php echo $message; ?></div>
      <a href="/index.php" class="btn" style="background:#333;">Vissza a főoldalra</a>
  <?php endif; ?>
</div>

</body>
</html>