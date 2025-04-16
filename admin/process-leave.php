<?php
session_start();
require '../includes/dbconfig.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['request_id'], $_POST['action'])) {
    $request_id = $_POST['request_id'];
    $action = $_POST['action'];

    if ($action === 'approve') {
        $status = 'Approved';
        $sql = "UPDATE wp_leave_request SET status = ? WHERE request_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $status, $request_id);
    } elseif ($action === 'reject' && isset($_POST['rejection_remark'])) {
        $status = 'Rejected';
        $remark = $_POST['rejection_remark'];
        $sql = "UPDATE wp_leave_request SET status = ?, rejection_remark = ? WHERE request_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $status, $remark, $request_id);
    }

    if ($stmt->execute()) {
        header("Location: manage-leaves.php");
        exit();
    } else {
        echo "Something went wrong.";
    }
}

?>
