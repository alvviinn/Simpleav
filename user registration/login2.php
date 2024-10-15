<?php
session_start();
include "connection.php";
require "Constants.php";
require "dbConn.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare('SELECT password FROM users WHERE Email = ?');
    $stmt->execute([$email]);
    $result = $stmt->fetch();

    if ($result && password_verify($password, $result['password'])) {
        $_SESSION['email'] = $email;
        header('Location: welcome.php'); // Redirect to a welcome page
        exit;
    } else {
        $error = 'Invalid email or password.';
    }
}
?>