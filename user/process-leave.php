<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', dirname(__FILE__) . '/../error_log.txt');

session_start();
require '../includes/dbconfig.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['designation'] !== 'Employee') {
    header("Location: ../index.php");
    exit();
}

$user_id     = $_SESSION['user']['id'];
$sub_office  = $_SESSION['user']['sub_office'];
$is_secretary = ($user_id == 19); // Secretary

// Form data
$leave_type     = $_POST['leave_type'] ?? '';
$start_date     = $_POST['leave_start_date'] ?? '';
$end_date       = $_POST['leave_end_date'] ?? '';
$reason         = trim($_POST['reason'] ?? '');
$substitute     = trim($_POST['substitute'] ?? '');
$is_half_day    = isset($_POST['is_half_day']) ? 1 : 0;

// === Validation ===
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

// === Calculate Days ===
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

// === Fetch Leave Balance ===
$balQ = $conn->prepare("SELECT casual_leave_balance, sick_leave_balance, leave_balance FROM wp_pradeshiya_sabha_users WHERE ID = ?");
$balQ->bind_param("i", $user_id);
$balQ->execute();
$bal = $balQ->get_result()->fetch_assoc();
$balQ->close();

$casual_balance = (float)($bal['casual_leave_balance'] ?? 21);
$sick_balance   = (float)($bal['sick_leave_balance']   ?? 24);
$total_balance  = (float)($bal['leave_balance']        ?? 45);

// === Used Approved Leaves ===
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

// === Balance Check ===
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

// === Auto-fill Substitute for Secretary ===
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

// === Set Step Statuses ===
if ($is_secretary) {
    $step1_status = 'approved';  // Auto-approved (secretary workflow)
    $step2_status = 'pending';   // Goes directly to Head of PS
} else {
    $step1_status = 'pending';
    $step2_status = 'pending';
}

// === INSERT REQUEST ===
$insert = $conn->prepare("
    INSERT INTO wp_leave_request 
    (user_id, leave_type, leave_start_date, leave_end_date, number_of_days,
     reason, substitute, sub_office, step_1_status, step_2_status)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

if (!$insert) {
    error_log("MySQL Prepare Error: " . $conn->error);
    $_SESSION['error_message'] = "Database error. Please try again later.";
    header("Location: leave_request.php");
    exit();
}

// Correct bind_param: 10 values with proper types
try {
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
} catch (Exception $e) {
    error_log("bind_param error: " . $e->getMessage());
    $_SESSION['error_message'] = "Database bind error.";
    header("Location: leave_request.php");
    exit();
}

try {
    if ($insert->execute()) {
        $_SESSION['success_message'] = "Leave request submitted successfully!";
    } else {
        error_log("Execute error: " . $insert->error);
        $_SESSION['error_message'] = "Failed to submit leave request: " . $insert->error;
    }
} catch (Exception $e) {
    error_log("Execute exception: " . $e->getMessage());
    $_SESSION['error_message'] = "Error submitting leave request.";
}

$insert->close();
header("Location: leave_request.php");
exit();