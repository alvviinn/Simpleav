<?php
// leave_application.php

// Start the session
session_start();

// Include the database connection
require_once '../DATABASE/db_connect.php';

/* 
    // The following section is commented out because login/registration is handled by another team.
    // Once available, you can uncomment this section to ensure only authenticated users can access the form.

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
    // Simulate a user with user_id = 1 (ensure this user exists in tbl_user)
    $_SESSION['user_id'] = 1;
    // Optionally, retrieve other user details if needed
    // For example, retrieve department from tbl_user
    $stmt = $conn->prepare("SELECT `username`, `department` FROM `tbl_user` WHERE `user_id` = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $stmt->bind_result($username, $department);
    $stmt->fetch();
    $_SESSION['username'] = $username;
    $_SESSION['department'] = $department;
    $stmt->close();
}

$username = htmlspecialchars($_SESSION['username']);
$department = htmlspecialchars($_SESSION['department']);

// Define predefined leave types
$leave_types = [
    "Sick Leave",
    "Casual Leave",
    "Annual Leave",
    "Maternity Leave",
    "Paternity Leave",
    "Bereavement Leave"
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Leave Application Form</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <header>
        <div class="navbar">
            <div class="logo">SIMPLEAV</div>
            <nav>
                <ul>
                    <li><a href="user_portal.php">Home</a></li>
                    <li><a href="leave_application.php">Apply for Leave</a></li>
                    <li><a href="#">Help</a></li>
                </ul>
            </nav>
            <div class="profile">
                <img src="img/avatar.png" alt="Profile Picture" class="profile-img">
            </div>
        </div>
    </header>

    <div class="form-container">
        <h1>Leave Application Form</h1>
        <form action="submit_leave.php" method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label for="leaveType">Leave Type</label>
                    <!-- Changed from input type="text" to select dropdown -->
                    <select id="leaveType" name="leave_type" required>
                        <option value="">--Select Leave Type--</option>
                        <?php foreach ($leave_types as $type): ?>
                            <option value="<?php echo htmlspecialchars($type); ?>"><?php echo htmlspecialchars($type); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="startDate">Start Date</label>
                    <input type="date" id="startDate" name="start_date" required>
                </div>

                <div class="form-group">
                    <label for="endDate">End Date</label>
                    <input type="date" id="endDate" name="end_date" required>
                </div>
            </div>

            <!-- Department and Employee Name are auto-filled and hidden -->
            <input type="hidden" name="department" value="<?php echo $department; ?>">
            <input type="hidden" name="employee_name" value="<?php echo $username; ?>">

            <div class="form-group">
                <label for="reason">Reason for Leave</label>
                <textarea id="reason" name="reason" rows="4" placeholder="Reason for Leave" required></textarea>
            </div>

            <div class="form-group" id="btn">
                <button type="submit">Submit</button>
            </div>
        </form>

        <!-- Navigation Button -->
        <div class="form-group" id = "btn1">
            <a href="user_portal.php"><button>Back to Home</button></a>
        </div>
    </div>

</body>
</html>
