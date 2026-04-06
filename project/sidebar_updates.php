<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['userID'])) {
    echo json_encode([]);
    exit;
}

$userId = (int) $_SESSION['userID'];

$stmt = $conn->prepare("
    SELECT
        u.id AS friend_id,
        (
            SELECT m.message
            FROM messages m
            WHERE ((m.sender_id = ? AND m.receiver_id = u.id) OR (m.sender_id = u.id AND m.receiver_id = ?))
            ORDER BY m.sent_at DESC, m.id DESC
            LIMIT 1
        ) AS last_message,
        (
            SELECT m.sent_at
            FROM messages m
            WHERE ((m.sender_id = ? AND m.receiver_id = u.id) OR (m.sender_id = u.id AND m.receiver_id = ?))
            ORDER BY m.sent_at DESC, m.id DESC
            LIMIT 1
        ) AS last_sent_at,
        (
            SELECT m.sender_id
            FROM messages m
            WHERE ((m.sender_id = ? AND m.receiver_id = u.id) OR (m.sender_id = u.id AND m.receiver_id = ?))
            ORDER BY m.sent_at DESC, m.id DESC
            LIMIT 1
        ) AS last_sender_id,
        (
            SELECT COUNT(*)
            FROM messages m
            WHERE m.sender_id = u.id
              AND m.receiver_id = ?
              AND m.id > COALESCE((
                    SELECT cs.last_read_id
                    FROM chat_state cs
                    WHERE cs.user_id = ?
                      AND cs.friend_id = u.id
                    LIMIT 1
              ), 0)
        ) AS unread_count
    FROM user_notification un
    JOIN users u ON u.id = IF(un.NOT_USERID_FROM = ?, un.NOT_USERID_TO, un.NOT_USERID_FROM)
    WHERE un.NOT_STATUS = '1'
      AND (un.NOT_USERID_FROM = ? OR un.NOT_USERID_TO = ?)
");
$stmt->bind_param(
    "iiiiiiiiiii",
    $userId,
    $userId,
    $userId,
    $userId,
    $userId,
    $userId,
    $userId,
    $userId,
    $userId,
    $userId,
    $userId
);
$stmt->execute();
$res = $stmt->get_result();

$items = [];
while ($row = $res->fetch_assoc()) {
    $lastMessageRaw = trim((string) ($row['last_message'] ?? ''));
    $lastSenderId = isset($row['last_sender_id']) ? (int) $row['last_sender_id'] : 0;
    $preview = 'No messages yet';
    if ($lastMessageRaw !== '') {
        $preview = ($lastSenderId === $userId ? 'You: ' : '') . $lastMessageRaw;
    }

    $lastTime = '--:--';
    $sortValue = 0;
    if (!empty($row['last_sent_at'])) {
        $lastTime = date('h:i A', strtotime($row['last_sent_at']));
        $sortValue = (int) strtotime($row['last_sent_at']);
    }

    $items[] = [
        'friend_id' => (int) $row['friend_id'],
        'preview' => $preview,
        'time' => $lastTime,
        'sort_value' => $sortValue,
        'unread_count' => (int) ($row['unread_count'] ?? 0),
    ];
}

echo json_encode($items);
