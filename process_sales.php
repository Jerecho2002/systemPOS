<?php
include "database/database.php";
$database->login_session();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';

  switch ($action) {
    case 'add_to_cart':
      $database->add_to_cart();
      break;

    case 'process_sale':
      $database->process_sale();
      break;

    case 'remove_item':
      $database->remove_from_cart();
      break;

    case 'remove_all':
      unset($_SESSION['cart']);
      $_SESSION['sale-success'] = "All items removed from cart.";
      break;

    default:
      // no action or unknown
      break;
  }
}

$items = $database->select_items();
$cart = $_SESSION['cart'] ?? [];

$subtotal = 0;
foreach ($cart as $item) {
  $subtotal += $item['line_total'];
}
$grand_total = $subtotal;
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
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24"
          stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
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
            <input type="text" id="productSearchInput" placeholder="Scan barcode or enter product name..."
              class="flex-1 border rounded-md px-3 py-2 focus:outline-none text-sm">
          </div>
        </div>

        <!-- Quick Add Products -->
        <div class="bg-white rounded-xl shadow p-4">
          <h3 class="font-semibold text-sm mb-2">Quick Add Products</h3>

          <?php if (isset($_SESSION['sale-success'])): ?>
            <div id="successAlert"
              class="mb-4 px-4 py-3 bg-green-100 border border-green-400 text-green-700 text-sm rounded-lg">
              <?= $_SESSION['sale-success'];
              unset($_SESSION['sale-success']); ?>
            </div>
          <?php endif; ?>

          <?php if (isset($_SESSION['sale-error'])): ?>
            <div id="errorAlert" class="mb-4 px-4 py-3 bg-red-100 border border-red-400 text-red-700 text-sm rounded-lg">
              <?= $_SESSION['sale-error'];
              unset($_SESSION['sale-error']); ?>
            </div>
          <?php endif; ?>

          <p class="text-xs text-gray-500 mb-4">Click products to add to cart</p>

          <!-- Scrollable product grid -->
          <div id="productGrid" class="grid grid-cols-2 md:grid-cols-3 gap-4 max-h-[130px] overflow-y-auto pr-1">

            <?php foreach ($items as $item): ?>
              <?php if ($item['quantity'] < 0)
                continue; ?>
              <button type="button" class="product-item w-full text-left"
                onclick="openQuantityModal(<?= $item['item_id']; ?>, '<?= htmlspecialchars($item['item_name'], ENT_QUOTES); ?>', <?= $item['quantity']; ?>)"
                <?= $item['quantity'] <= 0 ? 'disabled class="opacity-50 cursor-not-allowed"' : '' ?>>
                <div class="p-4 border rounded-lg hover:shadow cursor-pointer">
                  <p class="font-medium product-name"><?= $item['item_name']; ?></p>
                  <p class="text-sm text-gray-700">₱<?= number_format($item['selling_price']); ?></p>
                  <p class="text-xs text-gray-400 product-barcode"><?= $item['barcode']; ?></p>

                  <?php if ($item['quantity'] > 0 && $item['quantity'] <= $item['min_stock']): ?>
                    <span class="mt-2 inline-block text-xs bg-red-100 text-red-700 px-2 py-0.5 rounded">Stock:
                      <?= $item['quantity']; ?></span>
                  <?php elseif ($item['quantity'] <= 0): ?>
                    <span class="mt-2 inline-block text-xs bg-red-100 text-red-700 px-2 py-0.5 rounded">Out of Stock</span>
                  <?php else: ?>
                    <span class="mt-2 inline-block text-xs bg-gray-100 text-gray-700 px-2 py-0.5 rounded">Stock:
                      <?= $item['quantity']; ?></span>
                  <?php endif; ?>
                </div>
              </button>
            <?php endforeach; ?>
          </div>
        </div>

      </div>

      <?php
      $cart = $_SESSION['cart'] ?? [];
      ?>

      <!-- Shopping Cart -->
      <div class="bg-white rounded-xl shadow p-4">
        <h3 class="font-semibold text-sm mb-2">Shopping Cart</h3>
        <?php if (!empty($cart)): ?>
          <!-- Modal Trigger Button -->
          <button type="button" onclick="openRemoveAllModal()"
            class="text-sm text-red-600 hover:text-red-800 font-semibold mb-4">
            Remove All
          </button>
        <?php endif; ?>

        <?php
        if (empty($cart)) {
          echo "<p class='text-center text-gray-400 text-sm py-6'>Cart is empty</p>";
        } else {
          ?>
          <ul class='divide-y text-sm'>
            <?php foreach ($cart as $item): ?>
              <li class='py-2 flex justify-between items-center'>
                <div>
                  <p class='font-medium'><?= htmlspecialchars($item['name']); ?></p>
                  <p class='text-xs text-gray-500'>Qty: <?= $item['quantity']; ?> ×
                    ₱<?= number_format($item['unit_price'], 2); ?></p>
                </div>
                <div class='flex items-center space-x-2'>
                  <p class='font-semibold'>₱<?= number_format($item['line_total'], 2); ?></p>
                  <!-- Modal Trigger Button -->
                  <button type="button"
                    onclick="openRemoveItemModal(<?= $item['item_id']; ?>, '<?= htmlspecialchars($item['name'], ENT_QUOTES); ?>')"
                    class="text-red-500 hover:text-red-700" title="Remove item" aria-label="Remove item">
                    <!-- Trash Can SVG icon -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                      stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round"
                        d="M19 7L5 7M10 11L10 17M14 11L14 17M5 7L6 19C6 20.104 6.896 21 8 21H16C17.104 21 18 20.104 18 19L19 7M9 7V4H15V7" />
                    </svg>
                  </button>
                </div>
              </li>
            <?php endforeach; ?>
          </ul>
          <?php
        }
        ?>
      </div>

    </div>

    <!-- Container: Left (Customer) and Right (Order Summary) Side by Side -->
    <div class="flex space-x-6 mt-3 w-full">

      <!-- Left Side: Customer -->
      <div class="bg-white rounded-xl shadow p-4 flex-1">
        <h3 class="font-semibold text-sm mb-2">Customer (Optional)</h3>
        <input type="text" name="customer" id="customerInput" value="walk in"
          class="w-full border rounded-md px-3 py-2 text-sm text-gray-700" placeholder="Enter customer name">
      </div>

      <!-- Right Side: Order Summary -->
      <div class="bg-white rounded-xl shadow p-4 flex-1">
        <h3 class="font-semibold text-sm mb-4">Order Summary</h3>
        <?php
        $subtotal = 0;
        foreach ($cart as $item) {
          $subtotal += $item['line_total'];
        }
        $grand_total = $subtotal;
        ?>

        <div class="space-y-2 text-sm">
          <div class="flex justify-between">
            <span>Subtotal:</span>
            <span>₱<?= number_format($subtotal, 2); ?></span>
          </div>
          <div class="flex justify-between font-semibold">
            <span>Total:</span>
            <span id="grandTotalDisplay">₱<?= number_format($grand_total, 2); ?></span>
          </div>
        </div>

        <div class="mt-4">
          <form method="POST" action="<?= $_SERVER['PHP_SELF']; ?>">
            <input type="hidden" name="action" value="process_sale">
            <input type="hidden" name="customer" id="customerInputHidden" value="walk in">

            <div class="mt-4">
              <label class="block text-xs text-gray-500 mb-1">Cash Received</label>
              <input id="cashReceivedInput" name="cash_received" type="number" step="0.01" min="0"
                value="<?= $grand_total; ?>" class="w-full border rounded-md px-3 py-2 text-sm bg-gray-50" required>
            </div>

            <!-- Change Output -->
            <div class="mt-2">
              <p class="text-xs text-gray-500 mb-1">Change:</p>
              <p id="cashChangeDisplay" class="text-lg font-semibold text-green-700">₱0.00</p>
            </div>

            <button type="submit" class="w-full mt-4 bg-green-600 hover:bg-green-700 text-white py-2 rounded-md">
              Process Sale
            </button>
          </form>
        </div>
      </div>

    </div>



  </main>

  <!-- Remove Item Modal -->
  <div id="removeItemModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-white rounded-lg w-full max-w-sm p-6">
      <h3 class="text-lg font-semibold mb-4 text-red-600">Remove Item</h3>
      <p class="mb-4 text-sm text-gray-700">
        Are you sure you want to remove <span id="removeItemName" class="font-bold"></span> from the cart?
      </p>

      <form method="POST">
        <input type="hidden" name="action" value="remove_item">
        <input type="hidden" name="remove_item_id" id="removeItemId">
        <div class="flex justify-end space-x-2">
          <button type="button" onclick="closeRemoveItemModal()"
            class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">Cancel</button>
          <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Confirm</button>
        </div>
      </form>
    </div>
  </div>


  <!-- Remove All Item Modal -->
  <div id="removeAllModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-white rounded-lg w-full max-w-sm p-6">
      <h3 class="text-lg font-semibold mb-4 text-red-600">Clear Shopping Cart</h3>
      <p class="mb-4 text-sm text-gray-700">
        Are you sure you want to remove all items from the cart?
        This action cannot be undone.
      </p>

      <form method="POST">
        <input type="hidden" name="action" value="remove_all">
        <div class="flex justify-end space-x-2">
          <button type="button" onclick="closeRemoveAllModal()"
            class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">Cancel</button>
          <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Confirm</button>
        </div>
      </form>
    </div>
  </div>



  <!-- Quantity Modal -->
  <div id="quantityModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
    <div class="bg-white rounded-lg p-6 w-80 shadow-lg">
      <h3 id="modalItemName" class="text-lg font-semibold mb-4">Item Name</h3>
      <form method="POST" id="quantityForm" action="<?php echo $_SERVER['PHP_SELF']; ?>">
        <input type="hidden" name="action" value="add_to_cart">
        <input type="hidden" name="item_id" id="modalItemId" value="">
        <label for="modalQuantity" class="block mb-1 text-sm font-medium">Quantity</label>
        <input type="number" id="modalQuantity" name="quantity" min="1" value="1"
          class="w-full border rounded-md px-3 py-2 mb-4" required>
        <div class="flex justify-end space-x-3">
          <button type="button" onclick="closeQuantityModal()" class="px-4 py-2 rounded bg-gray-300 hover:bg-gray-400">
            Cancel
          </button>
          <button type="submit" class="px-4 py-2 rounded bg-green-600 text-white hover:bg-green-700">
            OK
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Save scroll before reload/submit -->
  <script>
    window.addEventListener('beforeunload', () => {
      sessionStorage.setItem('scrollPos', window.scrollY);
    });

    // Restore scroll on load
    window.addEventListener('load', () => {
      const scrollPos = sessionStorage.getItem('scrollPos');
      if (scrollPos) {
        window.scrollTo(0, parseInt(scrollPos));
        sessionStorage.removeItem('scrollPos');
      }
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

  <!-- Remove Item Modal -->
  <script>
    function openRemoveItemModal(itemId, itemName) {
      document.getElementById('removeItemId').value = itemId;
      document.getElementById('removeItemName').textContent = itemName;
      document.getElementById('removeItemModal').classList.remove('hidden');
    }

    function closeRemoveItemModal() {
      document.getElementById('removeItemModal').classList.add('hidden');
    }

    // ======= Remove All Items Modal ========
    function openRemoveAllModal() {
      document.getElementById('removeAllModal').classList.remove('hidden');
    }

    function closeRemoveAllModal() {
      document.getElementById('removeAllModal').classList.add('hidden');
    }
  </script>

  <!-- Auto-focus input/search bar-->
  <script>
    window.addEventListener('load', () => {
      setTimeout(() => {
        const input = document.getElementById('productSearchInput');
        if (input) input.focus();
      }, 300);
    });
  </script>

  <!-- Quantity Script -->
  <script>
    function openQuantityModal(itemId, itemName, maxQuantity) {
      const quantityInput = document.getElementById('modalQuantity');
      document.getElementById('modalItemId').value = itemId;
      document.getElementById('modalItemName').textContent = itemName;

      quantityInput.value = 1;
      quantityInput.max = maxQuantity;

      // Optional: reset validation state
      quantityInput.setCustomValidity('');

      document.getElementById('quantityModal').classList.remove('hidden');
    }

    function closeQuantityModal() {
      document.getElementById('quantityModal').classList.add('hidden');
    }

    // Close modal if clicking outside
    window.addEventListener('click', function (e) {
      const modal = document.getElementById('quantityModal');
      if (!modal.classList.contains('hidden') && e.target === modal) {
        closeQuantityModal();
      }
    });

    // Optional: Validate max on input change
    document.getElementById('modalQuantity').addEventListener('input', function () {
      const max = parseInt(this.max, 10);
      if (this.value > max) {
        this.setCustomValidity(`Maximum quantity allowed is ${max}.`);
      } else {
        this.setCustomValidity('');
      }
    });
  </script>

  <!-- Cash change automatically calculate -->
  <script>
    document.addEventListener("DOMContentLoaded", function () {
      const cashInput = document.getElementById("cashReceivedInput");
      const changeDisplay = document.getElementById("cashChangeDisplay");

      // Hardcoded total from PHP to JS (syncing the grand total)
      const grandTotal = <?= $grand_total; ?>;

      function updateChange() {
        const cash = parseFloat(cashInput.value);
        if (!isNaN(cash)) {
          const change = Math.max(0, cash - grandTotal);
          changeDisplay.textContent = `₱${change.toFixed(2)}`;
        } else {
          changeDisplay.textContent = '₱0.00';
        }
      }

      // Listen for input change
      cashInput.addEventListener("input", updateChange);

      // Trigger on page load too (useful if the value is pre-filled)
      updateChange();
    });
  </script>


  <!-- Search Barcode and Item Name Script -->
  <script>
    document.addEventListener("DOMContentLoaded", function () {
      const searchInput = document.getElementById('productSearchInput');
      const productItems = document.querySelectorAll('.product-item');

      searchInput.addEventListener('input', function () {
        const searchValue = this.value.toLowerCase().trim();

        productItems.forEach(item => {
          const name = item.querySelector('.product-name').textContent.toLowerCase();
          const barcode = item.querySelector('.product-barcode').textContent.toLowerCase();

          const matches = name.includes(searchValue) || barcode.includes(searchValue);
          item.style.display = matches ? '' : 'none';
        });
      });
    });
  </script>



</body>

</html>