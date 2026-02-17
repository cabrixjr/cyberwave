<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireLogin();

$job_id = isset($_GET['job_id']) ? (int)$_GET['job_id'] : 0;
if ($job_id <= 0) { header("Location: find_jobs.php"); exit; }

$db = getDB();
// Get job details so the user knows what they are applying for
$stmt = $db->prepare("SELECT j.*, u.full_name as employer_name FROM jobs j JOIN users u ON j.employer_id = u.id WHERE j.id = ?");
$stmt->execute([$job_id]);
$job = $stmt->fetch();

if (!$job) { die("Job not found."); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Complete Application | ConnectSphere</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #2563eb; --bg: #f8fafc; --white: #ffffff; --border: #e2e8f0; }
        body { background: var(--bg); font-family: 'Plus Jakarta Sans', sans-serif; margin: 0; padding-bottom: 50px; }
        .form-container { max-width: 600px; margin: 50px auto; background: white; padding: 40px; border-radius: 30px; border: 1px solid var(--border); box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
        .field-group { margin-bottom: 20px; }
        label { display: block; font-weight: 700; margin-bottom: 8px; color: #1e293b; font-size: 0.9rem; }
        input[type="file"], textarea { width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 12px; font-family: inherit; }
        textarea { height: 100px; resize: none; }
        .btn-row { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 30px; }
        .btn-submit { background: var(--primary); color: white; border: none; padding: 15px; border-radius: 12px; font-weight: 800; cursor: pointer; }
        .btn-cancel { background: #f1f5f9; color: #64748b; text-decoration: none; text-align: center; padding: 15px; border-radius: 12px; font-weight: 800; }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    <div class="form-container">
        <h2 style="margin-top:0; font-weight:800;">Apply for <?php echo htmlspecialchars($job['title']); ?></h2>
        <p style="color:#64748b; margin-bottom:30px;">at <?php echo htmlspecialchars($job['employer_name']); ?></p>

        <form action="handlers/apply_handler.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="job_id" value="<?php echo $job['id']; ?>">

            <div class="field-group">
                <label>Upload Resume (PDF only) *</label>
                <input type="file" name="resume" accept=".pdf" required>
            </div>

            <div class="field-group">
                <label>Profile Picture (JPG/PNG) *</label>
                <input type="file" name="photo" accept="image/*" required>
            </div>

            <div class="field-group">
                <label>Why do you want this job? *</label>
                <textarea name="motivation" required placeholder="Describe your interest..."></textarea>
            </div>

            <div class="field-group">
                <label>Why do you think you qualify? *</label>
                <textarea name="qualifications" required placeholder="Highlight your skills..."></textarea>
            </div>

            <div class="btn-row">
                <a href="find_jobs.php" class="btn-cancel">Cancel</a>
                <button type="submit" class="btn-submit">Submit Application</button>
            </div>
        </form>
    </div>
</body>
</html>