<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ... itt jön a többi kód ...
// PHPMailer betöltése
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

$DB_HOST = "localhost";
$DB_USER = "swmjndga_swmjndga";
$DB_PASS = "Teszt1234!";
$DB_NAME = "swmjndga_f1adat";

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) die("Hiba: " . $conn->connect_error);

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    // 1. Ellenőrizzük, létezik-e az email
    $stmt = $conn->prepare("SELECT id, username FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // 2. Generálunk egy tokent és lejárati időt (most + 1 óra)
        $token = bin2hex(random_bytes(32));
        $expires = date("Y-m-d H:i:s", time() + 3600); 

        // 3. Mentés az adatbázisba
        $update = $conn->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE email = ?");
        $update->bind_param("sss", $token, $expires, $email);
        
        if ($update->execute()) {
            // 4. Email küldés
            $mail = new PHPMailer(true);
            try {
// ... a try { után ...

    $mail->isSMTP();
    // 1. VÁLTOZÁS: Nem localhost, hanem a domain előtaggal
    $mail->Host       = 'mail.f1fanclub.hu'; 
    $mail->SMTPAuth   = true;
    $mail->Username   = 'noreply@f1fanclub.hu';
    $mail->Password   = 'oe1.;Mgm71YW9W'; // Ellenőrizd, hogy biztos jó-e!

    // 2. VÁLTOZÁS: Vissza a 465-ös portra és SMTPS-re
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = 465;

    // 3. VÁLTOZÁS: A biztonsági ellenőrzés kikapcsolása (EZ A KULCS!)
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );

    $mail->setFrom('noreply@f1fanclub.hu', 'F1 Fan Club');
    $mail->addAddress($email);

// ... innen mehet tovább a többi ...

                $mail->isHTML(true);
                $mail->Subject = 'Uj jelszo igenyles';
                
                $link = "http://f1fanclub.hu/ujjelszo.php?token=" . $token;
                
                $mail->Body    = "
                    <h3>Szia {$user['username']}!</h3>
                    <p>Jelszó-visszaállítási kérelem érkezett a fiókodhoz.</p>
                    <p>Kattints az alábbi linkre új jelszó megadásához (a link 1 óráig érvényes):</p>
                    <p><a href='$link'>$link</a></p>
                ";

                $mail->send();
                $message = "<span style='color:#52E252'>Az emailt elküldtük! Ellenőrizd a fiókodat.</span>";
            } catch (Exception $e) {
                $message = "Hiba az email küldésekor: {$mail->ErrorInfo}";
            }
        }
    } else {
        // Biztonsági okból nem írjuk ki, ha nincs ilyen email, vagy kiírhatjuk, te döntöd el.
        $message = "Ha létezik ez az email cím, küldtünk rá levelet.";
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
  <meta charset="UTF-8">
  <title>Elfelejtett jelszó</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Poppins', sans-serif; background: #111; color: #eee; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
    .box { background: #151515; padding: 40px; border-radius: 12px; border-top: 3px solid #e10600; width: 320px; text-align: center; }
    input { width: 100%; padding: 10px; margin: 10px 0; background: #2b2b2b; border: none; color: white; border-radius: 5px; }
    input[type="submit"] { background: #e10600; font-weight: bold; cursor: pointer; }
    input[type="submit"]:hover { background: #ff2a2a; }
    .back { display: block; margin-top: 15px; color: #ccc; text-decoration: none; font-size: 14px; }
  </style>
</head>
<body>
  <form class="box" method="post" action="">
    <h2 style="color:#e10600">Jelszó emlékeztető</h2>
    <?php if($message) echo "<p>$message</p>"; ?>
    <p style="font-size:14px; color:#aaa;">Add meg az email címed, és küldünk egy linket az új jelszó beállításához.</p>
    <input type="email" name="email" placeholder="Email cím" required>
    <input type="submit" value="Küldés">
    <a href="/login/login.html" class="back">Vissza a belépéshez</a>
  </form>
</body>
</html>