<?php

session_start();
require_once "config.php";
include "navbar.php";
if(!isset($_SESSION['email'])){
    header("Location: index.php");
    exit();
}
$loggedInId = $_SESSION['userID'];


$acceptReq = $conn->query("SELECT * FROM user_notification WHERE NOT_STATUS = '2'  AND NOT_USERID_TO = '$loggedInId'");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Page</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


</head>
<body  background:#fbffc0 >
    
       
     <div class="dashboard">
        <div class="sidebar" id="sidebar-users" >
            <table >
                <th>Chats</th>
                
                <tr >
                    <td>
                        <?php
                    while ($row = $acceptReq->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['NOT_USERNAME_FROM'] . "</td>";
    echo "</tr>";
                    }
    ?>
    </td>
      </tr>
            </table>
         </div>
<div class="chats">

   
    <div class="chat-header">
        <div class="nav-chat-act">
            <h4>LogLog</h4>
            <p>online</p>
        </div>
        <div class="nav-chat-icons">
            <i class='bx bx-video'></i>
            <i class='bx bx-phone'></i>
            
        </div>
    </div>
    

  
    <div class="chat-body" >
     
    </div>


    <div class="chat-input">
        <i class='bx bx-plus add-btn'></i>
        <input type="text" placeholder="Type a message...">
        <i class='bx bx-send send-btn'></i>
        
    </div>

</div>

<script>
  
  const sidebarUsers = document.querySelectorAll("#sidebar-users td");

 
  sidebarUsers.forEach(td => {
    td.addEventListener("click", function() {
      const clickedUsername = this.textContent;
      const chatHeader = document.querySelector(".chat-header h4");
      chatHeader.textContent = clickedUsername;
    });
  });
</script>

    
</body>
</html>