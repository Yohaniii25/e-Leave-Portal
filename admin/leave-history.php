<?php 
session_start(); 
require "../includes/dbconfig.php"; 
require "../includes/navbar.php";  

if (!isset($_SESSION['user']) || $_SESSION['user']['designation_id'] != 8) {     
    header("Location: ../index.php");     
    exit(); 
}  

// Fetch leaves approved at step 2 (for department 6 only)
$sql = "     
    SELECT lr.*, u.first_name, u.last_name, u.email, u.sub_office, d.department_name     
    FROM wp_leave_request lr     
    JOIN wp_pradeshiya_sabha_users u ON lr.user_id = u.ID     
    LEFT JOIN wp_departments d ON u.department_id = d.department_id     
    WHERE lr.step_2_status = 'approved' 
      AND u.sub_office = 'Head Office'
    ORDER BY lr.step_2_date DESC 
";


$stmt = $conn->prepare($sql); 
if (!$stmt) {     
    die("Prepare failed: " . $conn->error); 
}  

$stmt->execute(); 
$result = $stmt->get_result(); 
?>  

<!DOCTYPE html> 
<html lang="en">  
<head>     
    <meta charset="UTF-8">     
    <meta name="viewport" content="width=device-width, initial-scale=1.0">     
    <title>Leave Officer - Step 2 Approved Leaves</title>     
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
                Department 6 Leaves Approved at Step 2
            </h1>
            <p class="text-gray-600 text-sm md:text-base">
                Viewing all leave applications approved at step 2 in your department.
            </p>
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

            <!-- Mobile View -->
            <div class="lg:hidden space-y-4">
                <?php 
                $result->data_seek(0);
                while ($row = $result->fetch_assoc()): 
                ?>
                    <div class="bg-white rounded-lg shadow-md p-4 border-l-4 border-blue-500">
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

                        <div class="mb-3">
                            <span class="text-sm font-medium text-gray-700">Department:</span>
                            <span class="text-sm text-gray-900 ml-1">
                                <?= htmlspecialchars($row['department_name'] ?? 'N/A') ?>
                            </span>
                        </div>

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

        <?php else: ?>
            <!-- Empty State -->
            <div class="bg-white rounded-lg shadow-md p-8 text-center">
                <div class="mx-auto w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                    <i class="ph ph-calendar-x text-2xl text-gray-400"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No Approved Leaves Found</h3>
                <p class="text-gray-600 max-w-md mx-auto">
                    There are currently no leave applications approved at step 2 in department 6.
                </p>
            </div>
        <?php endif; ?>
    </div>
</body> 
</html>
