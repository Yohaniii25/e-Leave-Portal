<?php
session_start();
require '../includes/dbconfig.php';
require '../includes/navbar.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../index.php");
    exit();
}

$user = $_SESSION['user'];
$sub_office = $user['sub_office'];

// Fetch approved leaves for this sub-office
$stmt = $conn->prepare("
    SELECT lr.*, u.first_name, u.last_name
    FROM wp_leave_request lr
    JOIN wp_pradeshiya_sabha_users u ON lr.user_id = u.ID
    WHERE lr.office_type = 'sub'
    AND lr.sub_office = ?
    AND lr.step_1_status = 'approved'
    ORDER BY lr.leave_start_date DESC
");
$stmt->bind_param("s", $sub_office);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approved Leaves - <?= htmlspecialchars($sub_office) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
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
                        <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Approved Leave Requests</h1>
                        <p class="text-sm text-gray-500 mt-1"><?= htmlspecialchars($sub_office) ?></p>
                    </div>
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
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Start Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">End Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Days</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Substitute</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Approver</th>
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
                                            <?= htmlspecialchars($row['leave_type']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?= date('M d, Y', strtotime($row['leave_start_date'])) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?= date('M d, Y', strtotime($row['leave_end_date'])) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            <?= $row['number_of_days'] ?> days
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate" title="<?= htmlspecialchars($row['reason']) ?>">
                                        <?= htmlspecialchars($row['reason']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?= htmlspecialchars($row['substitute']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?= htmlspecialchars($row['step_1_approver_id']) ?>
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
                                            <?= htmlspecialchars($row['leave_type']) ?>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-500 block">Start Date:</span>
                                    <span class="font-medium text-gray-900">
                                        <?= date('M d, Y', strtotime($row['leave_start_date'])) ?>
                                    </span>
                                </div>
                                <div>
                                    <span class="text-gray-500 block">End Date:</span>
                                    <span class="font-medium text-gray-900">
                                        <?= date('M d, Y', strtotime($row['leave_end_date'])) ?>
                                    </span>
                                </div>
                                <div>
                                    <span class="text-gray-500 block">Days:</span>
                                    <span class="font-medium text-gray-900"><?= $row['number_of_days'] ?> days</span>
                                </div>
                                <div>
                                    <span class="text-gray-500 block">Substitute:</span>
                                    <span class="font-medium text-gray-900"><?= htmlspecialchars($row['substitute']) ?></span>
                                </div>
                            </div>

                            <div class="mt-3">
                                <span class="text-gray-500 block">Reason:</span>
                                <span class="font-medium text-gray-900"><?= htmlspecialchars($row['reason']) ?></span>
                            </div>

                            <div class="mt-3 pt-3 border-t border-gray-100">
                                <span class="text-xs text-gray-500">
                                    <i class="fas fa-user-check mr-1"></i>
                                    Approved by <?= htmlspecialchars($row['step_1_approver_id']) ?>
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
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No Approved Leaves</h3>
                    <p class="text-gray-500 max-w-sm mx-auto">
                        No approved leave requests found for your sub-office.
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
