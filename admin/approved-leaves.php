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

$sql = "
    SELECT lr.*, u.first_name, u.last_name, u.email, d.department_name
    FROM wp_leave_request lr
    JOIN wp_pradeshiya_sabha_users u ON lr.user_id = u.ID
    LEFT JOIN wp_departments d ON u.department_id = d.department_id
    WHERE u.department_id = ? AND lr.step_1_status = 'approved'
    ORDER BY lr.step_1_date DESC
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
    <title>Approved Leave Requests</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen">
    <?php include "../includes/navbar.php"; ?>

    <div class="max-w-7xl mx-auto p-6">
        <h1 class="text-3xl font-semibold mb-6 text-gray-800">
            Approved Leave Requests (Step 1)
        </h1>

        <?php if ($result->num_rows > 0): ?>
            <div class="overflow-x-auto bg-white rounded-lg shadow-md">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-green-600 text-white">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Employee Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Leave Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Start</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">End</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Days</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Reason</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Approved On</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm text-gray-900"><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
                                <td class="px-6 py-4 text-sm text-gray-500"><?= htmlspecialchars($row['email']) ?></td>
                                <td class="px-6 py-4 text-sm"><?= htmlspecialchars($row['leave_type']) ?></td>
                                <td class="px-6 py-4 text-sm"><?= htmlspecialchars($row['leave_start_date']) ?></td>
                                <td class="px-6 py-4 text-sm"><?= htmlspecialchars($row['leave_end_date']) ?></td>
                                <td class="px-6 py-4 text-sm"><?= htmlspecialchars($row['number_of_days']) ?></td>
                                <td class="px-6 py-4 text-sm"><?= htmlspecialchars($row['reason']) ?></td>
                                <td class="px-6 py-4 text-sm text-green-700"><?= htmlspecialchars($row['step_1_date']) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-gray-600">No approved leave requests found for your department.</p>
        <?php endif; ?>
    </div>
</body>

</html>
