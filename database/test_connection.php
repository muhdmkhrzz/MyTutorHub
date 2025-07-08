<?php
$host = "sql306.infinityfree.com";
$username = "if0_39319055";
$password = "NURiman3108";
$database = "if0_39319055_mytutorhub";

$conn = mysqli_connect($host, $username, $password, $database);

if (!$conn) {
    die("❌ Connection failed: " . mysqli_connect_error());
}
echo "✅ Connected successfully to the database.";
?>
