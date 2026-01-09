<?php
session_start();

/* ==== DEBUG (később kikapcsolhatod) ==== */
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

/* ==== LOGIN ADATOK ==== */
$isLoggedIn = isset($_SESSION['username']);
$username   = $isLoggedIn ? $_SESSION['username'] : null;

/* === Csapatszín függvény (Egységesítés miatt) === */
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
        default:              return '#ffffff';
    }
}

$profile_image = null;
$fav_team      = null;
$teamColor     = '#ffffff';
$isAdmin       = false;

/* ==== FELHASZNÁLÓ (profilkép + role + szín) ==== */
if ($isLoggedIn) {
    $stmt = $conn->prepare("SELECT profile_image, fav_team, role FROM users WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $profile_image = $row['profile_image'] ?? null;
    $fav_team      = $row['fav_team'] ?? null;
    $teamColor     = getTeamColor($fav_team);
    $isAdmin       = !empty($row['role']) && $row['role'] === 'admin';
}
$sqlFields = "
    n.id, n.title, n.content, n.image, n.author, n.created_at,
    n.likes, n.dislikes,
    (SELECT COUNT(*) FROM news_comments WHERE news_id = n.id) AS comment_count
";
/* ==== HÍREK LEKÉRDEZÉSE ==== */
if ($isLoggedIn) {
    $stmt = $conn->prepare("
        SELECT $sqlFields, r.reaction AS user_reaction
        FROM news n
        LEFT JOIN news_reactions r ON r.news_id = n.id AND r.username = ?
        ORDER BY n.created_at DESC
    ");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $newsResult = $stmt->get_result();
} else {
    $newsResult = $conn->query("
        SELECT $sqlFields
        FROM news n
        ORDER BY n.created_at DESC
    ");
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
<meta charset="UTF-8">
<title>News – F1 Fan Club</title>
<link rel="stylesheet" href="/css/style.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;800&display=swap" rel="stylesheet">

</head>
<body>

<header>
  <div class="left-header">
    <h1 class="logo-title">
      <img src="https://upload.wikimedia.org/wikipedia/commons/3/33/F1.svg" class="f1-logo" alt="F1 Logo" style="height: 40px; vertical-align: middle;">
      <span>Fan Club</span>
    </h1>
  </div>

  <nav style="margin: 20px 0;">
    <a href="/index.php" style="color:white; margin:0 10px;">Home</a>
    <a href="/Championship/championship.php" style="color:white; margin:0 10px;">Championship</a>
    <a href="/teams/teams.php" style="color:white; margin:0 10px;">Teams</a>
    <a href="/drivers/drivers.php" style="color:white; margin:0 10px;">Drivers</a>
    <a href="/news/news.php" style="color:#e10600; margin:0 10px; font-weight:bold;">News</a>
  </nav>

  <?php if ($isLoggedIn): ?>
    <div class="auth">
      <div class="welcome">
<?php if ($profile_image): ?>
  <img src="/uploads/<?php echo htmlspecialchars($profile_image); ?>" class="avatar" alt="Profile" style="width:30px; height:30px; border-radius:50%; vertical-align:middle; object-fit: cover;">
<?php endif; ?>
        <span class="welcome-text">
          Welcome,
          <span style="color: <?php echo htmlspecialchars($teamColor); ?>;">
            <?php echo htmlspecialchars($username); ?>
          </span>!
        </span>
      </div>
      
      <div style="display:inline-block;">
        <?php if ($isAdmin): ?>
          <a href="/news/hirszerkeszto.php" class="btn">News Editor</a>
        <?php endif; ?>
         <a href="/profile/profile.php" class="btn">Profile</a>
        <a href="/logout/logout.php" class="btn">Log out</a>
      </div>
    </div>
  <?php else: ?>
    <div class="auth">
      <a href="/register/register.html" class="btn">Register</a>
      <a href="/login/login.html" class="btn">Login</a>
    </div>
  <?php endif; ?>
</header>

<?php if ($isLoggedIn): ?>
<div class="profile-card" id="profileCard">
  <h3><?php echo htmlspecialchars($username); ?>'s Profile</h3>

  <?php if ($profile_image): ?>
    <img src="uploads/<?php echo htmlspecialchars($profile_image); ?>" alt="Profile picture" style="max-width:100px;">
  <?php endif; ?>

  <form action="upload_profile.php" method="post" enctype="multipart/form-data">
    <p><small>Upload new profile picture (max 250×250 px)</small></p>
    <input type="file" name="profile_image" required>
    <input type="submit" value="Upload" class="btn">
  </form>
</div>
<?php endif; ?>

<main class="news-main">
  <h1>F1 News</h1>
  <p class="news-subtitle">
    Live paddock vibes: breaking stories, transfer rumours, and race weekend recaps – mind egy helyen.
  </p>

  <?php
  $hasNews = ($newsResult instanceof mysqli_result && $newsResult->num_rows > 0);

  if ($hasNews) {
      // Első hír = „featured”
      $first = $newsResult->fetch_assoc();
  ?>
    <section class="news-section">
      <h2 class="news-section-title">Kiemelt hír</h2>
      <div class="featured-grid">
        <article class="featured-card news-card">
          <?php if (!empty($first['image'])): ?>
            <img class="news-thumb" src="<?= htmlspecialchars($first['image']); ?>" alt="">
          <?php endif; ?>

          <div class="news-content">
            <h3 class="featured-title"><?= htmlspecialchars($first['title']); ?></h3>
            <div class="featured-meta">
              <?= htmlspecialchars(date('Y.m.d H:i', strtotime($first['created_at']))); ?>
              <?php if (!empty($first['author'])): ?>
                · by <?= htmlspecialchars($first['author']); ?>
              <?php endif; ?>
            </div>
            <p class="featured-summary">
              <?php
                $content = $first['content'] ?? '';
                $snippet = substr($content, 0, 250);
                echo nl2br(htmlspecialchars($snippet));
                if (strlen($content) > 250) echo '...';
              ?>
            </p>
            <div class="full-content">
              <?= nl2br(htmlspecialchars($first['content'])); ?>
            </div>

            <div class="reaction-bar" onclick="event.stopPropagation();">
              <button class="react-btn like" data-id="<?= $first['id']; ?>" data-type="like">
                👍 <span class="count" id="like-count-<?= $first['id']; ?>"><?= (int)$first['likes']; ?></span>
              </button>
              <button class="react-btn dislike" data-id="<?= $first['id']; ?>" data-type="dislike">
                👎 <span class="count" id="dislike-count-<?= $first['id']; ?>"><?= (int)$first['dislikes']; ?></span>
              </button>
              <div class="reaction-bar" onclick="event.stopPropagation();">
    <button class="react-btn" style="border-color: transparent;">
        💬 <span class="count"><?= $n['comment_count'] ?? 0; ?></span>
    </button>
</div>

<div class="comments-section" id="comments-section-<?= $n['id']; ?>" onclick="event.stopPropagation();" style="display: none;">
    <div class="comments-title">🏁 Paddock Talk</div>
    
    <div class="comments-list" id="comments-list-<?= $n['id']; ?>">
        <div style="text-align:center; color:#777; padding:10px;">Loading comments...</div>
    </div>

    <?php if ($isLoggedIn): ?>
    <div class="comment-form">
        <?php if ($profile_image): ?>
            <img src="/uploads/<?= htmlspecialchars($profile_image); ?>" class="comment-avatar" style="border-color: <?= $teamColor; ?>">
        <?php endif; ?>
        <textarea class="comment-input" id="comment-input-<?= $n['id']; ?>" placeholder="Join the discussion..."></textarea>
        <button class="comment-submit" onclick="postComment(<?= $n['id']; ?>)">➤</button>
    </div>
    <?php else: ?>
        <p style="font-size:0.8rem; color:#999; text-align:center;">
            <a href="/login/login.html" style="color:#e10600;">Log in</a> to join the Paddock Talk.
        </p>
    <?php endif; ?>
</div>
            </div>

          </div>
        </article>
      </div>
    </section>
  <?php
  } // if hasNews
  ?>

  <section class="news-section">
    <h2 class="news-section-title">Összes hír</h2>
    <?php if ($hasNews && $newsResult->num_rows > 0): ?>
      <div class="general-grid">
        <?php while ($n = $newsResult->fetch_assoc()): ?>
          <article class="general-card news-card">
            <?php if (!empty($n['image'])): ?>
              <img class="news-thumb" src="<?= htmlspecialchars($n['image']); ?>" alt="">
            <?php endif; ?>

            <div class="news-content">
              <h3 class="general-title"><?= htmlspecialchars($n['title']); ?></h3>
              <div class="general-meta">
                <?= htmlspecialchars(date('Y.m.d', strtotime($n['created_at']))); ?>
                <?php if (!empty($n['author'])): ?>
                  · <?= htmlspecialchars($n['author']); ?>
                <?php endif; ?>
              </div>
              <p class="general-summary">
                <?php
                  $content = $n['content'] ?? '';
                  $snippet = substr($content, 0, 140);
                  echo nl2br(htmlspecialchars($snippet));
                  if (strlen($content) > 140) echo '...';
                ?>
              </p>
              <div class="full-content">
                <?= nl2br(htmlspecialchars($n['content'])); ?>
              </div>

              <div class="reaction-bar" onclick="event.stopPropagation();">
                <button class="react-btn like" data-id="<?= $n['id']; ?>" data-type="like">
                  👍 <span class="count" id="like-count-<?= $n['id']; ?>"><?= (int)$n['likes']; ?></span>
                </button>
                <button class="react-btn dislike" data-id="<?= $n['id']; ?>" data-type="dislike">
                  👎 <span class="count" id="dislike-count-<?= $n['id']; ?>"><?= (int)$n['dislikes']; ?></span>
                </button>
              </div>

            </div>
          </article>
        <?php endwhile; ?>
      </div>
    <?php else: ?>
      <p class="no-news">Még nincsenek hírek az adatbázisban.</p>
    <?php endif; ?>
  </section>
</main>

<script>
// Profil kártya (Marad a régi)
function toggleProfile() {
  const pc = document.getElementById("profileCard");
  if (!pc) return;
  pc.style.display = pc.style.display === "block" ? "none" : "block";
}

// Lenyílós hírek + Kommentek betöltése
document.querySelectorAll(".news-card").forEach(card => {
    // Keresünk benne like gombot, hogy kinyerjük az ID-t
    const idObj = card.querySelector(".react-btn[data-id]");
    if (!idObj) return; // Ha valamiért nincs ID
    const newsId = idObj.dataset.id;

    const full = card.querySelector(".full-content");
    const commentSec = document.getElementById("comments-section-" + newsId);

    card.addEventListener("click", () => {
        if (!card.classList.contains("open")) {
            // NYITÁS
            card.classList.add("open");
            // Először a szöveget nyitjuk
            full.style.maxHeight = full.scrollHeight + "px";
            
            // Megjelenítjük a komment szekciót is
            if(commentSec) {
                commentSec.style.display = "block";
                loadComments(newsId); // API hívás
                // A magasságot újra kell számolni a kommentek miatt kicsit később
                setTimeout(() => {
                    full.style.maxHeight = (full.scrollHeight + 500) + "px"; // Hagyunk helyet
                }, 100);
            }
        } else {
            // CSUKÁS
            full.style.maxHeight = "0px";
            if(commentSec) commentSec.style.display = "none";
            card.classList.remove("open");
        }
    });
});

// --- KOMMENTEK BETÖLTÉSE ---
function loadComments(newsId) {
    const listDiv = document.getElementById("comments-list-" + newsId);
    
    fetch(`/news/comment_api.php?news_id=${newsId}`)
    .then(r => r.json())
    .then(data => {
        if (!data.success) {
            listDiv.innerHTML = '<div style="color:red; text-align:center;">Error loading.</div>';
            return;
        }

        if (data.comments.length === 0) {
            listDiv.innerHTML = '<div style="color:#666; text-align:center; font-style:italic; padding:10px;">No comments yet. Be the first! 🏎️</div>';
            return;
        }

        let html = '';
        data.comments.forEach(c => {
            const avatar = c.profile_image ? `/uploads/${c.profile_image}` : 'https://via.placeholder.com/40';
            const badge = c.fav_team ? `<span class="comment-team-badge" style="color:${c.team_color}">${c.fav_team}</span>` : '';
            
            html += `
            <div class="comment-item">
                <img src="${avatar}" class="comment-avatar" style="border-color: ${c.team_color || '#fff'}">
                <div class="comment-bubble">
                    <div class="comment-header">
                        <span class="comment-user" style="color:${c.team_color}">${c.username} ${badge}</span>
                        <span class="comment-date">${c.date_formatted}</span>
                    </div>
                    <div>${escapeHtml(c.comment)}</div>
                </div>
            </div>`;
        });
        listDiv.innerHTML = html;
        
        // Görgessünk az aljára
        listDiv.scrollTop = listDiv.scrollHeight;
    })
    .catch(err => console.error(err));
}

// --- ÚJ KOMMENT KÜLDÉSE ---
function postComment(newsId) {
    const input = document.getElementById("comment-input-" + newsId);
    const text = input.value.trim();
    
    if (!text) return;

    const formData = new URLSearchParams();
    formData.append("news_id", newsId);
    formData.append("comment", text);

    fetch("/news/comment_api.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: formData.toString()
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            input.value = ""; // Töröljük a mezőt
            loadComments(newsId); // Újratöltjük a listát
            
            // Opcionális: Számláló növelése a kártyán (ha van ilyen elem)
            // const countSpan = document.querySelector(...)
        } else {
            alert("Error: " + data.error);
        }
    })
    .catch(err => console.error(err));
}

// XSS védelem egyszerűen
function escapeHtml(text) {
  return text
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
}

// LIKE / DISLIKE logika (Marad a régi)
document.querySelectorAll(".react-btn[data-type]").forEach(btn => {
    btn.addEventListener("click", function(e) {
        e.stopPropagation(); 
        const id = this.dataset.id;
        const type = this.dataset.type;
        // ... (itt marad a régi fetch logika) ...
        const formData = new URLSearchParams();
        formData.append("id", id);
        formData.append("type", type);

        fetch("/news/react_news.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: formData.toString()
        })
        .then(r => r.json())
        .then(data => {
            if (!data.success) return;
            const likeSpan = document.getElementById("like-count-" + id);
            const dislikeSpan = document.getElementById("dislike-count-" + id);
            if (likeSpan) likeSpan.textContent = data.likes;
            if (dislikeSpan) dislikeSpan.textContent = data.dislikes;
        });
    });
});
</script>

</body>
</html>