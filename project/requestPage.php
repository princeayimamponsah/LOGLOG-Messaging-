<?php

session_start();
require_once "config.php";
include "navbar.php";

$loggedInId = $_SESSION['userID'];
$loggedInName = $_SESSION['name'];

$stmt = $conn->prepare("SELECT * FROM users WHERE id != ?");
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
<h1>REQUESTS</h1>

<table>
    <tr>
        <th>Username</th>
        <th >Action</th>
    </tr>
     <?php if($details && $details->num_rows > 0): ?>
        <?php while($user = $details->fetch_assoc()): ?>
    <tr>
         <td><?= htmlspecialchars($user['name']); ?></td>
   
   <td>
    <button class="accept-btn" id="reqBtn<?php echo $user['id']; ?>" onclick="sendReq('<?php echo $user['id']; ?>','<?php echo $user['name']; ?>','<?php echo $loggedInId; ?>','<?php echo $loggedInName; ?>')"> Send Request</button>
    <button type="button" class="accept-btn" style="display:none" id="sentBtn<?php echo $user['id']; ?>" disabled>Request sent</button>
   <!-- <button class="reject-btn">Reject</button> -->
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
    function sendReq(idTo,nameTo,idFrom,nameFrom){
        // var reqBtn = document.getElementById('sentBtn');
        $.ajax({
            type:"POST",
            data: {"useridTo":idTo,
                    "userNameTo":nameTo,
                    "userIdFrom":idFrom,
                    "userNameFrom":nameFrom
             },
            url:"sentrequest.php",
            success: function(res){
                if(res == "done"){
                    // alert("we dey");
                       $('#sentBtn' + idTo).css("display", "block"); 
                       $('#reqBtn' + idTo).css("display", "none"); 
                }else{
                    alert("komot");
                }
            }
        });
   
    }
</script>

