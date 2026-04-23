<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$DB_HOST = "localhost";
$DB_USER = "swmjndga_swmjndga";
$DB_PASS = "Teszt1234!";
$DB_NAME = "swmjndga_f1adat";

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) {
    die("Adatbázis hiba: " . $conn->connect_error);
}

$message = "";
$messageType = "error";

if (isset($_GET['token'])) {
    $token = trim($_GET['token']);
    
    $stmt = $conn->prepare("SELECT id, username FROM users WHERE verification_token = ? AND is_verified = 0");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $userId = $user['id'];
        $username = $user['username'];

        $stmt_update = $conn->prepare("UPDATE users SET is_verified = 1, verification_token = NULL WHERE id = ?");
        $stmt_update->bind_param("i", $userId);
        
        if ($stmt_update->execute()) {
            $message = "Szia <strong>$username</strong>!<br>A fiókodat sikeresen aktiváltuk!";
            $messageType = "success";
        } else {
            $message = "Hiba történt az aktiválás közben: " . $conn->error;
        }
        $stmt_update->close();
        
    } else {
        $message = "Ez az aktiváló link érvénytelen, vagy a fiókodat már korábban aktiváltad.";
    }
    $stmt->close();
} else {
    $message = "Hiányzó aktiváló kód!";
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
<<<<<<< HEAD
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes, viewport-fit=cover">
    <title>Fiók Aktiválás – F1 Fan Club</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="icon" type="image/svg+xml" href="https://upload.wikimedia.org/wikipedia/commons/3/33/F1.svg">
    
=======
    <title>Fiók Aktiválás – F1 Fan Club</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="icon" type="image/svg+xml" href="https://upload.wikimedia.org/wikipedia/commons/3/33/F1.svg">
>>>>>>> f81424192996985be2da559c0f9a2c1f13f5eb7f
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

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

<<<<<<< HEAD
=======
        /* F1-themed background with gradient */
>>>>>>> f81424192996985be2da559c0f9a2c1f13f5eb7f
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

<<<<<<< HEAD
=======
        /* Speed lines effect */
>>>>>>> f81424192996985be2da559c0f9a2c1f13f5eb7f
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
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.8),
                        0 0 50px rgba(225, 6, 0, 0.2);
            position: relative;
            overflow: hidden;
            z-index: 1;
            animation: slideInUp 0.8s ease-out;
            text-align: center;
        }

<<<<<<< HEAD
=======
        /* F1-inspired accent line */
>>>>>>> f81424192996985be2da559c0f9a2c1f13f5eb7f
        .card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg,
                transparent 0%,
                #e10600 20%,
                #e10600 80%,
                transparent 100%);
            z-index: 2;
        }

<<<<<<< HEAD
=======
        /* Glowing effect */
>>>>>>> f81424192996985be2da559c0f9a2c1f13f5eb7f
        .card::after {
            content: "";
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(45deg,
                transparent 30%,
                rgba(225, 6, 0, 0.15) 50%,
                transparent 70%);
            border-radius: 32px;
            z-index: -1;
            animation: borderGlow 4s infinite;
        }

        @keyframes borderGlow {
            0%, 100% { opacity: 0.3; }
            50% { opacity: 0.8; }
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
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
            position: relative;
            z-index: 2;
<<<<<<< HEAD
            flex-wrap: wrap;
            word-break: break-word;
=======
>>>>>>> f81424192996985be2da559c0f9a2c1f13f5eb7f
        }

        .logo-title img {
            width: 50px;
            filter: drop-shadow(0 0 10px currentColor);
            transition: transform 0.3s ease;
        }

        .logo-title:hover img {
            transform: rotate(10deg) scale(1.1);
        }

        .success-icon, .error-icon {
            font-size: 60px;
            margin-bottom: 20px;
            display: block;
        }

        .success-icon {
            color: #28a745;
            filter: drop-shadow(0 0 15px rgba(40, 167, 69, 0.5));
        }

        .error-icon {
            color: #e10600;
            filter: drop-shadow(0 0 15px rgba(225, 6, 0, 0.5));
        }

        h2 {
            margin: 0 0 15px;
            font-weight: 800;
            font-size: 2rem;
            text-transform: uppercase;
            letter-spacing: 3px;
            position: relative;
            display: inline-block;
<<<<<<< HEAD
            word-break: break-word;
=======
>>>>>>> f81424192996985be2da559c0f9a2c1f13f5eb7f
        }

        h2.success {
            color: #28a745;
        }

        h2.error {
            color: #e10600;
        }

        h2::after {
            content: "";
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg,
                transparent 0%,
                currentColor 20%,
                currentColor 80%,
                transparent 100%);
            border-radius: 2px;
        }

        .message {
            margin: 20px 0;
            font-size: 1rem;
            line-height: 1.6;
            color: #ddd;
<<<<<<< HEAD
            word-break: break-word;
=======
>>>>>>> f81424192996985be2da559c0f9a2c1f13f5eb7f
        }

        .message strong {
            color: #fff;
        }

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
<<<<<<< HEAD
            position: relative;
            overflow: hidden;
=======
>>>>>>> f81424192996985be2da559c0f9a2c1f13f5eb7f
        }

        .btn:hover {
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 20px 40px rgba(225, 6, 0, 0.4);
            background: linear-gradient(145deg, #ff1a1a, #e10600);
        }

        .btn:active {
            transform: translateY(0);
        }

<<<<<<< HEAD
=======
        .btn-secondary {
            background: #333;
            border-color: #555;
            box-shadow: none;
        }

        .btn-secondary:hover {
            background: #444;
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
        }

        /* Speed lines effect on button */
        .btn {
            position: relative;
            overflow: hidden;
        }

>>>>>>> f81424192996985be2da559c0f9a2c1f13f5eb7f
        .btn::before {
            content: "";
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg,
                transparent 30%,
                rgba(255, 255, 255, 0.2) 50%,
                transparent 70%);
            transform: rotate(45deg) translateY(100%);
            transition: transform 0.8s ease;
            z-index: 2;
            pointer-events: none;
        }

        .btn:hover::before {
            transform: rotate(45deg) translateY(-100%);
        }

<<<<<<< HEAD
        .btn-secondary {
            background: #333;
            border-color: #555;
            box-shadow: none;
        }

        .btn-secondary:hover {
            background: #444;
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
        }

        @media (max-width: 600px) {
            body {
                padding: 12px;
                align-items: flex-start;
                padding-top: 30px;
            }
            
            .card {
                padding: 40px 25px;
                max-width: 100%;
            }
            
            .logo-title {
                font-size: 32px;
                gap: 12px;
            }
            
            .logo-title img {
                width: 55px;
            }
            
            .success-icon, .error-icon {
                font-size: 70px;
                margin-bottom: 25px;
            }
            
            h2 {
                font-size: 2rem;
                margin-bottom: 20px;
            }
            
            .message {
                font-size: 1.1rem;
                margin: 25px 0;
            }
            
            .btn {
                padding: 18px 40px;
                font-size: 1.1rem;
                width: 100%;
            }
        }
        
        @media (max-width: 400px) {
            .card {
                padding: 30px 18px;
            }
            
            .logo-title {
                font-size: 28px;
            }
            
            .logo-title img {
                width: 45px;
            }
            
            h2 {
                font-size: 1.8rem;
            }
            
            .message {
                font-size: 1rem;
            }
            
            .btn {
                padding: 16px 30px;
                font-size: 1rem;
            }
        }
        
        @media (max-width: 350px) {
            h2 {
                font-size: 1.6rem;
            }
            
            .btn {
                font-size: 0.95rem;
            }
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
        @media (max-width: 500px) {
            .card {
                padding: 35px 25px;
            }
            .logo-title {
                font-size: 26px;
            }
            .logo-title img {
                width: 40px;
            }
            h2 {
                font-size: 1.6rem;
            }
            .btn {
                padding: 12px 24px;
                font-size: 0.9rem;
            }
        }
>>>>>>> f81424192996985be2da559c0f9a2c1f13f5eb7f
    </style>
</head>
<body>

<div class="bg-lines"></div>

<div class="card">
    <div class="logo-title">
        <img src="https://upload.wikimedia.org/wikipedia/commons/3/33/F1.svg" alt="F1 Logo">
        <span>Fan Club</span>
    </div>

    <?php if ($messageType == 'success'): ?>
        <div class="success-icon">
            <i class="fas fa-check-circle" style="font-size: 60px;"></i>
        </div>
        <h2 class="success">Siker!</h2>
        <div class="message"><?php echo $message; ?></div>
        <a href="/f1fanclub/login/login.html" class="btn"><i class="fas fa-sign-in-alt" style="margin-right: 8px;"></i> Bejelentkezés</a>
    <?php else: ?>
        <div class="error-icon">
            <i class="fas fa-exclamation-triangle" style="font-size: 60px;"></i>
        </div>
        <h2 class="error">Hiba</h2>
        <div class="message"><?php echo $message; ?></div>
        <a href="/f1fanclub/index.php" class="btn btn-secondary"><i class="fas fa-home" style="margin-right: 8px;"></i> Főoldal</a>
    <?php endif; ?>
</div>

<<<<<<< HEAD
=======
<!-- Font Awesome for icons -->
>>>>>>> f81424192996985be2da559c0f9a2c1f13f5eb7f
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

</body>
</html>