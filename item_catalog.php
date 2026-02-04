<?php
include "database/database.php";
$database->login_session();
$database->create_item();
$database->update_item();
$database->delete_item();

$categories = $database->select_categories();
$suppliers = $database->select_suppliers();

// Pagination settings
$perPage = 5;
$search  = trim($_GET['search'] ?? '');
$categoryFilter = trim($_GET['category'] ?? '');
$priceFilter = trim($_GET['price'] ?? '');
$page    = max(1, (int) ($_GET['page'] ?? 1));

$offset = ($page - 1) * $perPage;

// Get data with filters
$totalItems = $database->getTotalItemsCount($search, $categoryFilter, $priceFilter);
$totalPages = max(1, ceil($totalItems / $perPage));

$items = $database->select_items_paginated($offset, $perPage, $search, $categoryFilter, $priceFilter);
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
      background-color: #d1d5db;
      /* Light gray */
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

   <!-- Mobile Sidebar Toggle Button -->
  <button id="sidebar-toggle" class="md:hidden p-3 fixed top-4 left-4 z-60 text-white rounded shadow-lg"
    style="background-color: rgba(170, 170, 170, 0.82);">
    ☰
  </button>


  <?php include "sidebar.php"; ?>

  <!-- Main Content -->
  <main class="flex-1 ml-0 md:ml-64 p-6">
    <header class="mb-6">
      <h2 class="text-2xl font-bold text-gray-800">Inventory Items</h2>
    </header>

    <section class="bg-white rounded-xl shadow-md p-6">
      <div class="flex items-center justify-between mb-4">
        <div>
          <h3 class="text-xl font-bold text-gray-800">Item Catalog</h3>
          <p class="text-sm text-gray-500">Manage your item inventory and pricing</p>
        </div>

        <div class="flex items-center space-x-2">
          <button id="openAddProductModal" class="px-4 py-2 text-sm bg-black text-white rounded-lg hover:bg-gray-800">
            + Add Item
          </button>
        </div>
      </div>

      <!-- Search and Filters -->
      <form method="GET" action="" class="flex flex-col lg:flex-row items-stretch lg:items-center space-y-3 lg:space-y-0 lg:space-x-4 mb-4">
        <div class="relative flex-1">
          <input 
            type="text" 
            name="search" 
            id="productSearchInput" 
            value="<?= htmlspecialchars($search) ?>"
            placeholder="Search products, barcodes, or supplier..."
            class="w-full px-4 py-2 pl-10 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
          <span class="material-icons absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm">search</span>
        </div>
        
        <select name="category" id="categoryFilter" class="px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
          <option value="">All Categories</option>
          <?php foreach ($categories as $cat): ?>
            <option value="<?= $cat['category_name'] ?>" <?= $categoryFilter === $cat['category_name'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($cat['category_name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
        
        <select name="price" id="itemFilter" class="px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
          <option value="">All Prices</option>
          <option value="below" <?= $priceFilter === 'below' ? 'selected' : '' ?>>₱5,000 Below</option>
          <option value="above" <?= $priceFilter === 'above' ? 'selected' : '' ?>>₱5,000 Above</option>
        </select>

        <?php if ($search !== '' || $categoryFilter !== '' || $priceFilter !== ''): ?>
          <a href="?" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 whitespace-nowrap text-center">
            Clear
          </a>
        <?php endif; ?>
      </form>

      <?php if ($search !== '' || $categoryFilter !== '' || $priceFilter !== ''): ?>
        <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-800">
          Showing filtered results (<?= $totalItems ?> item<?= $totalItems !== 1 ? 's' : '' ?>)
          <?php if ($search !== ''): ?>
            - Search: "<strong><?= htmlspecialchars($search) ?></strong>"
          <?php endif; ?>
          <?php if ($categoryFilter !== ''): ?>
            - Category: "<strong><?= htmlspecialchars($categoryFilter) ?></strong>"
          <?php endif; ?>
          <?php if ($priceFilter !== ''): ?>
            - Price: <strong><?= $priceFilter === 'below' ? '₱5,000 Below' : '₱5,000 Above' ?></strong>
          <?php endif; ?>
        </div>
      <?php endif; ?>

      <div class="bg-white border rounded-xl p-6">
        <h4 class="text-lg font-semibold mb-2">Item Inventory</h4>
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

        <p class="text-sm text-gray-500 mb-4">List of Inventory Items</p>

        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Item</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Category</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Supplier</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Pricing</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Stock</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Status</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <?php if (!empty($items)): ?>
                <?php foreach ($items as $item): ?>
                  <?php
                  if ($item['quantity'] <= 0) continue;
                  $stock_status = ($item['quantity'] <= $item['min_stock']) ? 'low' : 'in';
                  ?>
                  <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                      <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($item['item_name']) ?></p>
                      <p class="text-xs text-gray-500"><?= htmlspecialchars($item['barcode']) ?></p>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($item['category_name'] ?? 'N/A') ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($item['supplier_name'] ?? 'N/A') ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      <p>Cost: <span class="text-sm text-gray-800">₱<?= number_format($item['cost_price'], 2) ?></span></p>
                      <p>Sell: <span class="text-gray-900 font-medium">₱<?= number_format($item['selling_price'], 2) ?></span></p>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      <p class="text-gray-900 font-medium"><?= $item['quantity'] ?></p>
                      <p class="text-xs">Min: <?= $item['min_stock'] ?></p>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <?php if ($stock_status === 'low'): ?>
                        <span class="status-label bg-red-100 text-red-800">Low Stock</span>
                      <?php else: ?>
                        <span class="status-label bg-green-100 text-green-800">In Stock</span>
                      <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                      <div class="flex space-x-2">
                        <button class="text-green-400 hover:text-green-600 openEditProductModal"
                          data-id="<?= $item['item_id'] ?>" 
                          data-name="<?= htmlspecialchars($item['item_name']) ?>"
                          data-barcode="<?= htmlspecialchars($item['barcode']) ?>"
                          data-description="<?= htmlspecialchars($item['description'] ?? '') ?>"
                          data-category="<?= $item['category_id'] ?>" 
                          data-supplier="<?= $item['supplier_id'] ?>"
                          data-cost="<?= $item['cost_price'] ?>" 
                          data-sell="<?= $item['selling_price'] ?>"
                          data-qty="<?= $item['quantity'] ?>" 
                          data-min="<?= $item['min_stock'] ?>"
                          title="Edit Item">
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" />
                            <path fill-rule="evenodd"
                              d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"
                              clip-rule="evenodd" />
                          </svg>
                        </button>
                        <button class="text-red-500 hover:text-red-700 openDeleteProductModal"
                          data-id="<?= $item['item_id'] ?>" 
                          data-name="<?= htmlspecialchars($item['item_name']) ?>"
                          title="Delete Item">
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                              d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                              clip-rule="evenodd" />
                          </svg>
                        </button>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="7" class="px-6 py-10 text-center text-gray-500">
                    No items found matching your filters.
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
              // Build query parameters for pagination links
              $params = [];
              if ($search !== '') $params[] = 'search=' . urlencode($search);
              if ($categoryFilter !== '') $params[] = 'category=' . urlencode($categoryFilter);
              if ($priceFilter !== '') $params[] = 'price=' . urlencode($priceFilter);
              $queryString = !empty($params) ? '&' . implode('&', $params) : '';
              ?>

              <!-- Previous -->
              <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?><?= $queryString ?>"
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
                <a href="?page=1<?= $queryString ?>"
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
                  <a href="?page=<?= $i ?><?= $queryString ?>"
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
                <a href="?page=<?= $totalPages ?><?= $queryString ?>"
                    class="px-3 py-2 rounded-md text-sm font-medium border border-gray-300 hover:bg-gray-50">
                    <?= $totalPages ?>
                </a>
              <?php endif; ?>

              <!-- Next -->
              <?php if ($page < $totalPages): ?>
                <a href="?page=<?= $page + 1 ?><?= $queryString ?>"
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
    </section>
  </main>

  <!-- Add Item Modal -->
  <div id="addProductModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-white rounded-lg w-full max-w-xl p-6 shadow-lg">
      <div class="flex justify-between items-center mb-4">
        <div>
          <h3 class="text-lg font-semibold text-gray-900">Add New Item</h3>
          <p class="text-sm text-gray-500">Enter item details to add to your catalog</p>
        </div>
        <button id="closeAddProductModal" class="text-gray-500 hover:text-gray-700 text-xl leading-none">
          &times;
        </button>
      </div>

      <form method="POST" class="space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">Item Name *</label>
            <input type="text" name="item_name" required placeholder="Enter item name"
              class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200">
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Barcode *</label>
            <input type="text" name="barcode" required placeholder="Scan or enter barcode"
              class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200">
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Description</label>
          <textarea name="description" placeholder="Product description"
            class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200 resize-none"></textarea>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">Category</label>
            <select name="category_id"
              class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200">
              <option value="">Select category</option>
              <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['category_id'] ?>"><?= htmlspecialchars($cat['category_name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Supplier</label>
            <select name="supplier_id"
              class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200">
              <option value="">Select supplier</option>
              <?php foreach ($suppliers as $sp): ?>
                <option value="<?= $sp['supplier_id'] ?>"><?= htmlspecialchars($sp['supplier_name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">Cost Price</label>
            <input type="number" step="0.01" name="cost_price" value="0.00"
              class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Selling Price *</label>
            <input type="number" step="0.01" name="selling_price" value="0.00" required
              class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200">
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">Current Stock</label>
            <input type="number" name="quantity" value="0"
              class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Minimum Stock</label>
            <input type="number" name="min_stock" value="0"
              class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200">
          </div>
        </div>

        <div class="flex justify-end space-x-2 pt-4">
          <button type="button" id="cancelAddProductModal"
            class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">Cancel</button>
          <button name="create_item" type="submit" class="px-4 py-2 bg-black text-white rounded hover:bg-gray-800">Add
            Product</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Edit Product Modal -->
  <div id="editProductModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-white rounded-lg w-full max-w-xl p-6 shadow-lg">
      <div class="flex justify-between items-center mb-4">
        <div>
          <h3 class="text-lg font-semibold text-gray-900">Edit Product</h3>
          <p class="text-sm text-gray-500">Update product details</p>
        </div>
        <button id="closeEditProductModal" class="text-gray-500 hover:text-gray-700 text-xl leading-none">
          &times;
        </button>
      </div>

      <form method="POST" action="<?php $_SERVER['PHP_SELF']; ?>" class="space-y-4">
        <input type="hidden" name="item_id" id="edit_item_id">

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">Item Name *</label>
            <input type="text" name="item_name" id="edit_product_name" required
              class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200">
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Barcode *</label>
            <input type="text" name="barcode" id="edit_barcode" required
              class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200">
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Description</label>
          <textarea name="description" id="edit_description"
            class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200 resize-none"></textarea>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">Category</label>
            <select name="category_id" id="edit_category_id"
              class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200">
              <option value="">Select category</option>
              <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['category_id'] ?>"><?= htmlspecialchars($cat['category_name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Supplier</label>
            <select name="supplier_id" id="edit_supplier_id"
              class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200">
              <option value="">Select supplier</option>
              <?php foreach ($suppliers as $sp): ?>
                <option value="<?= $sp['supplier_id'] ?>"><?= htmlspecialchars($sp['supplier_name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">Cost Price</label>
            <input type="number" step="0.01" name="cost_price" id="edit_cost_price"
              class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Selling Price *</label>
            <input type="number" step="0.01" name="selling_price" id="edit_selling_price" required
              class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200">
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">Current Stock</label>
            <input type="number" name="quantity" id="edit_quantity"
              class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Minimum Stock</label>
            <input type="number" name="min_stock" id="edit_min_stock"
              class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200">
          </div>
        </div>

        <div class="flex justify-end space-x-2 pt-4">
          <button type="button" id="cancelEditProductModal"
            class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">Cancel</button>
          <button name="update_item" type="submit" class="px-4 py-2 bg-black text-white rounded hover:bg-gray-800">Save
            Changes</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Delete Product Modal -->
  <div id="deleteProductModal"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-white rounded-lg w-full max-w-md p-6 shadow-lg">
      <h3 class="text-lg font-semibold text-gray-900 mb-2">Delete Item</h3>
      <p class="text-sm text-gray-700 mb-4">Are you sure you want to delete <span id="delete_product_name"
          class="font-medium text-red-600"></span>?</p>

      <form method="POST" action="<?php $_SERVER['PHP_SELF']; ?>" class="flex justify-end space-x-2">
        <input type="hidden" name="delete_item_id" id="delete_item_id">

        <button type="button" id="cancelDeleteProductModal"
          class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">Cancel</button>
        <button type="submit" name="delete_item"
          class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Delete</button>
      </form>
    </div>
  </div>

  <!-- Auto-submit search form after user stops typing -->
<script>
  let searchTimeout;
  const searchInput = document.getElementById('productSearchInput');
  const searchForm = searchInput.closest('form');

  if (searchInput) {
    searchInput.addEventListener('input', function() {
      clearTimeout(searchTimeout);
      searchTimeout = setTimeout(() => {
        searchForm.submit();
      }, 500); // Wait 500ms after user stops typing
    });
  }

  // Also auto-submit when changing filters
  const categoryFilter = document.getElementById('categoryFilter');
  const itemFilter = document.getElementById('itemFilter');

  if (categoryFilter) {
    categoryFilter.addEventListener('change', function() {
      searchForm.submit();
    });
  }

  if (itemFilter) {
    itemFilter.addEventListener('change', function() {
      searchForm.submit();
    });
  }
</script>
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

  <!-- Add Product Script -->
  <script>
    const openProductBtn = document.getElementById('openAddProductModal');
    const closeProductBtn = document.getElementById('closeAddProductModal');
    const cancelProductBtn = document.getElementById('cancelAddProductModal');
    const productModal = document.getElementById('addProductModal');

    openProductBtn.addEventListener('click', () => productModal.classList.remove('hidden'));
    closeProductBtn.addEventListener('click', () => productModal.classList.add('hidden'));
    cancelProductBtn.addEventListener('click', () => productModal.classList.add('hidden'));

    // Optional: Close modal when clicking outside
    window.addEventListener('click', (e) => {
      if (e.target === productModal) {
        productModal.classList.add('hidden');
      }
    });
  </script>

  <!-- Edit Product Script -->
  <script>
    const editButtons = document.querySelectorAll('.openEditProductModal');
    const editModal = document.getElementById('editProductModal');
    const closeModalBtn = document.getElementById('closeEditProductModal');
    const cancelModalBtn = document.getElementById('cancelEditProductModal');

    editButtons.forEach(button => {
      button.addEventListener('click', () => {
        document.getElementById('edit_item_id').value = button.dataset.id;
        document.getElementById('edit_product_name').value = button.dataset.name;
        document.getElementById('edit_barcode').value = button.dataset.barcode;
        document.getElementById('edit_description').value = button.dataset.description || '';
        document.getElementById('edit_category_id').value = button.dataset.category;
        document.getElementById('edit_supplier_id').value = button.dataset.supplier;
        document.getElementById('edit_cost_price').value = button.dataset.cost;
        document.getElementById('edit_selling_price').value = button.dataset.sell;
        document.getElementById('edit_quantity').value = button.dataset.qty;
        document.getElementById('edit_min_stock').value = button.dataset.min;

        editModal.classList.remove('hidden');
      });
    });

    closeModalBtn.addEventListener('click', () => {
      editModal.classList.add('hidden');
    });

    cancelModalBtn.addEventListener('click', () => {
      editModal.classList.add('hidden');
    });

    // Optional: Click outside to close
    window.addEventListener('click', e => {
      if (e.target === editModal) {
        editModal.classList.add('hidden');
      }
    });
  </script>

  <!-- Delete Product Script -->
  <script>
    const deleteButtons = document.querySelectorAll('.openDeleteProductModal');
    const deleteModal = document.getElementById('deleteProductModal');
    const cancelDeleteModal = document.getElementById('cancelDeleteProductModal');

    deleteButtons.forEach(button => {
      button.addEventListener('click', () => {
        const id = button.dataset.id;
        const name = button.dataset.name;

        document.getElementById('delete_item_id').value = id;
        document.getElementById('delete_product_name').textContent = name;

        deleteModal.classList.remove('hidden');
      });
    });

    cancelDeleteModal.addEventListener('click', () => {
      deleteModal.classList.add('hidden');
    });

    // Optional: click outside to close
    window.addEventListener('click', (e) => {
      if (e.target === deleteModal) {
        deleteModal.classList.add('hidden');
      }
    });
  </script>

</body>

</html>