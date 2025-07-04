<?php
session_start();
include '../includes/connect.php';

// Authentication check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

$stud_id = $_SESSION['user_id'];

// Fetch student data
$student = [];
$sql_stud = "SELECT * FROM stud WHERE stud_id = ?";
$stmt = $conn->prepare($sql_stud);
$stmt->bind_param("i", $stud_id);
$stmt->execute();
$result_stud = $stmt->get_result();

if ($result_stud->num_rows > 0) {
    $student = $result_stud->fetch_assoc();
} else {
    $student['error'] = "Student data not found.";
}

// Fetch classes
$classes = [];
$sql_classes = "SELECT c.class_title, c.class_date, c.class_starttime, c.class_endtime, 
               c.class_capacity, t.tutor_name
               FROM class c
               JOIN tutor t ON c.tutor_id = t.tutor_id
               ORDER BY c.class_date ASC";
$result_classes = $conn->query($sql_classes);
if ($result_classes && $result_classes->num_rows > 0) {
    while ($row = $result_classes->fetch_assoc()) {
        $classes[] = $row;
    }
}

// Fetch lessons
$lessons = [];
$sql_lessons = "SELECT c.class_title, t.tutor_name, b.booking_date, 
               c.class_starttime, c.class_endtime
               FROM booking b
               JOIN class c ON b.class_id = c.class_id
               JOIN tutor t ON c.tutor_id = t.tutor_id
               WHERE b.stud_id = ?
               ORDER BY b.booking_date DESC";
$stmt = $conn->prepare($sql_lessons);
$stmt->bind_param("i", $stud_id);
$stmt->execute();
$result_lessons = $stmt->get_result();

if ($result_lessons && $result_lessons->num_rows > 0) {
    while ($row = $result_lessons->fetch_assoc()) {
        $lessons[] = $row;
    }
}

// Include view template
include 'student_view.php';
?>