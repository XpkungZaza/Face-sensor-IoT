<?php
$host = "localhost";
$user = "thariti_smartbusguard"; 
$pass = "smartbusguard090";
$db   = "thariti_smartbusguard";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>