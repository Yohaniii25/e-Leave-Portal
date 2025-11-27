<?php

session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../includes/dbconfig.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../index.php");
    exit();
}

$user           = $_SESSION['user'];
$user_id        = $user['id'];
$designation_id = $user['designation_id'] ?? 0;
$sub_office     = $user['sub_office'] ?? 'Head Office';
$is_secretary   = ($user_id == 19); // Secretary user

// ============== FORM DATA ==============
$leave_type     = trim($_POST['leave_type'] ?? '');
$start_date     = $_POST['leave_start_date'] ?? '';
$end_date       = $_POST['leave_end_date'] ?? '';
$reason         = trim($_POST['reason'] ?? '');
$substitute     = trim($_POST['substitute'] ?? '');
$is_half_day    = isset($_POST['is_half_day']);

// ============== BASIC VALIDATION ==============
if (empty($leave_type) || empty($start_date) || empty($end_date) || empty($reason)) {
    $_SESSION['error_message'] = "Please fill all required fields.";
    header("Location: leave_request.php");
    exit();
}
if ($start_date > $end_date) {
    $_SESSION['error_message'] = "End date cannot be before start date.";
    header("Location: leave_request.php");
    exit();
}

// ============== CALCULATE DAYS ==============
$start = new DateTime($start_date);
$end   = new DateTime($end_date);
$days  = $start->diff($end)->days + 1;

if ($is_half_day) {
    if (!in_array($leave_type, ['Casual Leave', 'Sick Leave'])) {
        $_SESSION['error_message'] = "Half-day only allowed for Casual or Sick Leave.";
        header("Location: leave_request.php");
        exit();
    }
    if ($days > 1) {
        $_SESSION['error_message'] = "Half-day can only be for a single day.";
        header("Location: leave_request.php");
        exit();
    }
    $number_of_days = 0.5;
} else {
    $number_of_days = $days;
}

// ============== GET LEAVE BALANCE ==============
$stmt = $conn->prepare("SELECT casual_leave_balance, sick_leave_balance, leave_balance FROM wp_pradeshiya_sabha_users WHERE ID = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$bal = $stmt->get_result()->fetch_assoc();
$stmt->close();

$casual_bal = (float)($bal['casual_leave_balance'] ?? 21);
$sick_bal   = (float)($bal['sick_leave_balance'] ?? 24);
$annual_bal = (float)($bal['leave_balance'] ?? 20);

// ============== CALCULATE USED APPROVED LEAVES ==============
$stmt = $conn->prepare("SELECT leave_type, SUM(number_of_days) as used FROM wp_leave_request WHERE user_id = ? AND final_status = 'approved' GROUP BY leave_type");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();

$used = ['Casual Leave' => 0, 'Sick Leave' => 0, 'Duty Leave' => 0];
while ($r = $res->fetch_assoc()) {
    $used[$r['leave_type']] = (float)$r['used'];
}
$stmt->close();

$rem_casual = $casual_bal - $used['Casual Leave'];
$rem_sick   = $sick_bal   - $used['Sick Leave'];
$rem_annual = $annual_bal - ($used['Casual Leave'] + $used['Sick Leave']);

// ============== BALANCE CHECK ==============
if ($leave_type == 'Casual Leave' && $number_of_days > $rem_casual) {
    $_SESSION['error_message'] = "Not enough Casual Leave. Available: " . number_format($rem_casual, 1);
    header("Location: leave_request.php"); exit();
}
if ($leave_type == 'Sick Leave' && $number_of_days > $rem_sick) {
    $_SESSION['error_message'] = "Not enough Sick Leave. Available: " . number_format($rem_sick, 1);
    header("Location: leave_request.php"); exit();
}
if ($leave_type != 'Duty Leave' && $number_of_days > $rem_annual) {
    $_SESSION['error_message'] = "Exceeds annual leave balance. Available: " . number_format($rem_annual, 1);
    header("Location: leave_request.php"); exit();
}

// ============== APPROVAL FLOW LOGIC ==============
$step_1_approver_id = $step_2_approver_id = $step_3_approver_id = null;
$step_1_status = $step_2_status = $step_3_status = 'pending';
$final_status = 'pending';

// 1. Secretary → direct to Head of Pradeshiya Sabha
if ($is_secretary) {
    $substitute = "Head of Pradeshiya Sabha";
    $step_1_status = $step_2_status = 'approved';
    $step_3_status = 'pending';
    $final_status  = 'pending';
}
// 2. Head of Department → direct to Authorized Officer (designation_id = 5)
elseif ($designation_id == 1 && !empty($_POST['hod_direct_to_auth_officer'])) {
    $step_1_status = 'approved';
    $step_2_status ='pending';
    $step_3_status = 'pending';
    $final_status  = 'pending';
}
// 3. Normal employee → normal flow
else {
    $step_1_status = $step_2_status = $step_3_status = 'pending';
    $final_status  = 'pending';
}

// ============== INSERT INTO DATABASE ==============
$sql = "
    INSERT INTO wp_leave_request (
        user_id, leave_type, leave_start_date, leave_end_date, number_of_days,
        reason, substitute, sub_office, office_type, status,
        step_1_approver_id, step_1_status,
        step_2_approver_id, step_2_status,
        step_3_approver_id, step_3_status,
        final_status
    ) VALUES (
        ?, ?, ?, ?, ?,
        ?, ?, ?, 'head', 1,
        ?, ?,
        ?, ?,
        ?, ?,
        ?
    )
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    error_log("Prepare failed: " . $conn->error);
    $_SESSION['error_message'] = "System error. Try again later.";
    header("Location: leave_request.php");
    exit();
}

$stmt->bind_param(
    "isssdsssisssiss",
    $user_id,
    $leave_type,
    $start_date,
    $end_date,
    $number_of_days,
    $reason,
    $substitute,
    $sub_office,
    $step_1_approver_id,
    $step_1_status,
    $step_2_approver_id,
    $step_2_status,
    $step_3_approver_id,
    $step_3_status,
    $final_status
);

if ($stmt->execute()) {
    $_SESSION['success_message'] = "Leave request submitted successfully!";
} else {
    error_log("Insert error: " . $stmt->error);
    $_SESSION['error_message'] = "Failed to submit. Please try again.";
}

$stmt->close();
$conn->close();

header("Location: leave_request.php");
exit();