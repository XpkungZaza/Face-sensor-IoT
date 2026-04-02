<?php
header("Content-Type: application/json");
include 'db_config.php';

$sql = "SELECT * FROM students";
$result = $conn->query($sql);
$students = array();

while($row = $result->fetch_assoc()) {
    $students[] = $row;
}

echo json_encode($students); 
?>