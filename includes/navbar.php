<?php
// navbar.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user'])) {
    // Not logged in, show minimal navbar
    echo '
    <nav class="bg-blue-600 p-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="text-white text-2xl font-bold">
                    <a href="./dashboard.php">Pannala Pradeshiya Sabha</a>
                </div>
                <div>
                    <a href="../index.php" class="text-white hover:bg-blue-700 px-3 py-2 rounded-md text-sm font-medium transition duration-150 ease-in-out">Login</a>
                </div>
            </div>
        </div>
    </nav>';
    exit();
}

$designation_id = $_SESSION['user']['designation_id'] ?? null;
$department_id = $_SESSION['user']['department_id'] ?? null;
$sub_office = $_SESSION['user']['sub_office'] ?? null;

// Helper function to create menu link
function nav_link($href, $text)
{
    return "<a href=\"$href\" class=\"text-white hover:bg-blue-700 px-3 py-2 rounded-md text-sm font-medium transition duration-150 ease-in-out\">$text</a>";
}

echo '
<nav class="bg-blue-600 p-4">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <!-- Left side - Brand -->
            <div class="text-white text-2xl font-bold">
                <a href="./dashboard.php">Pannala Pradeshiya Sabha</a>
            </div>

            <!-- Center - Navigation Links (Desktop) -->
            <div class="hidden md:flex space-x-6">';

// Admin (designation_id = 7) - Full access
if ($designation_id == 7) {
    echo nav_link('/admin/dashboard.php', 'Dashboard');
    echo nav_link('/admin/manage-users.php', 'Manage Users');
    echo nav_link('/admin/manage-leaves.php', 'Manage Leaves');
    echo nav_link('/admin/reports.php', 'Reports');
}
// Head Of Department (designation_id = 1)
elseif ($designation_id == 1) {
    echo nav_link('../admin/hod-leaves.php', 'Department Leave Requests');
    echo nav_link('../admin/approved-leaves.php', 'Approved Leaves');
}
// Head office Authorized Officer (designation_id = 5)
elseif ($designation_id == 5 || $designation_id == 3) {
    echo nav_link('../admin/head-of-ps-approval.php', 'HOD Approved Leaves');
    echo nav_link('../admin/reports.php', 'Reports');
}
// Sub office Authorized Officer (designation_id = 6)
elseif ($designation_id == 6) {
    echo nav_link('/user/suboffice-leaves.php', 'SubOffice Leave Requests');
}
// Leave Officer (designation_id = 8)
elseif ($designation_id == 8) {
    echo nav_link('../admin/leave-approvals.php', 'Leave Approvals');
    echo nav_link('../admin/leave-history.php', 'Leave History');
}
// Head of SubOffice (designation_id = 9)
elseif ($designation_id == 9) {
    echo nav_link('/user/suboffice-dashboard.php', 'SubOffice Dashboard');
    echo nav_link('/user/suboffice-leaves.php', 'SubOffice Leave Requests');
}
// Employee (designation_id = 2)
elseif ($designation_id == 2) {
    echo nav_link('/user/my-leaves.php', 'My Leave Requests');
    echo nav_link('/user/profile.php', 'My Profile');
}
// Default fallback
else {
    echo nav_link('/user/profile.php', 'Profile');
}

echo '
            </div>

            <!-- Right side - Logout -->
            <div class="hidden md:block">
                <form method="POST" action="../logout.php" class="mt-0">
                    <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md text-sm font-medium w-full transition duration-150 ease-in-out">
                        Logout
                    </button>
                </form>
            </div>

            <!-- Mobile menu button -->
            <div class="md:hidden flex items-center">
                <button id="hamburger" class="text-white focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile menu (hidden by default) -->
        <div id="mobile-menu" class="md:hidden bg-blue-600 text-white p-4 hidden">
';

if ($designation_id == 7) {
    echo '<a href="/admin/dashboard.php" class="block py-2 px-4 hover:bg-blue-700 rounded-md">Dashboard</a>';
    echo '<a href="/admin/manage-users.php" class="block py-2 px-4 hover:bg-blue-700 rounded-md">Manage Users</a>';
    echo '<a href="/admin/manage-leaves.php" class="block py-2 px-4 hover:bg-blue-700 rounded-md">Manage Leaves</a>';
    echo '<a href="/admin/reports.php" class="block py-2 px-4 hover:bg-blue-700 rounded-md">Reports</a>';
} elseif ($designation_id == 1) {
    echo '<a href="/user/hod-leaves.php" class="block py-2 px-4 hover:bg-blue-700 rounded-md">Department Leave Requests</a>';
    echo '<a href="/user/approved-leaves.php" class="block py-2 px-4 hover:bg-blue-700 rounded-md">Approved Leaves</a>';
} elseif ($designation_id == 5 || $designation_id == 3) {
    echo '<a href="/admin/hod-approved-leaves.php" class="block py-2 px-4 hover:bg-blue-700 rounded-md">HOD Approved Leaves</a>';
    echo '<a href="/admin/reports.php" class="block py-2 px-4 hover:bg-blue-700 rounded-md">Reports</a>';
} elseif ($designation_id == 6) {
    echo '<a href="/user/suboffice-leaves.php" class="block py-2 px-4 hover:bg-blue-700 rounded-md">SubOffice Leave Requests</a>';
} elseif ($designation_id == 8) {
    echo '<a href="/user/leave-approvals.php" class="block py-2 px-4 hover:bg-blue-700 rounded-md">Leave Approvals</a>';
    echo '<a href="/user/leave-history.php" class="block py-2 px-4 hover:bg-blue-700 rounded-md">Leave History</a>';
} elseif ($designation_id == 9) {
    echo '<a href="/user/suboffice-dashboard.php" class="block py-2 px-4 hover:bg-blue-700 rounded-md">SubOffice Dashboard</a>';
    echo '<a href="/user/suboffice-leaves.php" class="block py-2 px-4 hover:bg-blue-700 rounded-md">SubOffice Leave Requests</a>';
} elseif ($designation_id == 2) {
    echo '<a href="/user/my-leaves.php" class="block py-2 px-4 hover:bg-blue-700 rounded-md">My Leave Requests</a>';
    echo '<a href="/user/profile.php" class="block py-2 px-4 hover:bg-blue-700 rounded-md">My Profile</a>';
} else {
    echo '<a href="/user/profile.php" class="block py-2 px-4 hover:bg-blue-700 rounded-md">Profile</a>';
}

echo '
            <form method="POST" action="../logout.php" class="mt-4">
                <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md w-full transition duration-150 ease-in-out">
                    Logout
                </button>
            </form>
        </div>
    </div>
</nav>

<script>
    const hamburger = document.getElementById("hamburger");
    const mobileMenu = document.getElementById("mobile-menu");

    hamburger.addEventListener("click", () => {
        mobileMenu.classList.toggle("hidden");
    });
</script>
';
?>
