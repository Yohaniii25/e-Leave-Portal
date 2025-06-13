<?php
session_start();
require '../includes/dbconfig.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../index.php");
    exit();
}

$user = $_SESSION['user'];
$user_id = $user['id'];

$step = $_POST['step'];
$request_id = $_POST['request_id'];
$action = $_POST['action']; // 'approve' or 'reject'
$now = date('Y-m-d H:i:s');

$status = ($action === 'approve') ? 'approved' : 'rejected';
$remark = ($action === 'reject' && isset($_POST['rejection_remark'])) ? $_POST['rejection_remark'] : null;

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
    $_SESSION['success_message'] = "Leave has been {$status}.";
} else {
    $_SESSION['error_message'] = "Failed to update leave request.";
}

header("Location: suboffice-leave-approvals.php");
exit();
