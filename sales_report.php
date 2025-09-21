<?php
  include "database/database.php";
  $database->login_session();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>POS & Inventory - Sales Reports</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <style>
    /* Custom styles to mimic the icons in the image */
    .icon-placeholder {
      width: 24px;
      height: 24px;
      background-color: #d1d5db; /* A light gray */
      border-radius: 50%;
      display: flex;
      justify-content: center;
      align-items: center;
      font-size: 14px;
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
      <a href="sales_report.php" class="flex items-center px-3 py-2 rounded-lg bg-gray-100 font-medium">
        <span class="ml-2">Sales Reports</span>
      </a>

      <p class="text-xs uppercase text-gray-500 mt-4 mb-2">Inventory Management</p>
      <a href="product_catalog.php" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-100">
        <span class="ml-2">Product Catalog</span>
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
      <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-800">Pos Reports</h2>
        </div>
    </header>

    <section class="bg-white rounded-xl shadow-md p-6">
      <div class="flex items-center justify-between mb-4">
        <div>
          <h3 class="text-xl font-bold text-gray-800">Sales Reports & Analytics</h3>
          <p class="text-sm text-gray-500">Analyze your sales performance and trends</p>
        </div>
        <div class="flex items-center space-x-2">
          <select class="px-3 py-2 text-sm border rounded-lg focus:outline-none">
            <option>Last 7 Days</option>
            <option>Last 30 Days</option>
            <option>Last 90 Days</option>
          </select>
          <button class="px-4 py-2 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
            Export Report
          </button>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white border rounded-xl p-4 flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500">Total Sales</p>
            <h4 class="text-2xl font-bold">$12450.75</h4>
            <p class="text-xs text-green-600">+12.5% from last period</p>
          </div>
          <div class="icon-placeholder"></div>
        </div>
        <div class="bg-white border rounded-xl p-4 flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500">Transactions</p>
            <h4 class="text-2xl font-bold">156</h4>
            <p class="text-xs text-gray-500">completed sales</p>
          </div>
          <div class="icon-placeholder"></div>
        </div>
        <div class="bg-white border rounded-xl p-4 flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500">Avg. Transaction</p>
            <h4 class="text-2xl font-bold">$79.81</h4>
            <p class="text-xs text-gray-500">per sale</p>
          </div>
          <div class="icon-placeholder"></div>
        </div>
        <div class="bg-white border rounded-xl p-4 flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500">Top Customer</p>
            <h4 class="text-xl font-bold">Sarah Wilson</h4>
            <p class="text-xs text-gray-500">highest spender</p>
          </div>
          <div class="icon-placeholder"></div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="bg-white border rounded-xl p-6">
          <h4 class="text-lg font-semibold mb-2">Daily Sales Trend</h4>
          <p class="text-sm text-gray-500 mb-4">Sales performance over the last 7 days</p>
          <div class="h-64 bg-gray-100 rounded-lg flex items-end justify-between p-4">
            <div class="w-1/7 bg-blue-500 h-[30%] rounded-t-lg"></div>
            <div class="w-1/7 bg-blue-500 h-[60%] rounded-t-lg"></div>
            <div class="w-1/7 bg-blue-500 h-[25%] rounded-t-lg"></div>
            <div class="w-1/7 bg-blue-500 h-[80%] rounded-t-lg"></div>
            <div class="w-1/7 bg-blue-500 h-[70%] rounded-t-lg"></div>
            <div class="w-1/7 bg-blue-500 h-[95%] rounded-t-lg"></div>
            <div class="w-1/7 bg-blue-500 h-[50%] rounded-t-lg"></div>
          </div>
          <div class="flex justify-center items-center mt-2 text-xs text-gray-500">
            <div class="flex items-center mr-4"><span class="w-2 h-2 bg-blue-500 rounded-full mr-1"></span> Sales ($)</div>
            <div class="flex items-center"><span class="w-2 h-2 bg-green-500 rounded-full mr-1"></span> Transactions</div>
          </div>
        </div>

        <div class="bg-white border rounded-xl p-6">
          <h4 class="text-lg font-semibold mb-2">Top Selling Products</h4>
          <p class="text-sm text-gray-500 mb-4">Most popular items by quantity sold</p>
          <div class="flex justify-center items-center h-64 relative">
            <div class="w-40 h-40 bg-gray-100 rounded-full"></div>
            <div class="absolute inset-0 flex items-center justify-center">
              <div class="w-24 h-24 bg-white rounded-full"></div>
            </div>
            <div class="absolute bottom-4 left-1/2 -ml-20 flex flex-col text-xs space-y-2">
              <span class="text-blue-500">Wireless Mouse <span class="font-semibold text-gray-800">35%</span></span>
              <span class="text-green-500">USB Cable <span class="font-semibold text-gray-800">28%</span></span>
              <span class="text-red-500">Headphones <span class="font-semibold text-gray-800">22%</span></span>
              <span class="text-yellow-500">Laptop Stand <span class="font-semibold text-gray-800">15%</span></span>
            </div>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mt-8">
        <div class="bg-white border rounded-xl p-6 col-span-1 lg:col-span-2">
          <h4 class="text-lg font-semibold mb-2">Recent Transactions</h4>
          <p class="text-sm text-gray-500 mb-4">Latest sales processed today</p>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transaction ID</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">#1234</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">John Doe</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">$299.99</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">3</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">10:30 AM</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">2024-01-15</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Completed</span></td>
                </tr>
                <tr>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">#1233</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Jane Smith</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">$159.50</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">2</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">10:15 AM</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">2024-01-15</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Completed</span></td>
                </tr>
                <tr>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">#1232</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Mike Johnson</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">$89.99</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">1</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">10:00 AM</td>
                  <td class="px-6 py-4 whitespace-now-1rap text-sm text-gray-500">2024-01-15</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Completed</span></td>
                </tr>
                <tr>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">#1231</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Sarah Wilson</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">$450.75</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">5</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">09:45 AM</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">2024-01-15</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Completed</span></td>
                </tr>
                <tr>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">#1230</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Walk-in Customer</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">$199.99</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">2</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">09:30 AM</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">2024-01-15</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Completed</span></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="bg-white border rounded-xl p-6">
            <h4 class="text-lg font-semibold mb-2">Peak Hours</h4>
            <p class="text-sm text-gray-500 mb-4">Busiest times of the day</p>
            <ul class="space-y-4 text-sm">
              <li class="flex justify-between items-center">
                <span>10:00 AM - 12:00 PM</span>
                <span class="bg-red-500 text-white text-xs px-2 py-0.5 rounded-full">Peak</span>
              </li>
              <li class="flex justify-between items-center">
                <span>2:00 PM - 4:00 PM</span>
                <span class="bg-orange-500 text-white text-xs px-2 py-0.5 rounded-full">High</span>
              </li>
              <li class="flex justify-between items-center">
                <span>6:00 PM - 8:00 PM</span>
                <span class="bg-yellow-500 text-white text-xs px-2 py-0.5 rounded-full">Medium</span>
              </li>
            </ul>
          </div>
          <div class="bg-white border rounded-xl p-6">
            <h4 class="text-lg font-semibold mb-2">Payment Methods</h4>
            <p class="text-sm text-gray-500 mb-4">Preferred payment types</p>
            <ul class="space-y-4 text-sm">
              <li class="flex justify-between items-center">
                <span>Cash</span>
                <span class="font-medium">65%</span>
              </li>
              <li class="flex justify-between items-center">
                <span>Credit Card</span>
                <span class="font-medium">30%</span>
              </li>
              <li class="flex justify-between items-center">
                <span>Mobile Payment</span>
                <span class="font-medium">5%</span>
              </li>
            </ul>
          </div>
        </div>
      </div>

    </section>
  </main>
</body>
</html>