<?php
session_start();

require '../includes/dbconfig.php';
require '../includes/user-navbar.php';


if (!isset($_SESSION['user']) || strcasecmp($_SESSION['user']['designation'], 'Employee') !== 0) {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user']['id'];
$sub_office = $_SESSION['user']['sub_office'];
$full_name = $_SESSION['user']['first_name'] . ' ' . $_SESSION['user']['last_name'];
$department = $_SESSION['user']['department'] ?? '';  
$leave_balance = 0;

// Get leave balance
$sql = "SELECT leave_balance FROM wp_pradeshiya_sabha_users WHERE ID = ?";
$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $leave_balance = $row['leave_balance'];
    }
    $stmt->close();
}

// Get leave history
$sql = "SELECT leave_type, leave_start_date, leave_end_date, number_of_days, status, final_status, created_at 
        FROM wp_leave_request WHERE user_id = ? ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
$leave_history = [];
if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $leave_history[] = $row;
    }
    $stmt->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

    <div class="container mx-auto mt-8">
        <div class="bg-white p-6 rounded-lg shadow-lg">
            <h1 class="text-3xl font-bold text-gray-800">User Dashboard</h1>
            <p class="text-lg text-gray-600 mt-4">Welcome, <?php echo htmlspecialchars($full_name); ?>!</p>
        </div>

        <!-- Leave Balance Section -->


        <!-- Leave Requests Section -->
        <div class="mt-6 bg-white p-6 rounded-lg shadow-lg">
            <h2 class="text-xl font-semibold text-gray-800">Your Leave Requests</h2>
            <?php if (count($leave_history) > 0) : ?>
                <div class="overflow-x-auto mt-4">
                    <table class="min-w-full bg-white border border-gray-300">
                        <thead class="bg-gray-200">
                            <tr>
                                <th class="px-4 py-2 border">Leave Type</th>
                                <th class="px-4 py-2 border">Start Date</th>
                                <th class="px-4 py-2 border">End Date</th>
                                <th class="px-4 py-2 border">Days</th>
                                <th class="px-4 py-2 border">Status</th>
                                <th class="px-4 py-2 border">Requested On</th>
                            </tr>
                        </thead>
<tbody>
    <?php foreach ($leave_history as $leave) : ?>
        <?php
if ($leave['final_status'] === 'approved') {
    $status_label = 'Approved';
    $status_class = 'text-green-600';
} else {
    switch ($leave['status']) {
        case 1:
            $status_label = 'Pending';
            $status_class = 'text-yellow-600';
            break;
        case 2:
            $status_label = 'Approved';
            $status_class = 'text-green-600';
            break;
        case 3:
            $status_label = 'Rejected';
            $status_class = 'text-red-600';
            break;
        default:
            $status_label = 'Unknown';
            $status_class = 'text-gray-600';
    }
}
        ?>
        <tr class="border">
            <td class="px-4 py-2"><?php echo htmlspecialchars($leave['leave_type']); ?></td>
            <td class="px-4 py-2"><?php echo htmlspecialchars($leave['leave_start_date']); ?></td>
            <td class="px-4 py-2"><?php echo htmlspecialchars($leave['leave_end_date']); ?></td>
            <td class="px-4 py-2"><?php echo htmlspecialchars($leave['number_of_days']); ?></td>
            <td class="px-4 py-2 font-semibold <?php echo $status_class; ?>">
                <?php echo $status_label; ?>
            </td>
            <td class="px-4 py-2"><?php echo htmlspecialchars($leave['created_at']); ?></td>
        </tr>
    <?php endforeach; ?>
</tbody>

                    </table>
                </div>
            <?php else : ?>
                <p class="text-gray-600 mt-2">No leave requests found.</p>
            <?php endif; ?>
        </div>

        <!-- Actions Section -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 gap-6 mt-8">
            <!-- Request Leave -->
            <div class="bg-blue-500 text-white p-6 rounded-lg shadow-lg hover:bg-blue-600">
                <h2 class="text-xl font-semibold">Request Leave</h2>
                <p class="mt-2">Submit a leave request online.</p>
                <a href="./leave_request.php" class="text-white mt-4 inline-block">Apply Now →</a>
            </div>



            <!-- Visit Official Website -->
            <div class="bg-green-500 text-white p-6 rounded-lg shadow-lg hover:bg-yellow-600">
                <h2 class="text-xl font-semibold">Visit Website</h2>
                <p class="mt-2">Check latest updates on the official website.</p>
                <a href="https://pannalaps.lk/" target="_blank" class="text-white mt-4 inline-block">Go to Website →</a>
            </div>
        </div>
    </div>

</body>
</html>
