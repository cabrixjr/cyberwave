<?php
// logout.php
session_start();
require_once 'config/config.php';
require_once 'includes/auth.php';

// Initialize authentication
$auth = new Auth();

// Log out the user
$auth->logout();

// Redirect to login page with a logged-out confirmation
header('Location: login.php?logged_out=1');
exit;