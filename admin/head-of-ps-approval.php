<?php
session_start();
require "../includes/dbconfig.php";

if (!isset($_SESSION['user'])) {
    header("Location: ../index.php");
    exit();
}

$user = $_SESSION['user'];
$department_id = $user['department_id'] ?? null;

if (!$department_id) {
    die("Error: Your department is not set. Contact admin.");
}

// Handle form submission (approve/reject)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_id'], $_POST['action'])) {
    $request_id = intval($_POST['request_id']);
    $approver_id = $user['id'];
    $action = $_POST['action'];
    $remark = $_POST['remark'] ?? null;

    if (in_array($action, ['approve', 'reject'])) {
        $status = $action === 'approve' ? 'approved' : 'rejected';

        // Check if this is a special user (Pradeshiya Sabha Division secretary with user_id 19)
        // Get user_id from the leave request
        $check_sql = "SELECT user_id FROM wp_leave_request WHERE request_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("i", $request_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        $leave_row = $check_result->fetch_assoc();
        $leave_user_id = $leave_row['user_id'] ?? 0;
        $check_stmt->close();

        // Special handling for Pradeshiya Sabha Division secretary (user_id 19)
        if ($leave_user_id === 19 && $status === 'approved') {
            // For special user: Head of PS approval is final approval
            $update_sql = "
                UPDATE wp_leave_request 
                SET 
                    step_2_status = ?,
                    step_2_approver_id = ?,
                    step_2_date = NOW(),
                    final_status = 'approved',
                    status = 2,
                    rejection_remark = ?
                WHERE request_id = ?
            ";
        } elseif ($leave_user_id === 19 && $status === 'rejected') {
            // For special user rejected: set final_status to rejected
            $update_sql = "
                UPDATE wp_leave_request 
                SET 
                    step_2_status = ?,
                    step_2_approver_id = ?,
                    step_2_date = NOW(),
                    final_status = 'rejected',
                    status = 3,
                    rejection_remark = ?
                WHERE request_id = ?
            ";
        } elseif ($department_id == 6) {
            // Normal Head of PS approval at step 2
            $update_sql = "
                UPDATE wp_leave_request 
                SET 
                    step_2_status = ?,
                    step_2_approver_id = ?,
                    step_2_date = NOW(),
                    rejection_remark = ?
                WHERE request_id = ?
            ";
        } else {
            // HOD approves/rejects at step 1
            $update_sql = "
                UPDATE wp_leave_request 
                SET 
                    step_1_status = ?,
                    step_1_approver_id = ?,
                    step_1_date = NOW(),
                    rejection_remark = ?
                WHERE request_id = ?
            ";
        }

        $stmt = $conn->prepare($update_sql);
        if ($stmt) {
            // Only store rejection remark if rejected
            $remark_safe = $status === 'rejected' ? $remark : null;
            $stmt->bind_param("sisi", $status, $approver_id, $remark_safe, $request_id);

            if ($stmt->execute()) {
                header("Location: head-of-ps-approval.php?status=success&type=$status");
                exit();
            } else {
                echo "Execute failed: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Prepare failed: " . $conn->error;
        }
    }
}

// Fetch leave requests based on user role
if ($department_id == 6) {
    // Head of PS — show ONLY secretary (user_id = 19) leaves that are pending at step 1
    $sql = "
        SELECT lr.*, u.first_name, u.last_name, u.email, d.department_name
        FROM wp_leave_request lr
        JOIN wp_pradeshiya_sabha_users u ON lr.user_id = u.ID
        LEFT JOIN wp_departments d ON u.department_id = d.department_id
        WHERE lr.user_id = 19 
          AND lr.step_1_status = 'pending'
          AND lr.step_1_approver_id = ?
        ORDER BY lr.leave_start_date DESC
    ";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("i", $user['id']);
} else {
    // Other departments — show pending leaves filtered by department at step 1
    $sql = "
        SELECT lr.*, u.first_name, u.last_name, u.email, d.department_name
        FROM wp_leave_request lr
        JOIN wp_pradeshiya_sabha_users u ON lr.user_id = u.ID
        LEFT JOIN wp_departments d ON u.department_id = d.department_id
        WHERE u.department_id = ? AND lr.step_1_status = 'pending'
        ORDER BY lr.leave_start_date DESC
    ";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("i", $department_id);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>HOD Leave Requests</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen">
    <?php include "../includes/navbar.php"; ?>

    <div class="max-w-7xl mx-auto p-6">
        <h1 class="text-3xl font-semibold mb-6 text-gray-800">
            Leave Requests
            <?php if ($department_id == 6): ?>
                <span class="text-blue-600">(Head of PS - Final Approval Only)</span>
            <?php else: ?>
                <span class="text-blue-600">for Department: <?= htmlspecialchars($user['department_id']) ?></span>
            <?php endif; ?>
        </h1>

        <?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
            <div id="alert" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                Leave request successfully <?= htmlspecialchars($_GET['type']) ?>.
                <?php if ($_GET['type'] === 'approved'): ?>
                    <span class="text-sm text-green-600"> This leave has been marked as fully approved.</span>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Info Box for Special User Leaves -->
        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6 rounded">
            <p class="text-sm text-blue-800">
                <strong>Note:</strong> Any leaves from the Pradeshiya Sabha Division Secretary will be marked as <strong>fully approved</strong> once you approve them. 
                Your approval is the final step for these requests.
            </p>
        </div>

        <?php if ($result->num_rows > 0): ?>
            <div class="overflow-x-auto bg-white rounded-lg shadow-md">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-blue-600 text-white">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Employee Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Department</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Start Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">End Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Days</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Reason</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <?php $isSpecialUser = ($row['user_id'] == 19); ?>
                            <tr class="hover:bg-gray-50 <?= $isSpecialUser ? 'bg-yellow-50' : '' ?>">
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?>
                                    <?php if ($isSpecialUser): ?>
                                        <span class="ml-2 inline-block px-2 py-1 text-xs font-semibold text-yellow-800 bg-yellow-200 rounded-full">Secretary</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500"><?= htmlspecialchars($row['email']) ?></td>
                                <td class="px-6 py-4 text-sm text-gray-700"><?= htmlspecialchars($row['department_name'] ?? 'N/A') ?></td>
                                <td class="px-6 py-4 text-sm text-gray-900"><?= htmlspecialchars($row['leave_type']) ?></td>
                                <td class="px-6 py-4 text-sm text-gray-900"><?= htmlspecialchars($row['leave_start_date']) ?></td>
                                <td class="px-6 py-4 text-sm text-gray-900"><?= htmlspecialchars($row['leave_end_date']) ?></td>
                                <td class="px-6 py-4 text-sm text-gray-900"><?= htmlspecialchars($row['number_of_days']) ?></td>
                                <td class="px-6 py-4 text-sm text-gray-700"><?= htmlspecialchars($row['reason']) ?></td>
                                <td class="px-6 py-4 text-sm font-semibold">
                                    <?php if ($isSpecialUser): ?>
                                        <span class="px-3 py-1 text-xs font-bold text-white bg-yellow-600 rounded">Final Approval</span>
                                    <?php else: ?>
                                        <span class="px-3 py-1 text-xs font-bold text-white bg-blue-600 rounded">Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-sm font-semibold">
                                    <form method="POST" action="" class="space-y-2">
                                        <input type="hidden" name="request_id" value="<?= htmlspecialchars($row['request_id']) ?>" />

                                        <div class="flex flex-col gap-2">
                                            <button type="submit" name="action" value="approve"
                                                onclick="return confirm('Are you sure you want to approve this leave request?')"
                                                class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-sm">
                                                Approve
                                            </button>

                                            <button type="button" onclick="toggleReject(this)"
                                                class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm">
                                                Reject
                                            </button>

                                            <div class="mt-2 hidden reject-box">
                                                <textarea name="remark" placeholder="Enter rejection reason..."
                                                    class="w-full p-2 border border-gray-300 rounded" rows="3"></textarea>
                                                <button type="submit" name="action" value="reject"
                                                    onclick="return confirm('Are you sure you want to reject this leave request?')"
                                                    class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 mt-2 rounded text-sm">
                                                    Confirm Reject
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-gray-600">
                <?php
                if ($department_id == 6) {
                    echo "No leave requests pending your approval.";
                } else {
                    echo "No pending leave requests found for your department.";
                }
                ?>
            </p>
        <?php endif; ?>
    </div>

    <script>
        function toggleReject(button) {
            const rejectBox = button.closest('form').querySelector('.reject-box');
            if (rejectBox) {
                rejectBox.classList.toggle('hidden');
            }
        }

        // Auto-hide success alert
        setTimeout(() => {
            const alert = document.getElementById('alert');
            if (alert) {
                alert.style.display = 'none';
            }
        }, 4000);
    </script>
</body>

</html>
