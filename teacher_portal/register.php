<?php 
// 1. Initialize session and include all necessary files first
session_start();
require_once 'config/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php'; 
require_once 'includes/auth.php';

$error = null;
$success = false;

// 2. Handle the POST request BEFORE any HTML is sent
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify Security Token
    if (!validateCsrfToken($_POST['csrf_token'])) {
        die("CSRF token validation failed. Possible cross-site request forgery.");
    }

    // Sanitize and prepare data
    $full_name = sanitize($_POST['full_name']);
    $email = sanitize($_POST['email']);
    $password = hashPassword($_POST['password']);
    $role = sanitize($_POST['role']);

    try {
        $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$full_name, $email, $password, $role]);
        
        // Success: Redirect to login with a message
        // Using session is better than URL parameters for clean design
        $_SESSION['registration_success'] = "Account created successfully! Please sign in.";
        header('Location: login.php');
        exit;
    } catch (PDOException $e) {
        $error = "Account already exists or database error.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account | JobConnect</title>
    <style>
        :root {
            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --bg-subtle: #f8fafc;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --white: #ffffff;
            --border: #e2e8f0;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
            background: radial-gradient(circle at top right, #eff6ff, transparent 40%),
                        radial-gradient(circle at bottom left, #f5f3ff, transparent 40%),
                        var(--bg-subtle);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 40px 20px;
            color: var(--text-main);
        }

        .auth-card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 24px;
            width: 100%;
            max-width: 480px;
            padding: 50px 40px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.05);
            text-align: center;
        }

        .logo { 
            font-size: 1.6rem; 
            font-weight: 800; 
            color: var(--primary); 
            text-decoration: none;
            letter-spacing: -1px;
            margin-bottom: 10px;
            display: block;
        }

        h2 { font-size: 1.8rem; font-weight: 800; margin-bottom: 10px; letter-spacing: -0.5px; }

        .subtitle { color: var(--text-muted); margin-bottom: 35px; font-size: 1rem; }

        .vertical-form { display: flex; flex-direction: column; gap: 18px; }

        input, select {
            width: 100%;
            padding: 14px 18px;
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 12px;
            font-size: 1rem;
            color: var(--text-main);
            transition: 0.3s;
        }

        input:focus, select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
            outline: none;
        }

        .action-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
        }

        .primary-btn {
            background: var(--primary);
            color: white;
            padding: 14px 30px;
            border: none;
            border-radius: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: 0.3s;
        }

        .primary-btn:hover {
            background: var(--primary-hover);
            transform: translateY(-2px);
        }

        .link { color: var(--primary); text-decoration: none; font-weight: 600; font-size: 0.95rem; }

        .divider { margin: 30px 0; border-bottom: 1px solid var(--border); line-height: 0.1em; position: relative; }
        .divider span { background: var(--white); padding: 0 15px; color: var(--text-muted); font-size: 0.85rem; font-weight: 600; }

        .social-btn {
            width: 100%;
            padding: 12px;
            background: var(--white);
            border: 1px solid var(--border);
            color: var(--text-main);
            border-radius: 12px;
            cursor: pointer;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            font-size: 0.95rem;
            font-weight: 600;
            transition: 0.3s;
        }

        .error-msg {
            color: #ef4444;
            background: #fef2f2;
            padding: 12px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            border: 1px solid #fee2e2;
            text-align: left;
        }
    </style>
</head>
<body>

    <div class="auth-card">
        <a href="landing.php" class="logo">JobConnect.</a>
        <h2>Create account</h2>
        <p class="subtitle">Join our professional community today</p>

        <?php if ($error): ?>
            <div class="error-msg">⚠️ <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" class="vertical-form">
            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
            
            <input type="text" name="full_name" placeholder="Full Name" required>
            <input type="email" name="email" placeholder="Email Address" required>
            <input type="password" name="password" placeholder="Create Password" required>
            
            <select name="role" required>
                <option value="" disabled selected>I want to...</option>
                <option value="worker">Find Work (Job Seeker / Freelancer)</option>
                <option value="employer">Hire Talent (Employer / Business)</option>
            </select>

            <div class="action-container">
                <a href="login.php" class="link">Already have an account?</a>
                <button type="submit" class="primary-btn">Create Profile</button>
            </div>
        </form>

        <div class="divider"><span>or sign up with</span></div>

        <button class="social-btn">
            <img src="https://upload.wikimedia.org/wikipedia/commons/c/c1/Google_%22G%22_logo.svg" width="18"> 
            Google
        </button>
        <button class="social-btn">
            <img src="https://upload.wikimedia.org/wikipedia/commons/f/fa/Apple_logo_black.svg" width="18"> 
            Apple
        </button>
    </div>

</body>
</html>