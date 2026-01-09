<?php
session_start();
if (!isset($_SESSION['username'])) {
    die("Nem vagy bejelentkezve!");
}

$DB_HOST = "localhost";
$DB_USER = "swmjndga_swmjndga";
$DB_PASS = "Teszt1234!";
$DB_NAME = "swmjndga_f1adat";

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) {
    die("Kapcsolódási hiba: " . $conn->connect_error);
}

$username = $_SESSION['username'];

if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
    $tmpName = $_FILES['profile_image']['tmp_name'];
    $fileName = basename($_FILES['profile_image']['name']);
    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($ext, $allowed)) {
        die("❌ Csak JPG, JPEG, PNG vagy GIF fájl engedélyezett!");
    }

    $imgInfo = getimagesize($tmpName);
    if (!$imgInfo) {
        die("❌ Érvénytelen kép!");
    }

    if ($imgInfo[0] > 250 || $imgInfo[1] > 250) {
        die("❌ A kép túl nagy! Maximum 250x250 pixel lehet.");
    }

    $newName = $username . "_profile." . $ext;
    $uploadPath = "uploads/" . $newName;

    if (file_exists($uploadPath)) unlink($uploadPath);
    move_uploaded_file($tmpName, $uploadPath);

    $stmt = $conn->prepare("UPDATE users SET profile_image=? WHERE username=?");
    $stmt->bind_param("ss", $newName, $username);
    $stmt->execute();

    header("Location: /index.php");
    exit;
} else {
    die("❌ Nem választottál ki képet.");
}
?>
