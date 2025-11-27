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
    $allowed = true;
} elseif (in_array($designation_id, [3, 5])) {
    $allowed = true;
} elseif (in_array($designation_id, [8])) {
    $allowed = true;
}

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
            case 1:
                $counts['pending']   = $row['count'];
                break;
            case 2:
                $counts['approved']  = $row['count'];
                break;
            case 3:
                $counts['rejected'] = $row['count'];
                break;
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
        </div>

        <!-- Quick Actions + Overview -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">

            <?php if ($designation_id == 5): 
            ?>
                <!-- FINAL APPROVAL (for designation_id = 5) -->
                <a href="head-of-ps-approval.php" class="group relative overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-r from-orange-500 to-red-600 opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-xl"></div>
                    <div class="relative flex flex-col items-center justify-center p-8 bg-white rounded-xl shadow-md group-hover:shadow-xl transition-all duration-300 transform group-hover:-translate-y-1">
                        <div class="w-16 h-16 bg-gradient-to-br from-orange-100 to-red-200 rounded-2xl flex items-center justify-center mb-4 group-hover:from-orange-200 group-hover:to-red-300 transition-all duration-300">
                            <i class="fa-solid fa-stamp text-3xl text-orange-600"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 group-hover:text-orange-600 transition-colors">Final Approval</h3>
                        <p class="text-sm text-gray-600 mt-2 text-center group-hover:text-gray-700">Review & approve/reject leave requests</p>
                        <div class="mt-4 inline-flex items-center text-orange-600 font-semibold text-sm group-hover:gap-2 transition-all">
                            Open <i class="fas fa-arrow-right ml-2"></i>
                        </div>
                    </div>
                </a>

                <!-- REPORTS (for designation_id = 5) -->
                <a href="reports.php" class="group relative overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-r from-teal-500 to-cyan-600 opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-xl"></div>
                    <div class="relative flex flex-col items-center justify-center p-8 bg-white rounded-xl shadow-md group-hover:shadow-xl transition-all duration-300 transform group-hover:-translate-y-1">
                        <div class="w-16 h-16 bg-gradient-to-br from-teal-100 to-cyan-200 rounded-2xl flex items-center justify-center mb-4 group-hover:from-teal-200 group-hover:to-cyan-300 transition-all duration-300">
                            <i class="fa-solid fa-chart-pie text-3xl text-teal-600"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 group-hover:text-teal-600 transition-colors">Reports</h3>
                        <p class="text-sm text-gray-600 mt-2 text-center group-hover:text-gray-700">View leave statistics and summaries</p>
                        <div class="mt-4 inline-flex items-center text-teal-600 font-semibold text-sm group-hover:gap-2 transition-all">
                            View <i class="fas fa-arrow-right ml-2"></i>
                        </div>
                    </div>
                </a>

            <?php else: // All other roles (HOD, Leave Officer, Admin, etc.) 
            ?>
                <!-- Review Requests -->
                <a href="hod-leaves.php" class="group relative overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-r from-blue-500 to-blue-600 opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-xl"></div>
                    <div class="relative flex flex-col items-center justify-center p-8 bg-white rounded-xl shadow-md group-hover:shadow-xl transition-all duration-300 transform group-hover:-translate-y-1">
                        <div class="w-16 h-16 bg-gradient-to-br from-blue-100 to-blue-200 rounded-2xl flex items-center justify-center mb-4 group-hover:from-blue-200 group-hover:to-blue-300 transition-all duration-300">
                            <i class="fa-solid fa-list-check text-3xl text-blue-600"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 group-hover:text-blue-600 transition-colors">Review Requests</h3>
                        <p class="text-sm text-gray-600 mt-2 text-center group-hover:text-gray-700">Manage pending leave applications</p>
                        <div class="mt-4 inline-flex items-center text-blue-600 font-semibold text-sm group-hover:gap-2 transition-all">
                            View <i class="fas fa-arrow-right ml-2"></i>
                        </div>
                    </div>
                </a>

                <!-- View Approved -->
                <a href="approved-leaves.php" class="group relative overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-r from-green-500 to-emerald-600 opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-xl"></div>
                    <div class="relative flex flex-col items-center justify-center p-8 bg-white rounded-xl shadow-md group-hover:shadow-xl transition-all duration-300 transform group-hover:-translate-y-1">
                        <div class="w-16 h-16 bg-gradient-to-br from-green-100 to-green-200 rounded-2xl flex items-center justify-center mb-4 group-hover:from-green-200 group-hover:to-green-300 transition-all duration-300">
                            <i class="fa-solid fa-circle-check text-3xl text-green-600"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 group-hover:text-green-600 transition-colors">View Approved</h3>
                        <p class="text-sm text-gray-600 mt-2 text-center group-hover:text-gray-700">Check approved department leaves</p>
                        <div class="mt-4 inline-flex items-center text-green-600 font-semibold text-sm group-hover:gap-2 transition-all">
                            View <i class="fas fa-arrow-right ml-2"></i>
                        </div>
                    </div>
                </a>

                <!-- Apply for Leave - ONLY for Head of Department -->
                <?php if ($designation_id == 1): ?>
                    <a href="../user/leave_request.php" class="group relative overflow-hidden">
                        <div class="absolute inset-0 bg-gradient-to-r from-purple-500 to-indigo-600 opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-xl"></div>
                        <div class="relative flex flex-col items-center justify-center p-8 bg-white rounded-xl rounded-xl shadow-md group-hover:shadow-xl transition-all duration-300 transform group-hover:-translate-y-1">
                            <div class="w-16 h-16 bg-gradient-to-br from-purple-100 to-indigo-200 rounded-2xl flex items-center justify-center mb-4 group-hover:from-purple-200 group-hover:to-indigo-300 transition-all duration-300">
                                <i class="fa-solid fa-plane-departure text-3xl text-purple-600"></i>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900 group-hover:text-purple-600 transition-colors">Apply for Leave</h3>
                            <p class="text-sm text-gray-600 mt-2 text-center group-hover:text-gray-700">Submit your leave application</p>
                            <div class="mt-4 inline-flex items-center text-purple-600 font-semibold text-sm group-hover:gap-2 transition-all">
                                Apply Now <i class="fas fa-arrow-right ml-2"></i>
                            </div>
                        </div>
                    </a>
                <?php endif; ?>

            <?php endif; ?>

        </div>

    </div>
</body>

</html>