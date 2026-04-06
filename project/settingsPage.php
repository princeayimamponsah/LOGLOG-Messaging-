<?php
session_start();
require_once "config.php";
require_once "ui.php";

if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit;
}

$userEmail = $_SESSION['email'];
$getUser = "SELECT * FROM users WHERE email = '$userEmail'";
$runUser = mysqli_query($conn, $getUser);
$row = mysqli_fetch_array($runUser);

$name = $row['name'];
$email = $row['email'];
$role = $row['role'];
$id = $row['id'];
?>
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <?php ui_render_head('Settings'); ?>
</head>
<body class="min-h-full bg-slate-100 font-sans text-slate-900 dark:bg-slate-950 dark:text-slate-100">
<?php include "navbar.php"; ?>

<main class="mx-3 mb-4 max-w-4xl rounded-2xl border border-emerald-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900 md:mx-auto md:p-6">
    <div class="mb-5 flex items-center justify-between">
        <h1 class="text-xl font-extrabold tracking-tight md:text-2xl">Account Settings</h1>
        <?= ui_dark_toggle_button(); ?>
    </div>

    <form action="" method="post" enctype="multipart/form-data" class="space-y-4">
        <div class="<?php echo ui_card_classes(); ?>">
            <label class="mb-2 block text-sm font-semibold">Change Your Username</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($name); ?>" required class="<?php echo ui_input_classes(); ?>">
        </div>

        <div class="<?php echo ui_card_classes(); ?>">
            <label class="mb-2 block text-sm font-semibold">Change Profile Picture</label>
            <a href="upload.php" class="inline-flex items-center gap-2 <?php echo ui_primary_button_classes(); ?>">
                <i class='bx bx-image-add text-base'></i>
                Open Upload
            </a>
        </div>

        <div class="<?php echo ui_card_classes(); ?>">
            <label class="mb-2 block text-sm font-semibold">Change Your Email</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required class="<?php echo ui_input_classes(); ?>">
        </div>

        <div class="<?php echo ui_card_classes(); ?>">
            <p class="mb-3 text-sm font-semibold">Forgotten Password</p>
            <div class="rounded-xl bg-slate-50 p-3 text-sm text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                Recovery prompt is currently disabled in this build.
            </div>
        </div>

        <div class="<?php echo ui_card_classes(); ?>">
            <p class="text-sm text-slate-500 dark:text-slate-400">Role: <span class="font-semibold text-slate-900 dark:text-slate-100"><?php echo htmlspecialchars($role); ?></span></p>
            <p class="mt-1 text-xs text-slate-400">User ID: <?php echo (int) $id; ?></p>
        </div>
    </form>
</main>

<?php ui_darkmode_script(); ?>
</body>
</html>
