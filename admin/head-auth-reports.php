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

$allowed_designations = [5, 3]; // Authorized Officer
if (!in_array($designation_id, $allowed_designations)) {
    die("Access denied.");
}

// Fetch all step 2 approved leaves approved by this user
$sql = "
    SELECT 
        lr.*,
        u.first_name,
        u.last_name,
        d.department_name
    FROM wp_leave_request lr
    JOIN wp_pradeshiya_sabha_users u ON lr.user_id = u.ID
    LEFT JOIN wp_departments d ON u.department_id = d.department_id
    WHERE lr.step_2_status = 'approved'
      AND lr.step_2_approver_id = ?
      AND lr.user_id != 19  -- Exclude Secretary
      AND (u.designation_id IS NULL OR u.designation_id != 1)  -- Exclude Head of Departments
    ORDER BY lr.step_2_date DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authorized Officer - Step 2 Approved Leaves Report</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="max-w-7xl mx-auto p-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-8">
            Step 2 Approved Leaves Report
        </h1>

        <p class="text-gray-600 mb-8">All regular employee leaves that have been approved at Step 2 (sent to Leave Officer for final approval). HOD and Secretary leaves are excluded.</p>

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
                                <th class="px-6 py-4 text-center">Step 2 Status</th>
                                <th class="px-6 py-4 text-left">Step 2 Decided On</th>
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
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-4 py-2 text-xs font-bold rounded-full bg-green-100 text-green-800">
                                            Step 2 Approved
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        <?= date('d M Y, h:i A', strtotime($row['step_2_date'])) ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-xl shadow-lg p-16 text-center text-gray-600">
                <p class="text-2xl">No Step 2 approved leaves found.</p>
                <p class="text-gray-500 mt-2">Regular employee leaves will appear here after Step 2 approval.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>