<?php
// search-manageusers.php
require '../includes/dbconfig.php';

$sub_office = $_POST['sub_office'] ?? '';
$query = trim($_POST['query'] ?? '');
$dept_id = $_POST['department_id'] ?? '';

if (empty($sub_office)) {
    echo '<tr><td colspan="5" class="text-center text-gray-500">Invalid office.</td></tr>';
    exit;
}

// Base query
$sql = "SELECT u.ID, CONCAT(u.first_name, ' ', u.last_name) AS name, u.phone_number, 
               dpt.department_name AS department, u.email 
        FROM wp_pradeshiya_sabha_users u
        LEFT JOIN wp_departments dpt ON u.department_id = dpt.department_id
        WHERE u.sub_office = ?";

$params = [$sub_office];
$types = "s";

// Add name search
if (!empty($query)) {
    $sql .= " AND (CONCAT(u.first_name, ' ', u.last_name) LIKE ? OR u.email LIKE ?)";
    $like = "%$query%";
    $params[] = $like;
    $params[] = $like;
    $types .= "ss";
}

// Add department filter
if (!empty($dept_id)) {
    $sql .= " AND u.department_id = ?";
    $params[] = $dept_id;
    $types .= "i";
}

$sql .= " ORDER BY u.last_name";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo '<tr><td colspan="5" class="text-center text-red-500">Query error.</td></tr>';
    exit;
}

$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo '<tr><td colspan="5" class="px-4 py-4 text-center text-gray-500">No users found.</td></tr>';
    exit;
}

while ($row = $result->fetch_assoc()):
?>
<tr class="border-b hover:bg-gray-100">
    <td class="px-4 py-2"><?php echo htmlspecialchars($row['name']); ?></td>
    <td class="px-4 py-2"><?php echo htmlspecialchars($row['phone_number'] ?: 'N/A'); ?></td>
    <td class="px-4 py-2"><?php echo htmlspecialchars($row['department'] ?: 'N/A'); ?></td>
    <td class="px-4 py-2"><?php echo htmlspecialchars($row['email']); ?></td>
    <td class="px-4 py-2 flex justify-center space-x-4">
        <a href="view-user.php?id=<?php echo $row['ID']; ?>" class="text-blue-500 hover:text-blue-700" title="View">
            <i class="ph ph-eye text-xl"></i>
        </a>
        <a href="edit-user.php?id=<?php echo $row['ID']; ?>" class="text-green-500 hover:text-green-700" title="Edit">
            <i class="ph ph-pencil text-xl"></i>
        </a>
        <a href="delete-user.php?id=<?php echo $row['ID']; ?>" class="text-red-500 hover:text-red-700" 
           onclick="return confirm('Are you sure?');" title="Delete">
            <i class="ph ph-trash text-xl"></i>
        </a>
        <a href="user-leave-history.php?id=<?php echo $row['ID']; ?>" class="text-purple-500 hover:text-purple-700" title="Leave History">
            <i class="ph ph-clock-counter-clockwise text-xl"></i>
        </a>
    </td>
</tr>
<?php
endwhile;
?>