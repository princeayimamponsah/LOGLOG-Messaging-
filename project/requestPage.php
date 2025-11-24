<?php

session_start();
require_once "config.php";
include "navbar.php";

$loggedInId = $_SESSION['name'];

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
    <button class="accept-btn">Accept</button>
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

    <script src='../assets/js/script.js'></script>
</body>
</html>

