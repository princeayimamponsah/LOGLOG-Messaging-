<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['userID'])) {
    echo 'error';
    exit;
}

$senderId   = $_SESSION['userID'];
$receiverId = intval($_POST['receiver_id'] ?? 0);
$message    = trim($_POST['message'] ?? '');

if (!$receiverId || $message === '') {
    echo 'error';
    exit;
}

$stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $senderId, $receiverId, $message);
echo $stmt->execute() ? 'done' : 'error';
