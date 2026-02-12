<?php
// race_api.php - HELYEZD A /race MAPPÁBA!
header('Content-Type: application/json');
session_start();

// Adatbázis kapcsolat
$DB_HOST = "localhost";
$DB_USER = "swmjndga_swmjndga";
$DB_PASS = "Teszt1234!";
$DB_NAME = "swmjndga_f1adat";

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) {
    die(json_encode(["error" => "DB Error"]));
}

$action = $_GET['action'] ?? 'read';
$race_id = 25; // Kanadai Nagydíj

// --- FÜGGVÉNYEK ---

function resetRace($conn, $race_id)
{
    $now = date('Y-m-d H:i:s');
    // Itt a last_update-et beállítjuk a MOSTANI időre
    $conn->query("UPDATE race_control SET status='stopped', current_lap=0, last_update='$now' WHERE race_id=$race_id");
    $conn->query("DELETE FROM live_telemetry");

    $sql = "SELECT driver_id FROM pilotak ORDER BY points DESC";
    $result = $conn->query($sql);

    $pos = 1;
    $tyres = ['Soft', 'Medium', 'Hard'];

    while ($row = $result->fetch_assoc()) {
        $d_id = $row['driver_id'];
        $start_tyre = $tyres[array_rand($tyres)];
        $conn->query("INSERT INTO live_telemetry (race_control_id, driver_id, position, tyre_type, tyre_wear, status, gap) 
                      VALUES (1, $d_id, $pos, '$start_tyre', 0, 'Running', '')");
        $pos++;
    }
}

function simulateLap($conn, $race_id)
{
    $control = $conn->query("SELECT * FROM race_control WHERE race_id=$race_id")->fetch_assoc();
    if ($control['status'] != 'running')
        return;

    if ($control['current_lap'] >= $control['total_laps']) {
        $conn->query("UPDATE race_control SET status='finished' WHERE race_id=$race_id");
        return;
    }

    $new_lap = $control['current_lap'] + 1;
    $conn->query("UPDATE race_control SET current_lap = $new_lap WHERE race_id=$race_id");

    $drivers = $conn->query("SELECT * FROM live_telemetry WHERE status != 'DNF' ORDER BY position ASC");
    $driverData = [];
    while ($d = $drivers->fetch_assoc()) {
        $driverData[] = $d;
    }

    foreach ($driverData as $key => $driver) {
        $id = $driver['id'];

        $wear_increase = rand(1, 3);
        $new_wear = $driver['tyre_wear'] + $wear_increase;

        $status = 'Running';
        $new_tyre = $driver['tyre_type'];

        // DNF LOGIKA
        if (rand(0, 1000) < 4) {
            $gap_text = "Kör: " . $new_lap;
            $conn->query("UPDATE live_telemetry SET tyre_wear=$new_wear, status='DNF', gap='$gap_text', position=99 WHERE id=$id");
            continue;
        }

        // Boxkiállás
        if ($new_wear > 75) {
            $status = 'Pit';
            $new_wear = 0;
            $tyres = ['Soft', 'Medium', 'Hard'];
            $new_tyre = $tyres[array_rand($tyres)];
        } else {
            // Előzés
            if ($key > 0 && rand(0, 100) < 10) {
                $prevDriver = $driverData[$key - 1];
                $conn->query("UPDATE live_telemetry SET position=" . $prevDriver['position'] . " WHERE id=" . $id);
                $conn->query("UPDATE live_telemetry SET position=" . $driver['position'] . " WHERE id=" . $prevDriver['id']);
                $driverData[$key]['position'] = $prevDriver['position'];
                $driverData[$key - 1]['position'] = $driver['position'];
            }
        }

        $conn->query("UPDATE live_telemetry SET tyre_wear=$new_wear, tyre_type='$new_tyre', status='$status' WHERE id=$id");
    }

    // Tabella lyukak bezárása
    $activeDrivers = $conn->query("SELECT id FROM live_telemetry WHERE status != 'DNF' ORDER BY position ASC");
    $currentPos = 1;
    while ($active = $activeDrivers->fetch_assoc()) {
        $conn->query("UPDATE live_telemetry SET position = $currentPos WHERE id = " . $active['id']);
        $currentPos++;
    }

    // Ellenőrizzük, hogy ez volt-e az utolsó kör
    if ($new_lap >= $control['total_laps']) {
        $conn->query("UPDATE race_control SET status='finished' WHERE race_id=$race_id");
    }
}

// --- AZ ÚJ IDŐALAPÚ FELZÁRKÓZÁS (CATCH-UP) ---
function catchUpRace($conn, $race_id)
{
    $control = $conn->query("SELECT * FROM race_control WHERE race_id=$race_id")->fetch_assoc();

    // Csak akkor kell pótolni, ha megy a futam
    if ($control['status'] != 'running')
        return;

    $last_update = strtotime($control['last_update']);
    $now = time();
    $seconds_passed = $now - $last_update;

    // Tegyük fel, hogy 1 kör szimulálása 2 másodperc valós időt vesz igénybe
    $laps_to_sim = floor($seconds_passed / 2);

    if ($laps_to_sim > 0) {
        for ($i = 0; $i < $laps_to_sim; $i++) {
            // Minden iterációnál megnézzük, nem ért-e már véget a futam
            $chk = $conn->query("SELECT status, current_lap, total_laps FROM race_control WHERE race_id=$race_id")->fetch_assoc();
            if ($chk['status'] != 'running' || $chk['current_lap'] >= $chk['total_laps']) {
                break;
            }
            simulateLap($conn, $race_id);
        }

        // Frissítjük az utolsó update idejét, hozzáadva a leszimulált másodperceket.
        $new_time = date('Y-m-d H:i:s', $last_update + ($laps_to_sim * 2));
        $conn->query("UPDATE race_control SET last_update='$new_time' WHERE race_id=$race_id");
    }
}

// --- VEZÉRLÉS ---
if ($action == 'start') {
    resetRace($conn, $race_id);
    $now = date('Y-m-d H:i:s');
    $conn->query("UPDATE race_control SET status='running', last_update='$now' WHERE race_id=$race_id");
    echo json_encode(["msg" => "Race Started"]);
} elseif ($action == 'stop') {
    $conn->query("UPDATE race_control SET status='stopped' WHERE race_id=$race_id");
    echo json_encode(["msg" => "Race Stopped"]);
} elseif ($action == 'update' || $action == 'read') {

    // MIELŐTT VISSZAADJUK AZ ADATOKAT, BEHOZZUK A LEMARADÁST!
    catchUpRace($conn, $race_id);

    $race = $conn->query("SELECT * FROM race_control WHERE race_id=$race_id")->fetch_assoc();

    $sql = "SELECT lt.*, p.name, p.abbreviation, c.team_name, c.logo 
            FROM live_telemetry lt 
            JOIN pilotak p ON lt.driver_id = p.driver_id 
            JOIN csapatok c ON p.`team id` = c.team_id 
            ORDER BY 
                CASE WHEN lt.status = 'DNF' THEN 1 ELSE 0 END, 
                lt.position ASC";
    $drivers = $conn->query($sql);

    $grid = [];
    while ($row = $drivers->fetch_assoc()) {
        $grid[] = $row;
    }

    echo json_encode(["race" => $race, "grid" => $grid]);
}

$conn->close();
?>