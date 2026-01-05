<?php
session_start();
require "../includes/dbconfig.php";
require "../includes/user-navbar.php";

if (!isset($_SESSION['user']) || $_SESSION['user']['id'] != 19) {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user']['id'];

// ====================== HANDLE APPROVE / REJECT WITH DEBUG ======================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_id'], $_POST['action'])) {
    $request_id = (int)$_POST['request_id'];
    $action = $_POST['action'];
    $remark = trim($_POST['remark'] ?? '');


    if (!in_array($action, ['approve', 'reject'])) {
        echo "<div class='bg-red-100 p-4 mb-4 rounded'>Invalid action.</div>";
    } else {
        $new_status = $action === 'approve' ? 'approved' : 'rejected';
        $remark_to_save = ($action === 'reject' && !empty($remark)) ? $remark : null;

        $sql = "
            UPDATE wp_leave_request 
            SET 
                step_1_approver_id = ?,
                step_1_status = ?,
                step_1_date = NOW(),
                final_status = ?,
                rejection_remark = ?
            WHERE request_id = ? AND step_1_approver_id = ?
        ";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            echo "<div class='bg-red-500 text-white p-4 mb-4 rounded'>Prepare failed: " . $conn->error . "</div>";
        } else {
            $stmt->bind_param("isssii", $user_id, $new_status, $new_status, $remark_to_save, $request_id, $user_id);
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    echo "<div class='bg-green-100 text-green-700 p-4 mb-4 rounded'>Success! Leave request $new_status. Affected rows: " . $stmt->affected_rows . "</div>";
                } else {
                    echo "<div class='bg-orange-100 text-orange-700 p-4 mb-4 rounded'>No rows updated. Perhaps already processed or wrong request_id.</div>";
                }
            } else {
                echo "<div class='bg-red-500 text-white p-4 mb-4 rounded'>Execute failed: " . $stmt->error . "</div>";
            }
            $stmt->close();
        }
    }

    // Continue to show the list
}

// ====================== FETCH PENDING REQUESTS ======================
$pending_sql = "
    SELECT 
        lr.*,
        u.first_name,
        u.last_name,
        u.email,
        d.department_name
    FROM wp_leave_request lr
    JOIN wp_pradeshiya_sabha_users u ON lr.user_id = u.ID
    LEFT JOIN wp_departments d ON u.department_id = d.department_id
    WHERE lr.step_1_approver_id = ? AND lr.step_1_status = 'pending'
    ORDER BY lr.created_at DESC
";

$stmt = $conn->prepare($pending_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$pending_result = $stmt->get_result();

// Processed
$processed_sql = "
    SELECT 
        lr.*,
        u.first_name,
        u.last_name,
        u.email,
        d.department_name
    FROM wp_leave_request lr
    JOIN wp_pradeshiya_sabha_users u ON lr.user_id = u.ID
    LEFT JOIN wp_departments d ON u.department_id = d.department_id
    WHERE lr.step_1_approver_id = ? AND lr.final_status IN ('approved', 'rejected')
    ORDER BY lr.step_1_date DESC
";

$stmt2 = $conn->prepare($processed_sql);
$stmt2->bind_param("i", $user_id);
$stmt2->execute();
$processed_result = $stmt2->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secretary - Leave Approval (Debug Mode)</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="max-w-7xl mx-auto p-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-8">Secretary Approval - Debug Version</h1>

        <div class="border-b border-gray-200 mb-8">
            <nav class="-mb-px flex space-x-8">
                <a href="?tab=pending" class="<?= ($_GET['tab'] ?? 'pending') === 'pending' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500' ?> py-4 px-1 border-b-2 font-medium text-lg">
                    Requested (<?= $pending_result->num_rows ?>)
                </a>
                <a href="?tab=processed" class="<?= ($_GET['tab'] ?? 'pending') === 'processed' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500' ?> py-4 px-1 border-b-2 font-medium text-lg">
                    Processed (<?= $processed_result->num_rows ?>)
                </a>
            </nav>
        </div>

        <?php if (($_GET['tab'] ?? 'pending') === 'pending'): ?>
            <?php if ($pending_result->num_rows > 0): ?>
                <table class="min-w-full bg-white shadow rounded-lg">
                    <thead class="bg-purple-600 text-white">
                        <tr>
                            <th class="px-6 py-3 text-left">HOD</th>
                            <th class="px-6 py-3 text-left">Type</th>
                            <th class="px-6 py-3 text-left">Dates</th>
                            <th class="px-6 py-3 text-center">Days</th>
                            <th class="px-6 py-3 text-left">Reason</th>
                            <th class="px-6 py-3 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $pending_result->fetch_assoc()): ?>
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-6 py-4"><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
                                <td class="px-6 py-4"><?= htmlspecialchars($row['leave_type']) ?></td>
                                <td class="px-6 py-4"><?= date('d M Y', strtotime($row['leave_start_date'])) ?> - <?= date('d M Y', strtotime($row['leave_end_date'])) ?></td>
                                <td class="px-6 py-4 text-center"><?= number_format($row['number_of_days'], 1) ?></td>
                                <td class="px-6 py-4"><?= htmlspecialchars($row['reason']) ?></td>
                                <td class="px-6 py-4 text-center">
                                    <form method="POST">
                                        <input type="hidden" name="request_id" value="<?= $row['request_id'] ?>">
                                        <button type="submit" name="action" value="approve" class="bg-green-600 text-white px-4 py-2 rounded mr-2">
                                            Approve
                                        </button>
                                        <button type="button" onclick="this.nextElementSibling.classList.toggle('hidden')" class="bg-red-600 text-white px-4 py-2 rounded">
                                            Reject
                                        </button>
                                        <div class="hidden mt-2">
                                            <textarea name="remark" class="w-full border p-2 rounded" rows="3" placeholder="Rejection reason"></textarea>
                                            <button type="submit" name="action" value="reject" class="mt-2 bg-red-700 text-white px-4 py-2 rounded w-full">
                                                Confirm Reject
                                            </button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-center text-gray-600 text-xl mt-12">No pending requests.</p>
            <?php endif; ?>
        <?php else: ?>
            <!-- Processed tab similar -->
            <?php if ($processed_result->num_rows > 0): ?>
                <table class="min-w-full bg-white shadow rounded-lg">
                    <!-- Similar table for processed -->
                    <thead class="bg-gray-700 text-white">
                        <tr>
                            <th class="px-6 py-3 text-left">HOD</th>
                            <th class="px-6 py-3 text-left">Type</th>
                            <th class="px-6 py-3 text-left">Dates</th>
                            <th class="px-6 py-3 text-center">Days</th>
                            <th class="px-6 py-3 text-center">Final Status</th>
                            <th class="px-6 py-3 text-left">Decided On</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $processed_result->fetch_assoc()): ?>
                            <tr class="border-b">
                                <td class="px-6 py-4"><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
                                <td class="px-6 py-4"><?= htmlspecialchars($row['leave_type']) ?></td>
                                <td class="px-6 py-4"><?= date('d M Y', strtotime($row['leave_start_date'])) ?> - <?= date('d M Y', strtotime($row['leave_end_date'])) ?></td>
                                <td class="px-6 py-4 text-center"><?= number_format($row['number_of_days'], 1) ?></td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-4 py-1 rounded-full text-white <?= $row['final_status'] === 'approved' ? 'bg-green-600' : 'bg-red-600' ?>">
                                        <?= ucfirst($row['final_status']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4"><?= date('d M Y h:i A', strtotime($row['step_1_date'])) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-center text-gray-600 text-xl mt-12">No processed requests.</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>