<?php
// race_api.php - HELYEZD A /race MAPPÁBA!
error_reporting(0);
header('Content-Type: application/json');
session_start();

$DB_HOST = "localhost";
$DB_USER = "swmjndga_swmjndga";
$DB_PASS = "Teszt1234!";
$DB_NAME = "swmjndga_f1adat";

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) { die(json_encode(["error" => "DB Error"])); }

$action = $_GET['action'] ?? 'read';
$race_id = 25; 

function resetRace($conn, $race_id) {
    $conn->query("UPDATE race_control SET status='stopped', current_lap=0, weather='Sunny', safety_car=0, last_update=CURRENT_TIMESTAMP WHERE race_id=$race_id");
    $conn->query("DELETE FROM live_telemetry");
    // Biztonsági törlés az élő chatből, ha új futam indul
    $conn->query("DELETE FROM race_live_chat WHERE race_id=$race_id");

    $sql = "SELECT driver_id FROM pilotak ORDER BY points DESC"; 
    $result = $conn->query($sql);
    
    $pos = 1;
    $tyres = ['Soft', 'Medium', 'Hard'];
    
    while($row = $result->fetch_assoc()) {
        $d_id = $row['driver_id'];
        $start_tyre = $tyres[array_rand($tyres)];
        $conn->query("INSERT INTO live_telemetry (race_control_id, driver_id, position, tyre_type, tyre_wear, status, gap) 
                      VALUES (1, $d_id, $pos, '$start_tyre', 0, 'Running', '')");
        $pos++;
    }
}

function simulateLap($conn, $race_id) {
    $control = $conn->query("SELECT * FROM race_control WHERE race_id=$race_id")->fetch_assoc();
    if ($control['status'] != 'running') return;
    if ($control['current_lap'] >= $control['total_laps']) {
        $conn->query("UPDATE race_control SET status='finished' WHERE race_id=$race_id");
        return;
    }

    $new_lap = $control['current_lap'] + 1;
    $weather = $control['weather'];
    $sc = $control['safety_car'];

    if ($weather == 'Sunny' && rand(0, 100) < 5) { $weather = 'Rain'; }
    elseif ($weather == 'Rain' && rand(0, 100) < 10) { $weather = 'Sunny'; }
    if ($sc == 1 && rand(0, 100) < 25) { $sc = 0; }

    $conn->query("UPDATE race_control SET current_lap=$new_lap, weather='$weather', safety_car=$sc WHERE race_id=$race_id");

    $driversRes = $conn->query("SELECT * FROM live_telemetry WHERE status != 'DNF' ORDER BY position ASC");
    $driverData = [];
    while($d = $driversRes->fetch_assoc()) { $driverData[] = $d; }

    foreach ($driverData as $key => $driver) {
        $id = $driver['id'];
        $wear_increase = rand(1, 3);
        $new_wear = $driver['tyre_wear'] + $wear_increase;
        $status = 'Running';
        $new_tyre = $driver['tyre_type'];
        
        if ($weather == 'Rain' && !in_array($new_tyre, ['Inter', 'Wet'])) { $new_wear = 100; }
        if ($weather == 'Sunny' && in_array($new_tyre, ['Inter', 'Wet'])) { $new_wear = 100; }

        if ($sc == 0 && rand(0, 1000) < 5) { 
            $conn->query("UPDATE live_telemetry SET status='DNF', gap='Kör: $new_lap', position=99 WHERE id=$id");
            $conn->query("UPDATE race_control SET safety_car=1 WHERE race_id=$race_id"); 
            continue; 
        }

        if ($new_wear > 75) { 
            $status = 'Pit';
            $new_wear = 0;
            if ($weather == 'Rain') { $tyres = ['Inter', 'Wet']; }
            else { $tyres = ['Soft', 'Medium', 'Hard']; }
            $new_tyre = $tyres[array_rand($tyres)];
        } else {
             if ($sc == 0 && $key > 0 && rand(0, 100) < 12) {
                 $prevDriver = $driverData[$key - 1];
                 $conn->query("UPDATE live_telemetry SET position=" . $prevDriver['position'] . " WHERE id=" . $id);
                 $conn->query("UPDATE live_telemetry SET position=" . $driver['position'] . " WHERE id=" . $prevDriver['id']);
                 $driverData[$key]['position'] = $prevDriver['position'];
                 $driverData[$key-1]['position'] = $driver['position'];
             }
        }
        $conn->query("UPDATE live_telemetry SET tyre_wear=$new_wear, tyre_type='$new_tyre', status='$status' WHERE id=$id");
    }

    $activeDrivers = $conn->query("SELECT id FROM live_telemetry WHERE status != 'DNF' ORDER BY position ASC");
    $currentPos = 1;
    while($active = $activeDrivers->fetch_assoc()) {
        $conn->query("UPDATE live_telemetry SET position = $currentPos WHERE id = " . $active['id']);
        $currentPos++;
    }
    if ($new_lap >= $control['total_laps']) {
        $conn->query("UPDATE race_control SET status='finished' WHERE race_id=$race_id");
    }
}

function catchUpRace($conn, $race_id) {
    $control = $conn->query("SELECT status, current_lap, total_laps, UNIX_TIMESTAMP(CURRENT_TIMESTAMP) - UNIX_TIMESTAMP(last_update) AS seconds_passed FROM race_control WHERE race_id=$race_id")->fetch_assoc();
    if (!$control || $control['status'] != 'running') return;

    $seconds_passed = (int)$control['seconds_passed'];
    if ($seconds_passed < 10) return;

    $laps_to_sim = floor($seconds_passed / 10); 
    if ($laps_to_sim > 5) { $laps_to_sim = 5; }

    if ($laps_to_sim > 0) {
        for ($i = 0; $i < $laps_to_sim; $i++) {
            $chk = $conn->query("SELECT status, current_lap, total_laps FROM race_control WHERE race_id=$race_id")->fetch_assoc();
            if ($chk['status'] != 'running' || $chk['current_lap'] >= $chk['total_laps']) { break; }
            simulateLap($conn, $race_id);
        }
        $conn->query("UPDATE race_control SET last_update=CURRENT_TIMESTAMP WHERE race_id=$race_id");
    }
}

// --- VEZÉRLÉS ---
if ($action == 'start') {
    resetRace($conn, $race_id);
    $conn->query("UPDATE race_control SET status='running', last_update=CURRENT_TIMESTAMP WHERE race_id=$race_id");
    echo json_encode(["msg" => "Race Started"]);
} 
elseif ($action == 'stop') {
    $conn->query("UPDATE race_control SET status='stopped' WHERE race_id=$race_id");
    echo json_encode(["msg" => "Race Stopped"]);
}
// ÚJ FUNKCIÓ: HARD STOP (Archiválás)
elseif ($action == 'hard_stop') {
    // 1. Átmásoljuk az eddigi chatet az archívumba
    $conn->query("INSERT INTO race_chat_archives (race_id, username, message, sent_at) SELECT race_id, username, message, sent_at FROM race_live_chat WHERE race_id=$race_id");
    
    // 2. Kiürítjük az élő chatet és a telemetriát
    $conn->query("DELETE FROM race_live_chat WHERE race_id=$race_id");
    $conn->query("DELETE FROM live_telemetry");
    
    // 3. Státusz beállítása 'archived'-re
    $conn->query("UPDATE race_control SET status='archived', current_lap=0 WHERE race_id=$race_id");
    
    echo json_encode(["msg" => "A verseny véglegesen leállítva, a chat archiválva!"]);
}
elseif ($action == 'update' || $action == 'read') {
    if ($action == 'update') { catchUpRace($conn, $race_id); }
    $race = $conn->query("SELECT * FROM race_control WHERE race_id=$race_id")->fetch_assoc();
    $sql = "SELECT lt.*, p.name, p.abbreviation, p.image as driver_image, c.team_name, c.logo 
            FROM live_telemetry lt JOIN pilotak p ON lt.driver_id = p.driver_id JOIN csapatok c ON p.`team id` = c.team_id 
            ORDER BY CASE WHEN lt.status = 'DNF' THEN 1 ELSE 0 END, lt.position ASC";     
    $driversRes = $conn->query($sql);
    $drivers = [];
    if ($driversRes) { while($row = $driversRes->fetch_assoc()) { $drivers[] = $row; } }
    
    $mockStandings = [];
    if ($race && $race['status'] == 'finished') {
        $mockStandings = [
            ["pos"=>1, "name"=>"Max Verstappen", "team"=>"Red Bull", "points"=>194],
            ["pos"=>2, "name"=>"Lando Norris", "team"=>"McLaren", "points"=>171],
            ["pos"=>3, "name"=>"Charles Leclerc", "team"=>"Ferrari", "points"=>150],
            ["pos"=>4, "name"=>"Oscar Piastri", "team"=>"McLaren", "points"=>112],
            ["pos"=>5, "name"=>"Carlos Sainz", "team"=>"Williams", "points"=>98],
            ["pos"=>6, "name"=>"George Russell", "team"=>"Mercedes", "points"=>82],
            ["pos"=>7, "name"=>"Lewis Hamilton", "team"=>"Ferrari", "points"=>75],
            ["pos"=>8, "name"=>"Sergio Perez", "team"=>"Cadillac", "points"=>60],
            ["pos"=>9, "name"=>"Fernando Alonso", "team"=>"Aston Martin", "points"=>42],
            ["pos"=>10, "name"=>"Valtteri Bottas", "team"=>"Cadillac", "points"=>28],
        ];
    }
    echo json_encode(["race" => $race, "grid" => $drivers, "standings" => $mockStandings]);
}
$conn->close();
?>