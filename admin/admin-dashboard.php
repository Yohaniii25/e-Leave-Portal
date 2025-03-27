<?php
session_start();

// Redirect to login page if user is not logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Check if the session variables are set
$first_name = isset($_SESSION['user']['first_name']) ? $_SESSION['user']['first_name'] : 'Unknown';
$last_name = isset($_SESSION['user']['last_name']) ? $_SESSION['user']['last_name'] : 'User';
$designation = isset($_SESSION['user']['designation']) ? $_SESSION['user']['designation'] : 'No designation';

echo "Welcome " . $first_name . " " . $last_name . "! <br>";
echo "Role: " . $designation;
?>

<a href="logout.php">Logout</a>
