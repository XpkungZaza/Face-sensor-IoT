<?php
include 'db_config.php';
$current_page = 'classes';

// --- Fetch summary stats ---
$stats_sql = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'On Bus' THEN 1 ELSE 0 END) as on_bus,
    SUM(CASE WHEN status = 'Off Bus' THEN 1 ELSE 0 END) as off_bus,
    SUM(CASE WHEN status = 'Absent' THEN 1 ELSE 0 END) as absent
    FROM students";
$stats_result = $conn->query($stats_sql);
$stats = $stats_result->fetch_assoc();
$grandTotal = (int)($stats['total'] ?? 0);
$grandOnBus = (int)($stats['on_bus'] ?? 0);
$grandOffBus = (int)($stats['off_bus'] ?? 0);
$grandAbsent = (int)($stats['absent'] ?? 0);

// --- Fetch class data grouped by grade, class ---
$class_sql = "SELECT grade, class, 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'On Bus' THEN 1 ELSE 0 END) as on_bus,
    SUM(CASE WHEN status = 'Off Bus' THEN 1 ELSE 0 END) as off_bus,
    SUM(CASE WHEN status = 'Absent' THEN 1 ELSE 0 END) as absent
    FROM students 
    GROUP BY grade, class 
    ORDER BY grade ASC, class ASC";
$class_result = $conn->query($class_sql);

// Build a PHP lookup: grade => [ class_data, class_data, ... ]
$gradeGroups = [];
if ($class_result && $class_result->num_rows > 0) {
    while ($row = $class_result->fetch_assoc()) {
        $g = $row['grade'];
        if (!isset($gradeGroups[$g])) {
            $gradeGroups[$g] = [];
        }
        $gradeGroups[$g][] = [
            'class'  => $row['class'],
            'total'  => (int)$row['total'],
            'on_bus' => (int)$row['on_bus'],
            'off_bus'=> (int)$row['off_bus'],
            'absent' => (int)$row['absent'],
        ];
    }
}

// Grade color mapping
$gradeColors = [
    'ป.1' => ['bg' => 'linear-gradient(135deg, #007bff, #3395ff)', 'bar' => '#007bff'],
    'ป.2' => ['bg' => 'linear-gradient(135deg, #0056b3, #007bff)', 'bar' => '#0056b3'],
    'ป.3' => ['bg' => 'linear-gradient(135deg, #0dcaf0, #20c9dc)', 'bar' => '#0dcaf0'],
    'ป.4' => ['bg' => 'linear-gradient(135deg, #6610f2, #8540f5)', 'bar' => '#6610f2'],
    'ป.5' => ['bg' => 'linear-gradient(135deg, #6f42c1, #8b5cf6)', 'bar' => '#6f42c1'],
    'ป.6' => ['bg' => 'linear-gradient(135deg, #d63384, #e85dad)', 'bar' => '#d63384'],
];

$gradeEmojis = [
    'ป.1' => '🔵', 'ป.2' => '💙', 'ป.3' => '🩵',
    'ป.4' => '💜', 'ป.5' => '🟣', 'ป.6' => '💗',
];
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="All Classes — View student groups organized by grade and class.">
    <title>All Classes | Smart-Bus-Guard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <?php include 'navbar.php'; ?>

    <div class="container">
        <!-- Page Header -->
        <div class="page-header">
            <h1>ระบบตรวจสอบเด็กติดในรถรับส่งด้วยเทคโนโลยี AIoT</h1>
            <p>ดูสถานะรวมของนักเรียนทุกชั้น — คลิกเพื่อดูรายละเอียด</p>
        </div>

        <!-- Summary Stats -->
        <div class="stats-row">
            <div class="stat-card total animate-in">
                <div class="stat-icon">👥</div>
                <div class="stat-info">
                    <h4>Total Students</h4>
                    <div class="stat-number"><?= $grandTotal ?></div>
                </div>
            </div>
            <div class="stat-card on-bus animate-in" style="animation-delay:0.1s">
                <div class="stat-icon">🚌</div>
                <div class="stat-info">
                    <h4>On Bus</h4>
                    <div class="stat-number"><?= $grandOnBus ?></div>
                </div>
            </div>
            <div class="stat-card off-bus animate-in" style="animation-delay:0.2s">
                <div class="stat-icon">🏠</div>
                <div class="stat-info">
                    <h4>Off Bus</h4>
                    <div class="stat-number"><?= $grandOffBus ?></div>
                </div>
            </div>
            <div class="stat-card absent animate-in" style="animation-delay:0.3s">
                <div class="stat-icon">❌</div>
                <div class="stat-info">
                    <h4>Absent</h4>
                    <div class="stat-number"><?= $grandAbsent ?></div>
                </div>
            </div>
        </div>

        <!-- Class Sections (PHP rendered) -->
        <?php if (empty($gradeGroups)): ?>
            <div class="empty-state">
                <div class="icon">📭</div>
                <h3>ยังไม่มีข้อมูลห้องเรียน</h3>
            </div>
        <?php else: ?>
            <?php $gradeIndex = 0; foreach ($gradeGroups as $grade => $classList): ?>
                <?php 
                    $gradeTotal = 0;
                    foreach ($classList as $c) $gradeTotal += $c['total'];
                    $color = $gradeColors[$grade] ?? ['bg' => 'linear-gradient(135deg, #007bff, #3395ff)', 'bar' => '#007bff'];
                    $emoji = $gradeEmojis[$grade] ?? '📘';
                ?>
                <div class="grade-section animate-in" style="animation-delay: <?= $gradeIndex * 0.1 ?>s;">
                    <div class="grade-header">
                        <div class="grade-icon" style="background: <?= $color['bg'] ?>;"><?= htmlspecialchars($grade) ?></div>
                        <h2>ชั้น <?= htmlspecialchars($grade) ?></h2>
                        <span class="grade-count"><?= $gradeTotal ?> คน</span>
                    </div>
                    <div class="class-grid">
                        <?php foreach ($classList as $idx => $classInfo): ?>
                            <?php
                                $pctOnBus = $classInfo['total'] > 0 ? ($classInfo['on_bus'] / $classInfo['total'] * 100) : 0;
                                $pctOffBus = $classInfo['total'] > 0 ? ($classInfo['off_bus'] / $classInfo['total'] * 100) : 0;
                                $pctAbsent = $classInfo['total'] > 0 ? ($classInfo['absent'] / $classInfo['total'] * 100) : 0;
                            ?>
                            <a href="class_detail.php?grade=<?= urlencode($grade) ?>&class=<?= urlencode($classInfo['class']) ?>" 
                               class="class-card" 
                               style="animation-delay: <?= $idx * 0.08 ?>s; --card-color: <?= $color['bar'] ?>;">
                                <div class="class-card-header">
                                    <span class="class-card-title"><?= $emoji ?> ห้อง <?= htmlspecialchars($classInfo['class']) ?></span>
                                    <span class="class-card-total"><?= $classInfo['total'] ?> คน</span>
                                </div>
                                <div class="status-bar-wrap">
                                    <div class="status-bar">
                                        <div class="status-bar-fill bar-on-bus" style="width:<?= $pctOnBus ?>%"></div>
                                        <div class="status-bar-fill bar-off-bus" style="width:<?= $pctOffBus ?>%"></div>
                                        <div class="status-bar-fill bar-absent" style="width:<?= $pctAbsent ?>%"></div>
                                    </div>
                                </div>
                                <div class="status-detail">
                                    <div class="status-item">
                                        <span class="status-dot dot-on-bus"></span>
                                        <span>On Bus <strong><?= $classInfo['on_bus'] ?></strong></span>
                                    </div>
                                    <div class="status-item">
                                        <span class="status-dot dot-off-bus"></span>
                                        <span>Off Bus <strong><?= $classInfo['off_bus'] ?></strong></span>
                                    </div>
                                    <div class="status-item">
                                        <span class="status-dot dot-absent"></span>
                                        <span>Absent <strong><?= $classInfo['absent'] ?></strong></span>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php $gradeIndex++; endforeach; ?>
        <?php endif; ?>
    </div>

    <?php include 'footer.php'; ?>

</body>
</html>
