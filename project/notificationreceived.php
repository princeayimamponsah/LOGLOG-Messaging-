<?php
require_once 'config.php';
$notCode = uniqid("NOT");
$useridTo = $_POST['useridTo'];
// var_dump ($useridTo);
// exit;

  $stmt = $conn->query("UPDATE user_notification SET NOT_STATUS = '1'  WHERE NOT_CODE = '$useridTo' ");

              // $_SESSION['register_error'] = 'Registered Succesfully';

if($stmt == true){
    echo "done";
}else{
  echo "failed";
}

?>

<?php
require_once 'config.php';
$notCode = uniqid("NOT");
$useridTo = $_POST['useridTo'];
// var_dump ($useridTo);
// exit;

  $stmt = $conn->query("UPDATE user_notification SET NOT_STATUS = '2'  WHERE NOT_CODE = '$useridTo' ");

              // $_SESSION['register_error'] = 'Registered Succesfully';

if($stmt == true){
    echo "done";
}else{
  echo "failed";
}

?>


