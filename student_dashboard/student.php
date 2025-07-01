<?php
session_start();
include '../includes/connect.php';

// Redirect if not logged in or wrong role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

$stud_id = $_SESSION['user_id'];

$student = [];
$classes = [];
$lessons = [];

// Fetch student details
$sql_stud = "SELECT * FROM stud WHERE stud_id = '$stud_id'";
$result_stud = mysqli_query($conn, $sql_stud);
if ($result_stud && mysqli_num_rows($result_stud) > 0) {
    $student = mysqli_fetch_assoc($result_stud);
} else {
    $student['error'] = "Student data not found.";
}

// Fetch classes
$sql_classes = "
    SELECT c.class_title, c.class_date, c.class_starttime, c.class_endtime, 
           c.class_capacity, t.tutor_name
    FROM class c
    JOIN tutor t ON c.tutor_id = t.tutor_id
    ORDER BY c.class_date ASC
    LIMIT 3
";
$result_classes = mysqli_query($conn, $sql_classes);
if ($result_classes && mysqli_num_rows($result_classes) > 0) {
    while ($row = mysqli_fetch_assoc($result_classes)) {
        $classes[] = $row;
    }
} else {
    $classes['empty'] = true;
}

// Fetch lessons booked by the student
$sql_lessons = "
    SELECT c.class_title, t.tutor_name, b.booking_date, 
           c.class_starttime, c.class_endtime
    FROM booking b
    JOIN class c ON b.class_id = c.class_id
    JOIN tutor t ON c.tutor_id = t.tutor_id
    WHERE b.stud_id = '$stud_id'
    ORDER BY b.booking_date DESC
    LIMIT 3
";
$result_lessons = mysqli_query($conn, $sql_lessons);
if ($result_lessons && mysqli_num_rows($result_lessons) > 0) {
    while ($row = mysqli_fetch_assoc($result_lessons)) {
        $lessons[] = $row;
    }
} else {
    $lessons['empty'] = true;
}

// Pass to HTML
include 'dashboard.html';
?>
