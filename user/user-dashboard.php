<?php
session_start();
require '../includes/dbconfig.php';
require '../includes/user-navbar.php';

if (!isset($_SESSION['user']) || strcasecmp($_SESSION['user']['designation'], 'Employee') !== 0) {
    header("Location: ../index.php");
    exit();
}

$user_id       = $_SESSION['user']['id'];
$full_name     = $_SESSION['user']['first_name'] . ' ' . $_SESSION['user']['last_name'];
$sub_office    = $_SESSION['user']['sub_office'] ?? 'Head Office';

// === Get Current Casual & Sick Leave Balances ===
$stmt = $conn->prepare("
    SELECT casual_leave_balance, sick_leave_balance 
    FROM wp_pradeshiya_sabha_users 
    WHERE ID = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$bal = $stmt->get_result()->fetch_assoc();
$stmt->close();

$casual_balance = (float)($bal['casual_leave_balance'] ?? 21);
$sick_balance   = (float)($bal['sick_leave_balance'] ?? 24);

// === Calculate Used Approved Leaves ===
$stmt = $conn->prepare("
    SELECT leave_type, SUM(number_of_days) AS used 
    FROM wp_leave_request 
    WHERE user_id = ? AND final_status = 'approved' 
    GROUP BY leave_type
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();

$used_casual = $used_sick = 0;
while ($row = $res->fetch_assoc()) {
    if ($row['leave_type'] === 'Casual Leave') $used_casual = (float)$row['used'];
    if ($row['leave_type'] === 'Sick Leave')   $used_sick   = (float)$row['used'];
}
$stmt->close();

$remaining_casual = $casual_balance - $used_casual;
$remaining_sick   = $sick_balance   - $used_sick;

// === Get Leave History ===
$sql = "SELECT 
            leave_type, 
            leave_start_date, 
            leave_end_date, 
            number_of_days, 
            final_status, 
            created_at 
        FROM wp_leave_request 
        WHERE user_id = ? 
        ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
$leave_history = [];
if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $leave_history[] = $row;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Dashboard - Pannala Pradeshiya Sabha</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Header Section -->
        <div class="bg-white shadow-sm rounded-lg mb-6">
            <div class="px-6 py-8 border-l-4 border-blue-600">
                <div class="flex items-center justify-between flex-wrap gap-4">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 mb-1">Employee Dashboard</h1>
                        <p class="text-gray-600 text-lg">Welcome, <?= htmlspecialchars($full_name) ?></p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-500 uppercase tracking-wide mb-1">Sub-Office</p>
                        <p class="text-lg font-semibold text-gray-900"><?= htmlspecialchars($sub_office) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <a href="leave_request.php" class="bg-blue-600 hover:bg-gray-50 border-2 border-blue-600 rounded-lg p-6 transition-all duration-200 hover:shadow-md group">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-xl font-bold text-white mb-2 group-hover:text-blue-600">Request New Leave</h3>
                        <p class="text-white group-hover:text-blue-600">Apply for Casual, Sick, or Duty Leave</p>
                    </div>
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                </div>
            </a>

            <a href="https://pannalaps.lk/" target="_blank" class="bg-green-600 hover:bg-gray-50 border-2 border-green-600 rounded-lg p-6 transition-all duration-200 hover:shadow-md group">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-xl font-bold text-white mb-2 group-hover:text-green-600">Official Website</h3>
                        <p class="text-white group-hover:text-green-600">Visit Pannala Pradeshiya Sabha Portal</p>
                    </div>
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                    </svg>
                </div>
            </a>
        </div>

        <!-- Leave Balance Overview -->
        <div class="bg-white shadow-sm rounded-lg mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-900">Leave Balance Overview</h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 divide-y md:divide-y-0 md:divide-x divide-gray-200">
                
                <!-- Casual Leave -->
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide">Casual Leave</h3>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            Active
                        </span>
                    </div>
                    <div class="mb-4">
                        <div class="flex items-baseline">
                            <span class="text-4xl font-bold text-gray-900"><?= number_format($remaining_casual, 1) ?></span>
                            <span class="ml-2 text-lg text-gray-500">/ <?= $casual_balance ?> days</span>
                        </div>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2 mb-3">
                        <div class="bg-blue-600 h-2 rounded-full" style="width: <?= ($remaining_casual / $casual_balance) * 100 ?>%"></div>
                    </div>
                    <p class="text-sm text-gray-600">Used: <span class="font-semibold text-gray-900"><?= number_format($used_casual, 1) ?></span> days</p>
                </div>

                <!-- Sick Leave -->
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide">Sick Leave</h3>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            Active
                        </span>
                    </div>
                    <div class="mb-4">
                        <div class="flex items-baseline">
                            <span class="text-4xl font-bold text-gray-900"><?= number_format($remaining_sick, 1) ?></span>
                            <span class="ml-2 text-lg text-gray-500">/ <?= $sick_balance ?> days</span>
                        </div>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2 mb-3">
                        <div class="bg-green-600 h-2 rounded-full" style="width: <?= ($remaining_sick / $sick_balance) * 100 ?>%"></div>
                    </div>
                    <p class="text-sm text-gray-600">Used: <span class="font-semibold text-gray-900"><?= number_format($used_sick, 1) ?></span> days</p>
                </div>

                <!-- Duty Leave -->
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide">Duty Leave</h3>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                            Unlimited
                        </span>
                    </div>
                    <div class="mb-4">
                        <div class="flex items-baseline">
                            <span class="text-4xl font-bold text-gray-900">∞</span>
                            <span class="ml-2 text-lg text-gray-500">days</span>
                        </div>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2 mb-3">
                        <div class="bg-purple-600 h-2 rounded-full" style="width: 100%"></div>
                    </div>
                    <p class="text-sm text-gray-600">No limit — <span class="font-semibold text-gray-900">Tracked only</span></p>
                </div>

            </div>
        </div>

        <!-- Leave History -->
        <div class="bg-white shadow-sm rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-900">Leave Request History</h2>
            </div>

            <?php if (count($leave_history) > 0): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Leave Type</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Start Date</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">End Date</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Duration</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requested On</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($leave_history as $leave): ?>
                                <?php
                                $status = $leave['final_status'];
                                if ($status === 'approved') {
                                    $badge = 'bg-green-100 text-green-800';
                                    $text  = 'Approved';
                                } elseif ($status === 'rejected') {
                                    $badge = 'bg-red-100 text-red-800';
                                    $text  = 'Rejected';
                                } else {
                                    $badge = 'bg-yellow-100 text-yellow-800';
                                    $text  = 'Pending';
                                }
                                ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($leave['leave_type']) ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900"><?= date('d M Y', strtotime($leave['leave_start_date'])) ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900"><?= date('d M Y', strtotime($leave['leave_end_date'])) ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <div class="text-sm font-semibold text-gray-900"><?= number_format($leave['number_of_days'], 1) ?> days</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full <?= $badge ?>">
                                            <?= $text ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-500"><?= date('d M Y, h:i A', strtotime($leave['created_at'])) ?></div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="px-6 py-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h3 class="mt-4 text-lg font-medium text-gray-900">No leave requests yet</h3>
                    <p class="mt-2 text-gray-500">You haven't submitted any leave requests.</p>
                    <div class="mt-6">
                        <a href="leave_request.php" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                            Submit your first request
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>

    </div>

</body>
</html>