<?php
header("Access-Control-Allow-Origin: *");
include 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $s_id = $_POST['student_id'];
    $status = $_POST['status'];

    // อัปเดตสถานะและเวลาล่าสุดอัตโนมัติ
    $sql = "UPDATE students SET status = '$status' WHERE student_id = '$s_id'";
    
    if ($conn->query($sql) === TRUE) {
        echo "Update Success";
    } else {
        echo "Error: " . $conn->error;
    }
}
?>