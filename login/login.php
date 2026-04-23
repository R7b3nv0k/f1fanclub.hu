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
                ?>
                <!DOCTYPE html>
                <html lang="hu">
                <head>
                    <link rel="icon" type="image/svg+xml" href="https://upload.wikimedia.org/wikipedia/commons/3/33/F1.svg">
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
                    <title>Feldolgozás – F1 Fan Club</title>
                    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
                    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
                    <style>
                        *{margin:0;padding:0;box-sizing:border-box}
                        body{background:#0a0a0a;color:#fff;font-family:'Poppins',sans-serif;min-height:100vh;display:flex;align-items:center;justify-content:center;margin:0;padding:20px}
                        body::before{content:"";position:fixed;top:0;left:0;width:100%;height:100%;background:radial-gradient(circle at 20% 50%,rgba(225,6,0,0.05) 0%,transparent 50%),radial-gradient(circle at 80% 80%,rgba(225,6,0,0.05) 0%,transparent 50%);pointer-events:none;z-index:-1}
                        .bg-lines{position:fixed;width:200%;height:200%;background:repeating-linear-gradient(60deg,rgba(225,6,0,0.03)0px,rgba(225,6,0,0.03)2px,transparent 2px,transparent 10px);animation:slide 10s linear infinite;opacity:0.3;z-index:-1;top:0;left:0}
                        @keyframes slide{from{transform:translateX(0)}to{transform:translateX(-200px)}}
                        
                        .card{
                            background:linear-gradient(145deg,#111,#1a1a1a);
                            padding:40px 35px;
                            border-radius:30px;
                            max-width:420px;
                            width:100%;
                            border:1px solid rgba(225,6,0,0.3);
                            text-align:center;
                            position:relative;
                            box-shadow:0 30px 60px rgba(0,0,0,0.8),0 0 50px rgba(225,6,0,0.2);
                            display:flex;
                            flex-direction:column;
                            align-items:center;
                            justify-content:center;
                            min-height:380px;
                        }
                        .card::before{
                            content:"";
                            position:absolute;
                            top:0;
                            left:0;
                            right:0;
                            height:4px;
                            background:linear-gradient(90deg,transparent 0%,#ffcc00 20%,#ffcc00 80%,transparent 100%);
                            z-index:2;
                            border-radius:30px 30px 0 0;
                        }
                        .logo-title{
                            font-size:28px;
                            font-weight:800;
                            text-transform:uppercase;
                            margin-bottom:25px;
                            display:flex;
                            justify-content:center;
                            align-items:center;
                            gap:10px;
                            flex-wrap:wrap;
                            word-break:break-word;
                            color:#fff;
                        }
                        .logo-title img{width:45px}
                        .error-icon{
                            font-size:65px;
                            color:#ffcc00;
                            margin-bottom:15px;
                            line-height:1;
                        }
                        h2{
                            font-weight:800;
                            font-size:1.8rem;
                            text-transform:uppercase;
                            color:#ffcc00;
                            margin-bottom:20px;
                            word-break:break-word;
                            line-height:1.2;
                        }
                        .message{
                            margin:0 0 30px 0;
                            font-size:1rem;
                            color:#ddd;
                            line-height:1.6;
                            word-break:break-word;
                            flex:1;
                            display:flex;
                            align-items:center;
                        }
                        .btn{
                            display:inline-block;
                            background:linear-gradient(145deg,#e10600,#ff4d4d);
                            color:#fff;
                            padding:16px 40px;
                            text-decoration:none;
                            border-radius:50px;
                            font-weight:700;
                            border:none;
                            cursor:pointer;
                            font-size:1.1rem;
                            transition:all 0.3s;
                            text-transform:uppercase;
                            letter-spacing:2px;
                            width:100%;
                            max-width:280px;
                            margin-top:auto;
                        }
                        .btn:hover{
                            transform:translateY(-3px);
                            box-shadow:0 10px 25px rgba(225,6,0,0.4);
                        }
                        
                        @media (max-width: 600px) {
                            body{padding:15px;align-items:center;}
                            .card{padding:35px 25px;max-width:100%;min-height:400px;}
                            .logo-title{font-size:30px;}
                            .logo-title img{width:50px;}
                            .error-icon{font-size:75px;margin-bottom:15px;}
                            h2{font-size:2rem;margin-bottom:20px;}
                            .message{font-size:1.15rem;margin-bottom:30px;}
                            .btn{padding:18px 30px;font-size:1.2rem;max-width:100%;}
                        }
                        @media (max-width: 400px) {
                            .card{padding:30px 20px;min-height:380px;}
                            .logo-title{font-size:26px;}
                            .logo-title img{width:42px;}
                            .error-icon{font-size:65px;}
                            h2{font-size:1.7rem;}
                            .message{font-size:1rem;}
                            .btn{padding:16px 25px;font-size:1.1rem;}
                        }
                        @media (max-width: 350px) {
                            .card{padding:25px 16px;min-height:350px;}
                            h2{font-size:1.5rem;}
                            .btn{font-size:1rem;padding:14px 20px;}
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
                    <div class="error-icon"><i class="fas fa-envelope"></i></div>
                    <h2>FELDOLGOZÁS...</h2>
                    <div class="message">A fiókod még nincs megerősítve!<br>Kérlek ellenőrizd az emailjeidet.</div>
                    <a href="login.html" class="btn">VISSZA</a>
                </div>
                </body>
                </html>
                <?php
                exit;
            }

            session_regenerate_id(true); 
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            
            $ip_address = $_SERVER['REMOTE_ADDR'];
            $update_ip = $conn->prepare("UPDATE users SET ip_address = ? WHERE id = ?");
            $update_ip->bind_param("si", $ip_address, $user['id']);
            $update_ip->execute();
            $update_ip->close();
            
            $log_action = 'login';
            $log_details = 'Sikeres bejelentkezés';
            $insert_log = $conn->prepare("INSERT INTO activity_logs (username, action, details, ip_address) VALUES (?, ?, ?, ?)");
            $insert_log->bind_param("ssss", $user['username'], $log_action, $log_details, $ip_address);
            $insert_log->execute();
            $insert_log->close();

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
            ?>
            <!DOCTYPE html>
            <html lang="hu">
            <head>
                <link rel="icon" type="image/svg+xml" href="https://upload.wikimedia.org/wikipedia/commons/3/33/F1.svg">
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
                <title>Hibás Jelszó – F1 Fan Club</title>
                <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
                <style>
                    *{margin:0;padding:0;box-sizing:border-box}
                    body{background:#0a0a0a;color:#fff;font-family:'Poppins',sans-serif;min-height:100vh;display:flex;align-items:center;justify-content:center;margin:0;padding:20px}
                    body::before{content:"";position:fixed;top:0;left:0;width:100%;height:100%;background:radial-gradient(circle at 20% 50%,rgba(225,6,0,0.05) 0%,transparent 50%),radial-gradient(circle at 80% 80%,rgba(225,6,0,0.05) 0%,transparent 50%);pointer-events:none;z-index:-1}
                    .bg-lines{position:fixed;width:200%;height:200%;background:repeating-linear-gradient(60deg,rgba(225,6,0,0.03)0px,rgba(225,6,0,0.03)2px,transparent 2px,transparent 10px);animation:slide 10s linear infinite;opacity:0.3;z-index:-1;top:0;left:0}
                    @keyframes slide{from{transform:translateX(0)}to{transform:translateX(-200px)}}
                    
                    .card{
                        background:linear-gradient(145deg,#111,#1a1a1a);
                        padding:40px 35px;
                        border-radius:30px;
                        max-width:420px;
                        width:100%;
                        border:1px solid rgba(225,6,0,0.3);
                        text-align:center;
                        position:relative;
                        box-shadow:0 30px 60px rgba(0,0,0,0.8),0 0 50px rgba(225,6,0,0.2);
                        display:flex;
                        flex-direction:column;
                        align-items:center;
                        justify-content:center;
                        min-height:380px;
                    }
                    .card::before{
                        content:"";
                        position:absolute;
                        top:0;
                        left:0;
                        right:0;
                        height:4px;
                        background:linear-gradient(90deg,transparent 0%,#e10600 20%,#e10600 80%,transparent 100%);
                        z-index:2;
                        border-radius:30px 30px 0 0;
                    }
                    .logo-title{
                        font-size:28px;
                        font-weight:800;
                        text-transform:uppercase;
                        margin-bottom:25px;
                        display:flex;
                        justify-content:center;
                        align-items:center;
                        gap:10px;
                        flex-wrap:wrap;
                        word-break:break-word;
                        color:#fff;
                    }
                    .logo-title img{width:45px}
                    .error-icon{
                        font-size:65px;
                        color:#e10600;
                        margin-bottom:15px;
                        line-height:1;
                    }
                    h2{
                        font-weight:800;
                        font-size:1.8rem;
                        text-transform:uppercase;
                        color:#e10600;
                        margin-bottom:20px;
                        word-break:break-word;
                        line-height:1.2;
                    }
                    .message{
                        margin:0 0 30px 0;
                        font-size:1rem;
                        color:#ddd;
                        line-height:1.6;
                        word-break:break-word;
                        flex:1;
                        display:flex;
                        align-items:center;
                    }
                    .btn{
                        display:inline-block;
                        background:linear-gradient(145deg,#e10600,#ff4d4d);
                        color:#fff;
                        padding:16px 40px;
                        text-decoration:none;
                        border-radius:50px;
                        font-weight:700;
                        border:none;
                        cursor:pointer;
                        font-size:1.1rem;
                        transition:all 0.3s;
                        text-transform:uppercase;
                        letter-spacing:2px;
                        width:100%;
                        max-width:280px;
                        margin-top:auto;
                    }
                    .btn:hover{
                        transform:translateY(-3px);
                        box-shadow:0 10px 25px rgba(225,6,0,0.4);
                    }
                    
                    @media (max-width: 600px) {
                        body{padding:15px;align-items:center;}
                        .card{padding:35px 25px;max-width:100%;min-height:400px;}
                        .logo-title{font-size:30px;}
                        .logo-title img{width:50px;}
                        .error-icon{font-size:75px;margin-bottom:15px;}
                        h2{font-size:2rem;margin-bottom:20px;}
                        .message{font-size:1.15rem;margin-bottom:30px;}
                        .btn{padding:18px 30px;font-size:1.2rem;max-width:100%;}
                    }
                    @media (max-width: 400px) {
                        .card{padding:30px 20px;min-height:380px;}
                        .logo-title{font-size:26px;}
                        .logo-title img{width:42px;}
                        .error-icon{font-size:65px;}
                        h2{font-size:1.7rem;}
                        .message{font-size:1rem;}
                        .btn{padding:16px 25px;font-size:1.1rem;}
                    }
                    @media (max-width: 350px) {
                        .card{padding:25px 16px;min-height:350px;}
                        h2{font-size:1.5rem;}
                        .btn{font-size:1rem;padding:14px 20px;}
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
                <div class="error-icon"><i class="fas fa-lock"></i></div>
                <h2>HIBÁS JELSZÓ!</h2>
                <div class="message">A megadott jelszó nem megfelelő.<br>Kérlek próbáld újra!</div>
                <a href="login.html" class="btn">VISSZA</a>
            </div>
            </body>
            </html>
            <?php
        }
    } else {
        ?>
        <!DOCTYPE html>
        <html lang="hu">
        <head>
                                <link rel="icon" type="image/svg+xml" href="https://upload.wikimedia.org/wikipedia/commons/3/33/F1.svg">
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
            <title>Hibás Felhasználónév – F1 Fan Club</title>
            <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
            <style>
                *{margin:0;padding:0;box-sizing:border-box}
                body{background:#0a0a0a;color:#fff;font-family:'Poppins',sans-serif;min-height:100vh;display:flex;align-items:center;justify-content:center;margin:0;padding:20px}
                body::before{content:"";position:fixed;top:0;left:0;width:100%;height:100%;background:radial-gradient(circle at 20% 50%,rgba(225,6,0,0.05) 0%,transparent 50%),radial-gradient(circle at 80% 80%,rgba(225,6,0,0.05) 0%,transparent 50%);pointer-events:none;z-index:-1}
                .bg-lines{position:fixed;width:200%;height:200%;background:repeating-linear-gradient(60deg,rgba(225,6,0,0.03)0px,rgba(225,6,0,0.03)2px,transparent 2px,transparent 10px);animation:slide 10s linear infinite;opacity:0.3;z-index:-1;top:0;left:0}
                @keyframes slide{from{transform:translateX(0)}to{transform:translateX(-200px)}}
                
                .card{
                    background:linear-gradient(145deg,#111,#1a1a1a);
                    padding:40px 35px;
                    border-radius:30px;
                    max-width:420px;
                    width:100%;
                    border:1px solid rgba(225,6,0,0.3);
                    text-align:center;
                    position:relative;
                    box-shadow:0 30px 60px rgba(0,0,0,0.8),0 0 50px rgba(225,6,0,0.2);
                    display:flex;
                    flex-direction:column;
                    align-items:center;
                    justify-content:center;
                    min-height:380px;
                }
                .card::before{
                    content:"";
                    position:absolute;
                    top:0;
                    left:0;
                    right:0;
                    height:4px;
                    background:linear-gradient(90deg,transparent 0%,#e10600 20%,#e10600 80%,transparent 100%);
                    z-index:2;
                    border-radius:30px 30px 0 0;
                }
                .logo-title{
                    font-size:28px;
                    font-weight:800;
                    text-transform:uppercase;
                    margin-bottom:25px;
                    display:flex;
                    justify-content:center;
                    align-items:center;
                    gap:10px;
                    flex-wrap:wrap;
                    word-break:break-word;
                    color:#fff;
                }
                .logo-title img{width:45px}
                .error-icon{
                    font-size:65px;
                    color:#e10600;
                    margin-bottom:15px;
                    line-height:1;
                }
                h2{
                    font-weight:800;
                    font-size:1.8rem;
                    text-transform:uppercase;
                    color:#e10600;
                    margin-bottom:20px;
                    word-break:break-word;
                    line-height:1.2;
                }
                .message{
                    margin:0 0 30px 0;
                    font-size:1rem;
                    color:#ddd;
                    line-height:1.6;
                    word-break:break-word;
                    flex:1;
                    display:flex;
                    align-items:center;
                }
                .btn{
                    display:inline-block;
                    background:linear-gradient(145deg,#e10600,#ff4d4d);
                    color:#fff;
                    padding:16px 40px;
                    text-decoration:none;
                    border-radius:50px;
                    font-weight:700;
                    border:none;
                    cursor:pointer;
                    font-size:1.1rem;
                    transition:all 0.3s;
                    text-transform:uppercase;
                    letter-spacing:2px;
                    width:100%;
                    max-width:280px;
                    margin-top:auto;
                }
                .btn:hover{
                    transform:translateY(-3px);
                    box-shadow:0 10px 25px rgba(225,6,0,0.4);
                }
                
                @media (max-width: 600px) {
                    body{padding:15px;align-items:center;}
                    .card{padding:35px 25px;max-width:100%;min-height:400px;}
                    .logo-title{font-size:30px;}
                    .logo-title img{width:50px;}
                    .error-icon{font-size:75px;margin-bottom:15px;}
                    h2{font-size:2rem;margin-bottom:20px;}
                    .message{font-size:1.15rem;margin-bottom:30px;}
                    .btn{padding:18px 30px;font-size:1.2rem;max-width:100%;}
                }
                @media (max-width: 400px) {
                    .card{padding:30px 20px;min-height:380px;}
                    .logo-title{font-size:26px;}
                    .logo-title img{width:42px;}
                    .error-icon{font-size:65px;}
                    h2{font-size:1.7rem;}
                    .message{font-size:1rem;}
                    .btn{padding:16px 25px;font-size:1.1rem;}
                }
                @media (max-width: 350px) {
                    .card{padding:25px 16px;min-height:350px;}
                    h2{font-size:1.5rem;}
                    .btn{font-size:1rem;padding:14px 20px;}
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
            <div class="error-icon"><i class="fas fa-user-slash"></i></div>
            <h2>HIBÁS FELHASZNÁLÓNÉV!</h2>
            <div class="message">A megadott felhasználónév nem létezik.<br>Kérlek ellenőrizd és próbáld újra!</div>
            <a href="login.html" class="btn">VISSZA</a>
        </div>
        </body>
        </html>
        <?php
    }
    $stmt->close();
}
$conn->close();
?>