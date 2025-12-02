<?php
session_start();
require '../includes/dbconfig.php';
require '../includes/navbar.php'; // optional UI component

if (!isset($_SESSION['user'])) {
    header("Location: ../index.php");
    exit();
}

$user = $_SESSION['user'];
$user_id = $user['id'];
$designation_id = $user['designation_id'];
$department_id = $user['department_id'];

// Optional: Only allow if this user is a Leave Officer (you can customize the condition)
if ($designation_id != 8 && $designation_id != 10) {
    echo "<div class='min-h-screen bg-gray-50 flex items-center justify-center px-4'>
            <div class='bg-white rounded-lg shadow-lg p-8 max-w-md w-full text-center'>
                <div class='text-red-500 text-6xl mb-4'>⚠️</div>
                <h3 class='text-xl font-semibold text-red-600 mb-2'>Access Denied</h3>
                <p class='text-gray-600'>You are not authorized as a Leave Officer to access this page.</p>
            </div>
          </div>";
    exit();
}

// Fetch all leaves where this user approved in step 2
$stmt = $conn->prepare("
    SELECT lr.*, u.first_name, u.last_name, u.sub_office
    FROM wp_leave_request lr
    JOIN wp_pradeshiya_sabha_users u ON lr.user_id = u.ID
    WHERE lr.final_status = 'approved'
      AND lr.office_type = 'sub'
    ORDER BY lr.step_2_date DESC, lr.created_at DESC
");
$stmt->execute();
$result = $stmt->get_result();

// Helper function to get status color
function getStatusColor($status) {
    switch (strtolower($status)) {
        case 'approved':
            return 'text-green-600 bg-green-50';
        case 'rejected':
            return 'text-red-600 bg-red-50';
        case 'pending':
            return 'text-yellow-600 bg-yellow-50';
        default:
            return 'text-gray-600 bg-gray-50';
    }
}

// Helper function to format leave type
function formatLeaveType($type) {
    return ucwords(str_replace('_', ' ', $type));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Approval History - Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8'
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Header Section -->
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="bg-primary-500 p-2 rounded-lg">
                        <i class="fas fa-check-circle text-white text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Leave Approval History</h1>
                        <p class="text-sm text-gray-500 mt-1">Manage and review your approved leave requests</p>
                    </div>
                </div>
                <div class="hidden sm:flex items-center space-x-2 text-sm text-gray-500">
                    <i class="fas fa-user-shield"></i>
                    <span>Leave Officer</span>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">


        <!-- Table Section -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-list-alt mr-2 text-primary-500"></i>
                    Approved Leave Requests
                </h2>
            </div>

            <?php if ($result->num_rows > 0): ?>
                <!-- Desktop Table View -->
                <div class="hidden lg:block overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Leave Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duration</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Days</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Approved Date</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-50 transition-colors duration-200">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="bg-primary-100 p-2 rounded-full mr-3">
                                                <i class="fas fa-user text-primary-600 text-sm"></i>
                                            </div>
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">
                                                    <?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">
                                            <?= htmlspecialchars(formatLeaveType($row['leave_type'])) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <div class="flex flex-col">
                                            <span><i class="fas fa-calendar-day mr-1"></i><?= date('M d, Y', strtotime($row['leave_start_date'])) ?></span>
                                            <span><i class="fas fa-calendar-check mr-1"></i><?= date('M d, Y', strtotime($row['leave_end_date'])) ?></span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            <?= $row['number_of_days'] ?> days
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= getStatusColor($row['final_status']) ?>">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            <?= ucfirst($row['final_status']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?= date('M d, Y', strtotime($row['step_2_date'])) ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Card View -->
                <div class="lg:hidden">
                    <?php 
                    $result->data_seek(0); // Reset result pointer for mobile view
                    while ($row = $result->fetch_assoc()): 
                    ?>
                        <div class="border-b border-gray-200 p-4 hover:bg-gray-50 transition-colors duration-200">
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex items-center">
                                    <div class="bg-primary-100 p-2 rounded-full mr-3">
                                        <i class="fas fa-user text-primary-600 text-sm"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-sm font-semibold text-gray-900">
                                            <?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?>
                                        </h3>
                                        <span class="inline-block mt-1 px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">
                                            <?= htmlspecialchars(formatLeaveType($row['leave_type'])) ?>
                                        </span>
                                    </div>
                                </div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= getStatusColor($row['final_status']) ?>">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    <?= ucfirst($row['final_status']) ?>
                                </span>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-500 block">Duration:</span>
                                    <span class="font-medium text-gray-900">
                                        <?= date('M d', strtotime($row['leave_start_date'])) ?> - <?= date('M d, Y', strtotime($row['leave_end_date'])) ?>
                                    </span>
                                </div>
                                <div>
                                    <span class="text-gray-500 block">Days:</span>
                                    <span class="font-medium text-gray-900"><?= $row['number_of_days'] ?> days</span>
                                </div>
                            </div>
                            
                            <div class="mt-3 pt-3 border-t border-gray-100">
                                <span class="text-xs text-gray-500">
                                    <i class="fas fa-clock mr-1"></i>
                                    Approved on <?= date('M d, Y', strtotime($row['step_2_date'])) ?>
                                </span>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <!-- Empty State -->
                <div class="text-center py-12">
                    <div class="bg-gray-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-inbox text-gray-400 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No Leave Approvals Found</h3>
                    <p class="text-gray-500 max-w-sm mx-auto">
                        You haven't approved any leave requests yet. When you approve leave requests, they will appear here.
                    </p>
                </div>
            <?php endif; ?>
        </div>


    </div>

    <!-- Back to Top Button -->
    <button id="backToTop" class="fixed bottom-6 right-6 bg-primary-500 text-white p-3 rounded-full shadow-lg hover:bg-primary-600 transition-all duration-300 opacity-0 invisible">
        <i class="fas fa-arrow-up"></i>
    </button>

    <script>
        // Back to top functionality
        const backToTopButton = document.getElementById('backToTop');
        
        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 300) {
                backToTopButton.classList.remove('opacity-0', 'invisible');
            } else {
                backToTopButton.classList.add('opacity-0', 'invisible');
            }
        });
        
        backToTopButton.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });

        // Add loading animation for table rows
        document.addEventListener('DOMContentLoaded', function() {
            const rows = document.querySelectorAll('tbody tr, .lg\\:hidden > div');
            rows.forEach((row, index) => {
                row.style.opacity = '0';
                row.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    row.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                    row.style.opacity = '1';
                    row.style.transform = 'translateY(0)';
                }, index * 50);
            });
        });
    </script>
</body>
</html>