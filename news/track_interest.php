<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

/* ==== ADATBÁZIS KAPCSOLAT ==== */
$DB_HOST = "localhost";
$DB_USER = "swmjndga_swmjndga";
$DB_PASS = "Teszt1234!";
$DB_NAME = "swmjndga_f1adat";

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) {
    die(json_encode(["success" => false]));
}

if(isset($_SESSION['username']) && isset($_POST['category'])) {
    $username = $_SESSION['username'];
    $category = $_POST['category'];
    $action = $_POST['action'] ?? 'view';
    
    // Ha nem választott kategóriát, nem számoljuk
    if($category === 'Általános' || empty($category)) {
        echo json_encode(["success" => true, "msg" => "No tracking needed"]);
        exit;
    }

    // Pontozás: Megtekintés = 1 pont, Like = 5 pont
    $pointsToAdd = ($action === 'like') ? 5 : 1;

    // Ha még nincs ilyen kategóriája, beszúrjuk, ha van, hozzáadjuk a pontot (ON DUPLICATE KEY UPDATE)
    $sql = "INSERT INTO user_interests (username, category, score) 
            VALUES (?, ?, ?) 
            ON DUPLICATE KEY UPDATE score = score + ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssii", $username, $category, $pointsToAdd, $pointsToAdd);
    
    if($stmt->execute()){
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false]);
    }
    $stmt->close();
}
$conn->close();
?>