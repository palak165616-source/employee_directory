<?php
$servername = "your-server-name";
$username   = "your-username";
$password   = "your-password";
$dbname     = "database-name";

$conn = mysqli_connect($servername, $username, $password, $dbname);
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>
