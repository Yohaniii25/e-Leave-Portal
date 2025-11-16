<?php
session_start();
require '../includes/dbconfig.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../index.php");
    exit();
}

$email = $_SESSION['user']['email'];

$query = "SELECT d.designation_name, u.sub_office 
          FROM wp_pradeshiya_sabha_users u
          LEFT JOIN wp_designations d ON u.designation_id = d.designation_id
          WHERE u.email = ?";

$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    die("Query failed: " . $conn->error);
}

$row = $result->fetch_assoc();
if (!$row || strcasecmp(trim($row['designation_name']), 'Admin') !== 0) {
    header("Location: ../index.php");
    exit();
}

$admin_office = $row['sub_office'];

require '../includes/admin-navbar.php';

// Fetch users within admin's sub-office (initial load)
$sql = "SELECT u.ID, CONCAT(u.first_name, ' ', u.last_name) AS name, u.phone_number, 
               dpt.department_name AS department, u.email 
        FROM wp_pradeshiya_sabha_users u
        LEFT JOIN wp_departments dpt ON u.department_id = dpt.department_id
        WHERE u.sub_office = ?";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("s", $admin_office);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>e-Leave_Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="container mx-auto bg-white p-6 rounded-lg shadow-lg">
        <div class="flex justify-between items-center mb-4">
            <h1 class="text-2xl font-bold text-gray-800">Manage Users - <?php echo htmlspecialchars($admin_office); ?></h1>
            <a href="add-user.php" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 flex items-center">
                <i class="ph ph-plus-bold mr-2"></i> Add User
            </a>
        </div>

        <!-- Search Input -->
        <div class="mb-4">
            <label for="user_search" class="block text-sm font-medium text-gray-700 mb-2">
                Search by Name
            </label>
            <input
                type="text"
                id="user_search"
                placeholder="Type employee name..."
                autocomplete="off"
                class="w-full max-w-md px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
        </div>

        <!-- User Table -->
        <div class="overflow-x-auto">
            <table id="user-table" class="min-w-full bg-white border border-gray-300 rounded-lg overflow-hidden shadow-md">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="px-4 py-2 text-left text-gray-600">Name</th>
                        <th class="px-4 py-2 text-left text-gray-600">Contact</th>
                        <th class="px-4 py-2 text-left text-gray-600">Department</th>
                        <th class="px-4 py-2 text-left text-gray-600">Email</th>
                        <th class="px-4 py-2 text-center text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody id="user-table-body">
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
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
                                    <a href="delete-user.php?id=<?php echo $row['ID']; ?>" class="text-red-500 hover:text-red-700" onclick="return confirm('Are you sure you want to delete this user?');" title="Delete">
                                        <i class="ph ph-trash text-xl"></i>
                                    </a>
                                    <a href="user-leave-history.php?id=<?php echo $row['ID']; ?>" class="text-purple-500 hover:text-purple-700" title="View Leave History">
                                        <i class="ph ph-clock-counter-clockwise text-xl"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="px-4 py-4 text-center text-gray-500">No users found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#user_search').on('input', function() {
                let query = $(this).val().trim();
                let sub_office = <?php echo json_encode($admin_office); ?>;

                if (query.length < 2) {
                    // If query is too short, reload all users for the sub-office
                    $.ajax({
                        url: 'search-manageusers.php',
                        method: 'POST',
                        data: { sub_office: sub_office },
                        success: function(data) {
                            $('#user-table-body').html(data);
                        },
                        error: function() {
                            $('#user-table-body').html('<tr><td colspan="5" class="px-4 py-4 text-center text-gray-500">Error fetching users.</td></tr>');
                        }
                    });
                    return;
                }

                // Perform AJAX search
                $.ajax({
                    url: 'search-manageusers.php',
                    method: 'POST',
                    data: { query: query, sub_office: sub_office },
                    success: function(data) {
                        $('#user-table-body').html(data);
                    },
                    error: function() {
                        $('#user-table-body').html('<tr><td colspan="5" class="px-4 py-4 text-center text-gray-500">Error fetching users.</td></tr>');
                    }
                });
            });
        });
    </script>

<?php require '../includes/admin-footer.php'; ?>
</body>
</html>