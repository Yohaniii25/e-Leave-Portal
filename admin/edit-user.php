<?php
session_start();
require '../includes/dbconfig.php';
require '../includes/admin-navbar.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}

$admin_office = $_SESSION['user']['sub_office'];

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Invalid user ID.");
}

$user_id = intval($_GET['id']);

// Fetch user details
$sql = "SELECT * FROM wp_pradeshiya_sabha_users WHERE ID = ? AND sub_office = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $user_id, $admin_office);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("User not found or you do not have permission to edit this user.");
}

$user = $result->fetch_assoc();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $gender = $_POST['gender'];
    $nic = trim($_POST['nic']);
    $birthdate = isset($_POST['birthdate']) ? $_POST['birthdate'] : null;
    $address = trim($_POST['address']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone_number']);
    $department = trim($_POST['department']);
    $head_of_department = trim($_POST['head_of_department']);
    $designation = $_POST['designation'];
    $date_of_joining = $_POST['date_of_joining'];
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;

    if (empty($first_name) || empty($last_name) || empty($email) || empty($nic) || empty($birthdate) || empty($address)) {
        $error = "All required fields must be filled!";
    } else {
        // Base SQL query
        $sql = "UPDATE wp_pradeshiya_sabha_users SET 
                first_name = ?, 
                last_name = ?, 
                gender = ?, 
                NIC = ?, 
                birthdate = ?, 
                address = ?, 
                email = ?, 
                phone_number = ?, 
                department = ?, 
                head_of_department = ?, 
                designation = ?, 
                date_of_joining = ?";

        // If password is provided, append it to the SQL query
        if ($password) {
            $sql .= ", password = ?";
        }

        $sql .= " WHERE ID = ? AND sub_office = ?";

        // Prepare statement
        $stmt = $conn->prepare($sql);

        // Bind parameters dynamically
        if ($password) {
            $stmt->bind_param("sssssssssssssis", $first_name, $last_name, $gender, $nic, $birthdate, $address, $email, $phone, $department, $head_of_department, $designation, $date_of_joining, $password, $user_id, $admin_office);
        } else {
            $stmt->bind_param("sssssssssssssi", $first_name, $last_name, $gender, $nic, $birthdate, $address, $email, $phone, $department, $head_of_department, $designation, $date_of_joining, $user_id, $admin_office);
        }

        // Execute query
        if ($stmt->execute()) {
            $success = "User updated successfully!";

            // Refresh user data after update
            $stmt = $conn->prepare("SELECT * FROM wp_pradeshiya_sabha_users WHERE ID = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
        } else {
            $error = "Error updating user: " . $stmt->error;
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto py-8 px-4">
        <div class="max-w-4xl mx-auto bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="bg-gradient-to-r from-blue-500 to-purple-600 p-6">
                <h1 class="text-2xl font-bold text-white flex items-center">
                    <i class="fas fa-user-edit mr-3"></i>Edit User Profile
                </h1>
                <p class="text-white opacity-80">Update information for <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></p>
            </div>

            <?php if (isset($success)): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 mx-6 mt-6" role="alert">
                    <p class="font-bold">Success!</p>
                    <p><?php echo $success; ?></p>
                </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 mx-6 mt-6" role="alert">
                    <p class="font-bold">Error</p>
                    <p><?php echo $error; ?></p>
                </div>
            <?php endif; ?>

            <form action="" method="POST" class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Personal Information Section -->
                    <div class="space-y-4 col-span-1">
                        <h2 class="text-xl font-semibold text-gray-700 border-b pb-2 mb-4">
                            <i class="fas fa-user mr-2 text-blue-500"></i>Personal Information
                        </h2>

                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-1">First Name <span class="text-red-500">*</span></label>
                            <input type="text" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>"
                                class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition" required>
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-1">Last Name <span class="text-red-500">*</span></label>
                            <input type="text" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>"
                                class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition" required>
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-1">Gender <span class="text-red-500">*</span></label>
                            <select name="gender" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition" required>
                                <option value="Male" <?php echo ($user['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                                <option value="Female" <?php echo ($user['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-1">NIC <span class="text-red-500">*</span></label>
                            <input type="text" name="nic" value="<?php echo htmlspecialchars($user['NIC']); ?>"
                                class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition" required>
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-1">Date of Birth <span class="text-red-500">*</span></label>
                            <input type="date" name="birthdate" value="<?php echo htmlspecialchars($user['birthdate']); ?>"
                                class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition" required>
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-1">Address <span class="text-red-500">*</span></label>
                            <textarea name="address" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition h-24" required><?php echo htmlspecialchars($user['address']); ?></textarea>
                        </div>
                    </div>

                    <!-- Contact & Employment Section -->
                    <div class="space-y-4 col-span-1">
                        <h2 class="text-xl font-semibold text-gray-700 border-b pb-2 mb-4">
                            <i class="fas fa-briefcase mr-2 text-purple-500"></i>Contact & Employment Details
                        </h2>

                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-1">Email <span class="text-red-500">*</span></label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>"
                                class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition" required>
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-1">Phone Number</label>
                            <input type="text" name="phone_number" value="<?php echo htmlspecialchars($user['phone_number']); ?>"
                                class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-1">Department <span class="text-red-500">*</span></label>
                            <input type="text" name="department" value="<?php echo htmlspecialchars($user['department']); ?>"
                                class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition" required>
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-1">Head of Department <span class="text-red-500">*</span></label>
                            <input type="text" name="head_of_department" value="<?php echo htmlspecialchars($user['head_of_department']); ?>"
                                class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition" required>
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-1">Designation <span class="text-red-500">*</span></label>
                            <input type="text" name="designation" value="<?php echo htmlspecialchars($user['designation']); ?>"
                                class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition" required>
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-1">Date of Joining <span class="text-red-500">*</span></label>
                            <input type="date" name="date_of_joining" value="<?php echo htmlspecialchars($user['date_of_joining']); ?>"
                                class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition" required>
                        </div>
                    </div>

                    <!-- Password Section (Full Width) -->
                    <div class="col-span-1 md:col-span-2 mt-4">
                        <h2 class="text-xl font-semibold text-gray-700 border-b pb-2 mb-4">
                            <i class="fas fa-lock mr-2 text-yellow-500"></i>Password
                        </h2>

                        <div class="bg-yellow-50 p-4 rounded-lg mb-4">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-info-circle text-yellow-600"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-700">
                                        Leave the password field empty if you don't want to change it.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-1">New Password</label>
                            <input type="password" name="password"
                                class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
                                placeholder="Enter new password to change">
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row justify-between items-center mt-8 gap-4">
                    <a href="manage-users.php" class="bg-gray-500 text-white px-6 py-3 rounded-lg hover:bg-gray-600 transition duration-300 flex items-center justify-center w-full sm:w-auto">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Users
                    </a>
                    <button type="submit" class="bg-blue-500 text-white px-6 py-3 rounded-lg hover:bg-blue-600 transition duration-300 flex items-center justify-center w-full sm:w-auto">
                        <i class="fas fa-save mr-2"></i>Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
    <?php require '../includes/admin-footer.php'; ?>
</body>

</html>