<?php
/**
 * Fetches all necessary data for the student dashboard.
 * This includes student details, booked classes, and available classes.
 *
 * @param mysqli $conn The database connection object.
 * @param string $stud_id The ID of the logged-in student.
 * @return array An associative array containing dashboard data.
 */
function fetchDashboardData($conn, $stud_id) {
    $dashboardData = [
        'student_info' => [],
        'booked_classes' => [],
        'available_classes' => []
    ];

    // --- Fetch Student Information ---
    $stmt = $conn->prepare("SELECT stud_name, stud_prog, stud_sem, stud_telno, stud_email FROM student WHERE stud_id = ?");
    if ($stmt) {
        $stmt->bind_param("s", $stud_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $dashboardData['student_info'] = $result->fetch_assoc();
        }
        $stmt->close();
    } else {
        error_log("Error preparing student info statement: " . $conn->error);
    }

    // --- Fetch Booked Classes for the Student ---
    $sql_booked_classes = "
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
    $stmt = $conn->prepare($sql_booked_classes);
    if ($stmt) {
        $stmt->bind_param("s", $stud_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $dashboardData['booked_classes'][] = $row;
        }
        $stmt->close();
    } else {
        error_log("Error preparing booked classes statement: " . $conn->error);
    }

    // --- Fetch Available Classes (not yet booked by the student) ---
    $sql_available_classes = "
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
    $stmt = $conn->prepare($sql_available_classes);
    if ($stmt) {
        $stmt->bind_param("s", $stud_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $dashboardData['available_classes'][] = $row;
        }
        $stmt->close();
    } else {
        error_log("Error preparing available classes statement: " . $conn->error);
    }

    return $dashboardData;
}
?>
