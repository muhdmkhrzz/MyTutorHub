<?php
// student.php - Main handler for student dashboard actions

// Start the session to access session variables (e.g., student ID)
session_start();

// Include the database connection file
require_once 'connect.php'; // Adjust path if your connect.php is in a different directory

// Set content type to JSON for all responses
header('Content-Type: application/json');

// Check if the student is logged in (assuming 'stud_id' is stored in session upon login)
if (!isset($_SESSION['stud_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in.']);
    exit();
}

$stud_id = $_SESSION['stud_id']; // Get the logged-in student's ID

// Check if an action is requested via POST
if (isset($_POST['action'])) {
    $action = $_POST['action'];

    switch ($action) {
        case 'fetch_dashboard_data':
            // Include file to fetch all dashboard data
            require_once 'fetch_dashboard_data.php';
            // Call a function from fetch_dashboard_data.php to get data
            $dashboardData = fetchDashboardData($conn, $stud_id);
            echo json_encode(['status' => 'success', 'data' => $dashboardData]);
            break;

        case 'book_class':
            // Include file to handle class booking
            require_once 'book_class.php';

            $class_id = isset($_POST['class_id']) ? (int)$_POST['class_id'] : 0;
            $result = bookClass($conn, $stud_id, $class_id);
            echo json_encode($result);
            break;

        case 'submit_class_rating':
            // Include file to handle class rating submission
            require_once 'submit_class_rating.php';
            // Call a function from submit_class_rating.php
            $class_id = isset($_POST['class_id']) ? (int)$_POST['class_id'] : 0;
            $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
            $result = submitClassRating($conn, $stud_id, $class_id, $rating);
            echo json_encode($result);
            break;

        case 'submit_tutor_rating':
            // Include file to handle tutor rating submission
            require_once 'submit_tutor_rating.php';
            // Call a function from submit_tutor_rating.php
            $tutor_id = isset($_POST['tutor_id']) ? $_POST['tutor_id'] : '';
            $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
            $result = submitTutorRating($conn, $stud_id, $tutor_id, $rating);
            echo json_encode($result);
            break;

        case 'fetch_all_bookings': // New action for all bookings
            require_once 'fetch_all_bookings.php';
            $allBookings = fetchAllBookings($conn, $stud_id);
            echo json_encode(['status' => 'success', 'data' => $allBookings]);
            break;

        case 'fetch_all_available_classes': // New action for all available classes
            require_once 'fetch_all_available_classes.php';
            $allAvailableClasses = fetchAllAvailableClasses($conn, $stud_id);
            echo json_encode(['status' => 'success', 'data' => $allAvailableClasses]);
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
            break;
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'No action specified.']);
}

// Close the database connection
$conn->close();
?>
