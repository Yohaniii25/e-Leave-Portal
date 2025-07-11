<?php
session_start();
require '../includes/dbconfig.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../vendor/autoload.php';

if (!isset($_SESSION['user']) || strcasecmp($_SESSION['user']['designation'], 'Employee') !== 0) {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user']['id'];
$sub_office = $_SESSION['user']['sub_office'];
$full_name = $_SESSION['user']['first_name'] . ' ' . $_SESSION['user']['last_name'];
$department_id = $_SESSION['user']['department_id'] ?? '';

// ✅ Determine office_type based on sub_office value
$sub_offices = ['Pannala Sub-Office', 'Makandura Sub-Office', 'Yakkwila Sub-Office', 'Hamangalla Sub-Office'];
$office_type = in_array($sub_office, $sub_offices) ? 'sub' : 'head';

// Fetch leave balances
$balanceQuery = $conn->prepare("SELECT casual_leave_balance, sick_leave_balance FROM wp_pradeshiya_sabha_users WHERE ID = ?");
$balanceQuery->bind_param("i", $user_id);
$balanceQuery->execute();
$balances = $balanceQuery->get_result()->fetch_assoc();

// Fetch used leave (pending or approved)
$used = ['Casual Leave' => 0, 'Sick Leave' => 0];
$usageQuery = $conn->prepare("
    SELECT leave_type, SUM(number_of_days) AS total_requested 
    FROM wp_leave_request 
    WHERE user_id = ? AND status IN (1, 2) 
    GROUP BY leave_type
");
$usageQuery->bind_param("i", $user_id);
$usageQuery->execute();
$usageResult = $usageQuery->get_result();
while ($row = $usageResult->fetch_assoc()) {
    $used[$row['leave_type']] = $row['total_requested'];
}

$remaining = [
    'Casual Leave' => $balances['casual_leave_balance'] - $used['Casual Leave'],
    'Sick Leave'   => $balances['sick_leave_balance'] - $used['Sick Leave'],
];

// ✅ Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $leave_type = $_POST['leave_type'];
    $start_date = $_POST['leave_start_date'];
    $end_date = $_POST['leave_end_date'];
    $reason = $_POST['reason'];
    $substitute = $_POST['substitute'];

    $start = new DateTime($start_date);
    $end = new DateTime($end_date);
    $days = $start->diff($end)->days + 1;

    if ($leave_type !== 'Duty Leave') {
        if (!isset($remaining[$leave_type])) {
            $_SESSION['error_message'] = "Invalid leave type selected.";
            header("Location: leave_request.php");
            exit();
        }

        if ($days > $remaining[$leave_type]) {
            $_SESSION['error_message'] = "You only have {$remaining[$leave_type]} day(s) left for {$leave_type}.";
            header("Location: leave_request.php");
            exit();
        }
    }

    // ✅ Insert leave request with office_type
    $stmt = $conn->prepare("
        INSERT INTO wp_leave_request (
            user_id, leave_type, leave_start_date, leave_end_date,
            number_of_days, reason, substitute, sub_office, department_id, office_type, status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $status = 1;
    $stmt->bind_param(
        "isssisssssi",
        $user_id,
        $leave_type,
        $start_date,
        $end_date,
        $days,
        $reason,
        $substitute,
        $sub_office,
        $department_id,
        $office_type,
        $status
    );

    if ($stmt->execute()) {
        $request_id = $stmt->insert_id;

        // ✅ If Duty Leave, update count immediately
        if ($leave_type === 'Duty Leave') {
            $updateDutyLeave = $conn->prepare("
                UPDATE wp_pradeshiya_sabha_users
                SET duty_leave_count = duty_leave_count + ?
                WHERE ID = ?
            ");
            $updateDutyLeave->bind_param("ii", $days, $user_id);
            $updateDutyLeave->execute();
        }

        $_SESSION['success_message'] = "Leave submitted successfully.";
        header("Location: leave_request.php");
        exit();
    } else {
        $_SESSION['error_message'] = "Error submitting leave.";
        header("Location: leave_request.php");
        exit();
    }
}
