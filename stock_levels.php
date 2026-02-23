<?php
include "database/database.php";
$database->login_session();
$database->item_stock_adjust();

$perPage = 5;
$search  = trim($_GET['search'] ?? '');
$page    = max(1, (int) ($_GET['page'] ?? 1));

$offset = ($page - 1) * $perPage;

$totalItems = $database->getTotalStockItemsCount($search);
$totalPages = max(1, ceil($totalItems / $perPage));

$items = $database->select_stock_items_paginated($offset, $perPage, $search);

$all_items = $database->select_items();
$stock_adjustment = $database->select_stock_adjustment();

$low_stock = count(array_filter($all_items, fn($item) => $item['quantity'] > 0 && $item['quantity'] <= $item['min_stock']));
$out_of_stock = count(array_filter($all_items, fn($item) => $item['quantity'] <= 0));
$total_value = array_sum(array_map(fn($item) => $item['selling_price'] * $item['quantity'], $all_items));

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
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>POS & Inventory - Stock Levels</title>
  <link rel="stylesheet" href="assets/tailwind.min.css">
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
            <h4 class="text-2xl font-bold"><?= count($all_items) ?></h4>
            <p class="text-xs text-gray-500">products in catalog</p>
          </div>
        </div>
        <div class="bg-white border rounded-xl p-4 flex items-center justify-between">
          <div>
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
          <!-- Search Bar -->
          <div class="flex items-center justify-between mb-4">
            <h4 class="text-lg font-semibold">Inventory Levels</h4>
            <form method="GET" action="" class="relative w-full max-w-md ml-4">
              <input
                type="text"
                name="search"
                id="searchInput"
                value="<?= htmlspecialchars($search) ?>"
                placeholder="Search items by name or barcode..."
                class="w-full px-4 py-2 pl-10 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
              <span class="material-icons absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm">search</span>

              <?php if ($search !== ''): ?>
                <a href="?" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                  <span class="material-icons text-sm">close</span>
                </a>
              <?php endif; ?>
            </form>
          </div>

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

          <?php if ($search !== ''): ?>
            <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-800">
              Showing results for "<strong><?= htmlspecialchars($search) ?></strong>"
              (<?= $totalItems ?> item<?= $totalItems !== 1 ? 's' : '' ?>)
              <a href="?" class="ml-2 text-blue-600 hover:underline">Clear search</a>
            </div>
          <?php endif; ?>

          <p class="text-sm text-gray-500 mb-4">Items ordered by priority: Out of Stock → Low Stock → In Stock</p>

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
                <?php if (!empty($items)): ?>
                  <?php foreach ($items as $item): ?>
                    <?php
                    $qty = $item['quantity'];
                    $min = $item['min_stock'];

                    if ($qty <= 0) {
                      $statusText = "Out of Stock";
                      $statusClasses = "bg-red-100 text-red-800";
                    } elseif ($qty > 0 && $qty <= $min) {
                      $statusText = "Low Stock";
                      $statusClasses = "bg-yellow-100 text-yellow-800";
                    } else {
                      $statusText = "In Stock";
                      $statusClasses = "bg-green-100 text-green-800";
                    }
                    ?>
                    <tr class="hover:bg-gray-50">
                      <td class="px-6 py-4 whitespace-nowrap">
                        <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($item['item_name']) ?></p>
                        <p class="text-xs text-gray-500"><?= htmlspecialchars($item['barcode']) ?></p>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                        <?= $qty ?>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <p class="text-xs">Min: <?= $min ?></p>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-block px-2 py-0.5 rounded text-xs font-medium <?= $statusClasses ?>">
                          <?= $statusText ?>
                        </span>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <button class="px-4 py-2 border rounded-lg text-gray-700 text-sm hover:bg-gray-100 adjustBtn"
                          data-item-id="<?= $item['item_id'] ?>" data-item-name="<?= htmlspecialchars($item['item_name']) ?>"
                          data-current-qty="<?= $qty ?>">
                          Adjust
                        </button>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                      <?= $search !== '' ? 'No items found matching your search.' : 'No items found.' ?>
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
                $searchParam = $search !== '' ? '&search=' . urlencode($search) : '';
                ?>

                <!-- Previous -->
                <?php if ($page > 1): ?>
                  <a href="?page=<?= $page - 1 ?><?= $searchParam ?>"
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
                  <a href="?page=1<?= $searchParam ?>"
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
                    <a href="?page=<?= $i ?><?= $searchParam ?>"
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
                  <a href="?page=<?= $totalPages ?><?= $searchParam ?>"
                    class="px-3 py-2 rounded-md text-sm font-medium border border-gray-300 hover:bg-gray-50">
                    <?= $totalPages ?>
                  </a>
                <?php endif; ?>

                <!-- Next -->
                <?php if ($page < $totalPages): ?>
                  <a href="?page=<?= $page + 1 ?><?= $searchParam ?>"
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

        <div class="bg-white border rounded-xl p-6 col-span-1">
          <h4 class="text-lg font-semibold mb-2">Recent Adjustments</h4>
          <p class="text-sm text-gray-500 mb-4">Latest stock changes</p>
          <ul class="space-y-4">
            <?php foreach (array_slice($stock_adjustment, 0, 10) as $sa): ?>
              <li class="flex justify-between items-start">
                <div>
                  <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($sa['item_name']) ?></p>
                  <p class="text-xs text-gray-500"><?= htmlspecialchars($sa['reason_adjustment']) ?></p>
                  <p class="text-xs text-gray-500"><?= htmlspecialchars($sa['username']) ?></p>
                </div>
                <div class="text-right">
                  <?php
                  $adjustment = $sa['new_quantity'] - $sa['previous_quantity'];
                  $color = $adjustment >= 0 ? 'text-green-600' : 'text-red-600';
                  $sign = $adjustment > 0 ? '+' : '';
                  ?>
                  <span class="<?= $color ?> font-bold text-lg"><?= $sign . $adjustment ?></span>
                  <p class="text-xs text-gray-500"><?= date('M j, g:i A', strtotime($sa['created_at'])) ?></p>
                </div>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>
    </section>
  </main>

  <!-- Stock Adjustment Modal -->
  <div id="adjustModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
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
      sidebar.classList.add('-translate-x-full');
    });
  </script>

  <!-- Save scroll before reload/submit -->
  <script>
    window.addEventListener('beforeunload', () => {
      sessionStorage.setItem('scrollPos', window.scrollY);
    });

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

  <!-- Auto-submit search form after user stops typing -->
  <script>
    let searchTimeout;
    const searchInput = document.getElementById('searchInput');
    const searchForm = searchInput.closest('form');

    if (searchInput) {
      searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
          searchForm.submit();
        }, 500);
      });
    }
  </script>

  <!-- (+ -) Button Script -->
  <script>
    document.getElementById('decrementBtn').addEventListener('click', function() {
      const input = document.getElementById('adjustQty');
      let value = parseInt(input.value) || 0;
      input.value = value - 1;
    });

    document.getElementById('incrementBtn').addEventListener('click', function() {
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

        modalItemId.value = itemId;
        modalItemName.textContent = itemName;
        modalCurrentQty.textContent = currentQty;

        adjustForm.reset();
        document.getElementById('modalItemId').value = itemId;

        adjustModal.classList.remove('hidden');
      });
    });

    cancelBtn.addEventListener('click', () => {
      adjustModal.classList.add('hidden');
    });

    adjustModal.addEventListener('click', e => {
      if (e.target === adjustModal) {
        adjustModal.classList.add('hidden');
      }
    });
  </script>
</body>

</html>