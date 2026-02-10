<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

/* ==== ADATBÁZIS KONFIGURÁCIÓ ==== */
$DB_HOST = "localhost";
$DB_USER = "swmjndga_swmjndga";
$DB_PASS = "Teszt1234!";
$DB_NAME = "swmjndga_f1adat";

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) {
    die("Kapcsolódási hiba: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Adatok tisztítása
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $fav_team = !empty($_POST['fav_team']) ? $_POST['fav_team'] : null;

    // Jelszó hash
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    
    // Generálunk egy 64 karakteres tokent
    $token = bin2hex(random_bytes(32));

    // Ellenőrzés: Foglalt-e a név/email?
    $sql_check = "SELECT id FROM users WHERE username=? OR email=?";
    $stmt = $conn->prepare($sql_check);
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<h2 style='color:red; text-align:center;'>Már létezik ilyen felhasználónév vagy e-mail!</h2><div style='text-align:center'><a href='register.html'>Vissza</a></div>";
    } else {
        // Beszúrás
        // FONTOS: Az adatbázisban a verification_token legyen VARCHAR(100) !
        $sql = "INSERT INTO users (username, email, password, fav_team, verification_token, is_verified) VALUES (?, ?, ?, ?, ?, 0)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $username, $email, $passwordHash, $fav_team, $token);

        if ($stmt->execute()) {
            // Email küldése
            $to = $email;
            $subject = "F1 Fan Club - Regisztráció megerősítése";
            
            // Link összeállítása (ügyelj arra, hogy a domain helyes legyen!)
            // urlencode-ot használunk, hogy biztosan valid legyen a link
            $link = "http://f1fanclub.hu/f1fanclub/register/verify.php?token=" . urlencode($token);
            
            $message = "Szia $username!\n\nKöszi, hogy regisztráltál!\nKérlek kattints az alábbi linkre a fiókod megerősítéséhez:\n\n$link\n\nÜdvözlettel,\nF1 Fan Club Csapat";
            
            // Fejlécek a jobb kézbesítésért
            $headers = "From: noreply@swmjndga.hu\r\n";
            $headers .= "Reply-To: noreply@swmjndga.hu\r\n";
            $headers .= "X-Mailer: PHP/" . phpversion();

            if(mail($to, $subject, $message, $headers)) {
                echo "<div style='text-align:center; padding:50px; font-family:sans-serif; color:white; background:#151515;'>
                        <h2 style='color:#52E252;'>Sikeres regisztráció! 🏁</h2>
                        <p>Küldtünk egy emailt a(z) <strong>$email</strong> címre.</p>
                        <p>Kattints a benne lévő linkre a profilod aktiválásához!</p>
                        <small>(Nézd meg a Spam mappát is!)</small><br><br>
                        <a href='/f1fanclub/login/login.html' style='color:#e10600; text-decoration:none;'>Tovább a bejelentkezéshez &rarr;</a>
                      </div>";
            } else {
                echo "Hiba az email küldésekor.";
            }

        } else {
            echo "Adatbázis hiba: " . $stmt->error;
        }
    }
    $stmt->close();
}
$conn->close();
?>