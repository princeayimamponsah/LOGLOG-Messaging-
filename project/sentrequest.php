<?php
require_once 'config.php';
$notCode = uniqid("NOT");
$useridTo = $_POST['useridTo'];
$userNameTo = $_POST['userNameTo'];
$userIdFrom = $_POST['userIdFrom'];
$userNameFrom = $_POST['userNameFrom'];

  $stmt = $conn->query("INSERT INTO user_notification (NOT_CODE, NOT_USERNAME_FROM, NOT_USERID_FROM, NOT_USERNAME_TO,NOT_USERID_TO) VALUES ('$notCode', '$userNameFrom', '$userIdFrom', '$userNameTo','$useridTo')");
              $_SESSION['register_error'] = 'Registered Succesfully';

if($stmt == true){
    echo "done";
}

?>