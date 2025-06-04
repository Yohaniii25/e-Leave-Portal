<?php
// navbar.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user'])) {
    // Not logged in, redirect or show minimal navbar
    echo '
    <nav class="bg-blue-600 p-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
        <div class="text-white text-2xl font-bold">
            <a href="./admin-dashboard.php">Pannala Pradeshiya Sabha</a>
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

// Start navbar
echo '
<nav class="bg-blue-600 p-4">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <!-- Left side - Brand -->
        <div class="text-white text-2xl font-bold">
            <a href="./admin-dashboard.php">Pannala Pradeshiya Sabha</a>
        </div>
            
            <!-- Center - Navigation Links (Desktop) -->
            <div class="hidden md:block">
                <div class="ml-10 flex items-baseline space-x-4">';

// Admin (designation_id = 7) - Full access
if ($designation_id == 7) {
    echo nav_link('/admin/dashboard.php', 'Dashboard');
    echo nav_link('/admin/manage-users.php', 'Manage Users');
    echo nav_link('/admin/manage-leaves.php', 'Manage Leaves');
    echo nav_link('/admin/reports.php', 'Reports');
}
// Head Of Department (designation_id = 1)
elseif ($designation_id == 1) {
    echo nav_link('/user/hod-leaves.php', 'Department Leave Requests');
    echo nav_link('/user/approved-leaves.php', 'Approved Leaves');
}
// Head office Authorized Officer (designation_id = 5)
elseif ($designation_id == 5) {
    echo nav_link('/user/authorized-leaves.php', 'Leaves for Authorization');
    echo nav_link('/user/reports.php', 'Reports');
}
// Sub office Authorized Officer (designation_id = 6)
elseif ($designation_id == 6) {
    echo nav_link('/user/suboffice-leaves.php', 'SubOffice Leave Requests');
}
// Leave Officer (designation_id = 8)
elseif ($designation_id == 8) {
    echo nav_link('/user/leave-approvals.php', 'Leave Approvals');
    echo nav_link('/user/leave-history.php', 'Leave History');
}
// Head of SubOffice (designation_id = 9)
elseif ($designation_id == 9) {
    echo nav_link('/user/suboffice-dashboard.php', 'SubOffice Dashboard');
    echo nav_link('/user/suboffice-leaves.php', 'SubOffice Leave Requests');
}
// Employee (designation_id = 2) - only personal leaves
elseif ($designation_id == 2) {
    echo nav_link('/user/my-leaves.php', 'My Leave Requests');
    echo nav_link('/user/profile.php', 'My Profile');
}
// Default fallback for unknown roles
else {
    echo nav_link('/user/profile.php', 'Profile');
}

echo '
                </div>
            </div>
            
            <!-- Right side - Logout -->
<div class="hidden md:block">
    <form method="POST" action="/logout.php" class="mt-0">
        <button type="submit" class="bg-blue-700 hover:bg-blue-800 text-white px-4 py-2 rounded-md text-sm font-medium w-full transition duration-150 ease-in-out">
            Logout
        </button>
    </form>
</div>

            
            <!-- Mobile menu button -->
            <div class="md:hidden">
                <button type="button" class="mobile-menu-button text-white hover:bg-blue-700 px-2 py-2 rounded-md">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>
        </div>
        
        <!-- Mobile menu (hidden by default) -->
        <div class="mobile-menu hidden md:hidden">
            <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">';

// Repeat navigation links for mobile
if ($designation_id == 7) {
    echo '<a href="/admin/dashboard.php" class="text-white hover:bg-blue-700 block px-3 py-2 rounded-md text-base font-medium">Dashboard</a>';
    echo '<a href="/admin/manage-users.php" class="text-white hover:bg-blue-700 block px-3 py-2 rounded-md text-base font-medium">Manage Users</a>';
    echo '<a href="/admin/manage-leaves.php" class="text-white hover:bg-blue-700 block px-3 py-2 rounded-md text-base font-medium">Manage Leaves</a>';
    echo '<a href="/admin/reports.php" class="text-white hover:bg-blue-700 block px-3 py-2 rounded-md text-base font-medium">Reports</a>';
} elseif ($designation_id == 1) {
    echo '<a href="/user/hod-leaves.php" class="text-white hover:bg-blue-700 block px-3 py-2 rounded-md text-base font-medium">Department Leave Requests</a>';
    echo '<a href="/user/approved-leaves.php" class="text-white hover:bg-blue-700 block px-3 py-2 rounded-md text-base font-medium">Approved Leaves</a>';
} elseif ($designation_id == 5) {
    echo '<a href="/user/authorized-leaves.php" class="text-white hover:bg-blue-700 block px-3 py-2 rounded-md text-base font-medium">Leaves for Authorization</a>';
    echo '<a href="/user/reports.php" class="text-white hover:bg-blue-700 block px-3 py-2 rounded-md text-base font-medium">Reports</a>';
} elseif ($designation_id == 6) {
    echo '<a href="/user/suboffice-leaves.php" class="text-white hover:bg-blue-700 block px-3 py-2 rounded-md text-base font-medium">SubOffice Leave Requests</a>';
} elseif ($designation_id == 8) {
    echo '<a href="/user/leave-approvals.php" class="text-white hover:bg-blue-700 block px-3 py-2 rounded-md text-base font-medium">Leave Approvals</a>';
    echo '<a href="/user/leave-history.php" class="text-white hover:bg-blue-700 block px-3 py-2 rounded-md text-base font-medium">Leave History</a>';
} elseif ($designation_id == 9) {
    echo '<a href="/user/suboffice-dashboard.php" class="text-white hover:bg-blue-700 block px-3 py-2 rounded-md text-base font-medium">SubOffice Dashboard</a>';
    echo '<a href="/user/suboffice-leaves.php" class="text-white hover:bg-blue-700 block px-3 py-2 rounded-md text-base font-medium">SubOffice Leave Requests</a>';
} elseif ($designation_id == 2) {
    echo '<a href="/user/my-leaves.php" class="text-white hover:bg-blue-700 block px-3 py-2 rounded-md text-base font-medium">My Leave Requests</a>';
    echo '<a href="/user/profile.php" class="text-white hover:bg-blue-700 block px-3 py-2 rounded-md text-base font-medium">My Profile</a>';
} else {
    echo '<a href="/user/profile.php" class="text-white hover:bg-blue-700 block px-3 py-2 rounded-md text-base font-medium">Profile</a>';
}

echo '
                    <form method="POST" action="/logout.php" class="mt-0">
        <button type="submit" class="bg-blue-700 hover:bg-blue-800 text-white px-4 py-2 rounded-md text-sm font-medium w-full transition duration-150 ease-in-out">
            Logout
        </button>
    </form>
            </div>
        </div>
    </div>
</nav>

<script>
// Mobile menu toggle functionality
document.addEventListener("DOMContentLoaded", function() {
    const mobileMenuButton = document.querySelector(".mobile-menu-button");
    const mobileMenu = document.querySelector(".mobile-menu");
    
    if (mobileMenuButton && mobileMenu) {
        mobileMenuButton.addEventListener("click", function() {
            mobileMenu.classList.toggle("hidden");
        });
    }
});
</script>';
