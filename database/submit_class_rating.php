<?php
/**
 * Handles the submission of a class rating by a student.
 * Inserts a new review or updates an existing one for a specific class.
 *
 * @param mysqli $conn The database connection object.
 * @param string $stud_id The ID of the student submitting the rating.
 * @param int $class_id The ID of the class being rated.
 * @param int $rating The star rating (1-5).
 * @return array An associative array with 'status' and 'message'.
 */
function submitClassRating($conn, $stud_id, $class_id, $rating) {
    // Validate inputs
    if (empty($stud_id) || $class_id <= 0 || $rating < 1 || $rating > 5) {
        return ['status' => 'error', 'message' => 'Invalid input for class rating.'];
    }

    // Get current timestamp for review_date
    $review_date = date('Y-m-d H:i:s');

    // Check if a review for this class by this student already exists
    $stmt = $conn->prepare("SELECT review_id FROM review WHERE stud_id = ? AND class_id = ?");
    if (!$stmt) {
        error_log("Prepare statement failed for class review check: " . $conn->error);
        return ['status' => 'error', 'message' => 'Database error during review check.'];
    }
    $stmt->bind_param("si", $stud_id, $class_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $existing_review = $result->fetch_assoc();
    $stmt->close();

    if ($existing_review) {
        // Update existing review
        $stmt = $conn->prepare("UPDATE review SET class_rating = ?, review_date = ? WHERE review_id = ?");
        if (!$stmt) {
            error_log("Prepare statement failed for class review update: " . $conn->error);
            return ['status' => 'error', 'message' => 'Database error during review update.'];
        }
        $stmt->bind_param("isi", $rating, $review_date, $existing_review['review_id']);
        if ($stmt->execute()) {
            $stmt->close();
            return ['status' => 'success', 'message' => 'Class rating updated successfully!'];
        } else {
            error_log("Execute statement failed for class review update: " . $stmt->error);
            $stmt->close();
            return ['status' => 'error', 'message' => 'Failed to update class rating.'];
        }
    } else {

        $stmt_check_booking = $conn->prepare("SELECT COUNT(*) FROM booking WHERE stud_id = ? AND class_id = ?");
        if (!$stmt_check_booking) {
            error_log("Prepare statement failed for booking check: " . $conn->error);
            return ['status' => 'error', 'message' => 'Database error during booking verification.'];
        }
        $stmt_check_booking->bind_param("si", $stud_id, $class_id);
        $stmt_check_booking->execute();
        $booking_result = $stmt_check_booking->get_result();
        $booking_row = $booking_result->fetch_row();
        $booking_count = $booking_row[0];
        $stmt_check_booking->close();

        if ($booking_count == 0) {
            return ['status' => 'error', 'message' => 'You can only rate classes you have booked.'];
        }

        // Get the tutor_id associated with this class for the review table
        $stmt_get_tutor = $conn->prepare("SELECT tutor_id FROM class WHERE class_id = ?");
        if (!$stmt_get_tutor) {
            error_log("Prepare statement failed for getting tutor_id: " . $conn->error);
            return ['status' => 'error', 'message' => 'Database error getting tutor info.'];
        }
        $stmt_get_tutor->bind_param("i", $class_id);
        $stmt_get_tutor->execute();
        $tutor_result = $stmt_get_tutor->get_result();
        $tutor_data = $tutor_result->fetch_assoc();
        $stmt_get_tutor->close();

        if (!$tutor_data || empty($tutor_data['tutor_id'])) {
            return ['status' => 'error', 'message' => 'Could not find associated tutor for this class.'];
        }
        $tutor_id = $tutor_data['tutor_id'];

        $stmt = $conn->prepare("INSERT INTO review (class_rating, review_date, stud_id, class_id, tutor_id) VALUES (?, ?, ?, ?, ?)");
        if (!$stmt) {
            error_log("Prepare statement failed for class review insert: " . $conn->error);
            return ['status' => 'error', 'message' => 'Database error during review insertion.'];
        }
        $stmt->bind_param("issis", $rating, $review_date, $stud_id, $class_id, $tutor_id);
        if ($stmt->execute()) {
            $stmt->close();
            return ['status' => 'success', 'message' => 'Class rating submitted successfully!'];
        } else {
            error_log("Execute statement failed for class review insert: " . $stmt->error);
            $stmt->close();
            return ['status' => 'error', 'message' => 'Failed to submit class rating.'];
        }
    }
}
?>
