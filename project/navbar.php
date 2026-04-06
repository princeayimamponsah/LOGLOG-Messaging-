<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$profileFile = $_SESSION['profile_pic'] ?? '';
if (($profileFile === '' || $profileFile === null) && isset($conn) && isset($_SESSION['userID'])) {
    $navStmt = $conn->prepare("SELECT profile_pic FROM users WHERE id = ? LIMIT 1");
    if ($navStmt) {
        $navStmt->bind_param("i", $_SESSION['userID']);
        $navStmt->execute();
        $navRes = $navStmt->get_result();
        $navRow = $navRes ? $navRes->fetch_assoc() : null;
        $profileFile = $navRow['profile_pic'] ?? '';
        $_SESSION['profile_pic'] = $profileFile;
    }
}

$navProfilePic = !empty($profileFile)
    ? "uploads/" . rawurlencode($profileFile)
    : "https://ui-avatars.com/api/?name=" . rawurlencode($_SESSION['name'] ?? 'User') . "&background=ddf4e5&color=0f5132";

$pendingNotifications = 0;
if (isset($conn) && isset($_SESSION['userID'])) {
    $notiStmt = $conn->prepare("SELECT COUNT(*) AS total FROM user_notification WHERE NOT_USERID_TO = ? AND NOT_STATUS = '0'");
    if ($notiStmt) {
        $notiStmt->bind_param("i", $_SESSION['userID']);
        $notiStmt->execute();
        $notiRes = $notiStmt->get_result();
        $notiRow = $notiRes ? $notiRes->fetch_assoc() : null;
        $pendingNotifications = (int) ($notiRow['total'] ?? 0);
    }
}
?>

<nav class="mx-3 mt-4 mb-3 rounded-2xl border border-emerald-200 bg-white/90 px-4 py-3 shadow-sm backdrop-blur dark:border-slate-700 dark:bg-slate-900/90 md:mx-5 md:px-6">
    <div class="flex flex-wrap items-center gap-3 md:gap-4">
        <a href="../project/Home.php" class="inline-flex items-center gap-2 rounded-xl px-2 py-1 text-emerald-700 transition hover:bg-emerald-50 dark:text-emerald-300 dark:hover:bg-slate-800">
            <img src="../assets/Lift_London_logo-1.svg" alt="LOGLOG logo" class="h-8 w-8 object-contain md:h-9 md:w-9">
            <h1 class="text-lg font-extrabold tracking-tight md:text-xl">LOGLOG</h1>
        </a>

        <div class="hidden h-8 w-px bg-emerald-100 dark:bg-slate-700 sm:block"></div>

        <p class="text-sm text-slate-600 dark:text-slate-300 md:text-base">
            Welcome,
            <span class="font-semibold text-slate-900 dark:text-white"><?= htmlspecialchars($_SESSION['name'] ?? ''); ?></span>
        </p>

        <div class="ml-auto flex items-center gap-2">
            <img src="<?= htmlspecialchars($navProfilePic); ?>" alt="Profile" class="h-10 w-10 rounded-full border border-emerald-200 object-cover dark:border-slate-700">

            <a href="../project/requestPage.php" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-700 transition hover:-translate-y-0.5 hover:bg-emerald-100 dark:border-slate-700 dark:bg-slate-800 dark:text-emerald-300">
                <i class='bx bx-user-plus text-xl'></i>
            </a>

            <a href="../project/notifications.php" class="relative inline-flex h-10 w-10 items-center justify-center rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-700 transition hover:-translate-y-0.5 hover:bg-emerald-100 dark:border-slate-700 dark:bg-slate-800 dark:text-emerald-300">
                <i class='bx bx-message-minus text-xl'></i>
                <?php if ($pendingNotifications > 0): ?>
                    <span class="absolute -right-1 -top-1 inline-flex min-h-5 min-w-5 items-center justify-center rounded-full bg-rose-500 px-1 text-[10px] font-bold text-white">
                        <?= $pendingNotifications > 99 ? '99+' : $pendingNotifications; ?>
                    </span>
                <?php endif; ?>
            </a>

            <a href="../project/settingsPage.php" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-700 transition hover:-translate-y-0.5 hover:bg-emerald-100 dark:border-slate-700 dark:bg-slate-800 dark:text-emerald-300">
                <i class='bx bx-cog text-xl'></i>
            </a>

            <button onclick="window.location.href='logout.php'" class="rounded-xl bg-rose-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-rose-600">Logout</button>
        </div>
    </div>
</nav>
        