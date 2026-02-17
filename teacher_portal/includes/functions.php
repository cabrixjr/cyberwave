<?php
/**
 * JobConnect Masterpiece Functions
 * Handling Security, Files, and Data Utility
 */

// --- 1. DATA SECURITY & SANITIZATION ---

/**
 * Clean user input to prevent XSS
 */
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Secure Password Hashing
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * CSRF Protection - Generate Token
 */
function generateCsrfToken() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * CSRF Protection - Validate Token
 */
function validateCsrfToken($token) {
    if (session_status() === PHP_SESSION_NONE) session_start();
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}


// --- 2. FILE MANAGEMENT (Resumes & Avatars) ---

/**
 * Securely upload files (Multiple or Single)
 */
function uploadFiles($files, $destination, $allowed = ['pdf', 'jpg', 'png', 'docx'], $maxSize = 5242880) {
    $uploaded = [];
    
    // Normalize array if a single file is uploaded
    if (!is_array($files['name'])) {
        $files = [
            'name' => [$files['name']],
            'type' => [$files['type']],
            'tmp_name' => [$files['tmp_name']],
            'error' => [$files['error']],
            'size' => [$files['size']]
        ];
    }

    foreach ($files['name'] as $index => $name) {
        if ($files['error'][$index] !== UPLOAD_ERR_OK || $files['size'][$index] > $maxSize) {
            continue;
        }

        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            continue;
        }

        // Use unique ID to prevent overwriting files with the same name
        $filename = uniqid('JC_', true) . '.' . $ext;
        
        // Ensure directory exists
        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }

        $path = rtrim($destination, '/') . '/' . $filename;

        if (move_uploaded_file($files['tmp_name'][$index], $path)) {
            $uploaded[] = ['filename' => $filename, 'original_name' => $name];
        }
    }
    return $uploaded;
}

/**
 * Delete a file from the server
 */
function deleteFile($path) {
    if (file_exists($path) && is_file($path)) {
        return unlink($path);
    }
    return false;
}


// --- 3. DATABASE HELPERS & NOTIFICATIONS ---

/**
 * Get User Profile
 */
function getProfile($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM profiles WHERE user_id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}

/**
 * Create a system notification for the user
 */
function createNotification($user_id, $message) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO notifications (user_id, message, created_at) VALUES (?, ?, NOW())");
    $stmt->execute([$user_id, sanitize($message)]);
}


// --- 4. NAVIGATION & MESSAGING ---

/**
 * Simple Redirect
 */
function redirect($url) {
    header("Location: $url");
    exit();
}

/**
 * Helper for Relative Time (e.g. "2 hours ago")
 */
function timeAgo($timestamp) {
    $time = strtotime($timestamp);
    $diff = time() - $time;
    if ($diff < 60) return "Just now";
    if ($diff < 3600) return round($diff / 60) . " mins ago";
    if ($diff < 86400) return round($diff / 3600) . " hours ago";
    return date("M j, Y", $time);
}