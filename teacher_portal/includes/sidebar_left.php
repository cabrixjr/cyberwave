<aside class="left-sidebar">
    <?php
    $stmt = $pdo->prepare("SELECT full_name, role FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    $connections = $pdo->prepare("SELECT COUNT(*) FROM follows WHERE followed_id = ? OR follower_id = ?");
    $connections->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
    $conn_count = $connections->fetchColumn();
    ?>
    <div class="profile-card">
        <div class="profile-avatar"></div>
        <div class="profile-name"><?php echo htmlspecialchars($user['full_name']); ?></div>
        <div class="profile-role"><?php echo ucfirst($user['role']); ?> </div>
        <div class="profile-stats">
            <div class="stat">
                <div class="stat-number"><?php echo $conn_count; ?></div>
                <div class="stat-label">Connections</div>
            </div>
            <div class="stat">
                <div class="stat-number">89</div>
                <div class="stat-label">Following</div>
            </div>
            <div class="stat">
                <div class="stat-number">1.2K</div>
                <div class="stat-label">Profile views</div>
            </div>
        </div>
    </div>
    <div class="menu-card">
        <a href="feed.php" class="menu-item active"><i class="fas fa-rss"></i> Feed</a>
        <a href="jobs.php" class="menu-item"><i class="fas fa-briefcase"></i> Jobs</a>
        <a href="profile.php" class="menu-item"><i class="fas fa-user"></i> My Profile</a>
        <a href="network.php" class="menu-item"><i class="fas fa-user-friends"></i> Network</a>
        <a href="groups.php" class="menu-item"><i class="fas fa-users"></i> Groups</a>
    </div>
</aside>