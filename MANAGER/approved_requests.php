<?php
// Include the database configuration file
include 'config.php';

// Fetch approved leave requests where approved_by is not null
$query = "
    SELECT leave_id, user_id, date_requested, leave_type, department, startdate, end_date, approved_by, approval_timestamp, comments
    FROM TBL_LEAVE
    WHERE approved_by IS NOT NULL
";
$result = $conn->query($query);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approved Requests</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            width: 80%;
            max-width: 800px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .back-btn {
            padding: 10px;
            color: white;
            background-color: #007bff;
            border: none;
            border-radius: 4px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 10px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Approved Requests</h2>
        <table>
            <thead>
                <tr>
                    <th>Leave ID</th>
                    <th>User ID</th>
                    <th>Date Requested</th>
                    <th>Leave Type</th>
                    <th>Department</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Approved By</th>
                    <th>Approval Timestamp</th>
                    <th>Comments</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['leave_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['user_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['date_requested']); ?></td>
                            <td><?php echo htmlspecialchars($row['leave_type']); ?></td>
                            <td><?php echo htmlspecialchars($row['department']); ?></td>
                            <td><?php echo htmlspecialchars($row['startdate']); ?></td>
                            <td><?php echo htmlspecialchars($row['end_date']); ?></td>
                            <td><?php echo htmlspecialchars($row['approved_by']); ?></td>
                            <td><?php echo htmlspecialchars($row['approval_timestamp']); ?></td>
                            <td><?php echo htmlspecialchars($row['comments']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="10" style="text-align: center;">No approved requests found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <button class="back-btn" onclick="window.location.href='dashboard.html'">HOME PAGE</button>
    </div>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>
