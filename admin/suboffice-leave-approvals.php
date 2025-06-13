<?php
session_start();
require '../includes/dbconfig.php';
require '../includes/navbar.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../index.php");
    exit();
}

$user = $_SESSION['user'];
$designation_id = $user['designation_id'];
$department_id = $user['department_id'];
$sub_office = $user['sub_office'];
$user_id = $user['id'];

// Filter based on step and role
if (in_array($designation_id, [6, 9]) && $department_id == 6) {
    // Step 1 Approval
    $stmt = $conn->prepare("SELECT lr.*, u.first_name, u.last_name FROM wp_leave_request lr
        JOIN wp_pradeshiya_sabha_users u ON lr.user_id = u.ID
        WHERE lr.office_type = 'sub'
        AND lr.sub_office = ?
        AND lr.step_1_status = 'pending'");
    $stmt->bind_param("s", $sub_office);
} elseif ($designation_id == 8 && $department_id == 9) {
    // Step 2 Approval
    $stmt = $conn->prepare("SELECT lr.*, u.first_name, u.last_name FROM wp_leave_request lr
        JOIN wp_pradeshiya_sabha_users u ON lr.user_id = u.ID
        WHERE lr.office_type = 'sub'
        AND lr.sub_office = ?
        AND lr.step_1_status = 'approved'
        AND lr.step_2_status = 'pending'");
    $stmt->bind_param("s", $sub_office);
} else {
    echo "You are not authorized to approve sub-office leaves.";
    exit();
}

$stmt->execute();
$result = $stmt->get_result();
?>

<h2>Sub-Office Leave Requests</h2>

<table border="1">
    <tr>
        <th>Employee</th>
        <th>Leave Type</th>
        <th>Start</th>
        <th>End</th>
        <th>Days</th>
        <th>Reason</th>
        <th>Substitute</th>
        <th>Action</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
            <td><?= htmlspecialchars($row['leave_type']) ?></td>
            <td><?= $row['leave_start_date'] ?></td>
            <td><?= $row['leave_end_date'] ?></td>
            <td><?= $row['number_of_days'] ?></td>
            <td><?= htmlspecialchars($row['reason']) ?></td>
            <td><?= htmlspecialchars($row['substitute']) ?></td>
            <td>
                <form action="suboffice-leave-action.php" method="POST" style="display:inline-block;">
                    <input type="hidden" name="request_id" value="<?= $row['request_id'] ?>">
                    <input type="hidden" name="step" value="<?= ($designation_id == 8) ? 2 : 1 ?>">
                    <button name="action" value="approve">Approve</button>
                    <button name="action" value="reject">Reject</button>
                </form>
            </td>
        </tr>
    <?php endwhile; ?>
</table>
