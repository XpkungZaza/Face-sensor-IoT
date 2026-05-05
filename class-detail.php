<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Detail | Smart-Bus-Guard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
            color: var(--text-muted);
            font-weight: 500;
            margin-bottom: 1.5rem;
            transition: color 0.2s;
        }
        .back-link:hover { color: var(--primary); }

        .class-title {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 0.5rem;
        }
        .class-title h1 { margin: 0; }
        .class-title .badge-grade {
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            color: #fff;
        }

        /* Mini Stats */
        .mini-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .mini-stat {
            background: var(--card-bg);
            border-radius: 12px;
            padding: 1rem;
            border: 1px solid var(--border);
            text-align: center;
        }
        .mini-stat h4 {
            font-size: 0.75rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }
        .mini-stat .val {
            font-size: 1.75rem;
            font-weight: 700;
        }
        .val.total   { color: var(--primary); }
        .val.on-bus  { color: #00b894; }
        .val.arrived { color: #0984e3; }
        .val.absent  { color: #d63031; }

        /* Student List */
        .student-list-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 1.25rem;
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 12px;
            margin-bottom: 0.75rem;
            transition: all 0.2s;
        }
        .student-list-item:hover {
            transform: translateX(4px);
            box-shadow: var(--shadow-md);
        }

        .student-info {
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .student-avatar {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: var(--bg);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--primary);
            flex-shrink: 0;
        }
        .student-name-id h3 {
            font-size: 1rem;
            font-weight: 600;
            margin: 0 0 2px 0;
        }
        .student-name-id p {
            font-size: 0.8rem;
            color: var(--text-muted);
            margin: 0;
        }

        .status-pill {
            padding: 5px 14px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            white-space: nowrap;
        }
        .pill-on-bus  { background: #e3fcef; color: #00b894; }
        .pill-arrived { background: #dbeafe; color: #0984e3; }
        .pill-absent  { background: #fee2e2; color: #d63031; }
        .pill-unknown { background: #dfe6e9; color: #636e72; }

        .last-seen {
            font-size: 0.75rem;
            color: var(--text-muted);
            text-align: right;
            min-width: 80px;
        }

        .student-right {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        /* Mobile */
        @media (max-width: 600px) {
            .student-list-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            .student-right {
                width: 100%;
                justify-content: space-between;
            }
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(12px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .student-list-item {
            animation: fadeIn 0.35s ease forwards;
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
        <a href="all-status.html" class="back-link">&larr; กลับหน้ารวมทุกห้อง</a>

        <div class="class-title">
            <h1 id="class-title-text">กำลังโหลด...</h1>
            <span class="badge-grade" id="badge-grade" style="background: var(--primary);">-</span>
        </div>
        <p style="color: var(--text-muted); margin-bottom: 2rem;" id="class-subtitle">-</p>

        <!-- Mini Stats -->
        <div class="mini-stats">
            <div class="mini-stat">
                <h4>ทั้งหมด</h4>
                <div class="val total" id="ms-total">-</div>
            </div>
            <div class="mini-stat">
                <h4>🚌 On Bus</h4>
                <div class="val on-bus" id="ms-onbus">-</div>
            </div>
            <div class="mini-stat">
                <h4>✅ Arrived</h4>
                <div class="val arrived" id="ms-arrived">-</div>
            </div>
            <div class="mini-stat">
                <h4>❌ Absent</h4>
                <div class="val absent" id="ms-absent">-</div>
            </div>
        </div>

        <!-- Student List -->
        <div class="card" style="padding: 0; overflow: hidden;">
            <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border);">
                <h2 style="margin:0; font-size: 1.1rem;">📋 รายชื่อนักเรียน</h2>
            </div>
            <div id="student-list" style="padding: 1rem;">
                <p style="text-align:center; color:var(--text-muted); padding:2rem;">กำลังโหลดข้อมูล...</p>
            </div>
        </div>
    </div>

    <script>
    // Get grade & class from URL
    const params = new URLSearchParams(window.location.search);
    const grade  = parseInt(params.get('grade')) || 1;
    const cls    = parseInt(params.get('class')) || 1;

    const gradeColors = {
        1: '#6c5ce7', 2: '#0984e3', 3: '#00b894',
        4: '#e17055', 5: '#fdcb6e', 6: '#e84393'
    };

    // Update page title
    document.getElementById('class-title-text').textContent = `ป.${grade} / ห้อง ${cls}`;
    document.getElementById('badge-grade').textContent = `Grade ${grade}`;
    document.getElementById('badge-grade').style.background = gradeColors[grade] || 'var(--primary)';
    document.getElementById('class-subtitle').textContent = `รายชื่อนักเรียนชั้นประถมศึกษาปีที่ ${grade} ห้อง ${cls}`;
    document.title = `ป.${grade}/${cls} | Smart-Bus-Guard`;

    async function loadStudents() {
        const listEl = document.getElementById('student-list');

        try {
            const res = await fetch(`get_students_by_class.php?grade=${grade}&class=${cls}`);
            const students = await res.json();

            // Count stats
            let total = students.length;
            let onBus = 0, arrived = 0, absent = 0;
            students.forEach(s => {
                if (s.status === 'On Bus')       onBus++;
                else if (s.status === 'Arrived')  arrived++;
                else                              absent++;
            });

            document.getElementById('ms-total').textContent   = total;
            document.getElementById('ms-onbus').textContent   = onBus;
            document.getElementById('ms-arrived').textContent = arrived;
            document.getElementById('ms-absent').textContent  = absent;

            if (total === 0) {
                listEl.innerHTML = `
                    <div style="text-align:center; padding:3rem; color:var(--text-muted);">
                        <div style="font-size:2.5rem; margin-bottom:1rem;">📭</div>
                        <p>ยังไม่มีนักเรียนในห้องนี้</p>
                    </div>`;
                return;
            }

            let html = '';
            students.forEach((s, i) => {
                const initials = (s.name || '?').charAt(0).toUpperCase();

                let pillClass = 'pill-unknown';
                if (s.status === 'On Bus')      pillClass = 'pill-on-bus';
                else if (s.status === 'Arrived') pillClass = 'pill-arrived';
                else if (s.status === 'Absent')  pillClass = 'pill-absent';

                const lastSeen = s.lastseen || '-';

                html += `
                <div class="student-list-item" style="animation-delay: ${i * 0.05}s;">
                    <div class="student-info">
                        <div class="student-avatar" style="background: ${gradeColors[grade]}20; color: ${gradeColors[grade]};">
                            ${initials}
                        </div>
                        <div class="student-name-id">
                            <h3>${s.name || 'Unknown'}</h3>
                            <p>ID: ${s.student_id || '-'}</p>
                        </div>
                    </div>
                    <div class="student-right">
                        <span class="status-pill ${pillClass}">${s.status || 'N/A'}</span>
                        <div class="last-seen">${lastSeen}</div>
                    </div>
                </div>`;
            });

            listEl.innerHTML = html;

        } catch (err) {
            console.error('Error:', err);
            listEl.innerHTML = `
                <div style="text-align:center; padding:2rem; color:#d63031;">
                    <p>⚠️ ไม่สามารถโหลดข้อมูลได้: ${err.message}</p>
                </div>`;
        }
    }

    loadStudents();
    setInterval(loadStudents, 30000);
    </script>

</body>
</html>
