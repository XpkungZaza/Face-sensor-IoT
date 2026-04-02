<?php include 'db_config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Detail | FaceCheck</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <nav class="navbar">
    <a href="index.html" class="navbar-brand-container">
        <img src="image/tharit-face-scan.jpg" alt="Logo" class="logo-img">
        <span class="navbar-brand">Smart-Bus-Guard</span>
    </a>
    
    <ul class="nav-links">
        <li><a href="index.php" class="active">Dashboard</a></li>
        <li><a href="id-list.php">Students</a></li>
        <li><a href="user-info.php">Admin</a></li>
        <li><a href="login.php">Logout</a></li>
    </ul>

    
    </nav>
    <div class="container">
        <div style="margin-bottom: 2rem;">
            <a href="id-list.html" style="text-decoration: none; color: var(--text-muted); font-weight: 500;">&larr; Back to Students</a>
        </div>
        
        <!-- Detail container populated by JS -->
        <div id="student-detail-container" class="detail-container">
            <!-- Loading placeholder -->
            <div class="card" style="text-align: center; padding: 3rem;">
                <p style="color: var(--text-muted);">Loading student profile...</p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script>
    <script src="script.js"></script>
</body>
</html>
