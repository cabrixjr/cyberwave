<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireLogin();

$db = getDB();
$emp_id = $_SESSION['user_id'];

// Real Counters
$jobs_count = $db->query("SELECT COUNT(*) FROM jobs WHERE employer_id = $emp_id")->fetchColumn();
$apps_count = $db->query("SELECT COUNT(*) FROM applications a JOIN jobs j ON a.job_id = j.id WHERE j.employer_id = $emp_id")->fetchColumn();

// Recent Activity Query
$stmt = $db->prepare("
    SELECT u.full_name, j.title, a.applied_at, a.status 
    FROM applications a 
    JOIN users u ON a.user_id = u.id 
    JOIN jobs j ON a.job_id = j.id 
    WHERE j.employer_id = ? 
    ORDER BY a.applied_at DESC LIMIT 5
");
$stmt->execute([$emp_id]);
$recent = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Employer Dashboard | ConnectSphere</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #2563eb; --bg: #f8fafc; --border: #e2e8f0; }
        * { box-sizing: border-box; }
        body { background: var(--bg); font-family: 'Plus Jakarta Sans', sans-serif; margin: 0; }
        .master-wrapper { max-width: 1300px; margin: 30px auto; padding: 0 20px; display: grid; grid-template-columns: 280px 1fr; gap: 30px; }
        
        .sidebar { background: white; border-radius: 24px; padding: 30px; border: 1px solid var(--border); height: fit-content; }
        .nav-link { display: flex; align-items: center; gap: 12px; padding: 12px; text-decoration: none; color: #64748b; font-weight: 600; border-radius: 12px; }
        .nav-link.active { background: #eff6ff; color: var(--primary); }

        .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 25px; border-radius: 24px; border: 1px solid var(--border); border-bottom: 4px solid var(--primary); }
        .stat-val { font-size: 2rem; font-weight: 800; display: block; }

        .data-card { background: white; border-radius: 24px; padding: 30px; border: 1px solid var(--border); }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 15px; color: #64748b; font-size: 0.8rem; border-bottom: 1px solid var(--border); }
        td { padding: 15px; border-bottom: 1px solid #f8fafc; }
        .badge { padding: 5px 10px; border-radius: 8px; font-size: 0.7rem; font-weight: 700; background: #fffbeb; color: #92400e; }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    <div class="master-wrapper">
        <aside class="sidebar">
            <div style="text-align:center; margin-bottom: 30px;">
                <div style="width:60px; height:60px; background:#eff6ff; border-radius:20px; margin:0 auto 10px; display:flex; align-items:center; justify-content:center; font-size:1.5rem;">🏢</div>
                <h3 style="margin:0; font-size:1rem;"><?php echo htmlspecialchars($_SESSION['full_name']); ?></h3>
                <small style="color:#10b981; font-weight:700;">Employer</small>
            </div>
            <a href="employer_dashboard.php" class="nav-link active">📊 Dashboard</a>
            <a href="manage_jobs.php" class="nav-link">💼 My Jobs</a>
            <a href="candidates.php" class="nav-link">👥 Talent Pool</a>
            <a href="applicants.php" class="nav-link">📂 Applicants</a>
            <a href="settings.php" class="nav-link">⚙️ Settings</a>
        </aside>

        <main>
            <div style="display:flex; justify-content:space-between; margin-bottom:30px;">
                <h2 style="font-weight:800;">Recruitment Overview</h2>
                <a href="post_job.php" style="background:var(--primary); color:white; padding:12px 20px; border-radius:12px; text-decoration:none; font-weight:700;">+ Post Job</a>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <small style="color:#64748b; font-weight:700;">ACTIVE JOBS</small>
                    <span class="stat-val"><?php echo sprintf("%02d", $jobs_count); ?></span>
                </div>
                <div class="stat-card">
                    <small style="color:#64748b; font-weight:700;">APPLICATIONS</small>
                    <span class="stat-val"><?php echo sprintf("%02d", $apps_count); ?></span>
                </div>
                <div class="stat-card">
                    <small style="color:#64748b; font-weight:700;">SHORTLISTED</small>
                    <span class="stat-val">Check Pool</span>
                </div>
            </div>

            <div class="data-card">
                <h3 style="margin-top:0;">Recent Activity</h3>
                <table>
                    <thead><tr><th>Candidate</th><th>Job</th><th>Date</th><th>Status</th></tr></thead>
                    <tbody>
                        <?php foreach($recent as $r): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($r['full_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($r['title']); ?></td>
                                <td><?php echo date('M d', strtotime($r['applied_at'])); ?></td>
                                <td><span class="badge"><?php echo ucfirst($r['status']); ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>