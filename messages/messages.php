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
        case 'Haas F1 Team': return '#B6BABD'; case 'Cadillac': return '#B6BABD'; default: return '#ffffff';
    }
}

$profile_image = null; $teamColor = '#ffffff'; $isAdmin = false;
$stmt = $conn->prepare("SELECT profile_image, fav_team, role FROM users WHERE username=?");
$stmt->bind_param("s", $username); $stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$profile_image = $row['profile_image'] ?? null; 
$teamColor = getTeamColor($row['fav_team'] ?? null);
$isAdmin = !empty($row['role']) && $row['role'] === 'admin';
$stmt->close();
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Üzenetek - F1 Fan Club</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/svg+xml" href="https://upload.wikimedia.org/wikipedia/commons/3/33/F1.svg">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/messages.css?v=<?= time() ?>">
    <style>
        html, body { margin: 0; padding: 0; height: 100vh; overflow: hidden; }
        .app-container { display: flex; flex-direction: column; height: 100vh; }
        header { flex-shrink: 0; }
        
        /* ATOMBIZTOS KERESŐ STÍLUS BELEÉGETVE A HTML-BE */
        .sidebar-header-safe {
            padding: 20px;
            background: #111118;
            border-bottom: 1px solid #2a2a35;
            min-height: 85px; 
            flex-shrink: 0; 
            position: relative;
            display: block;
            width: 100%;
            box-sizing: border-box;
        }
        .sidebar-header-safe input {
            width: 100%;
            background: #202028;
            border: 1px solid #333;
            color: #fff;
            padding: 10px 15px;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            outline: none;
            box-sizing: border-box;
            height: 45px;
        }
        .sidebar-header-safe input:focus { border-color: #e10600; }

        /* Dropdown menu styles */
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

        .clickable-user {
            cursor: pointer;
            transition: opacity 0.2s;
        }
        
        .clickable-user:hover {
            opacity: 0.8;
        }

        /* User Modal Styles */
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

        .user-modal-close:hover {
            color: #e10600;
        }

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

        .user-modal-footer {
            display: flex;
            gap: 10px;
        }

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

        .btn-add-friend {
            background: #333;
            color: white;
        }

        .btn-add-friend:hover {
            background: #444;
        }

        .btn-send-msg {
            background: #e10600;
            color: white;
        }

        .btn-send-msg:hover {
            background: #b00500;
        }
    </style>
</head>
<body>

<div class="app-container">
    <header>
        <div class="left-header">
            <div class="logo-title">
                <img src="https://upload.wikimedia.org/wikipedia/commons/3/33/F1.svg" alt="F1 Logo" style="height:40px; filter: brightness(0) invert(1);">
                <span>Fan Club</span>
            </div>
        </div>

        <nav style="margin: 20px 0; display: flex; align-items: center; gap: 20px;">
            <a href="/f1fanclub/index.php" style="color:white; margin:0 10px;">Vissza a Főoldalra</a>
            
            <div style="position: relative;">
                <input type="text" id="userSearchInput" placeholder="Felhasználó keresése..." autocomplete="off" oninput="searchUsers(this.value)" 
                       style="width: 250px; background: #202028; border: 1px solid #333; color: #fff; padding: 8px 15px; border-radius: 20px; font-family: 'Poppins', sans-serif; outline: none;">
                <div id="searchResults" class="search-results" style="top: calc(100% + 10px); left: 0; right: 0; min-width: 250px;"></div>
            </div>
        </nav>

        <!-- DROPDOWN MENU - Updated from index.php -->
        <?php if ($isLoggedIn): ?>
            <div class="dropdown-container" id="userDropdownContainer">
                <div class="auth">
                    <div class="welcome" style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                        <?php if ($profile_image): ?>
                            <img src="/f1fanclub/uploads/<?php echo htmlspecialchars($profile_image); ?>" class="avatar clickable-user"
                                alt="Profilkép" onclick="openUserProfile('<?php echo htmlspecialchars(addslashes($username)); ?>')"
                                style="width:35px; height:35px; border-radius:50%; object-fit: cover; border: 2px solid <?php echo htmlspecialchars($teamColor); ?>;">
                        <?php endif; ?>
                        <span class="welcome-text">
                            <span class="clickable-user" onclick="openUserProfile('<?php echo htmlspecialchars(addslashes($username)); ?>')"
                                style="color: <?php echo htmlspecialchars($teamColor); ?>; font-weight:bold;"><?php echo htmlspecialchars($username); ?></span>
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

    <div class="messenger-wrapper">
        
        <!-- BAL OLDALI SÁV -->
        <div class="msg-sidebar">
            
            
            <div class="friend-list" id="friendList">
                <div style="color:#666; text-align:center; margin-top:20px;">Betöltés...</div>
            </div>
            
        </div>

        <!-- JOBB OLDALI CHAT SÁV -->
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
                    <input type="text" id="msgInput" placeholder="Írj egy üzenetet..." autocomplete="off" onkeypress="if(event.key === 'Enter') sendMessage()">
                    <button class="icon-btn" onclick="alert('Emojik hamarosan...')"><i class="fas fa-smile"></i></button>
                </div>
                <button class="send-btn" onclick="sendMessage()"><i class="fas fa-paper-plane"></i></button>
            </div>
        </div>
    </div>
</div>

<!-- POP-UP ABLAK -->
<div id="userProfileModal" class="user-modal-overlay" onclick="closeUserProfile(event)">
    <div class="user-modal-content" onclick="event.stopPropagation()">
        <button class="user-modal-close" onclick="closeUserProfile(event)">&times;</button>
        <div class="user-modal-header">
            <img id="modalProfileImg" src="" alt="Avatar">
            <h3 id="modalUsername">Felhasználónév</h3>
            <span id="modalRole" class="modal-role">Szerepkör</span>
        </div>
        <div class="user-modal-body">
            <p><i class="fas fa-flag-checkered" style="color:#888; width:20px;"></i> <strong>Csapat:</strong> <span id="modalTeam">Csapat</span></p>
            <p><i class="far fa-calendar-alt" style="color:#888; width:20px;"></i> <strong>Regisztrált:</strong> <span id="modalRegDate">Dátum</span></p>
        </div>
        <div class="user-modal-footer">
            <button id="modalFriendBtn" class="btn-add-friend" onclick="handleFriendAction()"><i class="fas fa-user-plus"></i> Barátnak jelölés</button>
            <button class="btn-send-msg" onclick="startChatFromModal()"><i class="fas fa-comment"></i> Üzenet küldése</button>
        </div>
    </div>
</div>

<script>
    let activePartner = null;
    let messageCount = 0;
    const currentUser = <?= json_encode($username) ?>;

    function makeSafeStr(str) {
        return str.replace(/\\/g, '\\\\').replace(/'/g, "\\'");
    }

    function loadFriends() {
        fetch('pm_api.php?action=get_friends')
        .then(r => r.json())
        .then(data => {
            if(data.success) {
                const list = document.getElementById('friendList');
                if(data.friends.length === 0) {
                    list.innerHTML = '<div style="color:#666; text-align:center; padding:20px; font-size:0.9rem;">Még nincsenek felvett barátaid. Keress egy felhasználót a fenti mezőben!</div>';
                    return;
                }
                list.innerHTML = '';
                data.friends.forEach(f => {
                    const isActive = f.friend_name === activePartner ? 'active' : '';
                    list.innerHTML += `
                        <div class="friend-item ${isActive}" onclick="openChat('${makeSafeStr(f.friend_name)}', '${f.profile_image}', '${f.color}')">
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

    function openChat(partnerName, partnerImg, partnerColor) {
        activePartner = partnerName;
        messageCount = 0; 
        document.getElementById('chatHeader').style.display = 'flex';
        document.getElementById('chatInputArea').style.display = 'flex';
        document.getElementById('activeFriendName').innerText = partnerName;
        document.getElementById('activeFriendName').style.color = partnerColor;
        document.getElementById('activeFriendImg').src = partnerImg;
        document.getElementById('activeFriendImg').style.border = `2px solid ${partnerColor}`;

        loadFriends(); 
        loadMessages(true); 
    }

    function loadMessages() {
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
                    box.scrollTop = box.scrollHeight;
                }
            }
        });
    }

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
            loadMessages(); 
        });
    }

    function searchUsers(query) {
        const resultsDiv = document.getElementById('searchResults');
        if (query.length < 3) {
            resultsDiv.style.display = 'none';
            return;
        }
        
        fetch('pm_api.php?action=search_users&term=' + encodeURIComponent(query))
        .then(r => r.json())
        .then(data => {
            if(data.success && data.users.length > 0) {
                resultsDiv.innerHTML = '';
                data.users.forEach(u => {
                    resultsDiv.innerHTML += `
                        <div class="search-result-item" onclick="openUserProfile('${makeSafeStr(u.username)}')">
                            <img src="${u.profile_image}" style="border-color: ${u.color}">
                            <span style="color: ${u.color}; font-weight: 600;">${u.username}</span>
                        </div>
                    `;
                });
                resultsDiv.style.display = 'block';
            } else {
                resultsDiv.innerHTML = '<div style="padding: 10px; color: #888; text-align: center; font-size:0.9rem;">Nincs találat...</div>';
                resultsDiv.style.display = 'block';
            }
        });
    }

    document.addEventListener('click', function(e) {
        if(!e.target.closest('nav')) {
            const resDiv = document.getElementById('searchResults');
            if(resDiv) resDiv.style.display = 'none';
        }
    });

    let currentModalUser = "";
    let currentFriendStatus = "";

    function openUserProfile(username) {
        document.getElementById('searchResults').style.display = 'none';
        document.getElementById('userSearchInput').value = '';

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
        });
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
            btn.innerHTML = '<i class="fas fa-clock"></i> Elküldve (Visszavonás)';
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
        }).then(r => r.json()).then(data => {
            if(data.success) {
                openUserProfile(currentModalUser);
                loadFriends(); 
            }
        });
    }

    function startChatFromModal() {
        closeUserProfile(); 
        let img = document.getElementById('modalProfileImg').src;
        let color = document.getElementById('modalProfileImg').style.borderColor;
        openChat(currentModalUser, img, color);
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

    loadFriends();
    setInterval(() => {
        loadMessages();
        if(!activePartner) loadFriends(); 
    }, 2000);
</script>
</body>
</html>