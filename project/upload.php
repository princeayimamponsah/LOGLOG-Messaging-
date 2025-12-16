<!-- <?php
session_start();
require_once "config.php";

if (!isset($_SESSION['userID'])) {
    die("User not logged in");
}

$userId = $_SESSION['userID'];

// CHECK IF FILE IS SENT
if (!isset($_FILES['profile_pic']) || $_FILES['profile_pic']['error'] !== UPLOAD_ERR_OK) {
    die("Upload failed");
}

$img = $_FILES['profile_pic'];

// VALIDATE EXTENSION
$allowed = ['jpg', 'jpeg', 'png', 'gif'];
$ext = strtolower(pathinfo($img['name'], PATHINFO_EXTENSION));

if (!in_array($ext, $allowed)) {
    die("Invalid file type");
}

// CREATE UPLOAD FOLDER IF MISSING
if (!is_dir("uploads")) {
    mkdir("uploads", 0777, true);
}

// CREATE UNIQUE FILENAME
$filename = "user_" . $userId . "_" . time() . "." . $ext;

// MOVE FILE
if (!move_uploaded_file($img['tmp_name'], "uploads/" . $filename)) {
    die("Failed to move file");
}

// SAVE IN DATABASE
$stmt = $conn->prepare("UPDATE users SET profile_pic=? WHERE id=?");
$stmt->bind_param("si", $filename, $userId);
$stmt->execute();

header("Location: profile.php?uploaded=1");
exit; -->
