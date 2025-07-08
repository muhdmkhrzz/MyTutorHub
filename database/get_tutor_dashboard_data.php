<?php
// Enable error reporting for debugging (REMOVE IN PRODUCTION)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
header('Content-Type: application/json'); // Indicate that the response is JSON

// Path to your database connection file (from tutor_dashboard to database folder)
require_once 'connect.php';

$response = ['success' => false, 'message' => ''];

// Check if the user is logged in and is a tutor
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'tutor') {
    $response['message'] = 'Unauthorized access.';
    echo json_encode($response);
    exit();
}

$tutor_id = $_SESSION['user_id']; // Get the logged-in tutor's ID from the session

try {
    // Fetch Today's Schedule (Classes for today)
    $today = date('Y-m-d');
    // ADDED class_file to SELECT statement
    $sql_today_classes = "SELECT class_id, class_title, class_description, class_date, class_starttime, class_endtime, class_capacity, class_file FROM Class WHERE tutor_id = ? AND class_date = ? ORDER BY class_starttime ASC";
    $stmt_today = $conn->prepare($sql_today_classes);
    if (!$stmt_today) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    $stmt_today->bind_param("is", $tutor_id, $today);
    $stmt_today->execute();
    $result_today = $stmt_today->get_result();
    $today_classes = [];
    while ($row = $result_today->fetch_assoc()) {
        // Calculate enrolled students (placeholder for now, actual implementation needs Booking table count)
        // For simplicity, let's assume class_capacity is the max, and we'll just show it for now.
        // A more complex query would count bookings for each class.
        $row['enrolled_students'] = rand(1, $row['class_capacity']); // Placeholder: random number of students
        $today_classes[] = $row;
    }
    $stmt_today->close();

    // Fetch All My Classes
    // ADDED class_file to SELECT statement
    $sql_my_classes = "SELECT class_id, class_title, class_description, class_file FROM Class WHERE tutor_id = ? ORDER BY class_date DESC, class_starttime DESC";
    $stmt_my = $conn->prepare($sql_my_classes);
    if (!$stmt_my) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    $stmt_my->bind_param("i", $tutor_id);
    $stmt_my->execute();
    $result_my = $stmt_my->get_result();
    $my_classes = [];
    while ($row = $result_my->fetch_assoc()) {
        $my_classes[] = $row;
    }
    $stmt_my->close();

    $response['success'] = true;
    $response['today_classes'] = $today_classes;
    $response['my_classes'] = $my_classes;

} catch (Exception $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
} finally {
    if (isset($conn) && $conn->ping()) { // Check if connection is still open before closing
        $conn->close();
    }
    echo json_encode($response);
}
?>
