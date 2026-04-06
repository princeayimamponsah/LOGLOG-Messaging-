<?php
session_start();
require_once "config.php";
require_once "ui.php";

if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}

$loggedInId = $_SESSION['userID'];

$selfStmt = $conn->prepare("SELECT profile_pic FROM users WHERE id = ? LIMIT 1");
$selfStmt->bind_param("i", $loggedInId);
$selfStmt->execute();
$selfRes = $selfStmt->get_result();
$selfRow = $selfRes ? $selfRes->fetch_assoc() : null;
$selfProfilePic = !empty($selfRow['profile_pic'])
    ? "uploads/" . rawurlencode($selfRow['profile_pic'])
    : "https://ui-avatars.com/api/?name=" . rawurlencode($_SESSION['name'] ?? 'User') . "&background=ddf4e5&color=0f5132";
$_SESSION['profile_pic'] = $selfRow['profile_pic'] ?? '';

$stmt = $conn->prepare("
    SELECT
        u.id AS friend_id,
        u.name AS friend_name,
        u.profile_pic,
        (
            SELECT m.message
            FROM messages m
            WHERE ((m.sender_id = ? AND m.receiver_id = u.id) OR (m.sender_id = u.id AND m.receiver_id = ?))
            ORDER BY m.sent_at DESC, m.id DESC
            LIMIT 1
        ) AS last_message,
        (
            SELECT m.sent_at
            FROM messages m
            WHERE ((m.sender_id = ? AND m.receiver_id = u.id) OR (m.sender_id = u.id AND m.receiver_id = ?))
            ORDER BY m.sent_at DESC, m.id DESC
            LIMIT 1
        ) AS last_sent_at,
        (
            SELECT m.sender_id
            FROM messages m
            WHERE ((m.sender_id = ? AND m.receiver_id = u.id) OR (m.sender_id = u.id AND m.receiver_id = ?))
            ORDER BY m.sent_at DESC, m.id DESC
            LIMIT 1
                ) AS last_sender_id,
                (
                        SELECT COUNT(*)
                        FROM messages m
                        WHERE m.sender_id = u.id
                            AND m.receiver_id = ?
                            AND m.id > COALESCE((
                                        SELECT cs.last_read_id
                                        FROM chat_state cs
                                        WHERE cs.user_id = ?
                                            AND cs.friend_id = u.id
                                        LIMIT 1
                            ), 0)
                ) AS unread_count
    FROM user_notification un
    JOIN users u ON u.id = IF(un.NOT_USERID_FROM = ?, un.NOT_USERID_TO, un.NOT_USERID_FROM)
    WHERE un.NOT_STATUS = '1'
      AND (un.NOT_USERID_FROM = ? OR un.NOT_USERID_TO = ?)
        ORDER BY COALESCE(last_sent_at, '1970-01-01 00:00:00') DESC, u.name ASC
");
$stmt->bind_param(
        "iiiiiiiiiii",
    $loggedInId,
    $loggedInId,
    $loggedInId,
    $loggedInId,
    $loggedInId,
    $loggedInId,
        $loggedInId,
        $loggedInId,
    $loggedInId,
    $loggedInId,
    $loggedInId
);
$stmt->execute();
$acceptReq = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <?php ui_render_head('Home Page'); ?>
    <style>
        #chatBody {
            scrollbar-width: thin;
            scrollbar-color: #10b981 #e5e7eb;
        }

        #chatBody::-webkit-scrollbar {
            width: 10px;
        }

        #chatBody::-webkit-scrollbar-track {
            background: #e5e7eb;
            border-radius: 999px;
        }

        #chatBody::-webkit-scrollbar-thumb {
            background: #10b981;
            border-radius: 999px;
        }
    </style>
</head>
<body class="min-h-full bg-slate-100 font-sans text-slate-900 dark:bg-slate-950 dark:text-slate-100">
    <?php include "navbar.php"; ?>

    <main class="mx-3 mb-4 h-[calc(100vh-112px)] rounded-2xl border border-emerald-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900 md:mx-5">
        <div class="grid h-full grid-cols-1 overflow-hidden rounded-2xl xl:grid-cols-4">
            <aside id="chatSidebar" class="fixed inset-y-0 left-0 z-40 w-[86%] max-w-sm -translate-x-full border-r border-emerald-100 bg-white p-4 transition-transform duration-300 dark:border-slate-700 dark:bg-slate-900 xl:static xl:z-auto xl:w-auto xl:max-w-none xl:translate-x-0 xl:col-span-1">
                <div class="mb-4 flex items-center gap-3 rounded-xl border border-emerald-100 bg-emerald-50 p-3 dark:border-slate-700 dark:bg-slate-800">
                    <img src="<?php echo $selfProfilePic; ?>" alt="Your profile" class="h-11 w-11 rounded-full object-cover">
                    <div>
                        <h2 class="text-sm font-bold"><?php echo htmlspecialchars($_SESSION['name'] ?? 'User'); ?></h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400"><span class="mr-1 inline-block h-2.5 w-2.5 rounded-full bg-emerald-500"></span>Available now</p>
                    </div>
                </div>

                <label class="mb-4 flex items-center gap-2 rounded-xl border border-emerald-100 bg-white px-3 py-2.5 dark:border-slate-700 dark:bg-slate-800">
                    <i class='bx bx-search text-lg text-slate-400'></i>
                    <input id="chatSearch" type="text" placeholder="Search chats or users" class="w-full bg-transparent text-sm outline-none placeholder:text-slate-400">
                </label>

                <div id="chatList" class="space-y-2 overflow-y-auto pb-2 xl:max-h-[calc(100vh-300px)]">
                    <?php while ($row = $acceptReq->fetch_assoc()): ?>
                        <?php
                            $friendName = htmlspecialchars($row['friend_name']);
                            $friendId = (int) $row['friend_id'];
                            $profilePic = !empty($row['profile_pic'])
                                ? "uploads/" . rawurlencode($row['profile_pic'])
                                : "https://ui-avatars.com/api/?name=" . rawurlencode($row['friend_name']) . "&background=ddf4e5&color=0f5132";
                            $lastMessageRaw = trim((string) ($row['last_message'] ?? ''));
                            $lastSenderId = isset($row['last_sender_id']) ? (int) $row['last_sender_id'] : 0;
                            $previewLabel = 'No messages yet';
                            if ($lastMessageRaw !== '') {
                                $previewLabel = ($lastSenderId === $loggedInId ? 'You: ' : '') . $lastMessageRaw;
                            }
                            $lastTime = '--:--';
                            if (!empty($row['last_sent_at'])) {
                                $lastTime = date('h:i A', strtotime($row['last_sent_at']));
                            }
                            $unreadCount = (int) ($row['unread_count'] ?? 0);
                        ?>
                        <button
                            type="button"
                            class="chat-list-item flex w-full items-center gap-3 rounded-xl border border-transparent p-2.5 text-left transition hover:-translate-y-0.5 hover:border-emerald-100 hover:bg-emerald-50 dark:hover:border-slate-700 dark:hover:bg-slate-800"
                            data-username="<?php echo $friendName; ?>"
                            data-userid="<?php echo $friendId; ?>"
                            data-pic="<?php echo $profilePic; ?>"
                            data-last-message="<?php echo htmlspecialchars($previewLabel, ENT_QUOTES); ?>"
                            data-last-time="<?php echo htmlspecialchars($lastTime, ENT_QUOTES); ?>"
                        >
                            <div class="relative">
                                <img src="<?php echo $profilePic; ?>" alt="<?php echo $friendName; ?>" class="h-11 w-11 rounded-full object-cover">
                                <span data-role="status-dot" class="absolute bottom-0 right-0 h-3 w-3 rounded-full border-2 border-white bg-slate-300 dark:border-slate-900"></span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between gap-2">
                                    <h3 data-role="chat-name" class="truncate text-sm <?php echo $unreadCount > 0 ? 'font-bold text-slate-900 dark:text-white' : 'font-semibold'; ?>"><?php echo $friendName; ?></h3>
                                    <span data-role="chat-time" class="text-[11px] <?php echo $unreadCount > 0 ? 'font-semibold text-slate-700 dark:text-slate-200' : 'text-slate-400'; ?>"><?php echo $lastTime; ?></span>
                                </div>
                                <p data-role="chat-preview" class="truncate text-xs <?php echo $unreadCount > 0 ? 'font-semibold text-slate-700 dark:text-slate-200' : 'text-slate-500 dark:text-slate-400'; ?>"><?php echo htmlspecialchars($previewLabel); ?></p>
                            </div>
                            <span data-role="unread-badge" class="<?php echo $unreadCount > 0 ? '' : 'hidden '; ?>min-w-5 rounded-full bg-emerald-500 px-1.5 py-0.5 text-center text-[10px] font-bold text-white"><?php echo $unreadCount > 99 ? '99+' : $unreadCount; ?></span>
                        </button>
                    <?php endwhile; ?>
                </div>
            </aside>

            <section class="flex min-w-0 min-h-0 flex-col xl:col-span-2 xl:border-r xl:border-emerald-100 dark:xl:border-slate-700">
                <header class="sticky top-0 z-20 flex items-center justify-between border-b border-emerald-100 bg-white px-3 py-2.5 dark:border-slate-700 dark:bg-slate-900 md:px-4 md:py-3">
                    <div class="flex items-center gap-2 md:gap-3">
                        <button id="openSidebarBtn" type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-emerald-100 text-slate-700 xl:hidden dark:border-slate-700 dark:text-slate-200">
                            <i class='bx bx-menu text-xl'></i>
                        </button>
                        <img id="chatHeaderPic" src="https://ui-avatars.com/api/?name=LOGLOG&background=d9f2e2&color=0f5132" alt="chat user" class="h-10 w-10 rounded-full object-cover">
                        <div>
                            <h4 id="chatHeaderName" class="text-sm font-bold md:text-base">No chat selected</h4>
                            <p id="chatHeaderStatus" class="text-xs text-slate-500 dark:text-slate-400">Pick a chat to start messaging</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-1.5 md:gap-2">
                        <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-emerald-100 text-slate-700 hover:bg-emerald-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"><i class='bx bx-phone text-lg'></i></button>
                        <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-emerald-100 text-slate-700 hover:bg-emerald-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"><i class='bx bx-video text-lg'></i></button>
                        <button id="toggleInfoBtn" type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-emerald-100 text-slate-700 hover:bg-emerald-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"><i class='bx bx-info-circle text-lg'></i></button>
                        <button id="darkModeBtn" type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-emerald-100 text-slate-700 hover:bg-emerald-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"><i class='bx bx-moon text-lg'></i></button>
                    </div>
                </header>

                <div id="chatBody" class="min-h-0 flex-1 space-y-2 overflow-y-auto bg-slate-100/60 p-3 dark:bg-slate-950 md:p-4">
                    <div id="emptyState" class="mx-auto mt-12 max-w-sm rounded-2xl border border-dashed border-emerald-200 bg-white px-6 py-10 text-center dark:border-slate-700 dark:bg-slate-900">
                        <i class='bx bx-message-rounded-detail text-4xl text-emerald-500'></i>
                        <h3 class="mt-3 text-lg font-bold">Your messages live here</h3>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Select a conversation to view and send messages.</p>
                    </div>
                </div>

                <div id="typingIndicator" class="hidden items-center gap-1 px-4 pb-2 text-xs text-slate-500 dark:text-slate-400">
                    <span class="h-2 w-2 animate-bounce rounded-full bg-emerald-500 [animation-delay:0ms]"></span>
                    <span class="h-2 w-2 animate-bounce rounded-full bg-emerald-500 [animation-delay:150ms]"></span>
                    <span class="h-2 w-2 animate-bounce rounded-full bg-emerald-500 [animation-delay:300ms]"></span>
                    <p id="typingText" class="ml-1">typing...</p>
                </div>

                <footer class="sticky bottom-0 z-10 grid grid-cols-[auto_auto_1fr_auto_auto] items-end gap-2 border-t border-emerald-100 bg-white p-3 dark:border-slate-700 dark:bg-slate-900">
                    <button id="attachBtn" type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-emerald-100 hover:bg-emerald-50 dark:border-slate-700 dark:hover:bg-slate-800"><i class='bx bx-paperclip text-lg'></i></button>

                    <div class="relative">
                        <button id="emojiBtn" type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-emerald-100 hover:bg-emerald-50 dark:border-slate-700 dark:hover:bg-slate-800"><i class='bx bx-smile text-lg'></i></button>
                        <div id="emojiPicker" class="hidden absolute bottom-12 left-0 z-30 grid grid-cols-3 gap-1 rounded-xl border border-emerald-100 bg-white p-2 shadow-lg dark:border-slate-700 dark:bg-slate-800">
                            <button type="button" class="emoji-item rounded-lg bg-slate-100 px-2 py-1 text-sm dark:bg-slate-700" data-char=":)">:)</button>
                            <button type="button" class="emoji-item rounded-lg bg-slate-100 px-2 py-1 text-sm dark:bg-slate-700" data-char=":D">:D</button>
                            <button type="button" class="emoji-item rounded-lg bg-slate-100 px-2 py-1 text-sm dark:bg-slate-700" data-char="<3">&lt;3</button>
                            <button type="button" class="emoji-item rounded-lg bg-slate-100 px-2 py-1 text-sm dark:bg-slate-700" data-char=":P">:P</button>
                            <button type="button" class="emoji-item rounded-lg bg-slate-100 px-2 py-1 text-sm dark:bg-slate-700" data-char=";)" >;)</button>
                            <button type="button" class="emoji-item rounded-lg bg-slate-100 px-2 py-1 text-sm dark:bg-slate-700" data-char=":O">:O</button>
                        </div>
                    </div>

                    <textarea id="messageInput" rows="1" placeholder="Type a message..." class="max-h-28 min-h-10 w-full resize-none rounded-xl border border-emerald-100 bg-slate-50 px-3 py-2 text-sm outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-300/40 dark:border-slate-700 dark:bg-slate-800"></textarea>

                    <button id="voiceBtn" type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-emerald-100 hover:bg-emerald-50 dark:border-slate-700 dark:hover:bg-slate-800"><i class='bx bx-microphone text-lg'></i></button>
                    <button id="sendBtn" type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-600 text-white transition hover:bg-emerald-700"><i class='bx bxs-send text-lg'></i></button>
                </footer>
            </section>

            <aside id="chatInfoPanel" class="hidden border-l border-emerald-100 bg-white p-4 dark:border-slate-700 dark:bg-slate-900 xl:col-span-1 xl:block">
                <div class="rounded-xl border border-emerald-100 bg-emerald-50 p-4 text-center dark:border-slate-700 dark:bg-slate-800">
                    <img id="infoUserPic" src="https://ui-avatars.com/api/?name=LOGLOG&background=d9f2e2&color=0f5132" alt="info user" class="mx-auto h-20 w-20 rounded-full object-cover">
                    <h5 id="infoUserName" class="mt-3 text-base font-bold">No active chat</h5>
                    <p id="infoUserStatus" class="mt-1 text-xs text-slate-500 dark:text-slate-400">Start a conversation to view details.</p>
                </div>

                <div class="mt-4">
                    <h6 class="mb-2 text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400">Shared media</h6>
                    <div class="grid grid-cols-2 gap-2">
                        <div class="grid h-20 place-items-center rounded-xl border border-emerald-100 bg-slate-50 text-xs font-bold text-slate-500 dark:border-slate-700 dark:bg-slate-800">IMG</div>
                        <div class="grid h-20 place-items-center rounded-xl border border-emerald-100 bg-slate-50 text-xs font-bold text-slate-500 dark:border-slate-700 dark:bg-slate-800">VID</div>
                        <div class="grid h-20 place-items-center rounded-xl border border-emerald-100 bg-slate-50 text-xs font-bold text-slate-500 dark:border-slate-700 dark:bg-slate-800">DOC</div>
                        <div class="grid h-20 place-items-center rounded-xl border border-emerald-100 bg-slate-50 text-xs font-bold text-slate-500 dark:border-slate-700 dark:bg-slate-800">GIF</div>
                    </div>
                </div>
            </aside>
        </div>

        <button id="sidebarBackdrop" type="button" class="fixed inset-0 z-30 hidden bg-black/30 xl:hidden"></button>
    </main>

<script>
const myUserId = <?= $loggedInId ?>;
let currentFriendId = null;
let lastMessageId = 0;
let pollInterval = null;
let sidebarInterval = null;

const chatBody = document.getElementById("chatBody");
const emptyState = document.getElementById("emptyState");
const chatHeaderName = document.getElementById("chatHeaderName");
const chatHeaderPic = document.getElementById("chatHeaderPic");
const chatHeaderStatus = document.getElementById("chatHeaderStatus");
const messageInput = document.getElementById("messageInput");
const sendBtn = document.getElementById("sendBtn");
const typingIndicator = document.getElementById("typingIndicator");
const typingText = document.getElementById("typingText");
const chatSearch = document.getElementById("chatSearch");
const emojiBtn = document.getElementById("emojiBtn");
const emojiPicker = document.getElementById("emojiPicker");
const toggleInfoBtn = document.getElementById("toggleInfoBtn");
const chatInfoPanel = document.getElementById("chatInfoPanel");
const infoUserName = document.getElementById("infoUserName");
const infoUserStatus = document.getElementById("infoUserStatus");
const infoUserPic = document.getElementById("infoUserPic");
const chatSidebar = document.getElementById("chatSidebar");
const sidebarBackdrop = document.getElementById("sidebarBackdrop");
const openSidebarBtn = document.getElementById("openSidebarBtn");

function formatTime(value) {
    const date = new Date(value);
    if (isNaN(date.getTime())) return "--:--";
    return date.toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" });
}

function setTyping(friendName, show) {
    typingText.textContent = friendName + " is typing...";
    typingIndicator.classList.toggle("hidden", !show);
    typingIndicator.classList.toggle("flex", show);
}

function updateInputHeight() {
    messageInput.style.height = "auto";
    messageInput.style.height = Math.min(messageInput.scrollHeight, 112) + "px";
}

function openMobileSidebar(show) {
    chatSidebar.classList.toggle("-translate-x-full", !show);
    sidebarBackdrop.classList.toggle("hidden", !show);
}

function updateChatMeta() {
    document.querySelectorAll(".chat-list-item").forEach((item, index) => {
        const statusDot = item.querySelector('[data-role="status-dot"]');

        const isOnline = ((parseInt(item.dataset.userid, 10) + index) % 2) === 0;

        item.dataset.online = isOnline ? "1" : "0";

        statusDot.classList.toggle("bg-emerald-500", isOnline);
        statusDot.classList.toggle("bg-slate-300", !isOnline);
    });
}

function refreshSidebarMeta() {
    fetch("sidebar_updates.php")
        .then(res => res.json())
        .then(items => {
            const chatList = document.getElementById("chatList");

            items.sort((a, b) => {
                const av = parseInt(a.sort_value || 0, 10);
                const bv = parseInt(b.sort_value || 0, 10);
                return bv - av;
            });

            items.forEach(item => {
                const row = document.querySelector('.chat-list-item[data-userid="' + item.friend_id + '"]');
                if (!row) return;

                const previewEl = row.querySelector('[data-role="chat-preview"]');
                const timeEl = row.querySelector('[data-role="chat-time"]');
                const nameEl = row.querySelector('[data-role="chat-name"]');
                const unreadEl = row.querySelector('[data-role="unread-badge"]');

                previewEl.textContent = item.preview || "No messages yet";
                timeEl.textContent = item.time || "--:--";

                const isActive = row.classList.contains("active");
                const unreadCount = isActive ? 0 : parseInt(item.unread_count || 0, 10);
                if (unreadCount > 0) {
                    unreadEl.textContent = unreadCount > 99 ? "99+" : String(unreadCount);
                    unreadEl.classList.remove("hidden");
                    previewEl.classList.add("font-semibold", "text-slate-700", "dark:text-slate-200");
                    previewEl.classList.remove("text-slate-500", "dark:text-slate-400");
                    timeEl.classList.add("font-semibold", "text-slate-700", "dark:text-slate-200");
                    timeEl.classList.remove("text-slate-400");
                    nameEl.classList.add("font-bold", "text-slate-900", "dark:text-white");
                    nameEl.classList.remove("font-semibold");
                } else {
                    unreadEl.classList.add("hidden");
                    previewEl.classList.remove("font-semibold", "text-slate-700", "dark:text-slate-200");
                    previewEl.classList.add("text-slate-500", "dark:text-slate-400");
                    timeEl.classList.remove("font-semibold", "text-slate-700", "dark:text-slate-200");
                    timeEl.classList.add("text-slate-400");
                    nameEl.classList.remove("font-bold", "text-slate-900", "dark:text-white");
                    nameEl.classList.add("font-semibold");
                }

                chatList.appendChild(row);
            });
        });
}

function updateActiveChatListPreview(msg) {
    const active = document.querySelector(".chat-list-item.active");
    if (!active) return;

    const previewEl = active.querySelector('[data-role="chat-preview"]');
    const timeEl = active.querySelector('[data-role="chat-time"]');
    const isSent = parseInt(msg.sender_id, 10) === myUserId;
    const previewText = (isSent ? "You: " : "") + msg.message;

    previewEl.textContent = previewText;
    timeEl.textContent = formatTime(msg.sent_at);
    active.dataset.lastMessage = previewText;
    active.dataset.lastTime = formatTime(msg.sent_at);
}

function renderMessage(msg, isLastIncomingBatchMessage) {
    const isSent = parseInt(msg.sender_id, 10) === myUserId;
    const row = document.createElement("div");
    row.className = "flex " + (isSent ? "justify-end" : "justify-start");

    const bubble = document.createElement("div");
    bubble.className = "max-w-[80%] rounded-2xl px-3 py-2 shadow-sm md:max-w-[70%]";
    if (isSent) {
        bubble.classList.add("rounded-br-md", "bg-emerald-600", "text-white");
    } else {
        bubble.classList.add("rounded-bl-md", "bg-white", "text-slate-900", "dark:bg-slate-800", "dark:text-slate-100");
    }

    const text = document.createElement("p");
    text.className = "text-sm leading-relaxed";
    text.textContent = msg.message;

    const meta = document.createElement("div");
    meta.className = "mt-1 flex items-center justify-end gap-1 text-[11px] " + (isSent ? "text-emerald-100" : "text-slate-500 dark:text-slate-400");

    const time = document.createElement("span");
    time.textContent = formatTime(msg.sent_at);
    meta.appendChild(time);

    if (isSent) {
        const status = document.createElement("i");
        const isOnline = document.querySelector(".chat-list-item.active")?.dataset.online === "1";
        const read = isOnline || !isLastIncomingBatchMessage;
        status.className = "bx bx-check-double " + (read ? "text-cyan-200" : "text-emerald-100");
        meta.appendChild(status);
    }

    bubble.appendChild(text);
    bubble.appendChild(meta);
    row.appendChild(bubble);
    chatBody.appendChild(row);
}

function loadMessages() {
    if (!currentFriendId) return;

    fetch("get_messages.php?friend_id=" + currentFriendId + "&after_id=" + lastMessageId)
        .then(res => res.json())
        .then(messages => {
            messages.forEach((msg, index) => {
                renderMessage(msg, index === messages.length - 1);
                lastMessageId = Math.max(lastMessageId, parseInt(msg.id, 10));
                updateActiveChatListPreview(msg);
            });

            if (messages.length > 0) {
                chatBody.scrollTo({ top: chatBody.scrollHeight, behavior: "smooth" });
            }

            const active = document.querySelector(".chat-list-item.active");
            if (active && active.dataset.online === "1" && Math.random() > 0.5) {
                setTyping(active.dataset.username, true);
                setTimeout(() => setTyping(active.dataset.username, false), 1200);
            } else {
                setTyping("", false);
            }
        });
}

function sendMessage() {
    const msg = messageInput.value.trim();
    if (!msg || !currentFriendId) return;

    const formData = new FormData();
    formData.append("receiver_id", currentFriendId);
    formData.append("message", msg);

    fetch("send_message.php", { method: "POST", body: formData })
        .then(res => res.text())
        .then(res => {
            if (res === "done") {
                messageInput.value = "";
                updateInputHeight();
                loadMessages();
            }
        });
}

function selectChat(item) {
    currentFriendId = item.dataset.userid;
    lastMessageId = 0;

    document.querySelectorAll(".chat-list-item").forEach(el => {
        el.classList.remove("active", "border-emerald-200", "bg-emerald-50", "dark:bg-slate-800", "dark:border-slate-700");
    });
    item.classList.add("active", "border-emerald-200", "bg-emerald-50", "dark:bg-slate-800", "dark:border-slate-700");

    const isOnline = item.dataset.online === "1";
    chatHeaderName.textContent = item.dataset.username;
    chatHeaderPic.src = item.dataset.pic;
    chatHeaderStatus.textContent = isOnline ? "Online now" : "Last seen recently";
    chatHeaderStatus.classList.toggle("text-emerald-600", isOnline);

    infoUserName.textContent = item.dataset.username;
    infoUserPic.src = item.dataset.pic;
    infoUserStatus.textContent = isOnline ? "Available for chat" : "Currently offline";

    emptyState.classList.add("hidden");
    chatBody.querySelectorAll(".message-item, .flex").forEach(node => {
        if (node !== emptyState) node.remove();
    });
    clearInterval(pollInterval);
    loadMessages();
    pollInterval = setInterval(loadMessages, 2000);
    refreshSidebarMeta();
    openMobileSidebar(false);
}

document.querySelectorAll(".chat-list-item").forEach(item => {
    item.addEventListener("click", () => selectChat(item));
});

chatSearch.addEventListener("input", () => {
    const q = chatSearch.value.trim().toLowerCase();
    document.querySelectorAll(".chat-list-item").forEach(item => {
        const match = item.dataset.username.toLowerCase().includes(q);
        item.classList.toggle("hidden", !match);
    });
});

messageInput.addEventListener("input", updateInputHeight);
messageInput.addEventListener("keydown", function (e) {
    if (e.key === "Enter" && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
    }
});

sendBtn.addEventListener("click", sendMessage);

emojiBtn.addEventListener("click", () => {
    emojiPicker.classList.toggle("hidden");
});

document.querySelectorAll(".emoji-item").forEach(btn => {
    btn.addEventListener("click", () => {
        messageInput.value += btn.dataset.char;
        updateInputHeight();
        emojiPicker.classList.add("hidden");
        messageInput.focus();
    });
});

document.addEventListener("click", function (e) {
    if (!emojiPicker.contains(e.target) && !emojiBtn.contains(e.target)) {
        emojiPicker.classList.add("hidden");
    }
});

toggleInfoBtn.addEventListener("click", () => {
    chatInfoPanel.classList.toggle("hidden");
});

openSidebarBtn.addEventListener("click", () => openMobileSidebar(true));
sidebarBackdrop.addEventListener("click", () => openMobileSidebar(false));

document.getElementById("attachBtn").addEventListener("click", () => alert("Attachment picker can be connected here."));
document.getElementById("voiceBtn").addEventListener("click", () => alert("Voice note recording UI placeholder."));

updateChatMeta();
refreshSidebarMeta();
if (!sidebarInterval) {
    sidebarInterval = setInterval(refreshSidebarMeta, 3000);
}
updateInputHeight();
</script>
<?php ui_darkmode_script(); ?>
</body>
</html>
