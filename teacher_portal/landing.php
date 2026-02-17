<?php
session_start();
// Check login status if needed
$isLoggedIn = false; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JobConnect | Premium Career Network</title>
    
    <style>
        /* --- BRANDING & VARIABLES --- */
        :root {
            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --accent: #7c3aed;
            --dark: #0f172a;
            --light-text: #64748b;
            --bg-subtle: #f8fafc;
            --white: #ffffff;
            --shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; outline: none; }

        body {
            font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
            background-color: var(--white);
            color: var(--dark);
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* --- NAVIGATION --- */
        nav {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
            padding: 1.2rem 10%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .logo { 
            font-size: 1.6rem; 
            font-weight: 800; 
            color: var(--primary); 
            text-decoration: none;
            letter-spacing: -1px;
        }

        .nav-links a { 
            text-decoration: none; 
            color: var(--dark); 
            margin-left: 25px; 
            font-weight: 600;
            font-size: 0.95rem;
            transition: 0.3s;
        }

        .nav-links a:hover { color: var(--primary); }

        .btn-login { 
            background: var(--bg-subtle);
            padding: 10px 22px; 
            border-radius: 10px; 
            border: 1px solid #e2e8f0;
        }

        /* --- HERO SECTION --- */
        .hero {
            padding: 120px 10% 80px;
            background: radial-gradient(circle at top right, #eff6ff, transparent 40%),
                        radial-gradient(circle at bottom left, #f5f3ff, transparent 40%);
            text-align: center;
        }

        .hero h1 { 
            font-size: 4rem; 
            font-weight: 800;
            margin-bottom: 20px; 
            letter-spacing: -2px;
            line-height: 1.1;
        }

        .hero h1 span {
            background: linear-gradient(to right, var(--primary), var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero p { 
            font-size: 1.25rem; 
            color: var(--light-text); 
            margin-bottom: 45px; 
            max-width: 650px;
            margin-left: auto;
            margin-right: auto;
        }

        /* --- ADVANCED SEARCH BAR --- */
        .search-wrapper {
            background: white;
            padding: 12px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            box-shadow: 0 20px 50px rgba(0,0,0,0.08);
            max-width: 850px;
            margin: 0 auto;
            border: 1px solid #f1f5f9;
        }

        .search-group {
            flex: 1;
            display: flex;
            align-items: center;
            padding: 0 15px;
            border-right: 1px solid #eee;
        }

        .search-group:last-of-type { border-right: none; }

        .search-group input {
            border: none;
            padding: 12px;
            width: 100%;
            font-size: 1rem;
            font-weight: 500;
        }

        .btn-search {
            background: var(--primary);
            color: white;
            border: none;
            padding: 15px 40px;
            border-radius: 15px;
            cursor: pointer;
            font-weight: 700;
            transition: 0.3s;
        }

        .btn-search:hover { background: var(--primary-hover); transform: scale(1.02); }

        /* --- STATS SECTION --- */
        .stats {
            display: flex;
            justify-content: center;
            gap: 50px;
            margin-top: 60px;
        }

        .stat-item b { font-size: 1.5rem; color: var(--dark); }
        .stat-item p { font-size: 0.9rem; color: var(--light-text); }

        /* --- JOB CATEGORIES --- */
        .section-header { padding: 80px 10% 0; text-align: center; }
        .section-header h2 { font-size: 2.2rem; font-weight: 800; }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            padding: 40px 10% 80px;
        }

        .cat-card {
            background: var(--white);
            padding: 40px 30px;
            border-radius: 24px;
            text-align: left;
            border: 1px solid #f1f5f9;
            transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            text-decoration: none;
            color: inherit;
        }

        .cat-card:hover { 
            transform: translateY(-10px); 
            box-shadow: var(--shadow);
            border-color: var(--primary);
        }

        .icon-box {
            font-size: 2rem;
            background: var(--bg-subtle);
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 15px;
            margin-bottom: 20px;
        }

        .cat-card h3 { margin-bottom: 8px; font-size: 1.2rem; }
        .cat-card p { color: var(--light-text); font-size: 0.9rem; }

        /* --- FOOTER --- */
        footer { 
            background: var(--dark); 
            color: rgba(255,255,255,0.6); 
            padding: 60px 10%; 
            text-align: center;
            border-top: 1px solid rgba(255,255,255,0.1);
        }

        footer b { color: white; }

        /* Mobile Adjustments */
        @media (max-width: 768px) {
            .hero h1 { font-size: 2.5rem; }
            .search-wrapper { flex-direction: column; border-radius: 20px; }
            .search-group { border-right: none; border-bottom: 1px solid #eee; width: 100%; }
            .btn-search { width: 100%; margin-top: 10px; }
            .stats { flex-direction: column; gap: 20px; }
        }
    </style>
</head>
<body>

    <nav>
        <a href="#" class="logo">JobConnect<span>.</span></a>
        <div class="nav-links">
            <a href="find_jobs.php">Explore Jobs</a>
            <a href="find_jobs.php">Companies</a>
            <?php if(!$isLoggedIn): ?>
                <a href="login.php" class="btn-login">Sign In</a>
                <a href="register.php" class="btn-search" style="padding: 10px 25px; text-decoration: none;">Join Free</a>
            <?php endif; ?>
        </div>
    </nav>

    <header class="hero">
        <div style="text-transform: uppercase; font-weight: 800; font-size: 0.8rem; color: var(--primary); letter-spacing: 2px; margin-bottom: 15px;">
            The #1 Job Board for Professionals
        </div>
        <h1>Find your next <span>career milestone</span></h1>
        <p>JobConnect uses AI-driven insights to match elite talent with world-class opportunities across the globe.</p>
        
        <form class="search-wrapper" action="search.php" method="GET">
            <div class="search-group">
                <span>🔍</span>
                <input type="text" name="query" placeholder="Job title, keywords...">
            </div>
            <div class="search-group">
                <span>📍</span>
                <input type="text" name="location" placeholder="City or Remote">
            </div>
            <button type="submit" class="btn-search">Find Jobs</button>
        </form>

        <div class="stats">
            <div class="stat-item"><b>12k+</b> <p>Active Jobs</p></div>
            <div class="stat-item"><b>8k+</b> <p>Companies</p></div>
            <div class="stat-item"><b>25k+</b> <p>Success Stories</p></div>
        </div>
    </header>

    <div class="section-header">
        <h2>Browse by Category</h2>
        <p style="color: var(--light-text);">Explore high-demand roles in top industries</p>
    </div>

    <section class="grid">
        <a href="find_jobs.php?id=tech" class="cat-card">
            <div class="icon-box">💻</div>
            <h3>Technology</h3>
            <p>Software Engineering, AI, Cloud Computing</p>
            <span style="color: var(--primary); font-size: 0.8rem; font-weight: 700; display: block; margin-top: 15px;">1,240 Openings &rarr;</span>
        </a>

        <a href="find_jobs.php?id=design" class="cat-card">
            <div class="icon-box">🎨</div>
            <h3>Creative & Design</h3>
            <p>UI/UX Design, Branding, Illustration</p>
            <span style="color: var(--primary); font-size: 0.8rem; font-weight: 700; display: block; margin-top: 15px;">850 Openings &rarr;</span>
        </a>

        <a href="find_jobs.php?id=marketing" class="cat-card">
            <div class="icon-box">📈</div>
            <h3>Marketing</h3>
            <p>SEO, Digital Strategy, Content Creation</p>
            <span style="color: var(--primary); font-size: 0.8rem; font-weight: 700; display: block; margin-top: 15px;">512 Openings &rarr;</span>
        </a>

        <a href="find_jobs.php?id=management" class="cat-card">
            <div class="icon-box">💼</div>
            <h3>Management</h3>
            <p>Project Managers, CTOs, HR Directors</p>
            <span style="color: var(--primary); font-size: 0.8rem; font-weight: 700; display: block; margin-top: 15px;">320 Openings &rarr;</span>
        </a>
    </section>

    <footer>
        <p>Built for the future of work. <b>JobConnect Pro</b></p>
        <p style="margin-top: 10px; font-size: 0.8rem;">&copy; <?php echo date("Y"); ?> All Rights Reserved.</p>
    </footer>

</body>
</html>