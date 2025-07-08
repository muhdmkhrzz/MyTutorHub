<?php
// book_class.php

// This file is included by student.php, so $conn and $stud_id are already available.

/**
 * Handles the booking of a class by a student.
 * Inserts a new record into the 'booking' table and decrements the class capacity.
 *
 * @param mysqli $conn The database connection object.
 * @param string $stud_id The ID of the student attempting to book.
 * @param int $class_id The ID of the class to be booked.
 * @return array An associative array with 'status' and 'message'.
 */
function bookClass($conn, $stud_id, $class_id) {
    // Validate inputs
    if (empty($stud_id) || $class_id <= 0) {
        return ['status' => 'error', 'message' => 'Invalid student ID or class ID provided.'];
    }

    // Start a transaction for atomicity
    $conn->begin_transaction();

    try {
        // 1. Check if the class exists and has available capacity
        $stmt = $conn->prepare("SELECT class_capacity, class_fee FROM class WHERE class_id = ? FOR UPDATE"); // FOR UPDATE locks the row
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $conn->error);
        }
        $stmt->bind_param("i", $class_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $class_info = $result->fetch_assoc();
        $stmt->close();

        if (!$class_info) {
            $conn->rollback();
            return ['status' => 'error', 'message' => 'Class not found.'];
        }

        $current_capacity = $class_info['class_capacity'];
        $class_fee = $class_info['class_fee'];

        if ($current_capacity <= 0) {
            $conn->rollback();
            return ['status' => 'error', 'message' => 'Class is fully booked.'];
        }

        // 2. Check if the student has already booked this class
        $stmt = $conn->prepare("SELECT COUNT(*) FROM booking WHERE stud_id = ? AND class_id = ?");
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $conn->error);
        }
        $stmt->bind_param("si", $stud_id, $class_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_row();
        $existing_booking_count = $row[0];
        $stmt->close();

        if ($existing_booking_count > 0) {
            $conn->rollback();
            return ['status' => 'error', 'message' => 'You have already booked this class.'];
        }

        // 3. Insert into booking table
        $booking_date = date('Y-m-d'); // Current date
        $stmt = $conn->prepare("INSERT INTO booking (booking_date, total_payment, stud_id, class_id) VALUES (?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $conn->error);
        }
        $stmt->bind_param("sdsi", $booking_date, $class_fee, $stud_id, $class_id);
        if (!$stmt->execute()) {
            throw new Exception("Execute statement failed: " . $stmt->error);
        }
        $stmt->close();

        // 4. Decrement class capacity
        $new_capacity = $current_capacity - 1;
        $stmt = $conn->prepare("UPDATE class SET class_capacity = ? WHERE class_id = ?");
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $conn->error);
        }
        $stmt->bind_param("ii", $new_capacity, $class_id);
        if (!$stmt->execute()) {
            throw new Exception("Execute statement failed: " . $stmt->error);
        }
        $stmt->close();

        // Commit the transaction
        $conn->commit();
        return ['status' => 'success', 'message' => 'Class booked successfully!'];

    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        error_log("Error booking class: " . $e->getMessage());
        return ['status' => 'error', 'message' => 'Failed to book class: ' . $e->getMessage()];
    }
}
?>
