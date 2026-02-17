<?php require_once 'auth.php'; requireLogin(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TeacherConnect Pro</title>
    <link href="css/styles.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <script src="js/main.js" defer></script>
</head>
<body>
<div class="top-nav">
    <div class="nav-container">
        <a href="feed.php" class="logo">
            <i class="fas fa-graduation-cap"></i> TeacherConnect
        </a>
        <div class="search-bar">
            <input type="text" placeholder="🔍 Search teachers, jobs, schools..." id="global-search">
        </div>
        <div class="nav-icons">
            <a href="feed.php" class="nav-icon" title="Home"><i class="fas fa-home"></i></a>
            <a href="network.php" class="nav-icon" title="Network"><i class="fas fa-user-friends"></i></a>
            <a href="jobs.php" class="nav-icon" title="Jobs">
                <i class="fas fa-briefcase"></i>
                <?php
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0 AND message LIKE '%job%'");
                $stmt->execute([$_SESSION['user_id']]);
                if($stmt->fetchColumn() > 0) echo '<span class="notification-badge">!</span>';
                ?>
            </a>
            <a href="messages.php" class="nav-icon" title="Messages"><i class="fas fa-envelope"></i>
                <?php
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0 AND message LIKE '%message%'");
                $stmt->execute([$_SESSION['user_id']]);
                $count = $stmt->fetchColumn();
                if($count > 0) echo '<span class="notification-badge">'.$count.'</span>';
                ?>
            </a>
            <a href="notifications.php" class="nav-icon" title="Notifications"><i class="fas fa-bell"></i>
                <?php
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
                $stmt->execute([$_SESSION['user_id']]);
                $count = $stmt->fetchColumn();
                if($count > 0) echo '<span class="notification-badge">'.$count.'</span>';
                ?>
            </a>
            <a href="profile.php" class="profile-pic"></a>
        </div>
    </div>
</div>