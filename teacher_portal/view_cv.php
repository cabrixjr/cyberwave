<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireLogin();

// Only Employers should access this
if ($_SESSION['role'] !== 'employer') { 
    die("Access Denied: Only employers can view resumes."); 
}

$file = $_GET['file'] ?? '';
$file = basename($file); // Security: Prevent folder jumping
$path = __DIR__ . '/uploads/' . $file;

if (!empty($file) && file_exists($path)) {
    // Clear any previous output to prevent PDF corruption
    if (ob_get_level()) ob_end_clean();

    // MASTERPIECE HEADERS
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="' . $file . '"');
    header('Content-Transfer-Encoding: binary');
    header('Accept-Ranges: bytes');
    
    // THIS FIXES THE "REFUSED TO CONNECT" ERROR
    header("Content-Security-Policy: frame-ancestors 'self'");
    header('X-Frame-Options: SAMEORIGIN'); 
    
    header('Content-Length: ' . filesize($path));

    readfile($path);
    exit;
} else {
    echo "<div style='font-family:sans-serif; padding:20px;'>";
    echo "<h2>CV File Not Found</h2>";
    echo "<p>The file <b>$file</b> could not be located in the uploads folder.</p>";
    echo "</div>";
}