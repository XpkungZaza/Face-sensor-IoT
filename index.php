<?php include 'db_config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smarth-Bus-Guard</title>
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
        <h1 style="margin-bottom: 2rem;">Main Dashboard</h1>
        
        <div class="dashboard-grid">
            <section>
                <div class="card">
                    <h2 style="margin-bottom: 1rem;">Last Face Scan</h2>
                    <!-- Camera Feed -->
                    <video  class="camera-placeholder" autoplay playsinline muted style="object-fit: cover;"></video>
                    <p style="color: var(--success); font-weight: 500;">Status: System Online - Waiting for face...</p>
                </div>
            </section>

            <aside>
                <div class="card" style="height: 100%;">
                    <h2 style="margin-bottom: 1.5rem;">Recent Check-ins</h2>
                    <!-- Dynamic live log container -->
                    <div id="live-log-container" class="live-log-list">
                        <!-- Populated by JS -->
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
            </aside>
        </div>
    </div>

    
    <script src="script.js"></script>
</body>
</html>