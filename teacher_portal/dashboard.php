<?php
// dashboard.php
session_start();
require_once 'config/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Require login
requireLogin();

// Get current user data
$user = getCurrentUser();
$user_id = getUserId();

// Fetch user stats (simulated; replace with database queries)
$stats = [
    'applications' => 3,
    'profile_views' => 125,
    'connections' => 42
];

// Fetch recent posts (simulated; replace with database query)
$recent_posts = [
    [
        'id' => 1,
        'author' => 'Global Education Network',
        'title' => 'Education Consultant • 15K followers',
        'time' => '2 hours ago',
        'type' => 'text',
        'content' => '<p>Excited to announce our new webinar on innovative teaching methods!</p>',
        'engagement' => ['likes' => 45, 'comments' => 12]
    ],
    [
        'id' => 2,
        'author' => 'Dubai International Academy',
        'title' => 'IB Mathematics Teacher',
        'time' => '1 day ago',
        'type' => 'job',
        'content' => '<h3>Job Opportunity: IB Mathematics Teacher</h3><p>We\'re hiring for the 2025-26 academic year. Apply now!</p>',
        'engagement' => ['likes' => 32, 'comments' => 8]
    ]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="TeacherConnect Pro Dashboard - Manage your teaching career">
    <title>Dashboard - TeacherConnect Pro</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Top Navigation -->
    <div class="top-nav">
        <div class="nav-container">
            <a href="index.php" class="logo" aria-label="TeacherConnect Home">
                <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 2l10 5.5v11L12 22 2 17.5V6.5L12 2z"/>
                </svg>
                TeacherConnect
            </a>
            <div class="search-bar">
                <input type="search" placeholder="🔍 Search jobs, teachers, schools..." aria-label="Search">
            </div>
            <div class="nav-icons">
                <a href="index.php" class="nav-icon" title="Home" aria-label="Home">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                    </svg>
                </a>
                <a href="network.php" class="nav-icon" title="Network" aria-label="Network">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>
                    </svg>
                </a>
                <a href="jobs.php" class="nav-icon" title="Jobs" aria-label="Jobs">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                        <path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/>
                    </svg>
                    <span class="notification-badge">5</span>
                </a>
                <a href="messages.php" class="nav-icon" title="Messages" aria-label="Messages">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>
                    </svg>
                    <span class="notification-badge">3</span>
                </a>
                <a href="notifications.php" class="nav-icon" title="Notifications" aria-label="Notifications">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 01-3.46 0"/>
                    </svg>
                    <span class="notification-badge">12</span>
                </a>
                <a href="profile.php" class="profile-pic" aria-label="User Profile"></a>
                <a href="logout.php" class="nav-icon" title="Logout" aria-label="Logout">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>

    <!-- Main Container -->
    <div class="main-container">
        <!-- Left Sidebar -->
        <aside class="left-sidebar">
            <div class="profile-card">
                <div class="profile-avatar"></div>
                <div class="profile-name"><?php echo htmlspecialchars($user['full_name']); ?></div>
                <div class="profile-role"><?php echo htmlspecialchars($user['role']); ?></div>
                <div class="profile-stats">
                    <div class="stat">
                        <div class="stat-number"><?php echo $stats['connections']; ?></div>
                        <div class="stat-label">Connections</div>
                    </div>
                    <div class="stat">
                        <div class="stat-number"><?php echo $stats['applications']; ?></div>
                        <div class="stat-label">Applications</div>
                    </div>
                    <div class="stat">
                        <div class="stat-number"><?php echo $stats['profile_views']; ?></div>
                        <div class="stat-label">Views</div>
                    </div>
                </div>
            </div>
            <div class="menu-card">
                <a href="index.php" class="menu-item active">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                    </svg>
                    Dashboard
                </a>
                <a href="jobs.php" class="menu-item">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                        <path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/>
                    </svg>
                    My Jobs
                </a>
                <a href="profile.php" class="menu-item">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                    My Profile
                </a>
                <a href="network.php" class="menu-item">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>
                    </svg>
                    Network
                </a>
                <a href="resources.php" class="menu-item">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <path d="M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3M12 17h.01"/>
                    </svg>
                    Resources
                </a>
            </div>
        </aside>

        <!-- Center Feed -->
        <main class="center-feed">
            <!-- Dashboard Stats -->
            <div class="dashboard-stats">
                <h2 class="dashboard-title">Your Dashboard</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $stats['applications']; ?></div>
                        <div class="stat-label">Job Applications</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $stats['profile_views']; ?></div>
                        <div class="stat-label">Profile Views</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $stats['connections']; ?></div>
                        <div class="stat-label">Connections</div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="recent-activity">
                <h3>Recent Activity</h3>
                <?php foreach ($recent_posts as $post): ?>
                    <div class="post-card" data-post-id="<?php echo $post['id']; ?>">
                        <div class="post-header">
                            <div class="post-avatar"></div>
                            <div class="post-author-info">
                                <div class="post-author-name"><?php echo htmlspecialchars($post['author']); ?></div>
                                <div class="post-author-title"><?php echo htmlspecialchars($post['title']); ?></div>
                                <div class="post-time"><?php echo htmlspecialchars($post['time']); ?></div>
                            </div>
                        </div>
                        <span class="post-tag <?php echo htmlspecialchars($post['type']); ?>">
                            <?php echo strtoupper(htmlspecialchars($post['type'])); ?>
                        </span>
                        <div class="post-content"><?php echo $post['content']; ?></div>
                        <div class="post-engagement">
                            <button class="engagement-btn" data-action="like" aria-label="Like post">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/>
                                </svg>
                                <?php echo $post['engagement']['likes']; ?> Likes
                            </button>
                            <button class="engagement-btn" data-action="comment" aria-label="Comment on post">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>
                                </svg>
                                <?php echo $post['engagement']['comments']; ?> Comments
                            </button>
                        </div>
                        <!-- Comment Section -->
                        <div class="post-comments">
                            <input type="text" class="comment-input" placeholder="Write a comment..." aria-label="Write a comment">
                            <div class="comment-list"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </main>

        <!-- Right Sidebar -->
        <aside class="right-sidebar">
            <div class="sidebar-card">
                <h3>Recommended Jobs</h3>
                <div class="suggestion-item">
                    <div class="suggestion-info">
                        <div class="suggestion-name">Science Teacher</div>
                        <div class="suggestion-role">Singapore International School</div>
                    </div>
                    <button class="apply-btn">Apply</button>
                </div>
                <div class="suggestion-item">
                    <div class="suggestion-info">
                        <div class="suggestion-name">English Teacher</div>
                        <div class="suggestion-role">Dubai Academy</div>
                    </div>
                    <button class="apply-btn">Apply</button>
                </div>
            </div>
            <div class="sidebar-card">
                <h3>Trending Topics</h3>
                <div class="trending-item">#InternationalEducation</div>
                <div class="trending-item">#TeachingAbroad</div>
            </div>
        </aside>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>