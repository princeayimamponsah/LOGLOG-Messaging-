<?php

// start session
session_start();
// Load DB connection in confing.php
require_once 'config.php';

$_SESSION['user_id'];


if (isset($_POST['register'])){
    $name =trim( $_POST['name']);
    $email =trim(  $_POST['email']);
    $password_raw =trim($_POST['password']);
    $role ='user';
  
  
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
   
        $_SESSION['register_error'] = 'Invalid email format';
        $_SESSION['active_form'] = 'register';
        header('Location: index.php');
        exit;
    }
   
    // -> Server-side email validation. If invalid, set a session "flash" error and redirect back.
    //    Using session flash + redirect implements POST/Redirect/GET so reloading doesn't resubmit the form.
  
    if (strlen($password_raw) < 6) {
        $_SESSION['register_error'] = 'Password must be at least 6 characters';
        $_SESSION['active_form'] = 'register';
        header('Location: index.php');
        exit;
    }
// check email 
    $checkEmail = $conn->query("SELECT email FROM users WHERE email = '$email'");  
    
    if($checkEmail->num_rows > 0){
       
        $_SESSION['register_error'] = 'Email is already registered';
        $_SESSION['active_form'] = 'register';
         header('Location: index.php');
         exit;

    }else{
      
    // $password = uniqid($password_raw);
 
   $conn->query("INSERT INTO users (name, email, password, role) VALUES ('$name', '$email', '$password_raw', '$role')");
              $_SESSION['register_error'] = 'Registered Succesfully';
        $_SESSION['active_form'] = 'login';
           header("Location: index.php");
            die;
        }

    }
 
    





// LOGIN
if (isset($_POST['login'])){
   
    $email = trim($_POST['email']);
    $password =trim($_POST['password']);
    
    $result = $conn->query("SELECT * FROM users WHERE email = '$email' AND password = '$password' ");
    if($result->num_rows > 0){
    $user = $result->fetch_assoc();
        // if (password_verify($password, $user['password'])){
    $_SESSION['name'] = $user['name'];
    $_SESSION['email'] = $user['email'];

    if($user['role'] === 'admin'){

        header("Location: Main.php"); 

    }else{
        
        header("Location: Main.php");
    }
   exit();
    // }

    
    }
    $_SESSION['login_error'] = 'Incorrect email or password';
    $_SESSION['active_form'] = 'login';     
    header("Location: index.php");
    exit();
}


?> 