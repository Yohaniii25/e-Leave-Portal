<?php
session_start();
require '../includes/dbconfig.php';

// PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

// Check if user is logged in and is an Employee
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Employee') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user']['id'];
$sub_office = $_SESSION['user']['sub_office'];
$full_name = $_SESSION['user']['first_name'] . ' ' . $_SESSION['user']['last_name'];
$department = $_SESSION['user']['department'] ?? '';

// Fetch Leave Balances from wp_pradeshiya_sabha_users
$leaveQuery = $conn->prepare("
    SELECT leave_balance, casual_leave_balance, sick_leave_balance, annual_leave_balance 
    FROM wp_pradeshiya_sabha_users WHERE ID = ?
");

if (!$leaveQuery) {
    die("SQL Error: " . $conn->error);
}

$leaveQuery->bind_param("i", $user_id);
$leaveQuery->execute();
$leaveResult = $leaveQuery->get_result();
$leaveData = $leaveResult->fetch_assoc();

if (!$leaveData) {
    die("No leave balance found for user ID: " . $user_id);
}

// Store balances
$leaveBalance = $leaveData['leave_balance'] ?? 0;
$casualLeaveBalance = $leaveData['casual_leave_balance'] ?? 0;
$sickLeaveBalance = $leaveData['sick_leave_balance'] ?? 0;
$annualLeaveBalance = $leaveData['annual_leave_balance'] ?? 0;

// Fetch Approved Leave Days from wp_leave_request
$approvedQuery = $conn->prepare("
    SELECT SUM(number_of_days) AS approved_days 
    FROM wp_leave_request 
    WHERE user_id = ? AND status = 'Approved'
");

if (!$approvedQuery) {
    die("SQL Error: " . $conn->error);
}

$approvedQuery->bind_param("i", $user_id);
$approvedQuery->execute();
$approvedResult = $approvedQuery->get_result()->fetch_assoc();
$approvedLeaves = $approvedResult['approved_days'] ?? 0;

// Calculate Remaining Leave Balances
$remainingLeaveBalance = $leaveBalance - $approvedLeaves;
?>





<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Leave</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50 min-h-screen font-sans">
    <?php include('../includes/user-navbar.php'); ?>

    <div class="container mx-auto py-4 md:py-8 px-4 max-w-4xl">
        <div class="bg-white p-4 md:p-8 rounded-xl shadow-lg border border-gray-100">
            <h2 class="text-2xl md:text-3xl font-bold text-gray-800 mb-4 md:mb-6 flex flex-wrap items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 md:h-8 md:w-8 mr-2 md:mr-3 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Request Leave
            </h2>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="bg-green-50 border-l-4 border-green-500 p-3 md:p-4 mb-4 md:mb-6 rounded">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-green-700"><?php echo $_SESSION['success_message'];
                                                                unset($_SESSION['success_message']); ?></p>
                        </div>
                    </div>
                </div>
            <?php elseif (isset($_SESSION['error_message'])): ?>
                <div class="bg-red-50 border-l-4 border-red-500 p-3 md:p-4 mb-4 md:mb-6 rounded">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700"><?php echo $_SESSION['error_message'];
                                                            unset($_SESSION['error_message']); ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Leave Balance Card - Mobile Responsive -->
            <div class="mb-6 bg-blue-50 rounded-lg p-3 md:p-4 border border-blue-100">
                <h3 class="text-base md:text-lg font-semibold text-gray-700 mb-2 md:mb-3 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 md:h-5 md:w-5 mr-1 md:mr-2 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    Leave Balance
                </h3>

                <!-- Mobile view - Cards instead of table -->
                <div class="md:hidden space-y-3">
                    <div class="bg-white p-3 rounded-lg shadow-sm">
                        <div class="flex justify-between items-center mb-1">
                            <span class="font-medium text-gray-800">Total Leave</span>
                            <span class="font-medium text-blue-600"><?= $leaveBalance - ($casualLeaveBalance + $sickLeaveBalance + $annualLeaveBalance) ?> remaining</span>
                        </div>
                        <div class="flex justify-between text-xs text-gray-500">
                            <span>Balance: <?= $leaveBalance ?></span>
                            <span>Approved: <?= $leaveBalance - $casualLeaveBalance - $sickLeaveBalance - $annualLeaveBalance ?></span>
                        </div>
                    </div>

                    <div class="bg-white p-3 rounded-lg shadow-sm">
                        <div class="flex justify-between items-center mb-1">
                            <span class="font-medium text-gray-800">Casual Leave</span>
                            <span class="font-medium text-blue-600"><?= $casualLeaveBalance - 0 ?> remaining</span>
                        </div>
                        <div class="flex justify-between text-xs text-gray-500">
                            <span>Balance: <?= $casualLeaveBalance ?></span>
                            <span>Approved: <?= $casualLeaveBalance ?></span>
                        </div>
                    </div>

                    <div class="bg-white p-3 rounded-lg shadow-sm">
                        <div class="flex justify-between items-center mb-1">
                            <span class="font-medium text-gray-800">Sick Leave</span>
                            <span class="font-medium text-blue-600"><?= $sickLeaveBalance - 0 ?> remaining</span>
                        </div>
                        <div class="flex justify-between text-xs text-gray-500">
                            <span>Balance: <?= $sickLeaveBalance ?></span>
                            <span>Approved: <?= $sickLeaveBalance ?></span>
                        </div>
                    </div>

                    <div class="bg-white p-3 rounded-lg shadow-sm">
                        <div class="flex justify-between items-center mb-1">
                            <span class="font-medium text-gray-800">Annual Leave</span>
                            <span class="font-medium text-blue-600"><?= $annualLeaveBalance - 0 ?> remaining</span>
                        </div>
                        <div class="flex justify-between text-xs text-gray-500">
                            <span>Balance: <?= $annualLeaveBalance ?></span>
                            <span>Approved: <?= $annualLeaveBalance ?></span>
                        </div>
                    </div>
                </div>

                <!-- Desktop view - Table -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="min-w-full bg-white rounded-lg overflow-hidden shadow">
                        <thead>
                            <tr class="bg-blue-600 text-white">
                                <th class="px-4 py-3 text-left text-sm font-medium">Leave Type</th>
                                <th class="px-4 py-3 text-left text-sm font-medium">Balance</th>
                                <th class="px-4 py-3 text-left text-sm font-medium">Approved</th>
                                <th class="px-4 py-3 text-left text-sm font-medium">Remaining</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium">Total Leave</td>
                                <td class="px-4 py-3"><?= $leaveBalance ?></td>
                                <td class="px-4 py-3"><?= $leaveBalance - $casualLeaveBalance - $sickLeaveBalance - $annualLeaveBalance ?></td>
                                <td class="px-4 py-3 font-medium text-blue-600"><?= $leaveBalance - ($casualLeaveBalance + $sickLeaveBalance + $annualLeaveBalance) ?></td>
                            </tr>
                            <tr class="bg-gray-50 hover:bg-gray-100">
                                <td class="px-4 py-3 font-medium">Casual Leave</td>
                                <td class="px-4 py-3"><?= $casualLeaveBalance ?></td>
                                <td class="px-4 py-3"><?= $casualLeaveBalance ?></td>
                                <td class="px-4 py-3 font-medium text-blue-600"><?= $casualLeaveBalance - 0 ?></td>
                            </tr>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium">Sick Leave</td>
                                <td class="px-4 py-3"><?= $sickLeaveBalance ?></td>
                                <td class="px-4 py-3"><?= $sickLeaveBalance ?></td>
                                <td class="px-4 py-3 font-medium text-blue-600"><?= $sickLeaveBalance - 0 ?></td>
                            </tr>
                            <tr class="bg-gray-50 hover:bg-gray-100">
                                <td class="px-4 py-3 font-medium">Annual Leave</td>
                                <td class="px-4 py-3"><?= $annualLeaveBalance ?></td>
                                <td class="px-4 py-3"><?= $annualLeaveBalance ?></td>
                                <td class="px-4 py-3 font-medium text-blue-600"><?= $annualLeaveBalance - 0 ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Leave Request Form - Mobile Responsive -->
            <div class="bg-gray-50 p-4 md:p-6 rounded-lg border border-gray-200">
                <h3 class="text-base md:text-lg font-semibold text-gray-700 mb-3 md:mb-4 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 md:h-5 md:w-5 mr-1 md:mr-2 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Submit Leave Request
                </h3>
                <form method="POST" action="process-leave.php" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Leave Type</label>
                        <select name="leave_type" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 py-2 px-3 border">
                            <option value="">--Select Type--</option>
                            <option value="Annual Leave">Annual Leave</option>
                            <option value="Sick Leave">Sick Leave</option>
                            <option value="Casual Leave">Casual Leave</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Substitute (Optional)</label>
                        <input type="text" name="substitute" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 py-2 px-3 border" placeholder="Colleague's name">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                            <input type="date" name="leave_start_date" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 py-2 px-3 border">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                            <input type="date" name="leave_end_date" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 py-2 px-3 border">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Reason for Leave</label>
                        <textarea name="reason" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 py-2 px-3 border h-24" placeholder="Please provide details about your leave request..."></textarea>
                    </div>

                    <div class="flex flex-wrap gap-3 justify-end">
                        <button type="reset" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Reset
                        </button>
                        <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200 flex items-center">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Submit Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>