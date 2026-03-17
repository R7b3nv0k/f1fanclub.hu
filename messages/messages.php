<?php
// /f1fanclub/messages/messages.php
session_start();

$DB_HOST = "localhost";
$DB_USER = "swmjndga_swmjndga";
$DB_PASS = "Teszt1234!";
$DB_NAME = "swmjndga_f1adat";

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) { die("Adatbázis hiba!"); }

$isLoggedIn = isset($_SESSION['username']);
$username = $isLoggedIn ? $_SESSION['username'] : null;

if (!$isLoggedIn) {
    header("Location: ../login/login.html");
    exit;
}

function getTeamColor($team) {
    switch ($team) {
        case 'Red Bull': return '#1E41FF'; case 'Ferrari': return '#DC0000'; case 'Mercedes': return '#00D2BE';
        case 'McLaren': return '#FF8700'; case 'Aston Martin': return '#006F62'; case 'Alpine': return '#0090FF';
        case 'Williams': return '#00A0DE'; case 'RB': return '#2b2bff'; case 'Audi': return '#e3000f';
        case 'Haas F1 Team': return '#B6BABD'; case 'Cadillac': return '#1b1b1b'; default: return '#ffffff';
    }
}

$profile_image = null; $teamColor = '#ffffff';
$stmt = $conn->prepare("SELECT profile_image, fav_team FROM users WHERE username=?");
$stmt->bind_param("s", $username); $stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$profile_image = $row['profile_image'] ?? null; $teamColor = getTeamColor($row['fav_team'] ?? null);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - F1 Fan Club</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/messages.css?v=<?= time() ?>">
    <style>
        body { margin: 0; padding: 0; overflow: hidden; } /* Discord stílus, nem lehet lejjebb görgetni a főoldalt */
    </style>
</head>
<body>

<header>
  <div class="left-header">
    <h1 class="logo-title">
      <img src="https://upload.wikimedia.org/wikipedia/commons/3/33/F1.svg" alt="F1 Logo" style="height:40px; filter:invert(1);">
      <span>Direct Messages</span>
    </h1>
  </div>
  <nav style="margin: 20px 0;">
      <a href="/f1fanclub/index.php" style="color:white; margin:0 10px;">Home</a>
      <a href="/f1fanclub/news/feed.php" style="color:white; margin:0 10px;">Paddock Feed</a>
  </nav>
  <div class="auth">
      <div class="welcome">
          <img src="<?= $profile_image ? '../uploads/'.$profile_image : '../drivers/default.png' ?>" class="avatar" style="border-color: <?= $teamColor ?>;">
          <span class="welcome-text">
              <span style="color: <?= $teamColor ?>;"><?= htmlspecialchars($username) ?></span>
          </span>
      </div>
  </div>
</header>

<div class="messenger-wrapper">
    <div class="msg-sidebar">
        <div class="sidebar-header">
            <input type="text" class="search-bar" placeholder="Barát keresése...">
        </div>
        <div class="friend-list" id="friendList">
            <div style="color:#666; text-align:center; margin-top:20px;">Betöltés...</div>
        </div>
    </div>

    <div class="chat-area">
        <div class="chat-header" id="chatHeader" style="display: none;">
            <img src="" id="activeFriendImg" style="width:40px; height:40px; border-radius:50%; object-fit:cover;">
            <h2 id="activeFriendName">Válassz partnert</h2>
        </div>
        
        <div class="chat-box" id="chatBox">
            <div class="empty-chat">
                <i class="fas fa-comments"></i>
                <h3>Nincs kiválasztott beszélgetés</h3>
                <p>Válassz egy barátot a bal oldali listából, hogy elkezdj csevegni!</p>
            </div>
        </div>

        <div class="chat-input-area" id="chatInputArea" style="display: none;">
            <div class="chat-input-container">
                <button class="icon-btn" onclick="alert('Képfeltöltés funkció hamarosan...')"><i class="fas fa-plus-circle"></i></button>
                <input type="text" id="msgInput" placeholder="Írj egy üzenetet @partner részére..." autocomplete="off" onkeypress="if(event.key === 'Enter') sendMessage()">
                <button class="icon-btn" onclick="alert('Emojik hamarosan...')"><i class="fas fa-smile"></i></button>
            </div>
            <button class="send-btn" onclick="sendMessage()"><i class="fas fa-paper-plane"></i></button>
        </div>
    </div>
</div>

<script>
    let activePartner = null;
    let messageCount = 0;
    const currentUser = "<?= $username ?>";

    // 1. Barátok betöltése
    function loadFriends() {
        fetch('pm_api.php?action=get_friends')
        .then(r => r.json())
        .then(data => {
            if(data.success) {
                const list = document.getElementById('friendList');
                if(data.friends.length === 0) {
                    list.innerHTML = '<div style="color:#666; text-align:center; padding:20px; font-size:0.9rem;">Még nincsenek felvett barátaid. (A Pop-up ablakban tudsz majd jelölni!)</div>';
                    return;
                }
                list.innerHTML = '';
                data.friends.forEach(f => {
                    const isActive = f.friend_name === activePartner ? 'active' : '';
                    list.innerHTML += `
                        <div class="friend-item ${isActive}" onclick="openChat('${f.friend_name}', '${f.profile_image}', '${f.color}')">
                            <img src="${f.profile_image}" class="friend-avatar" style="border-color: ${f.color}">
                            <div class="friend-info">
                                <div class="friend-name" style="color: ${f.color}">${f.friend_name}</div>
                            </div>
                        </div>
                    `;
                });
            }
        });
    }

    // 2. Chat megnyitása
    function openChat(partnerName, partnerImg, partnerColor) {
        activePartner = partnerName;
        messageCount = 0; // Nullázzuk, hogy az új betöltésnél legörgessen

        document.getElementById('chatHeader').style.display = 'flex';
        document.getElementById('chatInputArea').style.display = 'flex';
        
        document.getElementById('activeFriendName').innerText = partnerName;
        document.getElementById('activeFriendName').style.color = partnerColor;
        document.getElementById('activeFriendImg').src = partnerImg;
        document.getElementById('activeFriendImg').style.border = `2px solid ${partnerColor}`;

        loadFriends(); // Hogy frissítse az "active" (kijelölt) szürke hátteret a bal oldalon
        loadMessages(true); // true = kényszerített görgetés az aljára
    }

    // 3. Üzenetek betöltése (Polling)
    function loadMessages(forceScroll = false) {
        if (!activePartner) return;

        fetch('pm_api.php?action=get_messages&partner=' + encodeURIComponent(activePartner))
        .then(r => r.json())
        .then(data => {
            if(data.success) {
                if (data.messages.length !== messageCount) {
                    const box = document.getElementById('chatBox');
                    box.innerHTML = '';
                    
                    if(data.messages.length === 0) {
                        box.innerHTML = '<div style="margin:auto; color:#666;">Itt kezdődik a beszélgetésed. Integess! 👋</div>';
                    } else {
                        data.messages.forEach(m => {
                            const isMe = m.sender === currentUser;
                            const rowClass = isMe ? 'me' : 'them';
                            box.innerHTML += `
                                <div class="message-row ${rowClass}">
                                    <div class="message-bubble">${m.message}</div>
                                    <div class="message-time">${m.time}</div>
                                </div>
                            `;
                        });
                    }
                    
                    messageCount = data.messages.length;
                    // Ha új üzenet jött, vagy most nyitottuk meg, legörgetünk az aljára
                    box.scrollTop = box.scrollHeight;
                }
            }
        });
    }

    // 4. Üzenet küldése
    function sendMessage() {
        const input = document.getElementById('msgInput');
        const msg = input.value.trim();
        
        if (!msg || !activePartner) return;
        input.value = '';

        fetch('pm_api.php?action=send', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ receiver: activePartner, message: msg })
        }).then(() => {
            loadMessages(); // Azonnal letölti az újat
        });
    }

    // Inicializálás és folyamatos frissítés beállítása (Polling 2 másodpercenként)
    loadFriends();
    setInterval(() => {
        loadMessages();
        // A barát listát ritkábban is elég lenne, de teszteléshez jó ha frissül
        if(!activePartner) loadFriends(); 
    }, 2000);

</script>
</body>
</html>