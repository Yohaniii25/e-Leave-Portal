<?php
session_start();
require '../includes/dbconfig.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['designation'] !== 'Employee') {
    header("Location: ../index.php");
    exit();
}

$user_id       = $_SESSION['user']['id'];
$sub_office    = $_SESSION['user']['sub_office'];
$is_secretary  = ($user_id == 19);  // Secretary

// Form data
$leave_type     = $_POST['leave_type'] ?? '';
$start_date     = $_POST['leave_start_date'] ?? '';
$end_date       = $_POST['leave_end_date'] ?? '';
$reason         = trim($_POST['reason'] ?? '');
$substitute     = trim($_POST['substitute'] ?? '');
$is_half_day    = isset($_POST['is_half_day']) ? 1 : 0;

// Basic validation
if (empty($leave_type) || empty($start_date) || empty($end_date) || empty($reason)) {
    $_SESSION['error_message'] = "All required fields must be filled.";
    header("Location: leave_request.php");
    exit();
}
if ($start_date > $end_date) {
    $_SESSION['error_message'] = "End date cannot be before start date.";
    header("Location: leave_request.php");
    exit();
}

// Calculate days
$start = new DateTime($start_date);
$end   = new DateTime($end_date);
$days_diff = $start->diff($end)->days + 1;

if ($is_half_day) {
    if (!in_array($leave_type, ['Casual Leave', 'Sick Leave'])) {
        $_SESSION['error_message'] = "Half-day only for Casual/Sick Leave.";
        header("Location: leave_request.php");
        exit();
    }
    if ($days_diff > 1) {
        $_SESSION['error_message'] = "Half-day only for single day.";
        header("Location: leave_request.php");
        exit();
    }
    $number_of_days = 0.5;
} else {
    $number_of_days = $days_diff;
}

// Fetch leave balance
$balQ = $conn->prepare("SELECT casual_leave_balance, sick_leave_balance, leave_balance FROM wp_pradeshiya_sabha_users WHERE ID = ?");
$balQ->bind_param("i", $user_id);
$balQ->execute();
$bal = $balQ->get_result()->fetch_assoc();
$balQ->close();

$casual_balance = (float)($bal['casual_leave_balance'] ?? 21);
$sick_balance   = (float)($bal['sick_leave_balance']   ?? 24);
$total_balance  = (float)($bal['leave_balance']        ?? 45);

// Used approved leaves
$usedQ = $conn->prepare("SELECT leave_type, SUM(number_of_days) AS used FROM wp_leave_request WHERE user_id = ? AND status = 2 GROUP BY leave_type");
$usedQ->bind_param("i", $user_id);
$usedQ->execute();
$usedResult = $usedQ->get_result();

$used = ['Casual Leave' => 0, 'Sick Leave' => 0, 'Duty Leave' => 0];
while ($row = $usedResult->fetch_assoc()) {
    $used[$row['leave_type']] = (float)$row['used'];
}
$usedQ->close();

$remaining_casual = $casual_balance - $used['Casual Leave'];
$remaining_sick   = $sick_balance   - $used['Sick Leave'];
$remaining_total  = $total_balance  - ($used['Casual Leave'] + $used['Sick Leave']);

// Balance validation
if ($leave_type === 'Casual Leave' && $number_of_days > $remaining_casual) {
    $_SESSION['error_message'] = "Not enough Casual Leave. Available: " . number_format($remaining_casual, 1);
    header("Location: leave_request.php");
    exit();
}
if ($leave_type === 'Sick Leave' && $number_of_days > $remaining_sick) {
    $_SESSION['error_message'] = "Not enough Sick Leave. Available: " . number_format($remaining_sick, 1);
    header("Location: leave_request.php");
    exit();
}
if ($leave_type !== 'Duty Leave' && $number_of_days > $remaining_total) {
    $_SESSION['error_message'] = "Exceeds total leave balance. Available: " . number_format($remaining_total, 1);
    header("Location: leave_request.php");
    exit();
}

// Auto-fill substitute for Secretary
if ($is_secretary) {
    $headQ = $conn->prepare("
        SELECT CONCAT(first_name, ' ', last_name) AS name 
        FROM wp_pradeshiya_sabha_users 
        WHERE designation_id = 3 AND sub_office = 'Head Office' 
        LIMIT 1
    ");
    $headQ->execute();
    $head = $headQ->get_result()->fetch_assoc();
    $headQ->close();
    $substitute = $head['name'] ?? 'Head of Pradeshiya Sabha';
}

// INSERT — Only using existing columns
$insert = $conn->prepare("
    INSERT INTO wp_leave_request 
    (user_id, leave_type, leave_start_date, leave_end_date, number_of_days, 
     reason, substitute, sub_office, 
     step_1_status, step_2_status, status, created_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())
");

if (!$insert) {
    die("Database error: " . $conn->error);
}

// Set approval flow
if ($is_secretary) {
    $step1_status = 'skipped';   // Secretary skips HOD
    $step2_status = 'pending';   // Goes directly to Head of PS
} else {
    $step1_status = 'pending';
    $step2_status = 'pending';
}

$insert->bind_param(
    "isssdsssss",
    $user_id,
    $leave_type,
    $start_date,
    $end_date,
    $number_of_days,
    $reason,
    $substitute,
    $sub_office,
    $step1_status,
    $step2_status
);

if ($insert->execute()) {
    $_SESSION['success_message'] = "Leave request submitted successfully!";
} else {
    $_SESSION['error_message'] = "Failed to submit: " . $insert->error;
}

$insert->close();
header("Location: leave_request.php");
exit();
?>