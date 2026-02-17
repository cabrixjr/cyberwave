<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';

if ($_SESSION['role'] !== 'employer') { exit("Unauthorized"); }

$db = getDB();
$app_id = (int)$_GET['id'];
$new_status = $_GET['set'];

// Masterpiece Security Logic:
// 1. If trying to restore a rejected person, we block it (as per your request)
$stmt = $db->prepare("SELECT status FROM applications WHERE id = ?");
$stmt->execute([$app_id]);
$current = $stmt->fetchColumn();

if ($current === 'rejected' && $new_status === 'pending') {
    header("Location: ../applicants.php?error=cannot_restore_rejected");
    exit;
}

// 2. Otherwise, update the status
$update = $db->prepare("UPDATE applications SET status = ? WHERE id = ?");
$update->execute([$new_status, $app_id]);

header("Location: ../applicants.php?msg=updated");
exit;