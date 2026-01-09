<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.html");
    exit;
}

$username = $_SESSION['username'];

$DB_HOST = "localhost";
$DB_USER = "swmjndga_swmjndga";
$DB_PASS = "Teszt1234!";
$DB_NAME = "swmjndga_f1adat";

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) {
    die("Adatbázis hiba: " . $conn->connect_error);
}

// --- Lekérdezzük a belépett user-t (admin-e + profilkép) ---
$stmt = $conn->prepare("SELECT role, profile_image FROM users WHERE username=?");
$stmt->bind_param("s", $username);
$stmt->execute();
$userRow = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$userRow || $userRow['role'] !== 'admin') {
    echo "<h2>Nincs jogosultságod az admin felülethez.</h2>";
    exit;
}

$profile_image = $userRow['profile_image'] ?? null;

// ---------- 1) POST KEZELÉS: ROLE VÁLTÁS / FELHASZNÁLÓ TÖRLÉS ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ROLE módosítás
    if (isset($_POST['action']) && $_POST['action'] === 'change_role') {
        $userId   = (int)$_POST['user_id'];
        $newRole  = $_POST['new_role'];

        if (in_array($newRole, ['user', 'admin'], true)) {

            // opcionális: ne tudja magát véletlen törölni/lefokozni az egyetlen admin
            // de most egyszerűen engedjük

            $stmt = $conn->prepare("UPDATE users SET role=? WHERE id=?");
            $stmt->bind_param("si", $newRole, $userId);
            $stmt->execute();
            $stmt->close();
        }

        header("Location: /admin/admin.php");
        exit;
    }

    // FELHASZNÁLÓ törlése
    if (isset($_POST['action']) && $_POST['action'] === 'delete_user') {
        $userId = (int)$_POST['user_id'];

        // Ne tudd magad véletlen törölni
        $stmt = $conn->prepare("SELECT username FROM users WHERE id=?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($row && $row['username'] !== $username) {
            $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $stmt->close();
        }

        header("Location: admin.php");
        exit;
    }
}

// ---------- 2) ÖSSZES FELHASZNÁLÓ LEKÉRDEZÉSE LISTÁZÁSHOZ ----------
$usersResult = $conn->query("SELECT id, username, email, role, fav_team, reg_date FROM users ORDER BY reg_date DESC");
?>
<!DOCTYPE html>
<html lang="hu">
<head>
  <meta charset="UTF-8">
  <title>Admin panel - F1 Fan Club</title>
  <link rel="stylesheet" href="/css/style.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;800&display=swap" rel="stylesheet">
  <style>
    .admin-container {
      width: 90%;
      max-width: 1000px;
      margin: 40px auto;
      background: #1a1a1a;
      padding: 20px;
      border-radius: 12px;
      box-shadow: 0 0 15px #000;
      text-align: left;
    }
    .admin-container h2 {
      margin-top: 0;
      color: #e10600;
    }
    table.admin-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 14px;
    }
    .admin-table th, .admin-table td {
      padding: 8px 10px;
      border-bottom: 1px solid #333;
    }
    .admin-table th {
      background: #333;
    }
    .role-badge {
      padding: 3px 8px;
      border-radius: 10px;
      font-size: 12px;
    }
    .role-user {
      background: #444;
      color: #ddd;
    }
    .role-admin {
      background: #e10600;
      color: #fff;
    }
    .admin-actions {
      display: flex;
      gap: 6px;
      flex-wrap: wrap;
    }
    .admin-actions form {
      display: inline-block;
      margin: 0;
    }
    .role-select {
      background: #111;
      color: #eee;
      border-radius: 4px;
      border: 1px solid #444;
      padding: 3px 6px;
      font-size: 12px;
    }
    .btn-small {
      padding: 4px 8px;
      font-size: 12px;
      border-radius: 4px;
    }
    .btn-delete {
      background: #b30000;
    }
    .btn-delete:hover {
      background: #ff0000;
    }
  </style>
</head>
<body>

<header>
  <div class="left-header">
    <h1 class="logo-title">
      <img src="https://upload.wikimedia.org/wikipedia/commons/3/33/F1.svg" class="f1-logo" alt="F1 Logo">
      <span>Fan Club</span>
    </h1>
  </div>

  <nav>
    <a href="/index.php">Home</a>
    <a href="/Championship/championship.php">Championship</a>
    <a href="/teams/teams.php">Teams</a>
    <a href="/drivers/drivers.php">Drivers</a>
    <a href="/news/news.php">News</a>
  </nav>

  <div class="auth">
    <div class="welcome">
      <?php if ($profile_image): ?>
        <img src="uploads/<?= htmlspecialchars($profile_image); ?>" class="avatar" alt="Profile">
      <?php endif; ?>
      <span class="welcome-text">
        Admin:
        <span style="color:#e10600;">
          <?= htmlspecialchars($username); ?>
        </span>
      </span>
    </div>
    <a href="/logout/logout.php" class="btn">Log out</a>
  </div>
</header>

<div class="admin-container">
  <h2>Felhasználók kezelése</h2>
  <table class="admin-table">
    <thead>
      <tr>
        <th>ID</th>
        <th>Felhasználónév</th>
        <th>E-mail</th>
        <th>Szerep</th>
        <th>Kedvenc csapat</th>
        <th>Regisztráció</th>
        <th>Műveletek</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($u = $usersResult->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($u['id']); ?></td>
          <td><?= htmlspecialchars($u['username']); ?></td>
          <td><?= htmlspecialchars($u['email']); ?></td>
          <td>
            <span class="role-badge <?= $u['role'] === 'admin' ? 'role-admin' : 'role-user'; ?>">
              <?= htmlspecialchars($u['role']); ?>
            </span>
          </td>
          <td><?= htmlspecialchars($u['fav_team'] ?? '-'); ?></td>
          <td><?= htmlspecialchars($u['reg_date']); ?></td>
          <td>
            <div class="admin-actions">
              <!-- SZEREP VÁLTÁS FORM -->
              <form method="post">
                <input type="hidden" name="action" value="change_role">
                <input type="hidden" name="user_id" value="<?= (int)$u['id']; ?>">
                <select name="new_role" class="role-select">
                  <option value="user"  <?= $u['role'] === 'user'  ? 'selected' : ''; ?>>user</option>
                  <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : ''; ?>>admin</option>
                </select>
                <button type="submit" class="btn btn-small">Mentés</button>
              </form>

              <!-- FELHASZNÁLÓ TÖRLÉS FORM -->
              <?php if ($u['username'] !== $username): ?>
                <form method="post" onsubmit="return confirm('Biztos törölni akarod ezt a felhasználót?');">
                  <input type="hidden" name="action" value="delete_user">
                  <input type="hidden" name="user_id" value="<?= (int)$u['id']; ?>">
                  <button type="submit" class="btn btn-small btn-delete">Törlés</button>
                </form>
              <?php else: ?>
                <!-- Saját magad mellé nem rakunk törlés gombot 😄 -->
                <small> (saját fiók)</small>
              <?php endif; ?>
            </div>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

</body>
</html>
