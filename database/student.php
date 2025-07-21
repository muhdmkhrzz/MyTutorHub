<?php
// Start the session to access session variables (e.g., student ID)
session_start();

// Include the database connection file
require_once 'connect.php'; 

// Set content type to JSON for all responses
header('Content-Type: application/json');

// Check if the student is logged in (assuming 'stud_id' is stored in session upon login)
if (!isset($_SESSION['stud_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in.']);
    exit();
}

$stud_id = $_SESSION['stud_id']; 

// Check if an action is requested via POST
if (isset($_POST['action'])) {
    $action = $_POST['action'];

    switch ($action) {
        case 'fetch_dashboard_data':
            require_once 'fetch_dashboard_data.php';
            $dashboardData = fetchDashboardData($conn, $stud_id);
            echo json_encode(['status' => 'success', 'data' => $dashboardData]);
            break;

        case 'book_class':
            require_once 'book_class.php';

            $class_id = isset($_POST['class_id']) ? (int)$_POST['class_id'] : 0;
            $result = bookClass($conn, $stud_id, $class_id);
            echo json_encode($result);
            break;

        case 'submit_class_rating':
            require_once 'submit_class_rating.php';
            $class_id = isset($_POST['class_id']) ? (int)$_POST['class_id'] : 0;
            $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
            $result = submitClassRating($conn, $stud_id, $class_id, $rating);
            echo json_encode($result);
            break;

        case 'submit_tutor_rating':
            require_once 'submit_tutor_rating.php';
            $tutor_id = isset($_POST['tutor_id']) ? $_POST['tutor_id'] : 0;
            $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
            $result = submitTutorRating($conn, $stud_id, $tutor_id, $rating);
            echo json_encode($result);
            break;

        case 'fetch_all_bookings': 
            require_once 'fetch_all_bookings.php';
            $allBookings = fetchAllBookings($conn, $stud_id);
            echo json_encode(['status' => 'success', 'data' => $allBookings]);
            break;

        case 'fetch_all_available_classes': 
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

$conn->close();
?>
