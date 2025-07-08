<?php
// submit_tutor_rating.php

// This file is included by student.php, so $conn and $stud_id are already available.

/**
 * Handles the submission of a tutor rating by a student.
 * Inserts a new review or updates an existing one for a specific tutor.
 *
 * @param mysqli $conn The database connection object.
 * @param string $stud_id The ID of the student submitting the rating.
 * @param string $tutor_id The ID of the tutor being rated.
 * @param int $rating The star rating (1-5).
 * @return array An associative array with 'status' and 'message'.
 */
function submitTutorRating($conn, $stud_id, $tutor_id, $rating) {
    // Validate inputs
    if (empty($stud_id) || empty($tutor_id) || $rating < 1 || $rating > 5) {
        return ['status' => 'error', 'message' => 'Invalid input for tutor rating.'];
    }

    // Get current timestamp for review_date
    $review_date = date('Y-m-d H:i:s');

    // Check if a review for this tutor by this student already exists
    // Note: A student can rate a tutor multiple times if they book different classes with the same tutor.
    // However, for simplicity, we'll check if a review for this specific tutor by this student exists.
    // If you want to allow multiple reviews per tutor per class, you'd need to adjust the logic
    // to also include class_id in the WHERE clause, but your schema doesn't have a unique constraint
    // on stud_id, tutor_id, class_id for the review table.
    // For now, we'll assume one overall rating per tutor per student.
    $stmt = $conn->prepare("SELECT review_id FROM review WHERE stud_id = ? AND tutor_id = ?");
    if (!$stmt) {
        error_log("Prepare statement failed for tutor review check: " . $conn->error);
        return ['status' => 'error', 'message' => 'Database error during review check.'];
    }
    $stmt->bind_param("ss", $stud_id, $tutor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $existing_review = $result->fetch_assoc();
    $stmt->close();

    if ($existing_review) {
        // Update existing review
        $stmt = $conn->prepare("UPDATE review SET tutor_rating = ?, review_date = ? WHERE review_id = ?");
        if (!$stmt) {
            error_log("Prepare statement failed for tutor review update: " . $conn->error);
            return ['status' => 'error', 'message' => 'Database error during review update.'];
        }
        $stmt->bind_param("isi", $rating, $review_date, $existing_review['review_id']);
        if ($stmt->execute()) {
            $stmt->close();
            return ['status' => 'success', 'message' => 'Tutor rating updated successfully!'];
        } else {
            error_log("Execute statement failed for tutor review update: " . $stmt->error);
            $stmt->close();
            return ['status' => 'error', 'message' => 'Failed to update tutor rating.'];
        }
    } else {
        // Insert new review
        // Before inserting, verify that the student has booked at least one class with this tutor.
        $stmt_check_booking_with_tutor = $conn->prepare("SELECT COUNT(*) FROM booking b JOIN class c ON b.class_id = c.class_id WHERE b.stud_id = ? AND c.tutor_id = ?");
        if (!$stmt_check_booking_with_tutor) {
            error_log("Prepare statement failed for tutor booking check: " . $conn->error);
            return ['status' => 'error', 'message' => 'Database error during tutor booking verification.'];
        }
        $stmt_check_booking_with_tutor->bind_param("ss", $stud_id, $tutor_id);
        $stmt_check_booking_with_tutor->execute();
        $booking_result = $stmt_check_booking_with_tutor->get_result();
        $booking_row = $booking_result->fetch_row();
        $booking_count = $booking_row[0];
        $stmt_check_booking_with_tutor->close();

        if ($booking_count == 0) {
            return ['status' => 'error', 'message' => 'You can only rate tutors you have had classes with.'];
        }

        // For a new tutor rating, we need a class_id to insert into the review table.
        // We'll use the class_id of the most recent class the student booked with this tutor.
        $stmt_get_class_id = $conn->prepare("SELECT c.class_id FROM booking b JOIN class c ON b.class_id = c.class_id WHERE b.stud_id = ? AND c.tutor_id = ? ORDER BY b.booking_date DESC LIMIT 1");
        if (!$stmt_get_class_id) {
            error_log("Prepare statement failed for getting class_id for tutor review: " . $conn->error);
            return ['status' => 'error', 'message' => 'Database error getting class info for tutor review.'];
        }
        $stmt_get_class_id->bind_param("ss", $stud_id, $tutor_id);
        $stmt_get_class_id->execute();
        $class_id_result = $stmt_get_class_id->get_result();
        $class_id_data = $class_id_result->fetch_assoc();
        $stmt_get_class_id->close();

        if (!$class_id_data || empty($class_id_data['class_id'])) {
            return ['status' => 'error', 'message' => 'Could not find a relevant class to associate with this tutor rating.'];
        }
        $class_id_for_review = $class_id_data['class_id'];


        $stmt = $conn->prepare("INSERT INTO review (tutor_rating, review_date, stud_id, class_id, tutor_id) VALUES (?, ?, ?, ?, ?)");
        if (!$stmt) {
            error_log("Prepare statement failed for tutor review insert: " . $conn->error);
            return ['status' => 'error', 'message' => 'Database error during review insertion.'];
        }
        $stmt->bind_param("issis", $rating, $review_date, $stud_id, $class_id_for_review, $tutor_id);
        if ($stmt->execute()) {
            $stmt->close();
            return ['status' => 'success', 'message' => 'Tutor rating submitted successfully!'];
        } else {
            error_log("Execute statement failed for tutor review insert: " . $stmt->error);
            $stmt->close();
            return ['status' => 'error', 'message' => 'Failed to submit tutor rating.'];
        }
    }
}
?>
