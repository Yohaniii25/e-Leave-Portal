<?php
session_start();
require '../includes/dbconfig.php';


if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Employee') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user']['id'];
$sub_office = $_SESSION['user']['sub_office'];
$full_name = $_SESSION['user']['first_name'] . ' ' . $_SESSION['user']['last_name'];
$department = $_SESSION['user']['department'] ?? '';

$user_id = $_SESSION['user']['id'];

// Get user info + leave balances
$stmt = $conn->prepare("SELECT * FROM wp_pradeshiya_sabha_users WHERE ID = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Leave</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 font-sans">

    <?php include('../includes/user-navbar.php'); ?>

    <div class="max-w-4xl mx-auto mt-4">
        <!-- Profile Card -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-blue-500 to-indigo-600 px-6 py-8 text-white">
                <div class="flex flex-col md:flex-row md:items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h1>
                        <p class="mt-1 opacity-90"><?= htmlspecialchars($user['designation']) ?> | <?= htmlspecialchars($user['department']) ?></p>
                    </div>
                    <div class="mt-4 md:mt-0">
                        <div class="inline-flex items-center justify-center bg-white bg-opacity-20 backdrop-filter backdrop-blur-sm rounded-full w-24 h-24">
                            <span class="text-4xl"><?= substr(htmlspecialchars($user['first_name']), 0, 1) . substr(htmlspecialchars($user['last_name']), 0, 1) ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="p-6">
                <!-- Personal Information -->
                <div class="mb-8">
                    <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        Personal Information
                    </h2>

                    <div class="bg-gray-50 rounded-xl p-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-8">


                            <div>
                                <p class="text-sm text-gray-500">Gender</p>
                                <p class="font-medium text-gray-800"><?= htmlspecialchars($user['gender']) ?></p>
                            </div>

                            <div>
                                <p class="text-sm text-gray-500">Email</p>
                                <p class="font-medium text-gray-800 break-all"><?= htmlspecialchars($user['email']) ?></p>
                            </div>

                            <div>
                                <p class="text-sm text-gray-500">Phone</p>
                                <p class="font-medium text-gray-800"><?= htmlspecialchars($user['phone_number']) ?></p>
                            </div>

                            <div>
                                <p class="text-sm text-gray-500">NIC</p>
                                <p class="font-medium text-gray-800"><?= htmlspecialchars($user['NIC']) ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Employment Details -->
                <div class="mb-8">
                    <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        Employment Details
                    </h2>

                    <div class="bg-gray-50 rounded-xl p-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-8">
                            <div>
                                <p class="text-sm text-gray-500">Department</p>
                                <p class="font-medium text-gray-800"><?= htmlspecialchars($user['department']) ?></p>
                            </div>

                            <div>
                                <p class="text-sm text-gray-500">Designation</p>
                                <p class="font-medium text-gray-800"><?= htmlspecialchars($user['designation']) ?></p>
                            </div>

                            <div>
                                <p class="text-sm text-gray-500">Sub Office</p>
                                <p class="font-medium text-gray-800"><?= htmlspecialchars($user['sub_office']) ?></p>
                            </div>

                            <div>
                                <p class="text-sm text-gray-500">Date of Joining</p>
                                <p class="font-medium text-gray-800"><?= htmlspecialchars($user['date_of_joining']) ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Leave Balances -->
                <div>
                    <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        Leave Balances
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div class="bg-blue-50 border border-blue-100 rounded-lg p-4">
                            <p class="text-sm text-blue-600 mb-1">Total Leave</p>
                            <p class="text-2xl font-bold text-blue-800"><?= $user['leave_balance'] ?></p>
                            <p class="text-sm text-blue-600">days</p>
                        </div>

                        <div class="bg-green-50 border border-green-100 rounded-lg p-4">
                            <p class="text-sm text-green-600 mb-1">Casual Leave</p>
                            <p class="text-2xl font-bold text-green-800"><?= $user['casual_leave_balance'] ?></p>
                            <p class="text-sm text-green-600">days</p>
                        </div>

                        <div class="bg-red-50 border border-red-100 rounded-lg p-4">
                            <p class="text-sm text-red-600 mb-1">Sick Leave</p>
                            <p class="text-2xl font-bold text-red-800"><?= $user['sick_leave_balance'] ?></p>
                            <p class="text-sm text-red-600">days</p>
                        </div>

                        <div class="bg-purple-50 border border-purple-100 rounded-lg p-4">
                            <p class="text-sm text-purple-600 mb-1">Annual Leave</p>
                            <p class="text-2xl font-bold text-purple-800"><?= $user['annual_leave_balance'] ?></p>
                            <p class="text-sm text-purple-600">days</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

</body>

</html>