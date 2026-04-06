<?php
session_start();
require_once "ui.php";

// FIX: Correct variable name to stay consistent
$errors = [
    'login' => $_SESSION['login_error'] ?? '',
    'register' => $_SESSION['register_error'] ?? ''
];

// FIX: Use the same variable name everywhere (camelCase)
$activeForm = $_SESSION['active_form'] ?? 'login';

// Allow direct link switching even if JS fails
$queryForm = $_GET['form'] ?? '';
if ($queryForm === 'login' || $queryForm === 'register') {
    $activeForm = $queryForm;
}

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
    <?php ui_render_head('LOGLOG Auth', ['boxicons' => false]); ?>
</head>
<body class="min-h-screen bg-slate-950 font-sans text-slate-100">
    <div class="relative isolate min-h-screen overflow-hidden px-4 py-10 sm:px-6 lg:px-8">
        <div class="pointer-events-none absolute inset-0 -z-10 bg-[radial-gradient(circle_at_20%_20%,rgba(16,185,129,0.28),transparent_36%),radial-gradient(circle_at_80%_0%,rgba(56,189,248,0.2),transparent_30%),radial-gradient(circle_at_50%_100%,rgba(14,116,144,0.18),transparent_36%)]"></div>

        <div class="mx-auto w-full max-w-md rounded-3xl border border-white/10 bg-white/10 p-6 shadow-2xl backdrop-blur-xl sm:p-8">
            <div class="mb-6 text-center">
                <div class="flex items-center justify-center gap-2">
                    <img src="../assets/Lift_London_logo-1.svg" alt="LOGLOG logo" class="h-9 w-9 object-contain sm:h-10 sm:w-10">
                    <h1 class="text-3xl font-extrabold tracking-tight">LOGLOG</h1>
                </div>
                <p class="mt-2 text-sm text-slate-300">Secure messaging access</p>
            </div>

        <!-- LOGIN FORM -->
        <div class="form-box <?= isActiveForm('login', $activeForm); ?> <?= isActiveForm('login', $activeForm) ? '' : 'hidden'; ?>" id="login-form">
            <form action="login_register.php" method="post" >
                <h2 class="mb-5 text-center text-2xl font-bold">Login</h2>
                <?= showError($errors['login']); ?>
                <input type="email" name="email" placeholder="E-mail" class ="mb-3 w-full rounded-xl border border-white/20 bg-white/10 px-4 py-3 text-sm text-white placeholder:text-slate-300 focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-400/40" required>
                <input type="password" name="password" placeholder="Password" class ="mb-4 w-full rounded-xl border border-white/20 bg-white/10 px-4 py-3 text-sm text-white placeholder:text-slate-300 focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-400/40" required>
                <button type="submit" name="login" class="w-full rounded-xl bg-emerald-500 px-4 py-3 text-sm font-semibold text-white transition hover:bg-emerald-600">Login</button>
                <p class="mt-4 text-center text-sm text-slate-300">Don't have an account?
                    <a href="index.php?form=register" data-show-form="register-form" class="font-semibold text-emerald-300 hover:text-emerald-200">Register</a>
                </p>
            </form>
        </div>

        <!-- REGISTER FORM -->
        <div class="form-box <?= isActiveForm('register', $activeForm); ?> <?= isActiveForm('register', $activeForm) ? '' : 'hidden'; ?>" id="register-form">
            <form action="login_register.php" method="POST"  enctype="multipart/form-data">
                <h2 class="mb-5 text-center text-2xl font-bold">Register</h2>
                <?= showError($errors['register']); ?>
                <input type="text" name="name" placeholder="Full name"  class ="mb-3 w-full rounded-xl border border-white/20 bg-white/10 px-4 py-3 text-sm text-white placeholder:text-slate-300 focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-400/40" required>
                <input type="email" name="email" placeholder="E-mail"  class ="mb-3 w-full rounded-xl border border-white/20 bg-white/10 px-4 py-3 text-sm text-white placeholder:text-slate-300 focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-400/40" required>
                <input type="password" name="password" placeholder="Password" class ="mb-3 w-full rounded-xl border border-white/20 bg-white/10 px-4 py-3 text-sm text-white placeholder:text-slate-300 focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-400/40" required>
                <select name="role" class="mb-4 w-full rounded-xl border border-white/20 bg-white/10 px-4 py-3 text-sm text-white focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-400/40">
                    <option value="">--Select Role--</option>
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
                
                <button type="submit" name="register" class="w-full rounded-xl bg-cyan-500 px-4 py-3 text-sm font-semibold text-white transition hover:bg-cyan-600">Register</button>
                <p class="mt-4 text-center text-sm text-slate-300">Already have an account?
                    <a href="index.php?form=login" data-show-form="login-form" class="font-semibold text-cyan-300 hover:text-cyan-200">Login</a>
                </p>
            </form>
        </div>

        </div>
    </div>

    <style>
        .error-message {
            margin-bottom: 12px;
            border-radius: 12px;
            border: 1px solid rgba(248, 113, 113, 0.5);
            background: rgba(127, 29, 29, 0.35);
            padding: 10px 12px;
            font-size: 14px;
            color: #fecaca;
            text-align: center;
        }
    </style>

    <script src="../assets/js/script.js"></script>
</body>
</html>
