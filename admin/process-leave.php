<?php
session_start();
require '../includes/dbconfig.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user']['id'];
$designation_id = $_SESSION['user']['designation_id'] ?? 0;

if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_POST['request_id'], $_POST['action'])) {
    header("Location: manage-leaves.php");
    exit();
}

$request_id = intval($_POST['request_id']);
$action = $_POST['action']; // 'approve' or 'reject'

// === FETCH THE REQUEST TO KNOW WHICH STEP WE ARE ON ===
$stmt = $conn->prepare("SELECT step_1_approver_id, step_2_approver_id, step_3_approver_id, office_type FROM wp_leave_request WHERE request_id = ?");
$stmt->bind_param("i", $request_id);
$stmt->execute();
$request = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$request) {
    die("Invalid request.");
}

$step_1_approver = $request['step_1_approver_id'];
$step_2_approver = $request['step_2_approver_id'];
$step_3_approver = $request['step_3_approver_id'];
$office_type = $request['office_type'];

// === DETERMINE WHICH STEP THIS USER IS APPROVING ===
$approving_step = 0;
if ($user_id == $step_1_approver) $approving_step = 1;
elseif ($user_id == $step_2_approver) $approving_step = 2;
elseif ($user_id == $step_3_approver) $approving_step = 3;

if ($approving_step == 0) {
    die("You are not authorized to approve this request.");
}

// === BUILD UPDATE QUERY BASED ON STEP ===
$updates = [];
$params = [];
$types = "";

if ($action === 'approve') {
    $updates[] = "step_{$approving_step}_status = 'approved'";
    $updates[] = "step_{$approving_step}_date = NOW()";

    // === DETERMINE IF THIS IS THE FINAL STEP (FIXED FOR SUB-OFFICE HEAD) ===
    $is_final_step = false;

    // Case 1: Normal Sub-Office employee → 2 steps
    if ($office_type === 'sub' && $approving_step == 2) {
        $is_final_step = true;
    }
    // Case 2: Sub-Office Head/Leave Officer applied → treated as head office flow → final step is Step 2
    elseif ($office_type === 'head' && $approving_step == 2 && $request['step_3_approver_id'] == NULL) {
        $is_final_step = true;
    }
    // Case 3: Normal Head Office → final step is Step 3
    elseif ($office_type === 'head' && $approving_step == 3) {
        $is_final_step = true;
    }

    if ($is_final_step && $action === 'approve') {
        $updates[] = "final_status = 'approved'";
    }
} elseif ($action === 'reject' && !empty($_POST['rejection_remark'])) {
    $updates[] = "step_{$approving_step}_status = 'rejected'";
    $updates[] = "rejection_remark = ?";
    $params[] = $_POST['rejection_remark'];
    $types .= "s";

    $updates[] = "final_status = 'rejected'";
} else {
    die("Invalid action or remark missing.");
}

$updates[] = "updated_at = NOW()";
$sql = "UPDATE wp_leave_request SET " . implode(", ", $updates) . " WHERE request_id = ?";
$params[] = $request_id;
$types .= "i";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    $_SESSION['success'] = "Leave request " . ($action === 'approve' ? 'approved' : 'rejected') . " successfully.";
} else {
    $_SESSION['error'] = "Failed to process request.";
}

$stmt->close();
header("Location: manage-leaves.php");
exit();
