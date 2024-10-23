<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $sql = "SELECT * FROM students WHERE email = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            // Set student_id in session
            $_SESSION['student_id'] = $row['student_id'];
            // Redirect to home page upon successful login
            header("Location: home.php");
            exit();
        } else {
            echo "Invalid credentials!";
        }
    } else {
        echo "No user found with this email!";
    }

    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="./css/login.css"> <!-- Link to your CSS file -->
</head>
<body>
    <div class="container">
        <img src="./images/itslogo.png" alt="Logo" class="logo" /> <!-- Adjust path if necessary -->
        <h2>Login</h2>
        <form method="post" action="index.php">
            <div class="mb-3">
                <label for="email">Email</label>
                <input type="email" placeholder="Enter Email" name="email" id="email" required />
                <span class="text-danger" id="emailError"></span>
            </div>
            <div class="mb-3">
                <label for="password">Password</label>
                <input type="password" placeholder="Enter Password" name="password" id="password" required />
                <span class="text-danger" id="passwordError"></span>
            </div>
            <button type="submit" class="btn btn-primary">Log In</button>
            <p>Create an Account: <a href="register.php">Register here</a></p>
        </form>
    </div> 
</body>
</html>
