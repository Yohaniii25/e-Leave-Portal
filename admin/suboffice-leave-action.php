<?php
session_start();
require '../includes/dbconfig.php';
require '../includes/navbar.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user']['id'];
$step = $_POST['step'];
$request_id = $_POST['request_id'];
$status = $_POST['status']; // 'approved' or 'rejected'
$remark = $_POST['rejection_remark'] ?? null;
$now = date('Y-m-d H:i:s');

if ($step == 1) {
    $stmt = $conn->prepare("UPDATE wp_leave_request 
        SET step_1_status = ?, 
            step_1_approver_id = ?, 
            step_1_date = ?, 
            rejection_remark = IF(? = 'rejected', ?, rejection_remark),
            final_status = IF(? = 'rejected', 'rejected', final_status) 
        WHERE request_id = ?");
    $stmt->bind_param("sissssi", $status, $user_id, $now, $status, $remark, $status, $request_id);
} elseif ($step == 2) {
    $stmt = $conn->prepare("UPDATE wp_leave_request 
        SET step_2_status = ?, 
            step_2_approver_id = ?, 
            step_2_date = ?, 
            rejection_remark = IF(? = 'rejected', ?, rejection_remark),
            final_status = IF(? = 'rejected', 'rejected', 'approved') 
        WHERE request_id = ?");
    $stmt->bind_param("sissssi", $status, $user_id, $now, $status, $remark, $status, $request_id);
}

if ($stmt->execute()) {
    $_SESSION['success_message'] = "Leave request has been {$status} successfully.";
} else {
    $_SESSION['error_message'] = "Failed to update leave request.";
}

header("Location: suboffice-leave-approvals.php");
exit();
