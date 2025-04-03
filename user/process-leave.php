<?php
session_start();
require '../includes/dbconfig.php';

// PHPMailer
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

// Fetch Leave Balance
$leaveBalanceQuery = $conn->prepare("SELECT casual_leave_balance, sick_leave_balance, annual_leave_balance FROM wp_pradeshiya_sabha_users WHERE ID = ?");
$leaveBalanceQuery->bind_param("i", $user_id);
$leaveBalanceQuery->execute();
$leaveBalanceResult = $leaveBalanceQuery->get_result();
$leaveBalanceRow = $leaveBalanceResult->fetch_assoc();

$casual_leave_balance = $leaveBalanceRow['casual_leave_balance'];
$sick_leave_balance = $leaveBalanceRow['sick_leave_balance'];
$annual_leave_balance = $leaveBalanceRow['annual_leave_balance'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $leave_type = $_POST['leave_type'];
    $leave_start_date = $_POST['leave_start_date'];
    $leave_end_date = $_POST['leave_end_date'];
    $reason = $_POST['reason'];
    $substitute = $_POST['substitute'];

    // Calculate number of leave days
    $start = new DateTime($leave_start_date);
    $end = new DateTime($leave_end_date);
    $interval = $start->diff($end);
    $number_of_days = $interval->days + 1;

    if ($leave_type == "Casual Leave" && $number_of_days > $casual_leave_balance) {
        $_SESSION['error_message'] = "You don't have enough Casual Leave balance.";
        header("Location: leave_request.php");
        exit();
    } elseif ($leave_type == "Sick Leave" && $number_of_days > $sick_leave_balance) {
        $_SESSION['error_message'] = "You don't have enough Sick Leave balance.";
        header("Location: leave_request.php");
        exit();
    } elseif ($leave_type == "Annual Leave" && $number_of_days > $annual_leave_balance) {
        $_SESSION['error_message'] = "You don't have enough Annual Leave balance.";
        header("Location: leave_request.php");
        exit();
    }

    // Deduct leave balance
    if ($leave_type == "Casual Leave") {
        $new_balance = $casual_leave_balance - $number_of_days;
        $updateBalance = "UPDATE wp_pradeshiya_sabha_users SET casual_leave_balance = ? WHERE ID = ?";
    } elseif ($leave_type == "Sick Leave") {
        $new_balance = $sick_leave_balance - $number_of_days;
        $updateBalance = "UPDATE wp_pradeshiya_sabha_users SET sick_leave_balance = ? WHERE ID = ?";
    } else {
        $new_balance = $annual_leave_balance - $number_of_days;
        $updateBalance = "UPDATE wp_pradeshiya_sabha_users SET annual_leave_balance = ? WHERE ID = ?";
    }

    $balanceStmt = $conn->prepare($updateBalance);
    $balanceStmt->bind_param("ii", $new_balance, $user_id);
    $balanceStmt->execute();

    // Insert Leave Request
    $stmt = $conn->prepare("INSERT INTO wp_leave_request (user_id, leave_type, leave_start_date, leave_end_date, number_of_days, reason, substitute, sub_office, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pending')");
    $stmt->bind_param("isssisss", $user_id, $leave_type, $leave_start_date, $leave_end_date, $number_of_days, $reason, $substitute, $sub_office);

    if ($stmt->execute()) {
        $request_id = $stmt->insert_id;
        $admin_email = "yohanii725@gmail.com";

        // Send Email Notification
        $mail = new PHPMailer(true);
        try {
            $mail->setFrom('no-reply@yourdomain.com', 'Leave Management System');
            $mail->addAddress($admin_email);
            $mail->isHTML(true);
            $mail->Subject = "New Leave Request from $full_name";
            $mail->Body = "<html><body><h2>Leave Request Details</h2>
                            <p><strong>Employee Name:</strong> $full_name</p>
                            <p><strong>Department:</strong> $department</p>
                            <p><strong>Leave Type:</strong> $leave_type</p>
                            <p><strong>Leave Dates:</strong> $leave_start_date to $leave_end_date</p>
                            <p><strong>Number of Days:</strong> $number_of_days</p>
                            <p><strong>Reason:</strong> $reason</p>
                            <p><strong>Substitute:</strong> $substitute</p>
                            <p><a href='http://yourwebsite.com/approve_leave.php?request_id={$request_id}'
                                  style='background:green; color:white; padding:10px; text-decoration:none;'>Approve</a>
                               &nbsp;|&nbsp;
                               <a href='http://yourwebsite.com/reject_leave.php?request_id={$request_id}'
                                  style='background:red; color:white; padding:10px; text-decoration:none;'>Reject</a></p>
                           </body></html>";

            $mail->isMail();
            $mail->send();
            $_SESSION['success_message'] = "Leave request submitted successfully!";
        } catch (Exception $e) {
            error_log("Mail Error: " . $mail->ErrorInfo);
            $_SESSION['error_message'] = "Email sending failed!";
        }

        header("Location: leave_request.php");
        exit();
    } else {
        $_SESSION['error_message'] = "Error submitting leave request.";
        header("Location: request-leave.php");
        exit();
    }
}
