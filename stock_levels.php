<?php
include "database/database.php";
$database->login_session();
$database->item_stock_adjust();
$stock_adjustment = $database->select_stock_adjustment();
$items = $database->select_items();
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

  <!-- Mobile Sidebar Toggle Button -->
  <button id="sidebar-toggle" class="md:hidden p-3 fixed top-4 left-4 z-60 text-white rounded shadow-lg"
    style="background-color: rgba(170, 170, 170, 0.82);">
    ☰
  </button>


  <?php include "sidebar.php"; ?>

  <!-- Main Content -->
  <main class="flex-1 ml-0 md:ml-64 p-6">
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
            <h4 class="text-2xl font-bold"><?= count($items); ?></h4>
            <p class="text-xs text-gray-500">products in catalog</p>
          </div>
        </div>
        <div class="bg-white border rounded-xl p-4 flex items-center justify-between">
          <div>
            <?php
            $low_stock = count(array_filter($items, fn($item) => $item['quantity'] > 0 && $item['quantity'] <= $item['min_stock']));
            $out_of_stock = count(array_filter($items, fn($item) => $item['quantity'] <= 0));
            $total_value = array_sum(array_map(fn($item) => $item['selling_price'] * $item['quantity'], $items));
            ?>
            <?php
            function formatCompactCurrency($number)
            {
              if ($number >= 1_000_000_000) {
                return '₱' . round($number / 1_000_000_000, 1) . 'B';
              } elseif ($number >= 1_000_000) {
                return '₱' . round($number / 1_000_000, 1) . 'M';
              } elseif ($number >= 1_000) {
                return '₱' . round($number / 1_000, 1) . 'k';
              } else {
                return '₱' . number_format($number, 0);
              }
            }
            ?>
            <p class="text-sm text-gray-500">Low Stock Items</p>
            <h4 class="text-2xl font-bold"><?= $low_stock ?></h4>
            <p class="text-xs text-gray-500">need reordering</p>
          </div>
        </div>
        <div class="bg-white border rounded-xl p-4 flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500">Out of Stock</p>
            <h4 class="text-2xl font-bold"><?= $out_of_stock ?></h4>
            <p class="text-xs text-gray-500">urgent reorder</p>
          </div>
        </div>
        <div class="bg-white border rounded-xl p-4 flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500">Total Value</p>
            <h4 class="text-2xl font-bold"><?= formatCompactCurrency($total_value) ?></h4>
            <p class="text-xs text-gray-500">inventory value</p>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="bg-white border rounded-xl p-6 lg:col-span-2">
          <h4 class="text-lg font-semibold mb-2">Inventory Levels</h4>
          <?php
          if (isset($_SESSION['create-success'])) {
            $success = $_SESSION['create-success'];
            ?>
            <div id="successAlert"
              class="mb-4 px-4 py-3 bg-green-100 border border-green-400 text-green-700 text-sm rounded-lg">
              <?php echo $success ?>
            </div>
            <?php
          } ?>

          <?php
          if (isset($_SESSION['create-error'])) {
            $error = $_SESSION['create-error'];
            ?>
            <div id="errorAlert" class="mb-4 px-4 py-3 bg-red-100 border border-red-400 text-red-700 text-sm rounded-lg">
              <?php echo $error ?>
            </div>
            <?php
          } ?>
          <p class="text-sm text-gray-500 mb-4">Items Awaiting Restock</p>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th scope="col"
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                  <th scope="col"
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Stock
                  </th>
                  <th scope="col"
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Min stock
                  </th>
                  <th scope="col"
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                  <th scope="col"
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($items as $item): ?>
                  <?php
                  $qty = $item['quantity'];
                  $min = $item['min_stock'];

                  if ($qty > 0 && $qty <= $min) {
                    $statusText = "Low Stock";
                    $statusClasses = "bg-red-100 text-red-800";
                  } elseif ($qty <= 0) {
                    $statusText = "Out of Stock";
                    $statusClasses = "bg-red-100 text-red-800";
                  } else {
                    continue;
                  }
                  ?>
                  <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <p class="text-sm font-medium text-gray-900"><?= $item['item_name']; ?></p>
                      <p class="text-xs text-gray-500"><?= htmlspecialchars($item['barcode']); ?></p>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                      <?= $qty ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      <p class="text-xs">Min: <?= $min; ?></p>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span class="inline-block px-2 py-0.5 rounded text-xs font-medium <?= $statusClasses; ?>">
                        <?= $statusText; ?>
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                      <button class="px-4 py-2 border rounded-lg text-gray-700 text-sm hover:bg-gray-100 adjustBtn"
                        data-item-id="<?= $item['item_id'] ?>" data-item-name="<?= $item['item_name'] ?>"
                        data-current-qty="<?= $qty ?>">
                        Adjust
                      </button>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

        <div class="bg-white border rounded-xl p-6 col-span-1">
          <h4 class="text-lg font-semibold mb-2">Recent Adjustments</h4>
          <p class="text-sm text-gray-500 mb-4">Latest stock changes</p>
          <ul class="space-y-4">
            <?php foreach ($stock_adjustment as $sa): ?>
              <li class="flex justify-between items-start">
                <div>
                  <p class="text-sm font-medium text-gray-900"><?= $sa['item_name']; ?></p>
                  <p class="text-xs text-gray-500"><?= htmlspecialchars($sa['reason_adjustment']); ?></p>
                  <p class="text-xs text-gray-500"><?= htmlspecialchars($sa['username']); ?></p>
                </div>
                <div class="text-right">
                  <?php
                  $adjustment = $sa['new_quantity'] - $sa['previous_quantity'];
                  $color = $adjustment >= 0 ? 'text-green-600' : 'text-red-600';
                  $sign = $adjustment > 0 ? '+' : '';
                  ?>
                  <span class="<?= $color ?> font-bold text-lg"><?= $sign . $adjustment ?></span>
                  <p class="text-xs text-gray-500"><?= date('F j, Y, g:i A', strtotime($sa['created_at'])) ?></p>
                </div>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>

    </section>
  </main>

  <!-- Stock Adjustment Modal -->
  <div id="adjustModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
    <div class="bg-white rounded-lg shadow-lg w-96 p-6">
      <h2 class="text-xl font-semibold mb-4">Adjust Stock</h2>
      <form id="adjustForm" method="POST" action="">
        <input type="hidden" name="item_id" id="modalItemId" value="" />

        <div class="mb-3">
          <label class="block text-sm font-medium text-gray-700">Item Name</label>
          <p id="modalItemName" class="mt-1 text-gray-900"></p>
        </div>

        <div class="mb-3">
          <label class="block text-sm font-medium text-gray-700">Current Quantity:</label>
          <p id="modalCurrentQty" class="inline-block text-gray-900 ml-1"></p>
        </div>

        <div class="mb-3">
          <label for="adjustQty" class="block text-sm font-medium text-gray-700 mb-1">
            Adjustment Quantity
          </label>
          <div class="flex items-center space-x-2">
            <button type="button" id="decrementBtn"
              class="px-3 py-1 bg-gray-200 hover:bg-gray-300 rounded text-lg font-bold">-</button>
            <input type="number" id="adjustQty" name="adjust_qty" required
              class="w-full text-center rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              placeholder="Enter adjustment" />
            <button type="button" id="incrementBtn"
              class="px-3 py-1 bg-gray-200 hover:bg-gray-300 rounded text-lg font-bold">+</button>
          </div>
        </div>

        <div class="mb-4">
          <label for="reason" class="block text-sm font-medium text-gray-700">Reason for Adjustment</label>
          <textarea id="reason" name="reason_adjustment" required rows="3"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            placeholder="Enter reason"></textarea>
        </div>

        <div class="flex justify-end space-x-2">
          <button type="button" id="cancelBtn" class="px-4 py-2 rounded bg-gray-200 hover:bg-gray-300 text-gray-700">
            Cancel
          </button>
          <button type="submit" name="adjust_stock_submit"
            class="px-4 py-2 rounded bg-black text-white hover:bg-gray-800">
            Submit
          </button>
        </div>
      </form>
    </div>
  </div>

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

  <!-- (+ -) Button Script -->
  <script>
    document.getElementById('decrementBtn').addEventListener('click', function () {
      const input = document.getElementById('adjustQty');
      let value = parseInt(input.value) || 0;
      input.value = value - 1;
    });

    document.getElementById('incrementBtn').addEventListener('click', function () {
      const input = document.getElementById('adjustQty');
      let value = parseInt(input.value) || 0;
      input.value = value + 1;
    });
  </script>


  <!-- Stock Adjustment Script -->
  <script>
    const adjustModal = document.getElementById('adjustModal');
    const adjustButtons = document.querySelectorAll('.adjustBtn');
    const modalItemId = document.getElementById('modalItemId');
    const modalItemName = document.getElementById('modalItemName');
    const modalCurrentQty = document.getElementById('modalCurrentQty');
    const cancelBtn = document.getElementById('cancelBtn');
    const adjustForm = document.getElementById('adjustForm');

    adjustButtons.forEach(button => {
      button.addEventListener('click', () => {
        const itemId = button.dataset.itemId;
        const itemName = button.dataset.itemName;
        const currentQty = button.dataset.currentQty;

        // Set hidden input and modal text
        modalItemId.value = itemId;
        modalItemName.textContent = itemName;
        modalCurrentQty.textContent = currentQty;

        adjustForm.reset();

        // Show the modal
        adjustModal.classList.remove('hidden');
      });
    });

    cancelBtn.addEventListener('click', () => {
      adjustModal.classList.add('hidden');
    });

    // Close modal when clicking outside content
    adjustModal.addEventListener('click', e => {
      if (e.target === adjustModal) {
        adjustModal.classList.add('hidden');
      }
    });
  </script>
</body>

</html>