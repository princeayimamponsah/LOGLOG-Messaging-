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

if ((int) $useridTo === (int) $userIdFrom) {
    echo 'failed';
    exit;
}

$check = $conn->prepare("
    SELECT id, NOT_STATUS
    FROM user_notification
    WHERE (
        (NOT_USERID_FROM = ? AND NOT_USERID_TO = ?)
        OR
        (NOT_USERID_FROM = ? AND NOT_USERID_TO = ?)
    )
    ORDER BY id DESC
    LIMIT 1
");
$check->bind_param('iiii', $userIdFrom, $useridTo, $useridTo, $userIdFrom);
$check->execute();
$existing = $check->get_result()->fetch_assoc();

if ($existing) {
    if ($existing['NOT_STATUS'] === '2') {
        $update = $conn->prepare("
            UPDATE user_notification
            SET NOT_CODE = ?,
                NOT_USERNAME_FROM = ?,
                NOT_USERID_FROM = ?,
                NOT_USERNAME_TO = ?,
                NOT_USERID_TO = ?,
                NOT_STATUS = '0'
            WHERE id = ?
        ");
        $update->bind_param('ssissi', $notCode, $userNameFrom, $userIdFrom, $userNameTo, $useridTo, $existing['id']);
        $ok = $update->execute();
        echo $ok ? 'resent' : 'failed';
        exit;
    }

    echo 'exists';
    exit;
}

$stmt = $conn->prepare("INSERT INTO user_notification (NOT_CODE, NOT_USERNAME_FROM, NOT_USERID_FROM, NOT_USERNAME_TO, NOT_USERID_TO) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param('ssiss', $notCode, $userNameFrom, $userIdFrom, $userNameTo, $useridTo);
$ok = $stmt->execute();
echo $ok ? 'done' : 'failed';

?>