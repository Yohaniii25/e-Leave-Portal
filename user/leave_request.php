<?php
session_start();
require '../includes/dbconfig.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['designation'] !== 'Employee') {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user']['id'];
$sub_office = $_SESSION['user']['sub_office'];
$full_name = $_SESSION['user']['first_name'] . ' ' . $_SESSION['user']['last_name'];
$department_id = $_SESSION['user']['department_id'] ?? null;

// === GET LEAVE BALANCE ===
$leaveQuery = $conn->prepare("
    SELECT leave_balance, casual_leave_balance, sick_leave_balance, duty_leave_count 
    FROM wp_pradeshiya_sabha_users WHERE ID = ?
");
$leaveQuery->bind_param("i", $user_id);
$leaveQuery->execute();
$leaveResult = $leaveQuery->get_result();
$leaveData = $leaveResult->fetch_assoc();
$leaveQuery->close();

if (!$leaveData) {
    die("No leave balance found for user ID: " . $user_id);
}

// === DEFINE BALANCE VARIABLES ===
$leaveBalance       = (float)($leaveData['leave_balance'] ?? 20);
$casual_balance     = (float)($leaveData['casual_leave_balance'] ?? 21);
$sick_balance       = (float)($leaveData['sick_leave_balance'] ?? 24);
$dutyLeaveCount     = (int)($leaveData['duty_leave_count'] ?? 0);

// === GET APPROVED LEAVES (status = 2) ===
$approvedQuery = $conn->prepare("
    SELECT leave_type, SUM(number_of_days) as total_approved
    FROM wp_leave_request
    WHERE user_id = ? AND status = 2
    GROUP BY leave_type
");
$approvedQuery->bind_param("i", $user_id);
$approvedQuery->execute();
$approvedResult = $approvedQuery->get_result();

$approved = ['Casual Leave' => 0, 'Sick Leave' => 0, 'Duty Leave' => 0];
while ($row = $approvedResult->fetch_assoc()) {
    $approved[$row['leave_type']] = (float)$row['total_approved'];
}
$approvedQuery->close();

// === REMAINING BALANCE ===
$remaining = [
    'Casual Leave' => $casual_balance - $approved['Casual Leave'],
    'Sick Leave'   => $sick_balance   - $approved['Sick Leave'],
];

// === TOTAL: Only Casual + Sick (Duty Leave NOT counted) ===
$totalApproved = $approved['Casual Leave'] + $approved['Sick Leave'];
$totalRemaining = $leaveBalance - $totalApproved;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Leave</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
</head>
<body class="bg-gray-50 min-h-screen font-sans">
    <?php include('../includes/user-navbar.php'); ?>

    <div class="container mx-auto py-4 md:py-8 px-4 max-w-4xl">
        <div class="bg-white p-4 md:p-8 rounded-xl shadow-lg border border-gray-100">
            <h2 class="text-2xl md:text-3xl font-bold text-gray-800 mb-4 md:mb-6 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mr-3 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Request Leave
            </h2>

            <!-- Messages -->
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded">
                    <p class="text-sm text-green-700"><?= $_SESSION['success_message']; unset($_SESSION['success_message']); ?></p>
                </div>
            <?php elseif (isset($_SESSION['error_message'])): ?>
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded">
                    <p class="text-sm text-red-700"><?= $_SESSION['error_message']; unset($_SESSION['error_message']); ?></p>
                </div>
            <?php endif; ?>

            <!-- Leave Balance Card -->
            <div class="mb-6 bg-blue-50 rounded-lg p-4 border border-blue-100">
                <h3 class="text-lg font-semibold text-gray-700 mb-3">Leave Balance</h3>

                <!-- Mobile -->
                <div class="md:hidden space-y-3">
                    <div class="bg-white p-3 rounded-lg shadow-sm">
                        <div class="flex justify-between"><span class="font-medium">Total Leave</span><span class="text-blue-600"><?= $leaveBalance ?></span></div>
                        <div class="flex justify-between text-xs text-gray-500"><span>Used: <?= $totalApproved ?></span><span>Left: <?= $totalRemaining ?></span></div>
                    </div>
                    <div class="bg-white p-3 rounded-lg shadow-sm">
                        <div class="flex justify-between"><span class="font-medium">Casual Leave</span><span class="text-blue-600"><?= $casual_balance ?></span></div>
                        <div class="flex justify-between text-xs text-gray-500"><span>Used: <?= $approved['Casual Leave'] ?></span><span>Left: <?= $remaining['Casual Leave'] ?></span></div>
                    </div>
                    <div class="bg-white p-3 rounded-lg shadow-sm">
                        <div class="flex justify-between"><span class="font-medium">Sick Leave</span><span class="text-blue-600"><?= $sick_leave_balance ?></span></div>
                        <div class="flex justify-between text-xs text-gray-500"><span>Used: <?= $approved['Sick Leave'] ?></span><span>Left: <?= $remaining['Sick Leave'] ?></span></div>
                    </div>
                    <div class="bg-white p-3 rounded-lg shadow-sm">
                        <div class="flex justify-between"><span class="font-medium">Duty Leave Count</span><span class="text-purple-600"><?= $dutyLeaveCount ?></span></div>
                        <div class="text-xs text-gray-500">Taken: <?= $approved['Duty Leave'] ?? 0 ?></div>
                    </div>
                </div>

                <!-- Desktop -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="min-w-full bg-white rounded-lg shadow">
                        <thead>
                            <tr class="bg-blue-600 text-white">
                                <th class="px-4 py-3 text-left">Type</th>
                                <th class="px-4 py-3 text-left">Total</th>
                                <th class="px-4 py-3 text-left">Used</th>
                                <th class="px-4 py-3 text-left">Remaining</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium">Total Leave</td>
                                <td class="px-4 py-3"><?= $leaveBalance ?></td>
                                <td class="px-4 py-3"><?= $totalApproved ?></td>
                                <td class="px-4 py-3 text-blue-600 font-medium"><?= $totalRemaining ?></td>
                            </tr>
                            <tr class="bg-gray-50 hover:bg-gray-100">
                                <td class="px-4 py-3 font-medium">Casual Leave</td>
                                <td class="px-4 py-3"><?= $casual_balance ?></td>
                                <td class="px-4 py-3"><?= $approved['Casual Leave'] ?></td>
                                <td class="px-4 py-3 text-blue-600 font-medium"><?= $remaining['Casual Leave'] ?></td>
                            </tr>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium">Sick Leave</td>
                                <td class="px-4 py-3"><?= $sick_balance ?></td>
                                <td class="px-4 py-3"><?= $approved['Sick Leave'] ?></td>
                                <td class="px-4 py-3 text-blue-600 font-medium"><?= $remaining['Sick Leave'] ?></td>
                            </tr>
                            <tr class="bg-purple-50 hover:bg-purple-100">
                                <td class="px-4 py-3 font-medium">Duty Leave Count</td>
                                <td class="px-4 py-3">—</td>
                                <td class="px-4 py-3 text-purple-600 font-medium"><?= $approved['Duty Leave'] ?? 0 ?></td>
                                <td class="px-4 py-3 text-purple-600 font-medium"><?= $dutyLeaveCount ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Form -->
            <div class="bg-gray-50 p-6 rounded-lg border border-gray-200">
                <h3 class="text-lg font-semibold text-gray-700 mb-4">Submit Leave Request</h3>
                <form method="POST" action="process-leave.php" class="space-y-4" id="leaveForm">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Leave Type</label>
                        <select name="leave_type" id="leave_type" required class="w-full rounded-md border-gray-300 py-2 px-3 border">
                            <option value="">--Select--</option>
                            <option value="Duty Leave">Duty Leave</option>
                            <option value="Sick Leave">Sick Leave</option>
                            <option value="Casual Leave">Casual Leave</option>
                        </select>
                    </div>

                    <div id="half_day_container" class="hidden p-3 bg-yellow-50 border border-yellow-200 rounded-md">
                        <label class="flex items-center">
                            <input type="checkbox" name="is_half_day" id="is_half_day" class="rounded text-blue-600">
                            <span class="ml-2 text-sm font-medium">Half Day (0.5 day)</span>
                        </label>
                        <small class="text-gray-500">Only for single-day Casual/Sick Leave</small>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Substitute (Optional)</label>
                        <input type="text" id="substitute" name="substitute" class="w-full rounded-md border-gray-300 py-2 px-3 border" placeholder="Name">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                            <input type="date" name="leave_start_date" id="start_date" required class="w-full rounded-md border-gray-300 py-2 px-3 border">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                            <input type="date" name="leave_end_date" id="end_date" required class="w-full rounded-md border-gray-300 py-2 px-3 border">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Reason</label>
                        <textarea name="reason" required class="w-full rounded-md border-gray-300 py-2 px-3 border h-24" placeholder="Details..."></textarea>
                    </div>

                    <div class="flex justify-end gap-3">
                        <button type="reset" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50">Reset</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md">Submit Request</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        const $type = $('#leave_type');
        const $half = $('#half_day_container');
        const $halfCheck = $('#is_half_day');

        function toggleHalf() {
            const val = $type.val();
            if (val === 'Casual Leave' || val === 'Sick Leave') {
                $half.removeClass('hidden');
            } else {
                $half.addClass('hidden');
                $halfCheck.prop('checked', false);
            }
        }

        $type.on('change', toggleHalf);
        toggleHalf();

        $('#leaveForm').on('submit', function(e) {
            if ($halfCheck.is(':checked')) {
                const s = new Date($('#start_date').val());
                const e = new Date($('#end_date').val());
                if ((e - s) / (1000*60*60*24) > 0) {
                    alert('Half day only for single day.');
                    e.preventDefault();
                }
            }
        });

        $("#substitute").autocomplete({
            source: 'search_users.php',
            minLength: 2,
            select: function(e, ui) { $("#substitute").val(ui.item.label); return false; }
        });
    });
    </script>
</body>
</html>