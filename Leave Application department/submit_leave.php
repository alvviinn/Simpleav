<?php
// submit_leave.php

// Enable error reporting for debugging (disable in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start the session
session_start();

// Include the database connection
require_once '../DATABASE/db_connect.php';

/* 
    // The following section is commented out because login/registration is handled by another team.
    // Once available, you can uncomment this section to ensure only authenticated users can submit leave applications.

    // Check if the user is logged in
    if (!isset($_SESSION['user_id'])) {
        // Redirect to login page or show an error
        header("Location: login.php");
        exit();
    }
*/

// For now, we'll simulate a logged-in user.
// Remove the following lines once login is integrated.
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1; // Example user_id (ensure this exists in tbl_user)
    $_SESSION['username'] = "John Doe"; // Example username
    $_SESSION['department'] = "Engineering"; // Example department
}

// Initialize variables
$error = "";
$success = "";

// Check if the form is submitted via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize form inputs
    $leave_type = trim($_POST['leave_type']);
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $reason = trim($_POST['reason']);
    $user_id = intval($_SESSION['user_id']);

    // Basic validation
    if (empty($leave_type) || empty($start_date) || empty($end_date) || empty($reason)) {
        $error = "All fields are required.";
    } elseif ($start_date > $end_date) {
        $error = "Start date cannot be after end date.";
    } else {
        // Calculate the number of leave days (inclusive)
        $start = new DateTime($start_date);
        $end = new DateTime($end_date);
        $interval = $start->diff($end);
        $leave_days = $interval->days + 1; // +1 to include both start and end dates

        // Optional: Check if leave dates overlap with existing approved leaves for the employee
        
        $check_stmt = $conn->prepare("SELECT * FROM tbl_leave WHERE user_id = ? AND current_status = 1 AND (
            (start_date <= ? AND end_date >= ?) OR
            (start_date <= ? AND end_date >= ?) OR
            (start_date >= ? AND end_date <= ?)
        )");
        $check_stmt->bind_param("issssss", $user_id, $start_date, $start_date, $end_date, $end_date, $start_date, $end_date);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {
            $error = "You have overlapping approved leave dates.";
        } else {
            // Proceed to insert
        }

        $check_stmt->close();
        

        if (empty($error)) {
            // Prepare the SQL statement to prevent SQL injection
            $stmt = $conn->prepare("INSERT INTO tbl_leave (user_id, date_requested, leave_type, used_leave, days_remaining, current_status, start_date, end_date, approved_by, approval_timestamp, comments) VALUES (?, NOW(), ?, ?, ?, 0, ?, ?, NULL, NULL, NULL)");
            if ($stmt === false) {
                $error = "Prepare failed: (" . $conn->errno . ") " . $conn->error;
            } else {
                // Assume maximum leave days per type (for days_remaining calculation)
                $max_leave_days = [
                    "Sick Leave" => 14,
                    "Casual Leave" => 7,
                    "Annual Leave" => 21,
                    "Maternity Leave" => 90,
                    "Paternity Leave" => 14,
                    "Bereavement Leave" => 5
                ];

                // Calculate days_remaining based on maximum allowed
                $current_leave_type = $leave_type;
                $max_days = isset($max_leave_days[$current_leave_type]) ? $max_leave_days[$current_leave_type] : 0;
                $days_remaining = $max_days - $leave_days;

                // Bind parameters
                $stmt->bind_param("isiiiss",
                    $user_id,
                    $leave_type,
                    $leave_days,
                    $days_remaining,
                    $start_date,
                    $end_date
                );

                // Execute the statement
                if ($stmt->execute()) {
                    $success = "Leave application submitted successfully.";
                } else {
                    $error = "Error: " . $stmt->error;
                }

                // Close the statement
                $stmt->close();
            }
        }
    }
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Leave Application Submission</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <header>
        <div class="navbar">
            <div class="logo">SIMPLEAV</div>
            <nav>
                <ul>
                    <li><a href="#">Home</a></li>
                    <li><a href="#">Leave Requests</a></li>
                    <li><a href="#">Help</a></li>
                </ul>
            </nav>
            <div class="profile">
                <img src="img/avatar.png" alt="Profile Picture" class="profile-img">
            </div>
        </div>
    </header>

    <div class="form-container">
        <h1>Leave Application Submission</h1>

        <?php if ($error): ?>
            <div class="error">
                <p><?php echo htmlspecialchars($error); ?></p>
                <a href="leave_application.php">Go Back</a>
            </div>
        <?php elseif ($success): ?>
            <div class="success">
                <p><?php echo htmlspecialchars($success); ?></p>
                <a href="user_portal.php">Return to Home</a>
            </div>
        <?php else: ?>
            <!-- If accessed directly without form submission -->
            <div class="error">
                <p>Invalid access.</p>
                <a href="leave_application.php">Go to Leave Application Form</a>
            </div>
        <?php endif; ?>

    </div>

</body>
</html>
