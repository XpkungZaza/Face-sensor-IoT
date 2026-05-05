<?php
// Shared Navbar — Smart-Bus-Guard
// Set $current_page before including this file
// Values: 'home', 'students', 'classes'
if (!isset($current_page)) $current_page = '';
?>

<nav class="navbar">
    <a href="index.php" class="navbar-brand-container">
        <img src="image/tharit-face-scan.jpg" alt="Smart-Bus-Guard Logo" class="logo-img">
        <span class="navbar-brand">Smart-Bus-Guard</span>
    </a>

    <div class="hamburger" id="hamburger" onclick="toggleNav()">
        <span></span>
        <span></span>
        <span></span>
    </div>

    <ul class="nav-links" id="navLinks">
        <li>
            <a href="index.php" class="<?= $current_page === 'home' ? 'active' : '' ?>">
                <span class="nav-icon">🏠</span> Home
            </a>
        </li>
        <li>
            <a href="students.php" class="<?= $current_page === 'students' ? 'active' : '' ?>">
                <span class="nav-icon">📋</span> All Status
            </a>
        </li>
        <li>
            <a href="classes.php" class="<?= $current_page === 'classes' ? 'active' : '' ?>">
                <span class="nav-icon">🏫</span> All Classes
            </a>
        </li>
        <li>
            <a href="info.php" class="<?= $current_page === 'info' ? 'active' : '' ?>">
                <span class="nav-icon">👤</span> Info
            </a>
        </li>
        <li>
            <a href="logout.php">
                <span class="nav-icon">🚪</span> Logout
            </a>
        </li>
    </ul>
</nav>

<div class="nav-overlay" id="navOverlay" onclick="toggleNav()"></div>

<script>
function toggleNav() {
    document.getElementById('hamburger').classList.toggle('active');
    document.getElementById('navLinks').classList.toggle('active');
    document.getElementById('navOverlay').classList.toggle('active');
    document.body.style.overflow = document.getElementById('navLinks').classList.contains('active') ? 'hidden' : '';
}
</script>
