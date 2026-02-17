<?php
// manage_jobs.php
session_start();
require_once 'includes/db.php'; 
require_once 'includes/auth.php'; 
requireLogin();

$db = getDB();
$stmt = $db->prepare("SELECT * FROM jobs WHERE employer_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$jobs = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Jobs | ConnectSphere</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { background: #f8fafc; font-family: 'Plus Jakarta Sans', sans-serif; padding: 20px; }
        .container { max-width: 900px; margin: 0 auto; }
        .job-card { 
            background: white; padding: 20px; border-radius: 16px; 
            margin-bottom: 15px; border: 1px solid #e2e8f0;
            display: flex; justify-content: space-between; align-items: center;
        }
        .btn-delete { color: #ef4444; text-decoration: none; font-weight: 700; margin-left: 15px; }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    <div class="container">
        <h2>Your Posted Jobs</h2>
        <?php foreach ($jobs as $job): ?>
            <div class="job-card">
                <div>
                    <h3 style="margin:0;"><?php echo $job['title']; ?></h3>
                    <small><?php echo $job['location']; ?> • <?php echo $job['category']; ?></small>
                </div>
                <div>
                    <a href="handlers/delete_job.php?id=<?php echo $job['id']; ?>" class="btn-delete" onclick="return confirm('Delete this job?')">Delete</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>