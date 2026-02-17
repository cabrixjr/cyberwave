<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$db = getDB();

// Fetch latest user data
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Account Settings | ConnectSphere</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #2563eb; --bg: #f8fafc; --white: #ffffff; --border: #e2e8f0; }
        body { background: var(--bg); font-family: 'Plus Jakarta Sans', sans-serif; color: #1e293b; margin: 0; }
        .container { max-width: 1000px; margin: 50px auto; padding: 0 20px; }
        
        .settings-grid { display: grid; grid-template-columns: 280px 1fr; gap: 30px; }
        
        /* Sidebar Nav */
        .settings-nav { background: var(--white); padding: 20px; border-radius: 20px; border: 1px solid var(--border); height: fit-content; }
        .nav-link { display: block; padding: 12px 15px; border-radius: 12px; text-decoration: none; color: #64748b; font-weight: 600; margin-bottom: 5px; transition: 0.3s; }
        .nav-link.active { background: #eff6ff; color: var(--primary); }
        .nav-link:hover:not(.active) { background: #f1f5f9; }

        /* Main Content */
        .settings-card { background: var(--white); padding: 40px; border-radius: 24px; border: 1px solid var(--border); box-shadow: 0 4px 6px rgba(0,0,0,0.02); }
        .section-title { font-size: 1.5rem; font-weight: 800; margin-bottom: 25px; border-bottom: 2px solid #f1f5f9; padding-bottom: 15px; }
        
        .profile-pic-section { display: flex; align-items: center; gap: 20px; margin-bottom: 30px; }
        .current-avatar { width: 80px; height: 80px; border-radius: 20px; object-fit: cover; background: #dbeafe; display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: 800; color: var(--primary); }
        
        .form-group { margin-bottom: 20px; }
        label { display: block; font-size: 0.85rem; font-weight: 700; color: #64748b; margin-bottom: 8px; text-transform: uppercase; }
        input, textarea { width: 100%; padding: 12px 15px; border-radius: 12px; border: 1px solid var(--border); font-family: inherit; font-size: 1rem; box-sizing: border-box; }
        
        .btn-save { background: var(--primary); color: white; border: none; padding: 14px 30px; border-radius: 12px; font-weight: 700; cursor: pointer; transition: 0.3s; }
        .btn-save:hover { background: #1d4ed8; transform: translateY(-2px); }
        
        .alert { padding: 15px; border-radius: 12px; margin-bottom: 20px; font-weight: 600; }
        .alert-success { background: #dcfce7; color: #166534; }
        .alert-error { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container">
        <div class="settings-grid">
            <aside class="settings-nav">
    <a href="settings.php?tab=profile" 
       class="nav-link <?php echo (!isset($_GET['tab']) || $_GET['tab'] == 'profile') ? 'active' : ''; ?>">
       👤 General Profile
    </a>

    <a href="settings.php?tab=security" 
       class="nav-link <?php echo (isset($_GET['tab']) && $_GET['tab'] == 'security') ? 'active' : ''; ?>">
       🔒 Security
    </a>

    <hr style="border: 0; border-top: 1px solid #f1f5f9; margin: 20px 0;">

    <a href="logout.php" class="nav-link" style="color: #ef4444;">
       🚪 Logout
    </a>
</aside>

            <main class="settings-card">
                <?php if(isset($_GET['msg'])): ?>
                    <div class="alert alert-success">✅ Profile updated successfully!</div>
                <?php endif; ?>
                <?php if(isset($_GET['error'])): ?>
                    <div class="alert alert-error">❌ <?php echo htmlspecialchars($_GET['error']); ?></div>
                <?php endif; ?>

                <?php if(!isset($_GET['tab']) || $_GET['tab'] == 'profile'): ?>
                    <div class="section-title">General Profile</div>
                    <form action="handlers/update_settings.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="update_type" value="profile">
                        
                        <div class="profile-pic-section">
                            <?php if($user['profile_pic']): ?>
                                <img src="<?php echo $user['profile_pic']; ?>" class="current-avatar">
                            <?php else: ?>
                                <div class="current-avatar"><?php echo substr($user['full_name'],0,1); ?></div>
                            <?php endif; ?>
                            <div>
                                <label>Change Photo</label>
                                <input type="file" name="avatar" accept="image/*" style="border:none; padding:0;">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Full Name</label>
                            <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Professional Bio</label>
                            <textarea name="bio" rows="4"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                        </div>

                        <button type="submit" class="btn-save">Save Profile Changes</button>
                    </form>

                <?php elseif($_GET['tab'] == 'security'): ?>
                    <div class="section-title">Security & Password</div>
                    <form action="handlers/update_settings.php" method="POST">
                        <input type="hidden" name="update_type" value="security">
                        
                        <div class="form-group">
                            <label>Current Password</label>
                            <input type="password" name="current_password" required>
                        </div>

                        <div class="form-group">
                            <label>New Password</label>
                            <input type="password" name="new_password" required>
                        </div>

                        <div class="form-group">
                            <label>Confirm New Password</label>
                            <input type="password" name="confirm_password" required>
                        </div>

                        <button type="submit" class="btn-save" style="background: #1e293b;">Update Security</button>
                    </form>
                <?php endif; ?>
            </main>
        </div>
    </div>
</body>
</html>