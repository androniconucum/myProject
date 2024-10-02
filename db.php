<?php
$hostname = "localhost"; // Update with your server
$username = "root"; // Update with your database username
$password = "december12"; // Update with your database password
$dbname = "myproject"; // Replace with your database name

// Create connection
$conn = new mysqli($hostname, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
