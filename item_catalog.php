<?php
include "database/database.php";
$database->login_session();
$database->create_category();
$database->create_item();
$database->update_item();
$database->delete_item();
$categories = $database->select_categories();
$suppliers = $database->select_suppliers();
$items = $database->select_items();
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
          <button id="openAddCategoryModal" class="px-4 py-2 text-sm bg-black text-white rounded-lg hover:bg-gray-800">
            + Add Category
          </button>
        </div>
      </div>

      <div class="flex items-center space-x-4 mb-4">
        <div class="relative flex-1">
          <input type="text" id="productSearchInput" placeholder="Search products, barcodes, or supplier..."
            class="w-full px-4 py-2 border rounded-lg focus:outline-none">
        </div>
        <select id="categoryFilter" class="px-4 py-2 border rounded-lg focus:outline-none">
          <option value="">All Categories</option>
          <?php foreach($categories as $cat) : ?>
          <option><?php echo $cat['category_name']; ?></option>
          <?php endforeach; ?>
        </select>
         <select id="itemFilter" class="px-4 py-2 border rounded-lg focus:outline-none">
          <option value="">Sell Price Filter</option>
          <option value="below">₱5,000 Below</option>
          <option value="above">₱5,000 Above</option>
        </select>
      </div>

      <div class="bg-white border rounded-xl p-6">
        <h4 class="text-lg font-semibold mb-2">Item Inventory</h4>
        <?php 
          if(isset($_SESSION['create-success'])){ 
            $success = $_SESSION['create-success'];
            ?>
            <div id="successAlert" class="mb-4 px-4 py-3 bg-green-100 border border-green-400 text-green-700 text-sm rounded-lg">
              <?php echo $success ?>
            </div>
          <?php 
          } ?>

          <?php 
          if(isset($_SESSION['create-error'])){ 
            $error = $_SESSION['create-error'];
            ?>
            <div id="errorAlert" class="mb-4 px-4 py-3 bg-red-100 border border-red-400 text-red-700 text-sm rounded-lg">
              <?php echo $error ?>
            </div>
          <?php 
          } ?>
        <p class="text-sm text-gray-500 mb-4">5 items found</p>

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
            <?php foreach($items as $item) : ?>
              <tr>
                <td class="px-6 py-4 whitespace-nowrap">
                  <p class="text-sm font-medium text-gray-900"><?php echo $item['item_name']; ?></p>
                  <p class="text-xs text-gray-500"><?php echo $item['barcode']; ?></p>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $item['category_name']; ?></td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $item['supplier_name']; ?></td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  <p>Cost: <span class="text-sm text-gray-800">₱<?php echo number_format($item['cost_price']); ?></span></p>
                  <p>Sell: <span class="text-gray-900 font-medium">₱<?php echo number_format($item['selling_price']); ?></span></p>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  <p class="text-gray-900 font-medium"><?php echo $item['quantity']; ?></p>
                  <p class="text-xs">Min: <?php echo $item['min_stock'] ?></p>
                </td>
                <?php
                  $status = 1;
                  if($item['quantity'] <= $item['min_stock']){
                    $status = 0;
                  }
                ?>
                <?php if($status == 1) : ?>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="status-label bg-green-100 text-green-800">In Stock</span>
                </td>
                <?php elseif($status == 0) : ?>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="status-label bg-red-100 text-red-800">Low Stock</span>
                </td>
                <?php elseif($status == -0) : ?>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="status-label bg-red-100 text-red-800">Error</span>
                </td>
                <?php endif; ?>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <div class="flex space-x-2">
                    <button class="text-green-400 hover:text-green-600 openEditProductModal" data-id="<?= $item['item_id'] ?>" data-name="<?= htmlspecialchars($item['item_name']) ?>" data-barcode="<?= htmlspecialchars($item['barcode']) ?>" data-description="<?= htmlspecialchars($item['description'] ?? '') ?>" data-category="<?= $item['category_id'] ?>" data-supplier="<?= $item['supplier_id'] ?>" data-cost="<?= $item['cost_price'] ?>" data-sell="<?= $item['selling_price'] ?>" data-qty="<?= $item['quantity'] ?>" data-min="<?= $item['min_stock'] ?>">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" /><path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd" /></svg>
                    </button>
                    <button class="text-red-500 hover:text-red-700 openDeleteProductModal" data-id="<?= $item['item_id'] ?>" data-name="<?= htmlspecialchars($item['item_name']) ?>">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                    </button>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </section>
  </main>

<!-- Add Category Modal -->
<div id="addCategoryModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
  <div class="bg-white rounded-lg w-full max-w-md p-6">
    <div class="flex justify-between items-center mb-4">
      <h3 class="text-lg font-semibold">Add New Category</h3>
      <button id="closeAddCategoryModal" class="text-gray-500 hover:text-gray-700 text-xl leading-none">
        &times;
      </button>
    </div>

    <form method="POST" class="space-y-4">
      <div>
        <label for="category_name" class="block text-sm font-medium text-gray-700">Category Name</label>
        <input type="text" name="category_name" id="category_name" required
          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200">
      </div>

      <div class="flex justify-end space-x-2 pt-4">
        <button type="button" id="cancelCategoryModalBtn" class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">
          Cancel
        </button>
        <button name="create_category" type="submit" class="px-4 py-2 bg-black text-white rounded hover:bg-gray-800">
          Save Category
        </button>
      </div>
    </form>
  </div>
</div>

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
            <?php foreach($categories as $cat) : ?>
            <option value="<?php echo $cat['category_id']; ?>"><?php echo $cat['category_name']; ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Supplier</label>
          <select name="supplier_id"
            class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200">
            <option value="">Select supplier</option>
            <?php foreach($suppliers as $sp) : ?>
            <option value="<?php echo $sp['supplier_id']; ?>"><?php echo $sp['supplier_name']; ?></option>
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
        <button name="create_item" type="submit"
          class="px-4 py-2 bg-black text-white rounded hover:bg-gray-800">Add Product</button>
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
            <?php foreach($categories as $cat) : ?>
              <option value="<?= $cat['category_id']; ?>"><?= $cat['category_name']; ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Supplier</label>
          <select name="supplier_id" id="edit_supplier_id"
            class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200">
            <option value="">Select supplier</option>
            <?php foreach($suppliers as $sp) : ?>
              <option value="<?= $sp['supplier_id']; ?>"><?= $sp['supplier_name']; ?></option>
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
        <button name="update_item" type="submit"
          class="px-4 py-2 bg-black text-white rounded hover:bg-gray-800">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<!-- Delete Product Modal -->
<div id="deleteProductModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
  <div class="bg-white rounded-lg w-full max-w-md p-6 shadow-lg">
    <h3 class="text-lg font-semibold text-gray-900 mb-2">Delete Item</h3>
    <p class="text-sm text-gray-700 mb-4">Are you sure you want to delete <span id="delete_product_name" class="font-medium text-red-600"></span>?</p>

    <form method="POST" action="<?php $_SERVER['PHP_SELF']; ?>" class="flex justify-end space-x-2">
      <input type="hidden" name="delete_item_id" id="delete_item_id">

      <button type="button" id="cancelDeleteProductModal" class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">Cancel</button>
      <button type="submit" name="delete_item" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Delete</button>
    </form>
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


<!-- Add Category Script -->
<script>
  const openCategoryBtn = document.getElementById('openAddCategoryModal');
  const closeCategoryBtn = document.getElementById('closeAddCategoryModal');
  const cancelCategoryBtn = document.getElementById('cancelCategoryModalBtn');
  const categoryModal = document.getElementById('addCategoryModal');

  openCategoryBtn.addEventListener('click', () => {
    categoryModal.classList.remove('hidden');
  });

  closeCategoryBtn.addEventListener('click', () => {
    categoryModal.classList.add('hidden');
  });

  cancelCategoryBtn.addEventListener('click', () => {
    categoryModal.classList.add('hidden');
  });

  // Optional: close when clicking outside
  window.addEventListener('click', (e) => {
    if (e.target === categoryModal) {
      categoryModal.classList.add('hidden');
    }
  });
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

<!-- Search bar Script -->
<script>
  const productSearchInput = document.getElementById('productSearchInput');
  const categoryFilter = document.getElementById('categoryFilter');
  const itemFilter = document.getElementById('itemFilter');

  function filterTable() {
    const searchValue = productSearchInput.value.toLowerCase();
    const selectedCategory = categoryFilter.value.toLowerCase();
    const selectedPriceFilter = itemFilter.value;
    const tableRows = document.querySelectorAll('tbody tr');

    tableRows.forEach(row => {
      const productName = row.querySelector('td:nth-child(1) p:nth-child(1)').textContent.toLowerCase();
      const barcode = row.querySelector('td:nth-child(1) p:nth-child(2)').textContent.toLowerCase();
      const supplier = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
      const category = row.querySelector('td:nth-child(2)').textContent.toLowerCase();

      // Extract selling price as number
      let sellingPriceText = row.querySelector('td:nth-child(4) p:nth-child(2)').textContent;
      let sellingPrice = Number(sellingPriceText.replace(/[^0-9.-]+/g,""));

      const matchesSearch = productName.includes(searchValue) || barcode.includes(searchValue) || supplier.includes(searchValue);
      const matchesCategory = selectedCategory === "" || category === selectedCategory;

      let matchesPrice = true;
      if(selectedPriceFilter === 'below') {
        matchesPrice = sellingPrice <= 5000;
      } else if(selectedPriceFilter === 'above') {
        matchesPrice = sellingPrice > 5000;
      }

      row.style.display = (matchesSearch && matchesCategory && matchesPrice) ? '' : 'none';
    });
  }

  productSearchInput.addEventListener('input', filterTable);
  categoryFilter.addEventListener('change', filterTable);
  itemFilter.addEventListener('change', filterTable);
</script>

</body>
</html>