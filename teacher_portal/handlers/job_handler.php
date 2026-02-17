<?php
/**
 * ConnectSphere - Job Posting Handler
 * This script processes new job listings from Employers
 */

session_start();

// Using __DIR__ creates an absolute path, solving "No such file or directory" errors
require_once __DIR__ . '/../includes/db.php'; 
require_once __DIR__ . '/../includes/auth.php'; 

/**
 * 1. SECURITY GATEKEEPER
 * Ensure the user is logged in AND is an employer.
 */
if (!isLoggedIn()) {
    header("Location: ../login.php");
    exit();
}

if ($_SESSION['role'] !== 'employer') {
    // If a worker somehow tries to post a job, kick them to their dashboard
    header("Location: ../dashboards/worker_dashboard.php");
    exit();
}

/**
 * 2. DATA PROCESSING
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Collect and Sanitize input to prevent XSS (Security best practice)
    $employer_id = $_SESSION['user_id'];
    $title       = htmlspecialchars(trim($_POST['title']));
    $category    = htmlspecialchars(trim($_POST['category']));
    $location    = htmlspecialchars(trim($_POST['location']));
    $description = htmlspecialchars(trim($_POST['description']));

    // Basic Validation
    if (empty($title) || empty($description) || empty($location)) {
        header("Location: ../post_job.php?error=emptyfields");
        exit();
    }

    try {
        // Connect to Database
        $db = getDB();

        // Prepare the SQL Statement (Prevents SQL Injection)
        $sql = "INSERT INTO jobs (employer_id, title, category, location, description, status, created_at) 
                VALUES (?, ?, ?, ?, ?, 'active', NOW())";
        
        $stmt = $db->prepare($sql);
        $success = $stmt->execute([
            $employer_id, 
            $title, 
            $category, 
            $location, 
            $description
        ]);

        if ($success) {
            // Success: Take them to manage their new post
            header("Location: ../manage_jobs.php?status=job_posted");
            exit();
        } else {
            // Logic failure
            header("Location: ../post_job.php?error=failed_to_save");
            exit();
        }

    } catch (PDOException $e) {
        // 3. ERROR LOGGING
        // Records the exact error in your logs folder for debugging
        $logMessage = "[" . date('Y-m-d H:i:s') . "] JOB_POST_ERROR: " . $e->getMessage() . "\n";
        file_put_contents(__DIR__ . '/../logs/db_error.log', $logMessage, FILE_APPEND);
        
        header("Location: ../post_job.php?error=server_error");
        exit();
    }
} else {
    // If someone tries to access this file directly via URL without POSTing
    header("Location: ../post_job.php");
    exit();
}