<?php
// Enable error reporting for debugging (REMOVE IN PRODUCTION)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
header('Content-Type: application/json');

require_once 'connect.php'; // Path to your database connection file

$response = ['success' => false, 'message' => ''];

// Check if the user is logged in and is a tutor
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'tutor') {
    $response['message'] = 'Unauthorized access.';
    echo json_encode($response);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tutor_id = $_SESSION['user_id']; // Get the logged-in tutor's ID

    // Sanitize and retrieve form data
    $class_id = (int)$_POST['class_id'];
    $class_title = $conn->real_escape_string($_POST['class_title']);
    $course_id = (int)$_POST['course_id'];
    $class_description = $conn->real_escape_string($_POST['class_description']);
    $class_date = $conn->real_escape_string($_POST['class_date']);
    $class_starttime = $conn->real_escape_string($_POST['class_starttime']);
    $class_endtime = $conn->real_escape_string($_POST['class_endtime']);
    $class_capacity = (int)$_POST['class_capacity'];
    $class_deadline = !empty($_POST['class_deadline']) ? $conn->real_escape_string($_POST['class_deadline']) : NULL; // Handle optional deadline
    $class_fee = (double)$_POST['class_fee'];
    $class_file = !empty($_POST['class_file']) ? $conn->real_escape_string($_POST['class_file']) : NULL; // Handle optional class_file

    try {
        // Update the class details
        $sql_update_class = "UPDATE Class SET 
                                class_title = ?, 
                                class_description = ?, 
                                class_date = ?, 
                                class_starttime = ?, 
                                class_endtime = ?, 
                                class_capacity = ?, 
                                class_deadline = ?, 
                                class_fee = ?, 
                                course_id = ?,
                                class_file = ?
                             WHERE class_id = ? AND tutor_id = ?";
        
        $stmt_update_class = $conn->prepare($sql_update_class);
        if (!$stmt_update_class) {
            throw new Exception("Prepare failed (update class): " . $conn->error);
        }

        // sssssisdii - 10 strings/integers/doubles for 10 parameters
        // class_title, class_description, class_date, class_starttime, class_endtime,
        // class_capacity, class_deadline, class_fee, course_id, class_file,
        // class_id, tutor_id
        $stmt_update_class->bind_param("sssssisdiiii", 
            $class_title, 
            $class_description, 
            $class_date, 
            $class_starttime, 
            $class_endtime, 
            $class_capacity, 
            $class_deadline, 
            $class_fee, 
            $course_id,
            $class_file,
            $class_id, 
            $tutor_id
        );

        if ($stmt_update_class->execute()) {
            if ($stmt_update_class->affected_rows > 0) {
                $response['success'] = true;
                $response['message'] = 'Class updated successfully!';
            } else {
                $response['message'] = 'No changes made or class not found/owned by you.';
            }
        } else {
            error_log("SQL Error (update class): " . $stmt_update_class->error);
            throw new Exception("Execute failed (update class): " . $stmt_update_class->error);
        }
        $stmt_update_class->close();

    } catch (Exception $e) {
        $response['message'] = 'Error updating class: ' . $e->getMessage();
    } finally {
        if (isset($conn) && $conn->ping()) {
            $conn->close();
        }
        echo json_encode($response);
    }
} else {
    $response['message'] = 'Invalid request method.';
    echo json_encode($response);
}
?>
