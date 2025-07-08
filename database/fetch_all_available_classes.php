<?php
// fetch_all_available_classes.php

// This file is included by student.php, so $conn and $stud_id are already available.

/**
 * Fetches all available classes that the given student has not yet booked.
 *
 * @param mysqli $conn The database connection object.
 * @param string $stud_id The ID of the logged-in student.
 * @return array An associative array containing all available classes.
 */
function fetchAllAvailableClasses($conn, $stud_id) {
    $availableClasses = [];

    // Select classes that are not in the booking table for the current student
    // and are in the future or today.
    // Also include tutor details and average tutor rating.
    $sql_all_available_classes = "
        SELECT
            cl.class_id,
            cl.class_title,
            cl.class_description,
            cl.class_date,
            cl.class_starttime,
            cl.class_endtime,
            cl.class_capacity,
            cl.class_fee,
            tu.tutor_id,
            tu.tutor_name,
            (SELECT AVG(review.tutor_rating) FROM review WHERE review.tutor_id = tu.tutor_id) AS avg_tutor_rating
        FROM
            class cl
        JOIN
            tutor tu ON cl.tutor_id = tu.tutor_id
        WHERE
            cl.class_id NOT IN (SELECT class_id FROM booking WHERE stud_id = ?)
            AND cl.class_date >= CURDATE() -- Only show future classes
        ORDER BY
            cl.class_date ASC, cl.class_starttime ASC
    ";
    $stmt = $conn->prepare($sql_all_available_classes);
    if ($stmt) {
        $stmt->bind_param("s", $stud_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $availableClasses[] = $row;
        }
        $stmt->close();
    } else {
        error_log("Error preparing all available classes statement: " . $conn->error);
    }

    return $availableClasses;
}
?>
