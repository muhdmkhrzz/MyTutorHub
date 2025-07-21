<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
header('Content-Type: application/json');

require_once '../database/connect.php'; 

$response = ['success' => false, 'message' => '', 'class_details' => null];

// Check if the user is logged in and is a tutor
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'tutor') {
    $response['message'] = 'Unauthorized access.';
    echo json_encode($response);
    exit();
}

if (!isset($_GET['class_id']) || !is_numeric($_GET['class_id'])) {
    $response['message'] = 'Invalid class ID provided.';
    echo json_encode($response);
    exit();
}

$class_id = (int)$_GET['class_id'];
$tutor_id = $_SESSION['user_id']; 

try {
    // Ensure class_deadline is explicitly selected
    $sql = "SELECT class_id, class_title, class_description, class_date, class_starttime, class_endtime, class_capacity, class_fee, course_id, class_file, class_deadline FROM class WHERE class_id = ? AND tutor_id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("ii", $class_id, $tutor_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $class_details = $result->fetch_assoc();

        // Optionally, fetch course name
        if ($class_details['course_id']) {
            $sql_course = "SELECT course_name FROM course WHERE course_id = ?";
            $stmt_course = $conn->prepare($sql_course);
            if ($stmt_course) {
                $stmt_course->bind_param("i", $class_details['course_id']);
                $stmt_course->execute();
                $result_course = $stmt_course->get_result();
                if ($course_row = $result_course->fetch_assoc()) {
                    $class_details['course_name'] = $course_row['course_name'];
                }
                $stmt_course->close();
            }
        }

        // Optionally, count enrolled students for this class from the Booking table
        $sql_enrolled_students = "SELECT COUNT(booking_id) AS enrolled_count FROM booking WHERE class_id = ?";
        $stmt_enrolled = $conn->prepare($sql_enrolled_students);
        if ($stmt_enrolled) {
            $stmt_enrolled->bind_param("i", $class_id);
            $stmt_enrolled->execute();
            $result_enrolled = $stmt_enrolled->get_result();
            if ($enrolled_row = $result_enrolled->fetch_assoc()) {
                $class_details['enrolled_students_count'] = $enrolled_row['enrolled_count'];
            }
            $stmt_enrolled->close();
        }

        $response['success'] = true;
        $response['class_details'] = $class_details;
    } else {
        $response['message'] = 'Class not found or you do not have permission to view it.';
    }

    $stmt->close();

} catch (Exception $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
} finally {
    if (isset($conn) && $conn->ping()) {
        $conn->close();
    }
    echo json_encode($response);
}
?>
