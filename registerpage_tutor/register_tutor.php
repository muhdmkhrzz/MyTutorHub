<?php
// Include the database connection file
// CORRECTED PATH: Go up one directory, then into the 'database' folder
require_once '../database/connect.php';

// Check if the form was submitted using POST method
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and retrieve form data
    $tutor_id = $conn->real_escape_string($_POST['tutor_id']); // Now directly getting tutor_id
    $tutor_name = $conn->real_escape_string($_POST['tutor_name']); // New field for tutor name
    $tutor_email = $conn->real_escape_string($_POST['email']); // Assuming tutor_email column exists
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Basic validation
    if (empty($tutor_id) || empty($tutor_name) || empty($tutor_email) || empty($password) || empty($confirm_password)) {
        echo "<script>alert('Please fill in all required fields.'); window.location.href='registertutor.html';</script>";
        exit();
    }

    if ($password !== $confirm_password) {
        echo "<script>alert('Passwords do not match. Please try again.'); window.location.href='registertutor.html';</script>";
        exit();
    }

    // Hash the password for security
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Check if the tutor_id already exists
    $check_id_sql = "SELECT tutor_id FROM Tutor WHERE tutor_id = ?";
    $stmt_check_id = $conn->prepare($check_id_sql);
    $stmt_check_id->bind_param("i", $tutor_id); // 'i' for integer, assuming tutor_id is INT
    $stmt_check_id->execute();
    $stmt_check_id->store_result();

    if ($stmt_check_id->num_rows > 0) {
        echo "<script>alert('Tutor ID already registered. Please choose a different ID or log in.'); window.location.href='registertutor.html';</script>";
        $stmt_check_id->close();
        $conn->close();
        exit();
    }
    $stmt_check_id->close();

    // Check if the email already exists (assuming tutor_email column exists)
    $check_email_sql = "SELECT tutor_id FROM Tutor WHERE tutor_email = ?";
    $stmt_check_email = $conn->prepare($check_email_sql);
    $stmt_check_email->bind_param("s", $tutor_email);
    $stmt_check_email->execute();
    $stmt_check_email->store_result();

    if ($stmt_check_email->num_rows > 0) {
        echo "<script>alert('Email already registered. Please use a different email or log in.'); window.location.href='registertutor.html';</script>";
        $stmt_check_email->close();
        $conn->close();
        exit();
    }
    $stmt_check_email->close();

    // Prepare an SQL statement for insertion, including tutor_id and tutor_name
    // This assumes you have added a 'tutor_email' column to your Tutor table.
    $sql = "INSERT INTO Tutor (tutor_id, tutor_password, tutor_name, tutor_email) VALUES (?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }

    // Bind parameters: 'isss' for integer (tutor_id), string (password), string (name), string (email)
    $stmt->bind_param("isss", $tutor_id, $hashed_password, $tutor_name, $tutor_email);

    if ($stmt->execute()) {
        echo "<script>alert('Tutor registration successful! You can now log in.'); window.location.href='../loginpage_tutor/logintutor.html';</script>";
    } else {
        echo "<script>alert('Error: " . $stmt->error . "'); window.location.href='registertutor.html';</script>";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "<script>alert('Invalid request method.'); window.location.href='registertutor.html';</script>";
}
?>
