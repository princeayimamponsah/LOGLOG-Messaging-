<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?>

<nav class="navbar">
    <div class="nav-left">
        <a href="../project/Home.php">
            <i class='bx bx-message logo-icon'></i>
        </a>
         <a href="../project/Home.php">
        <h1 class="logo">LOGLOG</h1>
         </a>
    </div>

    <div class="box">
        <h1>Welcome,
            <span><?= htmlspecialchars($_SESSION['name'] ?? ''); ?></span>
        </h1>
    </div>

    <div class="nav-right">
        <a href="../project/requestPage.php">
            <i class='bx bx-user-plus add-btn'></i>
        </a>

        <a href="../project/notifications.php">
            <i class='bx bx-message-minus message-btn'></i>
        </a>

        <a href="../project/settingsPage.php">
            <i class='bx bx-cog settings-btn'></i>
        </a>

        <button onclick="window.location.href='logout.php'" class="logout-btn">Logout</button>
    </div>
</nav>
        