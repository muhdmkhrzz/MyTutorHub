<?php
ini_set('display_errors', 'Off');
ini_set('log_errors', 'On');
ini_set('error_log', __DIR__ . '/php-error.log'); 

require_once '../database/connect.php'; 

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => 'An unknown error occurred.',
    'total_revenue' => 0,
    'total_student_bookings' => 0
];

try {
    if (!isset($conn) || $conn->connect_error) {
        throw new Exception("Database connection failed: " . ($conn->connect_error ?? 'Unknown error'));
    }

    $stmt_revenue = $conn->prepare("SELECT SUM(total_payment) AS total_revenue FROM booking");
    if (!$stmt_revenue) {
        throw new Exception("Failed to prepare revenue statement: " . $conn->error);
    }
    $stmt_revenue->execute();
    $result_revenue = $stmt_revenue->get_result();
    $row_revenue = $result_revenue->fetch_assoc();
    $total_revenue = $row_revenue['total_revenue'] ?? 0; 

    $stmt_bookings = $conn->prepare("SELECT COUNT(booking_id) AS total_bookings FROM booking");
    if (!$stmt_bookings) {
        throw new Exception("Failed to prepare bookings statement: " . $conn->error);
    }
    $stmt_bookings->execute();
    $result_bookings = $stmt_bookings->get_result();
    $row_bookings = $result_bookings->fetch_assoc();
    $total_student_bookings = $row_bookings['total_bookings'] ?? 0; 

    $response['success'] = true;
    $response['message'] = 'Reports data fetched successfully.';
    $response['total_revenue'] = number_format((float)$total_revenue, 2, '.', ''); 
    $response['total_student_bookings'] = (int)$total_student_bookings;

} catch (Exception $e) {
    error_log('Reports data fetch error: ' . $e->getMessage());
    $response['message'] = 'Server error: ' . $e->getMessage();
} finally {
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
