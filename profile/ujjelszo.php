<?php
$DB_HOST = "localhost";
$DB_USER = "swmjndga_swmjndga";
$DB_PASS = "Teszt1234!";
$DB_NAME = "swmjndga_f1adat";

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) die("Hiba: " . $conn->connect_error);

$message = "";
$validToken = false;

// Token ellenőrzése URL-ből
if (isset($_GET['token'])) {
    $token = $_GET['token'];
    $currentDate = date("Y-m-d H:i:s");

    // Megnézzük, érvényes-e a token és nem járt-e le
    $stmt = $conn->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_expires > ?");
    $stmt->bind_param("ss", $token, $currentDate);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $validToken = true;
    } else {
        $message = "Ez a link érvénytelen vagy lejárt!";
    }
}

// Jelszó mentése
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['password'])) {
    $token = $_POST['token'];
    $pass1 = $_POST['password'];
    $pass2 = $_POST['password_confirm'];

    if ($pass1 === $pass2) {
        // Jelszó titkosítása
        $newHash = password_hash($pass1, PASSWORD_DEFAULT);

        // Frissítés és token törlése (hogy ne lehessen újra felhasználni)
        $update = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE reset_token = ?");
        $update->bind_param("ss", $newHash, $token);
        
        if ($update->execute()) {
            $message = "<span style='color:#52E252'>Jelszó sikeresen módosítva!</span> <br><br> <a href='/login/login.html' style='color:white; text-decoration:underline;'>Kattints ide a bejelentkezéshez</a>";
            $validToken = false; // Elrejtjük az űrlapot
        } else {
            $message = "Hiba történt a mentéskor.";
        }
    } else {
        $message = "A két jelszó nem egyezik!";
        $validToken = true; // Hagyjuk, hogy újra próbálja
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
  <meta charset="UTF-8">
  <title>Új jelszó megadása</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Poppins', sans-serif; background: #111; color: #eee; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
    .box { background: #151515; padding: 40px; border-radius: 12px; border-top: 3px solid #e10600; width: 320px; text-align: center; }
    input { width: 100%; padding: 10px; margin: 10px 0; background: #2b2b2b; border: none; color: white; border-radius: 5px; }
    input[type="submit"] { background: #e10600; font-weight: bold; cursor: pointer; }
    input[type="submit"]:hover { background: #ff2a2a; }
    .error { color: #e10600; margin-bottom: 15px; display: block; }
  </style>
</head>
<body>

<div class="box">
    <h2 style="color: white;">Új jelszó megadása</h2>
    
    <?php if ($message) echo "<div class='error'>$message</div>"; ?>

    <?php if ($validToken): ?>
        <form method="post" action="">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
            
            <input type="password" name="password" placeholder="Új jelszó" required>
            <input type="password" name="password_confirm" placeholder="Jelszó megerősítése" required>
            <input type="submit" value="Jelszó mentése">
        </form>
    <?php endif; ?>
    
    <?php if (!$validToken && strpos($message, 'Jelszó sikeresen') === false): ?>
        <a href="/login/login.html" style="color:#ccc; text-decoration:none;">Vissza a belépéshez</a>
    <?php endif; ?>
</div>

</body>
</html>