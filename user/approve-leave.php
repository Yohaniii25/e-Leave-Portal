<?php
session_start();
require '../includes/dbconfig.php';

if (!isset($_SESSION['admin'])) {
    header("Location: ../login.php");
    exit();
}

$request_id = $_POST['request_id'];
$status = $_POST['status']; // "Approved" or "Rejected"

// Fetch Leave Request
$query = $conn->prepare("SELECT user_id, leave_type, number_of_days FROM wp_leave_request WHERE request_id = ?");
$query->bind_param("i", $request_id);
$query->execute();
$result = $query->get_result();
$leaveRequest = $result->fetch_assoc();

if (!$leaveRequest) {
    $_SESSION['error_message'] = "Leave request not found.";
    header("Location: admin-dashboard.php");
    exit();
}

$user_id = $leaveRequest['user_id'];
$leave_type = $leaveRequest['leave_type'];
$leave_days = $leaveRequest['number_of_days'];

if ($status === "Approved") {
    // Update Leave Request Status
    $updateRequest = $conn->prepare("UPDATE wp_leave_request SET status = 'Approved' WHERE request_id = ?");
    $updateRequest->bind_param("i", $request_id);
    $updateRequest->execute();
} else {
    // If rejected, restore the leave balance
    $restoreQuery = $conn->prepare("
        UPDATE wp_pradeshiya_sabha_users 
        SET leave_balance = leave_balance + ?, 
            {$leave_type}_balance = {$leave_type}_balance + ? 
        WHERE ID = ?
    ");
    $restoreQuery->bind_param("iii", $leave_days, $leave_days, $user_id);
    $restoreQuery->execute();

    // Update Leave Request Status
    $updateRequest = $conn->prepare("UPDATE wp_leave_request SET status = 'Rejected' WHERE request_id = ?");
    $updateRequest->bind_param("i", $request_id);
    $updateRequest->execute();
}

$_SESSION['success_message'] = "Leave request updated successfully!";
header("Location: admin-dashboard.php");
exit();
