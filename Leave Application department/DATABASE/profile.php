<?php
// public/profile.php
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = htmlspecialchars($_SESSION['username']);
$department = htmlspecialchars($_SESSION['department']);
$role = htmlspecialchars($_SESSION['role']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profile - Leave Management System</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Welcome, <?php echo $username; ?>!</h2>
        <p><strong>Department:</strong> <?php echo $department; ?></p>
        <p><strong>Role:</strong> <?php echo ucfirst($role); ?></p>

        <nav>
            <a href="leave_application.php">Apply for Leave</a>
            <?php if ($role === 'manager'): ?>
                <a href="../manager/manager_portal.php">Manager Portal</a>
            <?php endif; ?>
            <a href="logout.php">Logout</a>
        </nav>
    </div>
</body>
</html>
