<?php
session_start();
require '../includes/dbconfig.php';
require '../includes/admin-navbar.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}

// ✅ Get logged-in admin's sub_office
$admin_office = $_SESSION['user']['sub_office'];

$sql = "SELECT r.*, u.first_name, u.last_name, u.designation, u.department 
        FROM wp_leave_request r
        JOIN wp_pradeshiya_sabha_users u ON r.user_id = u.ID
        WHERE u.sub_office = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $admin_office);
$stmt->execute();
$result = $stmt->get_result();


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>e-Leave_Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script> <!-- Icons -->
</head>

<body>
    <div class="max-w-7xl mx-auto bg-white shadow-md rounded-lg p-6">
        <h2 class="text-2xl font-bold mb-6 text-center text-blue-800">Manage Leave Requests</h2>

        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-200">
                <thead>
                    <tr class="bg-blue-100 text-left text-gray-700">

                        <th class="px-4 py-2 border">User ID</th>
                        <th class="py-2 px-4 border">Employee</th>
                        <th class="py-2 px-4 border">Department</th>
                        <th class="py-2 px-4 border">Leave Type</th>
                        <th class="py-2 px-4 border">From</th>
                        <th class="py-2 px-4 border">To</th>
                        <th class="py-2 px-4 border">Reason</th>
                        <th class="py-2 px-4 border">Status</th>
                        <th class="py-2 px-4 border">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr class="border-b">
                            <td class="px-4 py-2 border text-blue-600 underline">
                                <a href="view-user.php?id=<?= $row['user_id']; ?>" target="_blank"><?= $row['user_id']; ?></a>
                            </td>

                            <td class="py-2 px-4 border"><?php echo $row['first_name'] . ' ' . $row['last_name']; ?></td>
                            <td class="py-2 px-4 border"><?php echo $row['department']; ?></td>
                            <td class="py-2 px-4 border"><?php echo $row['leave_type']; ?></td>
                            <td class="py-2 px-4 border"><?php echo $row['leave_start_date']; ?></td>
                            <td class="py-2 px-4 border"><?php echo $row['leave_end_date']; ?></td>
                            <td class="py-2 px-4 border"><?php echo $row['reason']; ?></td>
                            <td class="py-2 px-4 border font-semibold 
    <?php
                        if ($row['status'] == 2) echo 'text-green-600';
                        elseif ($row['status'] == 3) echo 'text-red-600';
                        else echo 'text-yellow-600';
    ?>">
                                <?php
                                if ($row['status'] == 1) echo 'Pending';
                                elseif ($row['status'] == 2) echo 'Approved';
                                elseif ($row['status'] == 3) echo 'Rejected';
                                ?>

                                <?php if ($row['status'] == 3 && !empty($row['rejection_remark'])): ?>
                                    <div class="text-sm text-gray-600 italic mt-1">Remark: <?= htmlspecialchars($row['rejection_remark']); ?></div>
                                <?php endif; ?>
                            </td>

                            <td class="py-2 px-4 border flex gap-2">
                                <?php if ($row['status'] === 1): ?>
                                    <form method="POST" action="process-leave.php" onsubmit="return confirm('Are you sure you want to approve this leave?');">
                                        <input type="hidden" name="request_id" value="<?php echo $row['request_id']; ?>">
                                        <input type="hidden" name="action" value="approve">
                                        <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded">Approve</button>
                                    </form>
                                    <form method="POST" action="process-leave.php" onsubmit="return confirmRejection(this);">
                                        <input type="hidden" name="request_id" value="<?php echo $row['request_id']; ?>">
                                        <input type="hidden" name="action" value="reject">
                                        <input type="hidden" name="rejection_remark" value="">
                                        <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded">Reject</button>
                                    </form>

                                <?php else: ?>
                                    <span class="italic text-gray-500">No Actions</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function confirmRejection(form) {
            const reason = prompt("Enter reason for rejecting the leave:");
            if (reason === null || reason.trim() === "") {
                alert("Rejection remark is required.");
                return false;
            }
            form.rejection_remark.value = reason;
            return true;
        }
    </script>

</body>


</html>