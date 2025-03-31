<?php
session_start();
require '../includes/dbconfig.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Admin') {
    die("Access denied.");
}

$admin_office = $_SESSION['user']['sub_office'];

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Invalid user ID.");
}

$user_id = intval($_GET['id']);

if ($_SESSION['user']['ID'] == $user_id) {
    die("You cannot delete your own account.");
}

$sql = "SELECT * FROM wp_pradeshiya_sabha_users WHERE ID = ? AND sub_office = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $user_id, $admin_office);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("User not found or you do not have permission to delete this user.");
}

// Proceed with deletion
$sql = "DELETE FROM wp_pradeshiya_sabha_users WHERE ID = ? AND sub_office = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $user_id, $admin_office);

if ($stmt->execute()) {
    echo "User deleted successfully.";
    header("Location: manage-users.php"); 
    exit();
} else {
    echo "Error deleting user: " . $stmt->error;
}
?>
