<?php
// db.php - Database connection file

$serverName = "localhost";
$dbUsername = "root";
$dbPassword = "";
$dbName = "task";

$conn = new mysqli($serverName, $dbUsername, $dbPassword, $dbName);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
