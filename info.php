<?php
session_start();
include 'db_config.php';

// --- Redirect if not logged in ---
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

$teacher_name = $_SESSION['teacher_name'] ?? 'Unknown';
$username = $_SESSION['username'] ?? 'Unknown';
$user_id = $_SESSION['id'];
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account | Smart-Bus-Guard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, #e3f2fd 0%, #f0f4f8 50%, #e8eaf6 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .info-card {
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 123, 255, 0.12), 0 4px 12px rgba(0, 0, 0, 0.06);
            width: 100%;
            max-width: 480px;
            overflow: hidden;
            animation: slideUp 0.6s ease;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Header Banner */
        .info-header {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            padding: 2.5rem 2rem 2rem;
            text-align: center;
            position: relative;
        }

        .info-header::after {
            content: '';
            position: absolute;
            bottom: -20px;
            left: 0;
            right: 0;
            height: 40px;
            background: #ffffff;
            border-radius: 24px 24px 0 0;
        }

        .avatar-circle {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            border: 3px solid rgba(255, 255, 255, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 2rem;
            color: #ffffff;
            backdrop-filter: blur(10px);
        }

        .info-header h1 {
            color: #ffffff;
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .info-header p {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.88rem;
            font-weight: 400;
        }

        /* Body Content */
        .info-body {
            padding: 1.5rem 2rem 2rem;
        }

        .welcome-msg {
            text-align: center;
            margin-bottom: 2rem;
            padding: 1rem;
            background: #e3f2fd;
            border-radius: 12px;
            color: #007bff;
            font-weight: 500;
            font-size: 0.95rem;
        }

        .welcome-msg span {
            display: block;
            font-size: 0.82rem;
            color: #64748b;
            margin-top: 4px;
            font-weight: 400;
        }

        /* Detail Rows */
        .detail-row {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            border-radius: 12px;
            background: #f8fafc;
            margin-bottom: 0.75rem;
            transition: all 0.2s ease;
        }

        .detail-row:hover {
            background: #e3f2fd;
            transform: translateX(4px);
        }

        .detail-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            background: #e3f2fd;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        .detail-content label {
            display: block;
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #94a3b8;
            font-weight: 600;
            margin-bottom: 2px;
        }

        .detail-content span {
            font-size: 0.95rem;
            font-weight: 600;
            color: #1e293b;
        }

        /* Action Buttons */
        .actions {
            display: flex;
            gap: 0.75rem;
            margin-top: 1.75rem;
        }

        .btn {
            flex: 1;
            padding: 0.8rem 1rem;
            border-radius: 12px;
            font-size: 0.88rem;
            font-weight: 600;
            text-align: center;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            border: none;
            font-family: inherit;
        }

        .btn-primary {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: #ffffff;
            box-shadow: 0 4px 14px rgba(0, 123, 255, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 123, 255, 0.4);
        }

        .btn-danger {
            background: #ffffff;
            color: #ef4444;
            border: 2px solid #fecaca;
        }

        .btn-danger:hover {
            background: #fef2f2;
            border-color: #ef4444;
            transform: translateY(-2px);
        }

        /* Footer */
        .info-footer {
            text-align: center;
            padding: 1rem 2rem 1.5rem;
            font-size: 0.75rem;
            color: #94a3b8;
        }

        /* Mobile */
        @media (max-width: 500px) {
            body {
                padding: 1rem;
            }
            .info-body {
                padding: 1.25rem 1.5rem 1.5rem;
            }
            .actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>

    <div class="info-card">
        <!-- Header -->
        <div class="info-header">
            <div class="avatar-circle">
                <?= mb_strtoupper(mb_substr($teacher_name, 0, 1, 'UTF-8'), 'UTF-8') ?>
            </div>
            <h1><?= htmlspecialchars($teacher_name) ?></h1>
            <p>Smart-Bus-Guard Teacher Account</p>
        </div>

        <!-- Body -->
        <div class="info-body">
            <div class="welcome-msg">
                🎉 ยินดีต้อนรับ, <?= htmlspecialchars($teacher_name) ?>!
                <span>คุณเข้าสู่ระบบ Smart-Bus-Guard เรียบร้อยแล้ว</span>
            </div>

            <div class="detail-row">
                <div class="detail-icon">👤</div>
                <div class="detail-content">
                    <label>Teacher Name</label>
                    <span><?= htmlspecialchars($teacher_name) ?></span>
                </div>
            </div>

            <div class="detail-row">
                <div class="detail-icon">📧</div>
                <div class="detail-content">
                    <label>Email / Username</label>
                    <span><?= htmlspecialchars($username) ?></span>
                </div>
            </div>

            <div class="detail-row">
                <div class="detail-icon">🔑</div>
                <div class="detail-content">
                    <label>User ID</label>
                    <span>#<?= $user_id ?></span>
                </div>
            </div>

            <div class="actions">
                <a href="index.php" class="btn btn-primary">📊 Go to Dashboard</a>
                <a href="logout.php" class="btn btn-danger">🚪 Logout</a>
            </div>
        </div>

        <div class="info-footer">
            ผู้วิจัย นายธฤต ไชยมงคล, นางนฐมนพรรณ สุวรรณชาตรี, นาย ธนวินท์ จันท์ขอด | แผนก เทคโนโลยีสารสนเทศ วิทยาลัยเทคนิคเชียงใหม่<br>
            ติดต่อ: tharit.chai@cmtc.ac.th<br>
            Smart-Bus-Guard &copy; 2026 &mdash; IoT Student Tracking System
        </div>
    </div>

</body>
</html>
