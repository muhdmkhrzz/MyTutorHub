<?php
/**
 * Fetches all booked classes for a given student.
 *
 * @param mysqli $conn The database connection object.
 * @param string $stud_id The ID of the logged-in student.
 * @return array An associative array containing all booked classes.
 */
function fetchAllBookings($conn, $stud_id) {
    $bookedClasses = [];

    // Join booking, class, and tutor tables to get comprehensive details
    $sql_all_booked_classes = "
        SELECT
            b.booking_id,
            b.booking_date,
            b.total_payment,
            c.class_id,
            c.class_title,
            c.class_description,
            c.class_date,
            c.class_starttime,
            c.class_endtime,
            c.class_fee,
            t.tutor_id,
            t.tutor_name,
            (SELECT AVG(review.class_rating) FROM review WHERE review.class_id = c.class_id) AS avg_class_rating,
            (SELECT AVG(review.tutor_rating) FROM review WHERE review.tutor_id = t.tutor_id) AS avg_tutor_rating
        FROM
            booking b
        JOIN
            class c ON b.class_id = c.class_id
        JOIN
            tutor t ON c.tutor_id = t.tutor_id
        WHERE
            b.stud_id = ?
        ORDER BY
            c.class_date DESC, c.class_starttime DESC
    ";
    $stmt = $conn->prepare($sql_all_booked_classes);
    if ($stmt) {
        $stmt->bind_param("s", $stud_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $bookedClasses[] = $row;
        }
        $stmt->close();
    } else {
        error_log("Error preparing all booked classes statement: " . $conn->error);
    }

    return $bookedClasses;
}
?>
