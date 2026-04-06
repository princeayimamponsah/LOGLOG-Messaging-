<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['userID'])) {
    echo json_encode([]);
    exit;
}

$userId   = $_SESSION['userID'];
$friendId = intval($_GET['friend_id'] ?? 0);
$afterId  = intval($_GET['after_id'] ?? 0);

if (!$friendId) {
    echo json_encode([]);
    exit;
}

$stmt = $conn->prepare("
    SELECT id, sender_id, message, sent_at
    FROM messages
    WHERE ((sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?))
      AND id > ?
    ORDER BY sent_at ASC
");
$stmt->bind_param("iiiii", $userId, $friendId, $friendId, $userId, $afterId);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}

// Mark incoming messages from this friend as read for sidebar unread counts.
$readStmt = $conn->prepare("SELECT COALESCE(MAX(id), 0) AS max_incoming_id FROM messages WHERE sender_id = ? AND receiver_id = ?");
$readStmt->bind_param("ii", $friendId, $userId);
$readStmt->execute();
$readRes = $readStmt->get_result()->fetch_assoc();
$maxIncomingId = (int) ($readRes['max_incoming_id'] ?? 0);

if ($maxIncomingId > 0) {
    $upsert = $conn->prepare("INSERT INTO chat_state (user_id, friend_id, last_read_id) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE last_read_id = GREATEST(last_read_id, VALUES(last_read_id))");
    $upsert->bind_param("iii", $userId, $friendId, $maxIncomingId);
    $upsert->execute();
}

echo json_encode($messages);
