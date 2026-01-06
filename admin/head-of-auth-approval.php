<?php
session_start();
require "../includes/dbconfig.php";
require "../includes/navbar.php";

if (!isset($_SESSION['user'])) {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user']['id'];
$designation_id = $_SESSION['user']['designation_id'] ?? 0;

$allowed_designations = [5, 3];
if (!in_array($designation_id, $allowed_designations)) {
    die("Access denied: This page is only for Authorized Officer.");
}

// ====================== HANDLE APPROVE / REJECT ======================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_id'], $_POST['action'])) {
    $request_id = (int)$_POST['request_id'];
    $action = $_POST['action'];
    $remark = trim($_POST['remark'] ?? '');

    if (!in_array($action, ['approve', 'reject'])) {
        $_SESSION['error_message'] = "Invalid action.";
        header("Location: head-of-auth-approval.php");
        exit();
    }

    $new_status = $action === 'approve' ? 'approved' : 'rejected';
    $remark_to_save = ($action === 'reject') ? $remark : null;

    $sql = "
        UPDATE wp_leave_request 
        SET 
            step_2_approver_id = ?,
            step_2_status = ?,
            step_2_date = NOW(),
            rejection_remark = ?
        WHERE request_id = ?
    ";

    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("issi", $user_id, $new_status, $remark_to_save, $request_id);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Leave request has been " . ucfirst($new_status) . ".";
            if ($action === 'approve') {
                $_SESSION['success_message'] .= " It has been sent to the Leave Officer for final approval.";
            }
        } else {
            $_SESSION['error_message'] = "Failed to update request.";
        }
        $stmt->close();
    } else {
        $_SESSION['error_message'] = "Database error.";
    }

    header("Location: head-of-auth-approval.php");
    exit();
}

// ====================== FETCH PENDING REQUESTS FOR STEP 2 ======================
// Exclude Secretary (user_id = 19) AND HODs (designation_id = 1)
$sql = "
    SELECT 
        lr.*,
        u.first_name,
        u.last_name,
        u.email,
        d.department_name
    FROM wp_leave_request lr
    JOIN wp_pradeshiya_sabha_users u ON lr.user_id = u.ID
    LEFT JOIN wp_departments d ON u.department_id = d.department_id
    WHERE lr.step_1_status = 'approved'
      AND lr.step_2_status = 'pending'
      AND lr.user_id != 19  -- Exclude Secretary
      AND (u.designation_id IS NULL OR u.designation_id != 1)  -- Exclude Head of Departments
    ORDER BY lr.created_at DESC
";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authorized Officer - Step 2 Approval</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="max-w-7xl mx-auto p-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-8">
            Step 2: Pending Leave Requests (Regular Employees Only)
        </h1>

        <!-- Messages -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded">
                <?= htmlspecialchars($_SESSION['success_message']) ?>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
                <?= htmlspecialchars($_SESSION['error_message']) ?>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <div class="bg-amber-50 border-l-4 border-amber-500 p-4 mb-8 rounded">
            <p class="text-amber-800 font-medium">
                <strong>Note:</strong> You only review leaves from regular employees. 
                HOD and Secretary leaves are handled through separate approval paths and are not visible here.
            </p>
        </div>

        <?php if ($result->num_rows > 0): ?>
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-indigo-600 text-white">
                            <tr>
                                <th class="px-6 py-4 text-left">Employee</th>
                                <th class="px-6 py-4 text-left">Department</th>
                                <th class="px-6 py-4 text-left">Leave Type</th>
                                <th class="px-6 py-4 text-left">Dates</th>
                                <th class="px-6 py-4 text-center">Days</th>
                                <th class="px-6 py-4 text-left">Reason</th>
                                <th class="px-6 py-4 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 font-medium text-gray-900">
                                        <?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?>
                                    </td>
                                    <td class="px-6 py-4 text-gray-700">
                                        <?= htmlspecialchars($row['department_name'] ?? 'N/A') ?>
                                    </td>
                                    <td class="px-6 py-4 text-gray-900"><?= htmlspecialchars($row['leave_type']) ?></td>
                                    <td class="px-6 py-4 text-gray-700">
                                        <?= date('d M Y', strtotime($row['leave_start_date'])) ?> → 
                                        <?= date('d M Y', strtotime($row['leave_end_date'])) ?>
                                    </td>
                                    <td class="px-6 py-4 text-center font-bold">
                                        <?= number_format($row['number_of_days'], 1) ?>
                                    </td>
                                    <td class="px-6 py-4 text-gray-700 max-w-md truncate">
                                        <?= htmlspecialchars($row['reason']) ?>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <form method="POST" action="head-of-auth-approval.php" class="space-y-3">
                                            <input type="hidden" name="request_id" value="<?= $row['request_id'] ?>">

                                            <button type="submit" name="action" value="approve"
                                                onclick="return confirm('Approve this leave? It will go to Leave Officer for final approval.')"
                                                class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-lg font-medium transition">
                                                Approve
                                            </button>

                                            <button type="button"
                                                onclick="this.closest('form').querySelector('.reject-section').classList.toggle('hidden')"
                                                class="bg-red-600 hover:bg-red-700 text-white px-5 py-2 rounded-lg font-medium transition">
                                                Reject
                                            </button>

                                            <div class="reject-section hidden mt-3">
                                                <textarea name="remark" placeholder="Reason for rejection"
                                                    class="w-full p-3 border border-gray-300 rounded" rows="3"></textarea>
                                                <button type="submit" name="action" value="reject"
                                                    onclick="if(this.closest('form').querySelector('textarea[name=remark]').value.trim() === '') { alert('Please provide a reason for rejection'); return false; } return confirm('Reject this leave?')"
                                                    class="mt-2 bg-red-700 hover:bg-red-800 text-white px-5 py-2 rounded-lg font-medium w-full transition">
                                                    Confirm Reject
                                                </button>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-xl shadow-lg p-16 text-center text-gray-600">
                <p class="text-2xl">No leave requests pending your approval.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>