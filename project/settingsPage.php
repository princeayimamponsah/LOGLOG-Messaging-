<?php
session_start();
require_once "config.php";

if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit;
}

include "navbar.php";

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <div class="row">

        
        <div class="col-sm-2"> </div>
        <?php

        $user = $_SESSION['email'];
        $get_user = "SELECT * FROM users WHERE email = '$user'";
        $run_user = mysqli_query($conn, $get_user);
        $row = mysqli_fetch_array($run_user);

   
        $name = $row['name']    ;
        $password = $row['password'] ;
        $email = $row['email'] ;
        $role = $row['role'] ;
        $id = $row['id'];
        

        ?>


        <div class="col-sm-8">
            <form action="" method="post" enctype="multipart/form-data">
               <table class="table  table-bordered table-hover">
                <tr align-="center">
                    <td colspan="6" class="active"><h2>Change Account Settings</h2>


                   

                    </td>
                </tr>
                <tr>
                    <td style="font weight:bold;">
                    Change Your Username
                    </td>
                    <td>
                        <input type="text" name="name" class="form-control" required value="<?php echo $name;?>" />
                    </td>
                </tr>
                <tr>
                    <td><td><a class="btn btn-default" style="text-decoration: none; font-size:15px;" href="upload.php "><i class="fa fa-user" aria-hidden="true"></i>Change Profile Picture</a></td></td>
                </tr>

                <tr>
                    <td style="font weight:bold;">
                    Change Your Email
                    </td>
                    <td>
                        <input type="email" name="email" class="form-control" required value="<?php echo $email;?>" />
                    </td>
                </tr>
                <tr>
                    <td style="font weight:bold;">Forgotten Password </td>
                    <td>
                       <button type="button" class="btn btn-default" data-target="#myModal"> Forgotten Password</button>

                       <div id="myModal" class="modal fade" role="dialog"> 
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button " class="close" data-dismiss="modal">&times;</button>

                                </div>
                                <div class="modal-body">
                                    <form action="recovery.php?id-<?php echo $id ?>" method="post" id="f">
                                        <strong>What is School Best Friend Name </strong>
                                        <textarea class="form-control" cols="83" rows="4" name="content" placeholder="Someone" ></textarea><br>
                                        <input type="submit" class="btn btn-default" name="sub" value="Submit " style="width: 100px;"><br><br>
                                        <pre></pre>
                                    </form>
                                </div> 
                            </div>
                        </div>
                       </div>
                   
                    </td>
                </tr>

            


                

               </table>

        </form>
        </div>


            
            
        
       
    </div>


</body>
</html>