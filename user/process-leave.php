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
$leaveBalanceQuery = $conn->prepare("SELECT leave_balance FROM wp_pradeshiya_sabha_users WHERE ID = ?");
$leaveBalanceQuery->bind_param("i", $user_id);
$leaveBalanceQuery->execute();
$leaveBalanceResult = $leaveBalanceQuery->get_result();
$leaveBalanceRow = $leaveBalanceResult->fetch_assoc();
$leave_balance = $leaveBalanceRow['leave_balance'];

// Process form submission
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
    $number_of_days = $interval->days + 1; // Include start date

    if ($number_of_days > $leave_balance) {
        $_SESSION['error_message'] = "You don't have enough leave balance.";
        header("Location: leave_request.php");
        exit();
    } else {
        $stmt = $conn->prepare("INSERT INTO wp_leave_request (user_id, leave_type, leave_start_date, leave_end_date, number_of_days, reason, substitute, sub_office) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssisss", $user_id, $leave_type, $leave_start_date, $leave_end_date, $number_of_days, $reason, $substitute, $sub_office);

        if ($stmt->execute()) {
            $request_id = $stmt->insert_id;
            
            // Method 1: Simple mail() function - works on many PHP setups without additional configuration
            $hr_email = "yohanii725@gmail.com"; // HR/Admin email
            
            // Email Subject & Message
            $subject = "New Leave Request from $full_name";
            $message = "
                <html>
                <head>
                    <title>New Leave Request</title>
                </head>
                <body>
                    <h2>Leave Request Details</h2>
                    <p><strong>Employee Name:</strong> $full_name</p>
                    <p><strong>Department:</strong> $department</p>
                    <p><strong>Leave Type:</strong> $leave_type</p>
                    <p><strong>Leave Dates:</strong> $leave_start_date to $leave_end_date</p>
                    <p><strong>Number of Days:</strong> $number_of_days</p>
                    <p><strong>Reason:</strong> $reason</p>
                    <p><strong>Substitute:</strong> $substitute</p>
                    <p>
                        <a href='http://yourwebsite.com/approve_leave.php?request_id=$request_id' 
                           style='background:green; color:white; padding:10px; text-decoration:none;'>Approve</a>
                        &nbsp;|&nbsp;
                        <a href='http://yourwebsite.com/reject_leave.php?request_id=$request_id' 
                           style='background:red; color:white; padding:10px; text-decoration:none;'>Reject</a>
                    </p>
                </body>
                </html>
            ";

            // Email Headers
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= "From: no-reply@yourdomain.com" . "\r\n"; // Change to your domain

            // Send Email using PHP's mail() function
            if (mail($hr_email, $subject, $message, $headers)) {
                $_SESSION['success_message'] = "Leave request submitted successfully!";
            } else {
                // Fallback to logging the email if sending fails
                $logFile = '../email_logs.txt';
                $logContent = "--------- " . date('Y-m-d H:i:s') . " ---------\n";
                $logContent .= "To: $hr_email\n";
                $logContent .= "Subject: $subject\n";
                $logContent .= "Message: " . strip_tags($message) . "\n\n";
                file_put_contents($logFile, $logContent, FILE_APPEND);
                
                $_SESSION['success_message'] = "Leave request submitted successfully! (Email logged to file)";
            }

            header("Location: leave_request.php");
            exit();
        } else {
            $_SESSION['error_message'] = "Error submitting leave request.";
            header("Location: leave_request.php");
            exit();
        }
    }
} else {
    // If accessed directly without form submission
    header("Location: leave_request.php");
    exit();
}
?>