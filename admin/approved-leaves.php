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

/* ========================================
   GET FILTER DATES (from GET)
   ======================================== */
$filter_date = $_GET['filter_date'] ?? '';
$export = $_GET['export'] ?? '';

/* ========================================
   BUILD WHERE CLAUSE FOR DATE FILTER
   ======================================== */
$where = "u.department_id = ? AND lr.step_1_status = 'approved'";
$params = [$department_id];
$types = 'i';

if ($filter_date !== '') {
    $where .= " AND ? BETWEEN lr.leave_start_date AND lr.leave_end_date";
    $params[] = $filter_date;
    $types .= 's';
}

/* ========================================
   EXPORT TO CSV (filtered)
   ======================================== */
if ($export === 'csv') {
    $sql = "
        SELECT 
            CONCAT(u.first_name, ' ', u.last_name) AS employee_name,
            u.email,
            lr.leave_type,
            lr.leave_start_date,
            lr.leave_end_date,
            lr.number_of_days,
            lr.reason,
            lr.step_1_date
        FROM wp_leave_request lr
        JOIN wp_pradeshiya_sabha_users u ON lr.user_id = u.ID
        WHERE $where
        ORDER BY lr.step_1_date DESC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    $filename = "leave-on-$filter_date-dept-$department_id.csv";
    if ($filter_date === '') {
        $filename = "approved-leaves-dept-$department_id-" . date('Y-m-d') . ".csv";
    }

    header('Content-Type: text/csv; charset=utf-8');
    header("Content-Disposition: attachment; filename=\"$filename\"");
    $output = fopen('php://output', 'w');

    fputcsv($output, [
        'Employee Name', 'Email', 'Leave Type', 'Start Date', 'End Date',
        'Days', 'Reason', 'Approved On'
    ]);

    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['employee_name'],
            $row['email'],
            $row['leave_type'],
            $row['leave_start_date'],
            $row['leave_end_date'],
            $row['number_of_days'],
            $row['reason'],
            $row['step_1_date']
        ]);
    }

    $stmt->close();
    exit();
}

/* ========================================
   NORMAL PAGE: Fetch filtered data
   ======================================== */
$sql = "
    SELECT lr.*, u.first_name, u.last_name, u.email, d.department_name
    FROM wp_leave_request lr
    JOIN wp_pradeshiya_sabha_users u ON lr.user_id = u.ID
    LEFT JOIN wp_departments d ON u.department_id = d.department_id
    WHERE $where
    ORDER BY lr.step_1_date DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" />
</head>
<body class="bg-gray-100 min-h-screen">
    <?php include "../includes/navbar.php"; ?>

    <div class="max-w-7xl mx-auto p-6">
        <!-- Header + Filter + Export -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
            <h1 class="text-3xl font-semibold text-gray-800">
                Approved Leave Requests (Step 1)
            </h1>

            <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
                <!-- Date Filter -->
                <form method="GET" class="flex gap-2">
                    <input type="date" name="filter_date" value="<?= htmlspecialchars($filter_date) ?>"
                           class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <button type="submit"
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow transition flex items-center">
                        <i class="fas fa-filter mr-2"></i> Filter
                    </button>
                    <?php if ($filter_date): ?>
                        <a href="?" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white font-medium rounded-lg shadow transition">
                            Clear
                        </a>
                    <?php endif; ?>
                </form>

                <!-- Export Button -->
                <a href="?export=csv<?= $filter_date ? '&filter_date=' . urlencode($filter_date) : '' ?>"
                   class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg shadow transition">
                    <i class="fas fa-file-csv mr-2"></i>
                    Export CSV
                </a>
            </div>
        </div>

        <!-- Info Banner -->
        <?php if ($filter_date): ?>
            <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg text-blue-800">
                <i class="fas fa-info-circle mr-2"></i>
                Showing employees on leave on <strong><?= htmlspecialchars($filter_date) ?></strong>
            </div>
        <?php endif; ?>

        <!-- Results -->
        <?php if ($result->num_rows > 0): ?>
            <div class="overflow-x-auto bg-white rounded-lg shadow-md">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-green-600 text-white">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Employee</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Start</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">End</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Days</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Reason</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Approved</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm text-gray-900 font-medium">
                                    <?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    <?= htmlspecialchars($row['email']) ?>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <?= htmlspecialchars($row['leave_type']) ?>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <?= htmlspecialchars($row['leave_start_date']) ?>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <?= htmlspecialchars($row['leave_end_date']) ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-center">
                                    <span class="px-2 py-1 text-xs font-semibold bg-yellow-100 text-yellow-800 rounded-full">
                                        <?= $row['number_of_days'] ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    <?= htmlspecialchars($row['reason']) ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-green-700 font-medium">
                                    <?= date('M j, Y', strtotime($row['step_1_date'])) ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
                <i class="fas fa-exclamation-triangle text-yellow-600 text-2xl mb-2"></i>
                <p class="text-yellow-800 font-medium">
                    <?php if ($filter_date): ?>
                        No one is on leave on <strong><?= htmlspecialchars($filter_date) ?></strong>.
                    <?php else: ?>
                        No approved leave requests found.
                    <?php endif; ?>
                </p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>