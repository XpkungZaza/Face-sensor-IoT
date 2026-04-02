<?php include 'db_config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | FaceCheck</title>
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
        <h1 style="margin-bottom: 2rem;">Teacher Dashboard Overview</h1>
        
        <!-- Summary Stats -->
        <div class="stats-grid">
            <div class="card stat-card">
                <h3>Total Enrolled</h3>
                <div class="stat-value" id="stat-total">-</div>
            </div>
            <div class="card stat-card success">
                <h3>Checked In (Present)</h3>
                <div class="stat-value" id="stat-present">-</div>
            </div>
            <div class="card stat-card danger">
                <h3>Not Checked In (Absent)</h3>
                <div class="stat-value" id="stat-absent">-</div>
            </div>
        </div>

        <div class="card">
            <h2 style="margin-bottom: 1.5rem;">Active Students (Present)</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>ID</th>
                            <th>Grade</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="active-students-table-body">
                        <!-- Populated iteratively by JS via filter() -->
                        <tr>
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
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script>
    <script src="script.js"></script>
</body>
</html>
