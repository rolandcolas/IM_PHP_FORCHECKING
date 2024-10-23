<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $admin_passkey = $_POST['admin_passkey'];

    if ($admin_passkey === 'admin123') {
        $_SESSION['isAdmin'] = true;
        header("Location: admin.php");
        exit();
    } else {
        echo "<script>alert('Incorrect passkey');</script>";
        echo "<script>window.location.href = 'home.php';</script>"; // Adjust as needed
    }
}
?>
