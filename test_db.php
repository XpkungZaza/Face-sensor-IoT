<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db_config.php';

if ($conn->connect_error) {
    echo "❌ เชื่อมต่อไม่ได้เพื่อน!: " . $conn->connect_error;
} else {
    echo "✅ เชื่อมต่อ Cloud Database สำเร็จแล้วไปร์ท! ลุยต่อได้!";
    
    // ลองดึงจำนวนนักเรียนมาโชว์หน่อย
    $res = $conn->query("SELECT COUNT(*) as total FROM students");
    if ($res) {
        $row = $res->fetch_assoc();
        echo "<br>ตอนนี้มีรายชื่อเด็กในระบบทั้งหมด: " . $row['total'] . " คน";
    } else {
        echo "<br>⚠️ เชื่อมต่อได้นะ แต่หาตาราง 'students' ไม่เจอ (ลืมรัน SQL หรือเปล่า?)";
    }
}
?>