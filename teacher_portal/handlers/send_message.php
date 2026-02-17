<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isLoggedIn()) {
    $sender_id = $_SESSION['user_id'];
    $receiver_id = $_POST['receiver_id'];
    $message = trim($_POST['message']);
    $attachment_path = null;

    // Handle File Upload
    if (!empty($_FILES['attachment']['name'])) {
        $target_dir = "../uploads/messages/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        
        $file_name = time() . '_' . basename($_FILES["attachment"]["name"]);
        $target_file = $target_dir . $file_name;
        
        if (move_uploaded_file($_FILES["attachment"]["tmp_name"], $target_file)) {
            $attachment_path = "uploads/messages/" . $file_name;
        }
    }

    try {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO messages (sender_id, receiver_id, message, attachment_path) VALUES (?, ?, ?, ?)");
        $stmt->execute([$sender_id, $receiver_id, $message, $attachment_path]);
        
        header("Location: ../messages.php?chat_with=" . $receiver_id);
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}