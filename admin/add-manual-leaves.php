<?php
session_start();
require '../includes/dbconfig.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $leave_type = $_POST['leave_type'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $reason = $_POST['reason'] ?? '';

    $admin_id = $_SESSION['user']['id'];

    // Calculate number of days
    $start = new DateTime($start_date);
    $end = new DateTime($end_date);
    $days = $start->diff($end)->days + 1;

    // Get sub_office
    $stmtSub = $conn->prepare("SELECT sub_office FROM wp_pradeshiya_sabha_users WHERE ID = ?");
    $stmtSub->bind_param("i", $user_id);
    $stmtSub->execute();
    $subResult = $stmtSub->get_result()->fetch_assoc();
    $sub_office = $subResult['sub_office'] ?? '';

    // Insert into leave_request with status=2 (approved)
    $stmt = $conn->prepare("INSERT INTO wp_leave_request 
        (user_id, leave_type, leave_start_date, leave_end_date, number_of_days, reason, substitute, sub_office, status) 
        VALUES (?, ?, ?, ?, ?, ?, '', ?, 2)");
    $stmt->bind_param("isssiss", $user_id, $leave_type, $start_date, $end_date, $days, $reason, $sub_office);
    $stmt->execute();

    // Update relevant balances
    if ($leave_type === 'Casual Leave') {
        $update = $conn->prepare("UPDATE wp_pradeshiya_sabha_users SET casual_leave_balance = casual_leave_balance - ? WHERE ID = ?");
    } elseif ($leave_type === 'Sick Leave') {
        $update = $conn->prepare("UPDATE wp_pradeshiya_sabha_users SET sick_leave_balance = sick_leave_balance - ? WHERE ID = ?");
    } elseif ($leave_type === 'Duty Leave') {
        $update = $conn->prepare("UPDATE wp_pradeshiya_sabha_users SET duty_leave_count = duty_leave_count + ? WHERE ID = ?");
    }
    $update->bind_param("ii", $days, $user_id);
    $update->execute();

    // Optional: Log this action
    $log = $conn->prepare("INSERT INTO wp_manual_leave_logs (admin_id, user_id, leave_type, number_of_days, reason) VALUES (?, ?, ?, ?, ?)");
    $log->bind_param("iisds", $admin_id, $user_id, $leave_type, $days, $reason);
    $log->execute();

    echo "<p style='color: green;'>Leave added successfully.</p>";
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Manually Add Leave</title>
</head>

<body>
    <h2>Add Leave Manually</h2>
    <form method="post" action="">
        <label>User ID:</label>
        <input type="number" name="user_id" required><br><br>

        <label>Leave Type:</label>
        <select name="leave_type" required>
            <option value="Casual Leave">Casual Leave</option>
            <option value="Sick Leave">Sick Leave</option>
            <option value="Duty Leave">Duty Leave</option>
        </select><br><br>

        <label>Start Date:</label>
        <input type="date" name="start_date" required><br><br>

        <label>End Date:</label>
        <input type="date" name="end_date" required><br><br>

        <label>Reason (optional):</label>
        <textarea name="reason"></textarea><br><br>

        <input type="submit" name="submit" value="Add Leave">
    </form>
</body>

</html>