<?php
session_start();
require '../includes/dbconfig.php';
require '../includes/admin-navbar.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Admin') {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $leave_type = $_POST['leave_type'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $reason = $_POST['reason'] ?? '';

    $admin_id = $_SESSION['user']['id'];

    // Calculate number of days
    $start = new DateTime($start_date);
    $end = new DateTime($end_date);
    $days = $start->diff($end)->days + 1;

    // Get sub_office
    $stmtSub = $conn->prepare('SELECT sub_office FROM wp_pradeshiya_sabha_users WHERE ID = ?');
    $stmtSub->bind_param('i', $user_id);
    $stmtSub->execute();
    $subResult = $stmtSub->get_result()->fetch_assoc();
    $sub_office = $subResult['sub_office'] ?? '';

    // Insert into leave_request with status=2 (approved)
    $stmt = $conn->prepare("INSERT INTO wp_leave_request 
        (user_id, leave_type, leave_start_date, leave_end_date, number_of_days, reason, substitute, sub_office, status) 
        VALUES (?, ?, ?, ?, ?, ?, '', ?, 2)");
    $stmt->bind_param('isssiss', $user_id, $leave_type, $start_date, $end_date, $days, $reason, $sub_office);
    $stmt->execute();

    if ($leave_type === 'Casual Leave') {
        $update = $conn->prepare('UPDATE wp_pradeshiya_sabha_users SET casual_leave_balance = casual_leave_balance - ? WHERE ID = ?');
    } elseif ($leave_type === 'Sick Leave') {
        $update = $conn->prepare('UPDATE wp_pradeshiya_sabha_users SET sick_leave_balance = sick_leave_balance - ? WHERE ID = ?');
    } elseif ($leave_type === 'Duty Leave') {
        $update = $conn->prepare('UPDATE wp_pradeshiya_sabha_users SET duty_leave_count = duty_leave_count + ? WHERE ID = ?');
    }
    $update->bind_param('ii', $days, $user_id);
    $update->execute();

    $log = $conn->prepare('INSERT INTO wp_manual_leave_logs (admin_id, user_id, leave_type, number_of_days, reason) VALUES (?, ?, ?, ?, ?)');
    $log->bind_param('iisds', $admin_id, $user_id, $leave_type, $days, $reason);
    $log->execute();

    echo "<p style='color: green;'>Leave added successfully.</p>";
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manually Add Leave</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body>
    <br>
    <div class="max-w-md mx-auto bg-white rounded-lg shadow-md overflow-hidden">
        <!-- Header -->
        <div class="bg-blue-600 px-6 py-4">
            <h2 class="text-xl font-semibold text-white text-center">Add Leave Manually</h2>
        </div>

        <!-- Form -->
        <form method="post" action="" class="p-6 space-y-6">
            <!-- Search Employee -->
            <div class="space-y-2 relative">
                <label for="user_search" class="block text-sm font-medium text-gray-700">
                    Search Employee <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    id="user_search"
                    placeholder="Type employee name..."
                    autocomplete="off"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                <input type="hidden" name="user_id" id="user_id">
                <div
                    id="user_suggestions"
                    class="absolute z-10 w-full bg-white border border-gray-300 rounded-md shadow-lg max-h-40 overflow-auto mt-1 hidden"></div>
            </div>

            <!-- Leave Type -->
            <div class="space-y-2">
                <label for="leave_type" class="block text-sm font-medium text-gray-700">
                    Leave Type <span class="text-red-500">*</span>
                </label>
                <select
                    id="leave_type"
                    name="leave_type"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white">
                    <option value="" disabled selected>Select leave type</option>
                    <option value="Casual Leave">Casual Leave</option>
                    <option value="Sick Leave">Sick Leave</option>
                    <option value="Duty Leave">Duty Leave</option>
                </select>
            </div>

            <!-- Start Date -->
            <div class="space-y-2">
                <label for="start_date" class="block text-sm font-medium text-gray-700">
                    Start Date <span class="text-red-500">*</span>
                </label>
                <input
                    type="date"
                    id="start_date"
                    name="start_date"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
            </div>

            <!-- End Date -->
            <div class="space-y-2">
                <label for="end_date" class="block text-sm font-medium text-gray-700">
                    End Date <span class="text-red-500">*</span>
                </label>
                <input
                    type="date"
                    id="end_date"
                    name="end_date"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
            </div>

            <!-- Reason -->
            <div class="space-y-2">
                <label for="reason" class="block text-sm font-medium text-gray-700">
                    Reason (optional)
                </label>
                <textarea
                    id="reason"
                    name="reason"
                    rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors resize-none"
                    placeholder="Enter reason for leave (optional)"></textarea>
            </div>

            <!-- Submit Button -->
            <div class="pt-4">
                <button
                    type="submit"
                    name="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-4 rounded-md transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 active:bg-blue-800">
                    Add Leave
                </button>
            </div>
        </form>
    </div>

    <!-- Mobile-specific improvements -->
    <style>
        /* Ensure form inputs are properly sized on iOS */
        input[type="date"]::-webkit-calendar-picker-indicator {
            opacity: 1;
            display: block;
            background: url(data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHZpZXdCb3g9IjAgMCAyMCAyMCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHBhdGggZD0iTTYgMlY2TTEyIDJWNk0zIDEwSDE1TTUgNEgxM0MxNC4xIDQgMTUgNC45IDE1IDZWMTZDMTUgMTcuMSAxNC4xIDE4IDEzIDE4SDVDMy45IDE4IDMgMTcuMSAzIDE2VjZDMyA0LjkgMy45IDQgNSA0WiIgc3Ryb2tlPSIjNjY2IiBzdHJva2Utd2lkdGg9IjIiIHN0cm9rZS1saW5lY2FwPSJyb3VuZCIgc3Ryb2tlLWxpbmVqb2luPSJyb3VuZCIvPgo8L3N2Zz4K) no-repeat;
            background-size: contain;
            color: transparent;
            width: 20px;
            height: 20px;
            border: none;
            background-position: center;
        }

        /* Improve touch targets for mobile */
        @media (max-width: 640px) {

            input,
            select,
            textarea,
            button {
                min-height: 44px;
            }
        }
    </style>


    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $('#user_search').on('input', function() {
            let query = $(this).val();
            if (query.length < 2) {
                $('#user_suggestions').hide();
                return;
            }

            $.ajax({
                url: 'search-users.php',
                method: 'POST',
                data: {
                    query: query
                },
                success: function(data) {
                    let results = JSON.parse(data);
                    let html = '';
                    results.forEach(user => {
                        html += `<div class="user_option" data-id="${user.ID}" data-name="${user.name}" style="padding:5px; cursor:pointer;">${user.name} (${user.ID})</div>`;
                    });
                    $('#user_suggestions').html(html).show();
                }
            });
        });

        $(document).on('click', '.user_option', function() {
            let name = $(this).data('name');
            let id = $(this).data('id');
            $('#user_search').val(name);
            $('#user_id').val(id);
            $('#user_suggestions').hide();
        });
    </script>

<?php require '../includes/admin-footer.php'; ?>
</body>

</html>