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

/* ==== HÍR TÖRLÉSE ==== */
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Hiányzó hír ID.");
}

$id = (int)$_GET['id'];

// először kép elérési útvonal lekérdezése, hogy tudjuk törölni a fájlt is
$stmt = $conn->prepare("SELECT image FROM news WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($row) {
    if (!empty($row['image']) && file_exists($row['image'])) {
        @unlink($row['image']); // kép törlése (ha létezik)
    }

    // rekord törlése
    $stmt = $conn->prepare("DELETE FROM news WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

$conn->close();

// vissza a hírszerkesztőre
header("Location: /news/hirszerkeszto.php");
exit;
