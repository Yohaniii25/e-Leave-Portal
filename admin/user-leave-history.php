<?php
session_start();
require '../includes/dbconfig.php';
require '../includes/admin-navbar.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['designation'] !== 'Admin') {
    header("Location: ../index.php");
    exit();
}

// === DELETE LOGIC (SAME PAGE) ===
$deleteSuccess = $deleteError = '';
if (isset($_GET['delete_id']) && isset($_GET['id'])) {
    $requestId = (int)$_GET['delete_id'];
    $userId = (int)$_GET['id'];

    $conn->autocommit(false);
    try {
        // === VERIFY LEAVE EXISTS & IS APPROVED ===
        $stmt = $conn->prepare("
            SELECT request_id, status 
            FROM wp_leave_request 
            WHERE request_id = ? AND user_id = ?
        ");
        $stmt->bind_param("ii", $requestId, $userId);
        $stmt->execute();
        $leave = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$leave) {
            throw new Exception("Leave request not found.");
        }
        if ((int)$leave['status'] !== 2) {
            throw new Exception("Only approved leaves can be deleted.");
        }

        // === DELETE FROM wp_leave_request ONLY ===
        $stmt = $conn->prepare("DELETE FROM wp_leave_request WHERE request_id = ?");
        $stmt->bind_param("i", $requestId);
        if (!$stmt->execute()) {
            throw new Exception("Failed to delete leave record.");
        }
        $stmt->close();

        $conn->commit();
        $deleteSuccess = "Leave deleted successfully. Balance updated automatically.";
    } catch (Exception $e) {
        $conn->rollback();
        $deleteError = $e->getMessage();
    }
    $conn->autocommit(true);
}

// === GET USER ID ===
if (!isset($_GET['id'])) {
    echo "No user ID provided.";
    exit();
}
$userId = (int)$_GET['id'];

// === FETCH USER NAME ===
$stmt = $conn->prepare("SELECT first_name, last_name FROM wp_pradeshiya_sabha_users WHERE ID = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    echo "User not found.";
    exit();
}

// === FETCH LEAVE HISTORY ===
$stmt = $conn->prepare("
    SELECT * FROM wp_leave_request
    WHERE user_id = ?
    ORDER BY created_at DESC
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave History - <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="max-w-6xl mx-auto bg-white p-6 mt-8 rounded-xl shadow-lg">
        <div class="flex justify-between items-center mb-6 border-b pb-4">
            <h2 class="text-2xl font-bold text-gray-800">
                Leave History:
                <span class="text-blue-600"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></span>
            </h2>
            <a href="javascript:history.back()" class="text-blue-600 hover:underline text-sm">
                Back
            </a>
        </div>

        <!-- Success / Error Messages -->
        <?php if ($deleteSuccess): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <?= htmlspecialchars($deleteSuccess) ?>
            </div>
        <?php endif; ?>
        <?php if ($deleteError): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <?= htmlspecialchars($deleteError) ?>
            </div>
        <?php endif; ?>

        <!-- Leave Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full border border-gray-200 rounded-lg">
                <thead class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white">
                    <tr>
                        <th class="px-4 py-3 text-left text-sm font-semibold">Type</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold">Start</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold">End</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold">Days</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold">Status</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold">Reason</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold">Submitted</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <?php
                                $status = (int)$row['status'];
                                $statusMap = [
                                    1 => ['Pending', 'text-yellow-600 bg-yellow-100'],
                                    2 => ['Approved', 'text-green-600 bg-green-100'],
                                    3 => ['Rejected', 'text-red-600 bg-red-100']
                                ];
                                [$statusText, $statusStyle] = $statusMap[$status] ?? ['Unknown', 'text-gray-600 bg-gray-100'];
                                $canDelete = ($status === 2);
                            ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-3 text-sm border-b"><?= htmlspecialchars($row['leave_type']) ?></td>
                                <td class="px-4 py-3 text-sm border-b"><?= htmlspecialchars($row['leave_start_date']) ?></td>
                                <td class="px-4 py-3 text-sm border-b"><?= htmlspecialchars($row['leave_end_date']) ?></td>
                                <td class="px-4 py-3 text-sm border-b font-medium text-center">
                                    <?= number_format((float)$row['number_of_days'], 1) ?>
                                </td>
                                <td class="px-4 py-3 text-sm border-b">
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold <?= $statusStyle ?>">
                                        <?= $statusText ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm border-b max-w-xs truncate" title="<?= htmlspecialchars($row['reason']) ?>">
                                    <?= htmlspecialchars(substr($row['reason'], 0, 50)) . (strlen($row['reason']) > 50 ? '...' : '') ?>
                                </td>
                                <td class="px-4 py-3 text-sm border-b text-gray-600">
                                    <?= date('d M Y', strtotime($row['created_at'])) ?>
                                </td>
                                <td class="px-4 py-3 text-sm border-b text-center">
                                    <?php if ($canDelete): ?>
                                        <button onclick="confirmDelete(<?= $row['request_id'] ?>, <?= $userId ?>)"
                                            class="text-red-600 hover:text-red-800 font-medium text-xs transition">
                                            Delete
                                        </button>
                                    <?php else: ?>
                                        <span class="text-gray-400 text-xs">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="px-6 py-10 text-center text-gray-500 text-sm">
                                No leave history found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmDelete(leaveId, userId) {
            Swal.fire({
                title: 'Delete this leave?',
                text: "This will remove the record and restore balance automatically.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then(result => {
                if (result.isConfirmed) {
                    window.location.href = `?id=${userId}&delete_id=${leaveId}`;
                }
            });
        }

        // Auto-hide success message
        document.addEventListener('DOMContentLoaded', () => {
            const success = document.querySelector('.bg-green-100');
            if (success) {
                setTimeout(() => {
                    success.style.transition = 'opacity 0.5s';
                    success.style.opacity = '0';
                    setTimeout(() => success.remove(), 500);
                }, 5000);
            }
        });
    </script>

    <?php require '../includes/admin-footer.php'; ?>
</body>
</html>

<?php
// Close connections
if (isset($stmt)) $stmt->close();
$conn->close();
?>