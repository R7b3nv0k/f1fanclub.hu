<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

function try_connect($host) {
  $user = "swmjndga_swmjndga";
  $pass = "Teszt1234!";
  $db   = "swmjndga_f1adat";
  echo "<h3>Próba hosttal: $host</h3>";
  try {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $conn = new mysqli($host, $user, $pass, $db);
    $conn->set_charset("utf8mb4");
    echo "✅ Kapcsolat sikeres $host<br>";
    $res = $conn->query("SELECT 1");
    echo "SQL ok, eredmény: " . ($res ? "OK" : "N/A") . "<br>";
    $conn->close();
  } catch (mysqli_sql_exception $e) {
    echo "❌ Hiba ($host): " . $e->getMessage() . "<br>";
  }
}

try_connect("localhost");
try_connect("127.0.0.1");
