<?php
session_start();
require '../includes/dbconfig.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['designation'] !== 'Employee') {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user']['id'];
$sub_office = $_SESSION['user']['sub_office'];

$leave_type = $_POST['leave_type'] ?? '';
$start_date = $_POST['leave_start_date'] ?? '';
$end_date = $_POST['leave_end_date'] ?? '';
$reason = trim($_POST['reason'] ?? '');
$substitute = trim($_POST['substitute'] ?? '');
$is_half_day = isset($_POST['is_half_day']) ? 1 : 0;

if (empty($leave_type) || empty($start_date) || empty($end_date) || empty($reason)) {
    $_SESSION['error_message'] = "All fields required.";
    header("Location: leave_request.php");
    exit();
}

if ($start_date > $end_date) {
    $_SESSION['error_message'] = "End date cannot be before start.";
    header("Location: leave_request.php");
    exit();
}

$start = new DateTime($start_date);
$end = new DateTime($end_date);
$full_days = $start->diff($end)->days + 1;

if ($is_half_day) {
    if (!in_array($leave_type, ['Casual Leave', 'Sick Leave'])) {
        $_SESSION['error_message'] = "Half day only for Casual/Sick.";
        header("Location: leave_request.php");
        exit();
    }
    if ($full_days > 1) {
        $_SESSION['error_message'] = "Half day only for 1 day.";
        header("Location: leave_request.php");
        exit();
    }
    $number_of_days = 0.5;
} else {
    $number_of_days = $full_days;
}

// === GET CURRENT BALANCE ===
$balanceQ = $conn->prepare("
    SELECT casual_leave_balance, sick_leave_balance, leave_balance 
    FROM wp_pradeshiya_sabha_users 
    WHERE ID = ?
");
$balanceQ->bind_param("i", $user_id);
$balanceQ->execute();
$bal = $balanceQ->get_result()->fetch_assoc();
$balanceQ->close();

$casual_balance = (float)($leaveData['casual_leave_balance'] ?? 24);
$sick_balance   = (float)($leaveData['sick_leave_balance'] ?? 21);
$total_balance  = (float)($leaveData['leave_balance'] ?? 45);

// === GET USED LEAVES (status = 2) ===
$usedQ = $conn->prepare("
    SELECT leave_type, SUM(number_of_days) as used 
    FROM wp_leave_request 
    WHERE user_id = ? AND status = 2 
    GROUP BY leave_type
");
$usedQ->bind_param("i", $user_id);
$usedQ->execute();
$usedResult = $usedQ->get_result();

$used = ['Casual Leave' => 0, 'Sick Leave' => 0, 'Duty Leave' => 0];
while ($row = $usedResult->fetch_assoc()) {
    $used[$row['leave_type']] = (float)$row['used'];
}
$usedQ->close();

// === REMAINING BALANCE ===
$rem_casual = $casual_balance - $used['Casual Leave'];
$rem_sick   = $sick_balance   - $used['Sick Leave'];
$rem_total  = $total_balance  - ($used['Casual Leave'] + $used['Sick Leave']); // Duty excluded

// === VALIDATE ===
if ($leave_type === 'Casual Leave' && $number_of_days > $rem_casual) {
    $_SESSION['error_message'] = "Not enough Casual Leave. Available: " . number_format($rem_casual, 1);
    header("Location: leave_request.php");
    exit();
}
if ($leave_type === 'Sick Leave' && $number_of_days > $rem_sick) {
    $_SESSION['error_message'] = "Not enough Sick Leave. Available: " . number_format($rem_sick, 1);
    header("Location: leave_request.php");
    exit();
}
if ($leave_type !== 'Duty Leave' && $number_of_days > $rem_total) {
    $_SESSION['error_message'] = "Not enough total leave. Available: " . number_format($rem_total, 1);
    header("Location: leave_request.php");
    exit();
}

// === INSERT REQUEST ===
$insert = $conn->prepare("
    INSERT INTO wp_leave_request 
    (user_id, leave_type, leave_start_date, leave_end_date, number_of_days, reason, substitute, sub_office, status, final_status) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, 'pending')
");
$insert->bind_param("isssdsss", $user_id, $leave_type, $start_date, $end_date, $number_of_days, $reason, $substitute, $sub_office);

if ($insert->execute()) {
    $_SESSION['success_message'] = "Leave request submitted successfully.";
} else {
    $_SESSION['error_message'] = "Failed to submit request.";
}
$insert->close();

header("Location: leave_request.php");
exit();
?>