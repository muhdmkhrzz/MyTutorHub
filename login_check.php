<?php
include 'connect.php';
session_start();

$id = $_POST['id'];
$password = $_POST['password'];

// Run login query...
