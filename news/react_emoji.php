<?php
session_start();
header('Content-Type: application/json');

$DB_HOST = "localhost";
$DB_USER = "swmjndga_swmjndga";
$DB_PASS = "Teszt1234!";
$DB_NAME = "swmjndga_f1adat";

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
$conn->set_charset("utf8mb4"); // FONTOS AZ EMOJIK MIATT!

if ($conn->connect_error) {
    die(json_encode(["success" => false, "error" => "DB Error"]));
}

if (!isset($_SESSION['username']) || !isset($_POST['news_id']) || !isset($_POST['emoji'])) {
    echo json_encode(["success" => false, "error" => "Missing data"]);
    exit;
}

$username = $_SESSION['username'];
$news_id = (int)$_POST['news_id'];
$emoji = $_POST['emoji'];

// 1. Megnézzük, hogy reagált-e már ezzel
$check = $conn->prepare("SELECT id FROM news_emoji_reactions WHERE news_id = ? AND username = ? AND emoji = ?");
$check->bind_param("iss", $news_id, $username, $emoji);
$check->execute();
$res = $check->get_result();

if ($res->num_rows > 0) {
    // Ha már reagált, akkor visszavonjuk (Törlés)
    $del = $conn->prepare("DELETE FROM news_emoji_reactions WHERE news_id = ? AND username = ? AND emoji = ?");
    $del->bind_param("iss", $news_id, $username, $emoji);
    $del->execute();
} else {
    // Ha nem, akkor hozzáadjuk (Beszúrás)
    $ins = $conn->prepare("INSERT INTO news_emoji_reactions (news_id, username, emoji) VALUES (?, ?, ?)");
    $ins->bind_param("iss", $news_id, $username, $emoji);
    $ins->execute();
}

// 2. Visszaadjuk az új állapotot (Lekérjük az összes reakciót ehhez a poszthoz)
$sql = "SELECT emoji, COUNT(*) as count, SUM(IF(username = ?, 1, 0)) as user_reacted 
        FROM news_emoji_reactions WHERE news_id = ? GROUP BY emoji";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $username, $news_id);
$stmt->execute();
$result = $stmt->get_result();

$reactions = [];
while ($row = $result->fetch_assoc()) {
    $reactions[] = [
        "emoji" => $row['emoji'],
        "count" => $row['count'],
        "user_reacted" => (bool)$row['user_reacted']
    ];
}

echo json_encode(["success" => true, "reactions" => $reactions]);
$conn->close();
?>