<?php
include("config.php");

function fetchStaffData($conn) {
    $today = date('Y-m-d');  // Get today's date
    $query = "
        SELECT
            u.user_id,
            u.username,
            u.position,
            MAX(CASE WHEN l.startdate <= '$today' AND l.end_date >= '$today' THEN 'On Leave' ELSE 'Available' END) AS current_status
        FROM
            tbl_user AS u
        LEFT JOIN
            tbl_leave AS l
        ON
            u.user_id = l.user_id
        GROUP BY
            u.user_id, u.username, u.position
    ";

    $result = $conn->query($query);
    $staffData = [];

    while ($row = $result->fetch_assoc()) {
        $staffData[] = $row;
    }

    return $staffData;
}

$staffData = fetchStaffData($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Section</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .navigation{
    width:1200px;
    height: 30px;
    margin:auto;
    margin-top: 30px;
}
.nav{
    width:200px;
    float: left;
    height: 50px;
}
.nav img {
    width: 100px;
    height: 105px;
    size: 20px;
    background-color: transparent;

}

.simpleav{
    text-decoration: none;
    color: rgba(50, 135, 214, 0.975);
    font-size: 36px;
    font-weight: 500;
    margin-top: -80px;
    margin-left: 100px;

}
body{
    width: 100%;
    background-color: white;
    background-position: center;
    background-size: cover;
    height: 109vh;
    background-image: url(background2.jpg);
}
.container{
    margin-right: 250px;
    margin-left: 70px;
    width: 350%;
}
.container h2{
    color: rgba(50, 135, 214, 0.975);
}
.staff-table tr th{
    color: rgba(50, 135, 214, 0.975);

}
.view-details{
    background-color: rgba(50, 135, 214, 0.975);
}
        .modal {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            padding: 20px;
            background-color: white;
            border: 1px solid #ccc;
            z-index: 1000;
        }
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }
        .back-btn {
            display: block; /* Center the button */
            margin: 40px auto; /* Adjust margin to lower the button */
            padding: 10px 20px; /* Add padding for better appearance */
            background-color: rgba(50, 135, 214, 0.975); /* Button background color */
            color: white; /* Button text color */
            border: none; /* Remove border */
            border-radius: 5px; /* Rounded corners */
            cursor: pointer; /* Pointer cursor on hover */
            font-size: 16px; /* Font size */
        }
        .back-btn:hover {
            background-color: rgba(30, 100, 160, 0.975); /* Darker shade on hover */
        }
    </style>
</head>
<body>
    <nav class="navigation">
        <div class="nav">
            <img src="logo2.png" alt="logo">
            <h2 class="simpleav">SimpLeav</h2>
        </div>
    </nav>
    <div class="container">
        <h2>Staff Section</h2>
        <table class="staff-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Position</th>
                    <th>Current Status</th>
                    <th>Leave History</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($staffData as $staff): ?>
                    <tr>
                        <td><?= $staff['username'] ?></td>
                        <td><?= $staff['position'] ?></td>
                        <td><span class="status-indicator <?= $staff['current_status'] == 'On Leave' ? 'on-leave' : 'available' ?>"><?= $staff['current_status'] ?></span></td>
                        <td><button class="view-details" onclick="viewLeaveDetails(<?= $staff['user_id'] ?>)">View Details</button></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <button class="back-btn" onclick="window.location.href='dashboard.html'">HOME PAGE</button>
    </div>

    <!-- Modal for Leave Details -->
    <div class="modal-overlay" onclick="closeModal()"></div>
    <div class="modal" id="leaveDetailsModal">
        <h3>Leave Details</h3>
        <div id="leaveDetailsContent">Loading...</div>
        <button onclick="closeModal()">Close</button>
    </div>

    <script>
        // Function to view leave details in a modal
        function viewLeaveDetails(userId) {
            document.querySelector('.modal-overlay').style.display = 'block';
            document.getElementById('leaveDetailsModal').style.display = 'block';

            // Fetch leave history for the user
            fetch(`leave_details.php?user_id=${userId}`)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('leaveDetailsContent').innerHTML = data;
                })
                .catch(error => console.error('Error fetching leave details:', error));
        }

        function closeModal() {
            document.querySelector('.modal-overlay').style.display = 'none';
            document.getElementById('leaveDetailsModal').style.display = 'none';
        }
    </script>
</body>
</html>

<?php $conn->close(); ?>
