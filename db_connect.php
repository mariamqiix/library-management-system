<?php
// db_connect.php

// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "new_password"; // Replace with your password
$dbname = "library_management_system"; // Replace with your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>