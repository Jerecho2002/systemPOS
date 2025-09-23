<?php
  include "database/database.php";
  $database->login_session();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>POS & Inventory - Process Sales</title>
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
      <a href="dashboard.php" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-100">
        <span class="ml-2">Dashboard</span>
      </a>
      <p class="text-xs uppercase text-gray-500 mt-4 mb-2">Point of Sale (POS)</p>
      <a href="process_sales.php" class="flex items-center px-3 py-2 rounded-lg bg-gray-100 font-medium">
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
      <h2 class="text-2xl font-bold">Process Sales</h2>
      <p class="text-gray-500">Scan or search products to add to cart and process sales</p>
    </header>

    <!-- Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Left Side -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Product Scanner -->
        <div class="bg-white rounded-xl shadow p-4">
          <h3 class="font-semibold text-sm mb-2">Product Scanner</h3>
          <div class="flex">
            <input type="text" placeholder="Scan barcode or enter product name..." class="flex-1 border rounded-l-md px-3 py-2 focus:outline-none text-sm">
            <button class="bg-gray-900 text-white px-4 rounded-r-md">🔍</button>
          </div>
        </div>

        <!-- Quick Add Products -->
        <div class="bg-white rounded-xl shadow p-4">
          <h3 class="font-semibold text-sm mb-2">Quick Add Products</h3>
          <p class="text-xs text-gray-500 mb-4">Click products to add to cart</p>
          <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            <div class="p-4 border rounded-lg hover:shadow cursor-pointer">
              <p class="font-medium">Wireless Mouse</p>
              <p class="text-sm text-gray-500">$29.99</p>
              <span class="mt-2 inline-block text-xs bg-gray-100 text-gray-700 px-2 py-0.5 rounded">Stock: 15</span>
            </div>
            <div class="p-4 border rounded-lg hover:shadow cursor-pointer">
              <p class="font-medium">USB Cable - Type C</p>
              <p class="text-sm text-gray-500">$19.99</p>
              <span class="mt-2 inline-block text-xs bg-red-100 text-red-700 px-2 py-0.5 rounded">Stock: 5</span>
            </div>
            <div class="p-4 border rounded-lg hover:shadow cursor-pointer">
              <p class="font-medium">Bluetooth Headphones</p>
              <p class="text-sm text-gray-500">$89.99</p>
              <span class="mt-2 inline-block text-xs bg-gray-100 text-gray-700 px-2 py-0.5 rounded">Stock: 8</span>
            </div>
            <div class="p-4 border rounded-lg hover:shadow cursor-pointer">
              <p class="font-medium">Laptop Stand</p>
              <p class="text-sm text-gray-500">$45.99</p>
              <span class="mt-2 inline-block text-xs bg-gray-100 text-gray-700 px-2 py-0.5 rounded">Stock: 12</span>
            </div>
          </div>
        </div>

        <!-- Shopping Cart -->
        <div class="bg-white rounded-xl shadow p-4">
          <h3 class="font-semibold text-sm mb-2">Shopping Cart</h3>
          <p class="text-center text-gray-400 text-sm py-6">Cart is empty</p>
        </div>
      </div>

      <!-- Right Side -->
      <div class="space-y-6">
        <!-- Customer -->
        <div class="bg-white rounded-xl shadow p-4">
          <h3 class="font-semibold text-sm mb-2">Customer (Optional)</h3>
          <select class="w-full border rounded-md px-3 py-2 text-sm text-gray-500">
            <option>Select customer</option>
          </select>
        </div>

        <!-- Order Summary -->
        <div class="bg-white rounded-xl shadow p-4">
          <h3 class="font-semibold text-sm mb-4">Order Summary</h3>
          <div class="space-y-2 text-sm">
            <div class="flex justify-between"><span>Subtotal:</span><span>$0.00</span></div>
            <div class="flex justify-between"><span>Tax (10%):</span><span>$0.00</span></div>
            <div class="flex justify-between font-semibold"><span>Total:</span><span>$0.00</span></div>
          </div>
          <div class="mt-4">
            <label class="block text-xs text-gray-500 mb-1">Cash Received</label>
            <input type="text" value="0.00" class="w-full border rounded-md px-3 py-2 text-sm bg-gray-50">
          </div>
          <button class="w-full mt-4 bg-gray-400 text-white py-2 rounded-md">Process Sale</button>
        </div>
      </div>
    </div>
  </main>

</body>
</html>
