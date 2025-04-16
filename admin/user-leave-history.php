<?php
require '../includes/dbconfig.php';
require '../includes/admin-navbar.php';

if (!isset($_GET['id'])) {
    echo "No user ID provided.";
    exit();
}

$user_id = intval($_GET['id']);

$sql_user = "SELECT first_name, last_name FROM wp_pradeshiya_sabha_users WHERE ID = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$user = $result_user->fetch_assoc();

$sql_leaves = "SELECT * FROM wp_leave_request WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql_leaves);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Leave History</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
    <div class="max-w-5xl mx-auto bg-white p-6 rounded shadow">
        <h2 class="text-2xl font-bold mb-4">Leave History of <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h2>

        <div class="overflow-x-auto">
            <table class="min-w-full border">
                <thead>
                    <tr class="bg-gray-200 text-left">
                        <th class="px-4 py-2 border">Type</th>
                        <th class="px-4 py-2 border">Start</th>
                        <th class="px-4 py-2 border">End</th>
                        <th class="px-4 py-2 border">Days</th>
                        <th class="px-4 py-2 border">Status</th>
                        <th class="px-4 py-2 border">Reason</th>
                        <th class="px-4 py-2 border">Submitted On</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr class="border-b hover:bg-gray-100">
                                <td class="px-4 py-2 border"><?php echo htmlspecialchars($row['leave_type']); ?></td>
                                <td class="px-4 py-2 border"><?php echo htmlspecialchars($row['leave_start_date']); ?></td>
                                <td class="px-4 py-2 border"><?php echo htmlspecialchars($row['leave_end_date']); ?></td>
                                <td class="px-4 py-2 border"><?php echo htmlspecialchars($row['number_of_days']); ?></td>
                                <td class="px-4 py-2 border">
                                    <?php
                                        $statusColor = match ($row['status']) {
                                            'Pending' => 'text-yellow-600',
                                            'Approved' => 'text-green-600',
                                            'Rejected' => 'text-red-600',
                                            default => 'text-gray-600',
                                        };
                                    ?>
                                    <span class="<?= $statusColor ?> font-semibold"><?php echo htmlspecialchars($row['status']); ?></span>
                                </td>
                                <td class="px-4 py-2 border"><?php echo htmlspecialchars($row['reason']); ?></td>
                                <td class="px-4 py-2 border"><?php echo htmlspecialchars($row['created_at']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="px-4 py-4 text-center text-gray-500">No leave history found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
