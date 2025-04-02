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
            
            <!-- <p class="text-gray-700">Available Leave Balance: <strong><?php echo $leave_balance; ?> days</strong></p> -->

            <?php if (isset($success_message)): ?>
                <p class="text-green-600 mt-4"><?php echo $success_message; ?></p>
            <?php elseif (isset($error_message)): ?>
                <p class="text-red-600 mt-4"><?php echo $error_message; ?></p>
            <?php endif; ?>

            <form method="POST" action="process-leave.php" class="mt-6">
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
