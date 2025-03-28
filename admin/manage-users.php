<?php
session_start();
require '../includes/dbconfig.php';
require '../includes/admin-navbar.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}

// ✅ Get logged-in admin's sub_office
$admin_office = $_SESSION['user']['sub_office'];

// ✅ Modify SQL query to filter users by sub_office
$sql = "SELECT ID, CONCAT(first_name, ' ', last_name) AS name, phone_number, department, email 
        FROM wp_pradeshiya_sabha_users 
        WHERE sub_office = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $admin_office);
$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script> <!-- Icons -->
</head>

<body>

    <div class="container mx-auto bg-white p-6 rounded-lg shadow-lg">
        <div class="flex justify-between items-center mb-4">
            <h1 class="text-2xl font-bold text-gray-800">Manage Users - <?php echo htmlspecialchars($admin_office); ?></h1>
            <a href="add-user.php" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 flex items-center">
                <i class="ph ph-plus-bold mr-2"></i> Add User
            </a>
        </div>

        <!-- User Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-300 rounded-lg overflow-hidden shadow-md">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="px-4 py-2 text-left text-gray-600">Name</th>
                        <th class="px-4 py-2 text-left text-gray-600">Contact</th>
                        <th class="px-4 py-2 text-left text-gray-600">Department</th>
                        <th class="px-4 py-2 text-left text-gray-600">Email</th>
                        <th class="px-4 py-2 text-center text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr class="border-b hover:bg-gray-100">
                                <td class="px-4 py-2"><?php echo htmlspecialchars($row['name']); ?></td>
                                <td class="px-4 py-2"><?php echo htmlspecialchars($row['phone_number']); ?></td>
                                <td class="px-4 py-2"><?php echo htmlspecialchars($row['department']); ?></td>
                                <td class="px-4 py-2"><?php echo htmlspecialchars($row['email']); ?></td>
                                <td class="px-4 py-2 flex justify-center space-x-4">
                                    <a href="view-user.php?id=<?php echo $row['ID']; ?>" class="text-blue-500 hover:text-blue-700">
                                        <i class="ph ph-eye text-xl"></i>
                                    </a>
                                    <a href="edit-user.php?id=<?php echo $row['ID']; ?>" class="text-green-500 hover:text-green-700">
                                        <i class="ph ph-pencil text-xl"></i>
                                    </a>
                                    <a href="delete-user.php?id=<?php echo $row['ID']; ?>" class="text-red-500 hover:text-red-700" onclick="return confirm('Are you sure you want to delete this user?');">
                                        <i class="ph ph-trash text-xl"></i>
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

</body>

</html>
