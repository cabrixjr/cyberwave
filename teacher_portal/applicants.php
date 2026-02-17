<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireLogin();

$db = getDB();
$employer_id = $_SESSION['user_id'];

// Get all applicants for this employer's jobs
$stmt = $db->prepare("
    SELECT a.*, j.title as job_title, u.full_name as worker_name 
    FROM applications a
    JOIN jobs j ON a.job_id = j.id
    JOIN users u ON a.user_id = u.id
    WHERE j.employer_id = ?
    ORDER BY a.applied_at DESC
");
$stmt->execute([$employer_id]);
$apps = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Applicants | ConnectSphere</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #2563eb; --bg: #f8fafc; --border: #e2e8f0; }
        body { background: var(--bg); font-family: 'Plus Jakarta Sans', sans-serif; margin: 0; }
        .container { max-width: 1100px; margin: 40px auto; padding: 0 20px; }
        
        .app-card { background: white; border-radius: 20px; padding: 25px; border: 1px solid var(--border); margin-bottom: 20px; display: grid; grid-template-columns: 80px 1fr 220px; gap: 20px; align-items: center; transition: 0.3s; }
        .app-card:hover { border-color: var(--primary); box-shadow: 0 10px 25px rgba(37,99,235,0.05); }
        
        .worker-photo { width: 80px; height: 80px; border-radius: 15px; object-fit: cover; background: #f1f5f9; border: 2px solid var(--border); }
        
        .status-badge { padding: 6px 12px; border-radius: 8px; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; }
        .status-pending { background: #fff7ed; color: #9a3412; }
        .status-shortlisted { background: #dcfce7; color: #166534; }
        .status-rejected { background: #fee2e2; color: #991b1b; }

        .btn { padding: 12px; border-radius: 12px; text-decoration: none; font-weight: 700; font-size: 0.85rem; text-align: center; border: none; cursor: pointer; transition: 0.2s; }
        .btn-view { background: #f1f5f9; color: #1e293b; }
        .btn-shortlist { background: var(--primary); color: white; }
        .btn-reject { background: #ffffff; color: #ef4444; border: 1px solid #fee2e2; }
        .btn-restore { background: #fef3c7; color: #92400e; font-size: 0.8rem; }

        /* MODAL MASTERPIECE */
        .modal { display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.9); backdrop-filter: blur(5px); }
        .modal-content { background: white; margin: 2% auto; width: 90%; max-width: 1000px; height: 90vh; border-radius: 24px; position: relative; overflow: hidden; }
        .modal-header { padding: 15px 25px; background: #fff; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
        .close-btn { font-size: 28px; cursor: pointer; color: #64748b; font-weight: bold; }
        #cvFrame { width: 100%; height: calc(100% - 65px); border: none; }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container">
        <h1 style="font-weight: 800; font-size: 2rem;">Incoming Talent</h1>
        <p style="color: #64748b; margin-top: -10px; margin-bottom: 30px;">Review CVs and manage your candidates.</p>

        <?php foreach($apps as $app): ?>
        <div class="app-card">
            <img src="uploads/<?php echo $app['photo_path']; ?>" class="worker-photo" onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($app['worker_name']); ?>&background=random'">

            <div>
                <h3 style="margin: 0; color: #1e293b;"><?php echo htmlspecialchars($app['worker_name']); ?></h3>
                <p style="color: #64748b; margin: 4px 0; font-size: 0.9rem;">Role: <strong><?php echo htmlspecialchars($app['job_title']); ?></strong></p>
                <span class="status-badge status-<?php echo $app['status']; ?>"><?php echo $app['status']; ?></span>
            </div>

            <div style="display: flex; flex-direction: column; gap: 8px;">
                <button onclick="viewCV('<?php echo $app['resume_path']; ?>')" class="btn btn-view">👁 View Resume</button>
                
                <?php if($app['status'] === 'pending'): ?>
                    <a href="handlers/status_handler.php?id=<?php echo $app['id']; ?>&set=shortlisted" class="btn btn-shortlist">Shortlist</a>
                    <a href="handlers/status_handler.php?id=<?php echo $app['id']; ?>&set=rejected" class="btn btn-reject" onclick="return confirm('Reject this applicant?')">Reject</a>
                
                <?php elseif($app['status'] === 'shortlisted'): ?>
                    <a href="handlers/status_handler.php?id=<?php echo $app['id']; ?>&set=pending" class="btn btn-restore">↩ Undo Shortlist</a>
                
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div id="cvModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span style="font-weight: 800; color: #1e293b;">Resume Preview</span>
                <span class="close-btn" onclick="closeCV()">&times;</span>
            </div>
            <iframe id="cvFrame"></iframe>
        </div>
    </div>

    <script>
        function viewCV(fileName) {
            const modal = document.getElementById('cvModal');
            const frame = document.getElementById('cvFrame');
            // We point exactly to view_cv.php
            frame.src = 'view_cv.php?file=' + encodeURIComponent(fileName);
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden'; // Stop scrolling behind modal
        }

        function closeCV() {
            const modal = document.getElementById('cvModal');
            const frame = document.getElementById('cvFrame');
            modal.style.display = 'none';
            frame.src = ''; // Clear frame to save memory
            document.body.style.overflow = 'auto';
        }

        window.onclick = function(e) {
            if (e.target == document.getElementById('cvModal')) closeCV();
        }
    </script>
</body>
</html>