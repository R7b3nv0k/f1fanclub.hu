<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$DB_HOST = "localhost";
$DB_USER = "swmjndga_swmjndga";
$DB_PASS = "Teszt1234!";
$DB_NAME = "swmjndga_f1adat";

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
$conn->set_charset("utf8mb4"); 

if ($conn->connect_error) { die("Adatbázis hiba: " . $conn->connect_error); }

define('TINIFY_KEY', 'tLwDQHTf6nJrsbFN9Jcvsh9nwlSLh31J');

function getTeamColor($team) {
    switch ($team) {
        case 'Red Bull': return '#1E41FF'; case 'Ferrari': return '#DC0000'; case 'Mercedes': return '#00D2BE';
        case 'McLaren': return '#FF8700'; case 'Aston Martin': return '#006F62'; case 'Alpine': return '#0090FF';
        case 'Williams': return '#00A0DE'; case 'RB': return '#2b2bff'; case 'Audi': return '#e3000f';
        case 'Haas F1 Team': return '#B6BABD'; case 'Cadillac': return '#B6BABD'; default: return '#777777';
    }
}

function compressImageWithTinify($sourcePath, $targetPath) {
    $request = curl_init();
    curl_setopt($request, CURLOPT_URL, "https://api.tinify.com/shrink");
    curl_setopt($request, CURLOPT_USERPWD, "api:" . TINIFY_KEY);
    curl_setopt($request, CURLOPT_POSTFIELDS, file_get_contents($sourcePath));
    curl_setopt($request, CURLOPT_BINARYTRANSFER, true);
    curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($request, CURLOPT_SSL_VERIFYPEER, true);

    $result = curl_exec($request);
    $httpStatus = curl_getinfo($request, CURLINFO_HTTP_CODE);
    curl_close($request);

    if ($httpStatus === 201) {
        $data = json_decode($result);
        $url = $data->output->url;
        $resizeRequest = ["resize" => ["method" => "fit", "width" => 1200, "height" => 1200]];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERPWD, "api:" . TINIFY_KEY);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($resizeRequest));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        
        $finalImage = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($status === 200 && $finalImage) {
            file_put_contents($targetPath, $finalImage);
            return true;
        }
    }
    return false;
}

$isLoggedIn = isset($_SESSION['username']);
$username = $isLoggedIn ? $_SESSION['username'] : null;

$profile_image = null; $fav_team = null; $teamColor = '#ffffff'; $isAdmin = false;

if ($isLoggedIn) {
    $stmt = $conn->prepare("SELECT profile_image, fav_team, role FROM users WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $profile_image = $row['profile_image'] ?? 'default_avatar.png';
    $fav_team = $row['fav_team'] ?? null;
    $teamColor = getTeamColor($fav_team);
    $isAdmin = !empty($row['role']) && $row['role'] === 'admin';
}

if ($isLoggedIn && $isAdmin && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_post_id'])) {
    $delete_id = (int) $_POST['delete_post_id'];
    $stmtImg = $conn->prepare("SELECT image_path FROM news_images WHERE news_id = ?");
    $stmtImg->bind_param("i", $delete_id);
    $stmtImg->execute();
    $resImg = $stmtImg->get_result();
    while ($rowImg = $resImg->fetch_assoc()) {
        $filePath = '../uploads/' . $rowImg['image_path'];
        if (file_exists($filePath)) unlink($filePath);
    }
    $stmtImg->close();
    $conn->query("DELETE FROM news_images WHERE news_id = $delete_id");
    $conn->query("DELETE FROM news_comments WHERE news_id = $delete_id");
    $conn->query("DELETE FROM news_emoji_reactions WHERE news_id = $delete_id");
    $stmtDel = $conn->prepare("DELETE FROM news WHERE id = ?");
    $stmtDel->bind_param("i", $delete_id);
    $stmtDel->execute();
    $stmtDel->close();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

if ($isLoggedIn && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_post_content'])) {
    $content = trim($_POST['new_post_content']);
    $category = $_POST['post_category'] ?? 'Általános';
    $hasFile = !empty($_FILES['post_images']['name'][0]);

    if (!empty($content) || $hasFile) {
        $title = substr($content, 0, 30) . (strlen($content) > 30 ? '...' : '');
        $mainImage = "";

        $stmt = $conn->prepare("INSERT INTO news (title, content, image, author, category, created_at, likes, dislikes) VALUES (?, ?, ?, ?, ?, NOW(), 0, 0)");
        $stmt->bind_param("sssss", $title, $content, $mainImage, $username, $category);

        if ($stmt->execute()) {
            $news_id = $stmt->insert_id;
            $stmt->close();

            if ($hasFile) {
                $uploadDir = '../uploads/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

                $count = 0;
                foreach ($_FILES['post_images']['tmp_name'] as $key => $tmp_name) {
                    if ($count >= 2) break;
                    if ($_FILES['post_images']['error'][$key] === UPLOAD_ERR_OK) {
                        $ext = pathinfo($_FILES['post_images']['name'][$key], PATHINFO_EXTENSION);
                        $newFileName = 'post_' . $news_id . '_' . uniqid() . '.' . $ext;
                        $targetPath = $uploadDir . $newFileName;
                        $compressed = compressImageWithTinify($tmp_name, $targetPath);
                        if (!$compressed) { move_uploaded_file($tmp_name, $targetPath); }

                        $stmtImg = $conn->prepare("INSERT INTO news_images (news_id, image_path) VALUES (?, ?)");
                        $stmtImg->bind_param("is", $news_id, $newFileName);
                        $stmtImg->execute();
                        $stmtImg->close();
                        $count++;
                    }
                }
            }
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }
    }
}

$sql = "
    SELECT 
        n.id, n.content, n.author, n.created_at, n.category,
        (SELECT COUNT(*) FROM news_comments WHERE news_id = n.id) AS comment_count,
        (SELECT COUNT(*) FROM news_emoji_reactions WHERE news_id = n.id) AS total_emojis,
        u.profile_image as author_image,
        u.fav_team as author_team,
        COALESCE(ui.score, 0) as personal_interest
    FROM news n
    LEFT JOIN users u ON n.author = u.username
    LEFT JOIN user_interests ui ON ui.category = n.category AND ui.username = '$username'
    ORDER BY 
        (
            IF(n.created_at >= NOW() - INTERVAL 1 DAY, 100, 0) + 
            ((SELECT COUNT(*) FROM news_emoji_reactions WHERE news_id = n.id) * 2) + 
            (COALESCE(ui.score, 0) * 5)
        ) DESC,
        n.created_at DESC
";
$newsResult = $conn->query($sql);
$available_emojis = ['👍', '👎', '❤️', '🔥', '🏎️', '🏁', '😂', '😢', '🐐', '🤡', '🤯', '👀'];
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Paddock Feed – F1 Fan Club</title>
    <link rel="icon" type="image/svg+xml" href="https://upload.wikimedia.org/wikipedia/commons/3/33/F1.svg">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #0a0a0a; color: white; font-family: 'Poppins', sans-serif; min-height: 100vh; position: relative; overflow-x: hidden; margin: 0; padding: 0; }
        body::before { content: ""; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: radial-gradient(circle at 20% 50%, rgba(225, 6, 0, 0.05) 0%, transparent 50%), radial-gradient(circle at 80% 80%, rgba(225, 6, 0, 0.05) 0%, transparent 50%); pointer-events: none; z-index: -1; }
        .bg-lines { position: fixed; width: 200%; height: 200%; background: repeating-linear-gradient(60deg, rgba(225, 6, 0, 0.03) 0px, rgba(225, 6, 0, 0.03) 2px, transparent 2px, transparent 10px); animation: slide 10s linear infinite; opacity: 0.3; z-index: -1; top: 0; left: 0; }
        @keyframes slide { from { transform: translateX(0); } to { transform: translateX(-200px); } }
        
        header { background-color: #0a0a0a; border-bottom: 2px solid rgba(225, 6, 0, 0.3); padding: 0 40px; height: 80px; display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 1000; box-shadow: 0 4px 20px rgba(0,0,0,0.5); }
        .left-header { display: flex; align-items: center; }
        .logo-title { display: flex; align-items: center; gap: 12px; font-size: 1.5rem; font-weight: 800; color: #fff; text-transform: uppercase; letter-spacing: 1px; }
        .logo-title img { width: 40px; height: auto; filter: brightness(0) invert(1); }
        .logo-title span { display: block; margin-top: 4px; }

        /* Hamburger menu button */
        .hamburger {
            display: none;
            background: none;
            border: none;
            color: white;
            font-size: 28px;
            cursor: pointer;
            padding: 10px;
            z-index: 1001;
            transition: color 0.3s ease;
        }

        .hamburger:hover {
            color: #e10600;
        }

        nav { display: flex; gap: 5px; margin: 0 20px; }
        nav a { font-weight: 600; font-size: 0.9rem; text-transform: uppercase; padding: 8px 16px; border-radius: 4px; color: #ffffff !important; text-decoration: none; position: relative; transition: all 0.2s ease; letter-spacing: 0.5px; opacity: 0.9; }
        nav a:hover { color: #e10600 !important; opacity: 1; background: rgba(225, 6, 0, 0.1); }
        nav a.active { color: #e10600 !important; opacity: 1; font-weight: 700; background: rgba(225, 6, 0, 0.15); }
        nav a[style*="color"] { color: #ffffff !important; }
        nav a[style*="color"]:hover, nav a[style*="color"].active { color: #e10600 !important; }

        /* DROPDOWN MENU STYLES */
        .dropdown-container {
            position: relative;
            display: inline-block;
        }
        
        .welcome {
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .welcome:hover {
            background: rgba(225, 6, 0, 0.15);
            border-color: #e10600;
        }
        
        .dropdown-menu-modern {
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            background: linear-gradient(145deg, #111111, #1a1a1f);
            backdrop-filter: blur(12px);
            border-radius: 16px;
            border: 1px solid rgba(225, 6, 0, 0.4);
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.6);
            min-width: 240px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-8px);
            transition: all 0.2s cubic-bezier(0.2, 0.9, 0.4, 1.1);
            z-index: 1050;
        }
        
        .dropdown-container.open .dropdown-menu-modern {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
        .dropdown-menu-modern a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            color: #eee;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.2s;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        
        .dropdown-menu-modern a:last-child {
            border-bottom: none;
        }
        
        .dropdown-menu-modern a:hover {
            background: rgba(225, 6, 0, 0.2);
            color: white;
            padding-left: 24px;
        }
        
        .dropdown-menu-modern i {
            width: 24px;
            color: #e10600;
            font-size: 1.1rem;
        }
        
        .dropdown-divider {
            height: 1px;
            background: rgba(255, 255, 255, 0.1);
            margin: 6px 0;
        }
        
        .dropdown-arrow-icon {
            margin-left: 6px;
            font-size: 0.7rem;
            transition: transform 0.2s;
            color: #e10600;
        }
        
        .dropdown-container.open .dropdown-arrow-icon {
            transform: rotate(180deg);
        }
        
        .admin-badge {
            position: absolute;
            right: 15px;
            background: #e10600;
            color: white;
            font-size: 0.65rem;
            padding: 2px 8px;
            border-radius: 20px;
            font-weight: 600;
        }

        .auth .btn { display: inline-block; padding: 8px 20px; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; color: #fff; background-color: transparent; border: 1px solid rgba(225, 6, 0, 0.5); border-radius: 30px; cursor: pointer; transition: all 0.3s ease; text-align: center; text-decoration: none; letter-spacing: 0.5px; }
        .auth .btn:hover { background-color: #e10600; border-color: #e10600; transform: translateY(-2px); box-shadow: 0 4px 15px rgba(225, 6, 0, 0.4); color: #fff; }
        .auth .btn:first-child { background-color: rgba(225, 6, 0, 0.15); border-color: #e10600; }
        .auth .btn:first-child:hover { background-color: #e10600; }
        .auth .btn:not(:last-child) { border-color: rgba(255, 255, 255, 0.2); }
        .auth .btn:not(:last-child):hover { border-color: #e10600; background-color: #e10600; }
        .auth .btn:last-child { border-color: rgba(225, 6, 0, 0.5); }
        .auth .btn:last-child:hover { background-color: #e10600; }

        /* Desktop navigation - always visible */
        @media (min-width: 993px) {
            nav {
                display: flex !important;
            }
        }

        /* Mobile navigation - hamburger mode */
        @media (max-width: 992px) {
            .hamburger {
                display: block;
            }
            
            /* Hide the entire logo on mobile */
            .left-header {
                display: none;
            }
            
            nav {
                display: none;
                position: absolute;
                top: 80px;
                left: 0;
                right: 0;
                background: #0a0a0a;
                border-bottom: 2px solid #e10600;
                flex-direction: column;
                gap: 0;
                margin: 0;
                z-index: 1000;
                box-shadow: 0 10px 20px rgba(0,0,0,0.5);
            }
            
            nav.open {
                display: flex;
            }
            
            nav a {
                padding: 15px 20px;
                margin: 0;
                border-bottom: 1px solid rgba(255,255,255,0.1);
                text-align: center;
                font-size: 1rem;
            }
            
            nav a:last-child {
                border-bottom: none;
            }
            
            header {
                position: sticky;
                top: 0;
                flex-wrap: nowrap;
                justify-content: flex-start;
                gap: 15px;
                padding: 0 20px;
            }
            
            .hamburger {
                margin-right: auto;
            }
            
            .auth {
                margin-left: 0;
            }
        }

        @media (max-width: 768px) {
            .auth {
                flex-wrap: wrap;
                justify-content: flex-end;
            }
            .welcome {
                width: 100%;
                justify-content: center;
                margin-right: 0;
                margin-bottom: 5px;
            }
        }
        
        @media (max-width: 576px) {
            .hamburger {
                font-size: 24px;
                padding: 8px;
            }
            header {
                padding: 0 15px;
            }
            .auth .btn {
                padding: 6px 12px;
                font-size: 0.7rem;
            }
            nav a {
                padding: 12px 15px;
                font-size: 0.85rem;
            }
        }

        /* Feed specific styles */
        .feed-container {
            max-width: 750px;
            margin: 30px auto 60px;
            padding: 0 20px;
        }
        
        .create-post-card {
            background: linear-gradient(145deg, rgba(20, 20, 30, 0.95), rgba(10, 10, 15, 0.98));
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 25px;
            border: 1px solid rgba(225, 6, 0, 0.3);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
            display: flex;
            gap: 15px;
        }
        
        .create-post-card:hover {
            border-color: rgba(225, 6, 0, 0.6);
            box-shadow: 0 12px 40px rgba(225, 6, 0, 0.2);
        }
        
        .post-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            flex-shrink: 0;
        }
        
        .post-form { flex: 1; }
        
        .category-select {
            width: 100%;
            background: linear-gradient(135deg, #1a1a2a, #0f0f1a);
            border: 1px solid #e10600;
            color: #ffffff;
            font-size: 0.85rem;
            font-family: 'Poppins', sans-serif;
            border-radius: 40px;
            padding: 10px 16px;
            margin-bottom: 12px;
            cursor: pointer;
            transition: all 0.2s;
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='white'%3e%3cpath d='M7 10l5 5 5-5z'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 16px center;
            background-size: 20px;
        }
        
        .category-select option { background: #1a1a2a; color: white; }
        .category-select:focus { border-color: #e10600; outline: none; box-shadow: 0 0 0 2px rgba(225, 6, 0, 0.4); }
        
        .post-textarea {
            width: 100%;
            background: rgba(0, 0, 0, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: white;
            font-size: 1rem;
            resize: none;
            outline: none;
            min-height: 80px;
            font-family: 'Poppins', sans-serif;
            border-radius: 20px;
            padding: 12px 16px;
            margin-bottom: 12px;
            transition: all 0.2s;
        }
        
        .post-textarea:focus {
            background: rgba(0, 0, 0, 0.7);
            border-color: #e10600;
        }
        
        .post-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 8px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .upload-icon {
            color: #e10600;
            font-size: 1.4rem;
            cursor: pointer;
            padding: 8px;
            border-radius: 50%;
            transition: all 0.2s;
            background: rgba(225, 6, 0, 0.1);
        }
        
        .upload-icon:hover {
            background: rgba(225, 6, 0, 0.25);
            transform: scale(1.1);
        }
        
        .btn-tweet {
            background: #333;
            color: #aaa;
            cursor: not-allowed;
            font-weight: 700;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 3px;
            padding: 12px 28px;
            border: 2px solid #555;
            border-radius: 50px;
            position: relative;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.2, 0.8, 0.2, 1);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            opacity: 0.7;
        }
        
        .btn-tweet.valid {
            background: linear-gradient(145deg, #e10600, #ff4b2b);
            color: white;
            cursor: pointer;
            border: 2px solid #e10600;
            box-shadow: 0 10px 25px rgba(225, 6, 0, 0.4);
            opacity: 1;
        }
        
        .btn-tweet.valid:hover {
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 20px 40px rgba(225, 6, 0, 0.6);
        }
        
        .btn-tweet.valid:active { transform: translateY(0); }
        
        .btn-tweet::before {
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
        
        .btn-tweet.valid:hover::before {
            transform: rotate(45deg) translateY(-100%);
        }
        
        .feed-item {
            background: rgba(15, 15, 20, 0.95);
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .feed-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #e10600, #ff6b4a, #e10600);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }
        
        .feed-item:hover::before { transform: scaleX(1); }
        .feed-item:hover {
            background: rgba(25, 25, 35, 0.98);
            border-color: rgba(225, 6, 0, 0.3);
            transform: translateY(-2px);
        }
        
        .post-header {
            display: flex;
            gap: 12px;
            margin-bottom: 12px;
            align-items: center;
        }
        
        .author-name { font-weight: 700; font-size: 1rem; color: #fff; }
        .post-meta { color: #8a8a8a; font-size: 0.75rem; margin-top: 4px; }
        .post-category-badge {
            background: rgba(225, 6, 0, 0.2);
            color: #e10600;
            padding: 2px 10px;
            border-radius: 40px;
            font-size: 0.7rem;
            font-weight: 600;
            border: 1px solid rgba(225, 6, 0, 0.3);
            margin-left: 8px;
        }
        
        .post-content {
            font-size: 0.95rem;
            line-height: 1.5;
            margin-bottom: 15px;
            color: #e8e8e8;
        }
        
        .post-gallery {
            display: grid;
            gap: 2px;
            border-radius: 12px;
            overflow: hidden;
            margin-top: 12px;
            background: #000;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .post-gallery.one-image { grid-template-columns: 1fr; }
        .post-gallery.two-images { grid-template-columns: 1fr 1fr; }
        
        .gallery-item {
            width: 100%;
            aspect-ratio: 16 / 9;
            background: #050505;
            overflow: hidden;
        }
        
        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            transition: transform 0.3s ease;
        }
        
        .gallery-item img:hover { transform: scale(1.05); }
        
        .reactions-container {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 15px;
            padding-top: 12px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
        }
        
        .reaction-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 40px;
            padding: 5px 12px;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.2s;
            color: #bbb;
        }
        
        .reaction-pill:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-1px);
        }
        
        .reaction-pill.active {
            background: rgba(225, 6, 0, 0.2);
            border-color: #e10600;
            color: #fff;
        }
        
        .emoji-picker-wrapper { position: relative; }
        
        .add-reaction-btn {
            background: rgba(255, 255, 255, 0.05);
            border: 1px dashed rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 1rem;
            color: #888;
        }
        
        .add-reaction-btn:hover {
            background: rgba(225, 6, 0, 0.2);
            border-color: #e10600;
            color: #fff;
            transform: rotate(90deg);
        }
        
        .emoji-picker-popup {
            display: none;
            position: absolute;
            bottom: 120%;
            left: 0;
            background: #1e2126;
            border: 1px solid #e10600;
            border-radius: 12px;
            padding: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.6);
            z-index: 100;
            grid-template-columns: repeat(4, 1fr);
            gap: 5px;
            width: max-content;
        }
        
        .emoji-picker-popup.show { display: grid; }
        
        .emoji-option {
            font-size: 1.3rem;
            cursor: pointer;
            padding: 6px;
            text-align: center;
            border-radius: 8px;
            transition: all 0.2s;
        }
        
        .emoji-option:hover {
            background: rgba(225, 6, 0, 0.2);
            transform: scale(1.1);
        }
        
        .comment-toggle-btn {
            margin-left: auto;
            background: none;
            border: none;
            color: #8a8a8a;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.85rem;
            padding: 6px 12px;
            border-radius: 40px;
            transition: all 0.2s;
        }
        
        .comment-toggle-btn:hover {
            background: rgba(255, 255, 255, 0.05);
            color: #fff;
        }
        
        .comments-wrapper {
            display: none;
            margin-top: 15px;
            background: rgba(0, 0, 0, 0.3);
            border-radius: 16px;
            margin: 15px -20px -20px -20px;
            padding: 15px 20px;
        }
        
        .comment-item {
            display: flex;
            gap: 12px;
            margin-bottom: 15px;
        }
        
        .comment-bubble {
            background: rgba(255, 255, 255, 0.05);
            padding: 10px 15px;
            border-radius: 16px;
            flex: 1;
            font-size: 0.85rem;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        
        .hidden-comment { display: none; }
        
        .load-more-btn {
            width: 100%;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            padding: 10px;
            border-radius: 40px;
            cursor: pointer;
            margin-top: 10px;
            font-weight: 600;
            transition: all 0.2s;
        }
        
        .load-more-btn:hover {
            background: rgba(225, 6, 0, 0.2);
            border-color: #e10600;
        }
        
        .admin-delete-btn {
            background: rgba(225, 6, 0, 0.15);
            border: 1px solid rgba(225, 6, 0, 0.3);
            color: #ff6b4a;
            cursor: pointer;
            padding: 6px 10px;
            border-radius: 40px;
            font-size: 0.9rem;
            transition: all 0.2s;
        }
        
        .admin-delete-btn:hover {
            background: #e10600;
            color: white;
        }
        
        .user-modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.85);
            backdrop-filter: blur(8px);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }
        
        .user-modal-content {
            background: linear-gradient(145deg, #111, #1a1a1a);
            width: 320px;
            border-radius: 24px;
            border: 1px solid #e10600;
            padding: 20px;
            position: relative;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.6);
            animation: popIn 0.3s ease;
            text-align: center;
        }
        
        @keyframes popIn {
            from { transform: scale(0.8); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        
        .user-modal-close {
            position: absolute;
            top: 12px;
            right: 15px;
            background: none;
            border: none;
            color: #888;
            font-size: 1.3rem;
            cursor: pointer;
        }
        
        .user-modal-close:hover { color: #e10600; }
        
        .user-modal-header img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            border: 3px solid #e10600;
            object-fit: cover;
            margin-bottom: 10px;
        }
        
        .modal-role {
            display: inline-block;
            font-size: 0.7rem;
            background: rgba(255, 255, 255, 0.1);
            padding: 2px 10px;
            border-radius: 20px;
            margin-top: 5px;
            color: #aaa;
        }
        
        .user-modal-body {
            margin: 15px 0;
            background: rgba(0, 0, 0, 0.3);
            padding: 12px;
            border-radius: 16px;
            text-align: left;
        }
        
        .user-modal-footer { display: flex; gap: 10px; }
        
        .user-modal-footer button {
            flex: 1;
            padding: 10px;
            border: none;
            border-radius: 40px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }
        
        .btn-add-friend { background: #333; color: white; }
        .btn-add-friend:hover { background: #444; }
        .btn-send-msg { background: #e10600; color: white; }
        .btn-send-msg:hover { background: #b00500; }
        
        .clickable-user {
            cursor: pointer;
            transition: opacity 0.2s;
        }
        
        .clickable-user:hover { opacity: 0.8; }
        
        #preview-container {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
            flex-wrap: wrap;
        }
        
        .preview-thumb {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #e10600;
        }
        
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #1a1a1a; }
        ::-webkit-scrollbar-thumb { background: #e10600; border-radius: 3px; }
        
        @media (max-width: 768px) {
            .feed-container { margin: 20px auto 40px; padding: 0 15px; }
            .create-post-card { padding: 15px; }
            .post-avatar { width: 40px; height: 40px; }
            .btn-tweet { padding: 8px 20px; font-size: 0.75rem; letter-spacing: 2px; }
        }
        /* AUTH / WELCOME SECTION */
.auth {
    display: flex;
    align-items: center;
    gap: 10px;
}

.welcome {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 0.9rem;
    margin-right: 10px;
    padding: 5px 12px;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 30px;
    border: 1px solid rgba(225, 6, 0, 0.2);
    cursor: pointer;
    transition: all 0.2s ease;
}

.welcome:hover {
    background: rgba(225, 6, 0, 0.15);
    border-color: #e10600;
}

.welcome img.avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #e10600;
    transition: transform 0.3s;
}

.welcome img.avatar:hover {
    transform: scale(1.1);
}

.welcome-text {
    color: #ccc;
}

.welcome-text span {
    font-weight: 700;
}
    </style>
</head>

<body>
    <div class="bg-lines"></div>

    <header>
        <div class="left-header">
            <div class="logo-title">
                <img src="https://upload.wikimedia.org/wikipedia/commons/3/33/F1.svg" alt="F1 Logo">
                <span>Fan Club</span>
            </div>
        </div>

        <button class="hamburger" id="hamburgerBtn">
            <i class="fas fa-bars"></i>
        </button>

        <nav id="mainNav">
            <a href="/f1fanclub/index.php">Kezdőlap</a>
            <a href="/f1fanclub/Championship/championship.php">Bajnokság</a>
            <a href="/f1fanclub/teams/teams.php">Csapatok</a>
            <a href="/f1fanclub/drivers/drivers.php">Versenyzők</a>
            <a href="/f1fanclub/news/feed.php" class="active">Paddock</a>
            <a href="/f1fanclub/pitwall/pitwall.php"><i class="fas fa-trophy" style="margin-right: 5px;"></i> A Fal</a>
        </nav>

        <!-- DROPDOWN MENU -->
        <?php if ($isLoggedIn): ?>
            <div class="dropdown-container" id="userDropdownContainer">
                <div class="auth">
                    <div class="welcome" style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                        <?php if ($profile_image): ?>
                            <img src="/f1fanclub/uploads/<?php echo htmlspecialchars($profile_image); ?>" class="avatar"
                                alt="Profilkép" style="width:35px; height:35px; border-radius:50%; object-fit: cover; border-color: <?php echo htmlspecialchars($teamColor); ?>;">
                        <?php endif; ?>
                        <span class="welcome-text">
                            <span style="color: <?php echo htmlspecialchars($teamColor); ?>; font-weight:bold;">
                                <?php echo htmlspecialchars($username); ?>
                            </span>
                        </span>
                        <i class="fas fa-chevron-down dropdown-arrow-icon"></i>
                    </div>
                </div>
                
                <div class="dropdown-menu-modern">
                    <a href="/f1fanclub/profile/profile.php">
                        <i class="fas fa-user-circle"></i> Profilom
                    </a>
                    <a href="/f1fanclub/messages/messages.php">
                        <i class="fas fa-envelope"></i> Üzenetek
                    </a>
                    <?php if ($isAdmin): ?>
                        <a href="/f1fanclub/admin/admin.php" style="position: relative;">
                            <i class="fas fa-shield-alt"></i> Admin Panel
                            <span class="admin-badge">ADMIN</span>
                        </a>
                    <?php endif; ?>
                    <div class="dropdown-divider"></div>
                    <a href="/f1fanclub/logout/logout.php">
                        <i class="fas fa-sign-out-alt"></i> Kijelentkezés
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="auth">
                <a href="/f1fanclub/register/register.html" class="btn">Regisztráció</a>
                <a href="/f1fanclub/login/login.html" class="btn">Bejelentkezés</a>
            </div>
        <?php endif; ?>
    </header>

    <main class="feed-container">

        <?php if ($isLoggedIn): ?>
            <div class="create-post-card">
                <img src="/f1fanclub/uploads/<?= htmlspecialchars($profile_image); ?>" class="post-avatar clickable-user"
                    onclick="openUserProfile('<?= htmlspecialchars(addslashes($username)); ?>')"
                    style="border-color: <?= $teamColor ?>;">
                <div class="post-form">
                    <form action="" method="POST" enctype="multipart/form-data" id="postForm">
                        <select name="post_category" class="category-select" id="postCategory" required>
                            <option value="">-- Válassz kategóriát --</option>
                            <option value="Race Weekend">🏁 Versenyhétvége</option>
                            <option value="Drivers">🏎️ Pilóták</option>
                            <option value="Ferrari">🐎 Ferrari</option>
                            <option value="Red Bull">🐂 Red Bull</option>
                            <option value="Mercedes">⭐ Mercedes</option>
                            <option value="McLaren">🟠 McLaren</option>
                            <option value="Aston Martin">🟢 Aston Martin</option>
                            <option value="Tech">⚙️ Technika & Fejlesztések</option>
                            <option value="Általános">📢 Általános</option>
                        </select>

                        <textarea name="new_post_content" class="post-textarea" id="postContent"
                            placeholder="Mi történik a paddockban, <?= htmlspecialchars($username); ?>?"></textarea>
                        <div id="preview-container"></div>
                        <div class="post-actions">
                            <div>
                                <input type="file" name="post_images[]" id="file-input" multiple accept="image/*" max="2" style="display: none;">
                                <span class="upload-icon" title="Kép feltöltése (Max 2)"
                                    onclick="document.getElementById('file-input').click()"><i class="fas fa-image"></i></span>
                            </div>
                            <button type="submit" class="btn-tweet" id="postButton" disabled><i class="fas fa-feather-alt"></i> Posztolás</button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <div id="feed">
            <?php if ($newsResult && $newsResult->num_rows > 0): ?>
                <?php while ($post = $newsResult->fetch_assoc()):
                    $safeAuthor = htmlspecialchars(addslashes($post['author']));
                    $authorImg = $post['author_image'] ?? 'default.jpg';
                    $authorTeamColor = getTeamColor($post['author_team']);
                    $timeAgo = date('M d, H:i', strtotime($post['created_at']));
                    $category = $post['category'] ?? 'Általános';

                    $imgSql = "SELECT image_path FROM news_images WHERE news_id = " . $post['id'];
                    $imgResult = $conn->query($imgSql);
                    $images = [];
                    while ($imgRow = $imgResult->fetch_assoc()) $images[] = $imgRow['image_path'];

                    $reactSql = "SELECT emoji, COUNT(*) as count, SUM(IF(username = ?, 1, 0)) as user_reacted 
                             FROM news_emoji_reactions WHERE news_id = ? GROUP BY emoji";
                    $reactStmt = $conn->prepare($reactSql);
                    $reactStmt->bind_param("si", $username, $post['id']);
                    $reactStmt->execute();
                    $reactRes = $reactStmt->get_result();
                    $reactions = [];
                    while ($rRow = $reactRes->fetch_assoc()) $reactions[] = $rRow;
                    $reactStmt->close();
                    ?>

                    <article class="feed-item" data-postid="<?= $post['id']; ?>"
                        data-category="<?= htmlspecialchars($category); ?>">

                        <div class="post-header">
                            <img src="/f1fanclub/uploads/<?= htmlspecialchars($authorImg); ?>" class="post-avatar clickable-user"
                                style="border-color: <?= $authorTeamColor ?>;" onclick="openUserProfile('<?= $safeAuthor ?>')">
                            <div style="flex-grow: 1;">
                                <div>
                                    <span class="author-name clickable-user" onclick="openUserProfile('<?= $safeAuthor ?>')"><?= htmlspecialchars($post['author']); ?></span>
                                    <?php if ($post['author_team']): ?>
                                        <span style="color:<?= $authorTeamColor ?>; font-size:0.75rem;">•
                                            <?= htmlspecialchars($post['author_team']); ?></span>
                                    <?php endif; ?>
                                    <?php if ($category !== 'Általános'): ?>
                                        <span class="post-category-badge"><?= htmlspecialchars($category); ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="post-meta"><?= $timeAgo ?></div>
                            </div>

                            <?php if ($isAdmin): ?>
                                <div>
                                    <form method="POST" action=""
                                        onsubmit="return confirm('Biztosan törölni szeretnéd ezt a posztot?');"
                                        onclick="event.stopPropagation();">
                                        <input type="hidden" name="delete_post_id" value="<?= $post['id']; ?>">
                                        <button type="submit" class="admin-delete-btn" title="Poszt törlése"><i class="fas fa-trash-alt"></i></button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="post-content">
                            <?= nl2br(htmlspecialchars($post['content'])); ?>
                        </div>

                        <?php if (count($images) > 0): ?>
                            <div class="post-gallery <?= count($images) == 1 ? 'one-image' : 'two-images' ?>">
                                <?php foreach ($images as $img): ?>
                                    <div class="gallery-item"><img src="/f1fanclub/uploads/<?= htmlspecialchars($img); ?>"
                                            loading="lazy" alt="Poszt kép"></div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <div class="reactions-container" id="reaction-container-<?= $post['id']; ?>"
                            onclick="event.stopPropagation();">

                            <?php foreach ($reactions as $reaction): ?>
                                <div class="reaction-pill <?= $reaction['user_reacted'] ? 'active' : '' ?>"
                                    onclick="toggleEmoji(<?= $post['id']; ?>, '<?= $reaction['emoji']; ?>', '<?= htmlspecialchars($category); ?>')">
                                    <span class="emoji"><?= $reaction['emoji']; ?></span>
                                    <span class="count"><?= $reaction['count']; ?></span>
                                </div>
                            <?php endforeach; ?>

                            <div class="emoji-picker-wrapper">
                                <button class="add-reaction-btn" onclick="toggleEmojiPicker(<?= $post['id']; ?>)"><i class="fas fa-smile"></i></button>
                                <div class="emoji-picker-popup" id="emoji-picker-<?= $post['id']; ?>">
                                    <?php foreach ($available_emojis as $em): ?>
                                        <div class="emoji-option"
                                            onclick="toggleEmoji(<?= $post['id']; ?>, '<?= $em; ?>', '<?= htmlspecialchars($category); ?>')">
                                            <?= $em; ?></div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <button class="comment-toggle-btn" onclick="toggleComments(<?= $post['id']; ?>)">
                                <i class="far fa-comment"></i> <span id="comment-count-display-<?= $post['id']; ?>"><?= $post['comment_count']; ?></span>
                            </button>
                        </div>

                        <div class="comments-wrapper" id="comments-wrapper-<?= $post['id']; ?>"
                            onclick="event.stopPropagation();">
                            <div id="comments-list-<?= $post['id']; ?>"></div>
                            <?php if ($isLoggedIn): ?>
                                <div class="comment-input-group" style="display:flex; gap:10px; margin-top:10px;">
                                    <input type="text" id="comment-input-<?= $post['id']; ?>" class="comment-input"
                                        style="flex:1; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); border-radius:40px; padding:10px 15px; color:white; font-size:0.9rem; outline:none;"
                                        placeholder="Válasz írása...">
                                    <button onclick="postComment(<?= $post['id']; ?>)"
                                        style="background:#e10600; border:none; border-radius:50%; width:40px; height:40px; color:white; cursor:pointer; flex-shrink:0; transition:0.2s;">
                                        <i class="fas fa-paper-plane"></i>
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>

                    </article>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="text-align:center; padding: 60px 20px; background: rgba(255,255,255,0.05); border-radius: 24px;">
                    <i class="fas fa-flag-checkered" style="font-size: 48px; color: #e10600; margin-bottom: 20px; display: block;"></i>
                    <p style="color: #aaa;">Még nincsenek posztok. Légy te az első!</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <div id="userProfileModal" class="user-modal-overlay" onclick="closeUserProfile(event)">
        <div class="user-modal-content" onclick="event.stopPropagation()">
            <button class="user-modal-close" onclick="closeUserProfile(event)">&times;</button>
            <div class="user-modal-header">
                <img id="modalProfileImg" src="" alt="Avatar">
                <h3 id="modalUsername">Felhasználónév</h3>
                <span id="modalRole" class="modal-role">Szerepkör</span>
            </div>
            <div class="user-modal-body">
                <p><i class="fas fa-flag-checkered"></i> <strong>Csapat:</strong> <span id="modalTeam">Csapat</span></p>
                <p><i class="far fa-calendar-alt"></i> <strong>Regisztrált:</strong> <span id="modalRegDate">Dátum</span></p>
            </div>
            <div class="user-modal-footer">
                <button id="modalFriendBtn" class="btn-add-friend" onclick="handleFriendAction()"><i class="fas fa-user-plus"></i> Barátnak jelölés</button>
                <button class="btn-send-msg" onclick="window.location.href='/f1fanclub/messages/messages.php'"><i class="fas fa-comment"></i> Üzenet küldése</button>
            </div>
        </div>
    </div>

    <script>
        // POST BUTTON VALIDATION
        const postButton = document.getElementById('postButton');
        const postCategory = document.getElementById('postCategory');
        const postContent = document.getElementById('postContent');
        
        function validatePostButton() {
            if (postButton) {
                const hasCategory = postCategory && postCategory.value !== '';
                const hasContent = postContent && postContent.value.trim() !== '';
                if (hasCategory && hasContent) {
                    postButton.classList.add('valid');
                    postButton.disabled = false;
                } else {
                    postButton.classList.remove('valid');
                    postButton.disabled = true;
                }
            }
        }
        
        if (postCategory) {
            postCategory.addEventListener('change', validatePostButton);
        }
        if (postContent) {
            postContent.addEventListener('input', validatePostButton);
        }
        
        // HAMBURGER MENU TOGGLE
        const hamburgerBtn = document.getElementById('hamburgerBtn');
        const mainNav = document.getElementById('mainNav');
        
        if (hamburgerBtn && mainNav) {
            hamburgerBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                mainNav.classList.toggle('open');
            });
            
            document.addEventListener('click', function(event) {
                if (mainNav.classList.contains('open') && 
                    !mainNav.contains(event.target) && 
                    !hamburgerBtn.contains(event.target)) {
                    mainNav.classList.remove('open');
                }
            });
            
            const navLinks = mainNav.querySelectorAll('a');
            navLinks.forEach(link => {
                link.addEventListener('click', function() {
                    mainNav.classList.remove('open');
                });
            });
        }
        
        // DROPDOWN MENU TOGGLE
        document.addEventListener('DOMContentLoaded', function() {
            const dropdownContainer = document.getElementById('userDropdownContainer');
            if (dropdownContainer) {
                const welcomeDiv = dropdownContainer.querySelector('.welcome');
                if (welcomeDiv) {
                    welcomeDiv.addEventListener('click', function(e) {
                        e.stopPropagation();
                        dropdownContainer.classList.toggle('open');
                    });
                }
                
                document.addEventListener('click', function(e) {
                    if (!dropdownContainer.contains(e.target)) {
                        dropdownContainer.classList.remove('open');
                    }
                });
            }
        });
        
        // USER PROFILE POP-UP
        let currentModalUser = "";
        let currentFriendStatus = "";

        function openUserProfile(username) {
            fetch('/f1fanclub/profile/user_profile_api.php?username=' + encodeURIComponent(username))
            .then(r => r.json())
            .then(data => {
                if(data.success) {
                    currentModalUser = data.user.username;
                    currentFriendStatus = data.user.friendship_status;

                    document.getElementById('modalProfileImg').src = data.user.profile_image;
                    document.getElementById('modalProfileImg').style.borderColor = data.user.team_color;
                    document.getElementById('modalUsername').innerText = data.user.username;
                    document.getElementById('modalRole').innerText = data.user.role_name;
                    document.getElementById('modalTeam').innerText = data.user.fav_team || 'Nincs megadva';
                    document.getElementById('modalRegDate').innerText = data.user.reg_date;
                    
                    updateFriendButton(data.user.friendship_status);

                    document.getElementById('userProfileModal').style.display = 'flex';
                } else {
                    alert("Hiba: " + data.error);
                }
            }).catch(err => console.error(err));
        }

        function closeUserProfile(e) {
            if(e) e.stopPropagation();
            document.getElementById('userProfileModal').style.display = 'none';
        }

        function updateFriendButton(status) {
            const btn = document.getElementById('modalFriendBtn');
            if(!btn) return;
            btn.style.display = 'flex';
            
            if (status === 'self') {
                btn.style.display = 'none';
            } else if (status === 'none') {
                btn.innerHTML = '<i class="fas fa-user-plus"></i> Barátnak jelölés';
                btn.style.background = '#333';
            } else if (status === 'pending_sent') {
                btn.innerHTML = '<i class="fas fa-clock"></i> Jelölés elküldve';
                btn.style.background = '#888';
            } else if (status === 'pending_received') {
                btn.innerHTML = '<i class="fas fa-check"></i> Jelölés elfogadása';
                btn.style.background = '#28a745';
            } else if (status === 'accepted') {
                btn.innerHTML = '<i class="fas fa-user-minus"></i> Barát törlése';
                btn.style.background = '#e10600';
            }
        }

        function handleFriendAction() {
            let action = '';
            if (currentFriendStatus === 'none') action = 'add';
            else if (currentFriendStatus === 'pending_sent' || currentFriendStatus === 'accepted') action = 'remove';
            else if (currentFriendStatus === 'pending_received') action = 'accept';

            if(!action) return;

            fetch('/f1fanclub/profile/friend_api.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ action: action, target_user: currentModalUser })
            })
            .then(r => r.json())
            .then(data => {
                if(data.success) {
                    openUserProfile(currentModalUser);
                }
            });
        }

        function makeSafeStr(str) {
            return str.replace(/\\/g, '\\\\').replace(/'/g, "\\'");
        }
        
        // INTEREST TRACKING
        let viewTimers = {};
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                const postId = entry.target.dataset.postid;
                const category = entry.target.dataset.category;
                if (entry.isIntersecting) {
                    viewTimers[postId] = setTimeout(() => {
                        trackInterest(category, 'view');
                        observer.unobserve(entry.target);
                    }, 5000);
                } else { clearTimeout(viewTimers[postId]); }
            });
        }, { threshold: 0.6 });

        document.querySelectorAll('.feed-item').forEach(item => observer.observe(item));

        function trackInterest(category, action) {
            if (!category || category === 'Általános') return;
            const fd = new URLSearchParams();
            fd.append("category", category); fd.append("action", action);
            fetch("/f1fanclub/news/track_interest.php", { method: "POST", body: fd });
        }

        // EMOJI SYSTEM
        function toggleEmojiPicker(postId) {
            document.querySelectorAll('.emoji-picker-popup').forEach(p => {
                if (p.id !== 'emoji-picker-' + postId) p.classList.remove('show');
            });
            const picker = document.getElementById('emoji-picker-' + postId);
            picker.classList.toggle('show');
        }

        document.addEventListener('click', function (e) {
            if (!e.target.closest('.emoji-picker-wrapper')) {
                document.querySelectorAll('.emoji-picker-popup.show').forEach(p => p.classList.remove('show'));
            }
        });

        function toggleEmoji(postId, emoji, category) {
            document.getElementById('emoji-picker-' + postId).classList.remove('show');
            trackInterest(category, 'like');

            const fd = new URLSearchParams();
            fd.append("news_id", postId);
            fd.append("emoji", emoji);

            fetch("/f1fanclub/news/react_emoji.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: fd.toString()
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) { renderReactions(postId, data.reactions, category); } 
                else { if (!<?= $isLoggedIn ? 'true' : 'false' ?>) alert("Jelentkezz be a reakciókhoz!"); }
            });
        }

        function renderReactions(postId, reactions, category) {
            const container = document.getElementById('reaction-container-' + postId);
            const pickerWrapper = container.querySelector('.emoji-picker-wrapper').outerHTML;
            const commentBtn = container.querySelector('.comment-toggle-btn').outerHTML;

            let html = '';
            reactions.forEach(r => {
                const activeClass = r.user_reacted ? 'active' : '';
                html += `<div class="reaction-pill ${activeClass}" onclick="toggleEmoji(${postId}, '${r.emoji}', '${category}')">
                    <span class="emoji">${r.emoji}</span>
                    <span class="count">${r.count}</span>
                 </div>`;
            });
            container.innerHTML = html + pickerWrapper + commentBtn;
        }

        const fileInput = document.getElementById('file-input');
        const previewContainer = document.getElementById('preview-container');
        if (fileInput) {
            fileInput.addEventListener('change', function () {
                previewContainer.innerHTML = '';
                if (this.files.length > 2) { alert("Maximum 2 képet tölthetsz fel!"); this.value = ""; return; }
                Array.from(this.files).forEach(file => {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        const img = document.createElement('img'); img.src = e.target.result;
                        img.classList.add('preview-thumb'); previewContainer.appendChild(img);
                    }
                    reader.readAsDataURL(file);
                });
            });
        }

        // COMMENTS
        function toggleComments(postId) {
            const wrapper = document.getElementById(`comments-wrapper-${postId}`);
            if (wrapper.style.display === "none" || wrapper.style.display === "") {
                wrapper.style.display = "block"; loadComments(postId);
            } else { wrapper.style.display = "none"; }
        }

        function loadComments(postId) {
            const listDiv = document.getElementById(`comments-list-${postId}`);
            listDiv.innerHTML = '<div style="color:#777; font-size:0.8rem;">Betöltés...</div>';
            fetch(`/f1fanclub/news/comment_api.php?news_id=${postId}`)
                .then(r => r.json()).then(data => {
                    if (!data.success) { listDiv.innerHTML = '<div style="color:red;">Hiba.</div>'; return; }
                    if (data.comments.length === 0) { listDiv.innerHTML = '<div style="font-size:0.8rem; color:#ccc; font-style:italic; padding:5px;">Nincs még komment.</div>'; return; }
                    let html = '';
                    data.comments.forEach((c, index) => {
                        const avatar = c.profile_image ? `/f1fanclub/uploads/${c.profile_image}` : 'https://via.placeholder.com/40';
                        const hiddenClass = index >= 3 ? 'hidden-comment' : '';
                        html += `
                        <div class="comment-item ${hiddenClass} comment-item-${postId}">
                            <img src="${avatar}" class="clickable-user" onclick="openUserProfile('${makeSafeStr(c.username)}')" style="width:35px; height:35px; border-radius:50%; border: 2px solid ${c.team_color}; object-fit: cover;">
                            <div class="comment-bubble">
                                <div style="font-weight:bold; font-size:0.85rem; color:${c.team_color}" class="clickable-user" onclick="openUserProfile('${makeSafeStr(c.username)}')">${c.username}</div>
                                <div style="font-size:0.9rem; margin-top:2px; color:#eee;">${escapeHtml(c.comment)}</div>
                                <div style="font-size:0.7rem; color:#888; margin-top:5px;">${c.date_formatted}</div>
                            </div>
                        </div>`;
                    });
                    if (data.comments.length > 3) {
                        const remaining = data.comments.length - 3;
                        html += `<button class="load-more-btn" id="load-more-${postId}" onclick="showAllComments(${postId})">További ${remaining} komment megtekintése 👇</button>`;
                    }
                    listDiv.innerHTML = html;
                });
        }

        function showAllComments(postId) {
            document.querySelectorAll(`.comment-item-${postId}.hidden-comment`).forEach(el => el.classList.remove('hidden-comment'));
            document.getElementById(`load-more-${postId}`).style.display = 'none';
        }

        function postComment(postId) {
            const input = document.getElementById(`comment-input-${postId}`);
            const text = input.value.trim();
            if (!text) return;
            const formData = new URLSearchParams();
            formData.append("news_id", postId); formData.append("comment", text);
            fetch("/f1fanclub/news/comment_api.php", {
                method: "POST", headers: { "Content-Type": "application/x-www-form-urlencoded" }, body: formData.toString()
            }).then(r => r.json()).then(data => {
                if (data.success) {
                    input.value = ""; loadComments(postId);
                    const countSpan = document.getElementById(`comment-count-display-${postId}`);
                    if (countSpan) countSpan.innerText = parseInt(countSpan.innerText) + 1;
                }
            });
        }

        function escapeHtml(text) { return text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;"); }
    </script>
</body>
</html>