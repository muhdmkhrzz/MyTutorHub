<?php
session_start();
include '../includes/connect.php';

// Redirect if not logged in or wrong role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

$stud_id = $_SESSION['user_id'];

// Fetch student details
$sql_stud = "SELECT * FROM stud WHERE stud_id = '$stud_id'";
$result_stud = mysqli_query($conn, $sql_stud);
$student = ($result_stud && mysqli_num_rows($result_stud) > 0) 
    ? mysqli_fetch_assoc($result_stud) 
    : ['error' => "Student data not found."];

// Fetch classes
$sql_classes = "SELECT c.class_title, c.class_date, c.class_starttime, c.class_endtime, 
                c.class_capacity, t.tutor_name
                FROM class c
                JOIN tutor t ON c.tutor_id = t.tutor_id
                ORDER BY c.class_date ASC
                LIMIT 3";
$result_classes = mysqli_query($conn, $sql_classes);
$classes = [];
if ($result_classes && mysqli_num_rows($result_classes) > 0) {
    while ($row = mysqli_fetch_assoc($result_classes)) {
        $classes[] = $row;
    }
}

// Fetch lessons
$sql_lessons = "SELECT c.class_title, t.tutor_name, b.booking_date, 
                c.class_starttime, c.class_endtime
                FROM booking b
                JOIN class c ON b.class_id = c.class_id
                JOIN tutor t ON c.tutor_id = t.tutor_id
                WHERE b.stud_id = '$stud_id'
                ORDER BY b.booking_date DESC
                LIMIT 3";
$result_lessons = mysqli_query($conn, $sql_lessons);
$lessons = [];
if ($result_lessons && mysqli_num_rows($result_lessons) > 0) {
    while ($row = mysqli_fetch_assoc($result_lessons)) {
        $lessons[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="student.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="sidebar">
        <img src="mytutorhub_logo.png" class="logo" alt="Logo">
        <a href="dashboard.php">Dashboard</a>
        <a href="#">My Bookings</a>
        <a href="#">Recorded Lessons</a>
        <a href="../logout.php">Logout</a>
    </div>

    <div class="main-content">
        <div class="search-bar">
            <input type="text" placeholder="Search...">
            <span><?= date("d F Y, l") ?></span>
        </div>

        <div class="welcome-panel">
            <div class="text">
                <h2>Welcome back, <?= htmlspecialchars($student['stud_name'] ?? 'Student') ?>!</h2>
                <p>New classes are available. Learn more about HTML and CSS.</p>
                <button>Buy Lesson</button>
            </div>
            <img src="../img/profile.jpg" alt="Profile">
        </div>

        <?php if (!empty($classes)): ?>
            <h2>Classes</h2>
            <div class="classes">
                <?php foreach ($classes as $class): ?>
                <div class="class-card">
                    <h3><?= htmlspecialchars($class['class_title']) ?></h3>
                    <p class="info"><?= date('d M Y', strtotime($class['class_date'])) ?></p>
                    <p class="info"><?= $class['class_starttime'] ?> - <?= $class['class_endtime'] ?></p>
                    <p class="info">Tutor: <?= htmlspecialchars($class['tutor_name']) ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-data-message">
                <p>You have no classes available.</p>
            </div>
        <?php endif; ?>

        <?php if (!empty($lessons)): ?>
            <h2>Lessons</h2>
            <table class="lessons-table">
                <thead>
                    <tr>
                        <th>Class</th>
                        <th>Tutor</th>
                        <th>Starting</th>
                        <th>Material</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lessons as $lesson): ?>
                    <tr>
                        <td><?= htmlspecialchars($lesson['class_title']) ?></td>
                        <td><?= htmlspecialchars($lesson['tutor_name']) ?></td>
                        <td>
                            <?= date('d M Y', strtotime($lesson['booking_date'])) ?>
                            <?= $lesson['class_starttime'] ?> - <?= $lesson['class_endtime'] ?>
                        </td>
                        <td><a href="#">Download</a></td>
                        <td class="status-done">Done</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-data-message">
                <p>You have no lessons booked yet.</p>
            </div>
        <?php endif; ?>

        <div class="right-panel">
            <h3>Reminders</h3>
            <?php if (!empty($classes)): ?>
                <?php foreach ($classes as $reminder): ?>
                <div class="reminder">
                    <i class="fa fa-bell"></i>
                    <?= htmlspecialchars($reminder['class_title']) ?> -
                    <?= date('d M Y', strtotime($reminder['class_date'])) ?>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-data-message">
                    <p>No reminders available.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>