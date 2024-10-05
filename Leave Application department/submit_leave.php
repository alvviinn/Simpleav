<?php
// public/submit_leave.php
session_start();

// Check if user is logged in
// if (!isset($_SESSION['username'])) {
//     header("Location: login.php");
//     exit();
// }

require_once '../database/db_connect.php';

// Initialize variables
$error = "";
$success = "";

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve and sanitize input
    $employee_id = intval($_POST['employee_id']);
    $leave_type = trim($_POST['leave_type']);
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    // Basic validation
    if (empty($leave_type) || empty($start_date) || empty($end_date)) {
        $error = "All fields are required.";
    } elseif ($start_date > $end_date) {
        $error = "Start date cannot be after end date.";
    } else {
        // Insert into database using prepared statements
        $stmt = $conn->prepare("INSERT INTO leave_applications (employee_id, leave_type, start_date, end_date) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $employee_id, $leave_type, $start_date, $end_date);

        if ($stmt->execute()) {
            $success = "Leave application submitted successfully.";
        } else {
            $error = "Error: " . $stmt->error;
        }

        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Submit Leave - Leave Management System</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container">
        <h2>Submit Leave Application</h2>
        <?php if ($error): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <?php if ($success): ?>
            <p class="success"><?php echo htmlspecialchars($success); ?></p>
        <?php endif; ?>
        <nav>
            <a href="leave_application.php">Apply Again</a> |
            <a href="profile.php">Back to Profile</a>
        </nav>
    </div>
</body>
</html>
