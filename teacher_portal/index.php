<?php
/**
 * ConnectSphere - Master Traffic Controller
 * This file routes users to their specific dashboards based on their role.
 */

session_start();
require_once 'config/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// 1. Security Check: Redirect to landing page if not logged in
if (!isLoggedIn()) {
    header('Location: landing.php');
    exit;
}

// 2. Data Preparation
// Get user info from session (set during login in auth.php)
$user_id   = $_SESSION['user_id'];
$full_name = $_SESSION['full_name'];
$user_role = $_SESSION['role'] ?? 'worker'; // Default to worker if not set

/**
 * 3. Role-Based Routing Logic
 * Instead of one giant file, we include specialized dashboard files.
 * This keeps your code clean and easy to manage.
 */

if ($user_role === 'employer') {
    // This file will contain the UI for hiring, posting jobs, and managing candidates
    if (file_exists('dashboards/employer_dashboard.php')) {
        include 'dashboards/employer_dashboard.php';
    } else {
        die("Error: Employer dashboard file missing in dashboards folder.");
    }
} else {
    // This file handles all workers: Engineers, Teachers, Designers, Laborers, etc.
    // It focuses on finding jobs, gigs, and networking.
    if (file_exists('dashboards/worker_dashboard.php')) {
        include 'dashboards/worker_dashboard.php';
    } else {
        die("Error: Worker dashboard file missing in dashboards folder.");
    }
}
?>