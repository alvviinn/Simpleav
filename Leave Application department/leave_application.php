<?php
// leave_application.php

// Start the session
session_start();

// Include the database connection
require_once '../DATABASE/db_connect.php';

// Simulate a logged-in user (for testing purposes)
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;

    // Retrieve user details, including gender
    $stmt = $conn->prepare("SELECT `username`, `department`, `gender` FROM `tbl_user` WHERE `user_id` = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    
    if (!$stmt->execute()) {
        echo "Error executing statement: " . $stmt->error;
    }
    
    $stmt->bind_result($username, $department, $gender);

    // Fetch user details and store in session
    if ($stmt->fetch()) {
        $_SESSION['username'] = $username;
        $_SESSION['department'] = $department;
        $_SESSION['gender'] = $gender;
    } else {
        echo "<p>Debug: User not found or data could not be retrieved. Please check the database.</p>";
    }
    $stmt->close();
}

// Initialize session variables with default values to prevent undefined errors
$username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : "Unknown User";
$department = isset($_SESSION['department']) ? htmlspecialchars($_SESSION['department']) : "Unknown Department";
$gender = isset($_SESSION['gender']) ? htmlspecialchars($_SESSION['gender']) : "Unknown";

// Convert gender to uppercase for consistent checking
$gender = strtoupper($gender);
// Define leave types based on gender
$leave_types = ["Sick Leave", "Casual Leave", "Annual Leave", "Compassionate Leave"];
if ($gender === 'MALE') {
    $leave_types[] = "Paternity Leave";
} elseif ($gender === 'FEMALE') {
    $leave_types[] = "Maternity Leave";
}
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
        
                <div class="form-group">
                    <label for="leaveType">Leave Type</label>
                    <select id="leaveType" name="leave_type" required>
                        <option value="">--Select Leave Type--</option>
                        <?php foreach ($leave_types as $type): ?>
                            <option value="<?php echo htmlspecialchars($type); ?>"><?php echo htmlspecialchars($type); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <br>

                <div class="form-group">
                    <label for="startdate">Start Date</label>
                    <input type="date" id="startdate" name="startdate" required>
                </div>
                <br>

                <div class="form-group">
                    <label for="endDate">End Date</label>
                    <input type="date" id="endDate" name="end_date" required>
                </div>
                <br>
            

            <input type="hidden" name="department" value="<?php echo $department; ?>">
            <input type="hidden" name="employee_name" value="<?php echo $username; ?>">

            <div class="form-group">
                <label for="reason">Reason for Leave</label>
                <textarea id="reason" name="reason" rows="4" placeholder="Reason for Leave" required></textarea>
            </div>
            <br>

            <div class="form-group" id="btn">
                <button type="submit">Submit</button>
            </div>
           
        </form>
        <br>

        <div class="form-group" id="btn1">
            <a href="user_portal.php"><button>Back to Home</button></a>
        </div>
    </div>

</body>
</html>
