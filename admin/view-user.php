<?php
session_start();
require '../includes/dbconfig.php';
require '../includes/admin-navbar.php';

if (!isset($_SESSION['user']) || empty($_SESSION['user']['email'])) {
    header("Location: ../index.php");
    exit();
}

$email = $_SESSION['user']['email'];
$admin_office = $_SESSION['user']['sub_office'] ?? '';  

$query = "SELECT d.designation_name 
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

$row = $result->fetch_assoc();
if (!$row || strcasecmp(trim($row['designation_name']), 'Admin') !== 0) {
    header("Location: ../index.php");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid user ID.");
}
$user_id = intval($_GET['id']);

$sql = "SELECT u.*, d.department_name 
        FROM wp_pradeshiya_sabha_users u
        LEFT JOIN wp_departments d ON u.department_id = d.department_id
        WHERE u.ID = ? AND u.sub_office = ?";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("is", $user_id, $admin_office);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("User not found or you do not have permission to view this user.");
}

$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        .user-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .user-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        .detail-item {
            opacity: 0;
            animation: fadeInSlide 0.6s forwards;
        }

        @keyframes fadeInSlide {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
    </style>
</head>

<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="user-card bg-white rounded-xl shadow-lg overflow-hidden max-w-5xl mx-auto">
            <!-- Header Section - Full Width -->
            <div class="bg-gradient-to-r from-blue-500 to-purple-600 p-6 text-center">
                <div class="w-24 h-24 rounded-full bg-white mx-auto mb-4 flex items-center justify-center">
                    <i class="fas fa-user text-5xl text-gray-600"></i>
                </div>
                <h1 class="text-2xl font-bold text-white">
                    <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                </h1>
                <p class="text-white opacity-80"><?php echo htmlspecialchars($user['designation']); ?></p>
            </div>

            <!-- Two-column layout for details -->
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Left Column -->
                    <div class="space-y-4">
                        <div class="detail-item" style="animation-delay: 0.1s">
                            <div class="flex items-center">
                                <i class="fas fa-envelope text-blue-500 mr-3 w-8 text-center"></i>
                                <p class="text-gray-700">
                                    <strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?>
                                </p>
                            </div>
                        </div>

                        <div class="detail-item" style="animation-delay: 0.2s">
                            <div class="flex items-center">
                                <i class="fas fa-phone text-green-500 mr-3 w-8 text-center"></i>
                                <p class="text-gray-700">
                                    <strong>Phone:</strong> <?php echo htmlspecialchars($user['phone_number']); ?>
                                </p>
                            </div>
                        </div>

                        <div class="detail-item" style="animation-delay: 0.3s">
                            <div class="flex items-center">
                                <i class="fas fa-user text-green-500 mr-3 w-8 text-center"></i>
                                <p class="text-gray-700">
                                    <strong>NIC:</strong> <?php echo htmlspecialchars($user['NIC']); ?>
                                </p>
                            </div>
                        </div>

                        <div class="detail-item" style="animation-delay: 0.4s">
                            <div class="flex items-center">
                                <i class="fas fa-venus-mars text-purple-500 mr-3 w-8 text-center"></i>
                                <p class="text-gray-700">
                                    <strong>Gender:</strong> <?php echo htmlspecialchars($user['gender']); ?>
                                </p>
                            </div>
                        </div>

                        <!-- <div class="detail-item" style="animation-delay: 0.5s">
                            <div class="flex items-center">
                                <i class="fas fa-building text-indigo-500 mr-3 w-8 text-center"></i>
                                <p class="text-gray-700">
                                    <strong>Department:</strong> <?php echo htmlspecialchars($user['department_name']); ?>
                                </p>
                            </div>
                        </div> -->
                    </div>

                    <!-- Right Column -->
                    <div class="space-y-4">
                        <!-- <div class="detail-item" style="animation-delay: 0.6s">
                            <div class="flex items-center">
                                <i class="fas fa-user-tie text-red-500 mr-3 w-8 text-center"></i>
                                <p class="text-gray-700">
                                    <strong>Head Of Department:</strong> <?php echo htmlspecialchars($user['head_of_department']); ?>
                                </p>
                            </div>
                        </div> -->

                        <div class="detail-item" style="animation-delay: 0.7s">
                            <div class="flex items-center">
                                <i class="fas fa-building text-blue-600 mr-3 w-8 text-center"></i>
                                <p class="text-gray-700">
                                    <strong>Sub Office:</strong> <?php echo htmlspecialchars($user['sub_office']); ?>
                                </p>
                            </div>
                        </div>



                    </div>
                </div>
            </div>

            <!-- Leave Balances Section -->
            <div class="mt-10">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Leave Balances</h2>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-center">
                    <div class="bg-blue-100 p-4 rounded-lg shadow">
                        <h3 class="text-lg font-semibold text-blue-700">Total Leave</h3>
                        <p class="text-2xl text-blue-900 font-bold"><?php echo htmlspecialchars($user['leave_balance']); ?></p>
                    </div>
                    <div class="bg-yellow-100 p-4 rounded-lg shadow">
                        <h3 class="text-lg font-semibold text-yellow-700">Casual Leave</h3>
                        <p class="text-2xl text-yellow-900 font-bold"><?php echo htmlspecialchars($user['casual_leave_balance']); ?></p>
                    </div>
                    <div class="bg-green-100 p-4 rounded-lg shadow">
                        <h3 class="text-lg font-semibold text-green-700">Sick Leave</h3>
                        <p class="text-2xl text-green-900 font-bold"><?php echo htmlspecialchars($user['sick_leave_balance']); ?></p>
                    </div>

                </div>
            </div>


            <!-- Footer - Full Width -->
            <div class="bg-gray-100 p-4 text-center">
                <a href="manage-users.php" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg transition duration-300 ease-in-out transform hover:scale-105">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Users
                </a>
            </div>
        </div>
    </div>
    <?php require '../includes/admin-footer.php'; ?>
</body>

</html>