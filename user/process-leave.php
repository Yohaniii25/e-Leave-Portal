<?php

include ('../includes/dbconfig.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $employee_name = htmlspecialchars($_POST['employee_name']);
    $email = htmlspecialchars($_POST['email']);
    $leave_date = htmlspecialchars($_POST['leave_date']);
    $reason = htmlspecialchars($_POST['reason']);

    // HR Email Address
    $hr_email = "yohanii725@gmail.com";  // Replace with actual HR email

    // Email Subject & Message
    $subject = "New Leave Request from $employee_name";
    $message = "
        <html>
        <head>
            <title>Leave Request</title>
        </head>
        <body>
            <p><strong>Employee Name:</strong> $employee_name</p>
            <p><strong>Email:</strong> $email</p>
            <p><strong>Leave Date:</strong> $leave_date</p>
            <p><strong>Reason:</strong> $reason</p>
            <p><a href='http://yourwebsite.com/approve_leave.php?email=$email&date=$leave_date'>Approve</a> |
               <a href='http://yourwebsite.com/reject_leave.php?email=$email&date=$leave_date'>Reject</a></p>
        </body>
        </html>
    ";

    // Email Headers
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: no-reply@yourdomain.com" . "\r\n"; // Change to your domain

    // Send Email
    if (mail($hr_email, $subject, $message, $headers)) {
        echo "Leave request submitted successfully!";
    } else {
        echo "Error submitting leave request.";
    }
}
?>