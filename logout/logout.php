<?php
session_start();
$DB_HOST = "localhost";
$DB_USER = "swmjndga_swmjndga";
$DB_PASS = "Teszt1234!";
$DB_NAME = "swmjndga_f1adat";

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if (isset($_SESSION['username'])) {
    // Töröljük a DB-ből a remember tokent és a session tokent
    $stmt = $conn->prepare("UPDATE users SET remember_token = NULL, session_token = NULL WHERE username = ?");
    $stmt->bind_param("s", $_SESSION['username']);
    $stmt->execute();
}

// Session törlése
session_unset();
session_destroy();

// Süti törlése a böngészőből
setcookie('remember_me', '', time() - 3600, '/');

header("Location: /index.php");
exit;
?>