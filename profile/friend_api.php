<?php
// /f1fanclub/profile/friend_api.php
session_start();
header('Content-Type: application/json');

$DB_HOST = "localhost";
$DB_USER = "swmjndga_swmjndga";
$DB_PASS = "Teszt1234!";
$DB_NAME = "swmjndga_f1adat";

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) { die(json_encode(['success' => false])); }

$current_user = $_SESSION['username'] ?? null;
$data = json_decode(file_get_contents('php://input'), true);

$action = $data['action'] ?? '';
$target_user = trim($data['target_user'] ?? '');

if (!$current_user || !$target_user || $current_user === $target_user) {
    die(json_encode(['success' => false, 'error' => 'Invalid request']));
}

if ($action === 'add') {
    // Ellenőrzés, hogy nincs-e már jelölve
    $check = $conn->query("SELECT id FROM friendships WHERE (sender='$current_user' AND receiver='$target_user') OR (sender='$target_user' AND receiver='$current_user')");
    if ($check->num_rows == 0) {
        $stmt = $conn->prepare("INSERT INTO friendships (sender, receiver, status) VALUES (?, ?, 'pending')");
        $stmt->bind_param("ss", $current_user, $target_user);
        $stmt->execute();
    }
} elseif ($action === 'accept') {
    $stmt = $conn->prepare("UPDATE friendships SET status='accepted' WHERE sender=? AND receiver=?");
    $stmt->bind_param("ss", $target_user, $current_user);
    $stmt->execute();
} elseif ($action === 'remove') {
    // Törli a barátságot vagy visszavonja a jelölést
    $stmt = $conn->prepare("DELETE FROM friendships WHERE (sender=? AND receiver=?) OR (sender=? AND receiver=?)");
    $stmt->bind_param("ssss", $current_user, $target_user, $target_user, $current_user);
    $stmt->execute();
}

echo json_encode(['success' => true]);
$conn->close();
?>