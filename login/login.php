<?php
session_start();
$DB_HOST = "localhost";
$DB_USER = "swmjndga_swmjndga";
$DB_PASS = "Teszt1234!";
$DB_NAME = "swmjndga_f1adat";

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) die("Hiba: " . $conn->connect_error);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // ÚJ: Lekérjük a 'role' oszlopot is!
    $stmt = $conn->prepare("SELECT id, username, password, is_verified, role FROM users WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['password'])) {
            // 1. Email ellenőrzés
            if ($user['is_verified'] == 0) {
                echo "<h2>A fiókod még nincs megerősítve! Kérlek ellenőrizd az emailjeidet.</h2><a href='login.html'>Vissza</a>";
                exit;
            }

            // 2. Beléptetés
            session_regenerate_id(true); 
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role']; // ÚJ: Elmentjük a szerepkört a munkamenetbe
            
            // 3. Kickout (Párhuzamos bejelentkezés védelme)
            $current_session_id = session_id();
            $update_session = $conn->prepare("UPDATE users SET session_token = ? WHERE id = ?");
            $update_session->bind_param("si", $current_session_id, $user['id']);
            $update_session->execute();

            // 4. "Remember Me" kezelése
            if (isset($_POST['remember_me'])) {
                $token = bin2hex(random_bytes(32));
                setcookie('remember_me', $token, time() + (86400 * 30), "/");
                
                $update_token = $conn->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
                $update_token->bind_param("si", $token, $user['id']);
                $update_token->execute();
            }

            // 5. ÚJ: Irányítás szerepkör alapján
            if ($user['role'] === 'admin') {
                header("Location: /f1fanclub/admin/admin.php"); // Ha admin, ide megy
            } else {
                header("Location: /index.php"); // Ha user, ide megy
            }
            exit;

        } else {
            echo "<h2>Hibás jelszó!</h2><a href='login.html'>Vissza</a>";
        }
    } else {
        echo "<h2>Nincs ilyen felhasználó!</h2><a href='login.html'>Vissza</a>";
    }
    $stmt->close();
}
$conn->close();
?>