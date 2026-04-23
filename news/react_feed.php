<?php
session_start();
header('Content-Type: application/json');

/*
 * ============================================================================
 * DATABASE CONNECTION
 * ============================================================================
 */
$DB_HOST = "localhost";
$DB_USER = "swmjndga_swmjndga";
$DB_PASS = "Teszt1234!";
$DB_NAME = "swmjndga_f1adat";

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'DB error']);
    exit;
}

/**
 * ============================================================================
 * AUTHENTICATION CHECK
 * ============================================================================
 * Ensures the user is logged in before allowing reactions.
 */
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

/** Check if the user has already reacted to this news */
$stmt = $conn->prepare("SELECT reaction FROM news_reactions WHERE news_id = ? AND username = ?");
$stmt->bind_param("is", $id, $username);
$stmt->execute();
$res = $stmt->get_result();
$existing = $res->fetch_assoc();
$stmt->close();

/**
 * Handles 3 possible cases for a reaction:
 * 1. Initial reaction: insert new record and increment the corresponding counter.
 * 2. Toggle same reaction: delete existing record and decrement counter.
 * 3. Switch reaction: update record, decrement old counter, increment new counter.
 */

if (!$existing) {
    /** 1. Initial reaction */
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
        /** 2. Toggle same reaction */
        $stmt = $conn->prepare("DELETE FROM news_reactions WHERE news_id = ? AND username = ?");
        $stmt->bind_param("is", $id, $username);
        $stmt->execute();
        $stmt->close();

        if ($type === 'like') {
            $conn->query("UPDATE news SET $fieldLike = GREATEST($fieldLike - 1, 0) WHERE id = $id");
        } else {
            $conn->query("UPDATE news SET $fieldDislike = GREATEST($fieldDislike - 1, 0) WHERE id = $id");
        }

        $currentReaction = ''; /** No active reaction */
    } else {
        /** 3. Switch reaction */
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

/** Return updated values */
$stmt = $conn->prepare("SELECT likes, dislikes FROM news WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$final = $stmt->get_result()->fetch_assoc();
$stmt->close();

echo json_encode([
    'success'        => true,
    'likes'          => (int)$final['likes'],
    'dislikes'       => (int)$final['dislikes'],
    'userReaction'   => $currentReaction, /** '', 'like', or 'dislike' */
]);
