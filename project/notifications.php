<?php
session_start();
require_once "config.php";
require_once "ui.php";

if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}

$loggedInId = $_SESSION['userID'];

$stmt = $conn->prepare("SELECT * FROM user_notification WHERE NOT_USERID_TO = ? AND NOT_STATUS = '0'");
$stmt->bind_param("i", $loggedInId);
$stmt->execute();
$details = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <?php ui_render_head('Notifications', ['jquery' => true]); ?>
</head>
<body class="min-h-full bg-slate-100 font-sans text-slate-900 dark:bg-slate-950 dark:text-slate-100">
<?php include "navbar.php"; ?>

<main class="mx-3 mb-4 rounded-2xl border border-emerald-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900 md:mx-5 md:p-6">
    <div class="mb-5 flex items-center justify-between">
        <h1 class="text-xl font-extrabold tracking-tight md:text-2xl">Notifications</h1>
        <?= ui_dark_toggle_button(); ?>
    </div>

    <div class="overflow-hidden rounded-xl border border-emerald-100 dark:border-slate-700">
        <table class="min-w-full divide-y divide-emerald-100 text-sm dark:divide-slate-700">
            <thead class="bg-emerald-50 dark:bg-slate-800">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold">Username</th>
                    <th class="px-4 py-3 text-left font-semibold">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-emerald-100 dark:divide-slate-700">
            <?php if ($details && $details->num_rows > 0): ?>
                <?php while ($user = $details->fetch_assoc()): ?>
                    <tr class="hover:bg-emerald-50/70 dark:hover:bg-slate-800/80">
                        <td class="px-4 py-3 font-medium"><?= htmlspecialchars($user['NOT_USERNAME_FROM']); ?></td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-2">
                                <button class="<?php echo ui_primary_button_classes('rounded-lg px-3 py-2 text-xs'); ?>" onclick="acceptBtn('<?php echo $user['NOT_CODE']; ?>')">Accept</button>
                                <button class="rounded-lg bg-rose-500 px-3 py-2 text-xs font-semibold text-white hover:bg-rose-600" onclick="rejectBtn('<?php echo $user['NOT_CODE']; ?>')">Reject</button>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="2" class="px-4 py-10 text-center text-slate-500 dark:text-slate-400">No users found.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<script>
function acceptBtn(idTo) {
    $.ajax({
        type: "POST",
        url: "notificationreceived.php",
        data: { useridTo: idTo, action: 'accept' },
        success: function(res) {
            if (res === "done") {
                window.location.reload();
            }
        }
    });
}

function rejectBtn(idTo) {
    $.ajax({
        type: "POST",
        url: "notificationreceived.php",
        data: { useridTo: idTo, action: 'reject' },
        success: function(res) {
            if (res === "done") {
                window.location.reload();
            }
        }
    });
}

</script>
<?php ui_darkmode_script(); ?>
</body>
</html>
