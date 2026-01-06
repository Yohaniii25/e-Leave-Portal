<?php
session_start();
require '../includes/dbconfig.php';

if (!isset($_SESSION['user']) || ($_SESSION['user']['designation'] !== 'Employee' && $_SESSION['user']['designation'] !== 'Head Of Department' && $_SESSION['user']['designation'] !== 'Head of SubOffice')) {
    header("Location: ../index.php");
    exit();
}

$user_id        = $_SESSION['user']['id'];
$designation_id = $_SESSION['user']['designation_id'] ?? 0;
$sub_office     = $_SESSION['user']['sub_office'];
$full_name      = $_SESSION['user']['first_name'] . ' ' . $_SESSION['user']['last_name'];
$department_id  = $_SESSION['user']['department_id'] ?? null;

// === GET LEAVE BALANCE (Only what we need) ===
$leaveQuery = $conn->prepare("
    SELECT casual_leave_balance, sick_leave_balance, duty_leave_count 
    FROM wp_pradeshiya_sabha_users WHERE ID = ?
");
$leaveQuery->bind_param("i", $user_id);
$leaveQuery->execute();
$leaveResult = $leaveQuery->get_result();
$leaveData = $leaveResult->fetch_assoc();
$leaveQuery->close();

if (!$leaveData) {
    die("No leave balance found for user ID: " . $user_id);
}

$casual_balance = (float)($leaveData['casual_leave_balance'] ?? 21);
$sick_balance   = (float)($leaveData['sick_leave_balance'] ?? 24);
$duty_count     = (int)($leaveData['duty_leave_count'] ?? 0);

// === GET APPROVED LEAVES ===
$approvedQuery = $conn->prepare("
    SELECT leave_type, SUM(number_of_days) as total_approved
    FROM wp_leave_request
    WHERE user_id = ? AND final_status = 'approved'
    GROUP BY leave_type
");
$approvedQuery->bind_param("i", $user_id);
$approvedQuery->execute();
$approvedResult = $approvedQuery->get_result();

$approved = ['Casual Leave' => 0, 'Sick Leave' => 0, 'Duty Leave' => 0];
while ($row = $approvedResult->fetch_assoc()) {
    $approved[$row['leave_type']] = (float)$row['total_approved'];
}
$approvedQuery->close();

$remaining_casual = $casual_balance - $approved['Casual Leave'];
$remaining_sick   = $sick_balance   - $approved['Sick Leave'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Leave</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
</head>

<body class="bg-gray-50 min-h-screen font-sans">
    <?php include('../includes/user-navbar.php'); ?>

    <div class="container mx-auto py-4 md:py-8 px-4 max-w-4xl">
        <div class="bg-white p-4 md:p-8 rounded-xl shadow-lg border border-gray-100">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
                <h2 class="text-2xl md:text-3xl font-bold text-gray-800 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mr-3 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Request Leave
                </h2>
                <a href="<?php echo ($designation_id == 1 || $designation_id == 9) ? '../admin/dashboard.php' : 'user-dashboard.php'; ?>"
                    class="mt-4 sm:mt-0 inline-flex items-center px-6 py-3 bg-gray-700 hover:bg-gray-800 text-white font-medium rounded-lg shadow transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Dashboard
                </a>
            </div>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded">
                    <p class="text-sm text-green-700"><?= $_SESSION['success_message'];
                                                        unset($_SESSION['success_message']); ?></p>
                </div>
            <?php elseif (isset($_SESSION['error_message'])): ?>
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded">
                    <p class="text-sm text-red-700"><?= $_SESSION['error_message'];
                                                    unset($_SESSION['error_message']); ?></p>
                </div>
            <?php endif; ?>

            <!-- Leave Balance Card - CLEANED UP -->
            <div class="mb-6 bg-blue-50 rounded-lg p-4 border border-blue-100">
                <h3 class="text-lg font-semibold text-gray-700 mb-4">Your Leave Balance</h3>

                <!-- Mobile View -->
                <div class="md:hidden space-y-3">
                    <div class="bg-white p-4 rounded-lg shadow-sm">
                        <div class="flex justify-between text-lg"><span class="font-medium">Casual Leave</span><span class="text-blue-600 font-bold"><?= number_format($remaining_casual, 1) ?> / <?= $casual_balance ?></span></div>
                        <div class="text-sm text-gray-500 mt-1">Used: <?= $approved['Casual Leave'] ?></div>
                    </div>
                    <div class="bg-white p-4 rounded-lg shadow-sm">
                        <div class="flex justify-between text-lg"><span class="font-medium">Sick Leave</span><span class="text-blue-600 font-bold"><?= number_format($remaining_sick, 1) ?> / <?= $sick_balance ?></span></div>
                        <div class="text-sm text-gray-500 mt-1">Used: <?= $approved['Sick Leave'] ?></div>
                    </div>
                    <div class="bg-white p-4 rounded-lg shadow-sm border border-purple-200">
                        <div class="flex justify-between text-lg"><span class="font-medium">Duty Leave Taken</span><span class="text-purple-600 font-bold"><?= $approved['Duty Leave'] ?? 0 ?></span></div>
                    </div>
                </div>

                <!-- Desktop Table -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="min-w-full bg-white rounded-lg shadow">
                        <thead>
                            <tr class="bg-blue-600 text-white">
                                <th class="px-6 py-3 text-left">Leave Type</th>
                                <th class="px-6 py-3 text-center">Entitlement</th>
                                <th class="px-6 py-3 text-center">Used</th>
                                <th class="px-6 py-3 text-center">Remaining</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 font-medium">Casual Leave</td>
                                <td class="px-6 py-4 text-center"><?= $casual_balance ?></td>
                                <td class="px-6 py-4 text-center"><?= $approved['Casual Leave'] ?></td>
                                <td class="px-6 py-4 text-center text-blue-600 font-bold"><?= number_format($remaining_casual, 1) ?></td>
                            </tr>
                            <tr class="bg-gray-50 hover:bg-gray-100">
                                <td class="px-6 py-4 font-medium">Sick Leave</td>
                                <td class="px-6 py-4 text-center"><?= $sick_balance ?></td>
                                <td class="px-6 py-4 text-center"><?= $approved['Sick Leave'] ?></td>
                                <td class="px-6 py-4 text-center text-blue-600 font-bold"><?= number_format($remaining_sick, 1) ?></td>
                            </tr>
                            <tr class="bg-purple-50 hover:bg-purple-100">
                                <td class="px-6 py-4 font-medium">Duty Leave</td>
                                <td class="px-6 py-4 text-center text-gray-500">No limit</td>
                                <td class="px-6 py-4 text-center text-purple-600 font-bold"><?= $approved['Duty Leave'] ?? 0 ?></td>
                                <td class="px-6 py-4 text-center text-gray-500">Tracked only</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Rest of form remains the same -->
            <div class="bg-gray-50 p-6 rounded-lg border border-gray-200">
                <h3 class="text-lg font-semibold text-gray-700 mb-4">Submit Leave Request</h3>
                <form method="POST" action="process-leave.php" class="space-y-4" id="leaveForm">
                    <?php if ($designation_id == 1 || $department_id == 6): ?>
                        <input type="hidden" name="hod_direct_to_auth_officer" value="1">
                    <?php endif; ?>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Leave Type</label>
                        <select name="leave_type" id="leave_type" required class="w-full rounded-md border-gray-300 py-2 px-3 border focus:ring-blue-500 focus:border-blue-500">
                            <option value="">-- Select Type --</option>
                            <option value="Casual Leave">Casual Leave</option>
                            <option value="Sick Leave">Sick Leave</option>
                            <option value="Duty Leave">Duty Leave</option>
                        </select>
                    </div>

                    <div id="half_day_container" class="hidden p-3 bg-yellow-50 border border-yellow-200 rounded-md">
                        <label class="flex items-center">
                            <input type="checkbox" name="is_half_day" id="is_half_day" class="rounded text-blue-600">
                            <span class="ml-2 text-sm font-medium">Half Day (0.5 day)</span>
                        </label>
                        <small class="text-gray-500">Only for single-day Casual or Sick Leave</small>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                            <input type="date" name="leave_start_date" id="start_date" required class="w-full rounded-md border-gray-300 py-2 px-3 border">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                            <input type="date" name="leave_end_date" id="end_date" required class="w-full rounded-md border-gray-300 py-2 px-3 border">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Substitute (Optional)</label>
                        <input type="text" id="substitute" name="substitute" class="w-full rounded-md border-gray-300 py-2 px-3 border" placeholder="Type name to search...">
                        <small class="text-gray-500 block mt-1">Shows available staff from your office/department</small>
                        <div id="on-leave-list" class="mt-2 p-2 bg-red-50 border border-red-200 rounded text-xs">
                            <p class="text-gray-500">Select dates to check availability</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Reason</label>
                        <textarea name="reason" required class="w-full rounded-md border-gray-300 py-2 px-3 border h-32 resize-none" placeholder="Explain your reason for leave..."></textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <button type="reset" class="px-6 py-3 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50 transition">
                            Reset Form
                        </button>
                        <button type="submit" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-md shadow transition">
                            Submit Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JavaScript remains the same -->
    <script>
        $(document).ready(function() {
            // Half-day toggle
            $('#leave_type').on('change', function() {
                const type = $(this).val();
                if (type === 'Casual Leave' || type === 'Sick Leave') {
                    $('#half_day_container').removeClass('hidden');
                } else {
                    $('#half_day_container').addClass('hidden');
                    $('#is_half_day').prop('checked', false);
                }
            });

            // Prevent half-day on multi-day
            $('#leaveForm').on('submit', function(e) {
                if ($('#is_half_day').is(':checked')) {
                    const start = new Date($('#start_date').val());
                    const end = new Date($('#end_date').val());
                    if (end > start) {
                        alert('Half day is only allowed for a single day.');
                        e.preventDefault();
                    }
                }
            });

            // Autocomplete substitute
            $("#substitute").autocomplete({
                minLength: 2,
                delay: 300,
                source: function(request, response) {
                    const start = $('#start_date').val();
                    const end = $('#end_date').val();

                    if (!start || !end) {
                        response([]);
                        $('#on-leave-list').html('<p class="text-gray-500 text-sm">Select dates first</p>');
                        return;
                    }

                    $.ajax({
                        url: 'search_users.php',
                        data: {
                            term: request.term,
                            start_date: start,
                            end_date: end,
                            department_id: current_department_id,
                            user_id: current_user_id 
                        },
                        success: function(data) {
                            response(data.suggestions || []);

                            const list = $('#on-leave-list');
                            if (data.on_leave && data.on_leave.length > 0) {
                                let html = '<p class="font-medium text-red-700 mb-1">On leave during this period:</p><ul class="list-disc list-inside space-y-1 text-sm">';
                                data.on_leave.forEach(u => {
                                    html += `<li><strong>${u.name}</strong> - ${u.type}: ${u.dates}</li>`;
                                });
                                html += '</ul>';
                                list.html(html);
                            } else {
                                list.html('<p class="text-green-600 text-sm">✓ No one in your department is on leave</p>');
                            }
                        },
                        error: function() {
                            response([]);
                            $('#on-leave-list').html('<p class="text-red-500 text-sm">Error loading data.</p>');
                        }
                    });
                },
                select: function(e, ui) {
                    $("#substitute").val(ui.item.value);
                    return false;
                }
            });
        });
    </script>

    <script>
        const current_user_id = <?= json_encode($user_id) ?>;
        const current_department_id = <?= json_encode($department_id) ?>;
    </script>
</body>

</html>