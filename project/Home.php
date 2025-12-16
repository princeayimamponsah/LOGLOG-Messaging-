<?php



session_start();
require_once "config.php";
include "navbar.php";
if(!isset($_SESSION['email'])){
    header("Location: index.php");
    exit();
}


$loggedInId = $_SESSION['userID'];


$acceptReq = $conn->query("
    SELECT 
        un.NOT_USERNAME_FROM,
        u.profile_pic
    FROM user_notification AS un
    JOIN users AS u 
        ON u.id = un.NOT_USERID_FROM
    WHERE un.NOT_STATUS = '2'
      AND un.NOT_USERID_TO = '$loggedInId'

");

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
<body style="background:#fbffc0;">
    
       
     <div class="dashboard">
        <div class="sidebar" id="sidebar-users" >
            <table >
                <thead>
                <tr><th>Chats</th></tr>
                </thead>

                <tbody>
                
                    
                       <?php while ($row = $acceptReq->fetch_assoc()): ?>
    <tr class="chat-item"
        data-username="<?php echo $row['NOT_USERNAME_FROM']; ?>"
        data-pic="uploads/<?php echo $row['profile_pic']; ?>">
        <td>
            <div class="chat-user">
                <img src="uploads/<?php echo $row['profile_pic']; ?>" class="user-pic" alt="user">
                <span><?php echo htmlspecialchars($row['NOT_USERNAME_FROM']); ?></span>
            </div>
        </td>
    </tr>
<?php endwhile; ?>

</tbody>
            </table>
         </div>
<div class="chats">

   
    <div class="chat-header">
        <div class="nav-chat-act">
             <img src="" id="chatHeaderPic" class="chat-header-pic" alt="chat user">
            <h4 id="chatHeaderName">LogLog</h4>
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
        <input type="text" id="messageInput" placeholder="Type a message...">
        <i class='bx bx-send send-btn' id="sendBtn"></i>
        
    </div>

</div>

<script>
  
  const chatHeaderName = document.getElementById("chatHeaderName");
const chatHeaderPic = document.getElementById("chatHeaderPic");

const sidebarUsers = document.querySelectorAll(".chat-item");

sidebarUsers.forEach(item => {
    item.addEventListener("click", function() {
        const username = this.dataset.username;
        const picture = this.dataset.pic;

        chatHeaderName.textContent = username;
        chatHeaderPic.src = picture;
    });
});

const chatBody = document.querySelector(".chat-body");

sidebarUsers.forEach(item => {
    item.addEventListener("click", function() {
        
        const username = this.dataset.username;
        const picture = this.dataset.pic;
        const userId = this.dataset.userid;

        chatHeaderName.textContent = username;
        chatHeaderPic.src = picture;

            
    });
});


</script>

    
</body>
</html>