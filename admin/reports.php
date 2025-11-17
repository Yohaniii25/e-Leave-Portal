<?php 
session_start(); 
require "../includes/dbconfig.php"; 
require "../includes/navbar.php";  

if (!isset($_SESSION['user'])) {     
    header("Location: ../index.php");     
    exit(); 
}  

$user = $_SESSION['user']; 

// Handle CSV Export
if (isset($_POST['export_csv'])) {
    $filter_date = $_POST['filter_date'] ?? null;
    
    // Build query for export
    $export_sql = "     
        SELECT lr.*, u.first_name, u.last_name, u.email, d.department_name     
        FROM wp_leave_request lr     
        JOIN wp_pradeshiya_sabha_users u ON lr.user_id = u.ID     
        LEFT JOIN wp_departments d ON u.department_id = d.department_id     
        WHERE lr.step_2_status = 'approved'
    ";
    
    $export_params = [];
    $export_types = '';
    
    if ($filter_date) {
        $export_sql .= " AND ? BETWEEN lr.leave_start_date AND lr.leave_end_date";
        $export_params[] = $filter_date;
        $export_types .= 's';
    }
    
    $export_sql .= " ORDER BY lr.step_2_date DESC";
    
    $export_stmt = $conn->prepare($export_sql);
    if (!empty($export_params)) {
        $export_stmt->bind_param($export_types, ...$export_params);
    }
    $export_stmt->execute();
    $export_result = $export_stmt->get_result();
    
    // Generate CSV
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="approved-leaves-' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Employee Name', 'Email', 'Department', 'Leave Type', 'Start Date', 'End Date', 'Days', 'Approved Date']);
    
    while ($row = $export_result->fetch_assoc()) {
        fputcsv($output, [
            $row['first_name'] . ' ' . $row['last_name'],
            $row['email'],
            $row['department_name'] ?? 'N/A',
            $row['leave_type'],
            date('M d, Y', strtotime($row['leave_start_date'])),
            date('M d, Y', strtotime($row['leave_end_date'])),
            $row['number_of_days'],
            date('M d, Y', strtotime($row['step_2_date']))
        ]);
    }
    
    fclose($output);
    $export_stmt->close();
    exit;
}

// Get filter date
$filter_date = $_POST['filter_date'] ?? null;

// Fetch leaves approved at step 2 (Head of PS or Authorized Officer approval) 
$sql = "     
    SELECT lr.*, u.first_name, u.last_name, u.email, d.department_name     
    FROM wp_leave_request lr     
    JOIN wp_pradeshiya_sabha_users u ON lr.user_id = u.ID     
    LEFT JOIN wp_departments d ON u.department_id = d.department_id     
    WHERE lr.step_2_status = 'approved'
";

$params = [];
$types = '';

if ($filter_date) {
    $sql .= " AND ? BETWEEN lr.leave_start_date AND lr.leave_end_date";
    $params[] = $filter_date;
    $types .= 's';
}

$sql .= " ORDER BY lr.step_2_date DESC";

$stmt = $conn->prepare($sql); 
if (!$stmt) {     
    die("Prepare failed: " . $conn->error); 
}  

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute(); 
$result = $stmt->get_result(); 
?>  

<!DOCTYPE html> 
<html lang="en">  
<head>     
    <meta charset="UTF-8">     
    <meta name="viewport" content="width=device-width, initial-scale=1.0">     
    <title>e-Leave Portal - Approved Leaves</title>     
    <script src="https://cdn.tailwindcss.com"></script>     
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    screens: {
                        'xs': '475px',
                    }
                }
            }
        }
    </script>
</head>   

<body class="bg-gray-50 min-h-screen">     
    <div class="container mx-auto px-4 py-6 max-w-7xl">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-2">
                Leaves Approved at Step 2
            </h1>
            <p class="text-gray-600 text-sm md:text-base">
                Managing approved leave applications
            </p>
        </div>

        <!-- Filter & Export Section -->
        <div class="bg-white rounded-lg shadow-md p-4 mb-6">
            <form method="POST" class="flex flex-col md:flex-row gap-4 items-end">
                <div class="flex-1 md:flex-initial">
                    <label for="filter_date" class="block text-sm font-medium text-gray-700 mb-2">
                        Filter by Date
                    </label>
                    <input type="date" id="filter_date" name="filter_date" 
                           value="<?= htmlspecialchars($filter_date ?? '') ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <button type="submit" name="filter" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
                    <i class="ph ph-funnel mr-2"></i>Filter
                </button>
                <button type="submit" name="export_csv" 
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium">
                    <i class="ph ph-download mr-2"></i>Export CSV
                </button>
                <?php if ($filter_date): ?>
                    <a href="reports.php" class="px-4 py-2 bg-gray-400 text-white rounded-lg hover:bg-gray-500 transition-colors font-medium text-center">
                        Clear Filter
                    </a>
                <?php endif; ?>
            </form>
            <?php if ($filter_date): ?>
                <div class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                    <p class="text-sm text-blue-800">
                        <strong>Showing employees on leave for:</strong> <?= htmlspecialchars(date('F d, Y', strtotime($filter_date))) ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($result->num_rows > 0): ?>
            <!-- Desktop Table View -->
            <div class="hidden lg:block">
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <table class="min-w-full">
                        <thead class="bg-blue-600 text-white">
                            <tr>
                                <th class="py-3 px-4 text-left text-sm font-semibold">Employee Name</th>
                                <th class="py-3 px-4 text-left text-sm font-semibold">Email</th>
                                <th class="py-3 px-4 text-left text-sm font-semibold">Department</th>
                                <th class="py-3 px-4 text-left text-sm font-semibold">Leave Type</th>
                                <th class="py-3 px-4 text-left text-sm font-semibold">Start Date</th>
                                <th class="py-3 px-4 text-left text-sm font-semibold">End Date</th>
                                <th class="py-3 px-4 text-left text-sm font-semibold">Days</th>
                                <th class="py-3 px-4 text-left text-sm font-semibold">Approved Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="py-3 px-4 text-sm text-gray-900">
                                        <?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?>
                                    </td>
                                    <td class="py-3 px-4 text-sm text-gray-600">
                                        <?= htmlspecialchars($row['email']) ?>
                                    </td>
                                    <td class="py-3 px-4 text-sm text-gray-900">
                                        <?= htmlspecialchars($row['department_name'] ?? 'N/A') ?>
                                    </td>
                                    <td class="py-3 px-4 text-sm">
                                        <span class="inline-flex px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                                            <?= htmlspecialchars($row['leave_type']) ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-sm text-gray-900">
                                        <?= htmlspecialchars(date('M d, Y', strtotime($row['leave_start_date']))) ?>
                                    </td>
                                    <td class="py-3 px-4 text-sm text-gray-900">
                                        <?= htmlspecialchars(date('M d, Y', strtotime($row['leave_end_date']))) ?>
                                    </td>
                                    <td class="py-3 px-4 text-sm font-medium text-gray-900">
                                        <?= htmlspecialchars($row['number_of_days']) ?>
                                    </td>
                                    <td class="py-3 px-4 text-sm text-gray-600">
                                        <?= htmlspecialchars(date('M d, Y', strtotime($row['step_2_date']))) ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Mobile Card View -->
            <div class="lg:hidden space-y-4">
                <?php 
                // Reset result pointer for mobile view
                $result->data_seek(0);
                while ($row = $result->fetch_assoc()): 
                ?>
                    <div class="bg-white rounded-lg shadow-md p-4 border-l-4 border-blue-500">
                        <!-- Employee Info -->
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-900">
                                    <?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?>
                                </h3>
                                <p class="text-sm text-gray-600 mt-1">
                                    <?= htmlspecialchars($row['email']) ?>
                                </p>
                            </div>
                            <span class="inline-flex px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                                <?= htmlspecialchars($row['leave_type']) ?>
                            </span>
                        </div>

                        <!-- Department -->
                        <div class="mb-3">
                            <span class="text-sm font-medium text-gray-700">Department:</span>
                            <span class="text-sm text-gray-900 ml-1">
                                <?= htmlspecialchars($row['department_name'] ?? 'N/A') ?>
                            </span>
                        </div>

                        <!-- Leave Details Grid -->
                        <div class="grid grid-cols-2 gap-3 text-sm">
                            <div>
                                <span class="font-medium text-gray-700">Start Date:</span>
                                <p class="text-gray-900 mt-1">
                                    <?= htmlspecialchars(date('M d, Y', strtotime($row['leave_start_date']))) ?>
                                </p>
                            </div>
                            <div>
                                <span class="font-medium text-gray-700">End Date:</span>
                                <p class="text-gray-900 mt-1">
                                    <?= htmlspecialchars(date('M d, Y', strtotime($row['leave_end_date']))) ?>
                                </p>
                            </div>
                            <div>
                                <span class="font-medium text-gray-700">Duration:</span>
                                <p class="text-gray-900 mt-1 font-semibold">
                                    <?= htmlspecialchars($row['number_of_days']) ?> days
                                </p>
                            </div>
                            <div>
                                <span class="font-medium text-gray-700">Approved:</span>
                                <p class="text-gray-900 mt-1">
                                    <?= htmlspecialchars(date('M d, Y', strtotime($row['step_2_date']))) ?>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <!-- Results Summary -->
            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600">
                    Showing <?= $result->num_rows ?> approved leave application<?= $result->num_rows !== 1 ? 's' : '' ?>
                </p>
            </div>

        <?php else: ?>
            <!-- Empty State -->
            <div class="bg-white rounded-lg shadow-md p-8 text-center">
                <div class="mx-auto w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                    <i class="ph ph-calendar-x text-2xl text-gray-400"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No Approved Leaves Found</h3>
                <p class="text-gray-600 max-w-md mx-auto">
                    There are currently no leave applications that have been approved at step 2. 
                    Check back later or contact your administrator.
                </p>
            </div>
        <?php endif; ?>
    </div>


</body> 
</html>