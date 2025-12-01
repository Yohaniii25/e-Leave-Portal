<?php
session_start();
require '../includes/dbconfig.php';
require '../includes/navbar.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../index.php");
    exit();
}

$user = $_SESSION['user'];
$designation_id = $user['designation_id'] ?? 0;
$sub_office = $user['sub_office'] ?? 'Head Office';
$user_id = $user['id'];

// ============== ACCESS CONTROL: Only Sub-Office Head (9) or Leave Officer (10) ==============
if (!in_array($designation_id, [9, 10])) {
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
                <p class="text-xs text-gray-500 mt-4">Your Role ID: <?= $designation_id ?> | Sub-Office: <?= htmlspecialchars($sub_office) ?></p>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit();
}

// ============== DETERMINE APPROVAL STEP ==============
if ($designation_id == 9) {
    // Head of Sub-Office → Step 1
    $stmt = $conn->prepare("
        SELECT lr.*, u.first_name, u.last_name, u.email 
        FROM wp_leave_request lr
        JOIN wp_pradeshiya_sabha_users u ON lr.user_id = u.ID
        WHERE lr.office_type = 'sub'
          AND lr.sub_office = ?
          AND lr.step_1_status = 'pending'
        ORDER BY lr.created_at DESC
    ");
    $stmt->bind_param("s", $sub_office);
    $approval_step = 1;
    $page_title = "Step 1 - Initial Approval";
    $step_field = "step_1";
} 
elseif ($designation_id == 10) {
    // Sub-Office Leave Officer → Step 2
    $stmt = $conn->prepare("
        SELECT lr.*, u.first_name, u.last_name, u.email 
        FROM wp_leave_request lr
        JOIN wp_pradeshiya_sabha_users u ON lr.user_id = u.ID
        WHERE lr.office_type = 'sub'
          AND lr.sub_office = ?
          AND lr.step_1_status = 'approved'
          AND lr.step_2_status = 'pending'
        ORDER BY lr.created_at DESC
    ");
    $stmt->bind_param("s", $sub_office);
    $approval_step = 2;
    $page_title = "Step 2 - Final Approval";
    $step_field = "step_2";
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sub-Office Leave Requests - <?= htmlspecialchars($sub_office) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Sub-Office Leave Requests</h1>
                        <p class="text-sm text-gray-600 mt-1"><?= $page_title ?></p>
                    </div>
                    <div class="bg-blue-50 px-4 py-2 rounded-full">
                        <span class="text-sm font-bold text-blue-700">
                            <?= htmlspecialchars($sub_office) ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($result->num_rows == 0): ?>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No pending requests</h3>
                <p class="text-gray-500">All leave requests have been processed.</p>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Leave Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duration</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Substitute</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="h-10 w-10 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center text-white font-bold">
                                                <?= strtoupper(substr($row['first_name'], 0, 1) . substr($row['last_name'], 0, 1)) ?>
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-sm font-medium text-gray-900">
                                                    <?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-3 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                                            <?= htmlspecialchars($row['leave_type']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        <?= date('M d', strtotime($row['leave_start_date'])) ?> - 
                                        <?= date('M d, Y', strtotime($row['leave_end_date'])) ?><br>
                                        <span class="font-medium"><?= $row['number_of_days'] ?> day<?= $row['number_of_days'] > 1 ? 's' : '' ?></span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900 max-w-xs">
                                        <p class="truncate" title="<?= htmlspecialchars($row['reason']) ?>">
                                            <?= htmlspecialchars($row['reason']) ?>
                                        </p>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        <?= htmlspecialchars($row['substitute'] ?: '—') ?>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <form action="suboffice-leave-action.php" method="POST" class="inline-flex space-x-2">
                                            <input type="hidden" name="request_id" value="<?= $row['request_id'] ?>">
                                            <input type="hidden" name="step" value="<?= $approval_step ?>">

                                            <button name="action" value="approve"
                                                class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded hover:bg-green-700 transition">
                                                Approve
                                            </button>

                                            <button type="button" onclick="openRejectModal(<?= $row['request_id'] ?>, '<?= addslashes(htmlspecialchars($row['first_name'] . ' ' . $row['last_name'])) ?>')"
                                                class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded hover:bg-red-700 transition">
                                                Reject
                                            </button>
                                        </form>
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
    <div id="rejectModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Reject Leave Request</h3>
            <p class="text-sm text-gray-600 mb-4">
                You are rejecting the leave request of <span id="employeeName" class="font-semibold"></span>.
            </p>
            <form action="suboffice-leave-action.php" method="POST">
                <input type="hidden" name="request_id" id="modalRequestId">
                <input type="hidden" name="step" value="<?= $approval_step ?>">
                <input type="hidden" name="action" value="reject">

                <label class="block text-sm font-medium text-gray-700 mb-2">Reason for Rejection <span class="text-red-500">*</span></label>
                <textarea name="rejection_remark" id="rejection_remark" rows="4" required
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>

                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" onclick="closeRejectModal()" 
                        class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" 
                        class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                        Reject Request
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openRejectModal(id, name) {
            document.getElementById('modalRequestId').value = id;
            document.getElementById('employeeName').textContent = name;
            document.getElementById('rejection_remark').value = '';
            document.getElementById('rejectModal').classList.remove('hidden');
        }
        function closeRejectModal() {
            document.getElementById('rejectModal').classList.add('hidden');
        }
        // Close on outside click
        document.getElementById('rejectModal').addEventListener('click', function(e) {
            if (e.target === this) closeRejectModal();
        });
    </script>
</body>
</html>