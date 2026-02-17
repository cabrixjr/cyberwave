<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';
requireLogin();

// Handle post creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_content'])) {
    // ... existing post code + image upload ...
    // Add post_type = 'job' if from job form
}
?>
<?php require_once 'includes/header.php'; ?>
<?php
if ($_SESSION['role'] == 'teacher') header('Location: dashboard_teacher.php');
else if ($_SESSION['role'] == 'employer') header('Location: dashboard_employer.php');
?>
<?php include 'includes/footer.php'; ?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/layout.php'; ?>

<!-- Create Post Box -->
<div class="create-post">
    <form method="POST" enctype="multipart/form-data">
        <div class="create-post-input">
            <div class="create-post-avatar"></div>
            <input type="text" name="post_content" placeholder="Share an update, job opportunity, or insight..." required>
        </div>
        <div class="post-actions">
            <button type="submit" name="post_type" value="photo" class="post-action-btn">📷 Photo</button>
            <button type="submit" name="post_type" value="job" class="post-action-btn">💼 Job Post</button>
            <button type="submit" name="post_type" value="article" class="post-action-btn">📄 Article</button>
        </div>
    </form>
</div>

<!-- Feed Posts (mixed regular + job posts) -->
<?php
$stmt = $pdo->prepare("
    SELECT p.*, u.full_name, u.role,
           (SELECT COUNT(*) FROM likes l WHERE l.post_id = p.id) as likes_count
    FROM posts p 
    JOIN users u ON p.user_id = u.id 
    WHERE p.is_public = 1 OR p.user_id = ?
    ORDER BY p.created_at DESC LIMIT 20
");
$stmt->execute([$_SESSION['user_id']]);
while($post = $stmt->fetch()) {
    $is_job = ($post['post_type'] ?? '') === 'job';
    ?>
    <div class="post-card">
        <div class="post-header">
            <div class="post-avatar"></div>
            <div class="post-author-info">
                <div class="post-author-name"><?=htmlspecialchars($post['full_name'])?></div>
                <div class="post-author-title"><?=ucfirst($post['role'])?> • <?= $post['likes_count']?> connections</div>
                <div class="post-time"><?=timeAgo($post['created_at'])?></div>
            </div>
        </div>
        <?php if($is_job): ?>
            <span class="post-tag opportunity">🔥 JOB OPPORTUNITY</span>
            <div class="post-content">
                <h3><?=htmlspecialchars($post['title'] ?? 'Job Opportunity')?></h3>
                <p><?=nl2br(htmlspecialchars($post['content']))?></p>
                <div class="post-details">
                    <div class="post-detail">📍 <?=htmlspecialchars($post['location'] ?? 'Worldwide')?></div>
                    <div class="post-detail">🕑 <?=htmlspecialchars($post['type'] ?? 'Full-time')?></div>
                    <div class="post-detail">💰 $60K - $90K</div>
                </div>
                <button class="apply-btn" onclick="applyJob(<?=$post['id']?>)">Quick Apply</button>
            </div>
        <?php else: ?>
            <div class="post-content"><?=nl2br(htmlspecialchars($post['content']))?></div>
        <?php endif; ?>
        <?php if($post['image_path']): ?>
            <img src="uploads/posts/<?=$post['image_path']?>" style="width:100%; border-radius:12px; margin-top:1rem;">
        <?php endif; ?>
        <div class="post-engagement">
            <button class="engagement-btn <?=isLiked($post['id']) ? 'active' : ''?>" onclick="toggleLike(<?=$post['id']?>)">
                ❤️ <?=$post['likes_count']?>
            </button>
            <button class="engagement-btn">💬 Comment</button>
            <button class="engagement-btn">🔄 Share</button>
            <button class="engagement-btn">📌 Save</button>
        </div>
    </div>
<?php } ?>

<?php include 'includes/footer.php'; // close body/html ?>