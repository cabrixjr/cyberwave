<?php
// post_job.php
session_start();

// Correct paths for the root folder
require_once 'includes/db.php';   
require_once 'includes/auth.php'; 

// Using the helper functions from auth.php
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

if ($_SESSION['role'] !== 'employer') {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post a Job | ConnectSphere</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #2563eb; --bg: #f8fafc; }
        body { background: var(--bg); font-family: 'Plus Jakarta Sans', sans-serif; margin: 0; }
        .form-container { 
            max-width: 700px; margin: 60px auto; background: white; 
            padding: 40px; border-radius: 24px; border: 1px solid #e2e8f0;
            box-shadow: 0 10px 15px rgba(0,0,0,0.05); 
        }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 600; color: #475569; }
        input, textarea, select { 
            width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 12px; font-family: inherit; 
        }
        .btn-submit { 
            background: var(--primary); color: white; border: none; padding: 16px; 
            border-radius: 14px; font-weight: 700; cursor: pointer; width: 100%; transition: 0.3s;
        }
        .btn-submit:hover { opacity: 0.9; transform: translateY(-2px); }
    </style>
</head>
<body>

    <?php include 'includes/navbar.php'; ?>

    <div class="form-container">
        <h2>🚀 Post a New Opportunity</h2>
        <p style="color: #64748b; margin-bottom: 30px;">Fill in the details to find the best workers in Tanzania.</p>
        
        <form action="handlers/job_handler.php" method="POST">
            <div class="form-group">
                <label>Job Title</label>
                <input type="text" name="title" placeholder="e.g. Senior Accountant" required>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label>Category</label>
                    <select name="category" required>
                        <option value="Construction">Construction</option>
                        <option value="Engineering">Engineering</option>
                        <option value="IT">IT & Software</option>
                        <option value="Education">Education</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Location</label>
                    <input type="text" name="location" placeholder="e.g. Dar es Salaam" required>
                </div>
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="6" placeholder="What are the requirements?" required></textarea>
            </div>

            <button type="submit" class="btn-submit">Publish Job Posting</button>
        </form>
    </div>
</body>
</html>