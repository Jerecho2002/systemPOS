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

  <!-- Mobile Sidebar Toggle Button -->
  <button id="sidebar-toggle" class="md:hidden p-3 fixed top-4 left-4 z-60 text-white rounded shadow-lg"
    style="background-color: rgba(170, 170, 170, 0.82);">
    ☰
  </button>


  <?php include "sidebar.php"; ?>

  <!-- Main Content -->
  <main class="flex-1 ml-0 md:ml-64 p-6">
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
</body>
</html>