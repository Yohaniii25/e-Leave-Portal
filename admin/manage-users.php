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
if (!$stmt) die("Prepare failed: " . $conn->error);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (!$row || strcasecmp(trim($row['designation_name']), 'Admin') !== 0) {
    header("Location: ../index.php");
    exit();
}

$admin_office = $row['sub_office'];
require '../includes/admin-navbar.php';

// Fetch departments for filter dropdown
$dept_query = "SELECT department_id, department_name FROM wp_departments ORDER BY department_name";
$dept_result = $conn->query($dept_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>e-Leave Portal - Manage Users</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="container mx-auto bg-white p-6 rounded-lg shadow-lg">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Manage Users - <?php echo htmlspecialchars($admin_office); ?></h1>
            <a href="add-user.php" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 flex items-center">
                <i class="ph ph-plus-bold mr-2"></i> Add User
            </a>
        </div>

        <!-- Filters Row -->
        <div class="flex flex-col md:flex-row gap-4 mb-6">
            <!-- Search Input -->
            <div class="flex-1">
                <label for="user_search" class="block text-sm font-medium text-gray-700 mb-1">Search by Name</label>
                <input type="text" id="user_search" placeholder="Type employee name..." autocomplete="off"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Department Filter -->
            <div class="w-full md:w-64">
                <label for="dept_filter" class="block text-sm font-medium text-gray-700 mb-1">Filter by Department</label>
                <select id="dept_filter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Departments</option>
                    <?php while ($dept = $dept_result->fetch_assoc()): ?>
                        <option value="<?php echo $dept['department_id']; ?>">
                            <?php echo htmlspecialchars($dept['department_name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- Clear Button -->
            <div class="flex items-end">
                <button id="clear_filters" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition">
                    Clear
                </button>
            </div>
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
                    <!-- Loaded via AJAX -->
                </tbody>
            </table>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            const $search = $('#user_search');
            const $dept = $('#dept_filter');
            const $tableBody = $('#user-table-body');
            const subOffice = <?php echo json_encode($admin_office); ?>;

            // Load users (initial + on filter change)
            function loadUsers() {
                const query = $search.val().trim();
                const deptId = $dept.val();

                $.ajax({
                    url: 'search-manageusers.php',
                    method: 'POST',
                    data: {
                        sub_office: subOffice,
                        query: query,
                        department_id: deptId
                    },
                    success: function (data) {
                        $tableBody.html(data);
                    },
                    error: function () {
                        $tableBody.html('<tr><td colspan="5" class="px-4 py-4 text-center text-red-500">Error loading users.</td></tr>');
                    }
                });
            }

            // Trigger on input (debounced)
            let timeout;
            $search.on('input', function () {
                clearTimeout(timeout);
                timeout = setTimeout(loadUsers, 300);
            });

            // Trigger on department change
            $dept.on('change', loadUsers);

            // Clear filters
            $('#clear_filters').on('click', function () {
                $search.val('');
                $dept.val('');
                loadUsers();
            });

            // Initial load
            loadUsers();
        });
    </script>

<?php require '../includes/admin-footer.php'; ?>
</body>
</html>