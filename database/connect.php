<?php
$host = "sql306.infinityfree.com"; 
$username = "if0_39319055"; 
$password = "NURiman3108"; 
$database = "if0_39319055_mytutorhub"; 

// Connect to MySQL
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
