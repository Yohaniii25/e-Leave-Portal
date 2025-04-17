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
        header("Location: leave_request.php");
        exit();
    }