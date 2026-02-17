<?php
// includes/navbar.php
$role = $_SESSION['role'] ?? 'worker';
$fullName = $_SESSION['full_name'] ?? 'User';
$firstName = explode(' ', $fullName)[0];
$initial = strtoupper(substr($fullName, 0, 1));
?>
<nav class="top-nav">
    <div class="nav-container">
        <a href="index.php" class="logo">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 2l10 5.5v11L12 22 2 17.5V6.5L12 2z"/>
            </svg>
            ConnectSphere
        </a>

        <div class="search-bar">
            <form action="<?php echo ($role === 'employer') ? 'applicants.php' : 'find_jobs.php'; ?>" method="GET">
                <input type="search" name="search" placeholder="<?php echo ($role === 'employer') ? 'Search talent...' : 'Search jobs, companies...'; ?>">
            </form>
        </div>

        <div class="nav-icons">
            <a href="index.php" class="nav-icon" title="Home">🏠</a>
            
            <?php if ($role === 'employer'): ?>
                <a href="post_job.php" class="nav-icon" title="Post a Job">➕</a>
                <a href="applicants.php" class="nav-icon" title="Applicants">👥</a>
            <?php else: ?>
                <a href="find_jobs.php" class="nav-icon" title="Find Jobs">💼</a>
                <a href="network.php" class="nav-icon" title="Network">🌐</a>
            <?php endif; ?>

            <a href="messages.php" class="nav-icon" title="Messages">💬</a>
            
            <a href="logout.php" class="nav-icon" title="Logout" style="color: #ef4444;">🚪</a>
            
            <a href="profile.php" class="profile-link-wrapper">
                <span class="nav-user-name"><?php echo htmlspecialchars($firstName); ?></span>
                <div class="profile-pic-sm">
                    <?php echo $initial; ?>
                </div>
            </a>
        </div>
    </div>
</nav>

<style>
.top-nav { background: white; border-bottom: 1px solid #e2e8f0; padding: 10px 0; position: sticky; top: 0; z-index: 1000; }
.nav-container { max-width: 1200px; margin: 0 auto; display: flex; align-items: center; justify-content: space-between; padding: 0 20px; }
.logo { display: flex; align-items: center; gap: 8px; font-weight: 800; color: #2563eb; text-decoration: none; font-size: 1.2rem; }
.search-bar input { background: #f1f5f9; border: 1px solid #e2e8f0; padding: 8px 15px; border-radius: 20px; width: 300px; outline: none; }
.nav-icons { display: flex; align-items: center; gap: 18px; }
.nav-icon { text-decoration: none; font-size: 1.2rem; transition: transform 0.2s; }
.profile-link-wrapper { display: flex; align-items: center; gap: 10px; text-decoration: none; padding: 5px 10px; border-radius: 12px; transition: 0.2s; }
.profile-link-wrapper:hover { background: #f8fafc; }
.nav-user-name { font-size: 0.85rem; font-weight: 700; color: #1e293b; }
.profile-pic-sm { width: 32px; height: 32px; background: #2563eb; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.8rem; }
</style>