<?php
session_start();
require '../includes/dbconfig.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Employee') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user']['id'];
$sub_office = $_SESSION['user']['sub_office'];
$full_name = $_SESSION['user']['first_name'] . ' ' . $_SESSION['user']['last_name'];
$department = $_SESSION['user']['department'] ?? '';


$balanceQuery = $conn->prepare("SELECT casual_leave_balance, sick_leave_balance FROM wp_pradeshiya_sabha_users WHERE ID = ?");
$balanceQuery->bind_param("i", $user_id);
$balanceQuery->execute();
$balances = $balanceQuery->get_result()->fetch_assoc();


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
    'Sick Leave'   => $balances['sick_leave_balance']   - $used['Sick Leave'],
];


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


    $stmt = $conn->prepare("INSERT INTO wp_leave_request (user_id, leave_type, leave_start_date, leave_end_date, number_of_days, reason, substitute, sub_office, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)");
    $stmt->bind_param("isssissi", $user_id, $leave_type, $start_date, $end_date, $days, $reason, $substitute, $sub_office);

    if ($stmt->execute()) {
        $request_id = $stmt->insert_id;

        if ($leave_type === 'Duty Leave') {
            // Increment the duty_leave_count in the users table
            $updateDutyLeave = $conn->prepare("
        UPDATE wp_pradeshiya_sabha_users
        SET duty_leave_count = duty_leave_count + ?
        WHERE ID = ?
    ");
            $updateDutyLeave->bind_param("ii", $days, $user_id);
            $updateDutyLeave->execute();
        }


        $mail = new PHPMailer(true);
        try {
            $mail->setFrom('no-reply@yourdomain.com', 'Leave Management System');
            $mail->addAddress('yohanii725@gmail.com');
            $mail->isHTML(true);
            $mail->Subject = "New Leave Request from $full_name";
            $mail->Body = "
                <html><body>
                <h2>Leave Request Details</h2>
                <p><strong>Employee Name:</strong> $full_name</p>
                <p><strong>Department:</strong> $department</p>
                <p><strong>Leave Type:</strong> $leave_type</p>
                <p><strong>Leave Dates:</strong> $start_date to $end_date</p>
                <p><strong>Number of Days:</strong> $days</p>
                <p><strong>Reason:</strong> $reason</p>
                <p><strong>Substitute:</strong> $substitute</p>
                <p>
                    <a href='http://yourwebsite.com/approve_leave.php?request_id={$request_id}' style='background:green; color:white; padding:10px;'>Approve</a>
                    &nbsp;|&nbsp;
                    <a href='http://yourwebsite.com/reject_leave.php?request_id={$request_id}' style='background:red; color:white; padding:10px;'>Reject</a>
                </p>
                </body></html>
            ";
            $mail->isMail();
            $mail->send();

            $_SESSION['success_message'] = "Leave request submitted successfully.";
        } catch (Exception $e) {
            error_log("Email error: " . $mail->ErrorInfo);
            $_SESSION['error_message'] = "Leave saved, but email not sent.";
        }
        header("Location: leave_request.php");
        exit();
    } else {
        $_SESSION['error_message'] = "Error submitting leave.";
        header("Location: leave_request.php");
        exit();
    }
}
