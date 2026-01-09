<?php
session_start();
require '../includes/dbconfig.php';
require '../includes/user-navbar.php';

if (!isset($_SESSION['user']) || strcasecmp($_SESSION['user']['designation'], 'Employee') !== 0) {
    header("Location: ../index.php");
    exit();
}

$sub_office = $_SESSION['user']['sub_office'] ?? 'Head Office';

// Restrict access: Only Head Office employees can view this page
if ($sub_office !== 'Head Office') {
    header("Location: employee_dashboard.php");
    exit();
}

$full_name  = $_SESSION['user']['first_name'] . ' ' . $_SESSION['user']['last_name'];

// Get filter date from user input
$filter_date = isset($_GET['filter_date']) ? trim($_GET['filter_date']) : '';
$filter_mode = isset($_GET['filter_mode']) ? trim($_GET['filter_mode']) : 'all'; // 'all', 'on_leave'

// Fetch all approved leaves in Head Office
$sql = "
    SELECT 
        r.request_id,
        r.leave_type,
        r.leave_start_date,
        r.leave_end_date,
        r.number_of_days,
        r.created_at,
        u.first_name,
        u.last_name,
        u.designation
    FROM wp_leave_request r
    JOIN wp_pradeshiya_sabha_users u ON r.user_id = u.ID
    WHERE r.sub_office = 'Head Office'
      AND r.final_status = 'approved'
";

// Apply date filter if provided
if (!empty($filter_date)) {
    $filter_date_formatted = date('Y-m-d', strtotime($filter_date));
    $sql .= " AND r.leave_start_date <= '" . $conn->real_escape_string($filter_date_formatted) . "'
        AND r.leave_end_date >= '" . $conn->real_escape_string($filter_date_formatted) . "'";
}

$sql .= " ORDER BY r.leave_start_date DESC, r.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

$approved_leaves = [];
while ($row = $result->fetch_assoc()) {
    $approved_leaves[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approved Leaves - Head Office</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Header Section -->
        <div class="bg-white shadow-sm rounded-lg mb-6">
            <div class="px-6 py-8 border-l-4 border-teal-600">
                <div class="flex items-center justify-between flex-wrap gap-4">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 mb-1">Head Office Approved Leaves</h1>
                        <p class="text-gray-600 text-lg">All approved leave requests in the Head Office</p>
                    </div>
                    <div class="text-right">
                        <a href="employee_dashboard.php" class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                            Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="bg-white shadow-sm rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Filter by Date</h2>
                <form method="GET" class="flex flex-wrap gap-4 items-end">
                    <div class="flex-1 min-w-64">
                        <label for="filter_date" class="block text-sm font-medium text-gray-700 mb-2">
                            Select Date (to see who is on leave)
                        </label>
                        <input 
                            type="date" 
                            id="filter_date" 
                            name="filter_date" 
                            value="<?= isset($_GET['filter_date']) ? htmlspecialchars($_GET['filter_date']) : '' ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent"
                        >
                    </div>
                    <div class="flex gap-2">
                        <button 
                            type="submit" 
                            class="inline-flex items-center px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-lg transition font-medium"
                        >
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            Filter
                        </button>
                        <a 
                            href="view_head_office_approved_leaves.php" 
                            class="inline-flex items-center px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded-lg transition font-medium"
                        >
                            Clear
                        </a>
                    </div>
                </form>
                <?php if (!empty($filter_date)): ?>
                    <div class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                        <p class="text-sm text-blue-800">
                            <strong>Showing employees on leave on:</strong> <?= date('F d, Y', strtotime($filter_date)) ?>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Approved Leaves Table -->
        <div class="bg-white shadow-sm rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-900">Approved Leave Requests</h2>
            </div>

            <?php if (count($approved_leaves) > 0): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Designation</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">From</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">To</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Days</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requested On</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($approved_leaves as $leave): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                        <?= htmlspecialchars($leave['first_name'] . ' ' . $leave['last_name']) ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        <?= htmlspecialchars($leave['designation'] ?? 'Employee') ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        <?= htmlspecialchars($leave['leave_type']) ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        <?= date('d M Y', strtotime($leave['leave_start_date'])) ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        <?= date('d M Y', strtotime($leave['leave_end_date'])) ?>
                                    </td>
                                    <td class="px-6 py-4 text-center text-sm font-semibold text-gray-900">
                                        <?= number_format($leave['number_of_days'], 1) ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        <?= date('d M Y, h:i A', strtotime($leave['created_at'])) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="px-6 py-12 text-center text-gray-500">
                    <p class="text-lg">No approved leave requests found in Head Office yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>