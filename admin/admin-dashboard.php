<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}

require '../includes/admin-navbar.php'; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">


    <div class="container mx-auto mt-8">
        <div class="bg-white p-6 rounded-lg shadow-lg">
            <h1 class="text-3xl font-bold text-gray-800">Admin Dashboard</h1>
            <p class="text-lg text-gray-600 mt-4">Welcome, Admin!</p>
            <p class="text-gray-700 mt-6">Here you can manage users, leaves, and more.</p>
        </div>

     
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mt-8">
            <!-- Manage Leaves Card -->
            <div class="bg-blue-500 text-white p-6 rounded-lg shadow-lg hover:bg-blue-600">
                <h2 class="text-xl font-semibold">Manage Leaves</h2>
                <p class="mt-2">Approve or reject employee leave requests.</p>
                <a href="./manage-leaves.php" class="text-white mt-4 inline-block">Go to Manage Leaves →</a>
            </div>

         
            <div class="bg-green-500 text-white p-6 rounded-lg shadow-lg hover:bg-green-600">
                <h2 class="text-xl font-semibold">Manage Users</h2>
                <p class="mt-2">Add, update, or remove users.</p>
                <a href="./manage-users.php" class="text-white mt-4 inline-block">Go to Manage Users →</a>
            </div>

           
            <div class="bg-yellow-500 text-white p-6 rounded-lg shadow-lg hover:bg-yellow-600">
                <h2 class="text-xl font-semibold">Visit Website</h2>
                <p class="mt-2">Visit the official website for more details.</p>
                <a href="https://testing.sltdigitalweb.lk/pannalaps" target="_blank" class="text-white mt-4 inline-block">Go to Website →</a>
            </div>
        </div>
    </div>
    <br>
<?php require '../includes/admin-footer.php'; ?>
</body>
</html>
