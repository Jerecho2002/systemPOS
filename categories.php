<?php
include "database/database.php";
$database->login_session();

$database->create_category();
$database->update_category();
$database->delete_category();

$perPage = 5;
$search  = trim($_GET['search'] ?? '');
$page    = max(1, (int) ($_GET['page'] ?? 1));

$offset = ($page - 1) * $perPage;

$totalCategories = $database->getTotalCategoriesCount($search);
$totalPages = max(1, ceil($totalCategories / $perPage));

$categories = $database->select_categories_paginated($offset, $perPage, $search);
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

    <!-- Mobile Sidebar Toggle Button -->
    <button id="sidebar-toggle" class="md:hidden p-3 fixed top-4 left-4 z-60 text-white rounded shadow-lg"
        style="background-color: rgba(170, 170, 170, 0.82);">
        ☰
    </button>


    <?php include "sidebar.php"; ?>

    <!-- Main Content -->
    <main class="flex-1 ml-0 md:ml-64 p-6">
        <header class="mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Inventory Categories</h2>
        </header>

        <section class="bg-white rounded-xl shadow-md p-6">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-4 gap-4">
                <div>
                    <h3 class="text-xl font-bold text-gray-800">Category Management</h3>
                    <p class="text-sm text-gray-500">Manage your product categories</p>
                </div>
                
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full sm:w-auto">
                    <!-- Search Bar -->
                    <form method="GET" action="" class="relative w-full sm:w-64">
                        <input
                            type="text"
                            name="search"
                            id="searchInput"
                            value="<?= htmlspecialchars($search) ?>"
                            placeholder="Search categories..."
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                        <span class="material-icons absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm">search</span>
                        
                        <?php if ($search !== ''): ?>
                            <a href="?" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                <span class="material-icons text-sm">close</span>
                            </a>
                        <?php endif; ?>
                    </form>
                    
                    <!-- Add Button -->
                    <button id="openAddCategoryModal"
                        class="px-4 py-2 text-sm bg-black text-white rounded-lg hover:bg-gray-800 whitespace-nowrap">
                        + Add Category
                    </button>
                </div>
            </div>

            <?php if (isset($_SESSION['create-success'])): ?>
                <div id="successAlert"
                    class="mb-4 px-4 py-3 bg-green-100 border border-green-400 text-green-700 text-sm rounded-lg">
                    <?= $_SESSION['create-success'] ?>
                </div>
            <?php unset($_SESSION['create-success']);
            endif; ?>

            <?php if (isset($_SESSION['create-error'])): ?>
                <div id="errorAlert"
                    class="mb-4 px-4 py-3 bg-red-100 border border-red-400 text-red-700 text-sm rounded-lg">
                    <?= $_SESSION['create-error'] ?>
                </div>
            <?php unset($_SESSION['create-error']);
            endif; ?>

            <?php if ($search !== ''): ?>
                <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-800">
                    Showing results for "<strong><?= htmlspecialchars($search) ?></strong>"
                    (<?= $totalCategories ?> result<?= $totalCategories !== 1 ? 's' : '' ?>)
                    <a href="?" class="ml-2 text-blue-600 hover:underline">Clear search</a>
                </div>
            <?php endif; ?>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Category Name
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Description
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (!empty($categories)): ?>
                            <?php foreach ($categories as $cat): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($cat['category_name']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?= $cat['category_description'] ? htmlspecialchars($cat['category_description']) : "N/A" ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <button
                                                class="text-green-400 hover:text-green-600 openEditCategoryModal"
                                                data-id="<?= $cat['category_id'] ?>"
                                                data-name="<?= htmlspecialchars($cat['category_name']) ?>"
                                                data-description="<?= htmlspecialchars($cat['category_description']) ?>"
                                                data-category-type="<?= htmlspecialchars($cat['category_type']) ?>"
                                                data-supports-quantity="<?= (int) $cat['supports_quantity'] ?>"
                                                title="Edit Category">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                                    fill="currentColor">
                                                    <path
                                                        d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" />
                                                    <path fill-rule="evenodd"
                                                        d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                            <button class="text-red-500 hover:text-red-700 openDeleteCategoryModal"
                                                data-id="<?= $cat['category_id'] ?>"
                                                data-name="<?= htmlspecialchars($cat['category_name']) ?>"
                                                data-description="<?= htmlspecialchars($cat['category_description']) ?>"
                                                title="Delete Category">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                                    fill="currentColor">
                                                    <path fill-rule="evenodd"
                                                        d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="px-6 py-10 text-center text-gray-500">
                                    <?= $search !== '' ? 'No categories found matching your search.' : 'No categories found.' ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Controls -->
            <?php if ($totalPages > 1): ?>
                <div class="mt-6 flex flex-col sm:flex-row items-center justify-between gap-4 border-t pt-4">
                    <div class="text-sm text-gray-700">
                        Showing page <span class="font-medium"><?= $page ?></span> of <span class="font-medium"><?= $totalPages ?></span>
                    </div>

                    <nav class="flex items-center space-x-1">
                        <?php
                        // Build search parameter for all pagination links
                        $searchParam = $search !== '' ? '&search=' . urlencode($search) : '';
                        ?>

                        <!-- Previous -->
                        <?php if ($page > 1): ?>
                            <a href="?page=<?= $page - 1 ?><?= $searchParam ?>"
                                class="px-3 py-2 rounded-md text-sm font-medium bg-white border border-gray-300 text-gray-700 hover:bg-gray-50">
                                Previous
                            </a>
                        <?php else: ?>
                            <span class="px-3 py-2 rounded-md text-sm font-medium bg-gray-100 text-gray-400 cursor-not-allowed">
                                Previous
                            </span>
                        <?php endif; ?>

                        <?php
                        $range = 2;
                        $start = max(1, $page - $range);
                        $end = min($totalPages, $page + $range);

                        // Show first page and ellipsis if needed
                        if ($start > 1): ?>
                            <a href="?page=1<?= $searchParam ?>"
                                class="px-3 py-2 rounded-md text-sm font-medium border border-gray-300 hover:bg-gray-50">
                                1
                            </a>
                            <?php if ($start > 2): ?>
                                <span class="px-3 py-2 text-sm text-gray-500">...</span>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php
                        // Show page numbers in range
                        for ($i = $start; $i <= $end; $i++):
                            if ($i === $page): ?>
                                <span class="px-3 py-2 rounded-md text-sm font-medium bg-blue-600 text-white">
                                    <?= $i ?>
                                </span>
                            <?php else: ?>
                                <a href="?page=<?= $i ?><?= $searchParam ?>"
                                    class="px-3 py-2 rounded-md text-sm font-medium border border-gray-300 hover:bg-gray-50">
                                    <?= $i ?>
                                </a>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <?php
                        // Show ellipsis and last page if needed
                        if ($end < $totalPages): ?>
                            <?php if ($end < $totalPages - 1): ?>
                                <span class="px-3 py-2 text-sm text-gray-500">...</span>
                            <?php endif; ?>
                            <a href="?page=<?= $totalPages ?><?= $searchParam ?>"
                                class="px-3 py-2 rounded-md text-sm font-medium border border-gray-300 hover:bg-gray-50">
                                <?= $totalPages ?>
                            </a>
                        <?php endif; ?>

                        <!-- Next -->
                        <?php if ($page < $totalPages): ?>
                            <a href="?page=<?= $page + 1 ?><?= $searchParam ?>"
                                class="px-3 py-2 rounded-md text-sm font-medium bg-white border border-gray-300 text-gray-700 hover:bg-gray-50">
                                Next
                            </a>
                        <?php else: ?>
                            <span class="px-3 py-2 rounded-md text-sm font-medium bg-gray-100 text-gray-400 cursor-not-allowed">
                                Next
                            </span>
                        <?php endif; ?>
                    </nav>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <!-- Add Category Modal -->
    <div id="addCategoryModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
        <div class="bg-white rounded-lg w-full max-w-md p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Add New Category</h3>
                <button id="closeAddCategoryModal" class="text-gray-500 hover:text-gray-700 text-xl leading-none">
                    &times;
                </button>
            </div>

            <form method="POST" class="space-y-4">

                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        Category Name
                    </label>
                    <input
                        type="text"
                        name="category_name"
                        required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        Description
                    </label>
                    <textarea
                        name="category_description"
                        rows="3"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md resize-none"
                        placeholder="Optional"></textarea>
                </div>

                <!-- Category Type -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Category Type</label>
                    <select name="category_type" required
                        class="mt-1 w-full p-2 border rounded">
                        <option value="">Select type</option>
                        <option value="pc_part">PC Part</option>
                        <option value="accessory">Accessory</option>
                    </select>
                </div>

                <!-- Supports Quantity -->
                <div class="flex items-center gap-2">
                    <input
                        type="checkbox"
                        name="supports_quantity"
                        id="supports_quantity"
                        class="rounded border-gray-300">
                    <label for="supports_quantity" class="text-sm text-gray-700">
                        Supports quantity (RAM, Storage, Accessories)
                    </label>
                </div>


                <div class="flex justify-end space-x-2 pt-4">
                    <button type="button"
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300"
                        id="cancelCategoryModalBtn">
                        Cancel
                    </button>

                    <button
                        name="create_category"
                        type="submit"
                        class="px-4 py-2 bg-black text-white rounded">
                        Save Category
                    </button>
                </div>
            </form>
        </div>
    </div>


    <!-- Edit Category Modal -->
    <div id="editCategoryModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
        <div class="bg-white rounded-lg w-full max-w-md p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Update Category</h3>
                <button id="closeEditCategoryModal"
                    class="text-gray-500 hover:text-gray-700 text-2xl leading-none">
                    &times;
                </button>
            </div>

            <form method="POST" class="space-y-4">

                <input type="hidden" name="category_id" id="edit_category_id">

                <!-- Category Name -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        Category Name
                    </label>
                    <input
                        type="text"
                        name="category_name"
                        id="edit_category_name"
                        required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        Description
                    </label>
                    <textarea
                        name="category_description"
                        id="edit_category_description"
                        rows="3"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md resize-none"
                        placeholder="Optional">
                </textarea>
                </div>

                <!-- Category Type -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        Category Type
                    </label>
                    <select
                        name="category_type"
                        id="edit_category_type"
                        required
                        class="mt-1 w-full p-2 border rounded">
                        <option value="">Select type</option>
                        <option value="pc_part">PC Part</option>
                        <option value="accessory">Accessory</option>
                    </select>
                </div>

                <!-- Supports Quantity -->
                <div class="flex items-center gap-2">
                    <input
                        type="checkbox"
                        name="supports_quantity"
                        id="edit_supports_quantity"
                        class="rounded border-gray-300">
                    <label for="edit_supports_quantity" class="text-sm text-gray-700">
                        Supports quantity (RAM, Storage, Accessories)
                    </label>
                </div>

                <div class="flex justify-end space-x-2 pt-4">
                    <button type="button"
                        id="cancelEditCategoryModalBtn"
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">
                        Cancel
                    </button>

                    <button
                        name="update_category"
                        type="submit"
                        class="px-4 py-2 bg-black text-white rounded">
                        Update Category
                    </button>
                </div>
            </form>
        </div>
    </div>




    <!-- Delete Category Modal -->
    <div id="deleteCategoryModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
        <div class="bg-white rounded-lg w-full max-w-md p-6 shadow-lg">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Delete Category</h3>
            <p class="text-sm text-gray-700 mb-4">
                Are you sure you want to delete <span id="delete_category_name"
                    class="font-medium text-red-600"></span>?
            </p>

            <form method="POST" action="<?= $_SERVER['PHP_SELF']; ?>" class="flex justify-end space-x-2">
                <input type="hidden" name="delete_category_id" id="delete_category_id">

                <button type="button" id="cancelDeleteCategoryModal"
                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">Cancel</button>
                <button type="submit" name="delete_category"
                    class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Delete</button>
            </form>
        </div>
    </div>

    <!-- BugerBar Toggle -->
    <script>
        const sidebar = document.getElementById('mobile-sidebar');
        const toggleBtn = document.getElementById('sidebar-toggle');

        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('-translate-x-full');
        });
    </script>

    <!-- BugerBar Close -->
    <script>
        const closeBtn = document.getElementById('sidebar-close');

        closeBtn.addEventListener('click', () => {
            sidebar.classList.add('-translate-x-full'); // close sidebar
        });
    </script>

    <!-- Unset Alert -->
    <script>
        const successAlert = document.getElementById('successAlert');
        if (successAlert) {
            setTimeout(() => {
                successAlert.style.display = 'none';
                fetch('unset_alert.php');
            }, 3000);
        }

        const errorAlert = document.getElementById('errorAlert');
        if (errorAlert) {
            setTimeout(() => {
                errorAlert.style.display = 'none';
                fetch('unset_alert.php');
            }, 3000);
        }
    </script>

    <!-- Auto-submit search form after user stops typing -->
    <script>
        let searchTimeout;
        const searchInput = document.getElementById('searchInput');

        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    this.form.submit();
                }, 500); // Wait 500ms after user stops typing
            });
        }
    </script>

    <!-- Add Category Script -->
    <script>
        const openCategoryBtn = document.getElementById('openAddCategoryModal');
        const closeCategoryBtn = document.getElementById('closeAddCategoryModal');
        const cancelCategoryBtn = document.getElementById('cancelCategoryModalBtn');
        const categoryModal = document.getElementById('addCategoryModal');

        openCategoryBtn.addEventListener('click', () => {
            categoryModal.classList.remove('hidden');
        });

        closeCategoryBtn.addEventListener('click', () => {
            categoryModal.classList.add('hidden');
        });

        cancelCategoryBtn.addEventListener('click', () => {
            categoryModal.classList.add('hidden');
        });

        // Optional: close when clicking outside
        window.addEventListener('click', (e) => {
            if (e.target === categoryModal) {
                categoryModal.classList.add('hidden');
            }
        });
    </script>

    <!-- Edit Category Script -->
    <script>
        const editCategoryButtons = document.querySelectorAll('.openEditCategoryModal');
        const editCategoryModal = document.getElementById('editCategoryModal');
        const closeEditCategoryModal = document.getElementById('closeEditCategoryModal');
        const cancelEditCategoryModal = document.getElementById('cancelEditCategoryModalBtn');

        editCategoryButtons.forEach(button => {
            button.addEventListener('click', () => {
                document.getElementById('edit_category_id').value = button.dataset.id;
                document.getElementById('edit_category_name').value = button.dataset.name;
                document.getElementById('edit_category_description').value =
                    button.dataset.description || '';
                document.getElementById('edit_category_type').value =
                    button.dataset.categoryType || '';

                const supportsQuantity =
                    button.dataset.supportsQuantity === '1' ||
                    button.dataset.supportsQuantity === 'true';

                document.getElementById('edit_supports_quantity').checked = supportsQuantity;


                editCategoryModal.classList.remove('hidden');
            });
        });

        closeEditCategoryModal.addEventListener('click', () => {
            editCategoryModal.classList.add('hidden');
        });

        cancelEditCategoryModal.addEventListener('click', () => {
            editCategoryModal.classList.add('hidden');
        });

        window.addEventListener('click', (e) => {
            if (e.target === editCategoryModal) {
                editCategoryModal.classList.add('hidden');
            }
        });
    </script>

    <!-- Delete Category Script -->
    <script>
        const deleteCategoryButtons = document.querySelectorAll('.openDeleteCategoryModal');
        const deleteCategoryModal = document.getElementById('deleteCategoryModal');
        const cancelDeleteCategoryModal = document.getElementById('cancelDeleteCategoryModal');

        deleteCategoryButtons.forEach(button => {
            button.addEventListener('click', () => {
                const id = button.dataset.id;
                const name = button.dataset.name;

                document.getElementById('delete_category_id').value = id;
                document.getElementById('delete_category_name').textContent = name;

                deleteCategoryModal.classList.remove('hidden');
            });
        });

        cancelDeleteCategoryModal.addEventListener('click', () => {
            deleteCategoryModal.classList.add('hidden');
        });

        window.addEventListener('click', (e) => {
            if (e.target === deleteCategoryModal) {
                deleteCategoryModal.classList.add('hidden');
            }
        });
    </script>


</body>