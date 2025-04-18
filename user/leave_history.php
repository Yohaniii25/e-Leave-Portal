<?php
session_start();
require '../includes/dbconfig.php';


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Employee') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user']['id'];
$sub_office = $_SESSION['user']['sub_office'];
$full_name = $_SESSION['user']['first_name'] . ' ' . $_SESSION['user']['last_name'];
$department = $_SESSION['user']['department'] ?? '';

$query = "SELECT leave_type, leave_start_date, leave_end_date, number_of_days, status, created_at 
          FROM wp_leave_request 
          WHERE user_id = ? AND status = 2 
          ORDER BY created_at DESC";

$stmt = $conn->prepare($query);
if (!$stmt) {
    die("SQL error: " . $conn->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();



?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Leave</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50 min-h-screen font-sans">
    <?php include('../includes/user-navbar.php'); ?>

    <div class="max-w-5xl mx-auto mt-10 p-6 bg-white shadow-md rounded-xl">
        <h2 class="text-2xl font-bold mb-4 text-gray-700">Approved Leave History</h2>

        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-300 rounded-md">
                <thead class="bg-gray-100 text-left text-sm font-semibold text-gray-700">
                    <tr>
                        <th class="py-3 px-4 border-b">Leave Type</th>
                        <th class="py-3 px-4 border-b">Start Date</th>
                        <th class="py-3 px-4 border-b">End Date</th>
                        <th class="py-3 px-4 border-b">Days</th>
                        <th class="py-3 px-4 border-b">Applied On</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 text-sm">
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="py-3 px-4 border-b"><?php echo htmlspecialchars($row['leave_type']); ?></td>
                                <td class="py-3 px-4 border-b"><?php echo htmlspecialchars($row['leave_start_date']); ?></td>
                                <td class="py-3 px-4 border-b"><?php echo htmlspecialchars($row['leave_end_date']); ?></td>
                                <td class="py-3 px-4 border-b"><?php echo htmlspecialchars($row['number_of_days']); ?></td>
                                <td class="py-3 px-4 border-b"><?php echo htmlspecialchars($row['created_at']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-4 text-gray-500">No approved leave history found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php
    $stmt->close();
    ?>
</body>

</html>