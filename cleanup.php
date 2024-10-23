<?php
include 'db.php';

date_default_timezone_set('Asia/Manila');  // Set your timezone
$current_datetime = date('Y-m-d H:i:s');

// Update past reservations to 'rejected'
$stmt = $conn->prepare("UPDATE prereservation SET status = 'rejected' WHERE reservation_datetime <  ? AND status = 'pending'");
$stmt->bind_param("s", $current_datetime);
$stmt->execute();
$stmt->close();

echo "Cleanup completed at " . $current_datetime;
?>
