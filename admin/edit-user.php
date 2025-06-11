<?php
session_start();
require '../includes/dbconfig.php';
require '../includes/admin-navbar.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../index.php");
    exit();
}

$email = $_SESSION['user']['email'];

// Verify Admin Access
$query = "SELECT d.designation_name, u.sub_office 
          FROM wp_pradeshiya_sabha_users u
          LEFT JOIN wp_designations d ON u.designation_id = d.designation_id
          WHERE u.email = ?";
$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Prepare failed (Admin check): (" . $conn->errno . ") " . $conn->error);
}
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $row = $result->fetch_assoc()) {
    if (strcasecmp(trim($row['designation_name']), 'Admin') !== 0) {
        header("Location: ../index.php");
        exit();
    }
    $admin_office = $row['sub_office'];
} else {
    header("Location: ../index.php");
    exit();
}

// GET USER ID TO EDIT
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Invalid user ID.");
}

$user_id = intval($_GET['id']);

// FETCH USER DATA
$sql = "SELECT * FROM wp_pradeshiya_sabha_users WHERE ID = ? AND sub_office = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Prepare failed (Fetch user): (" . $conn->errno . ") " . $conn->error);
}
$stmt->bind_param("is", $user_id, $admin_office);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("User not found or you do not have permission to edit this user.");
}

$user = $result->fetch_assoc();

// FETCH DEPARTMENTS
$departments = [];
$dept_sql = "SELECT department_id as id, department_name as name FROM wp_departments ORDER BY department_name ASC";
$dept_result = $conn->query($dept_sql);
if ($dept_result) {
    while ($row = $dept_result->fetch_assoc()) {
        $departments[] = $row;
    }
}

// FETCH DESIGNATIONS
$designations = [];
$desig_sql = "SELECT designation_id as id, designation_name as name FROM wp_designations ORDER BY designation_name ASC";
$desig_result = $conn->query($desig_sql);
if ($desig_result) {
    while ($row = $desig_result->fetch_assoc()) {
        $designations[] = $row;
    }
}

// HANDLE FORM SUBMISSION
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone_number']);
    $designation = $_POST['designation'];
    $custom_designation = trim($_POST['custom_designation'] ?? '');
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;
    
    // Leave balances - casual, sick, and total leave balance
    $casual_leave_balance = isset($_POST['casual_leave_balance']) ? intval($_POST['casual_leave_balance']) : 0;
    $sick_leave_balance = isset($_POST['sick_leave_balance']) ? intval($_POST['sick_leave_balance']) : 0;
    $leave_balance = $casual_leave_balance + $sick_leave_balance; // Total leave balance

    // Determine final designation
    if ($designation === 'custom' && !empty($custom_designation)) {
        $designation_final = $custom_designation;
    } else {
        $designation_final = '';
        foreach ($designations as $desig) {
            if ($desig['id'] == $designation) {
                $designation_final = $desig['name'];
                break;
            }
        }
    }

    // Basic validation
    if (empty($first_name) || empty($last_name) || empty($email) || empty($designation_final)) {
        $error = "Please fill in all required fields: First Name, Last Name, Email, and Designation.";
    } else {
        // SQL Update - Update essential fields + all leave balances
        if ($password) {
            // Update with password
            $stmt = $conn->prepare("UPDATE wp_pradeshiya_sabha_users SET 
                first_name = ?, 
                last_name = ?, 
                email = ?, 
                phone_number = ?, 
                designation = ?, 
                password = ?,
                leave_balance = ?,
                casual_leave_balance = ?,
                sick_leave_balance = ?
                WHERE ID = ? AND sub_office = ?");
            
            if (!$stmt) {
                die("Prepare failed (Update with password): (" . $conn->errno . ") " . $conn->error);
            }

            $stmt->bind_param("ssssssiiiiis", $first_name, $last_name, $email, $phone, $designation_final, $password, $leave_balance, $casual_leave_balance, $sick_leave_balance, $user_id, $admin_office);
        } else {
            // Update without password
            $stmt = $conn->prepare("UPDATE wp_pradeshiya_sabha_users SET 
                first_name = ?, 
                last_name = ?, 
                email = ?, 
                phone_number = ?, 
                designation = ?,
                leave_balance = ?,
                casual_leave_balance = ?,
                sick_leave_balance = ?
                WHERE ID = ? AND sub_office = ?");
            
            if (!$stmt) {
                die("Prepare failed (Update without password): (" . $conn->errno . ") " . $conn->error);
            }

            $stmt->bind_param("sssssiiiis", $first_name, $last_name, $email, $phone, $designation_final, $leave_balance, $casual_leave_balance, $sick_leave_balance, $user_id, $admin_office);
        }

        if ($stmt->execute()) {
            $success = "User updated successfully!";
            // Refresh user data
            $stmt = $conn->prepare("SELECT * FROM wp_pradeshiya_sabha_users WHERE ID = ?");
            if ($stmt) {
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();
            }
        } else {
            $error = "Error updating user: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Edit User</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet" />
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


            <form action="" method="POST" class="p-6" id="editUserForm">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Personal Information Section -->
                    <div class="space-y-4 col-span-1">
                        <h2 class="text-xl font-semibold text-gray-700 border-b pb-2 mb-4">
                            <i class="fas fa-user mr-2 text-blue-500"></i>Personal Information
                        </h2>

                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-1">First Name <span class="text-red-500">*</span></label>
                            <input type="text" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required
                                class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition" />
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-1">Last Name <span class="text-red-500">*</span></label>
                            <input type="text" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required
                                class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition" />
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-1">Gender <span class="text-red-500">*</span></label>
                            <select name="gender" required
                                class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                                <option value="Male" <?php echo ($user['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                                <option value="Female" <?php echo ($user['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-1">NIC <span class="text-red-500">*</span></label>
                            <input type="text" name="nic" value="<?php echo htmlspecialchars($user['NIC']); ?>" required
                                class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition" />
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-1">Address <span class="text-red-500">*</span></label>
                            <textarea name="address" required
                                class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition h-24"><?php echo htmlspecialchars($user['address']); ?></textarea>
                        </div>
                    </div>

                    <!-- Contact & Employment Section -->
                    <div class="space-y-4 col-span-1">
                        <h2 class="text-xl font-semibold text-gray-700 border-b pb-2 mb-4">
                            <i class="fas fa-briefcase mr-2 text-purple-500"></i>Contact & Employment Details
                        </h2>

                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-1">Email <span class="text-red-500">*</span></label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required
                                class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition" />
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-1">Phone Number</label>
                            <input type="text" name="phone_number" value="<?php echo htmlspecialchars($user['phone_number']); ?>"
                                class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition" />
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-1">Department</label>
                            <select name="department" id="department"
                                class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                                <option value="">-- Select Department --</option>
                                <?php if (!empty($departments)): ?>
                                    <?php foreach ($departments as $dept): ?>
                                        <option value="<?= $dept['id']; ?>" <?= ($user['department_id'] == $dept['id'] || $user['department_id'] == $dept['name']) ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($dept['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="">No departments available</option>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-1">Designation <span class="text-red-500">*</span></label>
                            <select name="designation" id="designation" required onchange="toggleCustomDesignation(this.value)"
                                class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                                <option value="">-- Select Designation --</option>
                                <?php if (!empty($designations)): ?>
                                    <?php
                                    // Find if user's designation is in designations list
                                    $designation_found = false;
                                    foreach ($designations as $desig):
                                        $selected = '';
                                        if ($user['designation'] == $desig['name'] || $user['designation'] == $desig['id']) {
                                            $selected = 'selected';
                                            $designation_found = true;
                                        }
                                    ?>
                                        <option value="<?= $desig['id']; ?>" <?= $selected; ?>>
                                            <?= htmlspecialchars($desig['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                    <option value="custom" <?= !$designation_found ? 'selected' : ''; ?>>Other (Specify)</option>
                                <?php else: ?>
                                    <option value="custom">Other (Specify)</option>
                                <?php endif; ?>
                            </select>
                            <input type="text" name="custom_designation" id="custom_designation" placeholder="Enter custom designation"
                                class="w-full p-3 mt-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition <?= (!empty($designations) && $designation_found) ? 'hidden' : ''; ?>"
                                value="<?= (empty($designations) || !$designation_found) ? (isset($user['designation']) ? htmlspecialchars($user['designation']) : '') : ''; ?>" />
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-1">Date of Joining <span class="text-red-500">*</span></label>
                            <input type="date" name="date_of_joining" value="<?php echo htmlspecialchars($user['date_of_joining']); ?>" required
                                class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition" />
                        </div>
                        <!-- leave balance change -->
                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-1">Leave Balance</label>
                            <input type="number" name="leave_balance" value="<?php echo htmlspecialchars($user['leave_balance']); ?>"
                                class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition" />
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-1">Casual Leave Balance</label>
                            <input type="number" name="casual_leave_balance" value="<?php echo htmlspecialchars($user['casual_leave_balance']); ?>"
                                class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition" />
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-1">Sick Leave Balance</label>
                            <input type="number" name="sick_leave_balance" value="<?php echo htmlspecialchars($user['sick_leave_balance']); ?>"
                                class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition" />
                        </div>
                        <div>

                        </div>

                        <!-- Password Section (Full Width) -->
                        <div class="col-span-1 md:col-span-2 mt-4">
                            <h2 class="text-xl font-semibold text-gray-700 border-b pb-2 mb-4">
                                <i class="fas fa-lock mr-2 text-yellow-500"></i>Password
                            </h2>

                            <div class="bg-yellow-50 p-4 rounded-lg mb-4">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-info-circle text-yellow-500 mr-2 mt-1"></i>
                                    </div>
                                    <p class="text-yellow-700 text-sm">Leave blank if you do not want to change the password.</p>
                                </div>
                            </div>

                            <input type="password" name="password" placeholder="Enter new password" autocomplete="new-password"
                                class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500 transition" />
                        </div>
                    </div>

                    <div class="mt-8 flex justify-between">
                        <a href="view-user.php?id=<?= $user_id; ?>"
                            class="inline-block bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold py-3 px-6 rounded-lg transition">
                            Cancel
                        </a>
                        <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition">
                            Save Changes
                        </button>
                    </div>
            </form>
        </div>
    </div>

    <script>
        function toggleCustomDesignation(value) {
            const customInput = document.getElementById('custom_designation');
            if (value === 'custom') {
                customInput.classList.remove('hidden');
                customInput.required = true;
            } else {
                customInput.classList.add('hidden');
                customInput.required = false;
                customInput.value = '';
            }
        }

        // Run on page load in case the form is loaded with custom designation selected
        document.addEventListener('DOMContentLoaded', function() {
            const designationSelect = document.getElementById('designation');
            toggleCustomDesignation(designationSelect.value);
        });
    </script>
</body>

</html>