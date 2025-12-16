<?php
session_start();
require_once 'config.php';

// Helper to set flash and redirect
function redirect_with($key, $message, $form = 'login'){
    $_SESSION[$key] = $message;
    $_SESSION['active_form'] = $form;
    header('Location: index.php');
    exit;
}

if (isset($_POST['register'])){
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password_raw = trim($_POST['password'] ?? '');
    $role = 'user';
    $profile = '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        redirect_with('register_error', 'Invalid email format', 'register');
    }

    if (strlen($password_raw) < 6) {
        redirect_with('register_error', 'Password must be at least 6 characters', 'register');
    }

    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        redirect_with('register_error', 'Email is already registered', 'register');
    }
    $stmt->close();

    $hashed_password = password_hash($password_raw, PASSWORD_DEFAULT);
    $ins = $conn->prepare("INSERT INTO users (name, email, password, role, profile_pic) VALUES (?, ?, ?, ?, ?)");
    $ins->bind_param('sssss', $name, $email, $hashed_password, $role, $profile);
    if ($ins->execute()){
        redirect_with('register_error', 'Registered Successfully', 'login');
    } else {
        redirect_with('register_error', 'Registration failed', 'register');
    }
}

if (isset($_POST['login'])){
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    $stmt = $conn->prepare("SELECT id, name, email, password, role FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $res->num_rows > 0) {
        $user = $res->fetch_assoc();
        $dbpass = $user['password'];
        $verified = false;
        if (password_verify($password, $dbpass)) $verified = true;
        // Backwards-compatible: allow plaintext passwords if present
        if (!$verified && $password === $dbpass) $verified = true;

        if ($verified) {
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['userID'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            header('Location: Home.php');
            exit();
        }
    }
    redirect_with('login_error', 'Incorrect email or password', 'login');
}

?>