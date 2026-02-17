<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
requireLogin();

header('Content-Type: application/json');

$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action === 'like') {
    $data = json_decode(file_get_contents('php://input'), true);
    $post_id = (int)$data['post_id'];

    $stmt = $pdo->prepare("INSERT IGNORE INTO likes (user_id, post_id) VALUES (?, ?)");
    $stmt->execute([$_SESSION['user_id'], $post_id]);

    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM likes WHERE post_id = ?");
    $stmt->execute([$post_id]);
    $count = $stmt->fetch()['count'];

    createNotification($post_id, "Your post was liked by user ID {$_SESSION['user_id']}");

    echo json_encode(['success' => true, 'likes_count' => $count]);
}

if ($action === 'comment') {
    $data = json_decode(file_get_contents('php://input'), true);
    $post_id = (int)$data['post_id'];
    $content = sanitize($data['content']);

    $stmt = $pdo->prepare("INSERT INTO comments (user_id, post_id, content) VALUES (?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $post_id, $content]);

    createNotification($post_id, "Your post was commented on by user ID {$_SESSION['user_id']}");

    echo json_encode(['success' => true]);
}

if ($action === 'notifications') {
    $stmt = $pdo->prepare("SELECT message, created_at FROM notifications WHERE user_id = ? AND is_read = FALSE ORDER BY created_at DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $notifications = $stmt->fetchAll();

    echo json_encode(['notifications' => $notifications]);
}


// api.php (additions)
if ($action === 'teacher_profile') {
    $teacher_id = (int)$_GET['teacher_id'];
    $stmt = $pdo->prepare("SELECT * FROM teacher_profiles WHERE id = ?");
    $stmt->execute([$teacher_id]);
    $profile = $stmt->fetch();

    $stmt = $pdo->prepare("SELECT * FROM teacher_documents WHERE teacher_id = ?");
    $stmt->execute([$teacher_id]);
    $documents = $stmt->fetchAll();

    $stmt = $pdo->prepare("SELECT * FROM teacher_referees WHERE teacher_id = ?");
    $stmt->execute([$teacher_id]);
    $referees = $stmt->fetchAll();

    echo json_encode([
        'profile' => $profile,
        'documents' => $documents,
        'referees' => array_map(function($ref) {
            return [
                'name' => $ref['referee_name'],
                'email' => $ref['referee_email'],
                'phone' => $ref['referee_phone'],
                'relationship' => $ref['relationship']
            ];
        }, $referees)
    ]);
}


// ... existing code ...
if ($action === 'like') {
    // ... existing like code ...
}
if ($action === 'apply') {
    // Insert into applications, check if paid
    if (!$user['is_paid']) return json_encode(['error' => 'Subscribe first']);
    $job_id = (int)$_POST['job_id'];
    $stmt = $pdo->prepare("INSERT INTO applications (teacher_id, job_id) VALUES (?, ?)");
    $stmt->execute([$_SESSION['user_id'], $job_id]);
    echo json_encode(['success' => true]);
}



?>