<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Leave Dashboard</title>
    <link rel="stylesheet" href="index.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Additional CSS for layout */
        .leave-charts {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
            gap: 10px;
            margin-top: 20px;
        }

        .chart-container {
            width: 150px;
            height: 150px;
            position: relative;
        }

        .chart-container canvas {
            width: 100% !important;
            height: 100% !important;
        }

        /* Team Leave Track chart container */
        .team-leave-track-container {
            width: 90%;
            max-width: 800px;
            margin: 20px auto;
        }

        /* Style links to look like buttons */
        .button-link {
            text-decoration: none;
            padding: 10px 15px;
            background-color: #5cb85c;
            color: white;
            border-radius: 5px;
            font-size: 16px;
        }

        /* Sidebar styling */
        .sidebar {
            width: 250px;
            background-color: #2c3e50;
            color: white;
            padding: 15px;
            position: fixed;
            height: 100%;
        }

        .sidebar h1 {
            font-size: 24px;
            margin-bottom: 20px;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
        }

        .sidebar ul li {
            margin-bottom: 10px;
        }

        .sidebar ul li a {
            color: white;
            text-decoration: none;
            font-size: 18px;
            padding: 10px 15px;
            display: block;
            transition: background-color 0.3s;
        }

        .sidebar ul li a:hover {
            background-color: #34495e;
        }

        /* Main content styling */
        .main-content {
            margin-left: 270px;
            padding: 20px;
        }

        header nav ul {
            list-style: none;
            padding: 0;
        }

        header nav ul li {
            display: inline;
            margin-right: 15px;
        }

        header nav ul li a {
            text-decoration: none;
            color: #333;
        }

        .profile {
            float: right;
        }

        .profile img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
        }
    </style>
</head>

<?php
include("config.php");

$leaveTypes = ["earned", "casual", "sick", "maternity", "sabbatical"];
$leaveData = [];

foreach ($leaveTypes as $type) {
    $sql = "SELECT SUM(days_remaining) as remaining FROM TBL_LEAVE WHERE leave_type = '$type'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $leaveData[$type] = $row['remaining'] ?? 0;
}

// Fetch monthly leave data for Team Leave Track chart
$monthlyLeaves = [];
for ($i = 1; $i <= 12; $i++) {
    $sql = "SELECT COUNT(*) as total FROM TBL_LEAVE WHERE MONTH(startdate) = $i";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $monthlyLeaves[] = $row['total'] ?? 0;
}

echo "<script>
        var leaveData = " . json_encode($leaveData) . ";
        var monthlyLeaves = " . json_encode($monthlyLeaves) . ";
      </script>";

$conn->close();
?>

<body>
    <div class="container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <h1 class="logo">SimpLeave</h1>
            <ul>
                <div class="color">
                    <li><a href="dashboard.html" class="button-link">Dashboard</a></li>
                    <li><a href="#">Analytics</a></li>
                    <li><a href="#">Budgets</a></li>
                </div>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header with Navigation -->
            <header>
                <nav>
                    <ul>
                        <li><a href="#">HOME</a></li>
                        <li><a href="#">ABOUT US</a></li>
                    </ul>
                </nav>
                <div class="profile">
                    <img src="profile-placeholder.png" alt="Profile" class="profile-img">
                </div>
            </header>

            <!-- Leave Dashboard Section -->
            <section class="leave-dashboard">
                <h2>Leave Availability</h2>
                <div class="leave-charts">
                    <div class="chart-container"><canvas id="earnedChart"></canvas></div>
                    <div class="chart-container"><canvas id="casualChart"></canvas></div>
                    <div class="chart-container"><canvas id="sickChart"></canvas></div>
                    <div class="chart-container"><canvas id="maternityChart"></canvas></div>
                    <div class="chart-container"><canvas id="sabbaticalChart"></canvas></div>
                </div>

                <h2>Team Leave Track</h2>
                <div class="team-leave-track-container">
                    <canvas id="teamLeaveChart"></canvas>
                </div>
            </section>
        </main>
    </div>

    <script>
        // Pie Charts Configuration
        function createChart(elementId, title, remaining) {
            const ctx = document.getElementById(elementId).getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Used', 'Remaining'],
                    datasets: [{
                        data: [30, remaining],
                        backgroundColor: ['#FF6384', '#36A2EB'],
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: title
                        }
                    }
                }
            });
        }

        createChart('earnedChart', 'Earned Leave', leaveData.earned);
        createChart('casualChart', 'Casual Leave', leaveData.casual);
        createChart('sickChart', 'Sick Leave', leaveData.sick);
        createChart('maternityChart', 'Maternity Leave', leaveData.maternity);
        createChart('sabbaticalChart', 'Sabbatical Leave', leaveData.sabbatical);

        // Team Leave Track Bar Chart
        const labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        const teamLeaveCtx = document.getElementById('teamLeaveChart').getContext('2d');
        new Chart(teamLeaveCtx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Total Leaves',
                    data: monthlyLeaves,
                    backgroundColor: 'rgba(54, 162, 235, 0.85)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Team Leave Track'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>

    <button><a href="dashboard.html">DASHBOARD</a></button>
</body>
</html>
