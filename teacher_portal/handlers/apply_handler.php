<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Use __DIR__ to ensure we always find the includes regardless of where we are
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../find_jobs.php");
    exit;
}

$db = getDB();
$user_id = $_SESSION['user_id'];
$job_id = (int)$_POST['job_id'];
$motivation = $_POST['motivation'];
$qualifications = $_POST['qualifications'];

// Process Files
$upload_dir = "../uploads/";
if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

$resume_name = time() . "_" . $_FILES['resume']['name'];
$photo_name = time() . "_" . $_FILES['photo']['name'];

if (move_uploaded_file($_FILES['resume']['tmp_name'], $upload_dir . $resume_name) && 
    move_uploaded_file($_FILES['photo']['tmp_name'], $upload_dir . $photo_name)) {
    
    try {
        $stmt = $db->prepare("INSERT INTO applications (job_id, user_id, resume_path, photo_path, motivation, qualifications, status, applied_at) VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())");
        $stmt->execute([$job_id, $user_id, $resume_name, $photo_name, $motivation, $qualifications]);
        
        // SUCCESS: Redirect back to find_jobs.php as requested
        header("Location: ../find_jobs.php?msg=applied"); 
        exit;
    } catch (PDOException $e) {
        die("Database Error: " . $e->getMessage());
    }
} else {
    die("File Upload Error. Check folder permissions.");
}