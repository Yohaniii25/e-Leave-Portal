<?php
session_start();
require '../includes/dbconfig.php';

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

    // Check if user has enough leave balance
    if ($number_of_days > $leave_balance) {
        $error_message = "You don't have enough leave balance.";
    } else {
        // Insert Leave Request
        $stmt = $conn->prepare("INSERT INTO wp_leave_request (user_id, leave_type, leave_start_date, leave_end_date, number_of_days, reason, substitute, sub_office) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssisss", $user_id, $leave_type, $leave_start_date, $leave_end_date, $number_of_days, $reason, $substitute, $sub_office);

        if ($stmt->execute()) {
            // Fetch HOD Email
            $hodQuery = $conn->prepare("SELECT email FROM wp_pradeshiya_sabha_users WHERE designation = 'HOD' AND department = ?");
            $hodQuery->bind_param("s", $department);
            $hodQuery->execute();
            $hodResult = $hodQuery->get_result();
            $hodRow = $hodResult->fetch_assoc();
            $hod_email = $hodRow['email'];

            if ($hod_email) {
                // Send Email to HOD
                $to = $hod_email;
                $subject = "New Leave Request from $full_name";
                $message = "Dear HOD,\n\n$full_name has requested leave from $leave_start_date to $leave_end_date.\n\nReason: $reason\n\nPlease log in to the system to approve or reject the request.\n\nBest Regards,\nLeave Management System";
                $headers = "From: no-reply@yourdomain.com";

                mail($to, $subject, $message, $headers);
            }

            $success_message = "Leave request submitted successfully! The HOD has been notified.";
        } else {
            $error_message = "Error submitting leave request.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Leave</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <?php include('../includes/user-navbar.php'); ?>

    <div class="container mx-auto mt-8">
        <div class="bg-white p-6 rounded-lg shadow-lg">
            <h2 class="text-3xl font-bold text-gray-800">Request Leave</h2>
            
            <p class="text-gray-700">Available Leave Balance: <strong><?php echo $leave_balance; ?> days</strong></p>

            <?php if (isset($success_message)): ?>
                <p class="text-green-600 mt-4"><?php echo $success_message; ?></p>
            <?php elseif (isset($error_message)): ?>
                <p class="text-red-600 mt-4"><?php echo $error_message; ?></p>
            <?php endif; ?>

            <form method="POST" action="" class="mt-6">
                <div class="mb-4">
                    <label class="block text-gray-700">Leave Type:</label>
                    <select name="leave_type" required class="w-full border rounded p-2">
                        <option value="Annual Leave">Annual Leave</option>
                        <option value="Sick Leave">Sick Leave</option>
                        <option value="Casual Leave">Casual Leave</option>
                        <option value="Maternity Leave">Maternity Leave</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700">Start Date:</label>
                    <input type="date" name="leave_start_date" required class="w-full border rounded p-2">
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700">End Date:</label>
                    <input type="date" name="leave_end_date" required class="w-full border rounded p-2">
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700">Reason:</label>
                    <textarea name="reason" required class="w-full border rounded p-2"></textarea>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700">Substitute (Optional):</label>
                    <input type="text" name="substitute" class="w-full border rounded p-2">
                </div>

                <button type="submit" class="bg-blue-600 text-white p-2 rounded hover:bg-blue-700">Submit Leave Request</button>
            </form>
        </div>
    </div>
</body>
</html>
