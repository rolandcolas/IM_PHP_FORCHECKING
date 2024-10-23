<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_SESSION['student_id'])) {
        $student_id = $_SESSION['student_id'];
        $reservation_name = $_POST['reservation_name'];
        $reservation_start_datetime = $_POST['reservation_start_datetime'];
        $reservation_end_datetime = $_POST['reservation_end_datetime'];

        // Get the current date and time
        $current_datetime = date('Y-m-d H:i:s');

        // Check if the reservation start date is in the past
        if ($reservation_start_datetime < $current_datetime) {
            // If the start date is in the past, show an alert
            echo "<script>alert('Error: Reservation cannot be in the past. Please choose a future date and time.');</script>";
        } else {
            // Check for overlapping reservations
            $stmt = $conn->prepare("
                SELECT * FROM prereservation 
                WHERE (reservation_datetime < ? AND reservation_end_datetime > ?)
                AND status = 'accepted'
            ");
            $stmt->bind_param("ss", $reservation_end_datetime, $reservation_start_datetime);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // If there are overlapping reservations, display an error
                echo "<script>alert('Error: Overlapping reservation exists.');</script>";
            } else {
                // No overlap, proceed with inserting the new reservation
                $stmt = $conn->prepare("INSERT INTO prereservation (student_id, reservation_name, reservation_datetime, reservation_end_datetime, status) VALUES (?, ?, ?, ?, 'pending')");
                $stmt->bind_param("isss", $student_id, $reservation_name, $reservation_start_datetime, $reservation_end_datetime);

                if ($stmt->execute()) {
                    // Redirect to home page upon successful reservation
                    header("Location: home.php");
                    exit();
                } else {
                    echo "Error: " . $stmt->error;
                }
            }

            $stmt->close();
        }

        $conn->close();
    } else {
        echo "Error: Student ID not found in session.";
    }
}
?>
