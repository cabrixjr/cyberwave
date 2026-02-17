<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once '../includes/db.php';
require_once '../includes/auth.php';

$app_id = (int)($_GET['app_id'] ?? 0);
$user_id = $_SESSION['user_id'];

$db = getDB();
$stmt = $db->prepare("DELETE FROM applications WHERE id = ? AND user_id = ? AND status = 'pending'");
$stmt->execute([$app_id, $user_id]);

header("Location: ../find_jobs.php?msg=withdrawn");
exit;