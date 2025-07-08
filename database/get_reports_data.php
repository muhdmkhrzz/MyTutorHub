<?php
// Ensure error reporting is set to output errors to the log, not directly to the browser
// This helps prevent HTML errors from breaking JSON responses
ini_set('display_errors', 'Off');
ini_set('log_errors', 'On');
ini_set('error_log', __DIR__ . '/php-error.log'); // Log errors to a file in the same directory

// Include your database connection file
// IMPORTANT: Ensure db_connect.php exists and correctly establishes a database connection.
// If db_connect.php fails or outputs anything, it will cause a JSON parsing error on the frontend.
require_once '../database/connect.php'; // Assuming db_connect.php handles your database connection

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => 'An unknown error occurred.',
    'total_revenue' => 0,
    'total_student_bookings' => 0
];

try {
    // Check if the database connection ($conn) is successfully established
    if (!isset($conn) || $conn->connect_error) {
        throw new Exception("Database connection failed: " . ($conn->connect_error ?? 'Unknown error'));
    }

    // Query to calculate Total Revenue
    // Sum of total_payment from the booking table
    // CORRECTED TABLE NAME: 'booking' instead of 'mytutorhub_booking'
    $stmt_revenue = $conn->prepare("SELECT SUM(total_payment) AS total_revenue FROM booking");
    if (!$stmt_revenue) {
        throw new Exception("Failed to prepare revenue statement: " . $conn->error);
    }
    $stmt_revenue->execute();
    $result_revenue = $stmt_revenue->get_result();
    $row_revenue = $result_revenue->fetch_assoc();
    $total_revenue = $row_revenue['total_revenue'] ?? 0; // Use null coalescing to default to 0 if no bookings

    // Query to calculate Total Student Bookings
    // Count of all bookings made
    // CORRECTED TABLE NAME: 'booking' instead of 'mytutorhub_booking'
    $stmt_bookings = $conn->prepare("SELECT COUNT(booking_id) AS total_bookings FROM booking");
    if (!$stmt_bookings) {
        throw new Exception("Failed to prepare bookings statement: " . $conn->error);
    }
    $stmt_bookings->execute();
    $result_bookings = $stmt_bookings->get_result();
    $row_bookings = $result_bookings->fetch_assoc();
    $total_student_bookings = $row_bookings['total_bookings'] ?? 0; // Default to 0 if no bookings

    $response['success'] = true;
    $response['message'] = 'Reports data fetched successfully.';
    $response['total_revenue'] = number_format((float)$total_revenue, 2, '.', ''); // Format to 2 decimal places
    $response['total_student_bookings'] = (int)$total_student_bookings;

} catch (Exception $e) {
    // Log the error for debugging on the server
    error_log('Reports data fetch error: ' . $e->getMessage());
    $response['message'] = 'Server error: ' . $e->getMessage();
    // In a production environment, you might want a more generic message here
    // $response['message'] = 'Failed to load reports data due to a server error.';
} finally {
    // Close statements and connection
    if (isset($stmt_revenue) && $stmt_revenue instanceof mysqli_stmt) {
        $stmt_revenue->close();
    }
    if (isset($stmt_bookings) && $stmt_bookings instanceof mysqli_stmt) {
        $stmt_bookings->close();
    }
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}

echo json_encode($response);
?>
