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

    $stmt = $conn->prepare("SELECT id, username, password, is_verified, role FROM users WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['password'])) {
            // 1. Email ellenőrzés
            if ($user['is_verified'] == 0) {
                // Styled email not verified page
                ?>
                <!DOCTYPE html>
                <html lang="hu">
                <head>
                    <meta charset="UTF-8">
                    <title>Feldolgozás – F1 Fan Club</title>
                    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
                    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
                    <link rel="icon" type="image/svg+xml" href="https://upload.wikimedia.org/wikipedia/commons/3/33/F1.svg">
                    <style>
                        *{margin:0;padding:0;box-sizing:border-box}
                        body{background:#0a0a0a;color:#fff;font-family:'Poppins',sans-serif;min-height:100vh;display:flex;align-items:center;justify-content:center;margin:0;padding:20px}
                        .bg-lines{position:fixed;width:200%;height:200%;background:repeating-linear-gradient(60deg,rgba(225,6,0,0.03)0px,rgba(225,6,0,0.03)2px,transparent 2px,transparent 10px);animation:slide 10s linear infinite;opacity:0.3;z-index:-1;top:0;left:0}
                        @keyframes slide{from{transform:translateX(0)}to{transform:translateX(-200px)}}
                        .card{background:linear-gradient(145deg,#111,#1a1a1a);padding:50px 40px;border-radius:30px;max-width:450px;width:100%;border:1px solid rgba(225,6,0,0.3);text-align:center;position:relative;animation:slideInUp 0.8s ease-out}
                        .card::before{content:"";position:absolute;top:0;left:0;right:0;height:4px;background:linear-gradient(90deg,transparent,#e10600 20%,#e10600 80%,transparent)}
                        .logo-title{font-size:32px;font-weight:800;text-transform:uppercase;margin-bottom:30px;display:flex;justify-content:center;align-items:center;gap:12px}
                        .logo-title img{width:50px}
                        .error-icon{font-size:60px;color:#ffcc00;margin-bottom:20px}
                        h2{font-weight:800;font-size:2rem;text-transform:uppercase;color:#ffcc00;margin-bottom:15px}
                        .message{margin:20px 0;font-size:1rem;color:#ddd;line-height:1.6}
                        .btn{display:inline-block;background:linear-gradient(145deg,#e10600,#ff4d4d);color:#fff;padding:14px 32px;text-decoration:none;border-radius:50px;font-weight:700;margin-top:20px;transition:all 0.3s ease}
                        .btn:hover{transform:translateY(-3px)}
                        @keyframes slideInUp{from{opacity:0;transform:translateY(50px)}to{opacity:1;transform:translateY(0)}}
                        @media(max-width:500px){.card{padding:35px 25px}.logo-title{font-size:26px}.logo-title img{width:40px}h2{font-size:1.6rem}}
                    </style>
                </head>
                <body>
                <div class="bg-lines"></div>
                <div class="card">
                    <div class="logo-title"><img src="https://upload.wikimedia.org/wikipedia/commons/3/33/F1.svg" alt="F1 Logo"><span>Fan Club</span></div>
                    <div class="error-icon"><i class="fas fa-envelope"></i></div>
                    <h2>Feldolgozás...</h2>
                    <div class="message">A fiókod még nincs megerősítve!<br>Kérlek ellenőrizd az emailjeidet.</div>
                    <a href="login.html" class="btn">Vissza</a>
                </div>
                </body>
                </html>
                <?php
                exit;
            }

            session_regenerate_id(true); 
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            
            $current_session_id = session_id();
            $update_session = $conn->prepare("UPDATE users SET session_token = ? WHERE id = ?");
            $update_session->bind_param("si", $current_session_id, $user['id']);
            $update_session->execute();

            if (isset($_POST['remember_me'])) {
                $token = bin2hex(random_bytes(32));
                setcookie('remember_me', $token, time() + (86400 * 30), "/");
                $update_token = $conn->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
                $update_token->bind_param("si", $token, $user['id']);
                $update_token->execute();
            }

            if ($user['role'] === 'admin') {
                header("Location: /f1fanclub/admin/admin.php");
            } else {
                header("Location: /f1fanclub/index.php");
            }
            exit;

        } else {
            // Styled wrong password page
            ?>
            <!DOCTYPE html>
            <html lang="hu">
            <head>
                <meta charset="UTF-8">
                <title>Hibás Jelszó – F1 Fan Club</title>
                <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
                <link rel="icon" type="image/svg+xml" href="https://upload.wikimedia.org/wikipedia/commons/3/33/F1.svg">
                <style>
                    *{margin:0;padding:0;box-sizing:border-box}
                    body{background:#0a0a0a;color:#fff;font-family:'Poppins',sans-serif;min-height:100vh;display:flex;align-items:center;justify-content:center;margin:0;padding:20px}
                    .bg-lines{position:fixed;width:200%;height:200%;background:repeating-linear-gradient(60deg,rgba(225,6,0,0.03)0px,rgba(225,6,0,0.03)2px,transparent 2px,transparent 10px);animation:slide 10s linear infinite;opacity:0.3;z-index:-1;top:0;left:0}
                    @keyframes slide{from{transform:translateX(0)}to{transform:translateX(-200px)}}
                    .card{background:linear-gradient(145deg,#111,#1a1a1a);padding:50px 40px;border-radius:30px;max-width:450px;width:100%;border:1px solid rgba(225,6,0,0.3);text-align:center;position:relative;animation:slideInUp 0.8s ease-out}
                    .card::before{content:"";position:absolute;top:0;left:0;right:0;height:4px;background:linear-gradient(90deg,transparent,#e10600 20%,#e10600 80%,transparent)}
                    .logo-title{font-size:32px;font-weight:800;text-transform:uppercase;margin-bottom:30px;display:flex;justify-content:center;align-items:center;gap:12px}
                    .logo-title img{width:50px}
                    .error-icon{font-size:60px;color:#e10600;margin-bottom:20px}
                    h2{font-weight:800;font-size:2rem;text-transform:uppercase;color:#e10600;margin-bottom:15px}
                    .message{margin:20px 0;font-size:1rem;color:#ddd;line-height:1.6}
                    .btn{display:inline-block;background:linear-gradient(145deg,#e10600,#ff4d4d);color:#fff;padding:14px 32px;text-decoration:none;border-radius:50px;font-weight:700;margin-top:20px;transition:all 0.3s ease}
                    .btn:hover{transform:translateY(-3px)}
                    @keyframes slideInUp{from{opacity:0;transform:translateY(50px)}to{opacity:1;transform:translateY(0)}}
                    @media(max-width:500px){.card{padding:35px 25px}.logo-title{font-size:26px}.logo-title img{width:40px}h2{font-size:1.6rem}}
                </style>
            </head>
            <body>
            <div class="bg-lines"></div>
            <div class="card">
                <div class="logo-title"><img src="https://upload.wikimedia.org/wikipedia/commons/3/33/F1.svg" alt="F1 Logo"><span>Fan Club</span></div>
                <div class="error-icon"><i class="fas fa-lock"></i></div>
                <h2>Hibás jelszó!</h2>
                <div class="message">A megadott jelszó nem megfelelő.<br>Kérlek próbáld újra!</div>
                <a href="login.html" class="btn">Vissza</a>
            </div>
            </body>
            </html>
            <?php
        }
    } else {
        // Styled user not found page
        ?>
        <!DOCTYPE html>
        <html lang="hu">
        <head>
            <meta charset="UTF-8">
            <title>Hibás Felhasználónév – F1 Fan Club</title>
            <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
            <link rel="icon" type="image/svg+xml" href="https://upload.wikimedia.org/wikipedia/commons/3/33/F1.svg">
            <style>
                *{margin:0;padding:0;box-sizing:border-box}
                body{background:#0a0a0a;color:#fff;font-family:'Poppins',sans-serif;min-height:100vh;display:flex;align-items:center;justify-content:center;margin:0;padding:20px}
                .bg-lines{position:fixed;width:200%;height:200%;background:repeating-linear-gradient(60deg,rgba(225,6,0,0.03)0px,rgba(225,6,0,0.03)2px,transparent 2px,transparent 10px);animation:slide 10s linear infinite;opacity:0.3;z-index:-1;top:0;left:0}
                @keyframes slide{from{transform:translateX(0)}to{transform:translateX(-200px)}}
                .card{background:linear-gradient(145deg,#111,#1a1a1a);padding:50px 40px;border-radius:30px;max-width:450px;width:100%;border:1px solid rgba(225,6,0,0.3);text-align:center;position:relative;animation:slideInUp 0.8s ease-out}
                .card::before{content:"";position:absolute;top:0;left:0;right:0;height:4px;background:linear-gradient(90deg,transparent,#e10600 20%,#e10600 80%,transparent)}
                .logo-title{font-size:32px;font-weight:800;text-transform:uppercase;margin-bottom:30px;display:flex;justify-content:center;align-items:center;gap:12px}
                .logo-title img{width:50px}
                .error-icon{font-size:60px;color:#e10600;margin-bottom:20px}
                h2{font-weight:800;font-size:2rem;text-transform:uppercase;color:#e10600;margin-bottom:15px}
                .message{margin:20px 0;font-size:1rem;color:#ddd;line-height:1.6}
                .btn{display:inline-block;background:linear-gradient(145deg,#e10600,#ff4d4d);color:#fff;padding:14px 32px;text-decoration:none;border-radius:50px;font-weight:700;margin-top:20px;transition:all 0.3s ease}
                .btn:hover{transform:translateY(-3px)}
                @keyframes slideInUp{from{opacity:0;transform:translateY(50px)}to{opacity:1;transform:translateY(0)}}
                @media(max-width:500px){.card{padding:35px 25px}.logo-title{font-size:26px}.logo-title img{width:40px}h2{font-size:1.6rem}}
            </style>
        </head>
        <body>
        <div class="bg-lines"></div>
        <div class="card">
            <div class="logo-title"><img src="https://upload.wikimedia.org/wikipedia/commons/3/33/F1.svg" alt="F1 Logo"><span>Fan Club</span></div>
            <div class="error-icon"><i class="fas fa-user-slash"></i></div>
            <h2>Hibás felhasználónév!</h2>
            <div class="message">A megadott felhasználónév nem létezik.<br>Kérlek ellenőrizd és próbáld újra!</div>
            <a href="login.html" class="btn">Vissza</a>
        </div>
        </body>
        </html>
        <?php
    }
    $stmt->close();
}
$conn->close();
?>