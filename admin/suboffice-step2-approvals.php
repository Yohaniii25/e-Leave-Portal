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

// CORRECT ACCESS: Only Sub-Office Leave Officer (designation_id = 10)
if ($designation_id !== 10) {
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
        <div class="min-h-screen flex items-center justify-center px-4">
            <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8 text-center">
                <div class="mb-4">
                    <svg class="mx-auto h-16 w-16 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.728-.833-2.498 0L4.316 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Access Denied</h2>
                <p class="text-gray-600">Only Sub-Office Leave Officer can access this page.</p>
                <p class="text-xs text-gray-500 mt-4">Your Role ID: <?= $designation_id ?></p>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit();
}

// Fetch Step 2 pending requests (after Step 1 approved)
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
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Step 2 Approval - <?= htmlspecialchars($sub_office) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-8 p-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-2xl lg:text-3xl font-bold text-gray-900">Step 2 - Final Approval</h1>
                    <p class="text-gray-600 mt-1">Review leave requests approved by Head of Sub-Office</p>
                </div>
                <div class="mt-4 md:mt-0">
                    <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-bold bg-orange-100 text-orange-800">
                        <?= htmlspecialchars($sub_office) ?>
                    </span>
                </div>
            </div>
        </div>

        <?php if ($result->num_rows == 0): ?>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
                <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No Pending Requests</h3>
                <p class="text-gray-500">All Step 1 approved requests have been processed.</p>
            </div>
        <?php else: ?>
            <!-- Desktop Table -->
            <div class="hidden lg:block bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employee</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dates</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Days</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reason</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Substitute</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php while ($row = $result->fetch_assoc()): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    <?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                                    <?= htmlspecialchars($row['leave_type']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <?= date('M d', strtotime($row['leave_start_date'])) ?> - 
                                <?= date('M d, Y', strtotime($row['leave_end_date'])) ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-center font-medium">
                                <?= $row['number_of_days'] ?>
                            </td>
                            <td class="px-6 py-4 text-sm max-w-xs">
                                <p class="truncate" title="<?= htmlspecialchars($row['reason']) ?>">
                                    <?= htmlspecialchars($row['reason']) ?>
                                </p>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <?= htmlspecialchars($row['substitute'] ?: '—') ?>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <form action="suboffice-leave-action.php" method="POST" class="inline-flex space-x-2">
                                    <input type="hidden" name="request_id" value="<?= $row['request_id'] ?>">
                                    <input type="hidden" name="step" value="2">
                                    <button name="action" value="approve" 
                                        class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
                                        Approve
                                    </button>
                                    <button name="action" value="reject" 
                                        class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 text-sm"
                                        onclick="return confirm('Reject this request?')">
                                        Reject
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Cards -->
            <div class="lg:hidden space-y-4">
                <?php $result->data_seek(0); while ($row = $result->fetch_assoc()): ?>
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="font-bold text-lg"><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></h3>
                    <div class="mt-2 text-sm space-y-2">
                        <div><strong>Type:</strong> <?= htmlspecialchars($row['leave_type']) ?></div>
                        <div><strong>Dates:</strong> <?= date('M d', strtotime($row['leave_start_date'])) ?> - <?= date('M d, Y', strtotime($row['leave_end_date'])) ?></div>
                        <div><strong>Days:</strong> <?= $row['number_of_days'] ?></div>
                        <div><strong>Reason:</strong> <?= htmlspecialchars($row['reason']) ?></div>
                        <div><strong>Substitute:</strong> <?= htmlspecialchars($row['substitute'] ?: 'None') ?></div>
                    </div>
                    <div class="mt-4 flex space-x-3">
                        <form action="suboffice-leave-action.php" method="POST" class="flex-1">
                            <input type="hidden" name="request_id" value="<?= $row['request_id'] ?>">
                            <input type="hidden" name="step" value="2">
                            <button name="action" value="approve" class="w-full bg-green-600 text-white py-2 rounded hover:bg-green-700">
                                Approve
                            </button>
                        </form>
                        <form action="suboffice-leave-action.php" method="POST" class="flex-1">
                            <input type="hidden" name="request_id" value="<?= $row['request_id'] ?>">
                            <input type="hidden" name="step" value="2">
                            <button name="action" value="reject" class="w-full bg-red-600 text-white py-2 rounded hover:bg-red-700"
                                onclick="return confirm('Reject this leave?')">
                                Reject
                            </button>
                        </form>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>