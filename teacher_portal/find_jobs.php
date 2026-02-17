<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireLogin();

$db = getDB();
$user_id = $_SESSION['user_id'];

// 1. CAPTURE THE SEARCH INPUT
// We use trim() to remove accidental spaces and default to empty string if not set
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

// 2. PREPARE THE SEARCH PARAMETER
// The % signs allow the database to find the word anywhere in the title or description
$search_param = '%' . $search_query . '%';

// 3. THE MASTER SEARCH QUERY
// It joins jobs, users (for company name), and applications (to check status)
try {
    $stmt = $db->prepare("
        SELECT j.*, u.full_name as company_name, a.status as app_status, a.id as application_id
        FROM jobs j
        JOIN users u ON j.employer_id = u.id
        LEFT JOIN applications a ON j.id = a.job_id AND a.user_id = ?
        WHERE (j.title LIKE ? OR j.description LIKE ? OR u.full_name LIKE ?)
        ORDER BY j.created_at DESC
    ");
    // We pass the search param three times: for Title, Description, and Company Name
    $stmt->execute([$user_id, $search_param, $search_param, $search_param]);
    $jobs = $stmt->fetchAll();
    $result_count = count($jobs);
} catch (PDOException $e) {
    die("Search Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Find Jobs | ConnectSphere</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #2563eb; --bg: #f8fafc; --white: #ffffff; --border: #e2e8f0; --text-main: #1e293b; --text-muted: #64748b; }
        body { background: var(--bg); font-family: 'Plus Jakarta Sans', sans-serif; margin: 0; color: var(--text-main); }
        .container { max-width: 1100px; margin: 50px auto; padding: 0 20px; }

        /* Search Bar Masterpiece Design */
        .search-section { background: white; padding: 30px; border-radius: 24px; border: 1px solid var(--border); box-shadow: 0 4px 20px rgba(0,0,0,0.03); margin-bottom: 40px; }
        .search-form { display: flex; gap: 15px; }
        .search-input-wrapper { flex: 1; position: relative; }
        .search-input { width: 100%; padding: 16px 20px; border-radius: 14px; border: 1px solid var(--border); font-family: inherit; font-size: 1rem; outline: none; transition: 0.2s; box-sizing: border-box;}
        .search-input:focus { border-color: var(--primary); box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1); }
        .search-button { background: var(--primary); color: white; border: none; padding: 0 30px; border-radius: 14px; font-weight: 700; cursor: pointer; transition: 0.2s; }
        .search-button:hover { background: #1d4ed8; transform: translateY(-1px); }

        /* Job Grid & Cards */
        .results-info { margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }
        .job-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 25px; }
        .job-card { background: white; padding: 30px; border-radius: 24px; border: 1px solid var(--border); display: flex; flex-direction: column; transition: 0.3s; }
        .job-card:hover { transform: translateY(-5px); box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        
        .btn { padding: 14px; border-radius: 12px; text-decoration: none; font-weight: 700; text-align: center; display: block; width: 100%; border: none; cursor: pointer; margin-top: auto; }
        .btn-apply { background: var(--primary); color: white; }
        .btn-withdraw { background: #fee2e2; color: #ef4444; font-size: 0.85rem; margin-top: 10px; }
        .status-badge { background: #dcfce7; color: #166534; padding: 10px; border-radius: 10px; text-align: center; font-weight: 700; font-size: 0.85rem; }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container">
        <div class="search-section">
            <h1 style="margin: 0 0 20px 0; font-weight: 800;">Find Your Next Move</h1>
            <form action="find_jobs.php" method="GET" class="search-form">
                <div class="search-input-wrapper">
                    <input type="text" name="search" class="search-input" 
                           placeholder="Search by job title, description, or company name..." 
                           value="<?php echo htmlspecialchars($search_query); ?>">
                </div>
                <button type="submit" class="search-button">Search Jobs</button>
            </form>
        </div>

        <div class="results-info">
            <span style="font-weight: 600; color: var(--text-muted);">
                <?php echo $result_count; ?> jobs found 
                <?php if($search_query): ?> for "<?php echo htmlspecialchars($search_query); ?>"<?php endif; ?>
            </span>
            <?php if($search_query): ?>
                <a href="find_jobs.php" style="color: var(--primary); text-decoration: none; font-weight: 700; font-size: 0.9rem;">✕ Clear Search</a>
            <?php endif; ?>
        </div>

        <div class="job-grid">
            <?php if ($result_count === 0): ?>
                <div style="grid-column: 1/-1; text-align: center; padding: 50px; background: white; border-radius: 24px; border: 1px dashed var(--border);">
                    <p style="color: var(--text-muted); font-size: 1.1rem;">No jobs match your search. Try different keywords.</p>
                </div>
            <?php else: ?>
                <?php foreach($jobs as $job): ?>
                    <div class="job-card">
                        <span style="color: var(--primary); font-weight: 800; font-size: 0.8rem; text-transform: uppercase;"><?php echo htmlspecialchars($job['company_name']); ?></span>
                        <h3 style="margin: 10px 0; font-weight: 800; font-size: 1.3rem;"><?php echo htmlspecialchars($job['title']); ?></h3>
                        <p style="color: var(--text-muted); font-size: 0.95rem; line-height: 1.6; margin-bottom: 25px;">
                            <?php echo htmlspecialchars(substr($job['description'], 0, 120)) . '...'; ?>
                        </p>
                        
                        <?php if (!$job['application_id']): ?>
                            <a href="apply.php?job_id=<?php echo $job['id']; ?>" class="btn btn-apply">Apply Now</a>
                        
                        <?php elseif ($job['app_status'] === 'pending'): ?>
                            <div class="status-badge">✓ Application Sent</div>
                            <a href="handlers/withdraw_handler.php?app_id=<?php echo $job['application_id']; ?>" 
                               class="btn btn-withdraw" 
                               onclick="return confirm('Withdraw application to edit details?')">Withdraw & Edit</a>
                        
                        <?php else: ?>
                            <div style="background: #f1f5f9; color: #64748b; padding: 12px; border-radius: 12px; text-align: center; font-weight: 700;">
                                Status: <?php echo ucfirst($job['app_status']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>