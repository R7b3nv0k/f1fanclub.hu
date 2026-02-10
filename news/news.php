<?php
session_start();

/* ==== DEBUG ==== */
error_reporting(E_ALL);
ini_set('display_errors', 1);

/* ==== ADATBÁZIS KAPCSOLAT ==== */
$DB_HOST = "localhost";
$DB_USER = "swmjndga_swmjndga";
$DB_PASS = "Teszt1234!";
$DB_NAME = "swmjndga_f1adat";

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) {
    die("Adatbázis hiba: " . $conn->connect_error);
}

/* ==== TINIFY API KONFIG ==== */
define('TINIFY_KEY', 'tLwDQHTf6nJrsbFN9Jcvsh9nwlSLh31J');

/* ==== SEGÉDFÜGGVÉNYEK ==== */
function getTeamColor($team) {
    switch ($team) {
        case 'Red Bull':      return '#1E41FF';
        case 'Ferrari':       return '#DC0000';
        case 'Mercedes':      return '#00D2BE';
        case 'McLaren':       return '#FF8700';
        case 'Aston Martin':  return '#006F62';
        case 'Alpine':        return '#0090FF';
        case 'Williams':      return '#00A0DE';
        case 'RB':            return '#2b2bff';
        case 'Kick Sauber':   return '#52E252';
        case 'Haas F1 Team':  return '#B6BABD';
        default:              return '#777777'; 
    }
}

// Tinify tömörítés és átméretezés cURL segítségével
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

        $resizeRequest = [
            "resize" => [
                "method" => "fit",
                "width" => 1200,
                "height" => 1200
            ]
        ];

        $finalImage = file_get_contents($url, false, stream_context_create([
            "http" => [
                "method" => "POST",
                "header" => "Content-type: application/json\r\n",
                "content" => json_encode($resizeRequest)
            ]
        ]));

        if ($finalImage) {
            file_put_contents($targetPath, $finalImage);
            return true;
        }
    }
    return false; 
}

/* ==== LOGIN ADATOK ==== */
$isLoggedIn = isset($_SESSION['username']);
$username   = $isLoggedIn ? $_SESSION['username'] : null;

$profile_image = null;
$fav_team      = null;
$teamColor     = '#ffffff';
$isAdmin       = false;

/* ==== FELHASZNÁLÓ ADATOK ==== */
if ($isLoggedIn) {
    $stmt = $conn->prepare("SELECT profile_image, fav_team, role FROM users WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $profile_image = $row['profile_image'] ?? 'default_avatar.png'; 
    $fav_team      = $row['fav_team'] ?? null;
    $teamColor     = getTeamColor($fav_team);
    $isAdmin       = !empty($row['role']) && $row['role'] === 'admin';
}

/* ==== ÚJ POSZT BEKÜLDÉSE (Backend) ==== */
if ($isLoggedIn && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_post_content'])) {
    $content = trim($_POST['new_post_content']);
    $hasFile = !empty($_FILES['post_images']['name'][0]);

    if (!empty($content) || $hasFile) {
        $title = substr($content, 0, 30) . (strlen($content) > 30 ? '...' : '');
        $mainImage = ""; 

        // 1. Poszt létrehozása
        $stmt = $conn->prepare("INSERT INTO news (title, content, image, author, created_at, likes, dislikes) VALUES (?, ?, ?, ?, NOW(), 0, 0)");
        $stmt->bind_param("ssss", $title, $content, $mainImage, $username);
        
        if ($stmt->execute()) {
            $news_id = $stmt->insert_id; 
            $stmt->close();

            // 2. Képek kezelése
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
                        
                        if (!$compressed) {
                            move_uploaded_file($tmp_name, $targetPath);
                        }

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

/* ==== POSZTOK LEKÉRDEZÉSE ==== */
$sql = "
    SELECT 
        n.id, n.content, n.author, n.created_at,
        n.likes, n.dislikes,
        (SELECT COUNT(*) FROM news_comments WHERE news_id = n.id) AS comment_count,
        u.profile_image as author_image,
        u.fav_team as author_team
    FROM news n
    LEFT JOIN users u ON n.author = u.username
    ORDER BY n.created_at DESC
";
$newsResult = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="hu">
<head>
<meta charset="UTF-8">
<title>Paddock Feed – F1 Fan Club</title>
<link rel="stylesheet" href="/f1fanclub/css/style.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800&display=swap" rel="stylesheet">
<style>
    /* === FEED SPECIFIKUS STÍLUSOK === */
    :root {
        --card-bg: rgba(20, 20, 20, 0.85); 
        --card-bg-hover: rgba(30, 30, 30, 0.9);
        --input-bg: rgba(0, 0, 0, 0.5);
        --text-main: #ffffff;
        --text-muted: #8899a6;
        --accent: #e10600;
        --border: rgba(255, 255, 255, 0.1);
    }

    .feed-container {
        max-width: 750px; 
        margin: 40px auto; 
        padding: 0 15px;
        text-align: left; 
    }

    /* --- KÁRTYA STÍLUSOK --- */
    .create-post-card, .feed-item {
        background: var(--card-bg);
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 25px;
        border: 1px solid var(--border);
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.5); 
        backdrop-filter: blur(10px); 
        -webkit-backdrop-filter: blur(10px);
        transition: background 0.3s, border-color 0.3s, transform 0.2s;
    }

    .create-post-card {
        display: flex;
        gap: 15px;
        position: relative;
    }

    .feed-item:hover { 
        background: var(--card-bg-hover); 
        border-color: rgba(225, 6, 0, 0.4); 
    }

    .post-avatar {
        width: 50px; height: 50px; border-radius: 50%; object-fit: cover;
        border: 2px solid transparent;
        flex-shrink: 0;
        box-shadow: 0 4px 8px rgba(0,0,0,0.4);
    }

    .post-form { width: 100%; }

    .post-textarea {
        width: 100%;
        background: var(--input-bg);
        border: 1px solid var(--border);
        color: white;
        font-size: 1.1rem;
        resize: none;
        outline: none;
        min-height: 80px;
        font-family: inherit;
        border-radius: 8px;
        padding: 12px;
        margin-bottom: 12px;
        transition: 0.2s;
        box-sizing: border-box;
    }
    .post-textarea:focus { 
        background: rgba(0,0,0,0.8); 
        border-color: var(--accent); 
        box-shadow: 0 0 10px rgba(225, 6, 0, 0.2);
    }

    .post-actions { 
        display: flex; justify-content: space-between; align-items: center; 
        padding-top: 5px; border-top: 1px solid var(--border); margin-top: 5px;
    }

    .upload-icon {
        color: var(--accent); font-size: 1.4rem; cursor: pointer;
        padding: 8px; border-radius: 50%; transition: 0.2s; position: relative;
    }
    .upload-icon:hover { 
        background: rgba(225, 6, 0, 0.15); 
        transform: scale(1.1);
    }
    
    #file-input { display: none; }
    
    #preview-container {
        display: flex; gap: 10px; margin-bottom: 10px;
    }
    .preview-thumb {
        width: 60px; height: 60px; object-fit: cover; border-radius: 6px;
        border: 1px solid var(--border);
    }

    .btn-tweet {
        background: linear-gradient(135deg, #e10600, #ff4b2b);
        color: white; 
        border: none;
        padding: 10px 24px; 
        border-radius: 99px; 
        font-weight: 700;
        cursor: pointer; 
        transition: all 0.2s; 
        box-shadow: 0 4px 15px rgba(225, 6, 0, 0.4);
    }
    .btn-tweet:hover { 
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(225, 6, 0, 0.6);
        filter: brightness(1.1);
    }

    /* --- POSZT TARTALOM --- */
    .post-header { display: flex; gap: 12px; margin-bottom: 12px; align-items: center; }
    .author-name { font-weight: 700; font-size: 1.05rem; color: #fff; text-shadow: 0 2px 4px rgba(0,0,0,0.5); }
    .post-meta { color: var(--text-muted); font-size: 0.85rem; }
    
    .post-content { font-size: 1rem; line-height: 1.6; margin-bottom: 15px; white-space: pre-wrap; color: #eee; }

    /* Galéria */
    .post-gallery {
        display: grid;
        gap: 2px;
        border-radius: 12px;
        overflow: hidden;
        margin-top: 10px;
        background: #000; 
        border: 1px solid var(--border);
    }
    .post-gallery.one-image { grid-template-columns: 1fr; }
    .post-gallery.two-images { grid-template-columns: 1fr 1fr; }

    .gallery-item {
        width: 100%;
        aspect-ratio: 16 / 9; 
        background: #050505;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .gallery-item img {
        width: 100%;
        height: 100%;
        object-fit: contain; 
    }

    /* Interakciók */
    .interaction-bar {
        display: flex; justify-content: space-between; margin-top: 15px;
        padding-top: 12px; border-top: 1px solid var(--border); color: var(--text-muted);
    }
    .action-btn {
        background: none; border: none; color: var(--text-muted);
        cursor: pointer; display: flex; align-items: center; gap: 6px;
        font-size: 0.9rem; transition: 0.2s; padding: 6px 12px; border-radius: 20px;
    }
    .action-btn:hover { 
        color: var(--accent); 
        background: rgba(255, 255, 255, 0.05); 
        text-shadow: 0 0 8px rgba(225,6,0,0.5);
    }

    /* Kommentek */
    .comments-wrapper {
        display: none; margin-top: 15px; border-top: 1px solid var(--border);
        padding-top: 15px; background: rgba(0,0,0,0.3); padding: 15px;
        border-radius: 0 0 12px 12px; margin: 15px -20px -20px -20px;
    }
    .comment-item { display: flex; gap: 10px; margin-bottom: 15px; }
    .comment-bubble {
        background: rgba(255,255,255,0.05); padding: 10px 15px; border-radius: 12px;
        flex: 1; font-size: 0.9rem; border: 1px solid rgba(255,255,255,0.05);
    }
    .hidden-comment { display: none; }
    .load-more-btn {
        width: 100%; background: rgba(255,255,255,0.1); border: none; color: white;
        padding: 10px; border-radius: 8px; cursor: pointer; margin-top: 5px; font-weight: 600;
        transition: 0.2s;
    }
    .load-more-btn:hover { background: rgba(255,255,255,0.2); }

    /* === LÁBLÉC STÍLUSOK === */
    .site-footer {
        background-color: #111;
        color: #eee;
        padding: 40px 20px;
        margin-top: 60px;
        border-top: 2px solid #e10600;
        font-size: 0.9rem;
    }
    
    .footer-container {
        max-width: 1100px;
        margin: 0 auto;
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        gap: 30px;
    }

    .footer-section {
        flex: 1;
        min-width: 250px;
    }

    .footer-section h3 {
        color: #e10600;
        font-size: 1.1rem;
        margin-bottom: 15px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .footer-section p, .footer-section a {
        color: #bbb;
        line-height: 1.6;
        text-decoration: none;
        display: block;
        margin-bottom: 8px;
        transition: color 0.2s;
    }

    .footer-section a:hover {
        color: #fff;
        text-shadow: 0 0 5px rgba(225, 6, 0, 0.5);
    }

    .footer-logo {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 15px;
        font-weight: 800;
        font-size: 1.4rem;
        color: white;
    }

    .social-links {
        display: flex;
        gap: 15px;
        margin-top: 10px;
    }
    
    .social-icon {
        width: 24px;
        height: 24px;
        fill: #bbb;
        transition: fill 0.2s;
    }
    
    .social-icon:hover {
        fill: #e10600;
    }

    .copyright {
        text-align: center;
        margin-top: 40px;
        padding-top: 20px;
        border-top: 1px solid #333;
        color: #666;
        font-size: 0.8rem;
    }
</style>
</head>
<body>

<header>
  <div class="left-header">
    <h1 class="logo-title">
      <img src="https://upload.wikimedia.org/wikipedia/commons/3/33/F1.svg" class="f1-logo" alt="F1 Logo" style="height: 40px; vertical-align: middle;">
      <span>Paddock</span>
    </h1>
  </div>

  <nav style="margin: 20px 0;">
    <a href="/f1fanclub/index.php" style="color:white; margin:0 10px;">Home</a>
    <a href="/f1fanclub/Championship/championship.php" style="color:white; margin:0 10px;">Championship</a>
    <a href="/f1fanclub/teams/teams.php" style="color:white; margin:0 10px;">Teams</a>
    <a href="/f1fanclub/drivers/drivers.php" style="color:white; margin:0 10px;">Drivers</a>
    <a href="/f1fanclub/news/news.php" style="color:#e10600; margin:0 10px; font-weight:bold;">Paddock</a>
  </nav>

  <?php if ($isLoggedIn): ?>
    <div class="auth">
      <div class="welcome" style="display: flex; align-items: center; gap: 10px;">
        <?php if ($profile_image): ?>
          <img src="/f1fanclub/uploads/<?php echo htmlspecialchars($profile_image); ?>" class="avatar" alt="Profile" style="width:35px; height:35px; border-radius:50%; object-fit: cover; border-color: <?php echo htmlspecialchars($teamColor); ?>;">
        <?php endif; ?>
        <span class="welcome-text">
          Hello,
          <span style="color: <?php echo htmlspecialchars($teamColor); ?>; font-weight:bold;">
            <?php echo htmlspecialchars($username); ?>
          </span>!
        </span>
      </div>
      <div style="display:flex; gap: 8px;">
         <a href="/f1fanclub/logout/logout.php" class="btn">Log out</a>
         <a href="/f1fanclub/profile/profile.php" class="btn">Profile</a>
      </div>
    </div>
  <?php else: ?>
    <div class="auth">
      <a href="/f1fanclub/register/register.html" class="btn">Register</a>
      <a href="/f1fanclub/login/login.html" class="btn">Login</a>
    </div>
  <?php endif; ?>
</header>

<main class="feed-container">

    <?php if ($isLoggedIn): ?>
    <div class="create-post-card">
        <img src="/f1fanclub/uploads/<?= htmlspecialchars($profile_image); ?>" class="post-avatar" style="border-color: <?= $teamColor ?>;">
        <div class="post-form">
            <form action="" method="POST" enctype="multipart/form-data">
                <textarea name="new_post_content" class="post-textarea" placeholder="Mi történik a pályán, <?= htmlspecialchars($username); ?>?"></textarea>
                
                <div id="preview-container"></div>

                <div class="post-actions">
                    <input type="file" name="post_images[]" id="file-input" multiple accept="image/*" max="2">
                    
                    <span class="upload-icon" title="Kép feltöltése (Max 2)" onclick="document.getElementById('file-input').click()">📷</span> 
                    
                    <button type="submit" class="btn-tweet">Post</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <div id="feed">
        <?php if ($newsResult && $newsResult->num_rows > 0): ?>
            <?php while ($post = $newsResult->fetch_assoc()): 
                $authorImg = $post['author_image'] ?? 'default.jpg';
                $authorTeamColor = getTeamColor($post['author_team']);
                $timeAgo = date('M d, H:i', strtotime($post['created_at']));

                $imgSql = "SELECT image_path FROM news_images WHERE news_id = " . $post['id'];
                $imgResult = $conn->query($imgSql);
                $images = [];
                while($imgRow = $imgResult->fetch_assoc()) {
                    $images[] = $imgRow['image_path'];
                }
            ?>
            
            <article class="feed-item" id="post-<?= $post['id']; ?>">
                
                <div class="post-header">
                    <img src="/f1fanclub/uploads/<?= htmlspecialchars($authorImg); ?>" class="post-avatar" style="border-color: <?= $authorTeamColor ?>;">
                    <div>
                        <div>
                            <span class="author-name"><?= htmlspecialchars($post['author']); ?></span>
                            <?php if($post['author_team']): ?>
                                <span style="color:<?= $authorTeamColor ?>; font-size:0.8rem;">• <?= htmlspecialchars($post['author_team']); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="post-meta"><?= $timeAgo ?></div>
                    </div>
                </div>

                <div class="post-content">
                    <?= nl2br(htmlspecialchars($post['content'])); ?>
                </div>
                
                <?php if (count($images) > 0): ?>
                    <div class="post-gallery <?= count($images) == 1 ? 'one-image' : 'two-images' ?>">
                        <?php foreach ($images as $img): ?>
                            <div class="gallery-item">
                                <img src="/f1fanclub/uploads/<?= htmlspecialchars($img); ?>" loading="lazy" alt="Post image">
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="interaction-bar" onclick="event.stopPropagation();">
                    <button class="action-btn like-btn" data-id="<?= $post['id']; ?>" data-type="like">
                        🏁 <span id="like-count-<?= $post['id']; ?>"><?= $post['likes']; ?></span>
                    </button>
                    
                    <button class="action-btn dislike-btn" data-id="<?= $post['id']; ?>" data-type="dislike">
                        🟥 <span id="dislike-count-<?= $post['id']; ?>"><?= $post['dislikes']; ?></span>
                    </button>

                    <button class="action-btn comment-toggle-btn" onclick="toggleComments(<?= $post['id']; ?>)">
                        💬 <span id="comment-count-display-<?= $post['id']; ?>"><?= $post['comment_count']; ?></span>
                    </button>
                </div>

                <div class="comments-wrapper" id="comments-wrapper-<?= $post['id']; ?>" onclick="event.stopPropagation();">
                    <div id="comments-list-<?= $post['id']; ?>"></div>
                    <?php if ($isLoggedIn): ?>
                        <div style="display:flex; gap:10px; margin-top:10px;">
                            <input type="text" id="comment-input-<?= $post['id']; ?>" class="post-textarea" style="min-height:40px; margin-bottom:0; border-radius:20px; padding:10px 15px; font-size:0.9rem;" placeholder="Szólj hozzá...">
                            <button onclick="postComment(<?= $post['id']; ?>)" style="background:var(--accent); border:none; border-radius:50%; width:40px; height:40px; color:white; cursor:pointer; flex-shrink:0;">➤</button>
                        </div>
                    <?php endif; ?>
                </div>

            </article>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="text-align:center; color:#999; padding: 20px;">Még nincsenek posztok. Légy te az első!</p>
        <?php endif; ?>
    </div>

</main>

<footer class="site-footer">
    <div class="footer-container">
        <div class="footer-section">
            <div class="footer-logo">
                <img src="https://upload.wikimedia.org/wikipedia/commons/3/33/F1.svg" alt="F1 Logo" style="height: 30px;">
                <span>Fan Club</span>
            </div>
            <p>A legnagyobb magyar F1 közösség. Hírek, futamok, csapatok és szenvedély egy helyen.</p>
        </div>

        <div class="footer-section">
            <h3>Navigáció</h3>
            <a href="/f1fanclub/index.php">Főoldal</a>
            <a href="/f1fanclub/news/news.php">Paddock (Feed)</a>
            <a href="/f1fanclub/about.php">Rólunk & Működés</a> <a href="/f1fanclub/teams/teams.php">Csapatok</a>
        </div>

        <div class="footer-section">
            <h3>Kapcsolat</h3>
            <a href="mailto:info@f1fanclub.hu">📧 info@f1fanclub.hu</a>
            
            <div class="social-links">
                <a href="https://instagram.com" target="_blank" title="Instagram">
                    <svg class="social-icon" viewBox="0 0 24 24">
                        <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>
    
    <div class="copyright">
        &copy; <?php echo date("Y"); ?> F1 Fan Club. Minden jog fenntartva. | Nem hivatalos F1 oldal.
    </div>
</footer>

<script>
// Fájl kiválasztás előnézet
const fileInput = document.getElementById('file-input');
const previewContainer = document.getElementById('preview-container');

if(fileInput) {
    fileInput.addEventListener('change', function() {
        previewContainer.innerHTML = ''; 
        if (this.files.length > 2) {
            alert("Maximum 2 képet tölthetsz fel!");
            this.value = ""; 
            return;
        }

        Array.from(this.files).forEach(file => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.classList.add('preview-thumb');
                previewContainer.appendChild(img);
            }
            reader.readAsDataURL(file);
        });
    });
}

// --- KOMMENTEK ÉS LIKE LOGIKA (MARADT A RÉGI) ---
function toggleComments(postId) {
    const wrapper = document.getElementById(`comments-wrapper-${postId}`);
    if (wrapper.style.display === "none" || wrapper.style.display === "") {
        wrapper.style.display = "block";
        loadComments(postId);
    } else {
        wrapper.style.display = "none";
    }
}

function loadComments(postId) {
    const listDiv = document.getElementById(`comments-list-${postId}`);
    listDiv.innerHTML = '<div style="color:#777; font-size:0.8rem;">Betöltés...</div>';

    fetch(`/f1fanclub/news/comment_api.php?news_id=${postId}`)
    .then(r => r.json())
    .then(data => {
        if (!data.success) {
            listDiv.innerHTML = '<div style="color:red;">Hiba.</div>';
            return;
        }
        if (data.comments.length === 0) {
            listDiv.innerHTML = '<div style="font-size:0.8rem; color:#ccc; font-style:italic; padding:5px;">Nincs még komment.</div>';
            return;
        }
        let html = '';
        data.comments.forEach((c, index) => {
            const avatar = c.profile_image ? `/f1fanclub/uploads/${c.profile_image}` : 'https://via.placeholder.com/40';
            const hiddenClass = index >= 3 ? 'hidden-comment' : '';
            html += `
            <div class="comment-item ${hiddenClass} comment-item-${postId}">
                <img src="${avatar}" style="width:35px; height:35px; border-radius:50%; border: 2px solid ${c.team_color}">
                <div class="comment-bubble">
                    <div style="font-weight:bold; font-size:0.85rem; color:${c.team_color}">${c.username}</div>
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
    const btn = document.getElementById(`load-more-${postId}`);
    if(btn) btn.style.display = 'none';
}

function postComment(postId) {
    const input = document.getElementById(`comment-input-${postId}`);
    const text = input.value.trim();
    if (!text) return;
    const formData = new URLSearchParams();
    formData.append("news_id", postId);
    formData.append("comment", text);
    fetch("/f1fanclub/news/comment_api.php", {
        method: "POST", headers: { "Content-Type": "application/x-www-form-urlencoded" }, body: formData.toString()
    }).then(r => r.json()).then(data => {
        if (data.success) {
            input.value = ""; loadComments(postId);
            const countSpan = document.getElementById(`comment-count-display-${postId}`);
            if(countSpan) countSpan.innerText = parseInt(countSpan.innerText) + 1;
        }
    });
}

function escapeHtml(text) {
  return text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
}

document.querySelectorAll(".action-btn[data-type]").forEach(btn => {
    btn.addEventListener("click", function(e) {
        e.stopPropagation(); 
        const id = this.dataset.id;
        const type = this.dataset.type;
        const formData = new URLSearchParams();
        formData.append("id", id);
        formData.append("type", type);
        fetch("/f1fanclub/news/react_news.php", {
            method: "POST", headers: { "Content-Type": "application/x-www-form-urlencoded" }, body: formData.toString()
        }).then(r => r.json()).then(data => {
            if (data.success) {
                document.getElementById("like-count-" + id).textContent = data.likes;
                document.getElementById("dislike-count-" + id).textContent = data.dislikes;
            }
        });
    });
});
</script>

</body>
</html>