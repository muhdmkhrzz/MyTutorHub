<?php
// Enable error reporting for debugging (REMOVE IN PRODUCTION)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
header('Content-Type: application/json');

// Path to your database connection file (from tutor_dashboard to database folder)
require_once 'connect.php';

$response = ['success' => false, 'message' => ''];

// Check if the user is logged in and is a tutor
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'tutor') {
    $response['message'] = 'Unauthorized access.';
    echo json_encode($response);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tutor_id = $_SESSION['user_id']; 

    // Sanitize and retrieve form data
    $class_title = $conn->real_escape_string($_POST['class_title']);
    $class_description = $conn->real_escape_string($_POST['class_description']);
    $class_date = $conn->real_escape_string($_POST['class_date']);
    $class_starttime = $conn->real_escape_string($_POST['class_starttime']);
    $class_endtime = $conn->real_escape_string($_POST['class_endtime']);
    $class_capacity = (int)$_POST['class_capacity'];
    
    // Handle optional deadline: if empty, set to NULL, otherwise use the string
    // No real_escape_string here, as bind_param handles escaping for strings.
    $class_deadline = !empty($_POST['class_deadline']) ? $_POST['class_deadline'] : NULL;

    $class_fee = (double)$_POST['class_fee'];
    $course_id = (int)$_POST['course_id']; 

    // Basic validation
    if (empty($class_title) || empty($class_date) || empty($class_starttime) || empty($class_endtime) || empty($class_capacity) || empty($class_fee) || empty($course_id)) {
        $response['message'] = 'Please fill all required fields (including selecting a course).';
        echo json_encode($response);
        exit();
    }

    try {
    
        $sql_insert_class = "INSERT INTO class (class_title, class_description, class_date, class_starttime, class_endtime, class_capacity, class_deadline, class_fee, tutor_id, course_id) VALUES (?, ?, ?, ?, ?, ?, STR_TO_DATE(?, '%Y-%m-%d'), ?, ?, ?)";
        $stmt_insert_class = $conn->prepare($sql_insert_class);
        if (!$stmt_insert_class) {
            throw new Exception("Prepare failed (insert class): " . $conn->error);
        }
        
        
        $stmt_insert_class->bind_param("sssssisdii", 
            $class_title,
            $class_description,
            $class_date,
            $class_starttime,
            $class_endtime,
            $class_capacity,
            $class_deadline, 
            $class_fee,
            $tutor_id,
            $course_id
        );

        if ($stmt_insert_class->execute()) {
            $response['success'] = true;
            $response['message'] = 'Class created successfully!';
        } else {
            // Log the detailed SQL error for debugging
            error_log("SQL Error: " . $stmt_insert_class->error);
            throw new Exception("Execute failed (insert class): " . $stmt_insert_class->error);
        }
        $stmt_insert_class->close();

    } catch (Exception $e) {
        $response['message'] = 'Error creating class: ' . $e->getMessage();
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
