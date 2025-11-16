<?php
require '../includes/dbconfig.php';

$sub_office = $_POST['sub_office'] ?? '';
$query = $_POST['query'] ?? '';

$sql = "SELECT u.ID, CONCAT(u.first_name, ' ', u.last_name) AS name, u.phone_number, 
               dpt.department_name AS department, u.email 
        FROM wp_pradeshiya_sabha_users u
        LEFT JOIN wp_departments dpt ON u.department_id = dpt.department_id
        WHERE u.sub_office = ?";

$params = [$sub_office];
$types = 's';

if (!empty($query)) {
    $sql .= " AND CONCAT(u.first_name, ' ', u.last_name) LIKE ?";
    $params[] = "%$query%";
    $types .= 's';
}

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo '<tr><td colspan="5" class="px-4 py-4 text-center text-gray-500">Error preparing query.</td></tr>';
    exit;
}

$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo '<tr class="border-b hover:bg-gray-100">';
        echo '<td class="px-4 py-2">' . htmlspecialchars($row['name']) . '</td>';
        echo '<td class="px-4 py-2">' . htmlspecialchars($row['phone_number'] ?: 'N/A') . '</td>';
        echo '<td class="px-4 py-2">' . htmlspecialchars($row['department'] ?: 'N/A') . '</td>';
        echo '<td class="px-4 py-2">' . htmlspecialchars($row['email']) . '</td>';
        echo '<td class="px-4 py-2 flex justify-center space-x-4">';
        echo '<a href="view-user.php?id=' . $row['ID'] . '" class="text-blue-500 hover:text-blue-700" title="View"><i class="ph ph-eye text-xl"></i></a>';
        echo '<a href="edit-user.php?id=' . $row['ID'] . '" class="text-green-500 hover:text-green-700" title="Edit"><i class="ph ph-pencil text-xl"></i></a>';
        echo '<a href="delete-user.php?id=' . $row['ID'] . '" class="text-red-500 hover:text-red-700" onclick="return confirm(\'Are you sure you want to delete this user?\');" title="Delete"><i class="ph ph-trash text-xl"></i></a>';
        echo '<a href="user-leave-history.php?id=' . $row['ID'] . '" class="text-purple-500 hover:text-purple-700" title="View Leave History"><i class="ph ph-clock-counter-clockwise text-xl"></i></a>';
        echo '</td>';
        echo '</tr>';
    }
} else {
    echo '<tr><td colspan="5" class="px-4 py-4 text-center text-gray-500">No users found.</td></tr>';
}

$stmt->close();
$conn->close();
?>