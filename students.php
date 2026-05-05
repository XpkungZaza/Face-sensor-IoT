<?php
include 'db_config.php';
$current_page = 'students';
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="All Students Status — Real-time view of every student's bus status.">
    <title>All Status | Smart-Bus-Guard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <?php include 'navbar.php'; ?>

    <div class="container">
        <!-- Page Header -->
        <div class="page-header">
            <h1>ระบบตรวจสอบเด็กติดในรถรับส่งด้วยเทคโนโลยี AIoT</h1>
            <p>สถานะปัจจุบันของนักเรียนทุกคนในระบบ — อัปเดตอัตโนมัติทุก 30 วินาที</p>
        </div>

        <!-- Summary Stats -->
        <div class="stats-row" id="stats-row">
            <div class="stat-card total animate-in">
                <div class="stat-icon">👥</div>
                <div class="stat-info">
                    <h4>Total</h4>
                    <div class="stat-number" id="s-total">-</div>
                </div>
            </div>
            <div class="stat-card on-bus animate-in" style="animation-delay:0.1s">
                <div class="stat-icon">🚌</div>
                <div class="stat-info">
                    <h4>On Bus</h4>
                    <div class="stat-number" id="s-onbus">-</div>
                </div>
            </div>
            <div class="stat-card off-bus animate-in" style="animation-delay:0.2s">
                <div class="stat-icon">🏠</div>
                <div class="stat-info">
                    <h4>Off Bus</h4>
                    <div class="stat-number" id="s-offbus">-</div>
                </div>
            </div>
            <div class="stat-card absent animate-in" style="animation-delay:0.3s">
                <div class="stat-icon">❌</div>
                <div class="stat-info">
                    <h4>Absent</h4>
                    <div class="stat-number" id="s-absent">-</div>
                </div>
            </div>
        </div>

        <!-- Search -->
        <div class="search-bar animate-in" style="animation-delay:0.3s">
            <span class="search-icon">🔍</span>
            <input type="text" id="searchInput" placeholder="ค้นหาชื่อนักเรียน หรือ รหัส..." oninput="filterTable()">
        </div>

        <!-- Table -->
        <div class="table-wrapper animate-in" style="animation-delay:0.4s">
            <table id="studentTable">
                <thead>
                    <tr>
                        <th style="width:50px">#</th>
                        <th>Student</th>
                        <th>Student ID</th>
                        <th>Grade / Class</th>
                        <th>Status</th>
                        <th>Last Seen</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <tr><td colspan="6" style="text-align:center; padding:3rem; color:var(--text-muted);">กำลังโหลดข้อมูล...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script>
    let allStudents = [];

    async function loadStudents() {
        try {
            const res = await fetch('get_students.php');
            const data = await res.json();
            allStudents = data;

            // Count stats
            const total = data.length;
            const onBus = data.filter(s => s.status === 'On Bus').length;
            const offBus = data.filter(s => s.status === 'Off Bus').length;
            const absent = data.filter(s => s.status === 'Absent').length;

            document.getElementById('s-total').textContent = total;
            document.getElementById('s-onbus').textContent = onBus;
            document.getElementById('s-offbus').textContent = offBus;
            document.getElementById('s-absent').textContent = absent;

            renderTable(data);
        } catch (err) {
            console.error('Error loading students:', err);
            document.getElementById('tableBody').innerHTML = `
                <tr><td colspan="6" style="text-align:center; padding:2rem; color:var(--danger);">
                    ⚠️ ไม่สามารถโหลดข้อมูลได้: ${err.message}
                </td></tr>`;
        }
    }

    function renderTable(students) {
        const tbody = document.getElementById('tableBody');

        if (students.length === 0) {
            tbody.innerHTML = `<tr><td colspan="6" class="empty-state"><div class="icon">📭</div>ไม่พบข้อมูลนักเรียน</td></tr>`;
            return;
        }

        let html = '';
        students.forEach((s, i) => {
            let rowClass, badgeClass, badgeText;
            if (s.status === 'On Bus') {
                rowClass = 'row-on-bus'; badgeClass = 'on-bus'; badgeText = 'On Bus';
            } else if (s.status === 'Off Bus') {
                rowClass = 'row-off-bus'; badgeClass = 'off-bus'; badgeText = 'Off Bus';
            } else {
                rowClass = 'row-absent'; badgeClass = 'absent'; badgeText = 'Absent';
            }
            const initial = (s.name || '?').charAt(0).toUpperCase();
            const pic = s.pic ? `<img src="${s.pic}" alt="${s.name}">` : `<div class="avatar-placeholder">${initial}</div>`;

            html += `
            <tr class="${rowClass}" style="animation: fadeSlideUp 0.3s ease ${i * 0.03}s forwards; opacity:0;">
                <td style="color:var(--text-muted); font-weight:500;">${i + 1}</td>
                <td>
                    <div class="student-cell">
                        ${pic}
                        <span>${s.name || 'Unknown'}</span>
                    </div>
                </td>
                <td style="color:var(--text-secondary); font-size:0.88rem;">${s.student_id || '-'}</td>
                <td style="font-size:0.88rem;">${s.grade || '-'} / ${s.class || '-'}</td>
                <td><span class="badge ${badgeClass}">${badgeText}</span></td>
                <td style="font-size:0.82rem; color:var(--text-muted);">${s.lastseen || '-'}</td>
            </tr>`;
        });

        tbody.innerHTML = html;
    }

    function filterTable() {
        const query = document.getElementById('searchInput').value.toLowerCase().trim();
        if (!query) {
            renderTable(allStudents);
            return;
        }
        const filtered = allStudents.filter(s => 
            (s.name && s.name.toLowerCase().includes(query)) ||
            (s.student_id && s.student_id.toLowerCase().includes(query))
        );
        renderTable(filtered);
    }

    // Initial load & auto-refresh
    loadStudents();
    setInterval(loadStudents, 30000);
    </script>
</body>
</html>
