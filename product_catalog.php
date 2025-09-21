<?php
  include "database/database.php";
  $database->login_session();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>POS & Inventory - Product Catalog</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <style>
    /* Custom styles for icon placeholders */
    .icon-placeholder {
      width: 20px;
      height: 20px;
      background-color: #d1d5db; /* Light gray */
      border-radius: 4px;
      display: flex;
      justify-content: center;
      align-items: center;
      font-size: 12px;
      color: #374151;
    }
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
      <a href="product_catalog.php" class="flex items-center px-3 py-2 rounded-lg bg-gray-100 font-medium">
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
      <h2 class="text-2xl font-bold text-gray-800">Inventory Products</h2>
    </header>

    <section class="bg-white rounded-xl shadow-md p-6">
      <div class="flex items-center justify-between mb-4">
        <div>
          <h3 class="text-xl font-bold text-gray-800">Product Catalog</h3>
          <p class="text-sm text-gray-500">Manage your product inventory and pricing</p>
        </div>
        <div class="flex items-center space-x-2">
          <button class="px-4 py-2 text-sm bg-black text-white rounded-lg hover:bg-gray-800">
            + Add Product
          </button>
        </div>
      </div>

      <div class="flex items-center space-x-4 mb-4">
        <div class="relative flex-1">
          <input type="text" placeholder="Search products, barcodes, or descriptions..." class="w-full px-4 py-2 border rounded-lg focus:outline-none">
        </div>
        <select class="px-4 py-2 border rounded-lg focus:outline-none">
          <option>All Categories</option>
          <option>Peripherals</option>
          <option>Cables</option>
          <option>Accessories</option>
        </select>
      </div>

      <div class="bg-white border rounded-xl p-6">
        <h4 class="text-lg font-semibold mb-2">Product Inventory</h4>
        <p class="text-sm text-gray-500 mb-4">5 products found</p>

        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pricing</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr>
                <td class="px-6 py-4 whitespace-nowrap">
                  <p class="text-sm font-medium text-gray-900">Wireless Mouse</p>
                  <p class="text-xs text-gray-500">123456789</p>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Peripherals</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">TechCorp</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  <p>Cost: <span class="text-gray-900 font-medium">$20.00</span></p>
                  <p>Sell: <span class="text-gray-900 font-medium">$29.99</span></p>
                  <p>Margin: 33.3%</p>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  <p class="text-gray-900 font-medium">15</p>
                  <p class="text-xs">Min: 10</p>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="status-label bg-green-100 text-green-800">In Stock</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <div class="flex space-x-2">
                    <button class="text-gray-400 hover:text-gray-600">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" />
                        <path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd" />
                      </svg>
                    </button>
                    <button class="text-gray-400 hover:text-gray-600">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
              <tr>
                <td class="px-6 py-4 whitespace-nowrap">
                  <p class="text-sm font-medium text-gray-900">USB Cable - Type C</p>
                  <p class="text-xs text-gray-500">234567890</p>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Cables</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">ElectroSupply</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  <p>Cost: <span class="text-gray-900 font-medium">$8.00</span></p>
                  <p>Sell: <span class="text-gray-900 font-medium">$19.99</span></p>
                  <p>Margin: 60.0%</p>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  <p class="text-gray-900 font-medium">5</p>
                  <p class="text-xs">Min: 20</p>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="status-label bg-red-100 text-red-800">Low Stock</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <div class="flex space-x-2">
                    <button class="text-gray-400 hover:text-gray-600">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" />
                        <path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd" />
                      </svg>
                    </button>
                    <button class="text-gray-400 hover:text-gray-600">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
              <tr>
                <td class="px-6 py-4 whitespace-nowrap">
                  <p class="text-sm font-medium text-gray-900">Bluetooth Headphones</p>
                  <p class="text-xs text-gray-500">345678901</p>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Peripherals</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">GadgetHub</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  <p>Cost: <span class="text-gray-900 font-medium">$50.00</span></p>
                  <p>Sell: <span class="text-gray-900 font-medium">$89.99</span></p>
                  <p>Margin: 33.3%</p>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  <p class="text-gray-900 font-medium">8</p>
                  <p class="text-xs">Min: 5</p>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="status-label bg-green-100 text-green-800">In Stock</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <div class="flex space-x-2">
                    <button class="text-gray-400 hover:text-gray-600">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" />
                        <path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd" />
                      </svg>
                    </button>
                    <button class="text-gray-400 hover:text-gray-600">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
              <tr>
                <td class="px-6 py-4 whitespace-nowrap">
                  <p class="text-sm font-medium text-gray-900">Laptop Stand</p>
                  <p class="text-xs text-gray-500">456789012</p>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Accessories</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">TechCorp</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  <p>Cost: <span class="text-gray-900 font-medium">$25.00</span></p>
                  <p>Sell: <span class="text-gray-900 font-medium">$45.99</span></p>
                  <p>Margin: 45.4%</p>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  <p class="text-gray-900 font-medium">12</p>
                  <p class="text-xs">Min: 8</p>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="status-label bg-green-100 text-green-800">In Stock</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <div class="flex space-x-2">
                    <button class="text-gray-400 hover:text-gray-600">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" />
                        <path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd" />
                      </svg>
                    </button>
                    <button class="text-gray-400 hover:text-gray-600">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
              <tr>
                <td class="px-6 py-4 whitespace-nowrap">
                  <p class="text-sm font-medium text-gray-900">Mechanical Keyboard</p>
                  <p class="text-xs text-gray-500">567890123</p>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Peripherals</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">GadgetHub</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  <p>Cost: <span class="text-gray-900 font-medium">$80.00</span></p>
                  <p>Sell: <span class="text-gray-900 font-medium">$129.99</span></p>
                  <p>Margin: 38.5%</p>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  <p class="text-gray-900 font-medium">6</p>
                  <p class="text-xs">Min: 5</p>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="status-label bg-green-100 text-green-800">In Stock</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <div class="flex space-x-2">
                    <button class="text-gray-400 hover:text-gray-600">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" />
                        <path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd" />
                      </svg>
                    </button>
                    <button class="text-gray-400 hover:text-gray-600">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                      </svg>
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