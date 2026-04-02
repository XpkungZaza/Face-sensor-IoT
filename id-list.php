<?php include 'db_config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student List | Smarth-Bus-Guard</title>
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
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h1>Student Registry</h1>
        </div>
        
        <!-- Dynamic grid populated by JS -->
        <div id="student-grid" class="student-grid">
            <!-- Loading placeholder -->
            <div class="card" style="grid-column: 1 / -1; text-align: center; padding: 3rem;">
                <div class="list-group">
    <?php
    // ดึงข้อมูลเด็กที่เพิ่งเช็คอินล่าสุด 5 คน
    $sql = "SELECT * FROM students ORDER BY lastseen DESC LIMIT 5";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            // เลือกสีสถานะ
            $badge_color = ($row['status'] == 'On Bus') ? 'bg-success' : 'bg-secondary';
            
            echo '<div class="list-group-item d-flex justify-content-between align-items-center">';
            echo '  <div>';
            echo '    <h6 class="mb-0">' . $row['name'] . '</h6>';
            echo '    <small class="text-muted">' . $row['lastseen'] . '</small>';
            echo '  </div>';
            echo '  <span class="badge ' . $badge_color . '">' . $row['status'] . '</span>';
            echo '</div>';
        }
    } else {
        echo '<p class="text-center">ยังไม่มีข้อมูลการเช็คอิน</p>';
    }
    ?>
</div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script>
    <script src="script.js"></script>
</body>
</html>