<?php
session_start();
header('Content-Type: application/json');

/* ==== ADATBÁZIS KAPCSOLAT ==== */
$DB_HOST = "localhost";
$DB_USER = "swmjndga_swmjndga";
$DB_PASS = "Teszt1234!";
$DB_NAME = "swmjndga_f1adat";

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'DB error']);
    exit;
}

/* ==== LOGIN KELL ==== */
if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'error' => 'login_required']);
    exit;
}
$username = $_SESSION['username'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid method']);
    exit;
}

$id   = isset($_POST['id'])   ? (int)$_POST['id']   : 0;
$type = isset($_POST['type']) ? $_POST['type']      : '';

if ($id <= 0 || !in_array($type, ['like', 'dislike'], true)) {
    echo json_encode(['success' => false, 'error' => 'Bad params']);
    exit;
}

$fieldLike    = 'likes';
$fieldDislike = 'dislikes';

/* Megnézzük, reagált-e már a user erre a hírre */
$stmt = $conn->prepare("SELECT reaction FROM news_reactions WHERE news_id = ? AND username = ?");
$stmt->bind_param("is", $id, $username);
$stmt->execute();
$res = $stmt->get_result();
$existing = $res->fetch_assoc();
$stmt->close();

/*
  3 eset:
  1) Nincs még reaction -> beszúr + növeljük az adott countert
  2) Ugyanarra kattint újra -> reaction törlése + counter -1 (unlike / undislike)
  3) Másik gombra kattint -> reaction update + egyik counter -1, másik +1
*/

if (!$existing) {
    // 1) első reakció
    $stmt = $conn->prepare("INSERT INTO news_reactions (news_id, username, reaction) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $id, $username, $type);
    $stmt->execute();
    $stmt->close();

    if ($type === 'like') {
        $conn->query("UPDATE news SET $fieldLike = $fieldLike + 1 WHERE id = $id");
    } else {
        $conn->query("UPDATE news SET $fieldDislike = $fieldDislike + 1 WHERE id = $id");
    }

    $currentReaction = $type;
} else {
    $old = $existing['reaction'];

    if ($old === $type) {
        // 2) ugyanarra kattint = törli a reactiont
        $stmt = $conn->prepare("DELETE FROM news_reactions WHERE news_id = ? AND username = ?");
        $stmt->bind_param("is", $id, $username);
        $stmt->execute();
        $stmt->close();

        if ($type === 'like') {
            $conn->query("UPDATE news SET $fieldLike = GREATEST($fieldLike - 1, 0) WHERE id = $id");
        } else {
            $conn->query("UPDATE news SET $fieldDislike = GREATEST($fieldDislike - 1, 0) WHERE id = $id");
        }

        $currentReaction = ''; // nincs aktív reaction
    } else {
        // 3) vált like <-> dislike között
        $stmt = $conn->prepare("UPDATE news_reactions SET reaction = ? WHERE news_id = ? AND username = ?");
        $stmt->bind_param("sis", $type, $id, $username);
        $stmt->execute();
        $stmt->close();

        if ($old === 'like' && $type === 'dislike') {
            $conn->query("UPDATE news SET $fieldLike = GREATEST($fieldLike - 1, 0), $fieldDislike = $fieldDislike + 1 WHERE id = $id");
        } elseif ($old === 'dislike' && $type === 'like') {
            $conn->query("UPDATE news SET $fieldDislike = GREATEST($fieldDislike - 1, 0), $fieldLike = $fieldLike + 1 WHERE id = $id");
        }

        $currentReaction = $type;
    }
}

/* friss értékek visszaadása */
$stmt = $conn->prepare("SELECT likes, dislikes FROM news WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$final = $stmt->get_result()->fetch_assoc();
$stmt->close();

echo json_encode([
    'success'        => true,
    'likes'          => (int)$final['likes'],
    'dislikes'       => (int)$final['dislikes'],
    'userReaction'   => $currentReaction, // '', 'like' vagy 'dislike'
]);
