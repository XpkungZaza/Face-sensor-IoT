<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
include 'db_config.php';

$sql = "SELECT * FROM students ORDER BY lastseen DESC";
$result = $conn->query($sql);
$students = array();

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
}

echo json_encode($students);
?>