<?php
  include "database/database.php";
  $database->login_session();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>POS & Inventory - Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 flex">

  <!-- Sidebar -->
  <aside class="sticky top-0 w-64 h-screen bg-white border-r flex flex-col">
    <div class="p-6 border-b">
      <h1 class="text-lg font-bold">POS & Inventory</h1>
      <p class="text-xs text-gray-500">Management System</p>
    </div>
    <nav class="flex-1 p-4 space-y-2">
      <a href="dashboard.php" class="flex items-center px-3 py-2 rounded-lg bg-gray-100 font-medium">
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

  <!-- Main Content -->
  <main class="flex-1 p-6">
    <!-- Header -->
    <header class="mb-6">
      <h2 class="text-2xl font-bold">Dashboard</h2>
      <p class="text-gray-500">Welcome back, <?php echo $_SESSION['login-success']; ?>! Here's an overview of your business today.</p>
    </header>

    <!-- POS Overview -->
    <section>
      <h3 class="text-lg font-semibold mb-4">Point of Sale Overview</h3>
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow p-4">
          <p class="text-gray-500 text-sm">Today's Sales</p>
          <h4 class="text-2xl font-bold">12</h4>
          <p class="text-xs text-gray-500">transactions</p>
        </div>
        <div class="bg-white rounded-xl shadow p-4">
          <p class="text-gray-500 text-sm">Today's Revenue</p>
          <h4 class="text-2xl font-bold">$2847.50</h4>
          <p class="text-xs text-green-600">+12% from yesterday</p>
        </div>
        <div class="bg-white rounded-xl shadow p-4">
          <p class="text-gray-500 text-sm">Avg. Transaction</p>
          <h4 class="text-2xl font-bold">$237.29</h4>
          <p class="text-xs text-gray-500">per sale</p>
        </div>
        <div class="bg-white rounded-xl shadow p-4">
          <p class="text-gray-500 text-sm">Total Customers</p>
          <h4 class="text-2xl font-bold">156</h4>
          <p class="text-xs text-gray-500">registered</p>
        </div>
      </div>
    </section>

    <!-- Inventory Overview -->
    <section class="mt-8">
      <h3 class="text-lg font-semibold mb-4">Inventory Overview</h3>
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow p-4">
          <p class="text-gray-500 text-sm">Total Products</p>
          <h4 class="text-2xl font-bold">342</h4>
          <p class="text-xs text-gray-500">in catalog</p>
        </div>
        <div class="bg-white rounded-xl shadow p-4">
          <p class="text-gray-500 text-sm">Low Stock Alert</p>
          <h4 class="text-2xl font-bold text-red-600">8</h4>
          <p class="text-xs text-gray-500">items need reorder</p>
        </div>
        <div class="bg-white rounded-xl shadow p-4">
          <p class="text-gray-500 text-sm">Inventory Value</p>
          <h4 class="text-2xl font-bold">$45623.80</h4>
          <p class="text-xs text-gray-500">total value</p>
        </div>
        <div class="bg-white rounded-xl shadow p-4">
          <p class="text-gray-500 text-sm">Pending Orders</p>
          <h4 class="text-2xl font-bold">3</h4>
          <p class="text-xs text-gray-500">purchase orders</p>
        </div>
      </div>
    </section>

    <!-- Bottom Row -->
    <section class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-4">
      <!-- Recent Sales -->
      <div class="bg-white rounded-xl shadow p-4">
        <h4 class="font-semibold">Recent Sales</h4>
        <p class="text-sm text-gray-500 mb-4">Latest transactions processed</p>
        <ul class="space-y-3 text-sm">
          <li class="flex justify-between">
            <span>#1234 John Doe</span>
            <span class="font-medium">$299.99</span>
          </li>
          <li class="flex justify-between">
            <span>#1233 Jane Smith</span>
            <span class="font-medium">$159.50</span>
          </li>
          <li class="flex justify-between">
            <span>#1232 Mike Johnson</span>
            <span class="font-medium">$89.99</span>
          </li>
        </ul>
      </div>

      <!-- Low Stock Items -->
      <div class="bg-white rounded-xl shadow p-4">
        <div class="flex justify-between items-center mb-2">
          <h4 class="font-semibold">Low Stock Items</h4>
          <span class="bg-red-500 text-white text-xs rounded-full px-2 py-0.5">3</span>
        </div>
        <p class="text-sm text-gray-500 mb-4">Items that need immediate attention</p>
        <ul class="space-y-3 text-sm">
          <li class="flex justify-between items-center">
            <span>USB Cable - Type C<br><span class="text-xs text-gray-500">Current: 2 | Min: 10</span></span>
            <span class="bg-red-100 text-red-600 text-xs px-2 py-0.5 rounded">Low Stock</span>
          </li>
          <li class="flex justify-between items-center">
            <span>Wireless Mouse<br><span class="text-xs text-gray-500">Current: 1 | Min: 5</span></span>
            <span class="bg-red-100 text-red-600 text-xs px-2 py-0.5 rounded">Low Stock</span>
          </li>
          <li class="flex justify-between items-center">
            <span>HDMI Cable<br><span class="text-xs text-gray-500">Current: 3 | Min: 8</span></span>
            <span class="bg-red-100 text-red-600 text-xs px-2 py-0.5 rounded">Low Stock</span>
          </li>
        </ul>
      </div>
    </section>
  </main>

</body>
</html>
