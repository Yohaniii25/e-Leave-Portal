<nav class="bg-blue-600 p-4">
    <div class="container mx-auto flex items-center justify-between">
        <!-- Logo -->
        <div class="text-white text-2xl font-bold">
            <a href="./admin-dashboard.php">Pannala Pradeshiya Sabha</a>
        </div>

        <!-- Navigation Links (Hidden on mobile, shown on larger screens) -->
        <div class="hidden md:flex space-x-6">
            <!-- <a href="./manage-leaves.php" class="text-white hover:bg-blue-700 px-4 py-2 rounded-md">Manage Leaves</a> -->
            <a href="./manage-users.php" class="text-white hover:bg-blue-700 px-4 py-2 rounded-md">Users</a>
            <a href="./add-manual-leaves.php" class="text-white hover:bg-blue-700 px-4 py-2 rounded-md">Add Manual Leaves</a>
            <a href="https://pannalaps.lk/" target="_blank" class="text-white hover:bg-blue-700 px-4 py-2 rounded-md">Visit Website</a>
        </div>

        <!-- Logout Button -->
        <form method="POST" action="../logout.php">
            <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600">Logout</button>
        </form>

        <!-- Mobile Hamburger Menu -->
        <div class="md:hidden flex items-center">
            <button id="hamburger" class="text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>
        </div>
    </div>

    <!-- Mobile Menu (Hidden by default) -->
    <div id="mobile-menu" class="md:hidden bg-blue-600 text-white p-4 hidden">
        <!-- <a href="./manage-leaves.php" class="block py-2 px-4">Manage Leaves</a> -->
        <a href="./manage-users.php" class="block py-2 px-4">Users</a>
        <a href="./add-manual-leaves.php" class="block py-2 px-4">Add Manual Leaves</a>
        <a href="https://pannalaps.lk/" target="_blank" class="block py-2 px-4">Visit Website</a>
        <form method="POST" action="logout.php" class="mt-4">
            <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded-md w-full">Logout</button>
        </form>
    </div>
</nav>

<script>
    // Toggle mobile menu visibility
    const hamburger = document.getElementById('hamburger');
    const mobileMenu = document.getElementById('mobile-menu');

    hamburger.addEventListener('click', () => {
        mobileMenu.classList.toggle('hidden');
    });
</script>
