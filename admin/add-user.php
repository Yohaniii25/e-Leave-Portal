<?php
session_start();
require '../includes/dbconfig.php';
require '../includes/admin-navbar.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}

$admin_office = $_SESSION['user']['sub_office'];

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
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    if (empty($first_name) || empty($last_name) || empty($email) || empty($password) || empty($nic) || empty($birthdate) || empty($address)) {
        $error = "All fields are required!";
    } else {
        $sql = "INSERT INTO wp_pradeshiya_sabha_users (first_name, last_name, gender, NIC, birthdate, address, email, phone_number, department, head_of_department, sub_office, designation, date_of_joining, password)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssssssssssssss", $first_name, $last_name, $gender, $nic, $birthdate, $address, $email, $phone, $department, $head_of_department, $admin_office, $designation, $date_of_joining, $password);

            if ($stmt->execute()) {
                $success = "User added successfully!";
            } else {
                $error = "Error adding user: " . $stmt->error;
            }
        } else {
            $error = "Error preparing statement: " . $conn->error;
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
</head>
<body>
    <div class="container mx-auto bg-white p-6 rounded-lg shadow-lg max-w-lg">
        <h1 class="text-2xl font-bold mb-4 text-gray-800">Add User</h1>

        <?php if (isset($success)): ?>
            <p class="text-green-600"><?php echo $success; ?></p>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <p class="text-red-600"><?php echo $error; ?></p>
        <?php endif; ?>

        <form action="" method="POST" class="space-y-4">
            <div>
                <label class="block text-gray-700">First Name</label>
                <input type="text" name="first_name" class="w-full p-2 border rounded" required>
            </div>
            <div>
                <label class="block text-gray-700">Last Name</label>
                <input type="text" name="last_name" class="w-full p-2 border rounded" required>
            </div>
            <div>
                <label class="block text-gray-700">Gender</label>
                <select name="gender" class="w-full p-2 border rounded" required>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                </select>
            </div>
            <div>
                <label class="block text-gray-700">NIC</label>
                <input type="text" name="nic" class="w-full p-2 border rounded" required>
            </div>
            <div>
                <label class="block text-gray-700">Date of Birth</label>
                <input type="date" name="birthdate" class="w-full p-2 border rounded" required>
            </div>
            <div>
                <label class="block text-gray-700">Address</label>
                <textarea name="address" class="w-full p-2 border rounded" required></textarea>
            </div>
            <div>
                <label class="block text-gray-700">Email</label>
                <input type="email" name="email" class="w-full p-2 border rounded" required>
            </div>
            <div>
                <label class="block text-gray-700">Phone Number</label>
                <input type="text" name="phone_number" class="w-full p-2 border rounded">
            </div>
            <div>
                <label class="block text-gray-700">Department</label>
                <input type="text" name="department" class="w-full p-2 border rounded" required>
            </div>
            <div>
                <label class="block text-gray-700">Head of Department</label>
                <input type="text" name="head_of_department" class="w-full p-2 border rounded" required>
            </div>
            <div>
                <label class="block text-gray-700">Designation</label>
                <input type="text" name="designation" class="w-full p-2 border rounded" required>
            </div>
            <div>
                <label class="block text-gray-700">Date of Join</label>
                <input type="date" name="date_of_joining" class="w-full p-2 border rounded" required>
            </div>
            <div>
                <label class="block text-gray-700">Password</label>
                <input type="password" name="password" class="w-full p-2 border rounded" required>
            </div>
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 w-full">Add User</button>
        </form>
        <a href="manage-users.php" class="block text-blue-500 mt-4 text-center">Back to Users</a>
    </div>
</body>
</html>
