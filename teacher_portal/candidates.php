<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireLogin();

$db = getDB();
$search = isset($_GET['search']) ? '%' . $_GET['search'] . '%' : '%%';

try {
    $stmt = $db->prepare("
        SELECT id, full_name, bio, profile_pic 
        FROM users 
        WHERE role = 'worker' AND (full_name LIKE ? OR bio LIKE ?)
        ORDER BY full_name ASC
    ");
    $stmt->execute([$search, $search]);
    $workers = $stmt->fetchAll();
} catch (PDOException $e) { $workers = []; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Talent Pool | ConnectSphere</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #2563eb; --bg: #f8fafc; --border: #e2e8f0; }
        * { box-sizing: border-box; } /* Fixes layout overflow */
        body { background: var(--bg); font-family: 'Plus Jakarta Sans', sans-serif; margin: 0; overflow-x: hidden; }
        
        .container { max-width: 1200px; margin: 0 auto; padding: 40px 20px; }
        
        .header-flex { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px; margin-bottom: 30px; }
        .search-input { padding: 12px 20px; border-radius: 12px; border: 1px solid var(--border); width: 300px; outline: none; }

        /* Fluid Grid */
        .candidate-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); 
            gap: 20px; 
        }

        .candidate-card { 
            background: white; padding: 25px; border-radius: 20px; border: 1px solid var(--border); 
            text-align: center; transition: 0.3s; 
        }
        .candidate-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.05); }
        
        .avatar-box { 
            width: 70px; height: 70px; background: #eff6ff; color: var(--primary); 
            border-radius: 18px; display: flex; align-items: center; justify-content: center; 
            font-size: 1.8rem; font-weight: 800; margin: 0 auto 15px; 
        }

        .btn-msg { 
            display: block; background: var(--primary); color: white; text-decoration: none; 
            padding: 10px; border-radius: 10px; font-weight: 700; margin-top: 15px; font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    <div class="container">
        <div class="header-flex">
            <div>
                <h1 style="margin:0; font-weight:800;">Global Talent Pool</h1>
                <p style="color: #64748b; margin: 5px 0 0;">Connect with verified professionals.</p>
            </div>
            <form action="" method="GET">
                <input type="text" name="search" class="search-input" placeholder="Search talent..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
            </form>
        </div>

        <div class="candidate-grid">
            <?php foreach($workers as $worker): ?>
                <div class="candidate-card">
                    <div class="avatar-box"><?php echo strtoupper(substr($worker['full_name'], 0, 1)); ?></div>
                    <h3 style="margin:0; font-size: 1.1rem;"><?php echo htmlspecialchars($worker['full_name']); ?></h3>
                    <p style="color:#64748b; font-size:0.8rem; margin: 10px 0;"><?php echo $worker['bio'] ? substr(htmlspecialchars($worker['bio']), 0, 60).'...' : 'Professional Worker'; ?></p>
                    <a href="messages.php?chat_with=<?php echo $worker['id']; ?>&name=<?php echo urlencode($worker['full_name']); ?>" class="btn-msg">Send Message</a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>