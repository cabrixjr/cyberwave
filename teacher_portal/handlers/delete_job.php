<?php
// handlers/delete_job.php
session_start();
require_once '../config/db.php';
require_once '../includes/auth.php';

if (!isLoggedIn() || $_SESSION['role'] !== 'employer') {
    die("Unauthorized");
}

if (isset($_GET['id'])) {
    $job_id = $_GET['id'];
    $user_id = $_SESSION['user_id'];

    $db = getDB();
    // Safety check: only delete if the job belongs to THIS employer
    $stmt = $db->prepare("DELETE FROM jobs WHERE id = ? AND employer_id = ?");
    $stmt->execute([$job_id, $user_id]);

    header("Location: ../manage_jobs.php?deleted=1");
    exit();
}