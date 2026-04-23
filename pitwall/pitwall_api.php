<?php
session_start();
header('Content-Type: application/json');
error_reporting(0);

$DB_HOST = "localhost";
$DB_USER = "swmjndga_swmjndga";
$DB_PASS = "Teszt1234!";
$DB_NAME = "swmjndga_f1adat";

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) { die(json_encode(['success' => false, 'error' => 'DB hiba'])); }

$username = $_SESSION['username'] ?? null;
if (!$username) { die(json_encode(['success' => false, 'error' => 'Nincs bejelentkezve'])); }

$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? '';

if ($action === 'save_prediction') {
    $race_id = intval($data['race_id'] ?? 0);
    $predictions = $data['predictions'] ?? []; // Ez a pilóta ID-k tömbje lesz a helyes sorrendben

    if ($race_id === 0 || empty($predictions)) {
        echo json_encode(['success' => false, 'error' => 'Hiányzó adatok']);
        exit;
    }

    $pred_json = json_encode($predictions);

    // INSERT ... ON DUPLICATE KEY UPDATE: Ha még nem tippelt, létrehozza. Ha már tippelt, felülírja!
    $sql = "INSERT INTO pitwall_predictions (username, race_id, predictions) 
            VALUES (?, ?, ?) 
            ON DUPLICATE KEY UPDATE predictions = VALUES(predictions)";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sis", $username, $race_id, $pred_json);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
    exit;
}
?>