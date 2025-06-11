<?php
session_start();
require "../includes/dbconfig.php";
require "../includes/navbar.php";

// Only Leave Officer (designation_id = 8)
if (!isset($_SESSION['user']) || $_SESSION['user']['designation_id'] != 8) {
    header("Location: ../index.php");
    exit();
}

// Handle approve/reject
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_id'], $_POST['action'])) {
    $leaveId = intval($_POST['request_id']);
    $action = $_POST['action'] === 'approve' ? 'approved' : 'rejected';

    $stmt = $conn->prepare("UPDATE wp_leave_request SET step_3_status = ?, final_status = ? WHERE request_id = ?");
    $stmt->bind_param("ssi", $action, $action, $leaveId);
    $stmt->execute();
    $stmt->close();
}

// 🟡 Only get users in department_id = 6 (Head Office)
$query = "
    SELECT l.*, u.first_name, u.last_name, d.department_name
    FROM wp_leave_request l
    JOIN wp_pradeshiya_sabha_users u ON l.user_id = u.ID
    LEFT JOIN wp_departments d ON u.department_id = d.department_id
    WHERE l.step_1_status = 'approved' 
      AND l.step_2_status = 'approved' 
      AND (l.step_3_status IS NULL OR l.step_3_status = '' OR l.step_3_status = 'pending')
    ORDER BY l.request_id DESC
";



$result = $conn->query($query);

// 🛑 Error Check
if (!$result) {
    die("Query Failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Leave Officer - Approvals</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body>
    <div class="max-w-7xl mx-auto">
        <h1 class="text-3xl font-bold mb-6">Final Leave Approvals - Leave Officer</h1>

        <?php if ($result->num_rows > 0): ?>
        <table class="min-w-full bg-white rounded-lg shadow-md overflow-hidden">
            <thead class="bg-gray-200 text-gray-700">
                <tr>
                    <th class="p-3 text-left">Employee</th>
                    <th class="p-3 text-left">Department</th>
                    <th class="p-3 text-left">Leave Type</th>
                    <th class="p-3 text-left">From</th>
                    <th class="p-3 text-left">To</th>
                    <th class="p-3 text-left">Reason</th>
                    <th class="p-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="p-3"><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
                        <td class="p-3"><?= htmlspecialchars($row['department_name']) ?></td>
                        <td class="p-3"><?= htmlspecialchars($row['leave_type']) ?></td>
                        <td class="p-3"><?= htmlspecialchars($row['leave_start_date']) ?></td>
                        <td class="p-3"><?= htmlspecialchars($row['leave_end_date']) ?></td>
                        <td class="p-3"><?= htmlspecialchars($row['reason']) ?></td>
                        <td class="p-3">
                            <form method="POST" class="flex gap-2">
                                <input type="hidden" name="request_id" value="<?= $row['request_id'] ?>">
                                <button type="submit" name="action" value="approve"
                                    class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700">Approve</button>
                                <button type="submit" name="action" value="reject"
                                    class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700">Reject</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p class="text-gray-600">No leave requests pending final approval.</p>
        <?php endif; ?>
    </div>
</body>
</html>
