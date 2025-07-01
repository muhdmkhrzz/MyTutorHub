<?php
session_start();
include '../includes/connect.php';

// Redirect if not logged in or wrong role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'tutor') {
    header("Location: ../login.php");
    exit();
}

$tutor_id = $_SESSION['user_id'];

$tutor = [];
$classes = [];
$materials = [];

// Fetch tutor info
$sql_tutor = "SELECT * FROM tutor WHERE tutor_id = '$tutor_id'";
$result_tutor = mysqli_query($conn, $sql_tutor);
if ($result_tutor && mysqli_num_rows($result_tutor) > 0) {
    $tutor = mysqli_fetch_assoc($result_tutor);
}

// Fetch tutor classes
$sql_classes = "
    SELECT c.class_title, c.class_date, c.class_starttime, c.class_endtime, 
           c.class_capacity
    FROM class c
    WHERE c.tutor_id = '$tutor_id'
    ORDER BY c.class_date ASC
    LIMIT 3
";
$result_classes = mysqli_query($conn, $sql_classes);
while ($row = mysqli_fetch_assoc($result_classes)) {
    $classes[] = $row;
}

// Fetch materials (dummy placeholders)
$materials = [
    [
        "date" => "2025-06-21",
        "title" => "Operating System",
        "time" => "08:00 - 10:00"
    ],
    [
        "date" => "2025-07-04",
        "title" => "Database",
        "time" => "14:00 - 16:00"
    ]
];

include 'dashboard.html';
?>

