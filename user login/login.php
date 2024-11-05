<?php
session_start();
include("connection.php");

// Initialize error message
$error = "";

// Process form only if it's submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Capture email and password from the form
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Prepare SQL query
    $query = "SELECT * FROM tbl_user WHERE email = ? LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    // Validate user credentials
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Check password (assuming no hash)
        if ($password === $user['password']) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];

            // Check is_manager status and redirect
            if ($user['is_manager'] == 1) {
                header("Location: ../Leave Application department/user_portal.php");
                exit();
            } else {
                header("Location: ../Leave Application department/user_portal.php");
                exit();
            }
        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "No user found with that email.";
    }

    $stmt->close();
    $conn->close();
}

// Display error message if exists
if (!empty($error)) {
    echo "<p class='text-danger'>" . htmlspecialchars($error) . "</p>";
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <title>Login</title>
</head>
<body class="login">
    <nav class="navigation">
        <div class="nav">
            <img src="logo2.png" alt="logo">
            <h2 class="simpleav">SimpLeav</h2>
        </div> 
    </nav>
    <div class="container">
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
            <h1>Login</h1>
            <div class="input">
                <input type="email" class="form-control" placeholder="Email address" name="email" required>
            </div>
            <div class="input">
                <input type="password" class="form-control" placeholder="Password" name="password" required>
            </div>
            <button type="submit" name="submit" class="btn">Sign In</button>
        </form>
    </div>
</body>
</html>
