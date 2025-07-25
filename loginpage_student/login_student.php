<?php
// Start a session to manage user login state
session_start();


require_once '../database/connect.php';


if ($_SERVER["REQUEST_METHOD"] == "POST") {
   
    $stud_id = $conn->real_escape_string($_POST['id']);
    $password = $_POST['password']; 

    // Basic validation
    if (empty($stud_id) || empty($password)) {

        echo "<script>alert('Please enter both Student ID and password.'); window.location.href='loginstudent.html';</script>";
        exit();
    }

    $table = "student";
    $id_column = "stud_id";
    $password_column = "stud_password";
    $name_column = "stud_name";
    $redirect_success = "../student_dashboard/student.html";
    $redirect_fail = "loginstudent.html";

   
    $sql = "SELECT {$id_column}, {$password_column}, {$name_column} FROM {$table} WHERE {$id_column} = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }

    $stmt->bind_param("i", $stud_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 1) {
        
        $stmt->bind_result($db_id, $hashed_password, $user_name);
        $stmt->fetch();

        
        if (password_verify($password, $hashed_password)) {
            // Password is correct, set session variables
            $_SESSION['loggedin'] = true;           
            $_SESSION['stud_id'] = $db_id;
            $_SESSION['user_name'] = $user_name;
            $_SESSION['role'] = 'student'; 

            // Redirect to the student dashboard
            echo "<script>alert('Login successful! Welcome, " . $user_name . "!'); window.location.href='" . $redirect_success . "';</script>";
        } else {
            // Incorrect password
            echo "<script>alert('Invalid Student ID or password.'); window.location.href='" . $redirect_fail . "';</script>";
        }
    } else {
        // Student not found
        echo "<script>alert('Invalid Student ID or password.'); window.location.href='" . $redirect_fail . "';</script>";
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();
} else {
    // If accessed directly without POST request
    echo "<script>alert('Invalid request method.'); window.location.href='loginstudent.html';</script>";
}
?>
