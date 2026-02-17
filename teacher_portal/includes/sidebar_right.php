<aside class="right-sidebar">
    <div class="sidebar-card">
        <h3>People You May Know</h3>
        <?php
        $stmt = $pdo->prepare("SELECT id, full_name, role FROM users WHERE id != ? ORDER BY RAND() LIMIT 5");
        $stmt->execute([$_SESSION['user_id']]);
        while($u = $stmt->fetch()) {
            echo '<div class="suggestion-item">
                <div class="suggestion-avatar"></div>
                <div class="suggestion-info">
                    <div class="suggestion-name">'.htmlspecialchars($u['full_name']).'</div>
                    <div class="suggestion-role">'.ucfirst($u['role']).'</div>
                </div>
                <button class="follow-btn" onclick="followUser('.$u['id'].')">Connect</button>
            </div>';
        }
        ?>
    </div>
    <div class="sidebar-card">
        <h3>Trending Topics</h3>
        <div class="trending-item"><div class="trending-tag">#InternationalTeaching</div><div class="trending-count">2.4K posts</div></div>
        <div class="trending-item"><div class="trending-tag">#IBJobs</div><div class="trending-count">1.8K posts</div></div>
        <div class="trending-item"><div class="trending-tag">#TeacherLife</div><div class="trending-count">3.9K posts</div></div>
    </div>
</aside>