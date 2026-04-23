<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

<<<<<<< HEAD
=======
/**
 * ============================================================================
 * DATABASE CONFIGURATION
 * ============================================================================
 * Establishes the connection to the MySQL database.
 */
>>>>>>> f81424192996985be2da559c0f9a2c1f13f5eb7f
$DB_HOST = "localhost";
$DB_USER = "swmjndga_swmjndga";
$DB_PASS = "Teszt1234!";
$DB_NAME = "swmjndga_f1adat";

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) {
    die("Kapcsolódási hiba: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
<<<<<<< HEAD
=======
    /** Sanitize input data */
>>>>>>> f81424192996985be2da559c0f9a2c1f13f5eb7f
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

<<<<<<< HEAD
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d\w\W]{8,}$/', $password)) {
        ?>
        <!DOCTYPE html>
        <html lang="hu">
        <head>
          <link rel="icon" type="image/svg+xml" href="https://upload.wikimedia.org/wikipedia/commons/3/33/F1.svg">
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes, viewport-fit=cover">
            <title>Gyenge Jelszó – F1 Fan Club</title>
            <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body { background: #0a0a0a; color: white; font-family: 'Poppins', sans-serif; min-height: 100vh; display: flex; align-items: center; justify-content: center; position: relative; overflow-x: hidden; margin: 0; padding: 20px; }
                body::before { content: ""; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: radial-gradient(circle at 20% 50%, rgba(225,6,0,0.05) 0%, transparent 50%), radial-gradient(circle at 80% 80%, rgba(225,6,0,0.05) 0%, transparent 50%); pointer-events: none; z-index: -1; }
                .bg-lines { position: fixed; width: 200%; height: 200%; background: repeating-linear-gradient(60deg, rgba(225,6,0,0.03) 0px, rgba(225,6,0,0.03) 2px, transparent 2px, transparent 10px); animation: slide 10s linear infinite; opacity: 0.3; z-index: -1; top: 0; left: 0; }
                @keyframes slide { from { transform: translateX(0); } to { transform: translateX(-200px); } }
                .card { background: linear-gradient(145deg, #111111 0%, #1a1a1a 100%); padding: 50px 40px; border-radius: 30px; width: 100%; max-width: 450px; margin: 0 auto; border: 1px solid rgba(225,6,0,0.3); box-shadow: 0 30px 60px rgba(0,0,0,0.8), 0 0 50px rgba(225,6,0,0.2); position: relative; overflow: hidden; z-index: 1; text-align: center; }
                .card::before { content: ""; position: absolute; top: 0; left: 0; right: 0; height: 4px; background: linear-gradient(90deg, transparent 0%, #e10600 20%, #e10600 80%, transparent 100%); z-index: 2; }
                .logo-title { font-size: 32px; color: white; font-weight: 800; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 30px; display: flex; justify-content: center; align-items: center; gap: 12px; flex-wrap: wrap; word-break: break-word; }
                .logo-title img { width: 50px; }
                .error-icon { font-size: 60px; color: #e10600; margin-bottom: 20px; filter: drop-shadow(0 0 15px rgba(225,6,0,0.5)); }
                h2 { margin: 0 0 15px; font-weight: 800; font-size: 2rem; text-transform: uppercase; color: #e10600; word-break: break-word; }
                .message { margin: 20px 0; font-size: 1rem; line-height: 1.6; color: #ddd; word-break: break-word; }
                .btn { display: inline-block; background: linear-gradient(145deg, #e10600, #ff4d4d); color: white; padding: 14px 32px; text-decoration: none; border-radius: 50px; font-weight: 700; margin-top: 20px; transition: all 0.3s ease; border: 2px solid #e10600; box-shadow: 0 5px 15px rgba(225,6,0,0.3); }
                .btn:hover { transform: translateY(-5px); box-shadow: 0 20px 40px rgba(225,6,0,0.4); }
                
                @media (max-width: 600px) {
                    body { padding: 12px; align-items: flex-start; padding-top: 30px; }
                    .card { padding: 40px 25px; max-width: 100%; }
                    .logo-title { font-size: 32px; }
                    .logo-title img { width: 55px; }
                    .error-icon { font-size: 70px; margin-bottom: 25px; }
                    h2 { font-size: 2rem; }
                    .message { font-size: 1.1rem; }
                    .btn { padding: 18px 40px; font-size: 1.1rem; width: 100%; }
                }
                @media (max-width: 400px) {
                    .card { padding: 30px 18px; }
                    .logo-title { font-size: 28px; }
                    .logo-title img { width: 45px; }
                    h2 { font-size: 1.8rem; }
                    .message { font-size: 1rem; }
                    .btn { padding: 16px 30px; font-size: 1rem; }
                }
                /* Webkit browsers (Chrome, Safari, Edge) */
::-webkit-scrollbar {
    width: 6px;
    height: 6px; /* For horizontal scrollbars too */
}

::-webkit-scrollbar-track {
    background: #1a1a1a;
    border-radius: 3px;
}

::-webkit-scrollbar-thumb {
    background: #e10600;
    border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
    background: #ff2b2b; /* Lighter red on hover */
}

/* Firefox */
* {
    scrollbar-width: thin;
    scrollbar-color: #e10600 #1a1a1a;
}
            </style>
        </head>
        <body>
        <div class="bg-lines"></div>
        <div class="card">
            <div class="logo-title"><img src="https://upload.wikimedia.org/wikipedia/commons/3/33/F1.svg" alt="F1 Logo"><span>Fan Club</span></div>
            <div class="error-icon"><i class="fas fa-shield-alt"></i></div>
            <h2>Gyenge jelszó!</h2>
            <div class="message">A jelszónak legalább <strong>8 karakter hosszúnak</strong> kell lennie, és tartalmaznia kell legalább <strong>egy kisbetűt, egy nagybetűt és egy számot!</strong></div>
            <a href="register.html" class="btn"><i class="fas fa-arrow-left"></i> Vissza a regisztrációhoz</a>
        </div>
        </body>
        </html>
        <?php
        exit;
    }

    $fav_team = !empty($_POST['fav_team']) ? $_POST['fav_team'] : null;
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $token = bin2hex(random_bytes(32));

=======
    /** Create password hash */
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    
    /** Generate a 64-character secure verification token */
    $token = bin2hex(random_bytes(32));

    /** Check if username or email is already registered */
>>>>>>> f81424192996985be2da559c0f9a2c1f13f5eb7f
    $sql_check = "SELECT id FROM users WHERE username=? OR email=?";
    $stmt = $conn->prepare($sql_check);
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
<<<<<<< HEAD
=======
        // Error page - user already exists
>>>>>>> f81424192996985be2da559c0f9a2c1f13f5eb7f
        ?>
        <!DOCTYPE html>
        <html lang="hu">
        <head>
<<<<<<< HEAD
              <link rel="icon" type="image/svg+xml" href="https://upload.wikimedia.org/wikipedia/commons/3/33/F1.svg">

            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes, viewport-fit=cover">
            <title>Regisztrációs Hiba – F1 Fan Club</title>
            <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body { background: #0a0a0a; color: white; font-family: 'Poppins', sans-serif; min-height: 100vh; display: flex; align-items: center; justify-content: center; margin: 0; padding: 20px; }
                body::before { content: ""; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: radial-gradient(circle at 20% 50%, rgba(225,6,0,0.05) 0%, transparent 50%), radial-gradient(circle at 80% 80%, rgba(225,6,0,0.05) 0%, transparent 50%); pointer-events: none; z-index: -1; }
                .bg-lines { position: fixed; width: 200%; height: 200%; background: repeating-linear-gradient(60deg, rgba(225,6,0,0.03) 0px, rgba(225,6,0,0.03) 2px, transparent 2px, transparent 10px); animation: slide 10s linear infinite; opacity: 0.3; z-index: -1; top: 0; left: 0; }
                @keyframes slide { from { transform: translateX(0); } to { transform: translateX(-200px); } }
                .card { background: linear-gradient(145deg, #111111 0%, #1a1a1a 100%); padding: 50px 40px; border-radius: 30px; width: 100%; max-width: 450px; margin: 0 auto; border: 1px solid rgba(225,6,0,0.3); text-align: center; }
                .card::before { content: ""; position: absolute; top: 0; left: 0; right: 0; height: 4px; background: linear-gradient(90deg, transparent 0%, #e10600 20%, #e10600 80%, transparent 100%); z-index: 2; }
                .logo-title { font-size: 32px; font-weight: 800; text-transform: uppercase; margin-bottom: 30px; display: flex; justify-content: center; align-items: center; gap: 12px; flex-wrap: wrap; word-break: break-word; }
                .logo-title img { width: 50px; }
                .error-icon { font-size: 60px; color: #e10600; margin-bottom: 20px; filter: drop-shadow(0 0 15px rgba(225,6,0,0.5)); }
                h2 { font-weight: 800; font-size: 2rem; text-transform: uppercase; color: #e10600; margin-bottom: 15px; word-break: break-word; }
                .message { margin: 20px 0; font-size: 1rem; color: #ddd; line-height: 1.6; word-break: break-word; }
                .btn { display: inline-block; background: linear-gradient(145deg, #e10600, #ff4d4d); color: white; padding: 14px 32px; text-decoration: none; border-radius: 50px; font-weight: 700; margin-top: 20px; }
                
                @media (max-width: 600px) {
                    body { padding: 12px; align-items: flex-start; padding-top: 30px; }
                    .card { padding: 40px 25px; max-width: 100%; }
                    .logo-title { font-size: 32px; }
                    .logo-title img { width: 55px; }
                    .error-icon { font-size: 70px; margin-bottom: 25px; }
                    h2 { font-size: 2rem; }
                    .message { font-size: 1.1rem; }
                    .btn { padding: 18px 40px; font-size: 1.1rem; width: 100%; }
                }
                @media (max-width: 400px) {
                    .card { padding: 30px 18px; }
                    .logo-title { font-size: 28px; }
                    .logo-title img { width: 45px; }
                    h2 { font-size: 1.8rem; }
                    .message { font-size: 1rem; }
                    .btn { padding: 16px 30px; font-size: 1rem; }
                }
                /* Webkit browsers (Chrome, Safari, Edge) */
::-webkit-scrollbar {
    width: 6px;
    height: 6px; /* For horizontal scrollbars too */
}

::-webkit-scrollbar-track {
    background: #1a1a1a;
    border-radius: 3px;
}

::-webkit-scrollbar-thumb {
    background: #e10600;
    border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
    background: #ff2b2b; /* Lighter red on hover */
}

/* Firefox */
* {
    scrollbar-width: thin;
    scrollbar-color: #e10600 #1a1a1a;
}
=======
            <meta charset="UTF-8">
            <title>Regisztrációs Hiba – F1 Fan Club</title>
            <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
            <link rel="icon" type="image/svg+xml" href="https://upload.wikimedia.org/wikipedia/commons/3/33/F1.svg">
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body {
                    background: #0a0a0a;
                    color: white;
                    font-family: 'Poppins', sans-serif;
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    position: relative;
                    overflow-x: hidden;
                    margin: 0;
                    padding: 20px;
                }
                body::before {
                    content: "";
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: radial-gradient(circle at 20% 50%, rgba(225, 6, 0, 0.05) 0%, transparent 50%),
                                radial-gradient(circle at 80% 80%, rgba(225, 6, 0, 0.05) 0%, transparent 50%);
                    pointer-events: none;
                    z-index: -1;
                }
                .bg-lines {
                    position: fixed;
                    width: 200%;
                    height: 200%;
                    background: repeating-linear-gradient(60deg,
                        rgba(225, 6, 0, 0.03) 0px,
                        rgba(225, 6, 0, 0.03) 2px,
                        transparent 2px,
                        transparent 10px);
                    animation: slide 10s linear infinite;
                    opacity: 0.3;
                    z-index: -1;
                    top: 0;
                    left: 0;
                }
                @keyframes slide {
                    from { transform: translateX(0); }
                    to { transform: translateX(-200px); }
                }
                .card {
                    background: linear-gradient(145deg, #111111 0%, #1a1a1a 100%);
                    padding: 50px 40px;
                    border-radius: 30px;
                    width: 100%;
                    max-width: 450px;
                    margin: 0 auto;
                    border: 1px solid rgba(225, 6, 0, 0.3);
                    box-shadow: 0 30px 60px rgba(0, 0, 0, 0.8), 0 0 50px rgba(225, 6, 0, 0.2);
                    position: relative;
                    overflow: hidden;
                    z-index: 1;
                    animation: slideInUp 0.8s ease-out;
                    text-align: center;
                }
                .card::before {
                    content: "";
                    position: absolute;
                    top: 0;
                    left: 0;
                    right: 0;
                    height: 4px;
                    background: linear-gradient(90deg, transparent 0%, #e10600 20%, #e10600 80%, transparent 100%);
                    z-index: 2;
                }
                .card::after {
                    content: "";
                    position: absolute;
                    top: -2px;
                    left: -2px;
                    right: -2px;
                    bottom: -2px;
                    background: linear-gradient(45deg, transparent 30%, rgba(225, 6, 0, 0.15) 50%, transparent 70%);
                    border-radius: 32px;
                    z-index: -1;
                    animation: borderGlow 4s infinite;
                }
                @keyframes borderGlow { 0%,100% { opacity: 0.3; } 50% { opacity: 0.8; } }
                @keyframes slideInUp {
                    from { opacity: 0; transform: translateY(50px); }
                    to { opacity: 1; transform: translateY(0); }
                }
                .logo-title {
                    font-size: 32px;
                    color: white;
                    font-weight: 800;
                    text-transform: uppercase;
                    letter-spacing: 2px;
                    margin-bottom: 30px;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    gap: 12px;
                }
                .logo-title img { width: 50px; transition: transform 0.3s ease; }
                .logo-title:hover img { transform: rotate(10deg) scale(1.1); }
                .error-icon { font-size: 60px; color: #e10600; margin-bottom: 20px; filter: drop-shadow(0 0 15px rgba(225,6,0,0.5)); }
                h2 { margin: 0 0 15px; font-weight: 800; font-size: 2rem; text-transform: uppercase; letter-spacing: 3px; color: #e10600; position: relative; display: inline-block; }
                h2::after {
                    content: "";
                    position: absolute;
                    bottom: -10px;
                    left: 0;
                    width: 100%;
                    height: 3px;
                    background: linear-gradient(90deg, transparent 0%, #e10600 20%, #e10600 80%, transparent 100%);
                    border-radius: 2px;
                }
                .message { margin: 20px 0; font-size: 1rem; line-height: 1.6; color: #ddd; }
                .btn {
                    display: inline-block;
                    background: linear-gradient(145deg, #e10600, #ff4d4d);
                    color: white;
                    padding: 14px 32px;
                    text-decoration: none;
                    border-radius: 50px;
                    font-weight: 700;
                    font-size: 1rem;
                    text-transform: uppercase;
                    letter-spacing: 1.5px;
                    margin-top: 20px;
                    transition: all 0.3s ease;
                    border: 2px solid #e10600;
                    box-shadow: 0 5px 15px rgba(225, 6, 0, 0.3);
                    position: relative;
                    overflow: hidden;
                }
                .btn:hover { transform: translateY(-5px) scale(1.02); box-shadow: 0 20px 40px rgba(225, 6, 0, 0.4); }
                .btn::before {
                    content: "";
                    position: absolute;
                    top: -50%;
                    left: -50%;
                    width: 200%;
                    height: 200%;
                    background: linear-gradient(45deg, transparent 30%, rgba(255, 255, 255, 0.2) 50%, transparent 70%);
                    transform: rotate(45deg) translateY(100%);
                    transition: transform 0.8s ease;
                    pointer-events: none;
                }
                .btn:hover::before { transform: rotate(45deg) translateY(-100%); }
                @media (max-width: 500px) {
                    .card { padding: 35px 25px; }
                    .logo-title { font-size: 26px; }
                    .logo-title img { width: 40px; }
                    h2 { font-size: 1.6rem; }
                }
>>>>>>> f81424192996985be2da559c0f9a2c1f13f5eb7f
            </style>
        </head>
        <body>
        <div class="bg-lines"></div>
        <div class="card">
<<<<<<< HEAD
            <div class="logo-title"><img src="https://upload.wikimedia.org/wikipedia/commons/3/33/F1.svg" alt="F1 Logo"><span>Fan Club</span></div>
            <div class="error-icon"><i class="fas fa-exclamation-triangle"></i></div>
            <h2>Hiba!</h2>
            <div class="message">Már létezik ilyen felhasználónév vagy e-mail cím!</div>
            <a href="register.html" class="btn"><i class="fas fa-arrow-left"></i> Vissza</a>
=======
            <div class="logo-title">
                <img src="https://upload.wikimedia.org/wikipedia/commons/3/33/F1.svg" alt="F1 Logo">
                <span>Fan Club</span>
            </div>
            <div class="error-icon"><i class="fas fa-exclamation-triangle"></i></div>
            <h2>Hiba!</h2>
            <div class="message">Már létezik ilyen felhasználónév vagy e-mail cím!</div>
            <a href="register.html" class="btn"><i class="fas fa-arrow-left" style="margin-right: 8px;"></i> Vissza</a>
>>>>>>> f81424192996985be2da559c0f9a2c1f13f5eb7f
        </div>
        </body>
        </html>
        <?php
    } else {
<<<<<<< HEAD
=======
        /**
         * Insert new user into the database.
         * IMPORTANT: Ensure verification_token column is VARCHAR(100).
         */
>>>>>>> f81424192996985be2da559c0f9a2c1f13f5eb7f
        $sql = "INSERT INTO users (username, email, password, fav_team, verification_token, is_verified) VALUES (?, ?, ?, ?, ?, 0)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $username, $email, $passwordHash, $fav_team, $token);

        if ($stmt->execute()) {
<<<<<<< HEAD
            $to = $email;
            $subject = "F1 Fan Club - Regisztráció megerősítése";
=======
            /** Send verification email */
            $to = $email;
            $subject = "F1 Fan Club - Regisztráció megerősítése";
            
            /**
             * Construct verification link.
             * Uses urlencode to ensure the link remains valid.
             */
>>>>>>> f81424192996985be2da559c0f9a2c1f13f5eb7f
            $link = "http://f1fanclub.hu/f1fanclub/register/verify.php?token=" . urlencode($token);
            $emailBody = "Szia $username!\n\nKöszi, hogy regisztráltál!\nKérlek kattints az alábbi linkre a fiókod megerősítéséhez:\n\n$link\n\nÜdvözlettel,\nF1 Fan Club Csapat";
            
<<<<<<< HEAD
=======
            $emailBody = "Szia $username!\n\nKöszi, hogy regisztráltál!\nKérlek kattints az alábbi linkre a fiókod megerősítéséhez:\n\n$link\n\nÜdvözlettel,\nF1 Fan Club Csapat";
            
            /** Set email headers for better deliverability */
>>>>>>> f81424192996985be2da559c0f9a2c1f13f5eb7f
            $headers = "From: noreply@swmjndga.hu\r\n";
            $headers .= "Reply-To: noreply@swmjndga.hu\r\n";
            $headers .= "X-Mailer: PHP/" . phpversion();

            if(mail($to, $subject, $emailBody, $headers)) {
<<<<<<< HEAD
=======
                // Success page - styled
>>>>>>> f81424192996985be2da559c0f9a2c1f13f5eb7f
                ?>
                <!DOCTYPE html>
                <html lang="hu">
                <head>
<<<<<<< HEAD
                      <link rel="icon" type="image/svg+xml" href="https://upload.wikimedia.org/wikipedia/commons/3/33/F1.svg">

                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes, viewport-fit=cover">
                    <title>Regisztráció Sikeres – F1 Fan Club</title>
                    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
                    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
                    <style>
                        * { margin: 0; padding: 0; box-sizing: border-box; }
                        body { background: #0a0a0a; color: white; font-family: 'Poppins', sans-serif; min-height: 100vh; display: flex; align-items: center; justify-content: center; margin: 0; padding: 20px; }
                        body::before { content: ""; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: radial-gradient(circle at 20% 50%, rgba(225,6,0,0.05) 0%, transparent 50%), radial-gradient(circle at 80% 80%, rgba(225,6,0,0.05) 0%, transparent 50%); pointer-events: none; z-index: -1; }
                        .bg-lines { position: fixed; width: 200%; height: 200%; background: repeating-linear-gradient(60deg, rgba(225,6,0,0.03) 0px, rgba(225,6,0,0.03) 2px, transparent 2px, transparent 10px); animation: slide 10s linear infinite; opacity: 0.3; z-index: -1; top: 0; left: 0; }
                        @keyframes slide { from { transform: translateX(0); } to { transform: translateX(-200px); } }
                        .card { background: linear-gradient(145deg, #111111 0%, #1a1a1a 100%); padding: 50px 40px; border-radius: 30px; width: 100%; max-width: 450px; margin: 0 auto; border: 1px solid rgba(225,6,0,0.3); text-align: center; }
                        .card::before { content: ""; position: absolute; top: 0; left: 0; right: 0; height: 4px; background: linear-gradient(90deg, transparent 0%, #28a745 20%, #28a745 80%, transparent 100%); z-index: 2; }
                        .logo-title { font-size: 32px; font-weight: 800; text-transform: uppercase; margin-bottom: 30px; display: flex; justify-content: center; align-items: center; gap: 12px; flex-wrap: wrap; word-break: break-word; }
                        .logo-title img { width: 50px; }
                        .success-icon { font-size: 60px; color: #28a745; margin-bottom: 20px; filter: drop-shadow(0 0 15px rgba(40,167,69,0.5)); }
                        h2 { font-weight: 800; font-size: 2rem; text-transform: uppercase; color: #28a745; margin-bottom: 15px; word-break: break-word; }
                        .message { margin: 20px 0; font-size: 1rem; line-height: 1.6; color: #ddd; word-break: break-word; }
                        .email-highlight { color: #28a745; font-weight: 600; }
                        .btn { display: inline-block; background: linear-gradient(145deg, #28a745, #20c997); color: white; padding: 14px 32px; text-decoration: none; border-radius: 50px; font-weight: 700; margin-top: 20px; }
                        
                        @media (max-width: 600px) {
                            body { padding: 12px; align-items: flex-start; padding-top: 30px; }
                            .card { padding: 40px 25px; max-width: 100%; }
                            .logo-title { font-size: 32px; }
                            .logo-title img { width: 55px; }
                            .success-icon { font-size: 70px; margin-bottom: 25px; }
                            h2 { font-size: 2rem; }
                            .message { font-size: 1.1rem; }
                            .btn { padding: 18px 40px; font-size: 1.1rem; width: 100%; }
                        }
                        @media (max-width: 400px) {
                            .card { padding: 30px 18px; }
                            .logo-title { font-size: 28px; }
                            .logo-title img { width: 45px; }
                            h2 { font-size: 1.8rem; }
                            .message { font-size: 1rem; }
                            .btn { padding: 16px 30px; font-size: 1rem; }
                        }/* Webkit browsers (Chrome, Safari, Edge) */
::-webkit-scrollbar {
    width: 6px;
    height: 6px; /* For horizontal scrollbars too */
}

::-webkit-scrollbar-track {
    background: #1a1a1a;
    border-radius: 3px;
}

::-webkit-scrollbar-thumb {
    background: #e10600;
    border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
    background: #ff2b2b; /* Lighter red on hover */
}

/* Firefox */
* {
    scrollbar-width: thin;
    scrollbar-color: #e10600 #1a1a1a;
}
                    </style>
                </head>
                <body>
                <div class="bg-lines"></div>
                <div class="card">
                    <div class="logo-title"><img src="https://upload.wikimedia.org/wikipedia/commons/3/33/F1.svg" alt="F1 Logo"><span>Fan Club</span></div>
                    <div class="success-icon"><i class="fas fa-check-circle"></i></div>
                    <h2>Sikeres regisztráció! 🏁</h2>
                    <div class="message">Küldtünk egy emailt a(z) <strong class="email-highlight"><?php echo htmlspecialchars($email); ?></strong> címre.<br><br>Kattints a benne lévő linkre a profilod aktiválásához!</div>
                    <a href="/f1fanclub/login/login.html" class="btn"><i class="fas fa-sign-in-alt"></i> Tovább a bejelentkezéshez</a>
                </div>
                </body>
                </html>
                <?php
            } else {
                echo "Hiba az e-mail küldésekor.";
            }
=======
                    <meta charset="UTF-8">
                    <title>Regisztráció Sikeres – F1 Fan Club</title>
                    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
                    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
                    <link rel="icon" type="image/svg+xml" href="https://upload.wikimedia.org/wikipedia/commons/3/33/F1.svg">
                    <style>
                        * { margin: 0; padding: 0; box-sizing: border-box; }
                        body {
                            background: #0a0a0a;
                            color: white;
                            font-family: 'Poppins', sans-serif;
                            min-height: 100vh;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            position: relative;
                            overflow-x: hidden;
                            margin: 0;
                            padding: 20px;
                        }
                        body::before {
                            content: "";
                            position: fixed;
                            top: 0;
                            left: 0;
                            width: 100%;
                            height: 100%;
                            background: radial-gradient(circle at 20% 50%, rgba(225, 6, 0, 0.05) 0%, transparent 50%),
                                        radial-gradient(circle at 80% 80%, rgba(225, 6, 0, 0.05) 0%, transparent 50%);
                            pointer-events: none;
                            z-index: -1;
                        }
                        .bg-lines {
                            position: fixed;
                            width: 200%;
                            height: 200%;
                            background: repeating-linear-gradient(60deg,
                                rgba(225, 6, 0, 0.03) 0px,
                                rgba(225, 6, 0, 0.03) 2px,
                                transparent 2px,
                                transparent 10px);
                            animation: slide 10s linear infinite;
                            opacity: 0.3;
                            z-index: -1;
                            top: 0;
                            left: 0;
                        }
                        @keyframes slide {
                            from { transform: translateX(0); }
                            to { transform: translateX(-200px); }
                        }
                        .card {
                            background: linear-gradient(145deg, #111111 0%, #1a1a1a 100%);
                            padding: 50px 40px;
                            border-radius: 30px;
                            width: 100%;
                            max-width: 450px;
                            margin: 0 auto;
                            border: 1px solid rgba(225, 6, 0, 0.3);
                            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.8), 0 0 50px rgba(225, 6, 0, 0.2);
                            position: relative;
                            overflow: hidden;
                            z-index: 1;
                            animation: slideInUp 0.8s ease-out;
                            text-align: center;
                        }
                        .card::before {
                            content: "";
                            position: absolute;
                            top: 0;
                            left: 0;
                            right: 0;
                            height: 4px;
                            background: linear-gradient(90deg, transparent 0%, #e10600 20%, #e10600 80%, transparent 100%);
                            z-index: 2;
                        }
                        .card::after {
                            content: "";
                            position: absolute;
                            top: -2px;
                            left: -2px;
                            right: -2px;
                            bottom: -2px;
                            background: linear-gradient(45deg, transparent 30%, rgba(225, 6, 0, 0.15) 50%, transparent 70%);
                            border-radius: 32px;
                            z-index: -1;
                            animation: borderGlow 4s infinite;
                        }
                        @keyframes borderGlow { 0%,100% { opacity: 0.3; } 50% { opacity: 0.8; } }
                        @keyframes slideInUp {
                            from { opacity: 0; transform: translateY(50px); }
                            to { opacity: 1; transform: translateY(0); }
                        }
                        .logo-title {
                            font-size: 32px;
                            color: white;
                            font-weight: 800;
                            text-transform: uppercase;
                            letter-spacing: 2px;
                            margin-bottom: 30px;
                            display: flex;
                            justify-content: center;
                            align-items: center;
                            gap: 12px;
                        }
                        .logo-title img { width: 50px; transition: transform 0.3s ease; }
                        .logo-title:hover img { transform: rotate(10deg) scale(1.1); }
                        .success-icon { font-size: 60px; color: #28a745; margin-bottom: 20px; filter: drop-shadow(0 0 15px rgba(40,167,69,0.5)); }
                        h2 { margin: 0 0 15px; font-weight: 800; font-size: 2rem; text-transform: uppercase; letter-spacing: 3px; color: #28a745; position: relative; display: inline-block; }
                        h2::after {
                            content: "";
                            position: absolute;
                            bottom: -10px;
                            left: 0;
                            width: 100%;
                            height: 3px;
                            background: linear-gradient(90deg, transparent 0%, #28a745 20%, #28a745 80%, transparent 100%);
                            border-radius: 2px;
                        }
                        .message { margin: 20px 0; font-size: 1rem; line-height: 1.6; color: #ddd; }
                        .message strong { color: #fff; }
                        .email-highlight { color: #28a745; font-weight: 600; }
                        .small-text { font-size: 0.85rem; color: #888; margin-top: 10px; }
                        .btn {
                            display: inline-block;
                            background: linear-gradient(145deg, #28a745, #20c997);
                            color: white;
                            padding: 14px 32px;
                            text-decoration: none;
                            border-radius: 50px;
                            font-weight: 700;
                            font-size: 1rem;
                            text-transform: uppercase;
                            letter-spacing: 1.5px;
                            margin-top: 20px;
                            transition: all 0.3s ease;
                            border: 2px solid #28a745;
                            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
                            position: relative;
                            overflow: hidden;
                        }
                        .btn:hover { transform: translateY(-5px) scale(1.02); box-shadow: 0 20px 40px rgba(40, 167, 69, 0.4); background: linear-gradient(145deg, #34ce57, #28a745); }
                        .btn::before {
                            content: "";
                            position: absolute;
                            top: -50%;
                            left: -50%;
                            width: 200%;
                            height: 200%;
                            background: linear-gradient(45deg, transparent 30%, rgba(255, 255, 255, 0.2) 50%, transparent 70%);
                            transform: rotate(45deg) translateY(100%);
                            transition: transform 0.8s ease;
                            pointer-events: none;
                        }
                        .btn:hover::before { transform: rotate(45deg) translateY(-100%); }
                        .spam-note {
                            margin-top: 20px;
                            padding: 12px;
                            background: rgba(255, 255, 255, 0.05);
                            border-radius: 12px;
                            font-size: 0.8rem;
                            color: #888;
                        }
                        .spam-note i { margin-right: 8px; color: #e10600; }
                        @media (max-width: 500px) {
                            .card { padding: 35px 25px; }
                            .logo-title { font-size: 26px; }
                            .logo-title img { width: 40px; }
                            h2 { font-size: 1.6rem; }
                            .btn { padding: 12px 24px; font-size: 0.9rem; }
                        }
                    </style>
                </head>
                <body>
                <div class="bg-lines"></div>
                <div class="card">
                    <div class="logo-title">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/3/33/F1.svg" alt="F1 Logo">
                        <span>Fan Club</span>
                    </div>
                    <div class="success-icon"><i class="fas fa-check-circle"></i></div>
                    <h2>Sikeres regisztráció! 🏁</h2>
                    <div class="message">
                        Küldtünk egy emailt a(z) <strong class="email-highlight"><?php echo htmlspecialchars($email); ?></strong> címre.<br><br>
                        Kattints a benne lévő linkre a profilod aktiválásához!
                    </div>
                    <div class="spam-note">
                        <i class="fas fa-envelope"></i> Nézd meg a <strong>Spam mappát</strong> is, ha nem találod!
                    </div>
                    <a href="/f1fanclub/login/login.html" class="btn"><i class="fas fa-sign-in-alt" style="margin-right: 8px;"></i> Tovább a bejelentkezéshez →</a>
                </div>
                </body>
                </html>
                <?php
            } else {
                // Email sending error page
                ?>
                <!DOCTYPE html>
                <html lang="hu">
                <head>
                    <meta charset="UTF-8">
                    <title>Email Hiba – F1 Fan Club</title>
                    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
                    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
                    <link rel="icon" type="image/svg+xml" href="https://upload.wikimedia.org/wikipedia/commons/3/33/F1.svg">
                    <style>
                        * { margin: 0; padding: 0; box-sizing: border-box; }
                        body {
                            background: #0a0a0a;
                            color: white;
                            font-family: 'Poppins', sans-serif;
                            min-height: 100vh;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            position: relative;
                            overflow-x: hidden;
                            margin: 0;
                            padding: 20px;
                        }
                        body::before {
                            content: "";
                            position: fixed;
                            top: 0;
                            left: 0;
                            width: 100%;
                            height: 100%;
                            background: radial-gradient(circle at 20% 50%, rgba(225, 6, 0, 0.05) 0%, transparent 50%),
                                        radial-gradient(circle at 80% 80%, rgba(225, 6, 0, 0.05) 0%, transparent 50%);
                            pointer-events: none;
                            z-index: -1;
                        }
                        .bg-lines {
                            position: fixed;
                            width: 200%;
                            height: 200%;
                            background: repeating-linear-gradient(60deg, rgba(225, 6, 0, 0.03) 0px, rgba(225, 6, 0, 0.03) 2px, transparent 2px, transparent 10px);
                            animation: slide 10s linear infinite;
                            opacity: 0.3;
                            z-index: -1;
                            top: 0;
                            left: 0;
                        }
                        @keyframes slide { from { transform: translateX(0); } to { transform: translateX(-200px); } }
                        .card {
                            background: linear-gradient(145deg, #111111 0%, #1a1a1a 100%);
                            padding: 50px 40px;
                            border-radius: 30px;
                            width: 100%;
                            max-width: 450px;
                            margin: 0 auto;
                            border: 1px solid rgba(225, 6, 0, 0.3);
                            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.8), 0 0 50px rgba(225, 6, 0, 0.2);
                            position: relative;
                            overflow: hidden;
                            z-index: 1;
                            animation: slideInUp 0.8s ease-out;
                            text-align: center;
                        }
                        .card::before {
                            content: "";
                            position: absolute;
                            top: 0;
                            left: 0;
                            right: 0;
                            height: 4px;
                            background: linear-gradient(90deg, transparent 0%, #e10600 20%, #e10600 80%, transparent 100%);
                            z-index: 2;
                        }
                        .card::after {
                            content: "";
                            position: absolute;
                            top: -2px;
                            left: -2px;
                            right: -2px;
                            bottom: -2px;
                            background: linear-gradient(45deg, transparent 30%, rgba(225, 6, 0, 0.15) 50%, transparent 70%);
                            border-radius: 32px;
                            z-index: -1;
                            animation: borderGlow 4s infinite;
                        }
                        @keyframes borderGlow { 0%,100% { opacity: 0.3; } 50% { opacity: 0.8; } }
                        @keyframes slideInUp {
                            from { opacity: 0; transform: translateY(50px); }
                            to { opacity: 1; transform: translateY(0); }
                        }
                        .logo-title {
                            font-size: 32px;
                            color: white;
                            font-weight: 800;
                            text-transform: uppercase;
                            letter-spacing: 2px;
                            margin-bottom: 30px;
                            display: flex;
                            justify-content: center;
                            align-items: center;
                            gap: 12px;
                        }
                        .logo-title img { width: 50px; transition: transform 0.3s ease; }
                        .logo-title:hover img { transform: rotate(10deg) scale(1.1); }
                        .error-icon { font-size: 60px; color: #e10600; margin-bottom: 20px; filter: drop-shadow(0 0 15px rgba(225,6,0,0.5)); }
                        h2 { margin: 0 0 15px; font-weight: 800; font-size: 2rem; text-transform: uppercase; letter-spacing: 3px; color: #e10600; position: relative; display: inline-block; }
                        h2::after {
                            content: "";
                            position: absolute;
                            bottom: -10px;
                            left: 0;
                            width: 100%;
                            height: 3px;
                            background: linear-gradient(90deg, transparent 0%, #e10600 20%, #e10600 80%, transparent 100%);
                            border-radius: 2px;
                        }
                        .message { margin: 20px 0; font-size: 1rem; line-height: 1.6; color: #ddd; }
                        .btn {
                            display: inline-block;
                            background: linear-gradient(145deg, #e10600, #ff4d4d);
                            color: white;
                            padding: 14px 32px;
                            text-decoration: none;
                            border-radius: 50px;
                            font-weight: 700;
                            font-size: 1rem;
                            text-transform: uppercase;
                            letter-spacing: 1.5px;
                            margin-top: 20px;
                            transition: all 0.3s ease;
                            border: 2px solid #e10600;
                            box-shadow: 0 5px 15px rgba(225, 6, 0, 0.3);
                            position: relative;
                            overflow: hidden;
                        }
                        .btn:hover { transform: translateY(-5px) scale(1.02); box-shadow: 0 20px 40px rgba(225, 6, 0, 0.4); }
                        .btn::before {
                            content: "";
                            position: absolute;
                            top: -50%;
                            left: -50%;
                            width: 200%;
                            height: 200%;
                            background: linear-gradient(45deg, transparent 30%, rgba(255, 255, 255, 0.2) 50%, transparent 70%);
                            transform: rotate(45deg) translateY(100%);
                            transition: transform 0.8s ease;
                            pointer-events: none;
                        }
                        .btn:hover::before { transform: rotate(45deg) translateY(-100%); }
                        @media (max-width: 500px) {
                            .card { padding: 35px 25px; }
                            .logo-title { font-size: 26px; }
                            .logo-title img { width: 40px; }
                            h2 { font-size: 1.6rem; }
                        }
                    </style>
                </head>
                <body>
                <div class="bg-lines"></div>
                <div class="card">
                    <div class="logo-title">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/3/33/F1.svg" alt="F1 Logo">
                        <span>Fan Club</span>
                    </div>
                    <div class="error-icon"><i class="fas fa-envelope"></i></div>
                    <h2>Email küldési hiba!</h2>
                    <div class="message">Nem sikerült elküldeni a megerősítő emailt.<br>Kérlek próbáld újra később!</div>
                    <a href="register.html" class="btn"><i class="fas fa-arrow-left" style="margin-right: 8px;"></i> Vissza</a>
                </div>
                </body>
                </html>
                <?php
            }

        } else {
            // Database error page
            ?>
            <!DOCTYPE html>
            <html lang="hu">
            <head>
                <meta charset="UTF-8">
                <title>Adatbázis Hiba – F1 Fan Club</title>
                <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
                <link rel="icon" type="image/svg+xml" href="https://upload.wikimedia.org/wikipedia/commons/3/33/F1.svg">
                <style>
                    * { margin: 0; padding: 0; box-sizing: border-box; }
                    body {
                        background: #0a0a0a;
                        color: white;
                        font-family: 'Poppins', sans-serif;
                        min-height: 100vh;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        position: relative;
                        overflow-x: hidden;
                        margin: 0;
                        padding: 20px;
                    }
                    body::before {
                        content: "";
                        position: fixed;
                        top: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                        background: radial-gradient(circle at 20% 50%, rgba(225, 6, 0, 0.05) 0%, transparent 50%),
                                    radial-gradient(circle at 80% 80%, rgba(225, 6, 0, 0.05) 0%, transparent 50%);
                        pointer-events: none;
                        z-index: -1;
                    }
                    .bg-lines {
                        position: fixed;
                        width: 200%;
                        height: 200%;
                        background: repeating-linear-gradient(60deg, rgba(225, 6, 0, 0.03) 0px, rgba(225, 6, 0, 0.03) 2px, transparent 2px, transparent 10px);
                        animation: slide 10s linear infinite;
                        opacity: 0.3;
                        z-index: -1;
                        top: 0;
                        left: 0;
                    }
                    @keyframes slide { from { transform: translateX(0); } to { transform: translateX(-200px); } }
                    .card {
                        background: linear-gradient(145deg, #111111 0%, #1a1a1a 100%);
                        padding: 50px 40px;
                        border-radius: 30px;
                        width: 100%;
                        max-width: 450px;
                        margin: 0 auto;
                        border: 1px solid rgba(225, 6, 0, 0.3);
                        box-shadow: 0 30px 60px rgba(0, 0, 0, 0.8), 0 0 50px rgba(225, 6, 0, 0.2);
                        position: relative;
                        overflow: hidden;
                        z-index: 1;
                        animation: slideInUp 0.8s ease-out;
                        text-align: center;
                    }
                    .card::before {
                        content: "";
                        position: absolute;
                        top: 0;
                        left: 0;
                        right: 0;
                        height: 4px;
                        background: linear-gradient(90deg, transparent 0%, #e10600 20%, #e10600 80%, transparent 100%);
                        z-index: 2;
                    }
                    .card::after {
                        content: "";
                        position: absolute;
                        top: -2px;
                        left: -2px;
                        right: -2px;
                        bottom: -2px;
                        background: linear-gradient(45deg, transparent 30%, rgba(225, 6, 0, 0.15) 50%, transparent 70%);
                        border-radius: 32px;
                        z-index: -1;
                        animation: borderGlow 4s infinite;
                    }
                    @keyframes borderGlow { 0%,100% { opacity: 0.3; } 50% { opacity: 0.8; } }
                    @keyframes slideInUp {
                        from { opacity: 0; transform: translateY(50px); }
                        to { opacity: 1; transform: translateY(0); }
                    }
                    .logo-title {
                        font-size: 32px;
                        color: white;
                        font-weight: 800;
                        text-transform: uppercase;
                        letter-spacing: 2px;
                        margin-bottom: 30px;
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        gap: 12px;
                    }
                    .logo-title img { width: 50px; transition: transform 0.3s ease; }
                    .logo-title:hover img { transform: rotate(10deg) scale(1.1); }
                    .error-icon { font-size: 60px; color: #e10600; margin-bottom: 20px; filter: drop-shadow(0 0 15px rgba(225,6,0,0.5)); }
                    h2 { margin: 0 0 15px; font-weight: 800; font-size: 2rem; text-transform: uppercase; letter-spacing: 3px; color: #e10600; position: relative; display: inline-block; }
                    h2::after {
                        content: "";
                        position: absolute;
                        bottom: -10px;
                        left: 0;
                        width: 100%;
                        height: 3px;
                        background: linear-gradient(90deg, transparent 0%, #e10600 20%, #e10600 80%, transparent 100%);
                        border-radius: 2px;
                    }
                    .message { margin: 20px 0; font-size: 1rem; line-height: 1.6; color: #ddd; }
                    .btn {
                        display: inline-block;
                        background: linear-gradient(145deg, #e10600, #ff4d4d);
                        color: white;
                        padding: 14px 32px;
                        text-decoration: none;
                        border-radius: 50px;
                        font-weight: 700;
                        font-size: 1rem;
                        text-transform: uppercase;
                        letter-spacing: 1.5px;
                        margin-top: 20px;
                        transition: all 0.3s ease;
                        border: 2px solid #e10600;
                        box-shadow: 0 5px 15px rgba(225, 6, 0, 0.3);
                        position: relative;
                        overflow: hidden;
                    }
                    .btn:hover { transform: translateY(-5px) scale(1.02); box-shadow: 0 20px 40px rgba(225, 6, 0, 0.4); }
                    .btn::before {
                        content: "";
                        position: absolute;
                        top: -50%;
                        left: -50%;
                        width: 200%;
                        height: 200%;
                        background: linear-gradient(45deg, transparent 30%, rgba(255, 255, 255, 0.2) 50%, transparent 70%);
                        transform: rotate(45deg) translateY(100%);
                        transition: transform 0.8s ease;
                        pointer-events: none;
                    }
                    .btn:hover::before { transform: rotate(45deg) translateY(-100%); }
                    @media (max-width: 500px) {
                        .card { padding: 35px 25px; }
                        .logo-title { font-size: 26px; }
                        .logo-title img { width: 40px; }
                        h2 { font-size: 1.6rem; }
                    }
                </style>
            </head>
            <body>
            <div class="bg-lines"></div>
            <div class="card">
                <div class="logo-title">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/3/33/F1.svg" alt="F1 Logo">
                    <span>Fan Club</span>
                </div>
                <div class="error-icon"><i class="fas fa-database"></i></div>
                <h2>Adatbázis hiba!</h2>
                <div class="message">Hiba történt a regisztráció során.<br>Kérlek próbáld újra később!</div>
                <a href="register.html" class="btn"><i class="fas fa-arrow-left" style="margin-right: 8px;"></i> Vissza</a>
            </div>
            </body>
            </html>
            <?php
>>>>>>> f81424192996985be2da559c0f9a2c1f13f5eb7f
        }
    }
    $stmt->close();
}
$conn->close();
?>