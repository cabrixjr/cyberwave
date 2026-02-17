<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

$db = getDB();
$user_id = $_SESSION['user_id'];
$update_type = $_POST['update_type'];

if ($update_type === 'profile') {
    $name = htmlspecialchars($_POST['full_name']);
    $email = htmlspecialchars($_POST['email']);
    $bio = htmlspecialchars($_POST['bio']);
    $profile_pic = null;

    // Handle Image Upload
    if (!empty($_FILES['avatar']['name'])) {
        $target_dir = "../uploads/avatars/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        
        $file_ext = strtolower(pathinfo($_FILES["avatar"]["name"], PATHINFO_EXTENSION));
        $new_name = $user_id . "_" . time() . "." . $file_ext;
        $target_file = $target_dir . $new_name;

        if (move_uploaded_file($_FILES["avatar"]["tmp_name"], $target_file)) {
            $profile_pic = "uploads/avatars/" . $new_name;
            // Update DB with new picture
            $stmt = $db->prepare("UPDATE users SET profile_pic = ? WHERE id = ?");
            $stmt->execute([$profile_pic, $user_id]);
        }
    }

    // Update Text Data
    $stmt = $db->prepare("UPDATE users SET full_name = ?, email = ?, bio = ? WHERE id = ?");
    $stmt->execute([$name, $email, $bio, $user_id]);
    
    header("Location: ../settings.php?tab=profile&msg=success");
    exit;

} elseif ($update_type === 'security') {
    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    // 1. Fetch current hashed password
    $stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    // 2. Validate current password
    if (!password_verify($current, $user['password'])) {
        header("Location: ../settings.php?tab=security&error=Current password is incorrect");
        exit;
    }

    // 3. Match new passwords
    if ($new !== $confirm) {
        header("Location: ../settings.php?tab=security&error=New passwords do not match");
        exit;
    }

    // 4. Update and Hash
    $hashed = password_hash($new, PASSWORD_DEFAULT);
    $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->execute([$hashed, $user_id]);

    header("Location: ../settings.php?tab=security&msg=success");
    exit;
}