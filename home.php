<?php
session_start();
include 'db.php';
if (!isset($_SESSION['student_id'])) {
    header("Location: index.php");
    exit();
}

$student_id = $_SESSION['student_id'];
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <link rel="stylesheet" type="text/css" href="css/navbar.css">
    <link rel="stylesheet" type="text/css" href="css/modal.css">
    <title>Home</title>
</head>
<body>
    <?php include 'navbar.php'; ?>
    
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

    <!-- Reservation Display -->
    <h2>Your Reservations</h2>

    <!-- Display Pending Reservations -->
    <h3>Pending Reservations</h3>
    <ul>
        <?php
        $stmt = $conn->prepare("SELECT * FROM prereservation WHERE student_id = ? AND status = 'pending'");
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            echo "<li>{$row['reservation_name']} on {$row['reservation_datetime']} - Status: {$row['status']}</li>";
        }
        $stmt->close();
        ?>
    </ul>

    <!-- Display Accepted Reservations -->
    <h3>Accepted Reservations</h3>
    <ul>
        <?php
        $stmt = $conn->prepare("SELECT * FROM prereservation WHERE student_id = ? AND status = 'accepted'");
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            echo "<li>{$row['reservation_name']} on {$row['reservation_datetime']} - Status: {$row['status']}</li>";
        }
        $stmt->close();
        ?>
    </ul>

    <!-- Display Rejected Reservations -->
    <h3>Rejected Reservations</h3>
    <ul>
        <?php
        $stmt = $conn->prepare("SELECT * FROM prereservation WHERE student_id = ? AND status = 'rejected'");
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            echo "<li>{$row['reservation_name']} on {$row['reservation_datetime']} - Status: {$row['status']}</li>";
        }
        $stmt->close();
        ?>
    </ul>

    <!-- Borrowing Display -->
    <h2>Your Borrowings</h2>

    <!-- Display Pending Borrowings -->
    <h3>Pending Borrowings</h3>
    <ul>
        <?php
        $stmt = $conn->prepare("SELECT * FROM borrowing WHERE student_id = ? AND status = 'pending'");
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            echo "<li>Item ID: {$row['item_id']} - Borrow Start: {$row['borrow_datetime']} - Status: {$row['status']}</li>";
        }
        $stmt->close();
        ?>
    </ul>

    <!-- Display Accepted Borrowings -->
    <h3>Accepted Borrowings</h3>
    <ul>
        <?php
        $stmt = $conn->prepare("SELECT * FROM borrowing WHERE student_id = ? AND status = 'approved'");
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            echo "<li>Item ID: {$row['item_id']} - Borrow Start: {$row['borrow_datetime']} - Status: {$row['status']}</li>";
        }
        $stmt->close();
        ?>
    </ul>

    <!-- Display Rejected Borrowings -->
    <h3>Rejected Borrowings</h3>
    <ul>
        <?php
        $stmt = $conn->prepare("SELECT * FROM borrowing WHERE student_id = ? AND status = 'rejected'");
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            echo "<li>Item ID: {$row['item_id']} - Borrow Start: {$row['borrow_datetime']} - Status: {$row['status']}</li>";
        }
        $stmt->close();
        ?>
    </ul>

    <script>
        var reservationModal = document.getElementById("myModal");
        var borrowModal = document.getElementById("borrowModal");
        var reservationBtn = document.getElementById("openModalNav");
        var reservationSpan = document.getElementsByClassName("close")[0];

        reservationBtn.onclick = function() {
            reservationModal.style.display = "block";
        }

        reservationSpan.onclick = function() {
            reservationModal.style.display = "none";
        }

        function showBorrowModal() {
            borrowModal.style.display = "block";
        }

        function closeBorrowModal() {
            borrowModal.style.display = "none";
        }

        window.onclick = function(event) {
            if (event.target == reservationModal) {
                reservationModal.style.display = "none";
            } else if (event.target == borrowModal) {
                borrowModal.style.display = "none";
            }
        }

        function showAdminModal() {
            document.getElementById("adminModal").style.display = "block";
        }

        function closeAdminModal() {
            document.getElementById("adminModal").style.display = "none";
        }

        window.onclick = function(event) {
            var modal = document.getElementById("adminModal");
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>
</body>
</html>