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

// Only allow users with designation_id=10 and department_id=6
if ($designation_id == 10 && $department_id == 6) {
    // Fetch leave requests already approved by Step 1 (approved by desig 6 or 9, dept 6)
    $stmt = $conn->prepare("
        SELECT lr.*, u.first_name, u.last_name
        FROM wp_leave_request lr
        JOIN wp_pradeshiya_sabha_users u ON lr.user_id = u.ID
        WHERE lr.office_type = 'sub'
        AND lr.sub_office = ?
        AND lr.step_1_status = 'approved'
        AND lr.step_2_status = 'pending'
    ");
    $stmt->bind_param("s", $sub_office);
    $stmt->execute();
    $result = $stmt->get_result();
    $approval_step = 2;
} else {
    // Others cannot access this page
    echo '<div class="min-h-screen bg-gray-50 flex items-center justify-center px-4">
            <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8 text-center">
                <div class="mb-4">
                    <svg class="mx-auto h-16 w-16 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.728-.833-2.498 0L4.316 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Access Denied</h2>
                <p class="text-gray-600">You are not authorized to view this page.</p>
            </div>
          </div>';
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Approval - Step 2</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#3b82f6',
                        secondary: '#64748b',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <!-- Header Section -->
        <div class="mb-8">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between flex-wrap gap-4">
                    <div>
                        <h1 class="text-2xl lg:text-3xl font-bold text-gray-900">Leave Requests</h1>
                        <p class="text-gray-600 mt-1">Sub-Office Step 2 Approval</p>
                    </div>
                    <div class="flex items-center space-x-2 text-sm text-gray-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>Step 2 Approval Required</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile Card View (Hidden on larger screens) -->
        <div class="lg:hidden space-y-4">
            <?php 
            $result->data_seek(0); // Reset result pointer for mobile view
            $hasRequests = false;
            while ($row = $result->fetch_assoc()): 
                $hasRequests = true;
            ?>
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="p-6">
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">
                                    <?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?>
                                </h3>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mt-1">
                                    <?= htmlspecialchars($row['leave_type']) ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="space-y-3 mb-6">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-gray-500">Duration</span>
                                <span class="text-sm text-gray-900"><?= $row['number_of_days'] ?> days</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-gray-500">Start Date</span>
                                <span class="text-sm text-gray-900"><?= date('M d, Y', strtotime($row['leave_start_date'])) ?></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-gray-500">End Date</span>
                                <span class="text-sm text-gray-900"><?= date('M d, Y', strtotime($row['leave_end_date'])) ?></span>
                            </div>
                            <?php if (!empty($row['substitute'])): ?>
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-gray-500">Substitute</span>
                                <span class="text-sm text-gray-900"><?= htmlspecialchars($row['substitute']) ?></span>
                            </div>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($row['reason'])): ?>
                        <div class="mb-6">
                            <p class="text-sm font-medium text-gray-500 mb-1">Reason</p>
                            <p class="text-sm text-gray-700 bg-gray-50 p-3 rounded-lg">
                                <?= htmlspecialchars($row['reason']) ?>
                            </p>
                        </div>
                        <?php endif; ?>

                        <form action="suboffice-leave-action2.php" method="POST" class="flex space-x-3">
                            <input type="hidden" name="request_id" value="<?= $row['request_id'] ?>">
                            <input type="hidden" name="step" value="<?= $approval_step ?>">
                            <button type="submit" name="status" value="approved" 
                                    class="flex-1 bg-green-600 hover:bg-green-700 text-white font-medium py-2.5 px-4 rounded-lg transition duration-200 flex items-center justify-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span>Approve</span>
                            </button>
                            <button type="submit" name="status" value="rejected" 
                                    class="flex-1 bg-red-600 hover:bg-red-700 text-white font-medium py-2.5 px-4 rounded-lg transition duration-200 flex items-center justify-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                <span>Reject</span>
                            </button>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
            
            <?php if (!$hasRequests): ?>
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center">
                    <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No Pending Requests</h3>
                    <p class="text-gray-500">There are no leave requests awaiting your approval at this time.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Desktop Table View (Hidden on mobile) -->
        <div class="hidden lg:block">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <?php 
                $result->data_seek(0); // Reset result pointer for desktop view
                $hasRequests = false;
                $requests = [];
                while ($row = $result->fetch_assoc()) {
                    $hasRequests = true;
                    $requests[] = $row;
                }
                ?>
                
                <?php if ($hasRequests): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Leave Type</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duration</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Period</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Substitute</th>
                                <th class="px-6 py-4 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($requests as $row): ?>
                            <tr class="hover:bg-gray-50 transition-colors duration-200">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <?= htmlspecialchars($row['leave_type']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?= $row['number_of_days'] ?> days
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <div><?= date('M d, Y', strtotime($row['leave_start_date'])) ?></div>
                                    <div class="text-xs text-gray-500">to <?= date('M d, Y', strtotime($row['leave_end_date'])) ?></div>
                                </td>
                                <td class="px-6 py-4 max-w-xs">
                                    <div class="text-sm text-gray-900 truncate" title="<?= htmlspecialchars($row['reason']) ?>">
                                        <?= htmlspecialchars($row['reason']) ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?= htmlspecialchars($row['substitute']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <form action="suboffice-leave-action2.php" method="POST" class="flex justify-center space-x-2">
                                        <input type="hidden" name="request_id" value="<?= $row['request_id'] ?>">
                                        <input type="hidden" name="step" value="<?= $approval_step ?>">
                                        <button type="submit" name="status" value="approved" 
                                                class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition duration-200">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            Approve
                                        </button>
                                        <button type="submit" name="status" value="rejected" 
                                                class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition duration-200">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                            Reject
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="p-12 text-center">
                    <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No Pending Requests</h3>
                    <p class="text-gray-500">There are no leave requests awaiting your approval at this time.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Optional: Add confirmation dialogs -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('form[action="suboffice-leave-action2.php"]');
            
            forms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    const status = e.submitter.value;
                    const employeeName = form.closest('tr')?.querySelector('td:first-child')?.textContent.trim() || 
                                       form.closest('.bg-white')?.querySelector('h3')?.textContent.trim();
                    
                    const action = status === 'approved' ? 'approve' : 'reject';
                    const message = `Are you sure you want to ${action} the leave request${employeeName ? ' for ' + employeeName : ''}?`;
                    
                    if (!confirm(message)) {
                        e.preventDefault();
                    }
                });
            });
        });
    </script>
</body>
</html>