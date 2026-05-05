<?php
include 'db_config.php';
$current_page = 'classes';

// --- Get grade & class from URL (exact strings from DB) ---
$grade = isset($_GET['grade']) ? $_GET['grade'] : '';
$class = isset($_GET['class']) ? $_GET['class'] : '';

if (empty($grade) || empty($class)) {
    header('Location: classes.php');
    exit;
}

// --- Fetch students for this grade + class ---
$stmt = $conn->prepare("SELECT * FROM students WHERE grade = ? AND class = ? ORDER BY name ASC");
$stmt->bind_param("ss", $grade, $class);
$stmt->execute();
$result = $stmt->get_result();

$students = [];
while ($row = $result->fetch_assoc()) {
    $students[] = $row;
}
$stmt->close();

// --- Count stats ---
$total = count($students);
$onBus = 0;
$offBus = 0;
$absent = 0;
foreach ($students as $s) {
    if ($s['status'] === 'On Bus') $onBus++;
    elseif ($s['status'] === 'Off Bus') $offBus++;
    else $absent++;
}

// --- Grade color mapping ---
$gradeColors = [
    'ป.1' => '#007bff', 'ป.2' => '#0056b3', 'ป.3' => '#0dcaf0',
    'ป.4' => '#6610f2', 'ป.5' => '#6f42c1', 'ป.6' => '#d63384',
];
$color = $gradeColors[$grade] ?? '#007bff';
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= htmlspecialchars($grade) ?> / ห้อง <?= htmlspecialchars($class) ?> — Student list">
    <title><?= htmlspecialchars($grade) ?>/<?= htmlspecialchars($class) ?> | Smart-Bus-Guard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <?php include 'navbar.php'; ?>

    <div class="container">
        <!-- Back Button -->
        <a href="classes.php" class="back-link">← กลับหน้า All Classes</a>

        <!-- Class Title -->
        <div class="class-title-row">
            <h1>นักเรียนชั้น <?= htmlspecialchars($grade) ?> / ห้อง <?= htmlspecialchars($class) ?></h1>
            <span class="badge-grade" style="background: <?= $color ?>;"><?= htmlspecialchars($grade) ?></span>
        </div>
        <p style="color: var(--text-muted); margin-bottom: 2rem;">
            รายชื่อนักเรียนชั้น <?= htmlspecialchars($grade) ?> ห้อง <?= htmlspecialchars($class) ?> ทั้งหมด <?= $total ?> คน
        </p>

        <!-- Mini Stats -->
        <div class="mini-stats">
            <div class="mini-stat animate-in">
                <h4>ทั้งหมด</h4>
                <div class="val total"><?= $total ?></div>
            </div>
            <div class="mini-stat animate-in" style="animation-delay:0.1s">
                <h4>🚌 On Bus</h4>
                <div class="val on-bus"><?= $onBus ?></div>
            </div>
            <div class="mini-stat animate-in" style="animation-delay:0.2s">
                <h4>🏠 Off Bus</h4>
                <div class="val off-bus"><?= $offBus ?></div>
            </div>
            <div class="mini-stat animate-in" style="animation-delay:0.3s">
                <h4>❌ Absent</h4>
                <div class="val absent"><?= $absent ?></div>
            </div>
        </div>

        <!-- Student List -->
        <div class="card animate-in" style="padding: 0; overflow: hidden; animation-delay:0.3s;">
            <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border); display:flex; align-items:center; gap:8px;">
                <h2 style="margin:0; font-size: 1.1rem;">📋 รายชื่อนักเรียน</h2>
            </div>
            <div style="padding: 1rem;">
                <?php if ($total === 0): ?>
                    <div class="empty-state">
                        <div class="icon">📭</div>
                        <h3>ยังไม่มีนักเรียนในห้องนี้</h3>
                    </div>
                <?php else: ?>
                    <?php foreach ($students as $i => $s): ?>
                        <?php
                            $initials = mb_strtoupper(mb_substr($s['name'] ?? '?', 0, 1, 'UTF-8'), 'UTF-8');
                            $status = $s['status'] ?? '';
                            if ($status === 'On Bus') {
                                $pillClass = 'pill-on-bus';
                                $pillText = 'On Bus';
                            } elseif ($status === 'Off Bus') {
                                $pillClass = 'pill-off-bus';
                                $pillText = 'Off Bus';
                            } else {
                                $pillClass = 'pill-absent';
                                $pillText = 'Absent';
                            }
                            $lastSeen = $s['lastseen'] ?? '-';
                            $pic = $s['pic'] ?? '';
                        ?>
                        <div class="student-list-item" style="animation-delay: <?= $i * 0.05 ?>s;">
                            <div class="student-info">
                                <div class="student-avatar" style="background: <?= $color ?>15; color: <?= $color ?>; border-color: <?= $color ?>30;">
                                    <?php if ($pic): ?>
                                        <img src="<?= htmlspecialchars($pic) ?>" alt="<?= htmlspecialchars($s['name']) ?>">
                                    <?php else: ?>
                                        <?= $initials ?>
                                    <?php endif; ?>
                                </div>
                                <div class="student-name-id">
                                    <h3><?= htmlspecialchars($s['name'] ?? 'Unknown') ?></h3>
                                    <p>ID: <?= htmlspecialchars($s['student_id'] ?? '-') ?></p>
                                </div>
                            </div>
                            <div class="student-right">
                                <span class="status-pill <?= $pillClass ?>"><?= $pillText ?></span>
                                <div class="last-seen"><?= htmlspecialchars($lastSeen) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>

</body>
</html>
