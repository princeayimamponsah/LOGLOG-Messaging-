<?php
session_start();
require_once "config.php";
require_once "ui.php";

if (!isset($_SESSION['userID'])) {
    header("Location: index.php");
    exit;
}

$userId = $_SESSION['userID'];
$message = '';
$messageType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_FILES['profile_pic']) || $_FILES['profile_pic']['error'] !== UPLOAD_ERR_OK) {
        $message = 'Upload failed. Please choose an image file.';
        $messageType = 'error';
    } else {
        $img = $_FILES['profile_pic'];
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $ext = strtolower(pathinfo($img['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed, true)) {
            $message = 'Invalid file type. Use jpg, jpeg, png, or gif.';
            $messageType = 'error';
        } else {
            if (!is_dir('uploads')) {
                mkdir('uploads', 0777, true);
            }

            $filename = 'user_' . $userId . '_' . time() . '.' . $ext;

            if (!move_uploaded_file($img['tmp_name'], 'uploads/' . $filename)) {
                $message = 'Failed to save file. Try again.';
                $messageType = 'error';
            } else {
                $stmt = $conn->prepare("UPDATE users SET profile_pic=? WHERE id=?");
                $stmt->bind_param('si', $filename, $userId);
                $stmt->execute();
                $_SESSION['profile_pic'] = $filename;

                $message = 'Profile image uploaded successfully.';
                $messageType = 'success';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <?php ui_render_head('Upload Profile Picture'); ?>
</head>
<body class="min-h-full bg-slate-100 font-sans text-slate-900 dark:bg-slate-950 dark:text-slate-100">
<?php include "navbar.php"; ?>

<main class="mx-3 mb-4 max-w-xl rounded-2xl border border-emerald-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900 md:mx-auto">
    <div class="mb-5 flex items-center justify-between">
        <h1 class="text-xl font-extrabold tracking-tight">Upload Profile Picture</h1>
        <?= ui_dark_toggle_button(); ?>
    </div>

    <?php if ($message !== ''): ?>
        <div class="mb-4 rounded-xl border px-4 py-3 text-sm <?php echo $messageType === 'success' ? 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300' : 'border-rose-200 bg-rose-50 text-rose-700 dark:border-rose-800 dark:bg-rose-900/30 dark:text-rose-300'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="space-y-4">
        <label class="block text-sm font-semibold">Choose an image</label>
        <input type="file" name="profile_pic" accept="image/*" required class="w-full rounded-xl border border-emerald-100 bg-slate-50 px-4 py-2 text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-emerald-600 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-white hover:file:bg-emerald-700 dark:border-slate-700 dark:bg-slate-800">

        <div class="flex items-center gap-2">
            <button type="submit" class="<?php echo ui_primary_button_classes(); ?>">Upload</button>
            <a href="settingsPage.php" class="rounded-xl border border-emerald-100 px-4 py-2 text-sm font-semibold hover:bg-emerald-50 dark:border-slate-700 dark:hover:bg-slate-800">Back to Settings</a>
        </div>
    </form>
</main>

<?php ui_darkmode_script(); ?>
</body>
</html>
