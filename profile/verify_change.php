<?php
session_start();
$DB_HOST = "localhost";
$DB_USER = "swmjndga_swmjndga";
$DB_PASS = "Teszt1234!";
$DB_NAME = "swmjndga_f1adat";

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) die("Hiba: " . $conn->connect_error);

$msg = "Érvénytelen vagy lejárt link!";
$success = false;

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    $stmt = $conn->prepare("SELECT * FROM user_changes WHERE token = ? AND created_at >= NOW() - INTERVAL 1 HOUR");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if ($res->num_rows > 0) {
        $change = $res->fetch_assoc();
        $username = $change['username'];
        $type = $change['change_type'];
        $newValue = $change['new_value'];
        
        if ($type === 'password') {
            $upd = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
            $upd->bind_param("ss", $newValue, $username);
            $upd->execute();
            $msg = "A jelszavad sikeresen megváltozott!";
            $success = true;
        } 
        elseif ($type === 'email') {
            $upd = $conn->prepare("UPDATE users SET email = ? WHERE username = ?");
            $upd->bind_param("ss", $newValue, $username);
            $upd->execute();
            $msg = "Az e-mail címed sikeresen frissítve lett!";
            $success = true;
        }
        elseif ($type === 'username') {
            // HA NEVET VÁLT, MINDEN TÁBLÁBAN ÁT KELL ÍRNI!
            $conn->begin_transaction();
            try {
                $conn->query("UPDATE users SET username='$newValue' WHERE username='$username'");
                $conn->query("UPDATE activity_logs SET username='$newValue' WHERE username='$username'");
                $conn->query("UPDATE news_comments SET username='$newValue' WHERE username='$username'");
                $conn->query("UPDATE news_emoji_reactions SET username='$newValue' WHERE username='$username'");
                $conn->query("UPDATE pitwall_predictions SET username='$newValue' WHERE username='$username'");
                $conn->query("UPDATE private_messages SET sender='$newValue' WHERE sender='$username'");
                $conn->query("UPDATE private_messages SET receiver='$newValue' WHERE receiver='$username'");
                $conn->query("UPDATE race_chat_archives SET username='$newValue' WHERE username='$username'");
                $conn->query("UPDATE race_live_chat SET username='$newValue' WHERE username='$username'");
                $conn->query("UPDATE user_interests SET username='$newValue' WHERE username='$username'");
                $conn->query("UPDATE friendships SET sender='$newValue' WHERE sender='$username'");
                $conn->query("UPDATE friendships SET receiver='$newValue' WHERE receiver='$username'");
                $conn->commit();
                
                if(isset($_SESSION['username']) && $_SESSION['username'] == $username) {
                    $_SESSION['username'] = $newValue; // Frissítjük a belépett nevet
                }
                $msg = "A felhasználóneved sikeresen $newValue -ra változott!";
                $success = true;
            } catch (Exception $e) {
                $conn->rollback();
                $msg = "Kritikus hiba a névváltásnál!";
            }
        }
        
        // Töröljük a tokent, hogy ne lehessen újra felhasználni
        $conn->query("DELETE FROM user_changes WHERE token = '$token'");
    }
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Módosítás - F1 Fan Club</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        body { background: #0a0a0a; color: white; font-family: 'Poppins', sans-serif; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
        .card { background: #111; padding: 40px; border-radius: 20px; text-align: center; border: 1px solid <?php echo $success ? '#28a745' : '#e10600'; ?>; }
        h1 { color: <?php echo $success ? '#28a745' : '#e10600'; ?>; }
        a { display: inline-block; margin-top: 20px; padding: 10px 20px; background: #e10600; color: white; text-decoration: none; border-radius: 30px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="card">
        <h1><?php echo $success ? 'Sikeres módosítás!' : 'Hiba!'; ?></h1>
        <p><?php echo $msg; ?></p>
        <a href="/f1fanclub/index.php">Tovább az oldalra</a>
    </div>
</body>
</html>