<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<aside id="mobile-sidebar" class="bg-white w-64 space-y-4 flex flex-col h-screen fixed inset-y-0 left-0 
           transform -translate-x-full md:translate-x-0 
           transition duration-300 ease-in-out z-50 shadow-xl border-r">

    <div class="p-6 border-b">
        <div class="flex justify-between">
            <h1 class="text-xl font-extrabold text-blue-800 tracking-wide font-sans">HDPS</h1>
            <button id="sidebar-close"
                class="md:hidden text-gray-600 hover:text-gray-900 text-2xl font-bold leading-none">&times;</button>
        </div>
        <p class="text-xs text-gray-500">Hanging Parrot Digital Solutions</p>
    </div>

    <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
        <a href="dashboard.php"
            class="flex items-center px-3 py-2 rounded-lg transition duration-150 ease-in-out 
            <?php echo ($currentPage == 'dashboard.php') ? 'bg-blue-100 text-blue-800 font-semibold' : 'text-gray-600 hover:bg-gray-100'; ?>">
            <svg class="h-5 w-5 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0h6" />
            </svg>
            <span>Dashboard</span>
        </a>

        <p class="text-xs uppercase text-gray-400 font-semibold mt-4 mb-1 pt-2 px-3">Point of Sale (POS)</p>

        <a href="process_sales.php"
            class="flex items-center px-3 py-2 rounded-lg transition duration-150 ease-in-out 
            <?php echo ($currentPage == 'process_sales.php') ? 'bg-blue-100 text-blue-800 font-semibold' : 'text-gray-600 hover:bg-gray-100'; ?>">
            <svg class="h-5 w-5 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
            </svg>
            <span>Process Sales</span>
        </a>

        <a href="sales_report.php"
            class="flex items-center px-3 py-2 rounded-lg transition duration-150 ease-in-out 
            <?php echo ($currentPage == 'sales_report.php') ? 'bg-blue-100 text-blue-800 font-semibold' : 'text-gray-600 hover:bg-gray-100'; ?>">
            <svg class="h-5 w-5 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            <span>Sales Reports</span>
        </a>

        <!-- Inventory Management Dropdown -->
        <div x-data="{ open: <?php echo in_array($currentPage, [
            'categories.php',
            'item_catalog.php',
            'stock_levels.php',
            'purchase_orders.php',
            'suppliers.php',
            'inventory_reports.php'
        ]) ? 'true' : 'false'; ?> }" class="px-3">
            <button @click="open = !open"
                class="flex items-center justify-between w-full py-2 text-left text-gray-600 hover:bg-gray-100 rounded-lg transition duration-150 ease-in-out">
                <span class="text-xs uppercase font-semibold text-gray-400">Inventory Management</span>
                <svg :class="{ 'rotate-180': open }" class="h-4 w-4 transform transition-transform duration-200"
                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <!-- Dropdown Links -->
            <div x-show="open" x-collapse class="mt-2 space-y-1 pl-3 border-l border-gray-200">
                <?php
                $links = [
                    'categories.php' => 'Categories',
                    'item_catalog.php' => 'Item Catalog',
                    'stock_levels.php' => 'Stock Levels',
                    'purchase_orders.php' => 'Purchase Orders',
                    'suppliers.php' => 'Suppliers',
                    'inventory_reports.php' => 'Inventory Reports'
                ];

                foreach ($links as $file => $label) {
                    $isActive = ($currentPage == $file) ? 'bg-blue-100 text-blue-800 font-semibold' : 'text-gray-600 hover:bg-gray-100';
                    echo "
                <a href='$file'
                    class='flex items-center px-3 py-2 rounded-lg transition duration-150 ease-in-out $isActive'>
                    <span>$label</span>
                </a>
            ";
                }
                ?>
            </div>
        </div>

        <!-- Quotations & Builds Dropdown -->
        <div x-data="{ open: false }" class="px-3">
            <button @click="open = !open"
                class="flex items-center justify-between w-full py-2 text-left text-gray-600 hover:bg-gray-100 rounded-lg transition duration-150 ease-in-out">
                <span class="text-xs uppercase font-semibold text-gray-400">Quotations & Builds</span>
                <svg :class="{ 'rotate-180': open }" class="h-4 w-4 transform transition-transform duration-200"
                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <div x-show="open" x-collapse class="mt-2 space-y-1 pl-3 border-l border-gray-200">
                <?php
                $tools = [
                    'quotation.php' => 'Quotations',
                    'pc_builder.php' => 'PC Builder',
                    'archives.php' => 'Archives'
                ];
                foreach ($tools as $file => $label) {
                    $isActive = ($currentPage == $file) ? 'bg-blue-100 text-blue-800 font-semibold' : 'text-gray-600 hover:bg-gray-100';
                    echo "
                <a href='$file'
                    class='flex items-center px-3 py-2 rounded-lg transition duration-150 ease-in-out $isActive'>
                    <span>$label</span>
                </a>
            ";
                }
                ?>
            </div>
        </div>


    </nav>

    <div class="p-4 border-t">
        <?php
        $role = ucfirst($_SESSION['user-role'] ?? 'Guest');
        $username = $_SESSION['login-success'] ?? 'User Name';
        echo "
            <p class='text-sm font-semibold'>{$username}</p>
            <p class='text-xs text-gray-500'>{$role}</p>
        ";
        ?>
    </div>

    <div class="p-4 border-t flex items-center justify-between">
        <div class="flex items-center space-x-2 text-gray-600 hover:text-red-600 transition duration-150">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
            <a href="logout.php" class="text-sm font-medium">Sign Out</a>
        </div>
    </div>
</aside>
<script src="assets/alpine.js" defer></script>