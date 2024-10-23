<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $student_id = $_POST['student_id'];
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $yearlevel = $_POST['yearlevel'];

    // Check for existing email or student_id
    $check_sql = "SELECT * FROM students WHERE email = ? OR student_id = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("ss", $email, $student_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "Error: Email or Student ID already exists.";
    } else {
        // Use prepared statements to avoid SQL injection
        $insert_sql = "INSERT INTO students (email, password, student_id, firstname, lastname, yearlevel) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_sql);
        $stmt->bind_param("ssssss", $email, $password, $student_id, $firstname, $lastname, $yearlevel);

        if ($stmt->execute()) {
            // Redirect to login page upon successful registration
            header("Location: index.php");
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }
    }

    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="css/register.css">
    <title>Register</title>
</head>
<body>
    <div class="container">
        <img src="images/itslogo.png" alt="Logo" class="logo"> <!-- Added Logo Image -->
        <h2>Register</h2>
        <form method="post" action="register.php">
            <div class="row"> <!-- Email and Password in one row -->
                <div class="col">
                    <label for="email">Email:</label><br>
                    <input type="email" id="email" name="email" required placeholder="Enter your email"><br>
                </div>
                <div class="col">
                    <label for="password">Password:</label><br>
                    <input type="password" id="password" name="password" required placeholder="Enter your password"><br>
                </div>
            </div>
            <div class="row"> <!-- Last Name and First Name in one row -->
                <div class="col">
                    <label for="lastname">Last Name:</label><br>
                    <input type="text" id="lastname" name="lastname" required placeholder="Enter your last name"><br>
                </div>
                <div class="col">
                    <label for="firstname">First Name:</label><br>
                    <input type="text" id="firstname" name="firstname" required placeholder="Enter your first name"><br>
                </div>
            </div>
            <div class="row"> <!-- Student ID and Year Level in one row -->
                <div class="col">
                    <label for="student_id">Student ID:</label><br>
                    <input type="text" id="student_id" name="student_id" required placeholder="Enter your student ID"><br>
                </div>
                <div class="col">
                    <label for="yearlevel">Year Level:</label><br>
                    <input type="text" id="yearlevel" name="yearlevel" required placeholder="Enter your year level"><br>
                </div>
            </div>
            <div class="form-group">
                <input type="submit" value="Register" class="btn btn-primary">
            </div>
            <p>Already have an account? <a href="index.php">Login here</a></p>
        </form>
    </div>
</body>
</html>
