<?php

session_start();
require_once "config.php";
include "navbar.php";

$loggedInId = $_SESSION['userID'];
$loggedInName = $_SESSION['name'];
// var_dump($loggedInId,$loggedInName);

$stmt = $conn->prepare("SELECT * FROM user_notification WHERE NOT_USERID_TO = ? AND NOT_STATUS = '0' ");
$stmt->bind_param("i", $loggedInId); // "i" = integer
$stmt->execute();
$details = $stmt->get_result();



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Requests</title>
     <link rel="stylesheet" href="../assets/css/style.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<div class="request">
<h1>NOTIFICATIONS</h1>

<table>
    <tr>
        <th>Username</th>
        <th >Action</th>
    </tr>
     <?php if($details && $details->num_rows > 0): ?>
        <?php while($user = $details->fetch_assoc()): ?>
    <tr>
         <td><?= htmlspecialchars($user['NOT_USERNAME_FROM']); ?></td>
   
   <td>
    <button class="accept-btn" onclick="acceptBtn('<?php echo $user['NOT_CODE']  ?>')">Accept</button>
   <button class="reject-btn" onclick="rejectBtn('<?php echo $user['NOT_CODE'] ?>')">Reject</button>
</td>
</tr>   
 <?php endwhile; ?>
    <?php else: ?>
        <tr>
            <td colspan="2">No users found.</td>
        </tr>
    <?php endif; ?>
</table>
</div>
   </div>

   
</body>
</html>

<script>
    function acceptBtn(idTo){
        // var reqBtn = document.getElementById('sentBtn');
        $.ajax({
            type:"POST",
            data: {"useridTo":idTo,
                    
             },
            url:"notificationreceived.php",
            success: function(accept){
                if(accept == "done"){
                    
                    window.location.reload();

                }
            }
        });
   
    }


    function rejectBtn(idTo){

        $.ajax({
            type:"POST",
            data:{"useridTo":idTo,

            },
            url:"notificationreceived.php",
            succes: function(reject){
                if(reject == "done"){
                    window.location.reload();

                }

            }
        }

        )
    }
</script>

