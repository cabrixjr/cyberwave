<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireLogin();

$db = getDB();
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// 1. Fetch User Profile Data
$stmt = $db->prepare("SELECT full_name, email, bio, created_at FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// 2. Initialize Stats
$stats = [
    'total' => 0,
    'shortlisted' => 0,
    'rejected' => 0,
    'active_jobs' => 0
];

if ($role === 'employer') {
    // Employer: Count jobs posted
    $s1 = $db->prepare("SELECT COUNT(*) FROM jobs WHERE employer_id = ?");
    $s1->execute([$user_id]);
    $stats['active_jobs'] = $s1->fetchColumn();

    // Employer: Count total applications received
    $s2 = $db->prepare("SELECT COUNT(*) FROM applications a JOIN jobs j ON a.job_id = j.id WHERE j.employer_id = ?");
    $s2->execute([$user_id]);
    $stats['total'] = $s2->fetchColumn();

    // FIX: Specify 'a.status' to avoid ambiguity error
    $s3 = $db->prepare("SELECT a.status, COUNT(*) as count FROM applications a JOIN jobs j ON a.job_id = j.id WHERE j.employer_id = ? GROUP BY a.status");
    $s3->execute([$user_id]);
} else {
    // Worker: Count total applications sent
    $s1 = $db->prepare("SELECT COUNT(*) FROM applications WHERE user_id = ?");
    $s1->execute([$user_id]);
    $stats['total'] = $s1->fetchColumn();

    // Worker: Count status
    $s3 = $db->prepare("SELECT status, COUNT(*) as count FROM applications WHERE user_id = ? GROUP BY status");
    $s3->execute([$user_id]);
}

// Map status results to the stats array
while ($row = $s3->fetch()) {
    if ($row['status'] === 'shortlisted') $stats['shortlisted'] = $row['count'];
    if ($row['status'] === 'rejected') $stats['rejected'] = $row['count'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile | ConnectSphere</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #2563eb; --bg: #f8fafc; --border: #e2e8f0; --success: #10b981; --danger: #ef4444; }
        body { background: var(--bg); font-family: 'Plus Jakarta Sans', sans-serif; margin: 0; }
        .container { max-width: 900px; margin: 40px auto; padding: 0 20px; }
        
        .profile-card { background: white; padding: 40px; border-radius: 30px; border: 1px solid var(--border); display: flex; align-items: center; gap: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); }
        .avatar-xl { width: 120px; height: 120px; background: var(--primary); color: white; border-radius: 40px; display: flex; align-items: center; justify-content: center; font-size: 3.5rem; font-weight: 800; }
        .user-info h1 { margin: 0; font-size: 2rem; font-weight: 800; }
        .role-tag { display: inline-block; padding: 4px 12px; background: #eff6ff; color: var(--primary); border-radius: 20px; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; margin-top: 5px; }

        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 20px; margin: 30px 0; }
        .stat-item { background: white; padding: 25px; border-radius: 24px; border: 1px solid var(--border); text-align: center; }
        .stat-item h2 { margin: 0; font-size: 2.2rem; font-weight: 800; color: #1e293b; }
        .stat-item p { margin: 5px 0 0; color: #64748b; font-weight: 700; font-size: 0.8rem; text-transform: uppercase; }
        
        .stat-shortlisted h2 { color: var(--success); }
        .stat-rejected h2 { color: var(--danger); }

        .content-box { background: white; padding: 35px; border-radius: 24px; border: 1px solid var(--border); }
        .content-box h3 { margin-top: 0; font-weight: 800; font-size: 1.2rem; display: flex; align-items: center; gap: 10px; }
        .bio-text { color: #475569; line-height: 1.8; font-size: 1rem; }
        .edit-btn { display: inline-block; margin-top: 20px; color: var(--primary); text-decoration: none; font-weight: 700; font-size: 0.9rem; }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container">
        <div class="profile-card">
            <div class="avatar-xl"><?php echo strtoupper(substr($user['full_name'], 0, 1)); ?></div>
            <div class="user-info">
                <h1><?php echo htmlspecialchars($user['full_name']); ?></h1>
                <span class="role-tag"><?php echo $role; ?> Account</span>
                <p style="color: #64748b; margin: 10px 0 0; font-weight: 600;">Member since <?php echo date('F Y', strtotime($user['created_at'])); ?></p>
            </div>
        </div>

        <div class="stats-grid">
            <?php if($role === 'employer'): ?>
                <div class="stat-item">
                    <h2><?php echo sprintf("%02d", $stats['active_jobs']); ?></h2>
                    <p>Active Jobs</p>
                </div>
                <div class="stat-item">
                    <h2><?php echo sprintf("%02d", $stats['total']); ?></h2>
                    <p>Applications Received</p>
                </div>
            <?php else: ?>
                <div class="stat-item">
                    <h2><?php echo sprintf("%02d", $stats['total']); ?></h2>
                    <p>Jobs Applied</p>
                </div>
            <?php endif; ?>

            <div class="stat-item stat-shortlisted">
                <h2><?php echo sprintf("%02d", $stats['shortlisted']); ?></h2>
                <p>Shortlisted</p>
            </div>

            <div class="stat-item stat-rejected">
                <h2><?php echo sprintf("%02d", $stats['rejected']); ?></h2>
                <p>Declined</p>
            </div>
        </div>

        <div class="content-box">
            <h3>📝 Professional Bio</h3>
            <div class="bio-text">
                <?php echo !empty($user['bio']) ? nl2br(htmlspecialchars($user['bio'])) : "No bio provided yet."; ?>
            </div>
            <a href="edit_profile.php" class="edit-btn">Edit Profile Details →</a>
        </div>
    </div>
</body>
</html>