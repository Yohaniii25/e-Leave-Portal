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
$is_secretary   = ($user_id == 19); // Secretary

// ============== FORM DATA ==============
$leave_type     = trim($_POST['leave_type'] ?? '');
$start_date     = $_POST['leave_start_date'] ?? '';
$end_date       = $_POST['leave_end_date'] ?? '';
$reason         = trim($_POST['reason'] ?? '');
$substitute     = trim($_POST['substitute'] ?? '');
$is_half_day    = isset($_POST['is_half_day']);

// ============== VALIDATION & DAYS ==============
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

// ============== LEAVE BALANCE CHECK (same as yours) ==============
$stmt = $conn->prepare("SELECT casual_leave_balance, sick_leave_balance, leave_balance FROM wp_pradeshiya_sabha_users WHERE ID = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$bal = $stmt->get_result()->fetch_assoc();
$stmt->close();

$casual_bal = (float)($bal['casual_leave_balance'] ?? 21);
$sick_bal   = (float)($bal['sick_leave_balance'] ?? 24);
$annual_bal = (float)($bal['leave_balance'] ?? 20);

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

if ($leave_type == 'Casual Leave' && $number_of_days > $rem_casual) {
    $_SESSION['error_message'] = "Not enough Casual Leave. Available: " . number_format($rem_casual, 1);
    header("Location: leave_request.php");
    exit();
}
if ($leave_type == 'Sick Leave' && $number_of_days > $rem_sick) {
    $_SESSION['error_message'] = "Not enough Sick Leave. Available: " . number_format($rem_sick, 1);
    header("Location: leave_request.php");
    exit();
}
if ($leave_type != 'Duty Leave' && $number_of_days > $rem_annual) {
    $_SESSION['error_message'] = "Exceeds annual leave balance. Available: " . number_format($rem_annual, 1);
    header("Location: leave_request.php");
    exit();
}

// ============== SMART APPROVAL FLOW (FINAL VERSION) ==============
$step_1_approver_id = $step_2_approver_id = $step_3_approver_id = null;
$office_type = ($sub_office !== 'Head Office' && !empty($sub_office)) ? 'sub' : 'head';

if (in_array($designation_id, [9, 10]) && $office_type === 'sub') {
    // Sub-Office Head or Leave Officer applies → go to Head Office Auth → Leave Officer
    $step_1_approver_id = getHeadOfficeApprover($conn, 5);  // Auth Officer
    $step_2_approver_id = getHeadOfficeApprover($conn, 8);  // Leave Officer
    $step_3_approver_id = null;

}
// — Regular Sub-Office Employee —
elseif ($office_type === 'sub') {
    $step_1_approver_id = getSubOfficeApprover($conn, $sub_office, 9);  // Sub-Office Head
    $step_2_approver_id = getSubOfficeApprover($conn, $sub_office, 10); // Sub-Office Leave Officer
    $step_3_approver_id = null;
}
// — Head Office Employee —
else {
    $step_1_approver_id = getHOD($conn, $user_id);
    $step_2_approver_id = getHeadOfficeApprover($conn, 8);
    $step_3_approver_id = getHeadOfficeApprover($conn, 5);
}

// Special shortcuts
if ($is_secretary) {
    $step_1_approver_id = getHeadOfficeApprover($conn, 5);
    $step_1_status = 'approved';
    $step_2_status = 'approved';
}
if ($designation_id == 1 && !empty($_POST['hod_direct_to_auth_officer'])) {
    $step_1_status = 'approved';
}

$step_1_status = $step_1_status ?? 'pending';
$step_2_status = $step_2_status ?? 'pending';
$step_3_status = $step_3_status ?? 'pending';
$final_status  = 'pending';

// ============== HELPER FUNCTIONS ==============
function getSubOfficeApprover($conn, $sub_office, $designation_id)
{
    $stmt = $conn->prepare("SELECT ID FROM wp_pradeshiya_sabha_users WHERE designation_id = ? AND sub_office = ? LIMIT 1");
    $stmt->bind_param("is", $designation_id, $sub_office);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $stmt->close();
    return $row['ID'] ?? null;
}

function getHeadOfficeApprover($conn, $designation_id)
{
    $stmt = $conn->prepare("SELECT ID FROM wp_pradeshiya_sabha_users WHERE designation_id = ? AND sub_office = 'Head Office' LIMIT 1");
    $stmt->bind_param("i", $designation_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $stmt->close();
    return $row['ID'] ?? null;
}

function getHOD($conn, $user_id)
{
    $stmt = $conn->prepare("SELECT u.ID FROM wp_pradeshiya_sabha_users u
                            JOIN wp_pradeshiya_sabha_users emp ON u.department_id = emp.department_id
                            WHERE emp.ID = ? AND u.designation_id = 1 LIMIT 1");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $stmt->close();
    return $row['ID'] ?? null;
}

// ============== INSERT REQUEST ==============
$sql = "INSERT INTO wp_leave_request (
    user_id, leave_type, leave_start_date, leave_end_date, number_of_days,
    reason, substitute, sub_office, office_type,
    step_1_approver_id, step_1_status,
    step_2_approver_id, step_2_status,
    step_3_approver_id, step_3_status,
    final_status
) VALUES (
    ?, ?, ?, ?, ?, ?, ?, ?, ?,
    ?, ?, ?, ?, ?, ?, ?
)";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "isssdssssisssiss",
    $user_id,
    $leave_type,
    $start_date,
    $end_date,
    $number_of_days,
    $reason,
    $substitute,
    $sub_office,
    $office_type,
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
    error_log("Insert failed: " . $stmt->error);
    $_SESSION['error_message'] = "Failed to submit request.";
}

$stmt->close();
header("Location: leave_request.php");
exit();
