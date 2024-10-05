<?php
// user_portal.php

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
    // Once available, you can uncomment this section to ensure only authenticated users can access the portal.

    // Check if the user is logged in
    if (!isset($_SESSION['user_id'])) {
        // Redirect to login page or show an error
        header("Location: login.php");
        exit();
    }
*/

// For now, we'll simulate a logged-in user.
// Remove or comment out the following lines once login is integrated.
if (!isset($_SESSION['user_id'])) {
    // Simulated user details
    $_SESSION['user_id'] = 1; // Ensure that a user with user_id=1 exists in tbl_user
    $_SESSION['username'] = "John Doe";
    $_SESSION['department'] = "Engineering";
}

// Fetch user details
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'N/A';
$department = $_SESSION['department'] ?? 'N/A';

// Fetch additional user details from tbl_user
$stmt_user = $conn->prepare("SELECT email, company, role, position, profile_image FROM tbl_user WHERE user_id = ?");
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$stmt_user->bind_result($email, $company, $role, $position, $profile_image);
$stmt_user->fetch();
$stmt_user->close();

// Define maximum leave per type
$max_leave = array(
    "Sick Leave" => 14,
    "Casual Leave" => 7,
    "Annual Leave" => 21,
    "Maternity Leave" => 90,
    "Paternity Leave" => 10,
    "Bereavement Leave" => 5
);

// Fetch used leave per type (approved leaves)
$used_leave = array();

// Initialize used_leave with 0
foreach ($max_leave as $type => $max_days) {
    $used_leave[$type] = 0;
}

// Prepare statement to fetch used leave
$stmt_leave = $conn->prepare("SELECT leave_type, DATEDIFF(end_date, startdate) + 1 AS days_taken FROM tbl_leave WHERE user_id = ? AND current_status = 1"); // Assuming current_status=1 means approved
$stmt_leave->bind_param("i", $user_id);
$stmt_leave->execute();
$result_leave = $stmt_leave->get_result();

while ($row = $result_leave->fetch_assoc()) {
    $type = $row['leave_type'];
    $days_taken = $row['days_taken'];
    if (isset($used_leave[$type])) {
        $used_leave[$type] += $days_taken;
    }
}

$stmt_leave->close();

// Calculate remaining leave
$remaining_leave = array();

foreach ($max_leave as $type => $max_days) {
    $remaining = $max_days - ($used_leave[$type] ?? 0);
    $remaining = $remaining >= 0 ? $remaining : 0; // Prevent negative
    $remaining_leave[$type] = $remaining;
}

// Fetch leave applications
$leave_applications = array();

$stmt_applications = $conn->prepare("SELECT leave_id, leave_type, startdate, end_date, current_status, comments, approval_timestamp FROM tbl_leave WHERE user_id = ? ORDER BY date_requested DESC");
$stmt_applications->bind_param("i", $user_id);
$stmt_applications->execute();
$result_applications = $stmt_applications->get_result();

while ($row = $result_applications->fetch_assoc()) {
    $leave_applications[] = $row;
}

$stmt_applications->close();

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Portal - SIMPLEAV</title>
    <link rel="stylesheet" href="style.css">
    <!-- Include Chart.js via CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Additional styling for portal layout */
        .portal-container {
    display: flex;
    flex-wrap: wrap;
    margin: 20px;
}

.user-details {
    flex: 1; /* This will allow it to grow and fill the space */
    min-width: 250px;
    margin-right: 20px;
    background-color: #f9f9f9;
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); /* Added shadow for depth */
}

.leave-balance {
    flex: 1; /* This will allow it to grow and fill the space */
    min-width: 250px;
    max-width: 400px;
    background-color: #f9f9f9;
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); /* Added shadow for depth */
    height: auto; /* Ensures it doesn't grow too tall */
    overflow: hidden; /* Prevents overflow */
    display: flex;
    align-items: center; /* Center the pie chart vertically */
    justify-content: center; /* Center the pie chart horizontally */
}

.leave-table-container {
    width: 100%;
    margin-top: 30px;
}

.leave-table {
    width: 100%;
    border-collapse: collapse;
}

.leave-table th,
.leave-table td {
    border: 1px solid #ddd;
    padding: 8px;
    text-align: center;
}

.leave-table th {
    background-color: #0dbbbf;
    color: white;
}

.leave-table tr:nth-child(even) {
    background-color: #f2f2f2; /* Added striping for better readability */
}

.leave-table tr:hover {
    background-color: #ddd; /* Highlight row on hover */
}

/* Adjust pie chart size */
#leaveChart {
    max-width: 100%; /* Adjust to prevent large sizes */
    height: auto;
    margin: auto;
}

/* Navigation buttons */
.nav-buttons {
    margin-top: 20px;
    text-align: center;
}

.nav-buttons button {
    padding: 10px 20px;
    margin: 5px;
    background-color: #0dbbbf;
    border: none;
    color: white;
    cursor: pointer;
    border-radius: 4px;
    transition: background-color 0.3s; /* Smooth transition */
}

.nav-buttons button:hover {
    background-color: #3dafbf;
}

/* Responsive layout */
@media (max-width: 768px) {
    .portal-container {
        flex-direction: column;
    }

    .user-details,
    .leave-balance {
        margin-right: 0;
        margin-bottom: 20px;
        width: 100%; /* Make them full width on smaller screens */
    }

    #leaveChart {
        max-width: 100%;
        margin: 20px auto; /* Center the chart */
    }
}

    </style>
</head>
<body>

    <header>
        <div class="navbar">
            <div class="logo">SIMPLEAV</div>
            <nav>
                <ul>
                    <li><a href="user_portal.php">Home</a></li>
                    <li><a href="#">Leave Requests</a></li>
                    <li><a href="#">Help</a></li>
                </ul>
            </nav>
            <div class="profile">
                <img src="<?php echo htmlspecialchars($profile_image ?? '../img/Avatar.png'); ?>" alt="Profile Picture" class="profile-img">
            </div>
        </div>
    </header>

    <div class="portal-container">
        <!-- User Details -->
        <div class="user-details">
            <h1><?php echo htmlspecialchars($username ?? ''); ?></h1>
            <h2><strong>Email:</strong> <?php echo htmlspecialchars($email ?? ''); ?></h2>
            <h2><strong>Company:</strong> <?php echo htmlspecialchars($company ?? ''); ?></h2>
            <h2><strong>Department:</strong> <?php echo htmlspecialchars($department ?? ''); ?></h2>
            <h2><strong>Role:</strong> <?php echo htmlspecialchars($role ?? ''); ?></h2>
            <h2><strong>Position:</strong> <?php echo htmlspecialchars($position ?? ''); ?></h2>
        </div>

        <!-- Leave Balance Pie Chart -->
        <div class="leave-balance">
            <canvas id="leaveChart"></canvas>
        </div>
    </div>

    <!-- Navigation Buttons -->
    <div class="nav-buttons">
        <button onclick="window.location.href='leave_application.php'">Apply for Leave</button>
        <button onclick="window.location.href='user_portal.php'">Refresh</button>
    </div>

    <!-- Leave Applications Table -->
    <div class="leave-table-container">
        <h3 style="margin-left: 40px;">Past Leave Applications</h3>
        <table class="leave-table">
            <thead>
                <tr>
                    <th>Leave ID</th>
                    <th>Leave Type</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Status</th>
                    <th>Approval Date</th>
                    <th>Comments</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($leave_applications)): ?>
                    <?php foreach ($leave_applications as $application): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($application['leave_id'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($application['leave_type'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars(date("d-m-Y", strtotime($application['startdate']))); ?></td>
                            <td><?php echo htmlspecialchars(date("d-m-Y", strtotime($application['end_date']))); ?></td>
                            <td>
                                <?php
                                    $status = $application['current_status'];
                                    if ($status == 0) {
                                        echo 'Pending';
                                    } elseif ($status == 1) {
                                        echo 'Approved';
                                    } elseif ($status == 2) {
                                        echo 'Rejected';
                                    } else {
                                        echo 'Unknown';
                                    }
                                ?>
                            </td>
                            <td>
                                <?php
                                    if ($application['approval_timestamp']) {
                                        echo htmlspecialchars(date("d-m-Y H:i:s", strtotime($application['approval_timestamp'])));
                                    } else {
                                        echo 'N/A';
                                    }
                                ?>
                            </td>
                            <td><?php echo htmlspecialchars($application['comments'] ?? ''); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">No leave applications found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Chart.js Script for Pie Chart -->
    <script>
    // Assuming $remaining_leave and $max_leave are available in PHP
    const usedLeave = <?php echo json_encode(array_values($remaining_leave)); ?>; // Used leave
    const maxLeave = <?php echo json_encode(array_values($max_leave)); ?>; // Max leave
    const remainingLeave = maxLeave.map((max, index) => max - usedLeave[index]); // Calculate remaining leave

    // Data for the pie chart
    const leaveData = {
        labels: <?php echo json_encode(array_keys($max_leave)); ?>,
        datasets: [
            {
                label: 'Used Leave',
                data: usedLeave,
                backgroundColor: [
                    '#FF6384',
                    '#36A2EB',
                    '#FFCE56',
                    '#8BC34A',
                    '#FF9800',
                    '#9C27B0'
                ],
                borderColor: 'black', // Set border color
                borderWidth: 1, // Set border width to 1px
                hoverOffset: 4
            },
            {
                label: 'Remaining Leave',
                data: remainingLeave,
                backgroundColor: 'maroon', // Color for remaining leave
                borderColor: 'black', // Set border color for remaining leave
                borderWidth: 1, // Set border width to 1px
                hoverOffset: 4
            }
        ]
    };

    // Configuration options
    const config = {
        type: 'pie',
        data: leaveData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                },
                title: {
                    display: true,
                    text: 'Leave Balances',
                    font: {
                        size: 22, // Adjust the font size as needed
                        weight: 'bold', // Make the font bold
                    }
                }
            }
        },
    };

    // Render the pie chart
    const leaveChart = new Chart(
        document.getElementById('leaveChart'),
        config
    );
</script>


</body>
</html>
