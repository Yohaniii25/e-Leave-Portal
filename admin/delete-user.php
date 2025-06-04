<?php
session_start();
require '../includes/dbconfig.php';

if (!isset($_SESSION['user'])) {
    die("Access denied.");
}

$email = $_SESSION['user']['email'];

// Fetch the designation and sub_office of the logged-in user
$query = "SELECT d.designation_name, u.sub_office, u.ID 
          FROM wp_pradeshiya_sabha_users u
          LEFT JOIN wp_designations d ON u.designation_id = d.designation_id
          WHERE u.email = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if (!$result || $result->num_rows === 0) {
    die("Access denied.");
}

$user_data = $result->fetch_assoc();
$designation = strtolower(trim($user_data['designation_name']));
$admin_office = $user_data['sub_office'];
$logged_in_user_id = $user_data['ID'];

if ($designation !== 'admin') {
    die("Only Admins can perform this action.");
}

// Validate and sanitize user ID to delete
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Invalid user ID.");
}

$user_id = intval($_GET['id']);

if ($user_id === $logged_in_user_id) {
    die("You cannot delete your own account.");
}

// Check that the user to delete belongs to the same sub_office
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
    header("Location: manage-users.php?msg=deleted");
    exit();
} else {
    echo "Error deleting user: " . $stmt->error;
}
?>
