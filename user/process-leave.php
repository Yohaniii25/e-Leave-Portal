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
$department_id  = $user['department_id'] ?? null;
$sub_office     = $user['sub_office'] ?? 'Head Office';
$is_secretary   = ($user_id == 19); // Secretary - special 2-step flow

// ============== FORM DATA ==============
$leave_type     = trim($_POST['leave_type'] ?? '');
$start_date     = $_POST['leave_start_date'] ?? '';
$end_date       = $_POST['leave_end_date'] ?? '';
$reason         = trim($_POST['reason'] ?? '');
$substitute     = trim($_POST['substitute'] ?? '');
$is_half_day    = isset($_POST['is_half_day']);
$hod_direct     = isset($_POST['hod_direct_to_auth_officer']); // From leave_request.php for HODs

// ============== VALIDATION ==============
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

// ============== CALCULATE NUMBER OF DAYS ==============
$start = new DateTime($start_date);
$end   = new DateTime($end_date);
$days  = $start->diff($end)->days + 1; // Inclusive count

if ($is_half_day) {
    if (!in_array($leave_type, ['Casual Leave', 'Sick Leave'])) {
        $_SESSION['error_message'] = "Half-day is only allowed for Casual or Sick Leave.";
        header("Location: leave_request.php");
        exit();
    }
    if ($days > 1) {
        $_SESSION['error_message'] = "Half-day can only be requested for a single day.";
        header("Location: leave_request.php");
        exit();
    }
    $number_of_days = 0.5;
} else {
    $number_of_days = $days;
}

// ============== FETCH CURRENT BALANCES ==============
$stmt = $conn->prepare("
    SELECT casual_leave_balance, sick_leave_balance 
    FROM wp_pradeshiya_sabha_users 
    WHERE ID = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$bal = $stmt->get_result()->fetch_assoc();
$stmt->close();

$casual_balance = (float)($bal['casual_leave_balance'] ?? 21);
$sick_balance   = (float)($bal['sick_leave_balance'] ?? 24);

// ============== CALCULATE USED APPROVED LEAVES ==============
$stmt = $conn->prepare("
    SELECT leave_type, SUM(number_of_days) AS used 
    FROM wp_leave_request 
    WHERE user_id = ? AND final_status = 'approved' 
    GROUP BY leave_type
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();

$used_casual = $used_sick = 0;
while ($row = $res->fetch_assoc()) {
    if ($row['leave_type'] === 'Casual Leave') $used_casual = (float)$row['used'];
    if ($row['leave_type'] === 'Sick Leave')   $used_sick   = (float)$row['used'];
}
$stmt->close();

$remaining_casual = $casual_balance - $used_casual;
$remaining_sick   = $sick_balance   - $used_sick;

// ============== BALANCE VALIDATION ==============
if ($leave_type === 'Casual Leave' && $number_of_days > $remaining_casual) {
    $_SESSION['error_message'] = "Insufficient Casual Leave. Available: " . number_format($remaining_casual, 1) . " days.";
    header("Location: leave_request.php");
    exit();
}

if ($leave_type === 'Sick Leave' && $number_of_days > $remaining_sick) {
    $_SESSION['error_message'] = "Insufficient Sick Leave. Available: " . number_format($remaining_sick, 1) . " days.";
    header("Location: leave_request.php");
    exit();
}

// Duty Leave has no limit — only tracked

// ============== DETERMINE OFFICE TYPE ==============
$office_type = ($sub_office !== 'Head Office' && !empty($sub_office)) ? 'sub' : 'head';

// ============== KEY APPROVER IDs (Hardcoded for reliability) ==============
$head_ps_id         = 135; // Head of Pradeshiya Sabha
$auth_officer_id    = 136; // Head Office Authorized Officer
$leave_officer_id   = 137; // Leave Officer (final for most flows)

// ============== DEFAULT STATUS VALUES ==============
$step_1_approver_id = $step_2_approver_id = $step_3_approver_id = null;
$step_1_status = 'pending';
$step_2_status = 'pending';
$step_3_status = 'pending';
$final_status  = 'pending'; // ← Always 'pending' on new submission

// ============== APPROVAL WORKFLOW LOGIC ==============

// 1. SECRETARY (user_id = 19) → Strict 2-step process
if ($is_secretary) {
    $step_1_approver_id = $head_ps_id;        // Step 1: Head of PS
    $step_2_approver_id = $leave_officer_id;  // Step 2: Leave Officer (final)
    $step_3_approver_id = null;
}

// 2. HEAD OF DEPARTMENT (designation_id = 1)
elseif ($designation_id == 1) {
    if ($hod_direct) {
        $step_1_status = 'approved'; // HOD skips own approval
    }
    $step_1_approver_id = $auth_officer_id;
    $step_2_approver_id = $leave_officer_id;
    $step_3_approver_id = null;
}

// 3. SUB-OFFICE HEAD OR SUB-OFFICE LEAVE OFFICER (designation 9 or 10)
elseif (in_array($designation_id, [9, 10]) && $office_type === 'sub') {
    $step_1_approver_id = $auth_officer_id;
    $step_2_approver_id = $leave_officer_id;
    $step_3_approver_id = null;
}

// 4. REGULAR SUB-OFFICE EMPLOYEE
elseif ($office_type === 'sub') {
    $step_1_approver_id = getSubOfficeHead($conn, $sub_office);
    $step_2_approver_id = $auth_officer_id;
    $step_3_approver_id = null;
}

// 5. REGULAR HEAD OFFICE EMPLOYEE
else {
    $hod_id = getDepartmentHead($conn, $department_id);
    $step_1_approver_id = $hod_id ?: $auth_officer_id;
    $step_2_approver_id = $auth_officer_id;
    $step_3_approver_id = $leave_officer_id;
}

// ============== HELPER FUNCTIONS ==============
function getSubOfficeHead($conn, $sub_office) {
    $stmt = $conn->prepare("SELECT ID FROM wp_pradeshiya_sabha_users WHERE designation_id = 9 AND sub_office = ? LIMIT 1");
    $stmt->bind_param("s", $sub_office);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $stmt->close();
    return $row['ID'] ?? 136; // fallback to main Authorized Officer
}

function getDepartmentHead($conn, $department_id) {
    if (!$department_id) return null;
    $stmt = $conn->prepare("SELECT ID FROM wp_pradeshiya_sabha_users WHERE designation_id = 1 AND department_id = ? LIMIT 1");
    $stmt->bind_param("i", $department_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $stmt->close();
    return $row['ID'] ?? null;
}

// ============== INSERT INTO DATABASE ==============
$sql = "INSERT INTO wp_leave_request (
    user_id, leave_type, leave_start_date, leave_end_date, number_of_days,
    reason, substitute, sub_office, office_type, department_id,
    step_1_approver_id, step_1_status,
    step_2_approver_id, step_2_status,
    step_3_approver_id, step_3_status,
    final_status
) VALUES (
    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
    ?, ?, ?, ?, ?, ?, ?
)";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "isssdsssssisisiss",
    $user_id,
    $leave_type,
    $start_date,
    $end_date,
    $number_of_days,
    $reason,
    $substitute,
    $sub_office,
    $office_type,
    $department_id,
    $step_1_approver_id,
    $step_1_status,
    $step_2_approver_id,
    $step_2_status,
    $step_3_approver_id,
    $step_3_status,
    $final_status
);

if ($stmt->execute()) {
    $msg = "Leave request submitted successfully!";
    if ($is_secretary) {
        $msg .= " It will be reviewed by the Head of Pradeshiya Sabha first.";
    } else {
        $msg .= " Awaiting approval.";
    }
    $_SESSION['success_message'] = $msg;
} else {
    error_log("Leave request failed: " . $stmt->error);
    $_SESSION['error_message'] = "Failed to submit request. Please try again later.";
}

$stmt->close();
$conn->close();

header("Location: leave_request.php");
exit();