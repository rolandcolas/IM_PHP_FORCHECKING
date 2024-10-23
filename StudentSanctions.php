<?php
session_start();
include 'db.php';

// Check if the user is a student
if (!isset($_SESSION['student_id'])) {
    header("Location: home.php"); // Redirect if not a student
    exit();
}

// Fetch sanctions related to the current student
$student_id = $_SESSION['student_id'];

$stmt = $conn->prepare("
    SELECT s.violation, s.sanction, r.reservation_name, r.reservation_datetime, r.reservation_end_datetime
    FROM sanctions s
    JOIN prereservation r ON s.student_id = r.student_id
    WHERE s.student_id = ? AND r.status = 'accepted'
");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

// Check for success message for sanctions
if (isset($_SESSION['message'])) {
    echo "<script>alert('" . htmlspecialchars($_SESSION['message']) . "');</script>";
    unset($_SESSION['message']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <link rel="stylesheet" type="text/css" href="css/navbar.css">
    <link rel="stylesheet" type="text/css" href="css/modal.css"> <!-- Make sure modal styles are included -->
    <title>My Sanctions</title>
</head>
<body>

<?php include 'navbar.php'; ?> <!-- Include the Student Navbar -->

<h1>My Sanctions</h1>

<?php if ($result->num_rows > 0): ?>
    <h2>List of Sanctions</h2>
    <table>
        <thead>
            <tr>
                <th>Reservation</th>
                <th>From</th>
                <th>To</th>
                <th>Violation</th>
                <th>Sanction</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['reservation_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['reservation_datetime']); ?></td>
                    <td><?php echo htmlspecialchars($row['reservation_end_datetime']); ?></td>
                    <td><?php echo htmlspecialchars($row['violation']); ?></td>
                    <td><?php echo htmlspecialchars($row['sanction']); ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>You have no sanctions at the moment.</p>
<?php endif; ?>

<!-- Reservation Modal -->
<div id="myModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2>Make a Reservation</h2>
        <form method="post" action="reservation.php">
            <div class="form-group">
                <label for="reservation_name">Reservation Name:</label><br>
                <input type="text" id="reservation_name" name="reservation_name" required><br>
            </div>
            <div class="form-group">
                <label for="reservation_start_datetime">Reservation Start Date and Time:</label><br>
                <input type="datetime-local" id="reservation_start_datetime" name="reservation_start_datetime" required><br>
            </div>
            <div class="form-group">
                <label for="reservation_end_datetime">Reservation End Date and Time:</label><br>
                <input type="datetime-local" id="reservation_end_datetime" name="reservation_end_datetime" required><br>
            </div>
            <input type="submit" value="Reserve" class="btn btn-primary">
        </form>
    </div>
</div>

<!-- Borrow Modal -->
<div id="borrowModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeBorrowModal()">&times;</span>
        <h2>Borrow Item</h2>
        <form method="post" action="borrow.php">
            <div class="form-group">
                <label for="item_id">Select Item:</label><br>
                <select id="item_id" name="item_id" required>
                    <?php
                    // Fetch available items from the database
                    $stmt = $conn->prepare("SELECT * FROM items");
                    $stmt->execute();
                    $result = $stmt->get_result();
                    while ($row = $result->fetch_assoc()) {
                        echo "<option value='{$row['item_id']}'>{$row['itemname']} ({$row['itemtype']})</option>";
                    }
                    $stmt->close();
                    ?>
                </select><br>
            </div>
            <div class="form-group">
                <label for="borrow_start_datetime">Borrow Start Date and Time:</label><br>
                <input type="datetime-local" id="borrow_start_datetime" name="borrow_start_datetime" required><br>
            </div>
            <div class="form-group">
                <label for="borrow_end_datetime">Borrow End Date and Time:</label><br>
                <input type="datetime-local" id="borrow_end_datetime" name="borrow_end_datetime" required><br>
            </div>
            <input type="submit" value="Borrow" class="btn btn-primary">
        </form>
    </div>
</div>

<!-- Admin Modal -->
<div id="adminModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeAdminModal()">&times;</span>
        <h2>Admin Access</h2>
        <form method="post" action="verify_admin.php">
            <div class="form-group">
                <label for="admin_passkey">Enter Passkey</label><br>
                <input type="password" id="admin_passkey" name="admin_passkey" required><br>
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>
    </div>
</div>

<script>
    // Modal handling for reservations
    var reservationModal = document.getElementById("myModal");
    var borrowModal = document.getElementById("borrowModal");
    var adminModal = document.getElementById("adminModal");

    // Show reservation modal
    function showReservationModal() {
        reservationModal.style.display = "block";
    }

    // Close reservation modal
    function closeModal() {
        reservationModal.style.display = "none";
    }

    // Show borrow modal
    function showBorrowModal() {
        borrowModal.style.display = "block";
    }

    // Close borrow modal
    function closeBorrowModal() {
        borrowModal.style.display = "none";
    }

    // Show admin modal
    function showAdminModal() {
        adminModal.style.display = "block";
    }

    // Close admin modal
    function closeAdminModal() {
        adminModal.style.display = "none";
    }

    // Close modals when clicking outside
    window.onclick = function(event) {
        if (event.target == reservationModal) {
            reservationModal.style.display = "none";
        } else if (event.target == borrowModal) {
            borrowModal.style.display = "none";
        } else if (event.target == adminModal) {
            adminModal.style.display = "none";
        }
    }
</script>

</body>
</html>
