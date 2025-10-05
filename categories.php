<?php
include "database/database.php";
$database->create_category();
$database->update_category();
$database->delete_category();
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
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-xl font-bold text-gray-800">Category Management</h3>
                    <p class="text-sm text-gray-500">Manage your product categories</p>
                </div>
                <button id="openAddCategoryModal"
                    class="px-4 py-2 text-sm bg-black text-white rounded-lg hover:bg-gray-800">
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
                        <?php foreach ($categories as $cat): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $cat['category_name'] ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?= $cat['category_description'] ? htmlspecialchars($cat['category_description']) : "N/A"?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <button class="text-green-400 hover:text-green-600 openEditCategoryModal"
                                            data-id="<?= $cat['category_id'] ?>"
                                            data-name="<?= htmlspecialchars($cat['category_name']) ?>"
                                            data-description="<?= htmlspecialchars($cat['category_description']) ?>">
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
                                            data-description="<?= htmlspecialchars($cat['category_description']) ?>">
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
                    </tbody>
                </table>
            </div>
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
                    <label for="category_name" class="block text-sm font-medium text-gray-700">Category Name</label>
                    <input type="text" name="category_name" id="category_name" required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200">
                </div>

                <div>
                    <label for="category_description"
                        class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea name="category_description" id="category_description" rows="3"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md resize-none focus:outline-none focus:ring focus:ring-indigo-200"
                        placeholder="Write a brief description (optional)"></textarea>
                </div>

                <div class="flex justify-end space-x-2 pt-4">
                    <button type="button" id="cancelCategoryModalBtn"
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">
                        Cancel
                    </button>
                    <button name="create_category" type="submit"
                        class="px-4 py-2 bg-black text-white rounded hover:bg-gray-800">
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
                <button id="closeEditCategoryModal" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
            </div>

            <form id="editCategoryForm" method="POST" class="space-y-4">
                <input type="hidden" name="category_id" id="edit_category_id">

                <div>
                    <label for="edit_category_name" class="block text-sm font-medium text-gray-700">Category
                        Name</label>
                    <input type="text" name="category_name" id="edit_category_name" required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200">
                </div>

                <div>
                    <label for="edit_category_description"
                        class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea name="category_description" id="edit_category_description" rows="3"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md resize-none focus:outline-none focus:ring focus:ring-indigo-200"
                        placeholder="Update category description (optional)"></textarea>
                </div>

                <div class="flex justify-end space-x-2 pt-4">
                    <button type="button" id="cancelEditCategoryModalBtn"
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">
                        Cancel
                    </button>
                    <button name="update_category" type="submit"
                        class="px-4 py-2 bg-black text-white rounded hover:bg-gray-800">
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
                const id = button.dataset.id;
                const name = button.dataset.name;
                const description = button.dataset.description;

                document.getElementById('edit_category_id').value = id;
                document.getElementById('edit_category_name').value = name;
                document.getElementById('edit_category_description').value = description || '';

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

    </script>

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