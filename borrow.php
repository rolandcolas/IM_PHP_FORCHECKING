<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_SESSION['student_id'])) {
        $student_id = $_SESSION['student_id'];
        $item_id = $_POST['item_id'];
        $borrow_datetime = $_POST['borrow_start_datetime'];
        $end_borrow_datetime = $_POST['borrow_end_datetime'];

        // Check for overlapping borrowings that are accepted only
        $stmt = $conn->prepare("
            SELECT * FROM borrowing 
            WHERE (end_borrow_datetime > ? AND borrow_datetime < ?)
            AND item_id = ?
            AND status = 'accepted'  -- Only check for accepted borrowings
        ");
        $stmt->bind_param("ssi", $borrow_datetime, $end_borrow_datetime, $item_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // If there are overlapping borrowings, display an error
            echo "<script>alert('Error: Overlapping borrowing exists.');</script>";
        } else {
            // No overlap, proceed with inserting the new borrowing
            $stmt = $conn->prepare("INSERT INTO borrowing (item_id, student_id, borrow_datetime, end_borrow_datetime, status) VALUES (?, ?, ?, ?, 'pending')");
            $stmt->bind_param("iiss", $item_id, $student_id, $borrow_datetime, $end_borrow_datetime);

            if ($stmt->execute()) {
                // Redirect to home page upon successful borrowing
                header("Location: home.php");
                exit();
            } else {
                echo "Error: " . $stmt->error;
            }
        }

        $stmt->close();
        $conn->close();
    } else {
        echo "Error: Student ID not found in session.";
    }
}
?>
