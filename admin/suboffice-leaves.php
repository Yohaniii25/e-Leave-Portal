<?php
session_start();
require '../includes/dbconfig.php';
require '../includes/navbar.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../index.php");
    exit();
}

$user = $_SESSION['user'];
$sub_office = $user['sub_office'];

// Fetch approved leaves for this sub-office
$stmt = $conn->prepare("
    SELECT lr.*, u.first_name, u.last_name
    FROM wp_leave_request lr
    JOIN wp_pradeshiya_sabha_users u ON lr.user_id = u.ID
    WHERE lr.office_type = 'sub'
    AND lr.sub_office = ?
    AND lr.step_1_status = 'approved'
    ORDER BY lr.leave_start_date DESC
");
$stmt->bind_param("s", $sub_office);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approved Leaves - <?= htmlspecialchars($sub_office) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="container mx-auto px-4 py-6 max-w-7xl">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-2">
                Approved Leave Requests
            </h1>
            <p class="text-gray-600 text-sm md:text-base">
                <?= htmlspecialchars($sub_office) ?>
            </p>
        </div>

        <?php if ($result->num_rows > 0): ?>
            <!-- Desktop Table View -->
            <div class="hidden lg:block bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Leave Type</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Start Date</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">End Date</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Days</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Substitute</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Approver</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                            <?= htmlspecialchars($row['leave_type']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?= date('M d, Y', strtotime($row['leave_start_date'])) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?= date('M d, Y', strtotime($row['leave_end_date'])) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                            <?= $row['number_of_days'] ?> days
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate" title="<?= htmlspecialchars($row['reason']) ?>">
                                        <?= htmlspecialchars($row['reason']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?= htmlspecialchars($row['substitute']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?= htmlspecialchars($row['step_1_approver_id']) ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Mobile Card View -->
            <div class="lg:hidden space-y-4">
                <?php 
                // Reset result pointer for mobile view
                $result->data_seek(0);
                while ($row = $result->fetch_assoc()): 
                ?>
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                        <div class="flex justify-between items-start mb-3">
                            <h3 class="text-lg font-semibold text-gray-900">
                                <?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?>
                            </h3>
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                <?= htmlspecialchars($row['leave_type']) ?>
                            </span>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-3 text-sm">
                            <div>
                                <span class="font-medium text-gray-700">Start Date:</span>
                                <p class="text-gray-600"><?= date('M d, Y', strtotime($row['leave_start_date'])) ?></p>
                            </div>
                            <div>
                                <span class="font-medium text-gray-700">End Date:</span>
                                <p class="text-gray-600"><?= date('M d, Y', strtotime($row['leave_end_date'])) ?></p>
                            </div>
                            <div>
                                <span class="font-medium text-gray-700">Duration:</span>
                                <p class="text-gray-600">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                        <?= $row['number_of_days'] ?> days
                                    </span>
                                </p>
                            </div>
                            <div>
                                <span class="font-medium text-gray-700">Substitute:</span>
                                <p class="text-gray-600"><?= htmlspecialchars($row['substitute']) ?></p>
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <span class="font-medium text-gray-700">Reason:</span>
                            <p class="text-gray-600 text-sm mt-1"><?= htmlspecialchars($row['reason']) ?></p>
                        </div>
                        
                        <div class="mt-3 pt-3 border-t border-gray-100">
                            <span class="font-medium text-gray-700 text-sm">Approved by:</span>
                            <p class="text-gray-600 text-sm"><?= htmlspecialchars($row['step_1_approver_id']) ?></p>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

        <?php else: ?>
            <!-- Empty State -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center">
                <div class="max-w-sm mx-auto">
                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No Approved Leaves</h3>
                    <p class="text-gray-500">No approved leave requests found for your sub-office.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>