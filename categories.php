<?php
include "database/database.php";
$categories = $database->select_categories();
?>

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>POS & Inventory - Categories</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
    <style>
        .status-label {
            padding: 2px 8px;
            font-size: 12px;
            font-weight: 600;
            border-radius: 9999px;
        }
    </style>
</head>

<body class="flex bg-gray-50 min-h-screen">

    <!-- Sidebar -->
    <aside class="sticky top-0 w-64 h-screen bg-white border-r flex flex-col">
        <div class="p-6 border-b">
            <h1 class="text-lg font-bold">POS & Inventory</h1>
            <p class="text-xs text-gray-500">Management System</p>
        </div>
        <nav class="flex-1 p-4 space-y-2">
            <a href="dashboard.php" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-100">
                <span class="ml-2">Dashboard</span>
            </a>
            <p class="text-xs uppercase text-gray-500 mt-4 mb-2">Point of Sale (POS)</p>
            <a href="process_sales.php" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-100">
                <span class="ml-2">Process Sales</span>
            </a>
            <a href="sales_report.php" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-100">
                <span class="ml-2">Sales Reports</span>
            </a>

            <p class="text-xs uppercase text-gray-500 mt-4 mb-2">Inventory Management</p>
            <a href="item_catalog.php" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-100">
                <span class="ml-2">Item Catalog</span>
            </a>
            <a href="stock_levels.php" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-100 relative">
                <span class="ml-2">Stock Levels</span>
                <span class="absolute right-3 top-2 text-xs bg-red-500 text-white rounded-full px-2">3</span>
            </a>
            <a href="purchase_orders.php" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-100">
                <span class="ml-2">Purchase Orders</span>
            </a>
            <a href="categories.php" class="flex items-center px-3 py-2 rounded-lg bg-gray-100 font-medium">
                <span class="ml-2">Categories</span>
            </a>
            <a href="suppliers.php" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-100">
                <span class="ml-2">Suppliers</span>
            </a>
            <a href="inventory_reports.php" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-100">
                <span class="ml-2">Inventory Reports</span>
            </a>
        </nav>

        <div class="p-4 border-t">
            <?php
            $role = ucfirst($_SESSION['user-role']);
            $username = $_SESSION['login-success'];
            echo "
        <p class='text-sm font-medium'>{$username}</p>
        <p class='text-xs text-gray-500'>{$role}</p>
      ";
            ?>
        </div>

        <div class="p-4 border-t flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                <a href="logout.php" class="text-sm font-medium">Sign Out</a>
            </div>
        </div>
    </aside>

    <!-- Main content -->
    <main class="flex-1 p-6">
        <header class="mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Inventory Categories</h2>
        </header>

        <section class="bg-white rounded-xl shadow-md p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-xl font-bold text-gray-800">Category Management</h3>
                    <p class="text-sm text-gray-500">Manage your product categories</p>
                </div>
                <button class="px-4 py-2 text-sm bg-black text-white rounded-lg hover:bg-gray-800 ml-4"
                    id="openAddCategoryModal">
                    + Add Category
                </button>
            </div>

            <?php if (isset($_SESSION['create-success'])): ?>
                <div id="successAlert"
                    class="mb-4 px-4 py-3 bg-green-100 border border-green-400 text-green-700 text-sm rounded-lg">
                    <?= $_SESSION['create-success'] ?>
                </div>
                <?php unset($_SESSION['create-success']); endif; ?>

            <?php if (isset($_SESSION['create-error'])): ?>
                <div id="errorAlert"
                    class="mb-4 px-4 py-3 bg-red-100 border border-red-400 text-red-700 text-sm rounded-lg">
                    <?= $_SESSION['create-error'] ?>
                </div>
                <?php unset($_SESSION['create-error']); endif; ?>

            <h4 class="text-lg font-semibold mb-2">Category Database</h4>
            <p class="text-sm text-gray-500 mb-4"><?= count($categories) ?> categories found</p>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Category ID
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Category Name
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($categories as $cat): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $cat['category_id'] ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?= htmlspecialchars($cat['category_name']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <button class="text-green-400 hover:text-green-600 openEditCategoryModal"
                                            data-id="<?= $cat['category_id'] ?>"
                                            data-name="<?= htmlspecialchars($cat['category_name']) ?>"
                                            title="Edit Category">
                                            <span class="material-icons">edit</span>
                                        </button>
                                        <button class="text-red-500 hover:text-red-700 openDeleteCategoryModal"
                                            data-id="<?= $cat['category_id'] ?>"
                                            data-name="<?= htmlspecialchars($cat['category_name']) ?>"
                                            title="Delete Category">
                                            <span class="material-icons">delete</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <!-- You can add modals for add/edit/delete categories here -->

</body>