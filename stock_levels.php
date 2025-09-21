<?php
  include "database/database.php";
  $database->login_session();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>POS & Inventory - Stock Levels</title>
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
      <a href="product_catalog.php" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-100">
        <span class="ml-2">Product Catalog</span>
      </a>
      <a href="stock_levels.php" class="flex items-center px-3 py-2 rounded-lg bg-gray-100 font-medium">
        <span class="ml-2">Stock Levels</span>
      </a>
      <a href="purchase_orders.php" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-100">
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
      <h2 class="text-2xl font-bold text-gray-800">Inventory Stock</h2>
    </header>

    <section class="bg-white rounded-xl shadow-md p-6">
      <div class="flex items-center justify-between mb-4">
        <div>
          <h3 class="text-xl font-bold text-gray-800">Stock Levels</h3>
          <p class="text-sm text-gray-500">Monitor inventory levels and manage stock adjustments</p>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white border rounded-xl p-4 flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500">Total Items</p>
            <h4 class="text-2xl font-bold">6</h4>
            <p class="text-xs text-gray-500">products in catalog</p>
          </div>
        </div>
        <div class="bg-white border rounded-xl p-4 flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500">Low Stock Items</p>
            <h4 class="text-2xl font-bold">3</h4>
            <p class="text-xs text-gray-500">need reordering</p>
          </div>
        </div>
        <div class="bg-white border rounded-xl p-4 flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500">Out of Stock</p>
            <h4 class="text-2xl font-bold">1</h4>
            <p class="text-xs text-gray-500">urgent reorder</p>
          </div>
        </div>
        <div class="bg-white border rounded-xl p-4 flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500">Total Value</p>
            <h4 class="text-2xl font-bold">$1691.57</h4>
            <p class="text-xs text-gray-500">inventory value</p>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="bg-white border rounded-xl p-6 lg:col-span-2">
          <h4 class="text-lg font-semibold mb-2">Inventory Levels</h4>
          <p class="text-sm text-gray-500 mb-4">6 items displayed</p>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Stock</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Min/Max</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <p class="text-sm font-medium text-gray-900">Bluetooth Headphones</p>
                    <p class="text-xs text-gray-500">345678901</p>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">C2-D3</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <p class="text-gray-900 font-medium">0</p>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <p class="text-xs">Min: 5</p>
                    <p class="text-xs">Max: 30</p>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="status-label bg-red-100 text-red-800">Out of Stock</span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <button class="px-4 py-2 border rounded-lg text-gray-700 text-sm hover:bg-gray-100">Adjust</button>
                  </td>
                </tr>
                <tr>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <p class="text-sm font-medium text-gray-900">USB Cable - Type C</p>
                    <p class="text-xs text-gray-500">234567890</p>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">B3-C1</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <p class="text-gray-900 font-medium">5</p>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <p class="text-xs">Min: 20</p>
                    <p class="text-xs">Max: 100</p>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="status-label bg-red-100 text-red-800">Low Stock</span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <button class="px-4 py-2 border rounded-lg text-gray-700 text-sm hover:bg-gray-100">Adjust</button>
                  </td>
                </tr>
                <tr>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <p class="text-sm font-medium text-gray-900">Mechanical Keyboard</p>
                    <p class="text-xs text-gray-500">567890123</p>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">A3-B1</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <p class="text-gray-900 font-medium">3</p>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <p class="text-xs">Min: 5</p>
                    <p class="text-xs">Max: 25</p>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="status-label bg-red-100 text-red-800">Low Stock</span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <button class="px-4 py-2 border rounded-lg text-gray-700 text-sm hover:bg-gray-100">Adjust</button>
                  </td>
                </tr>
                <tr>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <p class="text-sm font-medium text-gray-900">HDMI Cable</p>
                    <p class="text-xs text-gray-500">678901234</p>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">B2-C3</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <p class="text-gray-900 font-medium">8</p>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <p class="text-xs">Min: 15</p>
                    <p class="text-xs">Max: 80</p>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="status-label bg-red-100 text-red-800">Low Stock</span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <button class="px-4 py-2 border rounded-lg text-gray-700 text-sm hover:bg-gray-100">Adjust</button>
                  </td>
                </tr>
                <tr>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <p class="text-sm font-medium text-gray-900">Wireless Mouse</p>
                    <p class="text-xs text-gray-500">123456789</p>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">A1-B2</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <p class="text-gray-900 font-medium">15</p>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <p class="text-xs">Min: 10</p>
                    <p class="text-xs">Max: 50</p>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="status-label bg-green-100 text-green-800">Normal</span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <button class="px-4 py-2 border rounded-lg text-gray-700 text-sm hover:bg-gray-100">Adjust</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div class="bg-white border rounded-xl p-6 col-span-1">
          <h4 class="text-lg font-semibold mb-2">Recent Adjustments</h4>
          <p class="text-sm text-gray-500 mb-4">Latest stock changes</p>
          <ul class="space-y-4">
            <li class="flex justify-between items-start">
              <div>
                <p class="text-sm font-medium text-gray-900">Wireless Mouse</p>
                <p class="text-xs text-gray-500">New shipment received</p>
                <p class="text-xs text-gray-500">Admin</p>
              </div>
              <div class="text-right">
                <span class="text-green-600 font-bold text-lg">+10</span>
                <p class="text-xs text-gray-500">2024-01-15</p>
              </div>
            </li>
            <li class="flex justify-between items-start">
              <div>
                <p class="text-sm font-medium text-gray-900">USB Cable - Type C</p>
                <p class="text-xs text-gray-500">Damaged goods</p>
                <p class="text-xs text-gray-500">Staff</p>
              </div>
              <div class="text-right">
                <span class="text-red-600 font-bold text-lg">-15</span>
                <p class="text-xs text-gray-500">2024-01-14</p>
              </div>
            </li>
            <li class="flex justify-between items-start">
              <div>
                <p class="text-sm font-medium text-gray-900">Laptop Stand</p>
                <p class="text-xs text-gray-500">Returns from customer</p>
                <p class="text-xs text-gray-500">Admin</p>
              </div>
              <div class="text-right">
                <span class="text-green-600 font-bold text-lg">+5</span>
                <p class="text-xs text-gray-500">2024-01-13</p>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </section>
  </main>
</body>
</html>