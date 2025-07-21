<?php
// Enable error reporting for debugging 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
header('Content-Type: application/json');

// Path to database connection file 
require_once 'connect.php';

$response = ['success' => false, 'message' => '', 'courses' => []];

// For tutor dashboard, we assume the user is already logged in as a tutor.
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    $response['message'] = 'Unauthorized access.';
    echo json_encode($response);
    exit();
}

try {
    // Fetch all courses
    $sql = "SELECT course_id, course_name FROM course ORDER BY course_name ASC";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $courses = [];
    while ($row = $result->fetch_assoc()) {
        $courses[] = $row;
    }

    $response['success'] = true;
    $response['courses'] = $courses;

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
