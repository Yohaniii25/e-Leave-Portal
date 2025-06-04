<?php
session_start();
require "../includes/dbconfig.php";

if (!isset($_SESSION['user'])) {
    header("Location: ../index.php");
    exit();
}

$user = $_SESSION['user'];
$designation_id = $user['designation_id'];
$department_id = $user['department_id'] ?? null;

// Validate department_id if required
if ($designation_id == 1 && $department_id === null) {
    die("Error: Department ID is not set for this user.");
}

// Count Leaves Helper Function
function countLeaves($conn, $department_id)
{
    if (!$department_id) {
        return ['pending' => 0, 'approved' => 0, 'rejected' => 0, 'total' => 0];
    }

    $sql = "SELECT status, COUNT(*) as count 
            FROM wp_leave_request 
            WHERE department_id = ? 
            GROUP BY status";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("i", $department_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $counts = ['pending' => 0, 'approved' => 0, 'rejected' => 0, 'total' => 0];
    while ($row = $result->fetch_assoc()) {
        $counts['total'] += $row['count'];
        if ($row['status'] == 1) {
            $counts['pending'] = $row['count'];
        } elseif ($row['status'] == 2) {
            $counts['approved'] = $row['count'];
        } elseif ($row['status'] == 3) {
            $counts['rejected'] = $row['count'];
        }
    }

    return $counts;
}

// Helper function to create dashboard cards
function createCard($title, $count, $color = 'blue', $icon = '') {
    $colorClasses = [
        'blue' => 'bg-blue-500 border-blue-200',
        'yellow' => 'bg-yellow-500 border-yellow-200',
        'green' => 'bg-green-500 border-green-200',
        'red' => 'bg-red-500 border-red-200',
        'purple' => 'bg-purple-500 border-purple-200',
        'indigo' => 'bg-indigo-500 border-indigo-200'
    ];
    
    $bgColor = $colorClasses[$color] ?? $colorClasses['blue'];
    
    return "
    <div class='bg-white rounded-lg shadow-lg border-l-4 {$bgColor} p-6 hover:shadow-xl transition-shadow duration-300'>
        <div class='flex items-center'>
            <div class='flex-shrink-0'>
                <div class='w-12 h-12 bg-{$color}-100 rounded-lg flex items-center justify-center'>
                    {$icon}
                </div>
            </div>
            <div class='ml-4'>
                <h3 class='text-lg font-semibold text-gray-800 mb-1'>{$title}</h3>
                <p class='text-3xl font-bold text-{$color}-600'>{$count}</p>
            </div>
        </div>
    </div>";
}

$leaveCounts = countLeaves($conn, $department_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HOD Dashboard - Pannala Pradeshiya Sabha</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <?php include "../includes/navbar.php"; ?>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header Section -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">
                Welcome, <?= htmlspecialchars($user['first_name']) ?>!
            </h1>
            <p class="text-gray-600">Head of Department Dashboard</p>
            <div class="mt-2 inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H3m2 0h3M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
                Department ID: <?= htmlspecialchars($department_id ?? 'Unknown') ?>
            </div>
        </div>

        <!-- Dashboard Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <?php
            // Icons for different card types
            $pendingIcon = '<svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
            $approvedIcon = '<svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
            $rejectedIcon = '<svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
            $totalIcon = '<svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>';

            echo createCard('Pending Leave Requests', $leaveCounts['pending'], 'yellow', $pendingIcon);
            echo createCard('Approved Leaves', $leaveCounts['approved'], 'green', $approvedIcon);
            echo createCard('Rejected Leaves', $leaveCounts['rejected'], 'red', $rejectedIcon);
            echo createCard('Total Department Requests', $leaveCounts['total'], 'blue', $totalIcon);
            ?>
        </div>

        <!-- Quick Actions Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Quick Actions Card -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    Quick Actions
                </h2>
                <div class="space-y-3">
                    <a href="/user/hod-leaves.php" class="flex items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors duration-200 group">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3 group-hover:bg-blue-200">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                            </svg>
                        </div>
                        <div>
                            <span class="font-medium text-gray-700">Review Department Requests</span>
                            <p class="text-sm text-gray-500">Manage pending leave applications</p>
                        </div>
                    </a>
                    
                    <a href="/user/approved-leaves.php" class="flex items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition-colors duration-200 group">
                        <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3 group-hover:bg-green-200">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <span class="font-medium text-gray-700">View Approved Leaves</span>
                            <p class="text-sm text-gray-500">Check approved department leaves</p>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Recent Activity Card -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    Department Overview
                </h2>
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <span class="text-sm font-medium text-gray-600">Approval Rate</span>
                        <span class="text-sm font-bold text-green-600">
                            <?php 
                            $total = $leaveCounts['total'];
                            $approved = $leaveCounts['approved'];
                            $rate = $total > 0 ? round(($approved / $total) * 100, 1) : 0;
                            echo $rate . '%';
                            ?>
                        </span>
                    </div>
                    
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <span class="text-sm font-medium text-gray-600">Pending Review</span>
                        <span class="text-sm font-bold text-yellow-600"><?= $leaveCounts['pending'] ?> requests</span>
                    </div>
                    
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <span class="text-sm font-medium text-gray-600">Department Activity</span>
                        <span class="text-sm font-bold text-blue-600">
                            <?php
                            if ($total == 0) {
                                echo 'No requests';
                            } elseif ($total <= 5) {
                                echo 'Low activity';
                            } elseif ($total <= 15) {
                                echo 'Moderate activity';
                            } else {
                                echo 'High activity';
                            }
                            ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alert Section -->
        <?php if ($leaveCounts['pending'] > 0): ?>
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700">
                        <strong>Action Required:</strong> You have <?= $leaveCounts['pending'] ?> pending leave request(s) awaiting your review.
                        <a href="/user/hod-leaves.php" class="font-medium underline text-yellow-700 hover:text-yellow-600">
                            Review now →
                        </a>
                    </p>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>