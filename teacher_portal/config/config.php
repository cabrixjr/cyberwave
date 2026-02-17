<?php

define('DB_HOST', 'localhost');
define('DB_NAME', 'teacher_portal');
define('DB_USER', 'root');
define('DB_PASS', '');


define('SESSION_TIMEOUT', 3600);
define('UPLOAD_DIR', 'Uploads/');
define('MAX_UPLOAD_SIZE', 50 * 1024 * 1024); 
define('ALLOWED_FILE_TYPES', ['image/jpeg', 'image/png', 'video/mp4', 'application/pdf']);

$log_dir = __DIR__ . '/logs';
if (!is_dir($log_dir)) {
    mkdir($log_dir, 0755, true);
}