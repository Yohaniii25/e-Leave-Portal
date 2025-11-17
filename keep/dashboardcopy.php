<?php
session_start();
require "../includes/dbconfig.php";

if (!isset($_SESSION['user'])) {
    header("Location: ../index.php");
    exit();
}

$user           = $_SESSION['user'];
$user_id        = $user['id'];
$designation_id = $user['designation_id'] ?? 0;
$department_id  = $user['department_id'] ?? null;
$sub_office     = $user['sub_office'] ?? null;

/* ========================================
   FETCH USER INFO
   ======================================== */
$info_stmt = $conn->prepare("
    SELECT 
        u.first_name, 
        u.last_name,
        COALESCE(d.designation_name, 'Employee') AS designation_name,
        COALESCE(dept.department_name, 'Unknown') AS department_name
    FROM wp_pradeshiya_sabha_users u
    LEFT JOIN wp_designations d   ON u.designation_id = d.designation_id
    LEFT JOIN wp_departments dept ON u.department_id   = dept.department_id
    WHERE u.ID = ?
");
$info_stmt->bind_param("i", $user_id);
$info_stmt->execute();
$info = $info_stmt->get_result()->fetch_assoc();
$info_stmt->close();

$full_name       = trim($info['first_name'] . ' ' . ($info['last_name'] ?? ''));
$role            = $info['designation_name'];
$department_name = $info['department_name'];

/* ========================================
   ACCESS CONTROL
   ======================================== */
$allowed = false;
if ($designation_id == 1) {
    $allowed = true; // Admin
} elseif ($department_id == 6 && in_array($designation_id, [3, 8])) {
    $allowed = true; // HOD or Leave Officer in Dept 6
} // ← THIS WAS MISSING!

if (!$allowed) {
    die("Access denied. You are not authorized to view this dashboard.");
}

/* ========================================
   IS ADMIN?
   ======================================== */
$is_admin = ($designation_id == 1);

/* ========================================
   COUNT LEAVES BY DEPARTMENT
   ======================================== */
function countDepartmentLeaves($conn, $department_id, $sub_office, $is_admin = false)
{
    $counts = ['pending' => 0, 'approved' => 0, 'rejected' => 0, 'total' => 0];

    $sql = "SELECT lr.status, COUNT(*) AS count FROM wp_leave_request lr";
    $where = [];
    $params = [];
    $types = '';

    if (!$is_admin) {
        $sql .= " JOIN wp_pradeshiya_sabha_users u ON lr.user_id = u.ID";

        if ($department_id) {
            $where[] = "u.department_id = ?";
            $params[] = $department_id;
            $types .= 'i';
        }
        if ($sub_office) {
            $where[] = "u.sub_office = ?";
            $params[] = $sub_office;
            $types .= 's';
        }
    }

    if (!empty($where)) {
        $sql .= " WHERE " . implode(' AND ', $where);
    }

    $sql .= " GROUP BY lr.status";

    $stmt = $conn->prepare($sql);
    if (!$stmt) return $counts;

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $counts['total'] += $row['count'];
        switch ($row['status']) {
            case 1: $counts['pending']   = $row['count']; break;
            case 2: $counts['approved']  = $row['count']; break;
            case 3: $counts['rejected']  = $row['count']; break;
        }
    }
    $stmt->close();

    return $counts;
}

$leaveCounts = countDepartmentLeaves($conn, $department_id, $sub_office, $is_admin);

/* ========================================
   CARD HELPER
   ======================================== */
function createCard($title, $count, $color = 'blue', $icon = '')
{
    $colors = [
        'blue'   => 'bg-blue-500 border-blue-200 text-blue-600',
        'yellow' => 'bg-yellow-500 border-yellow-200 text-yellow-600',
        'green'  => 'bg-green-500 border-green-200 text-green-600',
        'red'    => 'bg-red-500 border-red-200 text-red-600',
    ];
    $bg = $colors[$color] ?? $colors['blue'];

    return "
    <div class='bg-white rounded-lg shadow-lg border-l-4 {$bg} p-6 hover:shadow-xl transition-shadow'>
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($role) ?> Dashboard - Pannala Pradeshiya Sabha</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <?php include "../includes/navbar.php"; ?>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">
                Welcome, <?= htmlspecialchars($full_name) ?>!
            </h1>
            <p class="text-gray-600"><?= htmlspecialchars($role) ?> Dashboard</p>
            <div class="mt-2 inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                <i class="fas fa-building mr-1"></i>
                Department: 
                <?= $is_admin 
                    ? 'All Departments' 
                    : htmlspecialchars($department_name . ' (' . $sub_office . ')') 
                ?>
            </div>
        </div>

        <!-- Stats Grid -->
        <!-- <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <?php
            $icons = [
                'pending'  => '<i class="fas fa-clock text-yellow-600"></i>',
                'approved' => '<i class="fas fa-check-circle text-green-600"></i>',
                'rejected' => '<i class="fas fa-times-circle text-red-600"></i>',
                'total'    => '<i class="fas fa-clipboard-list text-blue-600"></i>',
            ];

            echo createCard('Pending Requests', $leaveCounts['pending'],   'yellow', $icons['pending']);
            echo createCard('Approved Leaves',  $leaveCounts['approved'],  'green',  $icons['approved']);
            echo createCard('Rejected Leaves',  $leaveCounts['rejected'],  'red',    $icons['rejected']);
            echo createCard('Total Requests',   $leaveCounts['total'],     'blue',   $icons['total']);
            ?>
        </div> -->

        <!-- Quick Actions + Overview -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Actions -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-bolt text-blue-600 mr-2"></i> Quick Actions
                </h2>
                <div class="space-y-3">
                    <a href="hod-leaves.php" class="flex items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition group">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3 group-hover:bg-blue-200">
                            <i class="fas fa-tasks text-blue-600"></i>
                        </div>
                        <div>
                            <span class="font-medium text-gray-700">Review Requests</span>
                            <p class="text-sm text-gray-500">Manage pending leave applications</p>
                        </div>
                    </a>
                    <a href="approved-leaves.php" class="flex items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition group">
                        <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3 group-hover:bg-green-200">
                            <i class="fas fa-check-double text-green-600"></i>
                        </div>
                        <div>
                            <span class="font-medium text-gray-700">View Approved</span>
                            <p class="text-sm text-gray-500">Check approved department leaves</p>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Overview -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-chart-bar text-green-600 mr-2"></i> Department Overview
                </h2>
                <div class="space-y-4">
                    <div class="flex justify-between p-3 bg-gray-50 rounded-lg">
                        <span class="text-sm font-medium text-gray-600">Approval Rate</span>
                        <span class="text-sm font-bold text-green-600">
                            <?= $leaveCounts['total'] > 0 ? round(($leaveCounts['approved'] / $leaveCounts['total']) * 100, 1) : 0 ?>%
                        </span>
                    </div>
                    <div class="flex justify-between p-3 bg-yellow-50 rounded-lg">
                        <span class="text-sm font-medium text-gray-600">Pending</span>
                        <span class="text-sm font-bold text-yellow-600"><?= $leaveCounts['pending'] ?> requests</span>
                    </div>
                    <div class="flex justify-between p-3 bg-blue-50 rounded-lg">
                        <span class="text-sm font-medium text-gray-600">Activity Level</span>
                        <span class="text-sm font-bold text-blue-600">
                            <?= $leaveCounts['total'] == 0 ? 'No activity' : ($leaveCounts['total'] <= 5 ? 'Low' : ($leaveCounts['total'] <= 15 ? 'Moderate' : 'High')) ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>