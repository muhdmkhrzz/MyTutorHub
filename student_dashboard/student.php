<?php
session_start();
// Include database connection file
include '../includes/connect.php';

// Authentication check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

$stud_id = $_SESSION['user_id'];

// --- Handle AJAX POST requests for booking and rating ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle Class Booking
    if (isset($_POST['action']) && $_POST['action'] === 'book_class') {
        $class_id = $_POST['class_id'];

        // Start transaction for atomicity
        $conn->begin_transaction();

        try {
            // Check current capacity and lock the row for update
            $sql_check_capacity = "SELECT class_capacity FROM class WHERE class_id = ? FOR UPDATE";
            $stmt_check = $conn->prepare($sql_check_capacity);
            if (!$stmt_check) {
                throw new mysqli_sql_exception("Prepare failed: (" . $conn->errno . ") " . $conn->error);
            }
            $stmt_check->bind_param("i", $class_id);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();
            $class_data = $result_check->fetch_assoc();

            if ($class_data && $class_data['class_capacity'] > 0) {
                // Decrement capacity
                $sql_update_capacity = "UPDATE class SET class_capacity = class_capacity - 1 WHERE class_id = ?";
                $stmt_update = $conn->prepare($sql_update_capacity);
                if (!$stmt_update) {
                    throw new mysqli_sql_exception("Prepare failed: (" . $conn->errno . ") " . $conn->error);
                }
                $stmt_update->bind_param("i", $class_id);
                $stmt_update->execute();

                // Add booking record
                $booking_date = date('Y-m-d'); // Current date for booking
                $sql_insert_booking = "INSERT INTO booking (stud_id, class_id, booking_date) VALUES (?, ?, ?)";
                $stmt_insert = $conn->prepare($sql_insert_booking);
                if (!$stmt_insert) {
                    throw new mysqli_sql_exception("Prepare failed: (" . $conn->errno . ") " . $conn->error);
                }
                $stmt_insert->bind_param("iis", $stud_id, $class_id, $booking_date);
                $stmt_insert->execute();

                $conn->commit();
                echo json_encode(['status' => 'success', 'message' => 'Class booked successfully!']);
            } else {
                $conn->rollback();
                echo json_encode(['status' => 'error', 'message' => 'Class is full or does not exist.']);
            }
        } catch (mysqli_sql_exception $e) {
            $conn->rollback();
            error_log("Booking error: " . $e->getMessage()); // Log the error for debugging
            echo json_encode(['status' => 'error', 'message' => 'An error occurred during booking. Please try again.']);
        }
        exit(); // Important: exit after AJAX response
    }

    // Handle Rating Submission
    if (isset($_POST['action']) && $_POST['action'] === 'submit_rating') {
        $class_id = $_POST['class_id'];
        $rating = $_POST['rating']; // This rating will be used for both class and tutor for simplicity

        // Get tutor_id for the class
        $sql_get_tutor_id = "SELECT tutor_id FROM class WHERE class_id = ?";
        $stmt_tutor = $conn->prepare($sql_get_tutor_id);
        if (!$stmt_tutor) {
            echo json_encode(['status' => 'error', 'message' => 'Failed to prepare tutor ID query.']);
            exit();
        }
        $stmt_tutor->bind_param("i", $class_id);
        $stmt_tutor->execute();
        $result_tutor = $stmt_tutor->get_result();
        $tutor_data = $result_tutor->fetch_assoc();
        $tutor_id = $tutor_data['tutor_id'] ?? null;

        if (!$tutor_id) {
            echo json_encode(['status' => 'error', 'message' => 'Tutor not found for this class.']);
            exit();
        }

        // Check if a review already exists for this student and class
        $sql_check_review = "SELECT review_id FROM review WHERE stud_id = ? AND class_id = ?";
        $stmt_check_review = $conn->prepare($sql_check_review);
        if (!$stmt_check_review) {
            echo json_encode(['status' => 'error', 'message' => 'Failed to prepare review check query.']);
            exit();
        }
        $stmt_check_review->bind_param("ii", $stud_id, $class_id);
        $stmt_check_review->execute();
        $result_check_review = $stmt_check_review->get_result();

        if ($result_check_review->num_rows > 0) {
            // Update existing review
            $sql_update_review = "UPDATE review SET class_rating = ?, tutor_rating = ?, review_date = NOW() WHERE stud_id = ? AND class_id = ?";
            $stmt_update_review = $conn->prepare($sql_update_review);
            if (!$stmt_update_review) {
                echo json_encode(['status' => 'error', 'message' => 'Failed to prepare update review query.']);
                exit();
            }
            $stmt_update_review->bind_param("iiii", $rating, $rating, $stud_id, $class_id);
            $stmt_update_review->execute();
            if ($stmt_update_review->affected_rows > 0) {
                echo json_encode(['status' => 'success', 'message' => 'Rating updated successfully!']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to update rating.']);
            }
        } else {
            // Insert new review
            $sql_insert_review = "INSERT INTO review (class_rating, tutor_rating, review_date, stud_id, class_id, tutor_id) VALUES (?, ?, NOW(), ?, ?, ?)";
            $stmt_insert_review = $conn->prepare($sql_insert_review);
            if (!$stmt_insert_review) {
                echo json_encode(['status' => 'error', 'message' => 'Failed to prepare insert review query.']);
                exit();
            }
            $stmt_insert_review->bind_param("iiiis", $rating, $rating, $stud_id, $class_id, $tutor_id);
            $stmt_insert_review->execute();
            if ($stmt_insert_review->affected_rows > 0) {
                echo json_encode(['status' => 'success', 'message' => 'Rating submitted successfully!']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to submit rating.']);
            }
        }
        exit();
    }
}

// --- Fetch student data ---
$student = [];
$sql_stud = "SELECT * FROM stud WHERE stud_id = ?";
$stmt = $conn->prepare($sql_stud);
if ($stmt) {
    $stmt->bind_param("i", $stud_id);
    $stmt->execute();
    $result_stud = $stmt->get_result();

    if ($result_stud->num_rows > 0) {
        $student = $result_stud->fetch_assoc();
    } else {
        $student['error'] = "Student data not found.";
    }
    $stmt->close();
} else {
    error_log("Failed to prepare student data query: " . $conn->error);
    $student['error'] = "Database error fetching student data.";
}


// --- Fetch booked classes (limit to 3) ---
$booked_classes = [];
$sql_booked_classes = "SELECT b.booking_id, c.class_id, c.class_title, c.class_date, c.class_starttime, c.class_endtime,
                       t.tutor_name, r.class_rating, r.tutor_rating
                       FROM booking b
                       JOIN class c ON b.class_id = c.class_id
                       JOIN tutor t ON c.tutor_id = t.tutor_id
                       LEFT JOIN review r ON b.class_id = r.class_id AND b.stud_id = r.stud_id
                       WHERE b.stud_id = ?
                       ORDER BY b.booking_date DESC
                       LIMIT 3";
$stmt_booked = $conn->prepare($sql_booked_classes);
if ($stmt_booked) {
    $stmt_booked->bind_param("i", $stud_id);
    $stmt_booked->execute();
    $result_booked_classes = $stmt_booked->get_result();

    if ($result_booked_classes && $result_booked_classes->num_rows > 0) {
        while ($row = $result_booked_classes->fetch_assoc()) {
            $booked_classes[] = $row;
        }
    }
    $stmt_booked->close();
} else {
    error_log("Failed to prepare booked classes query: " . $conn->error);
}


// --- Fetch available classes ---
$available_classes = [];
$sql_available_classes = "SELECT c.class_id, c.class_title, c.class_date, c.class_starttime, c.class_endtime,
                          c.class_capacity, c.class_fee, t.tutor_name,
                          (SELECT AVG(r.tutor_rating) FROM review r WHERE r.tutor_id = t.tutor_id) AS tutor_avg_rating
                          FROM class c
                          JOIN tutor t ON c.tutor_id = t.tutor_id
                          WHERE c.class_capacity > 0 AND c.class_date >= CURDATE()
                          ORDER BY c.class_date ASC";
$result_available_classes = $conn->query($sql_available_classes);
if ($result_available_classes && $result_available_classes->num_rows > 0) {
    while ($row = $result_available_classes->fetch_assoc()) {
        $available_classes[] = $row;
    }
} else {
    error_log("Failed to fetch available classes: " . $conn->error);
}


// Include the HTML view template
include 'student.html';

// Close database connection
$conn->close();
?>
