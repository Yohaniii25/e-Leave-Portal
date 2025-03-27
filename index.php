<?php

require './includes/dbconfig.php';
require './includes/LoginController.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $sub_office = $_POST['sub_office'];
    $password = $_POST['password'];

    $loginController = new LoginController($conn);
    $error_message = $loginController->login($email, $sub_office, $password);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pannala Pradeshiya Sabha - e-Leave Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex justify-center items-center h-screen bg-gray-50">

    <div class="bg-white p-8 rounded-xl shadow-lg w-full max-w-md">
        <div class="text-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Pannala Pradeshiya Sabha</h1>
            <p class="text-xl font-semibold text-gray-600">e-Leave Portal</p>
        </div>

        <!-- Display error message if login fails -->
        <?php if (isset($error_message)): ?>
            <div class="mb-4 text-red-500 text-center"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-4">
                <label for="email" class="block text-gray-700 text-sm font-medium mb-2">Email</label>
                <input type="email" id="email" name="email" class="w-full p-3 border border-gray-300 rounded-md" required>
            </div>

            <!-- Sub Office Dropdown -->
            <div class="mb-4">
                <label for="sub_office" class="block text-gray-700 text-sm font-medium mb-2">Sub Office</label>
                <select id="sub_office" name="sub_office" class="w-full p-3 border border-gray-300 rounded-md" required>
                    <option value="Head Office">Head Office</option>
                    <option value="Pannala Sub-Office">Pannala Sub-Office</option>
                    <option value="Makandura Sub-Office">Makandura Sub-Office</option>
                    <option value="Yakkwila Sub-Office">Yakkwila Sub-Office</option>
                    <option value="Hamangalla Sub-Office">Hamangalla Sub-Office</option>
                </select>
            </div>

            <div class="mb-6">
                <label for="password" class="block text-gray-700 text-sm font-medium mb-2">Password</label>
                <input type="password" id="password" name="password" class="w-full p-3 border border-gray-300 rounded-md" required>
            </div>

            <div class="text-center">
                <button type="submit" class="w-full bg-blue-500 text-white p-3 rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-300">
                    Login
                </button>
            </div>
        </form>
    </div>

</body>
</html>
