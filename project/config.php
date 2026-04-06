<?php
$host = "localhost";
$user = "root";
$password = "";
$database = "users_db";

$conn = new mysqli($host, $user, $password);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->query("CREATE DATABASE IF NOT EXISTS `$database`");
$conn->select_db($database);

$conn->query("CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'user',
    profile_pic VARCHAR(255) DEFAULT ''
)");

$conn->query("CREATE TABLE IF NOT EXISTS user_notification (
    id INT AUTO_INCREMENT PRIMARY KEY,
    NOT_CODE VARCHAR(50) NOT NULL UNIQUE,
    NOT_USERNAME_FROM VARCHAR(100),
    NOT_USERID_FROM INT,
    NOT_USERNAME_TO VARCHAR(100),
    NOT_USERID_TO INT,
    NOT_STATUS CHAR(1) DEFAULT '0'
)");

$conn->query("CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    message TEXT NOT NULL,
    sent_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

$conn->query("CREATE TABLE IF NOT EXISTS chat_state (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    friend_id INT NOT NULL,
    last_read_id INT DEFAULT 0,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_user_friend (user_id, friend_id)
)");
?>
