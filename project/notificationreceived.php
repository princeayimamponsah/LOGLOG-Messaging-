<?php
require_once 'config.php';

$useridTo = $_POST['useridTo'] ?? '';
$action = $_POST['action'] ?? 'accept';

if (empty($useridTo)){
    echo 'failed';
    exit;
}

$status = ($action === 'reject') ? '2' : '1';

$stmt = $conn->prepare("UPDATE user_notification SET NOT_STATUS = ? WHERE NOT_CODE = ?");
$stmt->bind_param('ss', $status, $useridTo);
$ok = $stmt->execute();
echo $ok ? 'done' : 'failed';

?>


