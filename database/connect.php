<?php
$host = "sql306.infinityfree.com"; // Replace with your actual DB host
$username = "if0_39319055"; // Replace with your DB username
$password = "NURiman3108"; // Replace with your DB password
$database = "if0_39319055_mytutorhub"; // Replace with your DB name

// Connect to MySQL
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    // It's better to log this error or handle it gracefully in a real application
    // For debugging, die() is okay, but for JSON responses, you'd want to return an error JSON
    die("Connection failed: " . $conn->connect_error);
}
?>
