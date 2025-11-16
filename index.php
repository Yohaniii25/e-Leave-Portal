<?php
// === ENABLE ERROR DISPLAY (TEMPORARY) ===
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// === SAFELY INCLUDE FILES ===
$includes_path = __DIR__ . '/includes/';
$db_file = $includes_path . 'dbconfig.php';
$login_file = $includes_path . 'LoginController.php';

$error_message = '';

// Check if files exist
if (!file_exists($db_file)) {
    die("<h3 style='color:red'>ERROR: dbconfig.php not found at: $db_file</h3>");
}
if (!file_exists($login_file)) {
    die("<h3 style='color:red'>ERROR: LoginController.php not found at: $login_file</h3>");
}

require $db_file;
require $login_file;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email'] ?? '');
    $sub_office = trim($_POST['sub_office'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($sub_office) || empty($password)) {
        $error_message = "All fields are required.";
    } else {
        $loginController = new LoginController($conn);
        $error_message = $loginController->login($email, $sub_office, $password);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pannala Pradeshiya Sabha - e-Leave Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .error { color: #dc2626; background: #fee2e2; padding: 10px; border-radius: 6px; margin-bottom: 16px; text-align: center; }
    </style>
</head>
<body class="flex justify-center items-center min-h-screen bg-gray-50">
    <div class="bg-white p-8 rounded-xl shadow-lg w-full max-w-md">
        <div class="text-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Pannala Pradeshiya Sabha</h1>
            <p class="text-xl font-semibold text-gray-600">e-Leave Portal</p>
        </div>

        <!-- Error Message -->
        <?php if ($error_message): ?>
            <div class="error"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>

        <!-- Login Form -->
        <form method="POST" action="">
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-medium mb-2">Email</label>
                <input type="email" name="email" class="w-full p-3 border border-gray-300 rounded-md" required>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-medium mb-2">Sub Office</label>
                <select name="sub_office" class="w-full p-3 border border-gray-300 rounded-md" required>
                    <option value="">-- Select Sub Office --</option>
                    <option value="Head Office">Head Office</option>
                    <option value="Pannala Sub-Office">Pannala Sub-Office</option>
                    <option value="Makandura Sub-Office">Makandura Sub-Office</option>
                    <option value="Yakkwila Sub-Office">Yakkwila Sub-Office</option>
                    <option value="Hamangalla Sub-Office">Hamangalla Sub-Office</option>
                </select>
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-medium mb-2">Password</label>
                <input type="password" name="password" class="w-full p-3 border border-gray-300 rounded-md" required>
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white p-3 rounded-md hover:bg-blue-700 transition">
                Login
            </button>
        </form>
    </div>
</body>
</html>