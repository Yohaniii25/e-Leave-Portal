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
    $approver_id = $user['ID'];
    $action = $_POST['action'];
    $remark = $_POST['remark'] ?? null;

    if (in_array($action, ['approve', 'reject'])) {
        $status = $action === 'approve' ? 'approved' : 'rejected';

        $update_sql = "
            UPDATE wp_leave_request 
            SET 
                step_1_status = ?,
                step_1_approver_id = ?,
                step_1_date = NOW(),
                rejection_remark = ?
            WHERE request_id = ?
        ";

        $stmt = $conn->prepare($update_sql);
        if ($stmt) {
            $remark_safe = $status === 'rejected' ? $remark : null;
            $stmt->bind_param("sisi", $status, $approver_id, $remark_safe, $request_id);
            $stmt->execute();
        } else {
            die("Prepare failed: " . $conn->error);
        }
    }
}

// Fetch leave requests
$sql = "
    SELECT lr.*, u.first_name, u.last_name, u.email, d.department_name
    FROM wp_leave_request lr
    JOIN wp_pradeshiya_sabha_users u ON lr.user_id = u.ID
    LEFT JOIN wp_departments d ON u.department_id = d.department_id
    WHERE u.department_id = ?
    ORDER BY lr.leave_start_date DESC
";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $department_id);
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
        Leave Requests for Department: 
        <span class="text-blue-600"><?= htmlspecialchars($user['department_id']) ?></span>
    </h1>

    <?php if ($result->num_rows > 0): ?>
        <div class="overflow-x-auto bg-white rounded-lg shadow-md">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-blue-600 text-white">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Employee Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Leave Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Start Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">End Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Days</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Reason</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-900"><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
                            <td class="px-6 py-4 text-sm text-gray-500"><?= htmlspecialchars($row['email']) ?></td>
                            <td class="px-6 py-4 text-sm text-gray-900"><?= htmlspecialchars($row['leave_type']) ?></td>
                            <td class="px-6 py-4 text-sm text-gray-900"><?= htmlspecialchars($row['leave_start_date']) ?></td>
                            <td class="px-6 py-4 text-sm text-gray-900"><?= htmlspecialchars($row['leave_end_date']) ?></td>
                            <td class="px-6 py-4 text-sm text-gray-900"><?= htmlspecialchars($row['number_of_days']) ?></td>
                            <td class="px-6 py-4 text-sm text-gray-700"><?= htmlspecialchars($row['reason']) ?></td>

                            <td class="px-6 py-4 text-sm font-semibold">
                                <?php if ($row['step_1_status'] === 'pending'): ?>
                                    <form method="POST" class="flex flex-col gap-2">
                                        <input type="hidden" name="request_id" value="<?= $row['request_id'] ?>">

                                        <button type="submit" name="action" value="approve"
                                            class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-sm">
                                            Approve
                                        </button>

                                        <div class="mt-2">
                                            <button type="button" onclick="toggleReject(this)"
                                                class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm">
                                                Reject
                                            </button>
                                            <div class="mt-2 hidden reject-box">
                                                <textarea name="remark" placeholder="Enter rejection reason..."
                                                    class="w-full p-2 border border-gray-300 rounded" required></textarea>
                                                <button type="submit" name="action" value="reject"
                                                    class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 mt-2 rounded text-sm">
                                                    Confirm Reject
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                <?php else: ?>
                                    <?= $row['step_1_status'] === 'approved'
                                        ? '<span class="text-green-600">Approved</span>'
                                        : '<span class="text-red-600">Rejected</span>' ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-gray-600">No leave requests found for your department.</p>
    <?php endif; ?>
</div>

<script>
function toggleReject(button) {
    const rejectBox = button.closest('div').querySelector('.reject-box');
    rejectBox.classList.toggle('hidden');
}
</script>

</body>
</html>
