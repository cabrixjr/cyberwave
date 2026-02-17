<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';
requireLogin();

$teacher_id = isset($_GET['teacher_id']) ? (int)$_GET['teacher_id'] : 0;
$stmt = $pdo->prepare("SELECT tp.*, u.full_name, u.email FROM teacher_profiles tp JOIN users u ON tp.user_id = u.id WHERE tp.id = ?");
$stmt->execute([$teacher_id]);
$profile = $stmt->fetch();

$stmt = $pdo->prepare("SELECT * FROM teacher_documents WHERE teacher_id = ?");
$stmt->execute([$teacher_id]);
$documents = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT * FROM teacher_referees WHERE teacher_id = ?");
$stmt->execute([$teacher_id]);
$referees = $stmt->fetchAll();
?>
<?php require_once 'includes/header.php'; ?>
<?php include 'includes/layout.php'; ?>
<?php
$id = (int)$_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM users u LEFT JOIN profiles p ON u.id = p.user_id WHERE u.id = ?");
$stmt->execute([$id]);
$profile = $stmt->fetch();
?>
<h2><?php echo htmlspecialchars($profile['full_name']); ?></h2>
<img src="uploads/avatars/<?php echo $profile['dp_path']; ?>" alt="Profile">
<p>Bio: <?php echo htmlspecialchars($profile['bio']); ?></p>
<!-- Docs, referees for teachers; company details for employers -->
<button class="follow-btn">Follow</button>
<button class="apply-btn">Message</button>
<?php include 'includes/footer.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Teacher Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="css/styles.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4">
        <h1 class="text-3xl mb-4"><?php echo htmlspecialchars($profile['full_name'] ?? 'Teacher Profile'); ?></h1>
        <?php if ($profile): ?>
            <div class="bg-white p-6 rounded shadow-md mb-6">
                <h2 class="text-2xl mb-4">Personal Details</h2>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($profile['email']); ?></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($profile['phone'] ?? 'N/A'); ?></p>
                <p><strong>Address:</strong> <?php echo htmlspecialchars($profile['address'] ?? 'N/A'); ?></p>
                <p><strong>Bio:</strong> <?php echo htmlspecialchars($profile['bio'] ?? 'N/A'); ?></p>
                <p><strong>Qualifications:</strong> <?php echo htmlspecialchars($profile['qualifications']); ?></p>
                <p><strong>Subjects:</strong> <?php echo htmlspecialchars($profile['subjects']); ?></p>
                <p><strong>Teaching Level:</strong> <?php echo htmlspecialchars($profile['teaching_level']); ?></p>
                <p><strong>Experience:</strong> <?php echo htmlspecialchars($profile['experience_years']); ?> years</p>
                <p><strong>Location:</strong> <?php echo htmlspecialchars($profile['location'] ?? 'N/A'); ?></p>
                <?php if ($profile['portfolio_link']): ?>
                    <p><strong>Portfolio:</strong> <a href="<?php echo htmlspecialchars($profile['portfolio_link']); ?>" class="text-blue-500">View</a></p>
                <?php endif; ?>
                <?php if ($profile['video_introduction']): ?>
                    <p><strong>Video:</strong> <a href="<?php echo htmlspecialchars($profile['video_introduction']); ?>" class="text-blue-500">View</a></p>
                <?php endif; ?>
            </div>

            <div class="bg-white p-6 rounded shadow-md mb-6">
                <h2 class="text-2xl mb-4">Documents</h2>
                <?php if ($documents): ?>
                    <ul class="document-list">
                        <?php foreach ($documents as $doc): ?>
                            <li>
                                <a href="uploads/documents/<?php echo htmlspecialchars($doc['file_path']); ?>" class="text-blue-500" target="_blank"><?php echo htmlspecialchars($doc['file_name']); ?> (<?php echo htmlspecialchars($doc['document_type']); ?>)</a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>No documents available.</p>
                <?php endif; ?>
            </div>

            <div class="bg-white p-6 rounded shadow-md">
                <h2 class="text-2xl mb-4">Referees</h2>
                <?php if ($referees): ?>
                    <ul class="referee-list">
                        <?php foreach ($referees as $referee): ?>
                            <li>
                                <strong><?php echo htmlspecialchars($referee['referee_name']); ?></strong> (<?php echo htmlspecialchars($referee['relationship']); ?>)
                                <p>Email: <?php echo htmlspecialchars($referee['referee_email'] ?? 'N/A'); ?>, Phone: <?php echo htmlspecialchars($referee['referee_phone'] ?? 'N/A'); ?></p>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>No referees listed.</p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <p class="text-red-500">Profile not found.</p>
        <?php endif; ?>
    </div>
</body>
</html>