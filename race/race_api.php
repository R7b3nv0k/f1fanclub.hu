<?php
// race_api.php - HELYEZD A /race MAPPÁBA!
header('Content-Type: application/json');
session_start();

$DB_HOST = "localhost";
$DB_USER = "swmjndga_swmjndga";
$DB_PASS = "Teszt1234!";
$DB_NAME = "swmjndga_f1adat";

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) { die(json_encode(["error" => "DB Error"])); }

$action = $_GET['action'] ?? 'read';
$race_id = 25; // Kanadai Nagydíj

function resetRace($conn, $race_id) {
    $now = date('Y-m-d H:i:s');
    $conn->query("UPDATE race_control SET status='stopped', current_lap=0, weather='Sunny', safety_car=0, last_update='$now' WHERE race_id=$race_id");
    $conn->query("DELETE FROM live_telemetry");

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

    // --- IDŐJÁRÁS LOGIKA ---
    // 5% esély, hogy elered az eső, 10%, hogy eláll
    if ($weather == 'Sunny' && rand(0, 100) < 5) { $weather = 'Rain'; }
    elseif ($weather == 'Rain' && rand(0, 100) < 10) { $weather = 'Sunny'; }

    // --- SAFETY CAR LOGIKA ---
    // 25% esély körönként, hogy az SC kimegy
    if ($sc == 1 && rand(0, 100) < 25) { $sc = 0; }

    $conn->query("UPDATE race_control SET current_lap=$new_lap, weather='$weather', safety_car=$sc WHERE race_id=$race_id");

    $drivers = $conn->query("SELECT * FROM live_telemetry WHERE status != 'DNF' ORDER BY position ASC");
    $driverData = [];
    while($d = $drivers->fetch_assoc()) { $driverData[] = $d; }

    foreach ($driverData as $key => $driver) {
        $id = $driver['id'];
        $wear_increase = rand(1, 3);
        $new_wear = $driver['tyre_wear'] + $wear_increase;
        $status = 'Running';
        $new_tyre = $driver['tyre_type'];
        
        // Ha esik, de nem vizes gumin van (vagy fordítva), azonnal ki KELL állnia (100% kopás)
        if ($weather == 'Rain' && !in_array($new_tyre, ['Inter', 'Wet'])) { $new_wear = 100; }
        if ($weather == 'Sunny' && in_array($new_tyre, ['Inter', 'Wet'])) { $new_wear = 100; }

        // DNF LOGIKA (Csak ha nincs bent az SC)
        if ($sc == 0 && rand(0, 1000) < 5) { 
            $conn->query("UPDATE live_telemetry SET status='DNF', gap='Kör: $new_lap', position=99 WHERE id=$id");
            $conn->query("UPDATE race_control SET safety_car=1 WHERE race_id=$race_id"); // DNF miatt bejön az SC!
            continue; 
        }

        // BOXKIÁLLÁS
        if ($new_wear > 75) { 
            $status = 'Pit';
            $new_wear = 0;
            if ($weather == 'Rain') { $tyres = ['Inter', 'Wet']; }
            else { $tyres = ['Soft', 'Medium', 'Hard']; }
            $new_tyre = $tyres[array_rand($tyres)];
        } else {
             // ELŐZÉS (Csak ha nincs SC és nem boxol!)
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
    $control = $conn->query("SELECT * FROM race_control WHERE race_id=$race_id")->fetch_assoc();
    if ($control['status'] != 'running') return;

    $last_update = strtotime($control['last_update']);
    $now = time();
    $seconds_passed = $now - $last_update;
    // Átlagosan 2 mp egy kör (ha SC van picit csalunk a háttérben, de vizuálisan jó lesz)
    $laps_to_sim = floor($seconds_passed / 2); 

    if ($laps_to_sim > 0) {
        for ($i = 0; $i < $laps_to_sim; $i++) {
            $chk = $conn->query("SELECT status, current_lap, total_laps FROM race_control WHERE race_id=$race_id")->fetch_assoc();
            if ($chk['status'] != 'running' || $chk['current_lap'] >= $chk['total_laps']) { break; }
            simulateLap($conn, $race_id);
        }
        $new_time = date('Y-m-d H:i:s', $last_update + ($laps_to_sim * 2));
        $conn->query("UPDATE race_control SET last_update='$new_time' WHERE race_id=$race_id");
    }
}

// --- VEZÉRLÉS ---
if ($action == 'start') {
    resetRace($conn, $race_id);
    $now = date('Y-m-d H:i:s');
    // EZ A SOR HIÁNYZOTT: Átállítjuk a státuszt 'running'-ra!
    $conn->query("UPDATE race_control SET status='running', last_update='$now' WHERE race_id=$race_id");
    echo json_encode(["msg" => "Race Started"]);
}
elseif ($action == 'stop') {
    $conn->query("UPDATE race_control SET status='stopped' WHERE race_id=$race_id");
    echo json_encode(["msg" => "Race Stopped"]);
}
elseif ($action == 'update' || $action == 'read') {
    if ($action == 'update') { catchUpRace($conn, $race_id); }
    
    $race = $conn->query("SELECT * FROM race_control WHERE race_id=$race_id")->fetch_assoc();
    
    $sql = "SELECT lt.*, p.name, p.abbreviation, p.image as driver_image, c.team_name, c.logo 
            FROM live_telemetry lt JOIN pilotak p ON lt.driver_id = p.driver_id JOIN csapatok c ON p.`team id` = c.team_id 
            ORDER BY CASE WHEN lt.status = 'DNF' THEN 1 ELSE 0 END, lt.position ASC";     
    $drivers = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
    
    // --- KAMU PONTTÁBLÁZAT (Csak ha vége) ---
    $mockStandings = [];
    if ($race['status'] == 'finished') {
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