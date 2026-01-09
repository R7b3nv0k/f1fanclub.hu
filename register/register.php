<?php
session_start();
// Hibaüzenetek megjelenítése fejlesztés alatt
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ADATBÁZIS KAPCSOLAT
$DB_HOST = "localhost";
$DB_USER = "swmjndga_swmjndga";
$DB_PASS = "Teszt1234!";
$DB_NAME = "swmjndga_f1adat";

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) die("Kapcsolódási hiba: " . $conn->connect_error);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $fav_team = !empty($_POST['fav_team']) ? $_POST['fav_team'] : null;
    
    // Generálunk egy véletlenszerű tokent az emailhez
    $token = bin2hex(random_bytes(32));

    // Ellenőrzés
    $sql_check = "SELECT id FROM users WHERE username=? OR email=?";
    $stmt = $conn->prepare($sql_check);
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<h2>Már létezik ilyen felhasználónév vagy e-mail!</h2><a href='register.html'>Vissza</a>";
    } else {
        // Alapból is_verified = 0
        $sql = "INSERT INTO users (username, email, password, fav_team, verification_token, is_verified) VALUES (?, ?, ?, ?, ?, 0)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $username, $email, $password, $fav_team, $token);

        if ($stmt->execute()) {
            // Email küldése
            $to = $email;
            $subject = "F1 Fan Club - Regisztráció megerősítése";
            $link = "http://f1fanclub.hu/register/verify.php?token=" . $token;
            $message = "Szia $username!\n\nKérlek kattints az alábbi linkre a fiókod megerősítéséhez:\n$link";
            $headers = "From: noreply@swmjndga.hu";

            mail($to, $subject, $message, $headers);

            echo "<h2>Sikeres regisztráció! Kérlek ellenőrizd az email fiókodat (a spamet is) a megerősítéshez!</h2><a href='login.html'>Bejelentkezés</a>";
        } else {
            echo "Hiba történt: " . $stmt->error;
        }
    }
    $stmt->close();
}
$conn->close();
?>