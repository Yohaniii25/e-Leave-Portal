<?php
session_start();
require '../includes/dbconfig.php';
require '../includes/admin-navbar.php';

$debug = true;
$success = '';
$error = '';

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


// 👇 Updated queries to match your actual table and column names
$departments_result = $conn->query("SELECT department_id AS id, department_name AS name FROM wp_departments");
$designations_result = $conn->query("SELECT designation_id AS id, designation_name AS name FROM wp_designations");

$departments = [];
$designations = [];

if ($departments_result && $designations_result) {
    $departments = $departments_result->fetch_all(MYSQLI_ASSOC);
    $designations = $designations_result->fetch_all(MYSQLI_ASSOC);
} else {
    if ($debug) {
        echo "<pre>Departments Query Error: " . $conn->error . "</pre>";
        echo "<pre>Designations Query Error: " . $conn->error . "</pre>";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);
        $gender = $_POST['gender'] ?? '';
        $nic = trim($_POST['nic']);
        $service_number = trim($_POST['service_number']);
        $address = trim($_POST['address']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone_number']);
        $department_id = (int)$_POST['department_id'];
        $designation_id = (int)$_POST['designation_id'];
        $custom_designation = trim($_POST['custom_designation'] ?? '');
        $date_of_joining = $_POST['date_of_joining'] ?? null;

        $casual_leave = isset($_POST['casual_leave_balance']) ? (int)$_POST['casual_leave_balance'] : 21;
        $sick_leave = isset($_POST['sick_leave_balance']) ? (int)$_POST['sick_leave_balance'] : 24;
        $leave_balance = $casual_leave + $sick_leave;

        if (empty($first_name) || empty($last_name) || empty($email) || empty($_POST['password'])) {
            throw new Exception("First name, last name, email, and password are required.");
        }

        $username = strtolower(explode('@', $email)[0]) ?: strtolower(substr($first_name, 0, 1) . $last_name);
        $username = preg_replace('/[^a-z0-9]/', '', $username);

        $stmt_check = $conn->prepare("SELECT ID FROM wp_pradeshiya_sabha_users WHERE email = ?");
        $stmt_check->bind_param("s", $email);
        $stmt_check->execute();
        if ($stmt_check->get_result()->num_rows > 0) {
            throw new Exception("Email already exists.");
        }
        $stmt_check->close();

        $stmt_username = $conn->prepare("SELECT ID FROM wp_pradeshiya_sabha_users WHERE username = ?");
        $stmt_username->bind_param("s", $username);
        $stmt_username->execute();
        if ($stmt_username->get_result()->num_rows > 0) {
            $username .= rand(100, 999);
        }
        $stmt_username->close();

        $password_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);

        $sql = "INSERT INTO wp_pradeshiya_sabha_users (
    username, password, first_name, last_name, gender, email, address,
    NIC, service_number, phone_number, designation_id, department_id,
    designation, sub_office, date_of_joining, 
    leave_balance, casual_leave_balance, sick_leave_balance
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param(
            "sssssssssiissssiii",
            $username,
            $password_hash,
            $first_name,
            $last_name,
            $gender,
            $email,
            $address,
            $nic,
            $service_number,
            $phone,
            $designation_id,
            $department_id,
            $custom_designation,  // stored in 'designation' column
            $admin_office,
            $date_of_joining,
            $leave_balance,
            $casual_leave,
            $sick_leave
        );


        if (!$stmt->execute()) {
            throw new Exception("Insert failed: " . $stmt->error);
        }

        $success = "User added successfully! Username: $username";
        $_POST = [];
        $stmt->close();
    } catch (Exception $e) {
        $error = $e->getMessage();
        if ($debug) {
            $error .= " (Debug info: " . $e->getTraceAsString() . ")";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add User</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <!-- Prevent form resubmission -->
    <script>
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
</head>

<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto py-8 px-4">
        <div class="max-w-4xl mx-auto bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="bg-gradient-to-r from-blue-500 to-purple-600 p-6">
                <h1 class="text-2xl font-bold text-white flex items-center">
                    <i class="fas fa-user-plus mr-3"></i>Add New User
                </h1>
                <p class="text-white opacity-80">Create a new user account for your office</p>
            </div>

            <?php if (!empty($success)): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 mx-6 mt-6" role="alert">
                    <p class="font-bold">Success!</p>
                    <p><?php echo htmlspecialchars($success); ?></p>
                </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 mx-6 mt-6" role="alert">
                    <p class="font-bold">Error</p>
                    <p><?php echo htmlspecialchars($error); ?></p>
                </div>
            <?php endif; ?>

            <form method="POST" class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Personal Information Section -->
                    <div class="space-y-4 col-span-1">
                        <h2 class="text-xl font-semibold text-gray-700 border-b pb-2 mb-4">
                            <i class="fas fa-user mr-2 text-blue-500"></i>Personal Information
                        </h2>

                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-1">First Name <span class="text-red-500">*</span></label>
                            <input type="text" name="first_name" value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>"
                                class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition" required>
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-1">Last Name <span class="text-red-500">*</span></label>
                            <input type="text" name="last_name" value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>"
                                class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition" required>
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-1">Gender</label>
                            <select name="gender" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                                <option value="">Select Gender</option>
                                <option value="Male" <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                                <option value="Female" <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-1">NIC</label>
                            <input type="text" name="nic" value="<?php echo isset($_POST['nic']) ? htmlspecialchars($_POST['nic']) : ''; ?>"
                                class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                        </div>


                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-1">Service Number</label>
                            <input type="text" name="service_number" value="<?php echo isset($_POST['service_number']) ? htmlspecialchars($_POST['service_number']) : ''; ?>"
                                class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                        </div>


                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-1">Address</label>
                            <textarea name="address" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition h-24"><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
                        </div>
                    </div>

                    <!-- Contact & Employment Section -->
                    <div class="space-y-4 col-span-1">
                        <h2 class="text-xl font-semibold text-gray-700 border-b pb-2 mb-4">
                            <i class="fas fa-briefcase mr-2 text-purple-500"></i>Contact & Employment Details
                        </h2>

                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-1">Email <span class="text-red-500">*</span></label>
                            <input type="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition" required>
                            <p class="text-sm text-gray-500 mt-1">This will be used to generate the username</p>
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-1">Phone Number</label>
                            <input type="text" name="phone_number" value="<?php echo isset($_POST['phone_number']) ? htmlspecialchars($_POST['phone_number']) : ''; ?>"
                                class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-1">Department</label>
                            <select name="department_id" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                                <option value="">Select Department</option>
                                <?php foreach ($departments as $department): ?>
                                    <option value="<?php echo $department['id']; ?>"
                                        <?php echo (isset($_POST['department_id']) && $_POST['department_id'] == $department['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($department['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>


                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-1">Designation</label>
                            <select name="designation_id" id="designation" onchange="toggleCustomDesignation()"
                                class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                                <option value="">Select Designation</option>
                                <?php foreach ($designations as $designation): ?>
                                    <option value="<?php echo $designation['id']; ?>"
                                        data-name="<?php echo htmlspecialchars($designation['name']); ?>"
                                        <?php echo (isset($_POST['designation_id']) && $_POST['designation_id'] == $designation['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($designation['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>


                        <div id="customDesignationContainer" style="display: none;">
                            <label class="block text-gray-700 text-sm font-medium mb-1">Custom Designation (for Employee)</label>
                            <input type="text" name="custom_designation" id="custom_designation"
                                value="<?php echo isset($_POST['custom_designation']) ? htmlspecialchars($_POST['custom_designation']) : ''; ?>"
                                class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                        </div>


                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-1">Date of Joining</label>
                            <input type="date" name="date_of_joining" value="<?php echo isset($_POST['date_of_joining']) ? $_POST['date_of_joining'] : ''; ?>"
                                class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                        </div>
                    </div>

                    <!-- Leave Balance Section -->
                    <div class="space-y-4 mt-6">
                        <h2 class="text-xl font-semibold text-gray-700 border-b pb-2 mb-4">
                            <i class="fas fa-calendar-check mr-2 text-indigo-500"></i>Leave Balances
                        </h2>

                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-1">Casual Leave Balance</label>
                            <input type="number" name="casual_leave_balance" value="<?php echo isset($_POST['casual_leave_balance']) ? (int)$_POST['casual_leave_balance'] : 21; ?>"
                                class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-1">Sick Leave Balance</label>
                            <input type="number" name="sick_leave_balance" value="<?php echo isset($_POST['sick_leave_balance']) ? (int)$_POST['sick_leave_balance'] : 24; ?>"
                                class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                        </div>



                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-1">Total Leave Balance <span class="text-gray-400">(Auto-calculated)</span></label>
                            <input type="number" id="total_leave" readonly
                                class="w-full p-3 border border-gray-300 rounded-lg bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 transition" value="28">
                        </div>
                    </div>

                    <!-- Password Section (Full Width) -->
                    <div class="col-span-1 md:col-span-2 mt-4">
                        <h2 class="text-xl font-semibold text-gray-700 border-b pb-2 mb-4">
                            <i class="fas fa-lock mr-2 text-yellow-500"></i>Security
                        </h2>

                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-1">Password <span class="text-red-500">*</span></label>
                            <input type="password" name="password"
                                class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition" required
                                placeholder="Create a strong password">
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row justify-between items-center mt-8 gap-4">
                    <a href="manage-users.php" class="bg-gray-500 text-white px-6 py-3 rounded-lg hover:bg-gray-600 transition duration-300 flex items-center justify-center w-full sm:w-auto">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Users
                    </a>
                    <button type="submit" class="bg-green-500 text-white px-6 py-3 rounded-lg hover:bg-green-600 transition duration-300 flex items-center justify-center w-full sm:w-auto">
                        <i class="fas fa-user-plus mr-2"></i>Create User
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Auto-calculate total leave balance
        document.addEventListener('DOMContentLoaded', function() {
            const casualLeave = document.querySelector('input[name="casual_leave_balance"]');
            const sickLeave = document.querySelector('input[name="sick_leave_balance"]');
            const totalLeave = document.getElementById('total_leave');

            function calculateTotal() {
                const casual = parseInt(casualLeave.value) || 0;
                const sick = parseInt(sickLeave.value) || 0;

                totalLeave.value = casual + sick;
            }

            // Initial calculation
            calculateTotal();

            // Add event listeners to recalculate on change
            casualLeave.addEventListener('input', calculateTotal);
            sickLeave.addEventListener('input', calculateTotal);

        });
    </script>

    <script>
        function toggleCustomDesignation() {
            const select = document.getElementById("designation");
            const selectedOption = select.options[select.selectedIndex];
            const customDesignationContainer = document.getElementById("customDesignationContainer");
            if (selectedOption.dataset.name === "Employee") {
                customDesignationContainer.style.display = "block";
            } else {
                customDesignationContainer.style.display = "none";
            }
        }

        // Call on load to reflect previous selection
        document.addEventListener("DOMContentLoaded", toggleCustomDesignation);
    </script>


    <?php require '../includes/admin-footer.php'; ?>
</body>

</html>