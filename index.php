<?php 
include 'db_config.php';
$current_page = 'home';

// --- Fetch summary counts ---
$total_result = $conn->query("SELECT COUNT(*) as total FROM students");
$total_students = $total_result->fetch_assoc()['total'] ?? 0;

$onbus_result = $conn->query("SELECT COUNT(*) as cnt FROM students WHERE status = 'On Bus'");
$on_bus_count = $onbus_result->fetch_assoc()['cnt'] ?? 0;

$offbus_result = $conn->query("SELECT COUNT(*) as cnt FROM students WHERE status = 'Off Bus'");
$off_bus_count = $offbus_result->fetch_assoc()['cnt'] ?? 0;

$absent_result = $conn->query("SELECT COUNT(*) as cnt FROM students WHERE status = 'Absent'");
$absent_count = $absent_result->fetch_assoc()['cnt'] ?? 0;

// --- Fetch last 5 scans ---
$recent_sql = "SELECT * FROM students ORDER BY lastseen DESC LIMIT 5";
$recent_result = $conn->query($recent_sql);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Smart-Bus-Guard Dashboard — Real-time IoT student tracking system for school buses.">
    <title>Dashboard | Smart-Bus-Guard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <?php include 'navbar.php'; ?>

    <div class="container">
        <!-- Page Header -->
        <div class="page-header">
            <h1>ระบบตรวจสอบเด็กติดในรถรับส่งด้วยเทคโนโลยี AIoT</h1>
            <p>ภาพรวมสถานะนักเรียนทั้งหมดในระบบ Smart-Bus-Guard</p>
        </div>

        <!-- Summary Stats -->
        <div class="stats-row">
            <div class="stat-card total animate-in" style="animation-delay: 0s;">
                <div class="stat-icon">👥</div>
                <div class="stat-info">
                    <h4>Total Students</h4>
                    <div class="stat-number" id="stat-total"><?= $total_students ?></div>
                </div>
            </div>
            <div class="stat-card on-bus animate-in" style="animation-delay: 0.1s;">
                <div class="stat-icon">🚌</div>
                <div class="stat-info">
                    <h4>On Bus</h4>
                    <div class="stat-number" id="stat-onbus"><?= $on_bus_count ?></div>
                </div>
            </div>
            <div class="stat-card off-bus animate-in" style="animation-delay: 0.2s;">
                <div class="stat-icon">🏠</div>
                <div class="stat-info">
                    <h4>Off Bus</h4>
                    <div class="stat-number" id="stat-offbus"><?= $off_bus_count ?></div>
                </div>
            </div>
            <div class="stat-card absent animate-in" style="animation-delay: 0.3s;">
                <div class="stat-icon">❌</div>
                <div class="stat-info">
                    <h4>Absent</h4>
                    <div class="stat-number" id="stat-absent"><?= $absent_count ?></div>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="dashboard-grid">
            <!-- Left: Last Face Scan / Camera -->
            <section class="animate-in" style="animation-delay: 0.3s;">
    <div class="card">
        <h2 style="margin-bottom: 1rem; font-size: 1.15rem; display: flex; align-items: center;">
            📷 Last Face Scan
        </h2>
        
        <div style="width: 100%; max-width: 220px; height: 280px; margin: 0 auto; background: #000; display: flex; align-items: center; justify-content: center; overflow: hidden; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
            <img id="live-face" src="assets/last_scan.jpg" style="width: 100%; height: 100%; object-fit: cover; object-position: center;">
        </div>
        
        <p style="color: var(--success); font-weight: 500; font-size: 0.9rem; display: flex; align-items: center; justify-content: center; margin-top: 15px;">
            <span style="width: 8px; height: 8px; background: var(--success); border-radius: 50%; display: inline-block; margin-right: 8px;"></span>
            System Online — Watching for face...
        </p>
    </div>
</section>

            <!-- Right: Recent Scans -->
            <aside class="animate-in" style="animation-delay: 0.4s;">
                <div class="card" style="height: 100%;">
                    <h2 style="margin-bottom: 1.25rem; font-size: 1.15rem; display: flex; align-items: center; gap: 8px;">
                        🕐 Recent Scans
                    </h2>
                    <div class="recent-scan-list">
                        <?php if ($recent_result && $recent_result->num_rows > 0): ?>
                            <?php while($row = $recent_result->fetch_assoc()): ?>
                                <?php 
                                    $initials = mb_strtoupper(mb_substr($row['name'], 0, 1, 'UTF-8'), 'UTF-8');
                                    $status = $row['status'] ?? '';
                                    if ($status === 'On Bus') {
                                        $badge_class = 'on-bus';
                                        $badge_text = 'On Bus';
                                    } elseif ($status === 'Off Bus') {
                                        $badge_class = 'off-bus';
                                        $badge_text = 'Off Bus';
                                    } else {
                                        $badge_class = 'absent';
                                        $badge_text = 'Absent';
                                    }
                                    $pic = !empty($row['pic']) ? $row['pic'] : '';
                                ?>
                                <div class="scan-item">
                                    <?php if ($pic): ?>
                                        <img src="<?= htmlspecialchars($pic) ?>" alt="<?= htmlspecialchars($row['name']) ?>" class="scan-avatar">
                                    <?php else: ?>
                                        <div class="scan-avatar-placeholder"><?= $initials ?></div>
                                    <?php endif; ?>
                                    <div class="scan-info">
                                        <h4><?= htmlspecialchars($row['name']) ?></h4>
                                        <p>ID: <?= htmlspecialchars($row['student_id']) ?></p>
                                    </div>
                                    <div class="scan-meta">
                                        <span class="badge <?= $badge_class ?>"><?= $badge_text ?></span>
                                        <span class="scan-time"><?= htmlspecialchars($row['lastseen'] ?? '-') ?></span>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="empty-state">
                                <div class="icon">📭</div>
                                <p>ยังไม่มีข้อมูลการเช็คอิน</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </aside>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script>
    // Auto webcam
    (async function() {
        const video = document.querySelector('video.camera-placeholder');
        if (!video) return;
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ video: true });
            video.srcObject = stream;
        } catch(e) {
            console.log('Camera not available');
        }
    })();
    setInterval(function() {
        var img = document.getElementById('live-face');
        if (img) {
            // การใส่ ?t= ต่อท้ายจะทำให้ Browser ไม่จำ Cache และดึงภาพใหม่จาก Assets เสมอ
            img.src = 'assets/last_scan.jpg?t=' + new Date().getTime();
        }
    }, 500);
    </script>
</body>
</html>