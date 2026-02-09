<?php
include "database/archiveDatabase.php";
$database->login_session();

// Handle Restore
if (isset($_POST['restore_record'])) {
    $restoreId = (int) $_POST['restore_id'];
    $restoreType = $_POST['restore_type'] ?? '';

    if ($database->restoreArchived($restoreType, $restoreId)) {
        $_SESSION['restore-success'] = 'Record successfully restored.';
    } else {
        $_SESSION['restore-error'] = 'Failed to restore record.';
    }

    header('Location: archives.php?type=' . urlencode($restoreType));
    exit;
}

// Handle Permanent Delete
if (isset($_POST['permanent_delete'])) {
    $deleteId = (int) $_POST['delete_id'];
    $deleteType = $_POST['delete_type'] ?? '';

    if ($database->permanentDelete($deleteType, $deleteId)) {
        $_SESSION['delete-success'] = 'Record permanently deleted.';
    } else {
        $_SESSION['delete-error'] = 'Failed to delete record.';
    }

    header('Location: archives.php?type=' . urlencode($deleteType));
    exit;
}

// Handle Clear All
if (isset($_POST['clear_all_archives'])) {
    $clearType = $_POST['clear_type'] ?? '';

    if ($database->clearAllArchives($clearType)) {
        $_SESSION['clear-success'] = 'All archived records cleared permanently.';
    } else {
        $_SESSION['clear-error'] = 'Failed to clear archives.';
    }

    header('Location: archives.php?type=' . urlencode($clearType));
    exit;
}

$type = $_GET['type'] ?? 'categories';
$search = trim($_GET['search'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));

$perPage = 10;
$offset = ($page - 1) * $perPage;

$total = $database->getArchivedTotal($type, $search);
$pages = max(1, ceil($total / $perPage));

$records = $database->getArchivedPaginated($type, $offset, $perPage, $search);
$tableConfig = $database->getTableConfig($type);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Archives - <?= $tableConfig['label'] ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
</head>


<body class="flex bg-gray-50 min-h-screen">
    <?php include 'sidebar.php'; ?>

    <main class="flex-1 ml-0 md:ml-64 p-6">

        <header class="mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Archives</h2>
            <p class="text-sm text-gray-500">View, restore, or permanently delete archived records</p>
        </header>

        <!-- Success/Error Messages -->
        <?php if (isset($_SESSION['restore-success'])): ?>
            <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                <?= $_SESSION['restore-success']; unset($_SESSION['restore-success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['delete-success'])): ?>
            <div class="mb-4 p-4 bg-blue-100 border border-blue-400 text-blue-700 rounded-lg">
                <?= $_SESSION['delete-success']; unset($_SESSION['delete-success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['clear-success'])): ?>
            <div class="mb-4 p-4 bg-blue-100 border border-blue-400 text-blue-700 rounded-lg">
                <?= $_SESSION['clear-success']; unset($_SESSION['clear-success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['restore-error']) || isset($_SESSION['delete-error']) || isset($_SESSION['clear-error'])): ?>
            <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                <?= $_SESSION['restore-error'] ?? $_SESSION['delete-error'] ?? $_SESSION['clear-error']; 
                    unset($_SESSION['restore-error'], $_SESSION['delete-error'], $_SESSION['clear-error']); 
                ?>
            </div>
        <?php endif; ?>

        <section class="bg-white rounded-xl shadow-md p-6">

            <!-- Filters and Actions -->
            <div class="flex flex-col lg:flex-row gap-4 mb-6">
                <form method="GET" class="flex flex-col lg:flex-row gap-4 flex-1">
                    <!-- Table Filter -->
                    <select name="type" onchange="this.form.submit()"
                        class="w-full lg:w-56 px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="categories" <?= $type === 'categories' ? 'selected' : '' ?>>Categories</option>
                        <option value="items" <?= $type === 'items' ? 'selected' : '' ?>>Items</option>
                        <option value="suppliers" <?= $type === 'suppliers' ? 'selected' : '' ?>>Suppliers</option>
                        <option value="purchase_orders" <?= $type === 'purchase_orders' ? 'selected' : '' ?>>Purchase Orders</option>
                        <option value="sales" <?= $type === 'sales' ? 'selected' : '' ?>>Sales</option>
                    </select>

                    <!-- Search -->
                    <div class="relative flex-1">
                        <input
                            type="text"
                            name="search"
                            value="<?= htmlspecialchars($search) ?>"
                            placeholder="Search archived records..."
                            class="w-full px-4 py-2 pl-10 border rounded-lg focus:ring-2 focus:ring-blue-500">
                        <span class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">search</span>
                    </div>
                </form>

                <!-- Clear All Button -->
                <?php if ($total > 0): ?>
                    <button 
                        onclick="openClearAllModal()"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition flex items-center gap-2 whitespace-nowrap">
                        <span class="material-icons text-sm">delete_forever</span>
                        Clear All
                    </button>
                <?php endif; ?>
            </div>

            <!-- Info -->
            <div class="mb-4 flex items-center justify-between">
                <div class="text-sm text-gray-600">
                    Showing <strong><?= $tableConfig['label'] ?></strong> archives
                    <span class="text-gray-400">·</span>
                    <strong><?= $total ?></strong> record<?= $total !== 1 ? 's' : '' ?>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <?php foreach ($tableConfig['display_columns'] as $column => $label): ?>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <?= htmlspecialchars($label) ?>
                                </th>
                            <?php endforeach; ?>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (!empty($records)): ?>
                            <?php foreach ($records as $row): ?>
                                <tr class="hover:bg-gray-50 transition">
                                    <?php foreach ($tableConfig['display_columns'] as $column => $label): ?>
                                        <td class="px-6 py-4 text-sm text-gray-800">
                                            <?php
                                            $value = $row[$column] ?? '—';
                                            
                                            // Format price columns
                                            if (stripos($column, 'price') !== false || stripos($column, 'total') !== false) {
                                                $value = $value !== '—' ? '₱' . number_format((float)$value, 2) : '—';
                                            }
                                            // Format status
                                            elseif ($column === 'status') {
                                                if (is_numeric($value)) {
                                                    // For suppliers (1 = Active, 0 = Inactive)
                                                    $statusClass = $value == 1 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                                                    $statusText = $value == 1 ? 'Active' : 'Inactive';
                                                    $value = "<span class='px-2 py-1 rounded-full text-xs font-medium {$statusClass}'>{$statusText}</span>";
                                                } else {
                                                    // For purchase orders (Ordered, Received, Cancelled)
                                                    $statusColors = [
                                                        'Ordered' => 'bg-blue-100 text-blue-800',
                                                        'Received' => 'bg-green-100 text-green-800',
                                                        'Cancelled' => 'bg-red-100 text-red-800'
                                                    ];
                                                    $statusClass = $statusColors[$value] ?? 'bg-gray-100 text-gray-800';
                                                    $value = "<span class='px-2 py-1 rounded-full text-xs font-medium {$statusClass}'>{$value}</span>";
                                                }
                                            }
                                            // Format category type
                                            elseif ($column === 'category_type') {
                                                $typeColors = [
                                                    'pc_part' => 'bg-blue-100 text-blue-800',
                                                    'accessory' => 'bg-purple-100 text-purple-800'
                                                ];
                                                $statusClass = $typeColors[$value] ?? 'bg-gray-100 text-gray-800';
                                                $displayValue = ucwords(str_replace('_', ' ', $value));
                                                $value = "<span class='px-2 py-1 rounded-full text-xs font-medium {$statusClass}'>{$displayValue}</span>";
                                            }
                                            // Format supports_quantity
                                            elseif ($column === 'supports_quantity') {
                                                $value = $value == 1 ? "<span class='text-green-600'>✓ Yes</span>" : "<span class='text-gray-400'>✗ No</span>";
                                            }
                                            // Format payment method
                                            elseif ($column === 'payment_method') {
                                                $paymentColors = [
                                                    'Cash' => 'bg-green-100 text-green-800',
                                                    'Credit Card' => 'bg-blue-100 text-blue-800',
                                                    'Gcash' => 'bg-purple-100 text-purple-800'
                                                ];
                                                $statusClass = $paymentColors[$value] ?? 'bg-gray-100 text-gray-800';
                                                $value = "<span class='px-2 py-1 rounded-full text-xs font-medium {$statusClass}'>{$value}</span>";
                                            }
                                            
                                            echo $value;
                                            ?>
                                        </td>
                                    <?php endforeach; ?>
                                    <td class="px-6 py-4 text-sm text-right">
                                        <div class="flex items-center justify-end gap-3">
                                            <!-- Restore Button -->
                                            <button
                                                class="text-green-600 hover:text-green-800 transition flex items-center gap-1 openRestoreModal"
                                                data-id="<?= $row[array_key_first($row)] ?>"
                                                data-type="<?= $type ?>"
                                                title="Restore">
                                                <span class="material-icons text-sm">restore</span>
                                                <span>Restore</span>
                                            </button>

                                            <!-- Permanent Delete Button -->
                                            <button
                                                class="text-red-600 hover:text-red-800 transition flex items-center gap-1 openDeleteModal"
                                                data-id="<?= $row[array_key_first($row)] ?>"
                                                data-type="<?= $type ?>"
                                                title="Delete Permanently">
                                                <span class="material-icons text-sm">delete_forever</span>
                                                <span>Delete</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="<?= count($tableConfig['display_columns']) + 1 ?>" 
                                    class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center gap-3">
                                        <span class="material-icons text-gray-300" style="font-size: 48px;">inventory_2</span>
                                        <p class="text-gray-500">No archived records found.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($pages > 1): ?>
                <div class="mt-6 flex flex-col sm:flex-row justify-between items-center gap-4 border-t pt-4">
                    <div class="text-sm text-gray-600">
                        Page <strong><?= $page ?></strong> of <strong><?= $pages ?></strong>
                    </div>

                    <div class="flex items-center gap-2">
                        <!-- Previous Button -->
                        <?php if ($page > 1): ?>
                            <a href="?type=<?= $type ?>&search=<?= urlencode($search) ?>&page=<?= $page - 1 ?>"
                                class="px-3 py-2 rounded-md text-sm border hover:bg-gray-100 transition">
                                Previous
                            </a>
                        <?php endif; ?>

                        <!-- Page Numbers -->
                        <div class="flex gap-1">
                            <?php
                            $start = max(1, $page - 2);
                            $end = min($pages, $page + 2);
                            
                            if ($start > 1): ?>
                                <a href="?type=<?= $type ?>&search=<?= urlencode($search) ?>&page=1"
                                    class="px-3 py-2 rounded-md text-sm border hover:bg-gray-100">1</a>
                                <?php if ($start > 2): ?>
                                    <span class="px-3 py-2 text-sm text-gray-400">...</span>
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php for ($i = $start; $i <= $end; $i++): ?>
                                <a href="?type=<?= $type ?>&search=<?= urlencode($search) ?>&page=<?= $i ?>"
                                    class="px-3 py-2 rounded-md text-sm border transition <?= $i === $page ? 'bg-blue-600 text-white border-blue-600' : 'hover:bg-gray-100' ?>">
                                    <?= $i ?>
                                </a>
                            <?php endfor; ?>

                            <?php if ($end < $pages): ?>
                                <?php if ($end < $pages - 1): ?>
                                    <span class="px-3 py-2 text-sm text-gray-400">...</span>
                                <?php endif; ?>
                                <a href="?type=<?= $type ?>&search=<?= urlencode($search) ?>&page=<?= $pages ?>"
                                    class="px-3 py-2 rounded-md text-sm border hover:bg-gray-100"><?= $pages ?></a>
                            <?php endif; ?>
                        </div>

                        <!-- Next Button -->
                        <?php if ($page < $pages): ?>
                            <a href="?type=<?= $type ?>&search=<?= urlencode($search) ?>&page=<?= $page + 1 ?>"
                                class="px-3 py-2 rounded-md text-sm border hover:bg-gray-100 transition">
                                Next
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

        </section>
    </main>

    <!-- Restore Modal -->
    <div id="restoreModal" class="fixed inset-0 hidden bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg w-full max-w-md p-6 m-4">
            <div class="flex items-center gap-3 mb-4">
                <span class="material-icons text-green-600">restore</span>
                <h3 class="text-lg font-semibold text-gray-800">Restore Record</h3>
            </div>
            <p class="text-sm text-gray-600 mb-6">Are you sure you want to restore this record? It will be moved back to the active records.</p>

            <form method="POST" class="flex justify-end gap-2">
                <input type="hidden" name="restore_id" id="restore_id">
                <input type="hidden" name="restore_type" id="restore_type">

                <button type="button" id="cancelRestore"
                    class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg transition">Cancel</button>
                <button type="submit" name="restore_record"
                    class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition">Restore</button>
            </form>
        </div>
    </div>

    <!-- Permanent Delete Modal -->
    <div id="deleteModal" class="fixed inset-0 hidden bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg w-full max-w-md p-6 m-4">
            <div class="flex items-center gap-3 mb-4">
                <span class="material-icons text-red-600">delete_forever</span>
                <h3 class="text-lg font-semibold text-gray-800">Permanent Delete</h3>
            </div>
            <p class="text-sm text-gray-600 mb-2">Are you sure you want to permanently delete this record?</p>
            <p class="text-sm text-red-600 font-semibold mb-6">⚠️ This action cannot be undone!</p>

            <form method="POST" class="flex justify-end gap-2">
                <input type="hidden" name="delete_id" id="delete_id">
                <input type="hidden" name="delete_type" id="delete_type">

                <button type="button" id="cancelDelete"
                    class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg transition">Cancel</button>
                <button type="submit" name="permanent_delete"
                    class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition">Delete Permanently</button>
            </form>
        </div>
    </div>

    <!-- Clear All Modal -->
    <div id="clearAllModal" class="fixed inset-0 hidden bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg w-full max-w-md p-6 m-4">
            <div class="flex items-center gap-3 mb-4">
                <span class="material-icons text-red-600">delete_sweep</span>
                <h3 class="text-lg font-semibold text-gray-800">Clear All Archives</h3>
            </div>
            <p class="text-sm text-gray-600 mb-2">Are you sure you want to permanently delete <strong>all <?= $total ?> archived <?= strtolower($tableConfig['label']) ?></strong>?</p>
            <p class="text-sm text-red-600 font-semibold mb-6">⚠️ This action cannot be undone!</p>

            <form method="POST" class="flex justify-end gap-2">
                <input type="hidden" name="clear_type" value="<?= $type ?>">

                <button type="button" id="cancelClearAll"
                    class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg transition">Cancel</button>
                <button type="submit" name="clear_all_archives"
                    class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition">Clear All</button>
            </form>
        </div>
    </div>

    <script>
        // Restore Modal
        const restoreBtns = document.querySelectorAll('.openRestoreModal');
        const restoreModal = document.getElementById('restoreModal');

        restoreBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                document.getElementById('restore_id').value = btn.dataset.id;
                document.getElementById('restore_type').value = btn.dataset.type;
                restoreModal.classList.remove('hidden');
            });
        });

        document.getElementById('cancelRestore').addEventListener('click', () => {
            restoreModal.classList.add('hidden');
        });

        // Permanent Delete Modal
        const deleteBtns = document.querySelectorAll('.openDeleteModal');
        const deleteModal = document.getElementById('deleteModal');

        deleteBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                document.getElementById('delete_id').value = btn.dataset.id;
                document.getElementById('delete_type').value = btn.dataset.type;
                deleteModal.classList.remove('hidden');
            });
        });

        document.getElementById('cancelDelete').addEventListener('click', () => {
            deleteModal.classList.add('hidden');
        });

        // Clear All Modal
        const clearAllModal = document.getElementById('clearAllModal');

        function openClearAllModal() {
            clearAllModal.classList.remove('hidden');
        }

        document.getElementById('cancelClearAll').addEventListener('click', () => {
            clearAllModal.classList.add('hidden');
        });

        // Close modals on outside click
        window.addEventListener('click', (e) => {
            if (e.target === restoreModal) restoreModal.classList.add('hidden');
            if (e.target === deleteModal) deleteModal.classList.add('hidden');
            if (e.target === clearAllModal) clearAllModal.classList.add('hidden');
        });
    </script>
</body>
</html>
