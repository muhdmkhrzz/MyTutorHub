<?php
// Enable error reporting for debugging (REMOVE IN PRODUCTION)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
header('Content-Type: application/json');

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

    // Sanitize and retrieve class_id
    $class_id = (int)$_POST['class_id'];

    if (empty($class_id)) {
        $response['message'] = 'Invalid class ID provided.';
        echo json_encode($response);
        exit();
    }

    try {
        // Start a transaction for atomicity
        $conn->begin_transaction(); 

        // 1. Delete related records from the 'Review' table
        $sql_delete_reviews = "DELETE FROM review WHERE class_id = ?";
        $stmt_delete_reviews = $conn->prepare($sql_delete_reviews);
        if ($stmt_delete_reviews) {
            $stmt_delete_reviews->bind_param("i", $class_id);
            $stmt_delete_reviews->execute();
            $stmt_delete_reviews->close();
        } else {
            
            error_log("Prepare failed (delete reviews): " . $conn->error);
        }

        // 2. Delete related records from the 'Booking' table
        $sql_delete_bookings = "DELETE FROM booking WHERE class_id = ?";
        $stmt_delete_bookings = $conn->prepare($sql_delete_bookings);
        if ($stmt_delete_bookings) {
            $stmt_delete_bookings->bind_param("i", $class_id);
            $stmt_delete_bookings->execute();
            $stmt_delete_bookings->close();
        } else {
            error_log("Prepare failed (delete bookings): " . $conn->error);
        }

        // 3. Delete the class itself
        $sql_delete_class = "DELETE FROM class WHERE class_id = ? AND tutor_id = ?";
        $stmt_delete_class = $conn->prepare($sql_delete_class);
        if (!$stmt_delete_class) {
            throw new Exception("Prepare failed (delete class): " . $conn->error);
        }
        $stmt_delete_class->bind_param("ii", $class_id, $tutor_id);

        if ($stmt_delete_class->execute()) {
            if ($stmt_delete_class->affected_rows > 0) {
                $conn->commit(); 
                $response['success'] = true;
                $response['message'] = 'Class and all related data deleted successfully!';
            } else {
                $conn->rollback(); 
                $response['message'] = 'Class not found or you do not have permission to delete it.';
            }
        } else {
            $conn->rollback(); 
            error_log("SQL Error (delete class): " . $stmt_delete_class->error);
            throw new Exception("Execute failed (delete class): " . $stmt_delete_class->error);
        }
        $stmt_delete_class->close();

    } catch (Exception $e) {
        if ($conn->autocommit(false)) {
             $conn->rollback(); 
             $conn->autocommit(true); 
        }
        $response['message'] = 'Error deleting class: ' . $e->getMessage();
    } finally {
        // Ensure connection is closed
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
