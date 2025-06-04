<?php
session_start();
require "../includes/dbconfig.php";

if (!isset($_POST['request_id'], $_POST['approver_id'], $_POST['action'])) {
    die("Invalid request.");
}

$request_id = intval($_POST['request_id']);
$approver_id = intval($_POST['approver_id']);
$action = $_POST['action'];
$remark = $_POST['remark'] ?? null;

if (!in_array($action, ['approve', 'reject'])) {
    die("Invalid action.");
}

$status = $action === 'approve' ? 'approved' : 'rejected';

$sql = "
    UPDATE wp_leave_request 
    SET 
        step_1_status = ?,
        step_1_approver_id = ?,
        step_1_date = NOW(),
        rejection_remark = ?
    WHERE request_id = ?
";

$stmt = $conn->prepare($sql);
$remark_safe = $status === 'rejected' ? $remark : null;
$stmt->bind_param("sisi", $status, $approver_id, $remark_safe, $request_id);
$stmt->execute();

header("Location: hod-leaves.php");
exit;
?>
