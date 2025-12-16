<?php
session_start();
require_once 'config.php';

$notCode = uniqid("NOT");
$useridTo = $_POST['useridTo'] ?? '';
$userNameTo = $_POST['userNameTo'] ?? '';
$userIdFrom = $_POST['userIdFrom'] ?? '';
$userNameFrom = $_POST['userNameFrom'] ?? '';

if (empty($useridTo) || empty($userIdFrom)){
    echo 'failed';
    exit;
}

$stmt = $conn->prepare("INSERT INTO user_notification (NOT_CODE, NOT_USERNAME_FROM, NOT_USERID_FROM, NOT_USERNAME_TO, NOT_USERID_TO) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param('ssiss', $notCode, $userNameFrom, $userIdFrom, $userNameTo, $useridTo);
$ok = $stmt->execute();
echo $ok ? 'done' : 'failed';

?>