<?php
  include "database/database.php";
  $database->login_session();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>POS & Inventory - Inventory Reports</title>
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
      <a href="suppliers.php" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-100">
        <span class="ml-2">Suppliers</span>
      </a>
      <a href="inventory_reports.php" class="flex items-center px-3 py-2 rounded-lg bg-gray-100 font-medium">
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
      <h2 class="text-2xl font-bold text-gray-800">Inventory Reports</h2>
      <p class="text-sm text-gray-500">Analyze inventory performance, valuation, and trends</p>
    </header>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
      <div class="bg-white border rounded-xl p-4 flex items-center justify-between">
        <div>
          <p class="text-sm text-gray-500">Total Value</p>
          <h4 class="text-2xl font-bold">$62800.00</h4>
          <p class="text-xs text-gray-500">Inventory worth</p>
        </div>
      </div>
      <div class="bg-white border rounded-xl p-4 flex items-center justify-between">
        <div>
          <p class="text-sm text-gray-500">Total Items</p>
          <h4 class="text-2xl font-bold">425</h4>
          <p class="text-xs text-gray-500">in stock</p>
        </div>
      </div>
      <div class="bg-white border rounded-xl p-4 flex items-center justify-between">
        <div>
          <p class="text-sm text-gray-500">Low Stock</p>
          <h4 class="text-2xl font-bold">8</h4>
          <p class="text-xs text-gray-500">items need reorder</p>
        </div>
      </div>
      <div class="bg-white border rounded-xl p-4 flex items-center justify-between">
        <div>
          <p class="text-sm text-gray-500">Out of Stock</p>
          <h4 class="text-2xl font-bold">3</h4>
          <p class="text-xs text-gray-500">urgent reorder</p>
        </div>
      </div>
      <div class="bg-white border rounded-xl p-4 flex items-center justify-between">
        <div>
          <p class="text-sm text-gray-500">Turnover Rate</p>
          <h4 class="text-2xl font-bold">4.2x</h4>
          <p class="text-xs text-gray-500">per year</p>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
      <div class="bg-white border rounded-xl p-6">
        <div class="flex items-center justify-between mb-4">
          <div>
            <h4 class="text-lg font-semibold">Inventory Value Trend</h4>
            <p class="text-sm text-gray-500">Monthly inventory valuation over time</p>
          </div>
        </div>
        <div class="h-64 bg-gray-100 rounded-lg flex items-center justify-center text-gray-400">
          Line Chart Placeholder
        </div>
      </div>
      <div class="bg-white border rounded-xl p-6">
        <h4 class="text-lg font-semibold mb-2">Inventory by Category</h4>
        <p class="text-sm text-gray-500">Value distribution across product categories</p>
        <div class="h-64 bg-gray-100 rounded-lg flex items-center justify-center text-gray-400">
          Pie Chart Placeholder
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
      <div class="bg-white border rounded-xl p-6">
        <h4 class="text-lg font-semibold mb-4">Top Products by Value</h4>
        <p class="text-sm text-gray-500 mb-4">Highest value inventory items</p>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Value</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Margin</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">Mechanical Keyboard</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">145</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">$18650.00</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">38.5%</td>
              </tr>
              <tr>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">Wireless Mouse</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">234</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">$7016.00</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">33.3%</td>
              </tr>
              <tr>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">USB-C Hub</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">89</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">$4005.00</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">33.3%</td>
              </tr>
              <tr>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">Monitor Stand</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">67</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">$3082.00</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">45.7%</td>
              </tr>
              <tr>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">Bluetooth Headphones</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">156</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">$14040.00</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">33.3%</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="bg-white border rounded-xl p-6">
        <h4 class="text-lg font-semibold mb-4">Reorder Alert List</h4>
        <p class="text-sm text-gray-500 mb-4">Items requiring immediate attention</p>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Min</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priority</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">USB Cable - Type C</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">5</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">20</td>
                <td class="px-6 py-4 whitespace-nowrap"><span class="status-label bg-red-100 text-red-800">Critical</span></td>
              </tr>
              <tr>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">Bluetooth Headphones</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">0</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">5</td>
                <td class="px-6 py-4 whitespace-nowrap"><span class="status-label bg-red-100 text-red-800">Critical</span></td>
              </tr>
              <tr>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">Mechanical Keyboard</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">3</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">5</td>
                <td class="px-6 py-4 whitespace-nowrap"><span class="status-label bg-orange-100 text-orange-800">Low</span></td>
              </tr>
              <tr>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">HDMI Cable</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">8</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">15</td>
                <td class="px-6 py-4 whitespace-nowrap"><span class="status-label bg-orange-100 text-orange-800">Low</span></td>
              </tr>
              <tr>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">Wireless Charger</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">2</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">8</td>
                <td class="px-6 py-4 whitespace-nowrap"><span class="status-label bg-orange-100 text-orange-800">Low</span></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <div class="bg-white border rounded-xl p-6">
        <h4 class="text-lg font-semibold mb-4">Supplier Performance Analysis</h4>
        <p class="text-sm text-gray-500 mb-4">Delivery performance and order statistics</p>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Orders</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">On-Time Delivery</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Avg. Delivery Days</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Value</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Performance Score</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">TechCorp</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">15</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">13/15 (86.7%)</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">7 days</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">$12450.00</td>
                <td class="px-6 py-4 whitespace-nowrap"><span class="status-label bg-green-100 text-green-800">Good</span></td>
              </tr>
              <tr>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">ElectroSupply</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">8</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">7/8 (87.5%)</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">5 days</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">$6890.00</td>
                <td class="px-6 py-4 whitespace-nowrap"><span class="status-label bg-green-100 text-green-800">Good</span></td>
              </tr>
              <tr>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">CompuParts</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">22</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">19/22 (86.4%)</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">8 days</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">$18750.00</td>
                <td class="px-6 py-4 whitespace-nowrap"><span class="status-label bg-green-100 text-green-800">Good</span></td>
              </tr>
              <tr>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">GadgetHub</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">5</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">4/5 (80.0%)</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">12 days</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">$3200.00</td>
                <td class="px-6 py-4 whitespace-nowrap"><span class="status-label bg-red-100 text-red-800">Poor</span></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="bg-white border rounded-xl p-6">
        <h4 class="text-lg font-semibold mb-4">Inventory Insights</h4>
        <p class="text-sm text-gray-500 mb-4">Key recommendations</p>
        <div class="space-y-4">
          <div class="bg-gray-50 border rounded-lg p-4">
            <h5 class="font-medium text-sm">Critical Stock Alert</h5>
            <p class="text-xs text-gray-500">3 items are out of stock and 8 items are below minimum levels.</p>
          </div>
          <div class="bg-gray-50 border rounded-lg p-4">
            <h5 class="font-medium text-sm">Best Performing Category</h5>
            <p class="text-xs text-gray-500">Peripherals lead with $18500.00 in inventory value.</p>
          </div>
          <div class="bg-gray-50 border rounded-lg p-4">
            <h5 class="font-medium text-sm">Supplier Recommendation</h5>
            <p class="text-xs text-gray-500">ElectroSupply has the best delivery performance at 87.5% on-time rate.</p>
          </div>
          <div class="bg-gray-50 border rounded-lg p-4">
            <h5 class="font-medium text-sm">Inventory Turnover</h5>
            <p class="text-xs text-gray-500">Current turnover rate of 4.2x/year indicates healthy stock movement.</p>
          </div>
        </div>
      </div>
    </div>
  </main>
</body>
</html>