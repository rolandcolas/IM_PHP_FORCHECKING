<?php
session_start();

// Check if the user is an admin
if (!isset($_SESSION['isAdmin']) || !$_SESSION['isAdmin']) {
    header("Location: home.php"); // Redirect if not an admin
    exit();
}

include 'db.php';

date_default_timezone_set('Asia/Manila'); // Set your timezone

// Function to handle database errors
function handleDbError($stmt) {
    if ($stmt->error) {
        die("Database error: " . $stmt->error);
    }
}

// Handle adding new item
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_item'])) {
    $itemtype = $_POST['itemtype'];
    $itemname = $_POST['itemname'];

    // Insert new item into the database
    $stmt = $conn->prepare("INSERT INTO items (itemtype, itemname) VALUES (?, ?)");
    $stmt->bind_param("ss", $itemtype, $itemname);
    $stmt->execute();
    handleDbError($stmt);
    $stmt->close();
}

// Handle removing item
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['remove_item'])) {
    $item_id = $_POST['item_id'];

    // Delete the item from the database
    $stmt = $conn->prepare("DELETE FROM items WHERE item_id = ?");
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    handleDbError($stmt);
    $stmt->close();
}

// Handle reservation actions (accept, reject, cancel)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $reservation_id = $_POST['reservation_id'];
    $action = $_POST['action'];

    // Update the status based on the action
    $status = ($action == 'accept') ? 'accepted' : (($action == 'reject') ? 'rejected' : 'pending');

    // Update reservation status in the database
    $stmt = $conn->prepare("UPDATE prereservation SET status = ? WHERE reservation_id = ?");
    $stmt->bind_param("si", $status, $reservation_id);
    $stmt->execute();
    handleDbError($stmt);
    $stmt->close();
}

// Handle borrowing actions (accept, reject)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['borrow_action'])) {
    $borrowing_id = $_POST['borrowing_id'];
    $action = $_POST['borrow_action'];

    // Update the status based on the action
    $status = ($action == 'accept') ? 'approved' : 'rejected';

    // Update borrowing status in the database
    $stmt = $conn->prepare("UPDATE borrowing SET status = ? WHERE borrowing_id = ?");
    $stmt->bind_param("si", $status, $borrowing_id);
    $stmt->execute();
    handleDbError($stmt);
    $stmt->close();
}

// Handle adding a sanction
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['violation'], $_POST['sanction'], $_POST['reservation_id'])) {
    // Fetch the student_id from the reservation
    $stmt = $conn->prepare("SELECT student_id FROM prereservation WHERE reservation_id = ?");
    $stmt->bind_param("i", $_POST['reservation_id']);
    $stmt->execute();
    handleDbError($stmt);
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $student_id = $row['student_id'];
    $stmt->close();

    // Insert the sanction into the database
    $violation = $_POST['violation'];
    $sanction = $_POST['sanction'];

    $stmt = $conn->prepare("INSERT INTO sanctions (student_id, violation, sanction) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $student_id, $violation, $sanction);
    $stmt->execute();
    handleDbError($stmt);
    $stmt->close();

    // Set a success message
    $_SESSION['message'] = "Sanction added successfully.";
}

// Fetch all items from the database
$stmt = $conn->prepare("SELECT * FROM items");
$stmt->execute();
handleDbError($stmt);
$items_result = $stmt->get_result();

// Fetch borrowing requests
$stmt = $conn->prepare("SELECT * FROM borrowing");
$stmt->execute();
handleDbError($stmt);
$borrowing_result = $stmt->get_result();

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
    <link rel="stylesheet" type="text/css" href="css/AdminNavbar.css">
    <title>Admin</title>
    <style>
        /* Modal Styling */
        .modal {
            display: none;
            position: fixed;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            width: 400px;
        }
        .close {
            float: right;
            cursor: pointer;
        }
    </style>
</head>
<body>

<?php include 'AdminNavbar.php'; ?> <!-- Include the Admin Navbar -->

<h1>Admin Page</h1>

<!-- Display all items -->
<h2>Items List</h2>
<ul>
    <?php while ($row = $items_result->fetch_assoc()): ?>
        <li>
            <?php echo htmlspecialchars("{$row['itemname']} ({$row['itemtype']})"); ?>
            <form method="post" style="display:inline;">
                <input type="hidden" name="item_id" value="<?php echo htmlspecialchars($row['item_id']); ?>">
                <button type="submit" name="remove_item">Remove</button>
            </form>
        </li>
    <?php endwhile; ?>
</ul>

<!-- Add new item form -->
<h2>Add New Item</h2>
<form method="post">
    <label for="itemtype">Item Type:</label>
    <select id="itemtype" name="itemtype" required>
        <option value="item">Item</option>
        <option value="book">Book</option>
    </select><br>

    <label for="itemname">Item Name:</label>
    <input type="text" id="itemname" name="itemname" required><br>

    <button type="submit" name="add_item">Add Item</button>
</form>

<h2>Pending Reservations</h2>
<ul>
    <?php
    $stmt = $conn->prepare("SELECT * FROM prereservation WHERE status = 'pending'");
    $stmt->execute();
    handleDbError($stmt);
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        echo "<li>" . htmlspecialchars($row['reservation_name']) . " from " . htmlspecialchars($row['reservation_datetime']) . " to " . htmlspecialchars($row['reservation_end_datetime']) . " - ";
        echo "<form method='post' style='display:inline;'>
                <input type='hidden' name='reservation_id' value='" . htmlspecialchars($row['reservation_id']) . "'>
                <button type='submit' name='action' value='accept'>Accept</button>
                <button type='submit' name='action' value='reject'>Reject</button>
              </form>";
        echo "</li>";
    }
    $stmt->close();
    ?>
</ul>

<h2>Accepted Reservations</h2>
<ul>
    <?php
    $stmt = $conn->prepare("SELECT * FROM prereservation WHERE status = 'accepted'");
    $stmt->execute();
    handleDbError($stmt);
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        echo "<li>" . htmlspecialchars($row['reservation_name']) . " from " . htmlspecialchars($row['reservation_datetime']) . " to " . htmlspecialchars($row['reservation_end_datetime']) . " - ";
        echo "<form method='post' style='display:inline;'>
                <input type='hidden' name='reservation_id' value='" . htmlspecialchars($row['reservation_id']) . "'>
                <button type='submit' name='action' value='cancel'>Cancel</button>
              </form>";
        
        // Add Sanction Button
        echo "<button class='sanction-btn' data-reservation-id='" . htmlspecialchars($row['reservation_id']) . "'>Add Sanction</button>";
        echo "</li>";
    }
    $stmt->close();
    ?>
</ul>

<!-- Modal for Adding Sanction -->
<div id="sanctionModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Add Sanction</h2>
        <form id="sanctionForm" method="post">
            <input type="hidden" id="reservation_id" name="reservation_id">
            
            <label for="violation">Violation:</label><br>
            <input type="text" id="violation" name="violation" required><br><br>

            <label for="sanction">Sanction:</label><br>
            <input type="text" id="sanction" name="sanction" required><br><br>

            <button type="submit">Submit</button>
        </form>
    </div>
</div>

<script>
// Get the modal and close button
const modal = document.getElementById('sanctionModal');
const closeModal = document.querySelector('.close');

// Show modal when a "Sanction" button is clicked
document.querySelectorAll('.sanction-btn').forEach(button => {
    button.addEventListener('click', function() {
        const reservationId = this.getAttribute('data-reservation-id');
        document.getElementById('reservation_id').value = reservationId;
        modal.style.display = 'flex';
    });
});

// Close the modal when the 'X' is clicked
closeModal.addEventListener('click', function() {
    modal.style.display = 'none';
});

// Close the modal when clicked outside
window.onclick = function(event) {
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}
</script>

<h2>Pending Borrowings</h2>
<ul>
    <?php
    $stmt = $conn->prepare("SELECT * FROM borrowing WHERE status = 'pending'");
    $stmt->execute();
    handleDbError($stmt);
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        echo "<li>Item ID: " . htmlspecialchars($row['item_id']) . " by Student ID: " . htmlspecialchars($row['student_id']) . " from " . htmlspecialchars($row['borrow_datetime']) . " to " . htmlspecialchars($row['end_borrow_datetime']) . " - ";
        echo "<form method='post' style='display:inline;'>
                <input type='hidden' name='borrowing_id' value='" . htmlspecialchars($row['borrowing_id']) . "'>
                <button type='submit' name='borrow_action' value='accept'>Accept</button>
                <button type='submit' name='borrow_action' value='reject'>Reject</button>
              </form>";
        echo "</li>";
    }
    $stmt->close();
    ?>
</ul>

<h2>Accepted Borrowings</h2>
<ul>
    <?php
    $stmt = $conn->prepare("SELECT * FROM borrowing WHERE status = 'approved'");
    $stmt->execute();
    handleDbError($stmt);
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        echo "<li>Item ID: " . htmlspecialchars($row['item_id']) . " by Student ID: " . htmlspecialchars($row['student_id']) . " from " . htmlspecialchars($row['borrow_datetime']) . " to " . htmlspecialchars($row['end_borrow_datetime']) . " - ";
        echo "<form method='post' style='display:inline;'>
                <input type='hidden' name='borrowing_id' value='" . htmlspecialchars($row['borrowing_id']) . "'>
                <button type='submit' name='borrow_action' value='reject'>Reject</button>
              </form>";
        echo "</li>";
    }
    $stmt->close();
    ?>
</ul>

<h2>Rejected Borrowings</h2>
<ul>
    <?php
    $stmt = $conn->prepare("SELECT * FROM borrowing WHERE status = 'rejected'");
    $stmt->execute();
    handleDbError($stmt);
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        echo "<li>Item ID: " . htmlspecialchars($row['item_id']) . " by Student ID: " . htmlspecialchars($row['student_id']) . " from " . htmlspecialchars($row['borrow_datetime']) . " to " . htmlspecialchars($row['end_borrow_datetime']) . " - ";
        echo "<form method='post' style='display:inline;'>
                <input type='hidden' name='borrowing_id' value='" . htmlspecialchars($row['borrowing_id']) . "'>
                <button type='submit' name='borrow_action' value='accept'>Accept</button>
              </form>";
        echo "</li>";
    }
    $stmt->close();
    ?>
</ul>
</body>
</html>

<?php
$conn->close();
?>