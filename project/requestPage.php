<?php
session_start();
require_once "config.php";
require_once "ui.php";

if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}

$loggedInId = $_SESSION['userID'];
$loggedInName = $_SESSION['name'];

$stmt = $conn->prepare("
    SELECT u.*
    FROM users u
    WHERE u.id != ?
      AND NOT EXISTS (
          SELECT 1
          FROM user_notification un
          WHERE (
              (un.NOT_USERID_FROM = ? AND un.NOT_USERID_TO = u.id)
              OR
              (un.NOT_USERID_FROM = u.id AND un.NOT_USERID_TO = ?)
          )
          AND un.NOT_STATUS IN ('0', '1')
      )
    ORDER BY u.name ASC
");
$stmt->bind_param("iii", $loggedInId, $loggedInId, $loggedInId);
$stmt->execute();
$details = $stmt->get_result();

$declinedStmt = $conn->prepare("
    SELECT DISTINCT u.id, u.name
    FROM users u
    JOIN user_notification un
      ON (
          (un.NOT_USERID_FROM = ? AND un.NOT_USERID_TO = u.id)
          OR
          (un.NOT_USERID_FROM = u.id AND un.NOT_USERID_TO = ?)
      )
    WHERE u.id != ?
      AND un.NOT_STATUS = '2'
      AND NOT EXISTS (
          SELECT 1
          FROM user_notification un2
          WHERE (
              (un2.NOT_USERID_FROM = ? AND un2.NOT_USERID_TO = u.id)
              OR
              (un2.NOT_USERID_FROM = u.id AND un2.NOT_USERID_TO = ?)
          )
          AND un2.NOT_STATUS IN ('0', '1')
      )
    ORDER BY u.name ASC
");
$declinedStmt->bind_param("iiiii", $loggedInId, $loggedInId, $loggedInId, $loggedInId, $loggedInId);
$declinedStmt->execute();
$declinedDetails = $declinedStmt->get_result();
?>
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <?php ui_render_head('Requests', ['jquery' => true]); ?>
</head>
<body class="min-h-full bg-slate-100 font-sans text-slate-900 dark:bg-slate-950 dark:text-slate-100">
<?php include "navbar.php"; ?>

<main class="mx-3 mb-4 rounded-2xl border border-emerald-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900 md:mx-5 md:p-6">
    <div class="mb-5 flex items-center justify-between">
        <h1 class="text-xl font-extrabold tracking-tight md:text-2xl">Requests</h1>
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
                        <td class="px-4 py-3 font-medium"><?php echo htmlspecialchars($user['name']); ?></td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-2">
                                <button class="<?php echo ui_primary_button_classes('rounded-lg px-3 py-2 text-xs'); ?>" id="reqBtn<?php echo $user['id']; ?>" onclick="sendReq('<?php echo $user['id']; ?>','<?php echo htmlspecialchars($user['name'], ENT_QUOTES); ?>','<?php echo $loggedInId; ?>','<?php echo htmlspecialchars($loggedInName, ENT_QUOTES); ?>')">Send Request</button>
                                <button type="button" class="hidden rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700 dark:border-slate-700 dark:bg-slate-800 dark:text-emerald-300" id="sentBtn<?php echo $user['id']; ?>" disabled>Request sent</button>
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

    <div class="mt-6">
        <h2 class="mb-3 text-lg font-bold tracking-tight">Declined Requests</h2>
        <div class="overflow-hidden rounded-xl border border-emerald-100 dark:border-slate-700">
            <table class="min-w-full divide-y divide-emerald-100 text-sm dark:divide-slate-700">
                <thead class="bg-emerald-50 dark:bg-slate-800">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold">Username</th>
                        <th class="px-4 py-3 text-left font-semibold">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-emerald-100 dark:divide-slate-700">
                <?php if ($declinedDetails && $declinedDetails->num_rows > 0): ?>
                    <?php while ($user = $declinedDetails->fetch_assoc()): ?>
                        <tr class="hover:bg-emerald-50/70 dark:hover:bg-slate-800/80">
                            <td class="px-4 py-3 font-medium"><?php echo htmlspecialchars($user['name']); ?></td>
                            <td class="px-4 py-3">
                                <button class="<?php echo ui_primary_button_classes('rounded-lg px-3 py-2 text-xs'); ?>" id="resendBtn<?php echo $user['id']; ?>" onclick="sendReq('<?php echo $user['id']; ?>','<?php echo htmlspecialchars($user['name'], ENT_QUOTES); ?>','<?php echo $loggedInId; ?>','<?php echo htmlspecialchars($loggedInName, ENT_QUOTES); ?>')">Send Again</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="2" class="px-4 py-10 text-center text-slate-500 dark:text-slate-400">No declined requests.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<script>
function sendReq(idTo, nameTo, idFrom, nameFrom) {
    $.ajax({
        type: "POST",
        data: {
            useridTo: idTo,
            userNameTo: nameTo,
            userIdFrom: idFrom,
            userNameFrom: nameFrom
        },
        url: "sentrequest.php",
        success: function(res) {
            if (res === "done" || res === "resent") {
                $('#sentBtn' + idTo).removeClass('hidden');
                $('#reqBtn' + idTo).addClass('hidden');
                window.location.reload();
            } else {
                if (res === "exists") {
                    alert("Request already pending or accepted.");
                } else {
                    alert("Request failed");
                }
            }
        }
    });
}

</script>
<?php ui_darkmode_script(); ?>
</body>
</html>
