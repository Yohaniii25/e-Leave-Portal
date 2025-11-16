<?php
session_start();
require '../includes/dbconfig.php';
require '../includes/admin-navbar.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../index.php");
    exit();
}

$email = $_SESSION['user']['email'];

// === CHECK ADMIN ===
$stmt = $conn->prepare("
    SELECT d.designation_name 
    FROM wp_pradeshiya_sabha_users u
    LEFT JOIN wp_designations d ON u.designation_id = d.designation_id
    WHERE u.email = ?
");
$stmt->bind_param("s", $email);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$row || strcasecmp(trim($row['designation_name']), 'Admin') !== 0) {
    header("Location: ../index.php");
    exit();
}

$success_message = $error_message = '';
$selected_user = null;
$balance = null;

// === FORM SUBMISSION ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    $user_id = (int)$_POST['user_id'];
    $leave_type = $_POST['leave_type'] ?? '';
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';
    $reason = trim($_POST['reason'] ?? '');
    $is_half_day = isset($_POST['is_half_day']) ? 1 : 0;
    $admin_id = $_SESSION['user']['id'];

    // === VALIDATION ===
    if (empty($user_id) || empty($leave_type) || empty($start_date) || empty($end_date)) {
        $error_message = "All required fields must be filled.";
    } elseif ($start_date > $end_date) {
        $error_message = "End date cannot be before start date.";
    } else {
        $start = new DateTime($start_date);
        $end = new DateTime($end_date);
        $full_days = $start->diff($end)->days + 1;

        if ($is_half_day) {
            if (!in_array($leave_type, ['Casual Leave', 'Sick Leave'])) {
                $error_message = "Half day is only allowed for Casual or Sick Leave.";
            } elseif ($full_days > 1) {
                $error_message = "Half day can only be applied to a single day.";
            } else {
                $number_of_days = 0.5;
            }
        } else {
            $number_of_days = $full_days;
        }

        if (!$error_message) {
            // === GET SUB OFFICE ===
            $stmtUser = $conn->prepare("SELECT sub_office FROM wp_pradeshiya_sabha_users WHERE ID = ?");
            $stmtUser->bind_param("i", $user_id);
            $stmtUser->execute();
            $subResult = $stmtUser->get_result()->fetch_assoc();
            $sub_office = $subResult['sub_office'] ?? '';
            $stmtUser->close();

            // === CHECK BALANCE FROM wp_leave_request ONLY ===
            $usedQuery = $conn->prepare("
                SELECT SUM(CASE WHEN leave_type = 'Casual Leave' THEN number_of_days ELSE 0 END) as casual_used,
                       SUM(CASE WHEN leave_type = 'Sick Leave' THEN number_of_days ELSE 0 END) as sick_used
                FROM wp_leave_request 
                WHERE user_id = ? AND status = 2
            ");
            $usedQuery->bind_param("i", $user_id);
            $usedQuery->execute();
            $used = $usedQuery->get_result()->fetch_assoc();
            $usedQuery->close();

            $casual_used = (float)($used['casual_used'] ?? 0);
            $sick_used   = (float)($used['sick_used'] ?? 0);
            $total_used  = $casual_used + $sick_used;

            $remaining_total = 45 - $total_used;

            if ($leave_type !== 'Duty Leave' && $remaining_total < $number_of_days) {
                $error_message = "Not enough leave balance. Available: $remaining_total days.";
            } else {
                // === INSERT INTO wp_leave_request (APPROVED) ===
                $status = 2;
                $final_status = 'approved';
                $substitute = '';

                $insert = $conn->prepare("
                    INSERT INTO wp_leave_request 
                    (user_id, leave_type, leave_start_date, leave_end_date, number_of_days, reason, substitute, sub_office, status, final_status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $insert->bind_param("isssdsssis", $user_id, $leave_type, $start_date, $end_date, $number_of_days, $reason, $substitute, $sub_office, $status, $final_status);

                if ($insert->execute()) {
                    $request_id = $insert->insert_id;

                    // === LOG ===
                    $log = $conn->prepare("
                        INSERT INTO wp_manual_leave_logs 
                        (admin_id, user_id, leave_type, number_of_days, reason, created_at) 
                        VALUES (?, ?, ?, ?, ?, NOW())
                    ");
                    if ($log) {
                        $log->bind_param("iisds", $admin_id, $user_id, $leave_type, $number_of_days, $reason);
                        $log->execute();
                        $log->close();
                    }

                    $success_message = "Leave added successfully. Balance updated from wp_leave_request.";
                } else {
                    $error_message = "Failed to add leave.";
                }
                $insert->close();
            }
        }
    }
}

// === SEARCH & SELECT USER ===
if (isset($_GET['user_id'])) {
    $user_id = (int)$_GET['user_id'];
    $stmt = $conn->prepare("
        SELECT ID, CONCAT(first_name, ' ', last_name) AS name, sub_office
        FROM wp_pradeshiya_sabha_users 
        WHERE ID = ?
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $selected_user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($selected_user) {
        // === GET BALANCE FROM wp_leave_request ONLY ===
        $usedQuery = $conn->prepare("
            SELECT 
                SUM(CASE WHEN leave_type = 'Casual Leave' THEN number_of_days ELSE 0 END) as casual_used,
                SUM(CASE WHEN leave_type = 'Sick Leave' THEN number_of_days ELSE 0 END) as sick_used,
                SUM(CASE WHEN leave_type = 'Duty Leave' THEN number_of_days ELSE 0 END) as duty_used
            FROM wp_leave_request 
            WHERE user_id = ? AND status = 2
        ");
        $usedQuery->bind_param("i", $user_id);
        $usedQuery->execute();
        $used = $usedQuery->get_result()->fetch_assoc();
        $usedQuery->close();

        $casual_used = (float)($used['casual_used'] ?? 0);
        $sick_used   = (float)($used['sick_used'] ?? 0);
        $duty_used   = (float)($used['duty_used'] ?? 0);
        $total_used  = $casual_used + $sick_used;
        $remaining   = 45 - $total_used;

        $balance = [
            'casual' => 21 - $casual_used,
            'sick'   => 24 - $sick_used,
            'total'  => $remaining,
            'duty'   => $duty_used
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Manual Leave</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-gray-50">

<div class="max-w-3xl mx-auto mt-10 p-6 bg-white rounded-xl shadow-lg">
    <h2 class="text-2xl font-bold text-center text-blue-700 mb-6">Add Leave Manually</h2>

    <!-- Messages -->
    <?php if ($success_message): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <?= htmlspecialchars($success_message) ?>
        </div>
    <?php elseif ($error_message): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            <?= htmlspecialchars($error_message) ?>
        </div>
    <?php endif; ?>

    <!-- Search Employee -->
    <div class="mb-6 relative">
        <label class="block text-sm font-medium text-gray-700 mb-1">Search Employee <span class="text-red-500">*</span></label>
        <input type="text" id="user_search" placeholder="Type name or ID..." autocomplete="off"
               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
        <input type="hidden" id="selected_user_id" value="<?= $selected_user['ID'] ?? '' ?>">
        <div id="user_suggestions" class="absolute z-10 w-full bg-white border border-gray-300 rounded-md shadow-lg max-h-48 overflow-auto mt-1 hidden"></div>
    </div>

    <?php if ($selected_user): ?>
    <!-- Balance Card -->
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-5 rounded-lg mb-6 border border-blue-200">
        <p class="font-bold text-blue-900 mb-3">Selected: <?= htmlspecialchars($selected_user['name']) ?> (ID: <?= $selected_user['ID'] ?>)</p>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-3 text-sm">
            <div class="bg-white p-3 rounded text-center shadow-sm">
                <div class="font-medium text-gray-700">Total Left</div>
                <div class="text-2xl font-bold text-green-600"><?= $balance['total'] ?></div>
            </div>
            <div class="bg-white p-3 rounded text-center shadow-sm">
                <div class="font-medium text-gray-700">Casual Left</div>
                <div class="text-xl font-bold text-blue-600"><?= $balance['casual'] ?></div>
            </div>
            <div class="bg-white p-3 rounded text-center shadow-sm">
                <div class="font-medium text-gray-700">Sick Left</div>
                <div class="text-xl font-bold text-blue-600"><?= $balance['sick'] ?></div>
            </div>
            <div class="bg-white p-3 rounded text-center shadow-sm bg-purple-50">
                <div class="font-medium text-gray-700">Duty Taken</div>
                <div class="text-xl font-bold text-purple-600"><?= $balance['duty'] ?></div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Form -->
    <form method="post" action="" class="space-y-5" id="leaveForm">
        <input type="hidden" name="user_id" value="<?= $selected_user['ID'] ?? '' ?>">

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Leave Type <span class="text-red-500">*</span></label>
            <select name="leave_type" id="leave_type" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                <option value="">-- Select Type --</option>
                <option value="Casual Leave" <?= ($selected_user && $balance['total'] <= 0) ? 'disabled' : '' ?>>Casual Leave (<?= $balance['casual'] ?? '?' ?> left)</option>
                <option value="Sick Leave" <?= ($selected_user && $balance['total'] <= 0) ? 'disabled' : '' ?>>Sick Leave (<?= $balance['sick'] ?? '?' ?> left)</option>
                <option value="Duty Leave">Duty Leave</option>
            </select>
        </div>

        <div id="half_day_container" class="hidden p-3 bg-yellow-50 border border-yellow-200 rounded-md">
            <label class="flex items-center">
                <input type="checkbox" name="is_half_day" id="is_half_day" class="rounded text-blue-600">
                <span class="ml-2 text-sm font-medium text-gray-700">Half Day (0.5 day)</span>
            </label>
            <p class="text-xs text-gray-500 mt-1">Only for single-day Casual or Sick Leave.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Start Date <span class="text-red-500">*</span></label>
                <input type="date" name="start_date" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">End Date <span class="text-red-500">*</span></label>
                <input type="date" name="end_date" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Reason (Optional)</label>
            <textarea name="reason" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
        </div>

        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-md transition">
            Add Leave (Approved)
        </button>
    </form>
</div>

<script>
$(document).ready(function() {
    let debounceTimer;
    $('#user_search').on('input', function() {
        clearTimeout(debounceTimer);
        const query = $(this).val().trim();
        if (query.length < 2) {
            $('#user_suggestions').hide();
            return;
        }
        debounceTimer = setTimeout(() => {
            $.post('search-users.php', { query: query }, function(data) {
                try {
                    const results = JSON.parse(data);
                    let html = '';
                    results.forEach(user => {
                        html += `<div class="p-2 hover:bg-blue-50 cursor-pointer border-b" 
                                        data-id="${user.ID}" data-name="${user.name}">
                                    ${user.name} <span class="text-gray-500 text-xs">(${user.ID})</span>
                                 </div>`;
                    });
                    $('#user_suggestions').html(html).toggle(results.length > 0);
                } catch (e) { console.error(e); }
            });
        }, 300);
    });

    $(document).on('click', '.cursor-pointer', function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        $('#user_search').val(name);
        $('#selected_user_id').val(id);
        $('#user_suggestions').hide();
        window.location.href = '?user_id=' + id;
    });

    $('#leave_type').on('change', function() {
        const type = $(this).val();
        const container = $('#half_day_container');
        if (type === 'Casual Leave' || type === 'Sick Leave') {
            container.removeClass('hidden');
        } else {
            container.addClass('hidden');
            $('#is_half_day').prop('checked', false);
        }
    });

    $('#leaveForm').on('submit', function(e) {
        if ($('#is_half_day').is(':checked')) {
            const start = new Date($('input[name="start_date"]').val());
            const end = new Date($('input[name="end_date"]').val());
            if ((end - start) / (1000*60*60*24) > 0) {
                e.preventDefault();
                alert('Half day only for single day.');
            }
        }
    });
});
</script>

<?php require '../includes/admin-footer.php'; ?>
</body>
</html>