<?php
session_start();
include 'db.php';

// Check if the user is an admin
if (!isset($_SESSION['isAdmin']) || !$_SESSION['isAdmin']) {
    header("Location: home.php"); // Redirect if not an admin
    exit();
}

// Handle canceling a sanction
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cancel_sanction'])) {
    $sanction_id = $_POST['sanction_id'];

    // Delete the sanction from the database
    $stmt = $conn->prepare("DELETE FROM sanctions WHERE sanction_id = ?");
    $stmt->bind_param("i", $sanction_id);
    $stmt->execute();
    if ($stmt->error) {
        die("Database error: " . $stmt->error);
    }
    $stmt->close();

    // Set a success message
    $_SESSION['message'] = "Sanction canceled successfully.";
}

// Fetch all sanctions from the database
$stmt = $conn->prepare("SELECT * FROM sanctions");
$stmt->execute();
if ($stmt->error) {
    die("Database error: " . $stmt->error);
}
$sanctions_result = $stmt->get_result();

// Check for success message for sanctions
if (isset($_SESSION['message'])) {
    echo "<script>alert('" . htmlspecialchars($_SESSION['message']) . "');</script>";
    unset($_SESSION['message']);
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <link rel="stylesheet" type="text/css" href="css/navbar.css">
    <title>Sanctions Management</title>
</head>
<body>

<?php include 'AdminNavbar.php'; ?> <!-- Include the Admin Navbar -->

<h1>Sanctions Management</h1>

<h2>List of Sanctions</h2>
<table>
    <thead>
        <tr>
            <th>Student ID</th>
            <th>Violation</th>
            <th>Sanction</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $sanctions_result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['student_id']); ?></td>
                <td><?php echo htmlspecialchars($row['violation']); ?></td>
                <td><?php echo htmlspecialchars($row['sanction']); ?></td>
                <td>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="sanction_id" value="<?php echo htmlspecialchars($row['sanction_id']); ?>">
                        <button type="submit" name="cancel_sanction">Cancel Sanction</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

</body>
</html>

<?php
$conn->close();
?>
