<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireLogin();

if ($_SESSION['role'] !== 'worker') {
    header('Location: index.php');
    exit;
}

$db = getDB();
$worker_id = $_SESSION['user_id'];

// 1. Fetch Application Stats
$stmt = $db->prepare("SELECT COUNT(*) FROM applications WHERE user_id = ?");
$stmt->execute([$worker_id]);
$total_applied = $stmt->fetchColumn();

$stmt = $db->prepare("SELECT COUNT(*) FROM applications WHERE user_id = ? AND status = 'shortlisted'");
$stmt->execute([$worker_id]);
$shortlisted_count = $stmt->fetchColumn();

// 2. FIXED QUERY: Joining with users (u2) to get the Employer's Name
try {
    $stmt = $db->prepare("
        SELECT j.title, u2.full_name as company_name, a.applied_at, a.status 
        FROM applications a
        JOIN jobs j ON a.job_id = j.id
        JOIN users u2 ON j.employer_id = u2.id
        WHERE a.user_id = ?
        ORDER BY a.applied_at DESC
    ");
    $stmt->execute([$worker_id]);
    $my_applications = $stmt->fetchAll();
} catch (PDOException $e) {
    $my_applications = [];
    // Silent catch to prevent crashing, or use: echo $e->getMessage(); for debugging
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Career Dashboard | ConnectSphere</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #2563eb; --bg: #f8fafc; --white: #ffffff; --border: #e2e8f0; }
        * { box-sizing: border-box; }
        body { background: var(--bg); font-family: 'Plus Jakarta Sans', sans-serif; margin: 0; }
        
        .layout-wrapper { max-width: 1200px; margin: 40px auto; padding: 0 20px; display: grid; grid-template-columns: 300px 1fr; gap: 30px; }
        
        .worker-sidebar { background: var(--white); border-radius: 24px; padding: 30px; border: 1px solid var(--border); text-align: center; height: fit-content; box-shadow: 0 4px 6px rgba(0,0,0,0.02); }
        .avatar-lg { width: 100px; height: 100px; background: #eff6ff; color: var(--primary); border-radius: 30px; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; font-weight: 800; margin: 0 auto 20px; }
        
        .stats-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 25px; border-radius: 20px; border: 1px solid var(--border); box-shadow: 0 4px 6px rgba(0,0,0,0.02); }
        .stat-num { font-size: 2rem; font-weight: 800; color: var(--primary); display: block; }

        .app-list-card { background: white; border-radius: 24px; padding: 30px; border: 1px solid var(--border); box-shadow: 0 4px 6px rgba(0,0,0,0.02); }
        .status-pill { padding: 6px 12px; border-radius: 8px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; }
        .status-shortlisted { background: #dcfce7; color: #166534; }
        .status-pending { background: #fffbeb; color: #92400e; }
        .status-rejected { background: #fee2e2; color: #991b1b; }

        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { text-align: left; color: #64748b; font-size: 0.8rem; padding: 15px; border-bottom: 1px solid var(--border); }
        td { padding: 20px 15px; border-bottom: 1px solid #f8fafc; }
        
        .nav-link { display:block; padding: 12px 15px; color: #64748b; text-decoration:none; border-radius: 12px; margin-bottom: 5px; font-weight: 600; }
        .nav-link.active { background: #eff6ff; color: var(--primary); font-weight: 700; }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="layout-wrapper">
        <aside class="worker-sidebar">
            <div class="avatar-lg"><?php echo strtoupper(substr($_SESSION['full_name'], 0, 1)); ?></div>
            <h3 style="margin:0;"><?php echo htmlspecialchars($_SESSION['full_name']); ?></h3>
            <p style="color: #64748b; font-size: 0.9rem;">Worker Profile</p>
            <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">
            <nav style="text-align: left;">
                <a href="index.php" class="nav-link active">📊 Dashboard</a>
                <a href="find_jobs.php" class="nav-link">🔍 Find Jobs</a>
                <a href="messages.php" class="nav-link">💬 Messages</a>
                <a href="logout.php" class="nav-link" style="color: #ef4444; margin-top: 20px;">🚪 Logout</a>
            </nav>
        </aside>

        <main>
            <h2 style="font-weight: 800; margin-bottom: 25px;">Welcome Back, <?php echo explode(' ', $_SESSION['full_name'])[0]; ?>!</h2>

            <div class="stats-row">
                <div class="stat-card">
                    <span class="stat-num"><?php echo sprintf("%02d", $total_applied); ?></span>
                    <small style="font-weight:700; color:#64748b; text-transform: uppercase;">Total Applied</small>
                </div>
                <div class="stat-card">
                    <span class="stat-num"><?php echo sprintf("%02d", $shortlisted_count); ?></span>
                    <small style="font-weight:700; color:#10b981; text-transform: uppercase;">Shortlisted</small>
                </div>
            </div>

            <div class="app-list-card">
                <h3 style="margin-top:0; font-weight: 800;">My Application History</h3>
                <?php if(empty($my_applications)): ?>
                    <div style="text-align:center; padding: 40px; color:#64748b;">
                        <p>No applications yet. Start your journey today!</p>
                        <a href="find_jobs.php" style="color: var(--primary); font-weight: 700; text-decoration: none;">Browse Jobs →</a>
                    </div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr><th>Position</th><th>Employer</th><th>Date</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach($my_applications as $app): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($app['title']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($app['company_name']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($app['applied_at'])); ?></td>
                                    <td>
                                        <span class="status-pill status-<?php echo $app['status']; ?>">
                                            <?php echo ucfirst($app['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>