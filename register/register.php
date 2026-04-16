<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

/**
 * ============================================================================
 * DATABASE CONFIGURATION
 * ============================================================================
 * Establishes the connection to the MySQL database.
 */
$DB_HOST = "localhost";
$DB_USER = "swmjndga_swmjndga";
$DB_PASS = "Teszt1234!";
$DB_NAME = "swmjndga_f1adat";

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) {
    die("Kapcsolódási hiba: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    /** Sanitize input data */
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $fav_team = !empty($_POST['fav_team']) ? $_POST['fav_team'] : null;

    /** Create password hash */
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    
    /** Generate a 64-character secure verification token */
    $token = bin2hex(random_bytes(32));

    /** Check if username or email is already registered */
    $sql_check = "SELECT id FROM users WHERE username=? OR email=?";
    $stmt = $conn->prepare($sql_check);
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Error page - user already exists
        ?>
        <!DOCTYPE html>
        <html lang="hu">
        <head>
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
            </style>
        </head>
        <body>
        <div class="bg-lines"></div>
        <div class="card">
            <div class="logo-title">
                <img src="https://upload.wikimedia.org/wikipedia/commons/3/33/F1.svg" alt="F1 Logo">
                <span>Fan Club</span>
            </div>
            <div class="error-icon"><i class="fas fa-exclamation-triangle"></i></div>
            <h2>Hiba!</h2>
            <div class="message">Már létezik ilyen felhasználónév vagy e-mail cím!</div>
            <a href="register.html" class="btn"><i class="fas fa-arrow-left" style="margin-right: 8px;"></i> Vissza</a>
        </div>
        </body>
        </html>
        <?php
    } else {
        /**
         * Insert new user into the database.
         * IMPORTANT: Ensure verification_token column is VARCHAR(100).
         */
        $sql = "INSERT INTO users (username, email, password, fav_team, verification_token, is_verified) VALUES (?, ?, ?, ?, ?, 0)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $username, $email, $passwordHash, $fav_team, $token);

        if ($stmt->execute()) {
            /** Send verification email */
            $to = $email;
            $subject = "F1 Fan Club - Regisztráció megerősítése";
            
            /**
             * Construct verification link.
             * Uses urlencode to ensure the link remains valid.
             */
            $link = "http://f1fanclub.hu/f1fanclub/register/verify.php?token=" . urlencode($token);
            
            $emailBody = "Szia $username!\n\nKöszi, hogy regisztráltál!\nKérlek kattints az alábbi linkre a fiókod megerősítéséhez:\n\n$link\n\nÜdvözlettel,\nF1 Fan Club Csapat";
            
            /** Set email headers for better deliverability */
            $headers = "From: noreply@swmjndga.hu\r\n";
            $headers .= "Reply-To: noreply@swmjndga.hu\r\n";
            $headers .= "X-Mailer: PHP/" . phpversion();

            if(mail($to, $subject, $emailBody, $headers)) {
                // Success page - styled
                ?>
                <!DOCTYPE html>
                <html lang="hu">
                <head>
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
        }
    }
    $stmt->close();
}
$conn->close();
?>