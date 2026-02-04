<?php
include "database/database.php";
$database->login_session();
$database->create_supplier();
$database->update_supplier();
$database->archive_supplier();

// Pagination settings
$perPage = 5;
$search  = trim($_GET['search'] ?? '');
$page    = max(1, (int) ($_GET['page'] ?? 1));

$offset = ($page - 1) * $perPage;

// Get data with search filter
$totalSuppliers = $database->getTotalSuppliersCount($search);
$totalPages = max(1, ceil($totalSuppliers / $perPage));

$suppliers = $database->select_suppliers_paginated($offset, $perPage, $search);
$purchase_orders = $database->select_purchase_orders();

// Calculate statistics
$allSuppliers = $database->select_all_suppliers_for_stats();
$grandTotal = array_sum(array_column($allSuppliers, 'total_spent'));
$active_count = count(array_filter($allSuppliers, fn($sp) => $sp['status'] == 1));
$inactive_count = count(array_filter($allSuppliers, fn($sp) => $sp['status'] == 0));

function formatCompactCurrency($number)
{
  if ($number >= 1_000_000_000) {
    return '₱' . round($number / 1_000_000_000, 1) . 'B';
  } elseif ($number >= 1_000_000) {
    return '₱' . round($number / 1_000_000, 1) . 'M';
  } elseif ($number >= 1_000) {
    return '₱' . round($number / 1_000, 1) . 'k';
  } else {
    return '₱' . number_format($number, 0);
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>POS & Inventory - Suppliers</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <style>
    .status-label {
      padding: 2px 8px;
      font-size: 12px;
      font-weight: 600;
      border-radius: 9999px;
    }
  </style>
</head>

<body class="bg-gray-50 flex">

  <!-- Mobile Sidebar Toggle Button -->
  <button id="sidebar-toggle" class="md:hidden p-3 fixed top-4 left-4 z-60 text-white rounded shadow-lg"
    style="background-color: rgba(170, 170, 170, 0.82);">
    ☰
  </button>

  <?php include "sidebar.php"; ?>

  <!-- Main Content -->
  <main class="flex-1 ml-0 md:ml-64 p-6">
    <header class="mb-6">
      <h2 class="text-2xl font-bold text-gray-800">Inventory Suppliers</h2>
    </header>

    <section class="bg-white rounded-xl shadow-md p-6">
      <div class="flex items-center justify-between mb-4">
        <div>
          <h3 class="text-xl font-bold text-gray-800">Supplier Management</h3>
          <p class="text-sm text-gray-500">Manage your supplier relationships and procurement</p>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white border rounded-xl p-4 flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500">Total Suppliers</p>
            <h4 class="text-2xl font-bold"><?= count($allSuppliers) ?></h4>
            <p class="text-xs text-gray-500">registered suppliers</p>
          </div>
        </div>
        <div class="bg-white border rounded-xl p-4 flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500">Total Spent</p>
            <h4 class="text-2xl font-bold"><?= formatCompactCurrency($grandTotal) ?></h4>
            <p class="text-xs text-gray-500">all time purchases</p>
          </div>
        </div>
        <div class="bg-white border rounded-xl p-4 flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500">Active Suppliers</p>
            <h4 class="text-2xl font-bold"><?= $active_count ?></h4>
            <p class="text-xs text-gray-500">currently active</p>
          </div>
        </div>
        <div class="bg-white border rounded-xl p-4 flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500">Inactive Suppliers</p>
            <h4 class="text-2xl font-bold"><?= $inactive_count ?></h4>
            <p class="text-xs text-gray-500">currently inactive</p>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 gap-4">
        <!-- SUPPLIER TABLE -->
        <div class="bg-white border rounded-xl p-6">
          <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between mb-4 gap-3">
            <form method="GET" action="" class="relative flex-1 max-w-md">
              <input 
                type="text" 
                name="search" 
                id="searchInput" 
                value="<?= htmlspecialchars($search) ?>"
                placeholder="Search suppliers by name..."
                class="w-full px-4 py-2 pl-10 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
              <span class="material-icons absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm">search</span>
              
              <?php if ($search !== ''): ?>
                <a href="?" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                  <span class="material-icons text-sm">close</span>
                </a>
              <?php endif; ?>
            </form>
            
            <button id="openAddSupplierModal"
              class="px-4 py-2 text-sm bg-black text-white rounded-lg hover:bg-gray-800 whitespace-nowrap">
              + Add Supplier
            </button>
          </div>

          <?php if (isset($_SESSION['create-success'])): ?>
            <div id="successAlert"
              class="mb-4 px-4 py-3 bg-green-100 border border-green-400 text-green-700 text-sm rounded-lg">
              <?= $_SESSION['create-success'] ?>
            </div>
            <?php unset($_SESSION['create-success']); ?>
          <?php endif; ?>

          <?php if (isset($_SESSION['create-error'])): ?>
            <div id="errorAlert" class="mb-4 px-4 py-3 bg-red-100 border border-red-400 text-red-700 text-sm rounded-lg">
              <?= $_SESSION['create-error'] ?>
            </div>
            <?php unset($_SESSION['create-error']); ?>
          <?php endif; ?>

          <?php if ($search !== ''): ?>
            <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-800">
              Showing results for "<strong><?= htmlspecialchars($search) ?></strong>"
              (<?= $totalSuppliers ?> supplier<?= $totalSuppliers !== 1 ? 's' : '' ?>)
              <a href="?" class="ml-2 text-blue-600 hover:underline">Clear search</a>
            </div>
          <?php endif; ?>

          <h4 class="text-lg font-semibold mb-2">Supplier Database</h4>
          <p class="text-sm text-gray-500 mb-4"><?= $totalSuppliers ?> supplier<?= $totalSuppliers !== 1 ? 's' : '' ?> found</p>

          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th scope="col"
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                  <th scope="col"
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Orders</th>
                  <th scope="col"
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Spent
                  </th>
                  <th scope="col"
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                  <th scope="col"
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <?php if (!empty($suppliers)): ?>
                  <?php foreach ($suppliers as $sp): ?>
                    <?php if($sp['status'] === 0) continue; ?>
                    <tr class="hover:bg-gray-50">
                      <td class="px-6 py-4 whitespace-nowrap">
                        <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($sp['supplier_name']) ?></p>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <p class="text-gray-900 font-medium"><?= $sp['order_count'] ?></p>
                        <p class="text-xs">
                          Last:
                          <?= $sp['last_order_date'] ? date('F j, Y, g:i A', strtotime($sp['last_order_date'])) : 'N/A' ?>
                        </p>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <p class="text-gray-900 font-medium">₱<?= number_format($sp['total_spent'], 2) ?></p>
                      </td>
                      <?php if ($sp['status'] == 1): ?>
                        <td class="px-6 py-4 whitespace-nowrap">
                          <span class="status-label bg-green-100 text-green-800">Active</span>
                        </td>
                      <?php elseif ($sp['status'] == 0): ?>
                        <td class="px-6 py-4 whitespace-nowrap">
                          <span class="status-label bg-red-100 text-red-800">Inactive</span>
                        </td>
                      <?php else: ?>
                        <td class="px-6 py-4 whitespace-nowrap">
                          <span class="status-label bg-red-100 text-red-800">Error</span>
                        </td>
                      <?php endif; ?>
                      <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex space-x-2">
                          <button class="text-green-400 hover:text-green-600 openEditSupplierModal"
                            data-id="<?= $sp['supplier_id'] ?>" data-name="<?= htmlspecialchars($sp['supplier_name']) ?>"
                            data-contact="<?= htmlspecialchars($sp['contact_number']) ?>"
                            data-email="<?= htmlspecialchars($sp['email']) ?>" data-status="<?= $sp['status'] ?>"
                            title="Edit Supplier">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                              <path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" />
                              <path fill-rule="evenodd"
                                d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"
                                clip-rule="evenodd" />
                            </svg>
                          </button>
                          <button class="text-red-500 hover:text-red-700 openArchiveSupplierModal"
                            data-id="<?= $sp['supplier_id'] ?>" data-name="<?= htmlspecialchars($sp['supplier_name']) ?>"
                            title="Archive Supplier">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                              <path fill-rule="evenodd"
                                d="M4 3a1 1 0 011-1h10a1 1 0 011 1v3a1 1 0 01-1 1H5a1 1 0 01-1-1V3zm3 4h10a1 1 0 011 1v9a1 1 0 01-1 1H5a1 1 0 01-1-1V8a1 1 0 011-1h10V5H7v3z"
                                clip-rule="evenodd" />
                            </svg>
                          </button>
                          <button class="text-blue-400 hover:text-blue-600 openViewSupplierModal"
                            data-id="<?= $sp['supplier_id'] ?>" data-name="<?= htmlspecialchars($sp['supplier_name']) ?>"
                            data-contact="<?= htmlspecialchars($sp['contact_number']) ?>"
                            data-email="<?= htmlspecialchars($sp['email']) ?>" data-status="<?= $sp['status'] ?>"
                            title="View Supplier">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                              <path
                                d="M10 3C5 3 1.73 7.11 1.05 10c.68 2.89 3.95 7 8.95 7s8.27-4.11 8.95-7c-.68-2.89-3.95-7-8.95-7zM10 15a5 5 0 110-10 5 5 0 010 10zm0-2a3 3 0 100-6 3 3 0 000 6z" />
                            </svg>
                          </button>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                      <?= $search !== '' ? 'No suppliers found matching your search.' : 'No suppliers found.' ?>
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
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">

          <?php
          $recentOrders = array_filter($purchase_orders, function ($po) {
            return in_array($po['status'], ['Received', 'Ordered']);
          });
          $recentOrders = array_slice($recentOrders, 0, 3);
          ?>

          <!-- Recent Orders -->
          <div class="bg-white border rounded-xl p-6">
            <h4 class="text-lg font-semibold mb-2">Recent Orders</h4>
            <p class="text-sm text-gray-500 mb-4">Latest purchase orders</p>
            <ul class="space-y-4 text-sm">
              <?php foreach ($recentOrders as $po): ?>
                <li class="flex justify-between items-start">
                  <div>
                    <p class="font-medium"><?= htmlspecialchars($po['po_number']); ?></p>
                    <p class="text-xs text-gray-500"><?= htmlspecialchars($po['supplier_name']); ?></p>
                    <p class="font-bold">₱<?= number_format($po['grand_total'], 2); ?></p>
                  </div>
                  <div class="text-right">
                    <?php if ($po['status'] === 'Received'): ?>
                      <span class="status-label bg-green-100 text-green-800"><?= $po['status']; ?></span>
                    <?php elseif ($po['status'] === 'Ordered'): ?>
                      <span class="status-label bg-blue-100 text-blue-800"><?= $po['status']; ?></span>
                    <?php endif; ?>
                    <p class="text-xs text-gray-500"><?= date('F j, Y, g:i A', strtotime($po['date'])); ?></p>
                  </div>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>

          <?php
          usort($allSuppliers, function ($a, $b) {
            return $b['total_spent'] <=> $a['total_spent'];
          });
          ?>

          <!-- Top Suppliers -->
          <div class="bg-white border rounded-xl p-6">
            <h4 class="text-lg font-semibold mb-2">Top Suppliers</h4>
            <p class="text-sm text-gray-500 mb-4">By total spending</p>
            <ul class="space-y-4">
              <?php foreach ($allSuppliers as $index => $sp): ?>
                <?php if ($index >= 3) break; ?>
                <li class="flex justify-between items-center">
                  <div class="flex items-center space-x-2">
                    <span class="text-lg font-bold"><?= $index + 1; ?>.</span>
                    <p class="text-sm font-medium"><?= htmlspecialchars($sp['supplier_name']); ?></p>
                  </div>
                  <span class="font-bold text-gray-800">₱<?= number_format($sp['total_spent']); ?></span>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>

        </div>
    </section>
  </main>

  <!-- Add Supplier Modal -->
  <div id="addSupplierModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-white rounded-lg w-full max-w-md p-6">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold">Add New Supplier</h3>
        <button id="closeAddSupplierModal" class="text-gray-500 hover:text-gray-700">
          &times;
        </button>
      </div>

      <form id="addSupplierForm" method="POST" class="space-y-4">
        <div>
          <label for="supplier_name" class="block text-sm font-medium text-gray-700">Supplier Name</label>
          <input type="text" name="supplier_name" id="supplier_name" required
            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200">
        </div>

        <div>
          <label for="contact_number" class="block text-sm font-medium text-gray-700">Contact Number</label>
          <input type="text" name="contact_number" id="contact_number"
            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200">
        </div>

        <div>
          <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
          <input type="email" name="email" id="email"
            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200">
        </div>

        <div>
          <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
          <select name="status" id="status"
            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200">
            <option value="1" selected>Active</option>
            <option value="0">Inactive</option>
          </select>
        </div>

        <div class="flex justify-end space-x-2 pt-4">
          <button type="button" id="cancelModalBtn"
            class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">
            Cancel
          </button>
          <button name="create_supplier" type="submit" class="px-4 py-2 bg-black text-white rounded hover:bg-gray-800">
            Save Supplier
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Update Supplier Modal -->
  <div id="editSupplierModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-white rounded-lg w-full max-w-md p-6">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold">Update Supplier</h3>
        <button id="closeEditSupplierModal" class="text-gray-500 hover:text-gray-700">&times;</button>
      </div>

      <form id="editSupplierForm" method="POST" class="space-y-4">
        <input type="hidden" name="supplier_id" id="edit_supplier_id">

        <div>
          <label for="edit_supplier_name" class="block text-sm font-medium text-gray-700">Supplier Name</label>
          <input type="text" name="supplier_name" id="edit_supplier_name" required
            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200">
        </div>

        <div>
          <label for="edit_contact_number" class="block text-sm font-medium text-gray-700">Contact Number</label>
          <input type="text" name="contact_number" id="edit_contact_number"
            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200">
        </div>

        <div>
          <label for="edit_email" class="block text-sm font-medium text-gray-700">Email Address</label>
          <input type="email" name="email" id="edit_email"
            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200">
        </div>

        <div>
          <label for="edit_status" class="block text-sm font-medium text-gray-700">Status</label>
          <select name="status" id="edit_status"
            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200">
            <option value="1">Active</option>
            <option value="0">Inactive</option>
          </select>
        </div>

        <div class="flex justify-end space-x-2 pt-4">
          <button type="button" id="cancelEditModalBtn"
            class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">
            Cancel
          </button>
          <button name="update_supplier" type="submit" class="px-4 py-2 bg-black text-white rounded hover:bg-gray-800">
            Update Supplier
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Archive Supplier Modal -->
  <div id="archiveSupplierModal"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-white rounded-lg w-full max-w-sm p-6">
      <h3 class="text-lg font-semibold mb-4 text-yellow-600">Archive Supplier</h3>
      <p class="mb-4 text-sm text-gray-700">
        Are you sure you want to archive <span id="archive_supplier_name" class="font-bold"></span>?
        This action can be undone.
      </p>

      <form method="POST">
        <input type="hidden" name="supplier_id" id="archive_supplier_id">
        <div class="flex justify-end space-x-2">
          <button type="button" id="cancelArchiveModalBtn"
            class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">
            Cancel
          </button>
          <button type="submit" name="archive_supplier"
            class="px-4 py-2 bg-yellow-600 text-white rounded hover:bg-yellow-700">
            Confirm Archive
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- View Supplier Modal -->
  <div id="viewSupplierModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-white rounded-lg w-full max-w-md p-6 shadow-lg">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold text-gray-900">Supplier Details</h3>
        <button id="closeViewSupplierModal"
          class="text-gray-500 hover:text-gray-700 text-xl leading-none">&times;</button>
      </div>

      <div class="space-y-3 text-sm text-gray-700">
        <div>
          <span class="font-semibold">Company Name:</span>
          <p id="view_supplier_name" class="text-gray-900"></p>
        </div>
        <div>
          <span class="font-semibold">Contact Number:</span>
          <p id="view_contact_number" class="text-gray-900"></p>
        </div>
        <div>
          <span class="font-semibold">Email Address:</span>
          <p id="view_email" class="text-gray-900"></p>
        </div>
        <div>
          <span class="font-semibold">Status:</span>
          <p id="view_status" class="inline-block px-2 py-1 text-xs font-medium rounded-full"></p>
        </div>
      </div>
      <div class="flex justify-end mt-6">
        <button
          class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-green-600 bg-green-100 hover:bg-green-200 rounded-md transition openEditSupplierModal"
          id="viewToEditBtn" data-id="" data-name="" data-contact="" data-email="" data-status="">
          Edit
        </button>
      </div>
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
      sidebar.classList.add('-translate-x-full');
    });
  </script>

  <!-- Save scroll before reload/submit -->
  <script>
    window.addEventListener('beforeunload', () => {
      sessionStorage.setItem('scrollPos', window.scrollY);
    });

    window.addEventListener('load', () => {
      const scrollPos = sessionStorage.getItem('scrollPos');
      if (scrollPos) {
        window.scrollTo(0, parseInt(scrollPos));
        sessionStorage.removeItem('scrollPos');
      }
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
    const searchForm = searchInput.closest('form');

    if (searchInput) {
      searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
          searchForm.submit();
        }, 500);
      });
    }
  </script>

  <!-- Add Supplier Script -->
  <script>
    const openModalBtn = document.getElementById('openAddSupplierModal');
    const closeModalBtn = document.getElementById('closeAddSupplierModal');
    const cancelModalBtn = document.getElementById('cancelModalBtn');
    const modal = document.getElementById('addSupplierModal');

    openModalBtn.addEventListener('click', () => {
      modal.classList.remove('hidden');
    });

    closeModalBtn.addEventListener('click', () => {
      modal.classList.add('hidden');
    });

    cancelModalBtn.addEventListener('click', () => {
      modal.classList.add('hidden');
    });

    window.addEventListener('click', (e) => {
      if (e.target === modal) {
        modal.classList.add('hidden');
      }
    });
  </script>

  <!-- Edit Supplier Script -->
  <script>
    const editButtons = document.querySelectorAll('.openEditSupplierModal');
    const editModal = document.getElementById('editSupplierModal');
    const closeEditModal = document.getElementById('closeEditSupplierModal');
    const cancelEditModal = document.getElementById('cancelEditModalBtn');

    editButtons.forEach(button => {
      button.addEventListener('click', () => {
        const id = button.dataset.id;
        const name = button.dataset.name;
        const contact = button.dataset.contact;
        const email = button.dataset.email;
        const status = button.dataset.status;

        document.getElementById('edit_supplier_id').value = id;
        document.getElementById('edit_supplier_name').value = name;
        document.getElementById('edit_contact_number').value = contact;
        document.getElementById('edit_email').value = email;
        document.getElementById('edit_status').value = status;

        editModal.classList.remove('hidden');
      });
    });

    closeEditModal.addEventListener('click', () => {
      editModal.classList.add('hidden');
    });

    cancelEditModal.addEventListener('click', () => {
      editModal.classList.add('hidden');
    });

    window.addEventListener('click', (e) => {
      if (e.target === editModal) {
        editModal.classList.add('hidden');
      }
    });
  </script>

  <!-- Archive Supplier Script -->
  <script>
    const archiveButtons = document.querySelectorAll('.openArchiveSupplierModal');
    const archiveModal = document.getElementById('archiveSupplierModal');
    const archiveSupplierId = document.getElementById('archive_supplier_id');
    const archiveSupplierName = document.getElementById('archive_supplier_name');
    const cancelArchiveBtn = document.getElementById('cancelArchiveModalBtn');

    archiveButtons.forEach(button => {
      button.addEventListener('click', () => {
        const id = button.getAttribute('data-id');
        const name = button.getAttribute('data-name');

        archiveSupplierId.value = id;
        archiveSupplierName.textContent = name;

        archiveModal.classList.remove('hidden');
      });
    });

    cancelArchiveBtn.addEventListener('click', () => {
      archiveModal.classList.add('hidden');
    });

    window.addEventListener('click', (e) => {
      if (e.target === archiveModal) {
        archiveModal.classList.add('hidden');
      }
    });
  </script>

  <!-- View Supplier Script -->
  <script>
    const viewButtons = document.querySelectorAll('.openViewSupplierModal');
    const viewModal = document.getElementById('viewSupplierModal');
    const closeViewModal = document.getElementById('closeViewSupplierModal');

    const editBtnInsideView = document.getElementById('viewToEditBtn');

    viewButtons.forEach(button => {
      button.addEventListener('click', () => {
        const id = button.dataset.id;
        const name = button.dataset.name;
        const contact = button.dataset.contact;
        const email = button.dataset.email;
        const status = button.dataset.status;

        document.getElementById('view_supplier_name').textContent = name;
        document.getElementById('view_contact_number').textContent = contact;
        document.getElementById('view_email').textContent = email;

        const statusEl = document.getElementById('view_status');
        statusEl.textContent = status === "1" ? "Active" : "Inactive";
        statusEl.className = `inline-block px-2 py-1 text-xs font-medium rounded-full ${status === "1"
          ? 'bg-green-100 text-green-800'
          : 'bg-red-100 text-red-800'
          }`;

        editBtnInsideView.dataset.id = id;
        editBtnInsideView.dataset.name = name;
        editBtnInsideView.dataset.contact = contact;
        editBtnInsideView.dataset.email = email;
        editBtnInsideView.dataset.status = status;

        viewModal.classList.remove('hidden');
      });
    });

    closeViewModal.addEventListener('click', () => {
      viewModal.classList.add('hidden');
    });

    window.addEventListener('click', (e) => {
      if (e.target === viewModal) {
        viewModal.classList.add('hidden');
      }
    });

    editBtnInsideView.addEventListener('click', () => {
      viewModal.classList.add('hidden');
    });
  </script>

</body>

</html>