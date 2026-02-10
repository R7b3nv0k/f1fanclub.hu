<?php
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
$messageType = "error";

if (isset($_GET['token'])) {
    // Fontos: szóközök levágása, ha véletlenül rosszul másolta ki a user
    $token = trim($_GET['token']);
    
    // 1. Keresés: Token egyezik ÉS még nincs verifikálva
    $stmt = $conn->prepare("SELECT id, username FROM users WHERE verification_token = ? AND is_verified = 0");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $userId = $user['id'];
        $username = $user['username'];

        // 2. Aktiválás
        $stmt_update = $conn->prepare("UPDATE users SET is_verified = 1, verification_token = NULL WHERE id = ?");
        $stmt_update->bind_param("i", $userId);
        
        if ($stmt_update->execute()) {
            $message = "Szia <strong>$username</strong>!<br>A fiókodat sikeresen aktiváltuk!";
            $messageType = "success";
        } else {
            $message = "Hiba történt az aktiválás közben: " . $conn->error;
        }
        $stmt_update->close();
        
    } else {
        // Hibakeresés: Miért nem találtuk?
        // 1. eset: Rossz a token
        // 2. eset: Már aktiválva van
        
        // Megnézzük, hogy létezik-e ez a token egyáltalán (akár aktiváltnál is, ha nem nulláztuk volna, de mi nullázzuk)
        // Vagy megnézzük, hogy a user már aktív-e. De mivel a tokent töröljük aktiváláskor, 
        // a "már felhasznált link" üzenet a legkorrektebb.
        
        $message = "Ez az aktiváló link érvénytelen, vagy a fiókodat már korábban aktiváltad.";
    }
    $stmt->close();
} else {
    $message = "Hiányzó aktiváló kód!";
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
      background: #111;
      color: #eee;
      height: 100vh;
      margin: 0;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .card {
      background: #1e2126;
      padding: 40px;
      border-radius: 12px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.5);
      width: 400px;
      text-align: center;
      border-top: 4px solid #e10600;
    }
    h2 { margin-top: 0; }
    .btn {
      display: inline-block;
      background: #e10600;
      color: white;
      padding: 12px 24px;
      text-decoration: none;
      border-radius: 30px;
      font-weight: bold;
      margin-top: 20px;
      transition: 0.2s;
    }
    .btn:hover { background: #ff3333; transform: scale(1.05); }
    .success-icon { font-size: 50px; color: #52E252; display: block; margin-bottom: 10px; }
    .error-icon { font-size: 50px; color: #e10600; display: block; margin-bottom: 10px; }
  </style>
</head>
<body>

<div class="card">
  <?php if ($messageType == 'success'): ?>
      <span class="success-icon">✔</span>
      <h2 style="color: #52E252;">Siker!</h2>
      <div class="message"><?php echo $message; ?></div>
      <a href="/f1fanclub/login/login.html" class="btn">Bejelentkezés</a>
  <?php else: ?>
      <span class="error-icon">✖</span>
      <h2 style="color: #e10600;">Hiba</h2>
      <div class="message"><?php echo $message; ?></div>
      <a href="/f1fanclub/index.php" class="btn" style="background:#444;">Főoldal</a>
  <?php endif; ?>
</div>

</body>
</html>