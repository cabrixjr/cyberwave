<?php
session_start();
require_once 'config/config.php';
require_once 'includes/db.php';        // Added for database access
require_once 'includes/functions.php'; // Added for sanitize and CSRF functions
require_once 'includes/auth.php';

$error = null;
$success_msg = null;

// Check if user just registered (from our new register.php logic)
if (isset($_SESSION['registration_success'])) {
    $success_msg = $_SESSION['registration_success'];
    unset($_SESSION['registration_success']); // Clear so it doesn't show again on refresh
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Verify the Security Token first
    if (!validateCsrfToken($_POST['csrf_token'])) {
        die("CSRF token validation failed. Possible cross-site request forgery.");
    }

    // 2. Proceed with sanitized login
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    
    $auth = new Auth();
    if ($auth->login($email, $password)) {
        header('Location: index.php');
        exit;
    } else {
        $error = "Invalid email or password";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In | JobConnect</title>
    <style>
        :root {
            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --bg-subtle: #f8fafc;
            --text-main: #0f172a;
            --text-muted: #475569;
            --white: #ffffff;
            --border: #e2e8f0;
            --error: #ef4444;
            --success: #10b981;
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
            max-width: 450px;
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
            margin-bottom: 12px;
            display: inline-block;
        }

        h2 { font-size: 1.8rem; font-weight: 800; margin-bottom: 10px; letter-spacing: -0.5px; }
        .subtitle { color: var(--text-muted); margin-bottom: 35px; font-size: 1rem; }

        .vertical-form { display: flex; flex-direction: column; gap: 18px; }
        
        input {
            width: 100%;
            padding: 14px 18px;
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 12px;
            font-size: 1rem;
            color: var(--text-main);
            transition: all 0.3s ease;
        }

        input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
            outline: none;
        }

        .action-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 25px;
        }

        .primary-btn {
            background: var(--primary);
            color: white;
            padding: 14px 35px;
            border: none;
            border-radius: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: 0.3s;
        }

        .primary-btn:hover {
            background: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(37, 99, 235, 0.2);
        }

        .link { color: var(--primary); text-decoration: none; font-weight: 600; font-size: 0.95rem; }
        .link:hover { text-decoration: underline; }

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

        /* --- ALERT STYLES --- */
        .alert {
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            text-align: left;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .alert-error { 
            color: var(--error); 
            background: #fef2f2; 
            border: 1px solid #fee2e2; 
        }
        .alert-success { 
            color: var(--success); 
            background: #ecfdf5; 
            border: 1px solid #d1fae5; 
        }
    </style>
</head>
<body>

    <div class="auth-card">
        <a href="landing.php" class="logo">JobConnect.</a>
        <h2>Welcome back</h2>
        <p class="subtitle">Enter your credentials to access your account</p>

        <?php if ($success_msg): ?>
            <div class="alert alert-success">✓ <?php echo htmlspecialchars($success_msg); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error">⚠️ <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" class="vertical-form">
            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
            
            <input type="email" name="email" placeholder="Email Address" required>
            <input type="password" name="password" placeholder="Password" required>
            
            <div style="text-align: left;">
                <a href="#" class="link" style="font-size: 0.85rem;">Forgot password?</a>
            </div>

            <div class="action-container">
                <a href="register.php" class="link">Create account</a>
                <button type="submit" class="primary-btn">Sign In</button>
            </div>
        </form>

        <div class="divider"><span>or sign in with</span></div>

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