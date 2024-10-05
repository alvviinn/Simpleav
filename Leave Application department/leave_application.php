<?php
// public/leave_application.php
session_start();

// Check if user is logged in
// if (!isset($_SESSION['username'])) {
//     header("Location: login.php");
//     exit();
// }

$username = htmlspecialchars($_SESSION['username']);
$department = htmlspecialchars($_SESSION['department']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Apply for Leave - Leave Management System</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container">
        <h2>Apply for Leave</h2>
        <form action="submit_leave.php" method="POST">
            <!-- Employee ID is stored in session and hidden -->
            <input type="hidden" name="employee_id" value="<?php echo htmlspecialchars($_SESSION['user_id']); ?>">

            <!-- Employee Name (auto-filled and hidden)
            <input type="hidden" name="employee_name" value="<?php echo $username; ?>">

            Department (auto-filled and hidden)
            <input type="hidden" name="department" value="<?php echo $department; ?>"> -->

            <!-- Type of Leave -->
            <label for="leave_type">Type of Leave:</label>
            <select name="leave_type" id="leave_type" required>
                <option value="">--Select Leave Type--</option>
                <option value="Sick Leave">Sick Leave</option>
                <option value="Casual Leave">Casual Leave</option>
                <option value="Annual Leave">Annual Leave</option>
                <option value="Maternity Leave">Maternity Leave</option>
                <!-- Add more predefined leave types as needed -->
            </select>

            <!-- Start Date -->
            <label for="start_date">Start Date:</label>
            <input type="date" id="start_date" name="start_date" required>

            <!-- End Date -->
            <label for="end_date">End Date:</label>
            <input type="date" id="end_date" name="end_date" required>

            <input type="submit" value="Submit Application">
        </form>
        <nav>
            <a href="profile.php">Back to Profile</a>
        </nav>
    </div>
</body>
</html>
