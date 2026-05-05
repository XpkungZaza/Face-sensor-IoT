<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Class Status | Smart-Bus-Guard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        /* ===== All-Status Page Styles ===== */

        /* Grade Section */
        .grade-section {
            margin-bottom: 2.5rem;
        }

        .grade-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 1.25rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid var(--border);
        }

        .grade-icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            font-weight: 700;
            color: #fff;
            flex-shrink: 0;
        }

        .grade-header h2 {
            font-size: 1.35rem;
            font-weight: 700;
            color: var(--text-main);
            margin: 0;
        }

        .grade-header .grade-count {
            margin-left: auto;
            font-size: 0.85rem;
            color: var(--text-muted);
            background: var(--bg);
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: 500;
        }

        /* Class Cards Grid */
        .class-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 1rem;
        }

        .class-card {
            background: var(--card-bg);
            border-radius: 14px;
            padding: 1.25rem;
            border: 1px solid var(--border);
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            color: inherit;
            position: relative;
            overflow: hidden;
        }

        .class-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            border-radius: 14px 14px 0 0;
            transition: height 0.3s ease;
        }

        .class-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px -8px rgba(0,0,0,0.12);
        }

        .class-card:hover::before {
            height: 6px;
        }

        .class-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .class-card-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text-main);
        }

        .class-card-total {
            font-size: 0.8rem;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 20px;
            background: var(--bg);
            color: var(--text-muted);
        }

        /* Status Bar */
        .status-bar-wrap {
            margin-bottom: 0.75rem;
        }

        .status-bar {
            height: 8px;
            background: #e9ecef;
            border-radius: 99px;
            overflow: hidden;
            display: flex;
        }

        .status-bar-fill {
            height: 100%;
            transition: width 0.6s ease;
        }

        .bar-on-bus  { background: linear-gradient(90deg, #00b894, #55efc4); }
        .bar-arrived { background: linear-gradient(90deg, #0984e3, #74b9ff); }
        .bar-absent  { background: linear-gradient(90deg, #d63031, #ff7675); }

        /* Status Detail */
        .status-detail {
            display: flex;
            justify-content: space-between;
            gap: 6px;
        }

        .status-item {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 0.78rem;
            color: var(--text-muted);
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .dot-on-bus  { background: #00b894; }
        .dot-arrived { background: #0984e3; }
        .dot-absent  { background: #d63031; }

        .status-item strong {
            color: var(--text-main);
        }

        /* Grade Color Themes */
        .grade-1 .grade-icon { background: linear-gradient(135deg, #6c5ce7, #a29bfe); }
        .grade-1 .class-card::before { background: linear-gradient(90deg, #6c5ce7, #a29bfe); }

        .grade-2 .grade-icon { background: linear-gradient(135deg, #0984e3, #74b9ff); }
        .grade-2 .class-card::before { background: linear-gradient(90deg, #0984e3, #74b9ff); }

        .grade-3 .grade-icon { background: linear-gradient(135deg, #00b894, #55efc4); }
        .grade-3 .class-card::before { background: linear-gradient(90deg, #00b894, #55efc4); }

        .grade-4 .grade-icon { background: linear-gradient(135deg, #e17055, #fab1a0); }
        .grade-4 .class-card::before { background: linear-gradient(90deg, #e17055, #fab1a0); }

        .grade-5 .grade-icon { background: linear-gradient(135deg, #fdcb6e, #ffeaa7); }
        .grade-5 .class-card::before { background: linear-gradient(90deg, #fdcb6e, #ffeaa7); }

        .grade-6 .grade-icon { background: linear-gradient(135deg, #e84393, #fd79a8); }
        .grade-6 .class-card::before { background: linear-gradient(90deg, #e84393, #fd79a8); }

        /* Summary Stats Bar */
        .page-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .page-stat-card {
            background: var(--card-bg);
            border-radius: 12px;
            padding: 1.25rem;
            border: 1px solid var(--border);
            text-align: center;
            transition: transform 0.2s;
        }

        .page-stat-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .page-stat-card h4 {
            font-size: 0.8rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }

        .page-stat-value {
            font-size: 2rem;
            font-weight: 700;
        }

        .page-stat-value.total   { color: var(--primary); }
        .page-stat-value.on-bus  { color: #00b894; }
        .page-stat-value.arrived { color: #0984e3; }
        .page-stat-value.absent  { color: #d63031; }

        /* Loading Skeleton */
        .skeleton {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
            border-radius: 8px;
        }

        @keyframes shimmer {
            0%   { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        .skeleton-card {
            height: 140px;
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: var(--text-muted);
        }

        .empty-state .icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        /* Animate cards on load */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .class-card {
            animation: fadeInUp 0.4s ease forwards;
        }

        /* Responsive */
        @media (max-width: 600px) {
            .class-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 0.75rem;
            }
            .class-card {
                padding: 1rem;
            }
            .status-detail {
                flex-direction: column;
                gap: 3px;
            }
            .page-stats {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <a href="index.php" class="navbar-brand-container">
            <img src="image/tharit-face-scan.jpg" alt="Logo" class="logo-img">
            <span class="navbar-brand">Smart-Bus-Guard</span>
        </a>
    
        <ul class="nav-links">
            <li><a href="index.php">Dashboard</a></li>
            <li><a href="id-list.php">Students</a></li>
            <li><a href="all-status.php" class="active">All Class</a></li>
            <li><a href="class-detail.php">Class Details</a></li>
            <li><a href="user-info.php">Admin</a></li>
            <li><a href="login.php">Logout</a></li>
        </ul>
    </nav>



    <div class="container" style="padding-top: 2rem; padding-bottom: 3rem;">
        <h1 style="margin-bottom: 0.5rem;">ระบบตรวจสอบเด็กติดในรถรับส่งด้วยเทคโนโลยี AIoT</h1>
        <p style="color: var(--text-muted); margin-bottom: 2rem;">ดูสถานะรวมของนักเรียนทุกชั้น ป.1 – ป.6 / ห้อง 1-5</p>

        <!-- Summary Stats -->
        <div class="page-stats" id="page-stats">
            <div class="page-stat-card">
                <h4>นักเรียนทั้งหมด</h4>
                <div class="page-stat-value total" id="stat-all-total">-</div>
            </div>
            <div class="page-stat-card">
                <h4>🚌 On Bus</h4>
                <div class="page-stat-value on-bus" id="stat-all-onbus">-</div>
            </div>
            <div class="page-stat-card">
                <h4>✅ Arrived</h4>
                <div class="page-stat-value arrived" id="stat-all-arrived">-</div>
            </div>
            <div class="page-stat-card">
                <h4>❌ Absent</h4>
                <div class="page-stat-value absent" id="stat-all-absent">-</div>
            </div>
        </div>

        <!-- Class sections will be rendered here -->
        <div id="all-classes-container">
            <!-- Loading skeleton -->
            <div class="grade-section">
                <div class="grade-header"><div class="skeleton" style="width:200px; height:30px;"></div></div>
                <div class="class-grid">
                    <div class="skeleton skeleton-card"></div>
                    <div class="skeleton skeleton-card"></div>
                    <div class="skeleton skeleton-card"></div>
                    <div class="skeleton skeleton-card"></div>
                    <div class="skeleton skeleton-card"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
    // ===== Configuration =====
    const TOTAL_GRADES = 6;   // ป.1 - ป.6
    const TOTAL_CLASSES = 5;  // ห้อง 1-5

    const gradeNames = {
        1: 'ป.1 (Grade 1)',
        2: 'ป.2 (Grade 2)',
        3: 'ป.3 (Grade 3)',
        4: 'ป.4 (Grade 4)',
        5: 'ป.5 (Grade 5)',
        6: 'ป.6 (Grade 6)'
    };

    const gradeEmojis = {
        1: '🟣', 2: '🔵', 3: '🟢', 4: '🟠', 5: '🟡', 6: '🩷'
    };

    // ===== Fetch data from PHP API =====
    async function loadClassStatus() {
        const container = document.getElementById('all-classes-container');

        try {
            const res = await fetch('get_class_status.php');
            const data = await res.json();

            // Build a lookup: { grade: { class: {total, on_bus, arrived, absent} } }
            const lookup = {};
            let grandTotal = 0, grandOnBus = 0, grandArrived = 0, grandAbsent = 0;

            data.forEach(row => {
                const g = parseInt(row.grade);
                const c = parseInt(row.class);
                if (!lookup[g]) lookup[g] = {};
                lookup[g][c] = {
                    total:   parseInt(row.total)   || 0,
                    on_bus:  parseInt(row.on_bus)   || 0,
                    arrived: parseInt(row.arrived)  || 0,
                    absent:  parseInt(row.absent)   || 0
                };
                grandTotal   += lookup[g][c].total;
                grandOnBus   += lookup[g][c].on_bus;
                grandArrived += lookup[g][c].arrived;
                grandAbsent  += lookup[g][c].absent;
            });

            // Update summary stats
            document.getElementById('stat-all-total').textContent   = grandTotal;
            document.getElementById('stat-all-onbus').textContent   = grandOnBus;
            document.getElementById('stat-all-arrived').textContent = grandArrived;
            document.getElementById('stat-all-absent').textContent  = grandAbsent;

            // Render each grade section
            let html = '';
            for (let g = 1; g <= TOTAL_GRADES; g++) {
                let gradeTotal = 0;

                // Count total students in this grade
                for (let c = 1; c <= TOTAL_CLASSES; c++) {
                    if (lookup[g] && lookup[g][c]) {
                        gradeTotal += lookup[g][c].total;
                    }
                }

                html += `
                <div class="grade-section grade-${g}">
                    <div class="grade-header">
                        <div class="grade-icon">${g}</div>
                        <h2>${gradeNames[g]}</h2>
                        <span class="grade-count">${gradeTotal} คน</span>
                    </div>
                    <div class="class-grid">`;

                for (let c = 1; c <= TOTAL_CLASSES; c++) {
                    const info = (lookup[g] && lookup[g][c]) 
                        ? lookup[g][c] 
                        : { total: 0, on_bus: 0, arrived: 0, absent: 0 };

                    const pctOnBus  = info.total ? (info.on_bus  / info.total * 100) : 0;
                    const pctArrived = info.total ? (info.arrived / info.total * 100) : 0;
                    const pctAbsent = info.total ? (info.absent  / info.total * 100) : 0;

                    // Animation delay per card
                    const delay = (c - 1) * 0.08;

                    html += `
                    <a href="class-detail.html?grade=${g}&class=${c}" 
                       class="class-card" 
                       style="animation-delay: ${delay}s;">
                        <div class="class-card-header">
                            <span class="class-card-title">${gradeEmojis[g]} ห้อง ${c}</span>
                            <span class="class-card-total">${info.total} คน</span>
                        </div>
                        <div class="status-bar-wrap">
                            <div class="status-bar">
                                <div class="status-bar-fill bar-on-bus" style="width:${pctOnBus}%"></div>
                                <div class="status-bar-fill bar-arrived" style="width:${pctArrived}%"></div>
                                <div class="status-bar-fill bar-absent" style="width:${pctAbsent}%"></div>
                            </div>
                        </div>
                        <div class="status-detail">
                            <div class="status-item">
                                <span class="status-dot dot-on-bus"></span>
                                <span>On Bus <strong>${info.on_bus}</strong></span>
                            </div>
                            <div class="status-item">
                                <span class="status-dot dot-arrived"></span>
                                <span>Arrived <strong>${info.arrived}</strong></span>
                            </div>
                            <div class="status-item">
                                <span class="status-dot dot-absent"></span>
                                <span>Absent <strong>${info.absent}</strong></span>
                            </div>
                        </div>
                    </a>`;
                }

                html += `</div></div>`;
            }

            container.innerHTML = html;

        } catch (err) {
            console.error('Error loading class status:', err);
            container.innerHTML = `
                <div class="empty-state">
                    <div class="icon">⚠️</div>
                    <h3>ไม่สามารถโหลดข้อมูลได้</h3>
                    <p>กรุณาตรวจสอบการเชื่อมต่อ Database</p>
                    <p style="font-size: 0.8rem; margin-top:8px; color:#aaa;">${err.message}</p>
                </div>`;
        }
    }

    // Auto refresh every 30 seconds
    loadClassStatus();
    setInterval(loadClassStatus, 30000);
    </script>

</body>
</html>
