<?php
include "database/database.php";
$database->login_session();
$database->create_supplier();
$database->update_supplier();
$database->delete_supplier();
$suppliers = $database->select_suppliers();
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
      <a href="suppliers.php" class="flex items-center px-3 py-2 rounded-lg bg-gray-100 font-medium">
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

  <main class="flex-1 p-6">
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
            <h4 class="text-2xl font-bold"><?php echo count($suppliers) ?></h4>
            <p class="text-xs text-gray-500">registered suppliers</p>
          </div>
        </div>
        <div class="bg-white border rounded-xl p-4 flex items-center justify-between">
          <div>
            <?php
            $active_count = 0;
            foreach ($suppliers as $supplier) {
              if ($supplier['status'] == 1) {
                $active_count++;
              }
            }
            ?>
            <p class="text-sm text-gray-500">Active Suppliers</p>
            <h4 class="text-2xl font-bold"><?php echo $active_count; ?></h4>
            <p class="text-xs text-gray-500">currently active</p>
          </div>
        </div>
        <div class="bg-white border rounded-xl p-4 flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500">Total Spent</p>
            <h4 class="text-2xl font-bold">₱41291.50</h4>
            <p class="text-xs text-gray-500">all time purchases</p>
          </div>
        </div>
        <div class="bg-white border rounded-xl p-4 flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500">Total Orders</p>
            <h4 class="text-2xl font-bold">50</h4>
            <p class="text-xs text-gray-500">purchase orders</p>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 gap-4">
        <!-- SUPPLIER TABLE -->
        <div class="bg-white border rounded-xl p-6">
          <div class="flex items-center justify-between mb-4">
            <div class="flex items-center space-x-4 flex-grow">
              <input type="text" id="searchInput" placeholder="Search suppliers by name..."
                class="flex-1 px-4 py-2 border rounded-lg focus:outline-none">
            </div>
            <button id="openAddSupplierModal"
              class="px-4 py-2 text-sm bg-black text-white rounded-lg hover:bg-gray-800 ml-4">
              + Add Supplier
            </button>
          </div>
          <?php
          if (isset($_SESSION['create-success'])) {
            $success = $_SESSION['create-success'];
            ?>
            <div id="successAlert"
              class="mb-4 px-4 py-3 bg-green-100 border border-green-400 text-green-700 text-sm rounded-lg">
              <?php echo $success ?>
            </div>
            <?php
          } ?>

          <?php
          if (isset($_SESSION['create-error'])) {
            $error = $_SESSION['create-error'];
            ?>
            <div id="errorAlert" class="mb-4 px-4 py-3 bg-red-100 border border-red-400 text-red-700 text-sm rounded-lg">
              <?php echo $error ?>
            </div>
            <?php
          } ?>
          <h4 class="text-lg font-semibold mb-2">Supplier Database</h4>
          <p class="text-sm text-gray-500 mb-4"><?= count($suppliers) ?> suppliers found</p>

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
                <?php foreach ($suppliers as $sp): ?>
                  <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <p class="text-sm font-medium text-gray-900"><?php echo $sp['supplier_name']; ?></p>
                      <p class="text-xs text-gray-500">25 products</p>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      <p class="text-gray-900 font-medium">15</p>
                      <p class="text-xs">Last: 2024-01-15</p>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      <p class="text-gray-900 font-medium">₱12450.75</p>
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
                          data-email="<?= htmlspecialchars($sp['email']) ?>" data-status="<?= $sp['status'] ?>">
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" />
                            <path fill-rule="evenodd"
                              d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"
                              clip-rule="evenodd" />
                          </svg>
                        </button>
                        <button class="text-red-500 hover:text-red-700 openDeleteSupplierModal"
                          data-id="<?= $sp['supplier_id'] ?>" data-name="<?= htmlspecialchars($sp['supplier_name']) ?>">
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                              d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                              clip-rule="evenodd" />
                          </svg>
                        </button>
                        <button class="text-gray-400 hover:text-gray-600 openViewSupplierModal"
                          data-id="<?= $sp['supplier_id'] ?>" data-name="<?= htmlspecialchars($sp['supplier_name']) ?>"
                          data-contact="<?= htmlspecialchars($sp['contact_number']) ?>"
                          data-email="<?= htmlspecialchars($sp['email']) ?>" data-status="<?= $sp['status'] ?>">
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path
                              d="M10 3C5 3 1.73 7.11 1.05 10c.68 2.89 3.95 7 8.95 7s8.27-4.11 8.95-7c-.68-2.89-3.95-7-8.95-7zM10 15a5 5 0 110-10 5 5 0 010 10zm0-2a3 3 0 100-6 3 3 0 000 6z" />
                          </svg>
                        </button>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">

          <!-- Recent Orders -->
          <div class="bg-white border rounded-xl p-6">
            <h4 class="text-lg font-semibold mb-2">Recent Orders</h4>
            <p class="text-sm text-gray-500 mb-4">Latest purchase orders</p>
            <ul class="space-y-4 text-sm">
              <li class="flex justify-between items-start">
                <div>
                  <p class="font-medium">PO-2024-001</p>
                  <p class="text-xs text-gray-500">TechCorp</p>
                  <p class="font-bold">$850</p>
                </div>
                <div class="text-right">
                  <span class="status-label bg-blue-100 text-blue-800">Ordered</span>
                  <p class="text-xs text-gray-500">2024-01-15</p>
                </div>
              </li>
              <li class="flex justify-between items-start">
                <div>
                  <p class="font-medium">PO-2024-002</p>
                  <p class="text-xs text-gray-500">ElectroSupply</p>
                  <p class="font-bold">$320</p>
                </div>
                <div class="text-right">
                  <span class="status-label bg-green-100 text-green-800">Received</span>
                  <p class="text-xs text-gray-500">2024-01-12</p>
                </div>
              </li>
              <li class="flex justify-between items-start">
                <div>
                  <p class="font-medium">PO-2024-003</p>
                  <p class="text-xs text-gray-500">CompuParts</p>
                  <p class="font-bold">$1200</p>
                </div>
                <div class="text-right">
                  <span class="status-label bg-blue-100 text-blue-800">Ordered</span>
                  <p class="text-xs text-gray-500">2024-01-10</p>
                </div>
              </li>
            </ul>
          </div>

          <!-- Top Suppliers -->
          <div class="bg-white border rounded-xl p-6">
            <h4 class="text-lg font-semibold mb-2">Top Suppliers</h4>
            <p class="text-sm text-gray-500 mb-4">By total spending</p>
            <ul class="space-y-4">
              <li class="flex justify-between items-center">
                <div class="flex items-center space-x-2">
                  <span class="text-lg font-bold">1.</span>
                  <p class="text-sm font-medium">CompuParts</p>
                </div>
                <span class="font-bold text-gray-800">$18,750.50</span>
              </li>
              <li class="flex justify-between items-center">
                <div class="flex items-center space-x-2">
                  <span class="text-lg font-bold">2.</span>
                  <p class="text-sm font-medium">TechCorp</p>
                </div>
                <span class="font-bold text-gray-800">$12,450.75</span>
              </li>
              <li class="flex justify-between items-center">
                <div class="flex items-center space-x-2">
                  <span class="text-lg font-bold">3.</span>
                  <p class="text-sm font-medium">ElectroSupply</p>
                </div>
                <span class="font-bold text-gray-800">$6,890.25</span>
              </li>
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

  <!-- Delete Supplier Modal -->
  <div id="deleteSupplierModal"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-white rounded-lg w-full max-w-sm p-6">
      <h3 class="text-lg font-semibold mb-4 text-red-600">Delete Supplier</h3>
      <p class="mb-4 text-sm text-gray-700">
        Are you sure you want to delete <span id="delete_supplier_name" class="font-bold"></span>?
        This action cannot be undone.
      </p>

      <form method="POST">
        <input type="hidden" name="supplier_id" id="delete_supplier_id">
        <div class="flex justify-end space-x-2">
          <button type="button" id="cancelDeleteModalBtn"
            class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">
            Cancel
          </button>
          <button type="submit" name="delete_supplier" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
            Confirm Delete
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

    <!-- Search Bar -->
    <script>
      document.getElementById('searchInput').addEventListener('input', function () {
        const searchValue = this.value.toLowerCase();
        const tableRows = document.querySelectorAll('tbody tr');

        tableRows.forEach(row => {
          const name = row.querySelector('td:nth-child(1) p').textContent.toLowerCase();

          const match = name.includes(searchValue);
          row.style.display = match ? '' : 'none';
        });
      });
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

    <!-- Delete Supplier Script -->
    <script>
      const deleteButtons = document.querySelectorAll('.openDeleteSupplierModal');
      const deleteModal = document.getElementById('deleteSupplierModal');
      const deleteSupplierId = document.getElementById('delete_supplier_id');
      const deleteSupplierName = document.getElementById('delete_supplier_name');
      const cancelDeleteBtn = document.getElementById('cancelDeleteModalBtn');

      deleteButtons.forEach(button => {
        button.addEventListener('click', () => {
          const id = button.getAttribute('data-id');
          const name = button.getAttribute('data-name');

          deleteSupplierId.value = id;
          deleteSupplierName.textContent = name;

          deleteModal.classList.remove('hidden');
        });
      });

      cancelDeleteBtn.addEventListener('click', () => {
        deleteModal.classList.add('hidden');
      });

      window.addEventListener('click', (e) => {
        if (e.target === deleteModal) {
          deleteModal.classList.add('hidden');
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