<?php
// includes/auth.php
require_once 'db.php';

class Auth {
    private $db;

    public function __construct() {
        try {
            $this->db = getDB();
        } catch (Exception $e) {
            file_put_contents('logs/db_error.log', "Auth init error: " . $e->getMessage() . "\n", FILE_APPEND);
            throw $e;
        }
    }

    public function login($email, $password) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // --- ESSENTIAL UPDATES FOR ROUTING ---
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role']; // Crucial for index.php routing
                $_SESSION['last_activity'] = time();
                
                $this->db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?")->execute([$user['id']]);
                file_put_contents('logs/db_error.log', "Login success: email=$email role=" . $user['role'] . "\n", FILE_APPEND);
                return true;
            }
            file_put_contents('logs/db_error.log', "Login failed: invalid credentials for email=$email\n", FILE_APPEND);
            return false;
        } catch (PDOException $e) {
            file_put_contents('logs/db_error.log', "Login error: " . $e->getMessage() . "\n", FILE_APPEND);
            throw new Exception("Login failed: Database error");
        }
    }

    public function register($email, $password, $full_name, $role) {
        try {
            // Validate inputs
            if (empty($email) || empty($password) || empty($full_name) || empty($role)) {
                return false;
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return false;
            }
            
            // Updated roles to be industry-neutral
            $allowed_roles = ['worker', 'employer', 'admin'];
            if (!in_array($role, $allowed_roles)) {
                file_put_contents('logs/db_error.log', "Register error: Invalid role '$role'\n", FILE_APPEND);
                return false;
            }

            // Check for existing email
            $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                return false;
            }

            // Insert user
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $this->db->prepare("INSERT INTO users (email, password, full_name, role) VALUES (?, ?, ?, ?)");
            $result = $stmt->execute([$email, $hashed_password, $full_name, $role]);
            
            if ($result) {
                file_put_contents('logs/db_error.log', "Register success: User '$email' created as '$role'\n", FILE_APPEND);
                return true;
            }
            return false;
        } catch (PDOException $e) {
            file_put_contents('logs/db_error.log', "Register error: " . $e->getMessage() . "\n", FILE_APPEND);
            return false;
        }
    }

    public function logout() {
        session_unset();
        session_destroy();
    }

    public function isLoggedIn() {
        // Ensure session isn't expired based on config constant
        $timeout = defined('SESSION_TIMEOUT') ? SESSION_TIMEOUT : 3600;
        
        if (isset($_SESSION['user_id']) && (time() - $_SESSION['last_activity'] < $timeout)) {
            $_SESSION['last_activity'] = time();
            return true;
        }
        return false;
    }

    public function getCurrentUser() {
        if ($this->isLoggedIn()) {
            $stmt = $this->db->prepare("SELECT id, email, full_name, role FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            return $stmt->fetch();
        }
        return null;
    }

    public function getUserId() {
        return $_SESSION['user_id'] ?? null;
    }
}

// Global helper functions
function isLoggedIn() {
    $auth = new Auth();
    return $auth->isLoggedIn();
}

function getCurrentUser() {
    $auth = new Auth();
    return $auth->getCurrentUser();
}

function getUserId() {
    $auth = new Auth();
    return $auth->getUserId();
}


function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php'); 
        exit;
    }
}