<?php
include 'connect.php'; // DB connection

session_start(); // optional: if using sessions

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $role = $_POST['role']; // "student" or "tutor"
    $id = $_POST['id'];
    $password = $_POST['password'];

    if ($role === 'student') {
        $sql = "SELECT * FROM stud WHERE stud_id = '$id' AND stud_password = '$password'";
    } elseif ($role === 'tutor') {
        $sql = "SELECT * FROM tutor WHERE tutor_id = '$id' AND tutor_password = '$password'";
    } else {
        die("Invalid role.");
    }

    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);

        // Store session data (optional)
        $_SESSION['user_id'] = $id;
        $_SESSION['role'] = $role;

        echo "Login successful as $role. Welcome, " . ($role === 'student' ? $user['stud_name'] : $user['tutor_name']) . "!";

        // Redirect to dashboard:
        // header("Location: dashboard.php");
        // exit;
    } else {
        echo "Invalid ID or password.";
    }
} else {
    echo "Invalid request.";
}
?>
