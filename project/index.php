<?php
session_start();

// FIX: Correct variable name to stay consistent
$errors = [
    'login' => $_SESSION['login_error'] ?? '',
    'register' => $_SESSION['register_error'] ?? ''
];

// FIX: Use the same variable name everywhere (camelCase)
$activeForm = $_SESSION['active_form'] ?? 'login';

// FIX: DO NOT unset the entire session, only remove error and form keys
unset($_SESSION['login_error'], $_SESSION['register_error'], $_SESSION['active_form']);

function showError($error) {
    return !empty($error) ? "<p class='error-message'>$error</p>" : '';
}

function isActiveForm($formName, $activeForm) {
    return $formName === $activeForm ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">

        <!-- LOGIN FORM -->
        <div class="form-box <?= isActiveForm('login', $activeForm); ?>" id="login-form">
            <form action="login_register.php" method="post">
                <h2>Login</h2>
                <?= showError($errors['login']); ?>
                <input type="email" name="email" placeholder="E-mail" class ="email-input" required>
                <input type="password" name="password" placeholder="Password" class ="password-input" required>
                <button type="submit" name="login" class="log-button">Login</button>
                <p>Don't have an account? 
                    <a href="#" onclick="showForm('register-form')">Register</a>
                </p>
            </form>
        </div>

        <!-- REGISTER FORM -->
        <div class="form-box <?= isActiveForm('register', $activeForm); ?>" id="register-form">
            <form action="login_register.php" method="POST">
                <h2>Register</h2>
                <?= showError($errors['register']); ?>
                <input type="file" name="profilePic" id="profilePic" class="file-input"  /><h4>---Upload a profile picture</h4>
                <input type="text" name="name" placeholder="Full name"  class ="name-input" required>
                <input type="email" name="email" placeholder="E-mail"  class ="email-input" required>
                <input type="password" name="password" placeholder="Password" class ="password-input"required>
                <select name="role">
                    <option value="">--Select Role--</option>
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
                
                <button type="submit" name="register" class="reg-button">Register</button>
                <p>Already have an account? 
                    <a href="#" onclick="showForm('login-form')">Login</a>
                </p>
            </form>
        </div>

    </div>

    <script src="../assets/js/script.js"></script>
</body>
</html>
