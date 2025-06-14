<?php
session_start();
require '../includes/dbconfig.php';
require '../includes/navbar.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../index.php");
    exit();
}

$user = $_SESSION['user'];
$designation_id = $user['designation_id'];
$department_id = $user['department_id'];
$sub_office = $user['sub_office'];
$user_id = $user['id'];

// Filter based on step and role
if (in_array($designation_id, [6, 9]) && $department_id == 6) {
    // Step 1 Approval
    $stmt = $conn->prepare("SELECT lr.*, u.first_name, u.last_name FROM wp_leave_request lr
        JOIN wp_pradeshiya_sabha_users u ON lr.user_id = u.ID
        WHERE lr.office_type = 'sub'
        AND lr.sub_office = ?
        AND lr.step_1_status = 'pending'");
    $stmt->bind_param("s", $sub_office);
    $approval_step = 1;
// } elseif ($designation_id == 8 && $department_id == 9) {
//     // Step 2 Approval
//     $stmt = $conn->prepare("SELECT lr.*, u.first_name, u.last_name FROM wp_leave_request lr
//         JOIN wp_pradeshiya_sabha_users u ON lr.user_id = u.ID
//         WHERE lr.office_type = 'sub'
//         AND lr.sub_office = ?
//         AND lr.step_1_status = 'approved'
//         AND lr.step_2_status = 'pending'");
//     $stmt->bind_param("s", $sub_office);
//     $approval_step = 2;
// } 
}else {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Access Denied</title>
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="bg-gray-50">
        <div class="min-h-screen flex items-center justify-center">
            <div class="bg-white p-8 rounded-lg shadow-md text-center">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
                <h1 class="text-xl font-semibold text-gray-900 mb-2">Access Denied</h1>
                <p class="text-gray-600">You are not authorized to approve sub-office leaves.</p>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit();
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sub-Office Leave Requests</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Sub-Office Leave Requests</h1>
                        <p class="text-sm text-gray-600 mt-1">
                            <?= $approval_step == 1 ? 'Step 1 - Initial Approval' : 'Step 2 - Final Approval' ?>
                        </p>
                    </div>
                    <div class="bg-blue-50 px-3 py-1 rounded-full">
                        <span class="text-sm font-medium text-blue-700">
                            <?= htmlspecialchars($sub_office) ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($result->num_rows == 0): ?>
            <!-- Empty State -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No pending leave requests</h3>
                <p class="text-gray-500">All leave requests have been processed.</p>
            </div>
        <?php else: ?>
            <!-- Leave Requests Table -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Leave Details</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duration</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Substitute</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-50 transition-colors duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div class="h-10 w-10 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center">
                                                    <span class="text-sm font-medium text-white">
                                                        <?= strtoupper(substr($row['first_name'], 0, 1) . substr($row['last_name'], 0, 1)) ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    <?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                <?= htmlspecialchars($row['leave_type']) ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <div class="space-y-1">
                                            <div class="flex items-center text-xs text-gray-500">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                                <?= date('M d, Y', strtotime($row['leave_start_date'])) ?>
                                            </div>
                                            <div class="flex items-center text-xs text-gray-500">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                                <?= date('M d, Y', strtotime($row['leave_end_date'])) ?>
                                            </div>
                                            <div class="text-sm font-medium text-gray-900">
                                                <?= $row['number_of_days'] ?> day<?= $row['number_of_days'] > 1 ? 's' : '' ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900 max-w-xs">
                                            <p class="truncate" title="<?= htmlspecialchars($row['reason']) ?>">
                                                <?= htmlspecialchars($row['reason']) ?>
                                            </p>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?= htmlspecialchars($row['substitute']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <div class="flex items-center justify-center space-x-2">
                                            <form action="suboffice-leave-action.php" method="POST" class="inline-block">
                                                <input type="hidden" name="request_id" value="<?= $row['request_id'] ?>">
                                                <input type="hidden" name="step" value="<?= $approval_step ?>">
                                                
                                                <div class="flex space-x-2">
                                                    <button name="action" value="approve" 
                                                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-150">
                                                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                        </svg>
                                                        Approve
                                                    </button>
                                                    
                                                    <button type="button" 
                                                            onclick="openRejectModal(<?= $row['request_id'] ?>, '<?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?>')"
                                                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-150">
                                                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                        </svg>
                                                        Reject
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Reject Modal -->
    <div id="rejectModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Reject Leave Request</h3>
                    <button onclick="closeRejectModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <div class="mb-4">
                    <p class="text-sm text-gray-600">
                        You are about to reject the leave request for <span id="employeeName" class="font-medium"></span>.
                    </p>
                </div>

                <form id="rejectForm" action="suboffice-leave-action.php" method="POST">
                    <input type="hidden" name="request_id" id="modalRequestId">
                    <input type="hidden" name="step" value="<?= $approval_step ?>">
                    <input type="hidden" name="action" value="reject">
                    
                    <div class="mb-4">
                        <label for="rejection_remark" class="block text-sm font-medium text-gray-700 mb-2">
                            Reason for rejection <span class="text-red-500">*</span>
                        </label>
                        <textarea 
                            name="rejection_remark" 
                            id="rejection_remark" 
                            rows="4" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="Please provide a clear reason for rejecting this leave request..."
                            required></textarea>
                    </div>
                    
                    <div class="flex items-center justify-end space-x-3">
                        <button type="button" 
                                onclick="closeRejectModal()"
                                class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            Reject Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openRejectModal(requestId, employeeName) {
            document.getElementById('modalRequestId').value = requestId;
            document.getElementById('employeeName').textContent = employeeName;
            document.getElementById('rejection_remark').value = '';
            document.getElementById('rejectModal').classList.remove('hidden');
            document.getElementById('rejection_remark').focus();
        }

        function closeRejectModal() {
            document.getElementById('rejectModal').classList.add('hidden');
        }

        // Close modal when clicking outside
        document.getElementById('rejectModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeRejectModal();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeRejectModal();
            }
        });
    </script>
</body>
</html>