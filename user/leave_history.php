<?php
session_start();
require '../includes/dbconfig.php';

if (!isset($_SESSION['user']) || ($_SESSION['user']['designation'] !== 'Employee' && $_SESSION['user']['designation'] !== 'Head Of Department')) {
    header("Location: ../index.php");
    exit();
}


$user_id = $_SESSION['user']['id'];
$full_name = $_SESSION['user']['first_name'] . ' ' . $_SESSION['user']['last_name'];

if (isset($_POST['delete_pending_id'])) {
    $leave_id = (int)$_POST['delete_pending_id'];

    $delete = $conn->prepare("DELETE FROM wp_leave_request WHERE request_id = ? AND user_id = ? AND final_status = 'pending'");
    $delete->bind_param("ii", $leave_id, $user_id);
    if ($delete->execute()) {
        $_SESSION['success_message'] = "Leave request deleted.";
    } else {
        $_SESSION['error_message'] = "Failed to delete leave.";
    }
    $delete->close();
    header("Location: leave_history.php");
    exit();
}

if (isset($_POST['delete_request_id'])) {
    $leave_id = (int)$_POST['delete_request_id'];
    $reason = trim($_POST['delete_reason'] ?? '');

    if (empty($reason)) {
        $_SESSION['error_message'] = "Reason is required.";
    } else {
        $check = $conn->prepare("
            SELECT request_id, leave_start_date, created_at 
            FROM wp_leave_request 
            WHERE request_id = ? AND user_id = ? AND final_status = 'approved'
        ");
        if (!$check) {
            $_SESSION['error_message'] = "DB Error: " . $conn->error;
            header("Location: leave_history.php");
            exit();
        }

        $check->bind_param("ii", $leave_id, $user_id);
        $check->execute();
        $res = $check->get_result();

        if ($res->num_rows === 0) {
            $_SESSION['error_message'] = "Leave not found or not approved.";
        } else {
            $leave = $res->fetch_assoc();
            $leave_start = new DateTime($leave['leave_start_date']);
            $now = new DateTime();
            $approval_time = new DateTime($leave['created_at']);
            $hours_since = $now->diff($approval_time)->h + ($now->diff($approval_time)->days * 24);
        }
        $check->close();
    }
    header("Location: leave_history.php");
    exit();
}


$pending_query = "
    SELECT request_id, leave_type, leave_start_date, leave_end_date, number_of_days, created_at 
    FROM wp_leave_request 
    WHERE user_id = ? AND final_status = 'pending'
    ORDER BY created_at DESC
";
$pending_stmt = $conn->prepare($pending_query);
$pending_stmt->bind_param("i", $user_id);
$pending_stmt->execute();
$pending_result = $pending_stmt->get_result();


$approved_query = "
    SELECT request_id, leave_type, leave_start_date, leave_end_date, number_of_days, created_at 
    FROM wp_leave_request 
    WHERE user_id = ? AND final_status = 'approved'
    ORDER BY created_at DESC
";
$approved_stmt = $conn->prepare($approved_query);
$approved_stmt->bind_param("i", $user_id);
$approved_stmt->execute();
$approved_result = $approved_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave History</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-50 min-h-screen font-sans">
    <?php include('../includes/user-navbar.php'); ?>

    <div class="max-w-6xl mx-auto mt-10 p-6 bg-white shadow-md rounded-xl space-y-8">

        <!-- Messages -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                <?= $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
            </div>
        <?php elseif (isset($_SESSION['error_message'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                <?= $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>

        <!-- PENDING LEAVES -->
        <div>
            <h2 class="text-xl font-bold text-orange-600 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Pending Leave Requests
            </h2>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border border-gray-300 rounded-md">
                    <thead class="bg-orange-100 text-orange-800 text-sm font-medium">
                        <tr>
                            <th class="py-3 px-4 text-left">Type</th>
                            <th class="py-3 px-4 text-left">Start</th>
                            <th class="py-3 px-4 text-left">End</th>
                            <th class="py-3 px-4 text-left">Days</th>
                            <th class="py-3 px-4 text-left">Applied</th>
                            <th class="py-3 px-4 text-left">Action</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700 text-sm">
                        <?php if ($pending_result->num_rows > 0): ?>
                            <?php while ($row = $pending_result->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="py-3 px-4 border-b"><?= htmlspecialchars($row['leave_type']) ?>
                                        <?php if ($row['number_of_days'] == 0.5): ?>
                                            <span class="text-xs bg-yellow-100 text-yellow-800 px-2 py-1 rounded ml-1">Half</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 px-4 border-b"><?= date('M d', strtotime($row['leave_start_date'])) ?></td>
                                    <td class="py-3 px-4 border-b"><?= date('M d, Y', strtotime($row['leave_end_date'])) ?></td>
                                    <td class="py-3 px-4 border-b font-medium">
                                        <?= $row['number_of_days'] == 0.5 ? '0.5' : $row['number_of_days'] ?>
                                    </td>
                                    <td class="py-3 px-4 border-b text-xs text-gray-500">
                                        <?= date('M d, Y h:i A', strtotime($row['created_at'])) ?>
                                    </td>
                                    <td class="py-3 px-4 border-b">
                                        <button onclick="confirmDelete(<?= $row['request_id'] ?>, 'pending')"
                                                class="text-red-600 hover:text-red-800 text-sm font-medium">
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center py-6 text-gray-500">No pending requests.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- APPROVED LEAVES -->
        <div>
            <h2 class="text-xl font-bold text-green-600 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Approved Leave History
            </h2>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border border-gray-300 rounded-md">
                    <thead class="bg-green-100 text-green-800 text-sm font-medium">
                        <tr>
                            <th class="py-3 px-4 text-left">Type</th>
                            <th class="py-3 px-4 text-left">Start</th>
                            <th class="py-3 px-4 text-left">End</th>
                            <th class="py-3 px-4 text-left">Days</th>
                            <th class="py-3 px-4 text-left">Applied</th>
                            <!-- <th class="py-3 px-4 text-left">Action</th> -->
                        </tr>
                    </thead>
                    <tbody class="text-gray-700 text-sm">
                        <?php if ($approved_result->num_rows > 0): ?>
                            <?php while ($row = $approved_result->fetch_assoc()): ?>
                                <?php
                                $can_request_delete = false;
                                $leave_start = new DateTime($row['leave_start_date']);
                                $now = new DateTime();
                                $approval_time = new DateTime($row['created_at']);
                                $hours_since = $now->diff($approval_time)->h + ($now->diff($approval_time)->days * 24);

                                if ($leave_start >= $now && $hours_since <= 24) {
                                    $can_request_delete = true;
                                }
                                ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="py-3 px-4 border-b"><?= htmlspecialchars($row['leave_type']) ?>
                                        <?php if ($row['number_of_days'] == 0.5): ?>
                                            <span class="text-xs bg-yellow-100 text-yellow-800 px-2 py-1 rounded ml-1">Half</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 px-4 border-b"><?= date('M d', strtotime($row['leave_start_date'])) ?></td>
                                    <td class="py-3 px-4 border-b"><?= date('M d, Y', strtotime($row['leave_end_date'])) ?></td>
                                    <td class="py-3 px-4 border-b font-medium">
                                        <?= $row['number_of_days'] == 0.5 ? '0.5' : $row['number_of_days'] ?>
                                    </td>
                                    <td class="py-3 px-4 border-b text-xs text-gray-500">
                                        <?= date('M d, Y h:i A', strtotime($row['created_at'])) ?>
                                    </td>

                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center py-6 text-gray-500">No approved leaves.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
    function confirmDelete(id, type) {
        Swal.fire({
            title: 'Delete this leave?',
            text: "This action cannot be undone.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, Delete'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `<input type="hidden" name="delete_pending_id" value="${id}">`;
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    </script>

    <?php
    $pending_stmt->close();
    $approved_stmt->close();
    $conn->close();
    ?>
</body>
</html>