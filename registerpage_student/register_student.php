<?php

require_once '../database/connect.php';


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $stud_id = $conn->real_escape_string($_POST['stud_id']); // 
    $stud_name = $conn->real_escape_string($_POST['stud_name']); 
    $stud_email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    
    if (empty($stud_id) || empty($stud_name) || empty($stud_email) || empty($password) || empty($confirm_password)) {
        echo "<script>alert('Please fill in all required fields.'); window.location.href='registerstudent.html';</script>";
        exit();
    }

    if ($password !== $confirm_password) {
        echo "<script>alert('Passwords do not match. Please try again.'); window.location.href='registerstudent.html';</script>";
        exit();
    }

    // Hash the password for security
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Check if the stud_id already exists
    $check_id_sql = "SELECT stud_id FROM Student WHERE stud_id = ?";
    $stmt_check_id = $conn->prepare($check_id_sql);
    $stmt_check_id->bind_param("i", $stud_id); // 'i' for integer, assuming stud_id is INT
    $stmt_check_id->execute();
    $stmt_check_id->store_result();

    if ($stmt_check_id->num_rows > 0) {
        echo "<script>alert('Student ID already registered. Please choose a different ID or log in.'); window.location.href='registerstudent.html';</script>";
        $stmt_check_id->close();
        $conn->close();
        exit();
    }
    $stmt_check_id->close();

    // Check if the email already exists
    $check_email_sql = "SELECT stud_id FROM Student WHERE stud_email = ?";
    $stmt_check_email = $conn->prepare($check_email_sql);
    $stmt_check_email->bind_param("s", $stud_email);
    $stmt_check_email->execute();
    $stmt_check_email->store_result();

    if ($stmt_check_email->num_rows > 0) {
        echo "<script>alert('Email already registered. Please use a different email or log in.'); window.location.href='registerstudent.html';</script>";
        $stmt_check_email->close();
        $conn->close();
        exit();
    }
    $stmt_check_email->close();

    // Prepare an SQL statement for insertion, including stud_id and stud_name
    $sql = "INSERT INTO Student (stud_id, stud_password, stud_name, stud_email) VALUES (?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }

    
    $stmt->bind_param("isss", $stud_id, $hashed_password, $stud_name, $stud_email);

    if ($stmt->execute()) {
        echo "<script>alert('Student registration successful! You can now log in.'); window.location.href='../loginpage_student/loginstudent.html';</script>";
    } else {
        echo "<script>alert('Error: " . $stmt->error . "'); window.location.href='registerstudent.html';</script>";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "<script>alert('Invalid request method.'); window.location.href='registerstudent.html';</script>";
}
?>
