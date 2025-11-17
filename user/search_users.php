<?php
session_start();                     // <-- Needed for $_SESSION['user']
require '../includes/dbconfig.php';

if (!isset($_GET['term']) || trim($_GET['term']) === '') {
    echo json_encode(['suggestions' => [], 'on_leave' => []]);
    exit;
}

$term       = '%' . trim($_GET['term']) . '%';
$start_date = $_GET['start_date'] ?? null;
$end_date   = $_GET['end_date']   ?? null;

// === CURRENT USER CONTEXT ===
if (!isset($_SESSION['user'])) {
    echo json_encode(['suggestions' => [], 'on_leave' => []]);
    exit;
}

$current_user_id   = $_SESSION['user']['id'] ?? 0;
$current_dept_id   = $_SESSION['user']['department_id'] ?? 0;
$current_sub_office = $_SESSION['user']['sub_office'] ?? '';

// === SUGGESTIONS: Same sub-office + same department + exclude self ===
$sql = "
    SELECT 
        u.ID AS id,
        u.first_name,
        u.last_name,
        u.service_number,
        d.department_name
    FROM wp_pradeshiya_sabha_users u
    LEFT JOIN wp_departments d ON u.department_id = d.department_id
    WHERE u.sub_office = ?
      AND u.department_id = ?
      AND u.ID != ?
      AND (
          CONCAT(u.first_name, ' ', u.last_name) LIKE ?
          OR u.service_number LIKE ?
      )
";

$params = [$current_sub_office, $current_dept_id, $current_user_id, $term, $term];
$types  = 'siiss';

if ($start_date && $end_date) {
    $sql .= "
        AND u.ID NOT IN (
            SELECT lr.user_id
            FROM wp_leave_request lr
            WHERE lr.status IN (1,2)
              AND lr.leave_start_date <= ?
              AND lr.leave_end_date   >= ?
        )
    ";
    $params[] = $end_date;
    $params[] = $start_date;
    $types   .= 'ss';
}

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['suggestions' => [], 'on_leave' => []]);
    exit;
}

$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$suggestions = [];
while ($row = $result->fetch_assoc()) {
    $dept = $row['department_name'] ?? 'Unknown';
    $suggestions[] = [
        'label' => $row['first_name'] . ' ' . $row['last_name'] . ' - ' . $dept . ' (' . $row['service_number'] . ')',
        'value' => $row['first_name'] . ' ' . $row['last_name'],
        'service_number' => $row['service_number'],
        'id' => $row['id']
    ];
}

// === USERS ON LEAVE IN THE SELECTED RANGE ===
$on_leave = [];
if ($start_date && $end_date) {
    $leave_sql = "
        SELECT DISTINCT
            u.ID,
            CONCAT(u.first_name, ' ', u.last_name) AS name,
            d.department_name,
            lr.leave_type,
            lr.leave_start_date,
            lr.leave_end_date
        FROM wp_leave_request lr
        JOIN wp_pradeshiya_sabha_users u ON lr.user_id = u.ID
        LEFT JOIN wp_departments d ON u.department_id = d.department_id
        WHERE lr.status IN (1,2)
          AND lr.leave_start_date <= ?
          AND lr.leave_end_date   >= ?
          AND u.sub_office = ?
          AND u.department_id = ?
    ";

    $leave_stmt = $conn->prepare($leave_sql);
    if ($leave_stmt) {
        $leave_stmt->bind_param("sssi", $end_date, $start_date, $current_sub_office, $current_dept_id);
        $leave_stmt->execute();
        $leave_result = $leave_stmt->get_result();

        while ($row = $leave_result->fetch_assoc()) {
            $on_leave[] = [
                'name' => $row['name'],
                'department' => $row['department_name'] ?? '—',
                'type' => $row['leave_type'],
                'dates' => date('M j', strtotime($row['leave_start_date'])) . ' – ' . date('M j', strtotime($row['leave_end_date']))
            ];
        }
        $leave_stmt->close();
    }
}

header('Content-Type: application/json');
echo json_encode([
    'suggestions' => $suggestions,
    'on_leave'    => $on_leave
]);