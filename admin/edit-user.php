<?php
session_start();
require '../includes/dbconfig.php';
require '../includes/admin-navbar.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../index.php");
    exit();
}

$email = $_SESSION['user']['email'];

// === ADMIN CHECK ===
$query = "SELECT d.designation_name, u.sub_office 
          FROM wp_pradeshiya_sabha_users u
          LEFT JOIN wp_designations d ON u.designation_id = d.designation_id
          WHERE u.email = ?";
$stmt = $conn->prepare($query);
if (!$stmt) die("Prepare failed (Admin check): " . $conn->error);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if (!$result || !($row = $result->fetch_assoc()) || strcasecmp(trim($row['designation_name']), 'Admin') !== 0) {
    header("Location: ../index.php");
    exit();
}
$admin_office = $row['sub_office'];

// === GET USER ID ===
if (!isset($_GET['id']) || !($user_id = intval($_GET['id']))) {
    die("Invalid user ID.");
}

// === FETCH USER ===
$sql = "SELECT * FROM wp_pradeshiya_sabha_users WHERE ID = ? AND sub_office = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) die("Prepare failed (Fetch user): " . $conn->error);
$stmt->bind_param("is", $user_id, $admin_office);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("User not found or access denied.");
}
$user = $result->fetch_assoc();

// === FETCH DEPARTMENTS ===
$departments = [];
$dept_sql = "SELECT department_id, department_name FROM wp_departments ORDER BY department_name";
$dept_result = $conn->query($dept_sql);
while ($dept_result && $row = $dept_result->fetch_assoc()) {
    $departments[] = $row;
}

// === FETCH DESIGNATIONS ===
$designations = [];
$desig_sql = "SELECT designation_id, designation_name FROM wp_designations ORDER BY designation_name";
$desig_result = $conn->query($desig_sql);
while ($desig_result && $row = $desig_result->fetch_assoc()) {
    $designations[] = $row;
}

// === HANDLE FORM SUBMISSION ===
$success = $error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = trim($_POST['first_name']);
    $last_name  = trim($_POST['last_name']);
    $email      = trim($_POST['email']);
    $phone      = trim($_POST['phone_number']);
    $gender     = $_POST['gender'] ?? '';
    $nic        = trim($_POST['nic'] ?? '');
    $address    = trim($_POST['address'] ?? '');

    // Department ID
    $department_id = isset($_POST['department_id']) ? intval($_POST['department_id']) : $user['department_id'];

    // Designation
    $designation_id = $_POST['designation'];
    $custom_designation = trim($_POST['custom_designation'] ?? '');
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;

    // Leave balances
    $casual_leave_balance = max(0, intval($_POST['casual_leave_balance'] ?? 0));
    $sick_leave_balance   = max(0, intval($_POST['sick_leave_balance'] ?? 0));
    $leave_balance = $casual_leave_balance + $sick_leave_balance;

    // Final designation name
    $final_designation = '';
    if ($designation_id === 'custom' && !empty($custom_designation)) {
        $final_designation = $custom_designation;
    } else {
        foreach ($designations as $d) {
            if ($d['designation_id'] == $designation_id) {
                $final_designation = $d['designation_name'];
                break;
            }
        }
    }

    // Validation
    if (empty($first_name) || empty($last_name) || empty($email) || empty($final_designation) || $department_id <= 0) {
        $error = "All required fields must be filled: Name, Email, Department, Designation.";
    } else {
        // === BUILD UPDATE QUERY ===
        $fields = [
            'first_name' => $first_name,
            'last_name'  => $last_name,
            'email'      => $email,
            'phone_number' => $phone,
            'gender'     => $gender,
            'NIC'        => $nic,
            'address'    => $address,
            'department_id' => $department_id,
            'designation' => $final_designation,
            'leave_balance' => $leave_balance,
            'casual_leave_balance' => $casual_leave_balance,
            'sick_leave_balance' => $sick_leave_balance
        ];

        $sets = [];
        $types = '';
        $params = [];

        foreach ($fields as $col => $val) {
            $sets[] = "$col = ?";
            $types .= is_int($val) ? 'i' : 's';
            $params[] = $val;
        }

        if ($password) {
            $sets[] = "password = ?";
            $types .= 's';
            $params[] = $password;
        }

        $sets[] = "updated_at = NOW()";
        $types .= 'is';
        $params[] = $user_id;
        $params[] = $admin_office;

        $sql = "UPDATE wp_pradeshiya_sabha_users SET " . implode(', ', $sets) . " WHERE ID = ? AND sub_office = ?";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            $error = "Prepare failed: " . $conn->error;
        } else {
            $stmt->bind_param($types, ...$params);
            if ($stmt->execute()) {
                $success = "User updated successfully!";
                // Refresh user data
                $refresh = $conn->prepare("SELECT * FROM wp_pradeshiya_sabha_users WHERE ID = ?");
                $refresh->bind_param("i", $user_id);
                $refresh->execute();
                $user = $refresh->get_result()->fetch_assoc();
            } else {
                $error = "Update failed: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Edit User - e-Leave Portal</title>
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

            <?php if ($success): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 mx-6 mt-6" role="alert">
                    <p class="font-bold">Success!</p>
                    <p><?php echo $success; ?></p>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 mx-6 mt-6" role="alert">
                    <p class="font-bold">Error</p>
                    <p><?php echo $error; ?></p>
                </div>
            <?php endif; ?>

            <form action="" method="POST" class="p-6" id="editUserForm">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <!-- Personal Information -->
                    <div class="space-y-4">
                        <h2 class="text-xl font-semibold text-gray-700 border-b pb-2 mb-4">
                            <i class="fas fa-user mr-2 text-blue-500"></i>Personal Information
                        </h2>

                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-1">First Name <span class="text-red-500">*</span></label>
                            <input type="text" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required
                                   class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" />
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-1">Last Name <span class="text-red-500">*</span></label>
                            <input type="text" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required
                                   class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" />
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-1">Gender <span class="text-red-500">*</span></label>
                            <select name="gender" required class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="Male" <?php echo ($user['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                                <option value="Female" <?php echo ($user['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-1">NIC</label>
                            <input type="text" name="nic" value="<?php echo htmlspecialchars($user['NIC']); ?>"
                                   class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" />
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-1">Address</label>
                            <textarea name="address" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 h-24"><?php echo htmlspecialchars($user['address']); ?></textarea>
                        </div>
                    </div>

                    <!-- Employment & Leave -->
                    <div class="space-y-4">
                        <h2 class="text-xl font-semibold text-gray-700 border-b pb-2 mb-4">
                            <i class="fas fa-briefcase mr-2 text-purple-500"></i>Employment & Leave
                        </h2>

                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-1">Email <span class="text-red-500">*</span></label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required
                                   class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" />
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-1">Phone Number</label>
                            <input type="text" name="phone_number" value="<?php echo htmlspecialchars($user['phone_number']); ?>"
                                   class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" />
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-1">Department <span class="text-red-500">*</span></label>
                            <select name="department_id" required class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="">-- Select Department --</option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?php echo $dept['department_id']; ?>" 
                                        <?php echo ($user['department_id'] == $dept['department_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($dept['department_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-1">Designation <span class="text-red-500">*</span></label>
                            <select name="designation" id="designation" required onchange="toggleCustomDesignation(this.value)"
                                    class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="">-- Select Designation --</option>
                                <?php
                                $designation_found = false;
                                foreach ($designations as $desig):
                                    $selected = ($user['designation'] == $desig['designation_name']) ? 'selected' : '';
                                    if ($selected) $designation_found = true;
                                ?>
                                    <option value="<?php echo $desig['designation_id']; ?>" <?php echo $selected; ?>>
                                        <?php echo htmlspecialchars($desig['designation_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                                <option value="custom" <?php echo !$designation_found ? 'selected' : ''; ?>>Other (Specify)</option>
                            </select>
                            <input type="text" name="custom_designation" id="custom_designation" placeholder="Enter custom designation"
                                   class="w-full p-3 mt-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 <?php echo !$designation_found ? '' : 'hidden'; ?>"
                                   value="<?php echo !$designation_found ? htmlspecialchars($user['designation']) : ''; ?>" />
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-1">Casual Leave Balance</label>
                            <input type="number" name="casual_leave_balance" min="0" value="<?php echo $user['casual_leave_balance']; ?>"
                                   class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" />
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-1">Sick Leave Balance</label>
                            <input type="number" name="sick_leave_balance" min="0" value="<?php echo $user['sick_leave_balance']; ?>"
                                   class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" />
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-1">Total Leave Balance</label>
                            <input type="number" value="<?php echo $user['leave_balance']; ?>" disabled
                                   class="w-full p-3 bg-gray-100 border border-gray-300 rounded-lg" />
                        </div>
                    </div>
                </div>

                <!-- Password -->
                <div class="mt-8 col-span-2">
                    <h2 class="text-xl font-semibold text-gray-700 border-b pb-2 mb-4">
                        <i class="fas fa-lock mr-2 text-yellow-500"></i>Change Password
                    </h2>
                    <div class="bg-yellow-50 p-4 rounded-lg mb-4">
                        <p class="text-yellow-700 text-sm flex items-center">
                            <i class="fas fa-info-circle mr-2"></i>
                            Leave blank to keep current password.
                        </p>
                    </div>
                    <input type="password" name="password" placeholder="New password (optional)"
                           class="w-full md:w-1/2 p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500" />
                </div>

                <!-- Buttons -->
                <div class="mt-8 flex justify-between">
                    <a href="manage-users.php" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold py-3 px-6 rounded-lg transition">
                        Cancel
                    </a>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition">
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

        document.addEventListener('DOMContentLoaded', function() {
            const designationSelect = document.getElementById('designation');
            toggleCustomDesignation(designationSelect.value);
        });
    </script>
</body>
</html>