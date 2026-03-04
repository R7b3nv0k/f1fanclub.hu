<?php
// Hibák megjelenítése
ini_set('display_errors', 1);
error_reporting(E_ALL);

/* ==== ADATBÁZIS KAPCSOLAT ==== */
$DB_HOST = "localhost";
$DB_USER = "swmjndga_swmjndga";
$DB_PASS = "Teszt1234!";
$DB_NAME = "swmjndga_f1adat";

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    die("Adatbázis hiba: " . $conn->connect_error);
}

// Fejlettebb cURL függvény hibakezeléssel
function fetchApiData($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // Ha átirányítás van, kövesse
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // HTTPS ellenőrzés lazítása
    curl_setopt($ch, CURLOPT_USERAGENT, 'F1FanClub-App');
    
    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        die("cURL Hiba a lekérdezésnél ({$url}): " . $error);
    }
    
    return json_decode($response, true);
}

echo "<h3>F1 API Szinkronizáció indítása...</h3>";

/* --- 1. Pontok és Győzelmek lekérése (IDEIGLENESEN 2025-ös szezon!) --- */
// Amint lement az első 2026-os futam, írd át a '2025'-öt 'current'-re!
$apiUrl = "https://api.jolpi.ca/ergast/f1/2025/driverStandings.json";
echo "<p>API URL: " . $apiUrl . "</p>";

$standingsData = fetchApiData($apiUrl);
$driver_stats = [];

// Ellenőrizzük, hogy egyáltalán mit kaptunk vissza (Hibakeresés)
if ($standingsData === null) {
    die("Hiba: Az API nem adott vissza érvényes JSON-t. Lehet, hogy nem elérhető az oldal.");
}

if (isset($standingsData['MRData']['StandingsTable']['StandingsLists'][0]['DriverStandings'])) {
    $standings = $standingsData['MRData']['StandingsTable']['StandingsLists'][0]['DriverStandings'];
    
    foreach ($standings as $d) {
        $code = $d['Driver']['code']; 
        $driver_stats[$code] = [
            'points' => (int)$d['points'],
            'position' => (int)$d['position'],
            'wins' => (int)$d['wins'],
            'podiums' => 0,
            'fastest_laps' => 0,
            'poles' => 0
        ];
    }
    echo "<p>✅ Bajnokság állása sikeresen letöltve!</p>";
} else {
    // Ha ide jutunk, kiíratjuk a nyers API választ, hogy lássuk mi a baj
    echo "<pre>NYERS API VÁLASZ:\n";
    print_r($standingsData);
    echo "</pre>";
    die("Hiba: Nem sikerült lekérni a bajnokság állását (Nincs adat a tömbben).");
}

/* --- 2. Dobogók és Leggyorsabb Körök --- */
$resultsData = fetchApiData("https://api.jolpi.ca/ergast/f1/2025/results.json?limit=1000");

if (isset($resultsData['MRData']['RaceTable']['Races'])) {
    $races = $resultsData['MRData']['RaceTable']['Races'];
    foreach ($races as $race) {
        foreach ($race['Results'] as $result) {
            $code = $result['Driver']['code'];
            if (isset($driver_stats[$code])) {
                if ((int)$result['position'] <= 3) {
                    $driver_stats[$code]['podiums']++;
                }
                if (isset($result['FastestLap']['rank']) && (int)$result['FastestLap']['rank'] === 1) {
                    $driver_stats[$code]['fastest_laps']++;
                }
            }
        }
    }
    echo "<p>✅ Futameredmények és dobogók feldolgozva!</p>";
}

/* --- 3. Pole Pozíciók --- */
$qualiData = fetchApiData("https://api.jolpi.ca/ergast/f1/2025/qualifying.json?limit=1000");

if (isset($qualiData['MRData']['RaceTable']['Races'])) {
    $qualiRaces = $qualiData['MRData']['RaceTable']['Races'];
    foreach ($qualiRaces as $race) {
        if (isset($race['QualifyingResults'][0])) {
            $poleDriverCode = $race['QualifyingResults'][0]['Driver']['code'];
            if (isset($driver_stats[$poleDriverCode])) {
                $driver_stats[$poleDriverCode]['poles']++;
            }
        }
    }
    echo "<p>✅ Időmérők és Pole pozíciók feldolgozva!</p>";
}

/* --- 4. Adatbázis Frissítése --- */
$frissitett_pilotak = 0;
$stmt = $conn->prepare("UPDATE pilotak SET points=?, current_position=?, current_wins=?, current_podiums=?, current_fastest_laps=?, current_poles=? WHERE abbreviation=?");

foreach ($driver_stats as $abbreviation => $stats) {
    $stmt->bind_param(
        "iiiiiss", 
        $stats['points'], 
        $stats['position'], 
        $stats['wins'], 
        $stats['podiums'], 
        $stats['fastest_laps'], 
        $stats['poles'], 
        $abbreviation
    );
    
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        $frissitett_pilotak++;
    }
}

$stmt->close();
$conn->close();

echo "<h2>🏁 Sikeres frissítés! Összesen {$frissitett_pilotak} pilóta statisztikája bekerült az adatbázisba!</h2>";
?>