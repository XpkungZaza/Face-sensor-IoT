<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
include 'db_config.php';

// Summary of students per grade+class with On Bus / Off Bus counts
$sql = "SELECT grade, class, 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'On Bus' THEN 1 ELSE 0 END) as on_bus,
        SUM(CASE WHEN status != 'On Bus' OR status IS NULL OR status = '' THEN 1 ELSE 0 END) as off_bus
        FROM students 
        GROUP BY grade, class 
        ORDER BY grade ASC, class ASC";

$result = $conn->query($sql);
$classes = array();

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $classes[] = $row;
    }
}

echo json_encode($classes);
?>
