<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
include 'db_config.php';

$grade = isset($_GET['grade']) ? $_GET['grade'] : '';
$class = isset($_GET['class']) ? $_GET['class'] : '';

if (empty($grade) || empty($class)) {
    echo json_encode(["error" => "Missing grade or class parameter"]);
    exit;
}

$stmt = $conn->prepare("SELECT * FROM students WHERE grade = ? AND class = ? ORDER BY name ASC");
$stmt->bind_param("ss", $grade, $class);
$stmt->execute();
$result = $stmt->get_result();

$students = array();
while ($row = $result->fetch_assoc()) {
    $students[] = $row;
}
$stmt->close();

echo json_encode($students);
?>
