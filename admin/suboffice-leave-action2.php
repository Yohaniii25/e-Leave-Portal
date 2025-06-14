<?php
session_start();
require '../includes/dbconfig.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../index.php");
    exit();
}

$user = $_SESSION['user'];
$designation_id = $user['designation_id'];
$department_id = $user['department_id'];
$user_id = $user['id'];

// Only allow if designation_id=10 and department_id=6
if (!($designation_id == 10 && $department_id == 6)) {
    $_SESSION['error_message'] = "You are not authorized to perform this action.";
    header("Location: suboffice-step2-approvals.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_id = $_POST['request_id'] ?? null;
    $status = $_POST['status'] ?? null; // 'approved' or 'rejected'
    $remark = $_POST['rejection_remark'] ?? null;
    $now = date('Y-m-d H:i:s');

    if (!$request_id || !in_array($status, ['approved', 'rejected'])) {
        $_SESSION['error_message'] = "Invalid input.";
        header("Location: suboffice-step2-approvals.php");
        exit();
    }

    if ($status === 'approved') {
        $stmt = $conn->prepare("UPDATE wp_leave_request 
            SET step_2_status = ?, 
                step_2_approver_id = ?, 
                step_2_date = ?, 
                final_status = 'approved'
            WHERE request_id = ? 
            AND step_1_status = 'approved' 
            AND step_2_status = 'pending'");
        $stmt->bind_param("sisi", $status, $user_id, $now, $request_id);
    } else {
        $stmt = $conn->prepare("UPDATE wp_leave_request 
            SET step_2_status = ?, 
                step_2_approver_id = ?, 
                step_2_date = ?, 
                rejection_remark = ?, 
                final_status = 'rejected'
            WHERE request_id = ? 
            AND step_1_status = 'approved' 
            AND step_2_status = 'pending'");
        $stmt->bind_param("sissi", $status, $user_id, $now, $remark, $request_id);
    }

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Leave request has been {$status}.";
    } else {
        $_SESSION['error_message'] = "Failed to update leave request.";
    }
} else {
    $_SESSION['error_message'] = "Invalid request method.";
}

header("Location: suboffice-step2-approvals.php");
exit();
