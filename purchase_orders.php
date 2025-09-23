<?php
  include "database/database.php";
  $database->login_session();
  $database->create_purchase_order();
  $suppliers = $database->select_suppliers();
  $items = $database->select_items();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>POS & Inventory - Purchase Orders</title>
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
      <a href="purchase_orders.php" class="flex items-center px-3 py-2 rounded-lg bg-gray-100 font-medium">
        <span class="ml-2">Purchase Orders</span>
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
    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
    </svg>
    <a href="logout.php" class="text-sm font-medium">Sign Out</a>
  </div>
</div>

  </aside>

  <main class="flex-1 p-6">
    <header class="mb-6">
      <h2 class="text-2xl font-bold text-gray-800">Inventory Purchases</h2>
    </header>

    <section class="bg-white rounded-xl shadow-md p-6">
      <div class="flex items-center justify-between mb-4">
        <div>
          <h3 class="text-xl font-bold text-gray-800">Purchase Orders</h3>
          <p class="text-sm text-gray-500">Manage supplier orders and inventory procurement</p>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white border rounded-xl p-4 flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500">Total POs</p>
            <h4 class="text-2xl font-bold">4</h4>
            <p class="text-xs text-gray-500">all time</p>
          </div>
        </div>
        <div class="bg-white border rounded-xl p-4 flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500">Pending Orders</p>
            <h4 class="text-2xl font-bold">2</h4>
            <p class="text-xs text-gray-500">awaiting delivery</p>
          </div>
        </div>
        <div class="bg-white border rounded-xl p-4 flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500">Received</p>
            <h4 class="text-2xl font-bold">1</h4>
            <p class="text-xs text-gray-500">completed orders</p>
          </div>
        </div>
        <div class="bg-white border rounded-xl p-4 flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500">Total Value</p>
            <h4 class="text-2xl font-bold">$2370.00</h4>
            <p class="text-xs text-gray-500">active orders</p>
          </div>
        </div>
      </div>

      <div class="bg-white border rounded-xl p-6">
        <div class="flex items-center justify-between mb-4">
          <div class="flex items-center space-x-4">
            <div class="relative flex-1">
              <input type="text" placeholder="Search by PO number or supplier..." class="w-full px-4 py-2 border rounded-lg focus:outline-none">
            </div>
            <select class="px-4 py-2 border rounded-lg focus:outline-none">
              <option>All Orders</option>
              <option>Pending</option>
              <option>Received</option>
              <option>Cancelled</option>
            </select>
          </div>
          <button id="createPoButton" class="px-4 py-2 text-sm bg-black text-white rounded-lg hover:bg-gray-800">
           + Create PO
          </button>
        </div>

        <h4 class="text-lg font-semibold mb-2">Purchase Orders</h4>
        <p class="text-sm text-gray-500 mb-4">4 orders found</p>

        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PO Number</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order Date</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expected Date</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Amount</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">PO-2024-001</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">TechCorp</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">2024-01-15</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">2024-01-22</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">3 items</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">$850.00</td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="status-label bg-blue-100 text-blue-800">Ordered</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <div class="flex space-x-2">
                    <button class="text-gray-400 hover:text-gray-600">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M10 12a2 2 0 100-4 2 2 0 000 4z" /><path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" /></svg>
                    </button>
                    <button class="text-gray-400 hover:text-gray-600">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                    </button>
                  </div>
                </td>
              </tr>
              <tr>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">PO-2024-002</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">ElectroSupply</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">2024-01-12</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">2024-01-19</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">2 items</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">$320.00</td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="status-label bg-green-100 text-green-800">Received</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <div class="flex space-x-2">
                    <button class="text-gray-400 hover:text-gray-600">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M10 12a2 2 0 100-4 2 2 0 000 4z" /><path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" /></svg>
                    </button>
                    <button class="text-gray-400 hover:text-gray-600">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                    </button>
                  </div>
                </td>
              </tr>
              <tr>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">PO-2024-003</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">GadgetHub</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">2024-01-14</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">2024-01-21</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">4 items</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">$1200.00</td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="status-label bg-blue-100 text-blue-800">Ordered</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <div class="flex space-x-2">
                    <button class="text-gray-400 hover:text-gray-600">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M10 12a2 2 0 100-4 2 2 0 000 4z" /><path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" /></svg>
                    </button>
                    <button class="text-gray-400 hover:text-gray-600">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                    </button>
                  </div>
                </td>
              </tr>
              <tr>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">PO-2024-004</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">CompuParts</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">2024-01-10</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">2024-01-17</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">2 items</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">$450.00</td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="status-label bg-red-100 text-red-800">Cancelled</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <div class="flex space-x-2">
                    <button class="text-gray-400 hover:text-gray-600">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M10 12a2 2 0 100-4 2 2 0 000 4z" /><path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" /></svg>
                    </button>
                    <button class="text-gray-400 hover:text-gray-600">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </section>
  </main>

  
</body>
</html>