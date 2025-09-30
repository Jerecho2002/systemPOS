<?php
include "database/database.php";
$database->login_session();
$database->create_purchase_order();
$database->cancel_purchase_order();
$database->archive_purchase_order();
$database->receive_purchase_order();
$purchaseOrders = $database->list_purchase_orders();
$purchase_orders = $database->select_purchase_orders();
$purchase_order_items = $database->select_purchase_order_items();

$suppliers = $database->select_suppliers();
$items = $database->select_items();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>POS & Inventory - Purchase Orders</title>
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
      <a href="item_catalog.php" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-100">
        <span class="ml-2">Item Catalog</span>
      </a>
      <a href="stock_levels.php" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-100 relative">
        <span class="ml-2">Stock Levels</span>
        <span class="absolute right-3 top-2 text-xs bg-red-500 text-white rounded-full px-2">3</span>
      </a>
      <a href="purchase_orders.php" class="flex items-center px-3 py-2 rounded-lg bg-gray-100 font-medium">
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
      <h2 class="text-2xl font-bold text-gray-800">Inventory Purchases</h2>
    </header>

    <section class="bg-white rounded-xl shadow-md p-6">
      <div class="flex items-center justify-between mb-4">
        <div>
          <h3 class="text-xl font-bold text-gray-800">Purchase Orders</h3>
          <p class="text-sm text-gray-500">Manage supplier orders and inventory procurement</p>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white border rounded-xl p-4 flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500">Total POs</p>
            <h4 class="text-2xl font-bold"><?php echo count($purchase_orders); ?></h4>
            <p class="text-xs text-gray-500">all time</p>
          </div>
        </div>
        <div class="bg-white border rounded-xl p-4 flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500">Pending Orders</p>
            <?php
            $statusOrdered = count(array_filter($purchase_orders, fn($po) => $po['status'] === "Ordered"));
            ?>
            <h4 class="text-2xl font-bold"><?php echo $statusOrdered; ?></h4>
            <p class="text-xs text-gray-500">awaiting delivery</p>
          </div>
        </div>
        <div class="bg-white border rounded-xl p-4 flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500">Received</p>
            <?php
            $statusReceivedTotal = count(array_filter($purchase_orders, fn($po) => $po['status'] === "Received"));
            ?>
            <h4 class="text-2xl font-bold"><?php echo $statusReceivedTotal; ?></h4>
            <p class="text-xs text-gray-500">completed orders</p>
          </div>
        </div>
        <div class="bg-white border rounded-xl p-4 flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500">Total Value</p>
            <?php
            $grandTotal = array_sum(
              array_column(
                array_filter($purchase_orders, fn($po) => $po['status'] === "Ordered"),
                'grand_total'
              )
            );
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
            <h4 class="text-2xl font-bold"><?php echo formatCompactCurrency($grandTotal); ?></h4>
            <p class="text-xs text-gray-500">pending orders</p>
          </div>
        </div>
      </div>

      <div class="bg-white border rounded-xl p-6">
        <div class="flex items-center justify-between mb-4">
          <div class="flex items-center space-x-4">
            <div class="flex items-center gap-4 mb-4">
              <div class="relative flex-1">
                <input type="text" id="poSearchInput" placeholder="Search by PO number or supplier..."
                  class="w-[24rem] px-4 py-2 border rounded-lg focus:outline-none">
              </div>
              <select id="poStatusFilter" class="px-4 py-2 border rounded-lg focus:outline-none">
                <option value="">All Statuses</option>
                <option value="Ordered">Ordered</option>
                <option value="Received">Received</option>
                <option value="Cancelled">Cancelled</option>
              </select>
            </div>
          </div>
          <button id="createPoButton" class="px-4 py-2 text-sm bg-black text-white rounded-lg hover:bg-gray-800">
            + Create PO
          </button>
        </div>

        <h4 class="text-lg font-semibold mb-2">Purchase Orders</h4>
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
        <p class="text-sm text-gray-500 mb-4">List purchase orders from suppliers.</p>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PO Number
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order Date
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Amount
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <?php
            // Build item count per purchase_order_id
            $purchase_items_count = [];

            foreach ($purchase_order_items as $poi) {
              $po_id = $poi['purchase_order_id'];

              if (!isset($purchase_items_count[$po_id])) {
                $purchase_items_count[$po_id] = 0;
              }

              $purchase_items_count[$po_id]++;
            }
            ?>
            <tbody class="bg-white divide-y divide-gray-200">
              <?php foreach ($purchaseOrders as $po): ?>
                <?php if ($po['is_active'] === 0)
                  continue; ?>
                <tr>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    <?= htmlspecialchars($po['po_number']) ?>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <?= htmlspecialchars($po['supplier_name']) ?>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <?= date('F j, Y, g:i A', strtotime($po['date'])) ?>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <?php
                    $count = count($po['items'] ?? []);
                    echo $count . ' item' . ($count !== 1 ? 's' : '');
                    ?>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    ₱<?= number_format($po['grand_total'], 2) ?>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <?php
                    $status = strtolower($po['status']);
                    switch ($status) {
                      case 'ordered':
                        $color = 'bg-blue-100 text-blue-800';
                        break;
                      case 'received':
                        $color = 'bg-green-100 text-green-800';
                        break;
                      case 'cancelled':
                        $color = 'bg-red-100 text-red-800';
                        break;
                      default:
                        $color = 'bg-gray-100 text-gray-800';
                    }
                    ?>
                    <span
                      class="status-label <?= $color ?> px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                      <?= ucfirst($status) ?>
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <button class="viewPoBtn text-blue-400 hover:text-blue-600" title="View"
                      data-po-number="<?= htmlspecialchars($po['po_number']) ?>"
                      data-supplier="<?= htmlspecialchars($po['supplier_name']) ?>"
                      data-date="<?= htmlspecialchars($po['date']) ?>"
                      data-status="<?= htmlspecialchars($po['status']) ?>"
                      data-created-by="<?= htmlspecialchars($po['created_by']) ?>"
                      data-items='<?= json_encode($po['items']) ?>'>
                      <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                        <path fill-rule="evenodd"
                          d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z"
                          clip-rule="evenodd" />
                      </svg>
                    </button>
                    <?php if ($po['status'] != "Cancelled"): ?>
                      <button class="text-red-500 hover:text-red-700 openCancelPoModal"
                        data-id="<?= $po['purchase_order_id'] ?>" data-number="<?= htmlspecialchars($po['po_number']) ?>"
                        title="Cancel Purchase Order">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                          <path fill-rule="evenodd"
                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                            clip-rule="evenodd" />
                        </svg>
                      </button>
                    <?php endif; ?>
                    <?php if ($po['status'] == "Cancelled"): ?>
                      <button class="text-red-500 hover:text-red-700 openArchivePoModal"
                        data-id="<?= $po['purchase_order_id'] ?>" data-name="<?= htmlspecialchars($po['po_number']) ?>"
                        title="Archive Purchase Order">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                          <path fill-rule="evenodd"
                            d="M4 3a1 1 0 011-1h10a1 1 0 011 1v3a1 1 0 01-1 1H5a1 1 0 01-1-1V3zm3 4h10a1 1 0 011 1v9a1 1 0 01-1 1H5a1 1 0 01-1-1V8a1 1 0 011-1h10V5H7v3z"
                            clip-rule="evenodd" />
                        </svg>
                      </button>
                    <?php endif; ?>
                    <?php if ($po['status'] != "Received" && $po['status'] != "Cancelled"): ?>
                      <button class="text-green-500 hover:text-green-700 openReceivePoModal"
                        data-id="<?= $po['purchase_order_id'] ?>" data-name="<?= htmlspecialchars($po['po_number']) ?>"
                        title="Mark this PO as received">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                          <path fill-rule="evenodd"
                            d="M16.707 5.293a1 1 0 00-1.414 0L8 12.586 4.707 9.293a1 1 0 00-1.414 1.414l4 4a1 1 0 001.414 0l8-8a1 1 0 000-1.414z"
                            clip-rule="evenodd" />
                        </svg>
                      </button>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </section>
  </main>

  <!-- Purchase Orders Modal -->
  <div id="createPoModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-white rounded-lg w-full max-w-xl p-6 shadow-lg">
      <div class="flex justify-between items-center mb-4">
        <div>
          <h3 class="text-lg font-semibold text-gray-900">Create Purchase Order</h3>
          <p class="text-sm text-gray-500">Create a new purchase order for supplier</p>
        </div>
        <button id="closeCreatePoModal" class="text-gray-500 hover:text-gray-700 text-xl leading-none">&times;</button>
      </div>

      <form method="POST" class="space-y-4" id="purchaseOrderForm">
        <input type="hidden" name="status" value="Ordered" />
        <div>
          <label for="supplier" class="block text-sm font-medium text-gray-700">Supplier *</label>
          <select id="supplier" name="supplier_id" required
            class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200">
            <option value="">Select supplier</option>
            <?php foreach ($suppliers as $sp): ?>
              <option value="<?php echo $sp['supplier_id']; ?>"><?php echo $sp['supplier_name']; ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Items</label>
          <div id="itemsContainer" class="space-y-2">
            <div class="grid grid-cols-[2fr_1fr_auto] gap-2 items-center">
              <select name="item_id[]" required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200">
                <option value="">Select item</option>
                <?php foreach ($items as $item): ?>
                  <option value="<?php echo $item['item_id']; ?>"><?php echo $item['item_name']; ?></option>
                <?php endforeach; ?>
              </select>
              <input type="number" name="quantity[]" min="1" required value="1"
                class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200" />
              <button type="button"
                class="removeItemBtn text-gray-500 hover:text-red-600 text-xl font-bold leading-none">&times;</button>
            </div>
          </div>

          <button type="button" id="addItemBtn"
            class="mt-2 px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">+ Add Item</button>
        </div>

        <div class="flex justify-end space-x-2 pt-4">
          <button type="button" id="cancelCreatePoModal"
            class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">Cancel</button>
          <button type="submit" class="px-4 py-2 bg-black text-white rounded hover:bg-gray-800">Create Purchase
            Order</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Receive Purchase Order Modal -->
  <div id="receivePoModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-white rounded-lg w-full max-w-sm p-6">
      <h3 class="text-lg font-semibold mb-4 text-green-600">Confirm Delivery</h3>
      <p class="mb-4 text-sm text-gray-700">
        Has <span id="receive_po_number" class="font-bold text-gray-900"></span> been received from the supplier?<br>
        Confirming this will update the inventory based on the delivered items.
      </p>

      <form method="POST">
        <input type="hidden" name="receive_po_id" id="receive_po_id">
        <div class="flex justify-end space-x-2">
          <button type="button" id="cancelReceiveModalBtn"
            class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">
            Cancel
          </button>
          <button type="submit" name="receive_po" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
            Mark as Received
          </button>
        </div>
      </form>
    </div>
  </div>


  <!-- View PO Modal -->
  <div id="viewPoModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white p-6 rounded shadow-lg w-full max-w-3xl">
      <div class="flex justify-between items-center mb-4">
        <h2 id="modalPoNumber" class="text-lg font-bold"></h2>
        <button id="closeViewPoModal" class="text-gray-500 hover:text-black">&times;</button>
      </div>
      <div class="mb-4">
        <p><strong>PO Number:</strong> <span id="modalPoNum"></span></p>
        <p><strong>Date:</strong> <span id="modalOrderDate"></span></p>
        <p><strong>Supplier:</strong> <span id="modalSupplier"></span></p>
        <p><strong>Status:</strong> <span id="modalStatus"
            class="inline-block px-2 py-0.5 rounded text-xs font-medium"></span></p>
        <p><strong>Created by:</strong> <span id="modalCreatedBy"></span></p>
      </div>
      <table class="w-full text-sm table-auto border">
        <thead>
          <tr>
            <th class="px-4 py-2 text-left border">Item</th>
            <th class="px-4 py-2 text-left border">Quantity</th>
            <th class="px-4 py-2 text-left border">Unit Cost</th>
            <th class="px-4 py-2 text-left border">Line Total</th>
          </tr>
        </thead>
        <tbody id="modalItemsTable">
        </tbody>
      </table>
    </div>
  </div>

  <!-- Cancel Purchase Order Modal -->
  <div id="cancelPoModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-white rounded-lg w-full max-w-sm p-6">
      <h3 class="text-lg font-semibold mb-4 text-red-600">Cancel Purchase Order</h3>
      <p class="mb-4 text-sm text-gray-700">
        Are you sure you want to cancel purchase order <span id="cancel_po_number"
          class="font-bold text-gray-900"></span>?
        This action cannot be undone.
      </p>

      <form method="POST">
        <input type="hidden" name="cancel_po_id" id="cancel_po_id">
        <div class="flex justify-end space-x-2">
          <button type="button" id="cancelCancelModalBtn"
            class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">
            Cancel
          </button>
          <button type="submit" name="cancel_po" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
            Confirm Cancel
          </button>
        </div>
      </form>
    </div>
  </div>


  <!-- Archive Purchase Order Modal -->
  <div id="archivePurchaseOrderModal"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-white rounded-lg w-full max-w-sm p-6">
      <h3 class="text-lg font-semibold mb-4 text-yellow-600">Archive Purchase Order</h3>
      <p class="mb-4 text-sm text-gray-700">
        Are you sure you want to archive <span id="archive_po_number" class="font-bold"></span>?
        This action can be undone.
      </p>

      <form method="POST">
        <input type="hidden" name="purchase_order_id" id="archive_po_id">
        <div class="flex justify-end space-x-2">
          <button type="button" id="cancelArchivePoModalBtn"
            class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">
            Cancel
          </button>
          <button type="submit" name="archive_purchase_order"
            class="px-4 py-2 bg-yellow-600 text-white rounded hover:bg-yellow-700">
            Confirm Archive
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

  <!-- Create Purchase Orders Script -->
  <script>
    const createPoButton = document.getElementById('createPoButton');
    const createPoModal = document.getElementById('createPoModal');
    const closeCreatePoModal = document.getElementById('closeCreatePoModal');
    const cancelCreatePoModal = document.getElementById('cancelCreatePoModal');
    const addItemBtn = document.getElementById('addItemBtn');
    const itemsContainer = document.getElementById('itemsContainer');

    createPoButton.addEventListener('click', () => {
      createPoModal.classList.remove('hidden');
    });

    closeCreatePoModal.addEventListener('click', () => {
      createPoModal.classList.add('hidden');
    });
    cancelCreatePoModal.addEventListener('click', () => {
      createPoModal.classList.add('hidden');
    });
    createPoModal.addEventListener('click', (e) => {
      if (e.target === createPoModal) {
        createPoModal.classList.add('hidden');
      }
    });

    // Add new item row
    addItemBtn.addEventListener('click', () => {
      const newItem = document.createElement('div');
      newItem.classList.add('grid', 'grid-cols-[2fr_1fr_auto]', 'gap-2', 'items-center');
      newItem.innerHTML = `
      <select name="item_id[]" required
        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200">
        <option value="">Select item</option>
        <?php foreach ($items as $item): ?>
        <option value="<?php echo $item['item_id']; ?>"><?php echo $item['item_name']; ?></option>
        <?php endforeach; ?>
      </select>
      <input type="number" name="quantity[]" min="1" required value="1"
        class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200" />
      <button type="button" class="removeItemBtn text-gray-500 hover:text-red-600 text-xl font-bold leading-none">&times;</button>
    `;
      itemsContainer.appendChild(newItem);

      // Attach remove event to the new remove button
      newItem.querySelector('.removeItemBtn').addEventListener('click', () => {
        newItem.remove();
      });
    });

    // Remove item row
    document.querySelectorAll('.removeItemBtn').forEach(btn => {
      btn.addEventListener('click', (e) => {
        e.target.closest('div').remove();
      });
    });
  </script>

  <!-- View Purchase Orders Script -->
  <script>
    const viewPoModal = document.getElementById('viewPoModal');

    function formatDateTime(dateString) {
      const date = new Date(dateString);
      if (isNaN(date)) return 'Invalid date';

      return date.toLocaleString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
      });
    }

    document.querySelectorAll('.viewPoBtn').forEach(button => {
      button.addEventListener('click', () => {
        const poNumber = button.dataset.poNumber;
        const supplier = button.dataset.supplier;
        const date = button.dataset.date;
        const status = button.dataset.status;
        const createdBy = button.dataset.createdBy;

        let items = [];
        try {
          items = JSON.parse(button.dataset.items || '[]');
        } catch (e) {
          console.error('Failed to parse items JSON:', e);
        }

        document.getElementById('modalPoNumber').innerText = `${poNumber} - ${supplier}`;
        document.getElementById('modalPoNum').innerText = poNumber;
        document.getElementById('modalOrderDate').innerText = formatDateTime(date);
        document.getElementById('modalSupplier').innerText = supplier;
        document.getElementById('modalStatus').innerText = status;
        document.getElementById('modalCreatedBy').innerText = createdBy;

        const badge = document.getElementById('modalStatus');
        badge.className = 'inline-block px-2 py-0.5 rounded text-xs font-medium';
        if (status === 'Received') {
          badge.classList.add('bg-green-100', 'text-green-800');
        } else if (status === 'Cancelled') {
          badge.classList.add('bg-red-100', 'text-red-800');
        } else {
          badge.classList.add('bg-blue-100', 'text-blue-800');
        }

        function numberWithCommas(x) {
          return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        }

        const tbody = document.getElementById('modalItemsTable');
        tbody.innerHTML = '';

        if (items.length === 0) {
          const emptyRow = `
          <tr>
            <td colspan="4" class="px-4 py-2 text-center text-gray-500">No items found.</td>
          </tr>
        `;
          tbody.insertAdjacentHTML('beforeend', emptyRow);
        } else {
          items.forEach(item => {
            const row = `
            <tr>
              <td class="px-4 py-2 border">${item.item_name}</td>
              <td class="px-4 py-2 border">${numberWithCommas(item.quantity)}</td>
              <td class="px-4 py-2 border">₱${numberWithCommas(parseFloat(item.unit_cost).toFixed(2))}</td>
              <td class="px-4 py-2 border">₱${numberWithCommas(parseFloat(item.line_total).toFixed(2))}</td>
            </tr>
          `;
            tbody.insertAdjacentHTML('beforeend', row);
          });
        }

        viewPoModal.classList.remove('hidden');
      });
    });

    document.getElementById('closeViewPoModal').addEventListener('click', () => {
      viewPoModal.classList.add('hidden');
    });

    viewPoModal.addEventListener('click', (e) => {
      if (e.target === viewPoModal) {
        viewPoModal.classList.add('hidden');
      }
    });
  </script>

  <!-- Receive Purchase Orders Script -->
  <script>
    const receiveButtons = document.querySelectorAll('.openReceivePoModal');
    const receiveModal = document.getElementById('receivePoModal');
    const receivePoId = document.getElementById('receive_po_id');
    const receivePoNumber = document.getElementById('receive_po_number');
    const cancelReceiveBtn = document.getElementById('cancelReceiveModalBtn');

    receiveButtons.forEach(button => {
      button.addEventListener('click', () => {
        const id = button.getAttribute('data-id');
        const poNumber = button.getAttribute('data-name');

        receivePoId.value = id;
        receivePoNumber.textContent = poNumber;

        receiveModal.classList.remove('hidden');
      });
    });

    cancelReceiveBtn.addEventListener('click', () => {
      receiveModal.classList.add('hidden');
    });

    window.addEventListener('click', (e) => {
      if (e.target === receiveModal) {
        receiveModal.classList.add('hidden');
      }
    });
  </script>

  <!-- Cancel Purchase Orders Script -->
  <script>
    const cancelButtons = document.querySelectorAll('.openCancelPoModal');
    const cancelModal = document.getElementById('cancelPoModal');
    const cancelPoId = document.getElementById('cancel_po_id');
    const cancelPoNumber = document.getElementById('cancel_po_number');
    const cancelCancelBtn = document.getElementById('cancelCancelModalBtn');

    cancelButtons.forEach(button => {
      button.addEventListener('click', () => {
        const id = button.getAttribute('data-id');
        const number = button.getAttribute('data-number');

        cancelPoId.value = id;
        cancelPoNumber.textContent = number;

        cancelModal.classList.remove('hidden');
      });
    });

    cancelCancelBtn.addEventListener('click', () => {
      cancelModal.classList.add('hidden');
    });

    window.addEventListener('click', (e) => {
      if (e.target === cancelModal) {
        cancelModal.classList.add('hidden');
      }
    });
  </script>


  <!-- Delete Purchase Orders Script -->
  <script>
    const archivePoButtons = document.querySelectorAll('.openArchivePoModal');
    const archivePoModal = document.getElementById('archivePurchaseOrderModal');
    const archivePoIdInput = document.getElementById('archive_po_id');
    const archivePoNumberSpan = document.getElementById('archive_po_number');
    const cancelArchivePoBtn = document.getElementById('cancelArchivePoModalBtn');

    archivePoButtons.forEach(button => {
      button.addEventListener('click', () => {
        const id = button.getAttribute('data-id');
        const poNumber = button.getAttribute('data-name');

        archivePoIdInput.value = id;
        archivePoNumberSpan.textContent = poNumber;

        archivePoModal.classList.remove('hidden');
      });
    });

    cancelArchivePoBtn.addEventListener('click', () => {
      archivePoModal.classList.add('hidden');
    });

    window.addEventListener('click', (e) => {
      if (e.target === archivePoModal) {
        archivePoModal.classList.add('hidden');
      }
    });
  </script>

  <!-- Filter & Serach Purchase Orders Script -->
  <script>
    document.addEventListener("DOMContentLoaded", function () {
      const poSearchInput = document.getElementById('poSearchInput');
      const poStatusFilter = document.getElementById('poStatusFilter');

      function filterPurchaseOrders() {
        const searchValue = poSearchInput.value.toLowerCase().trim();
        const selectedStatus = poStatusFilter.value.toLowerCase().trim();
        const rows = document.querySelectorAll('tbody tr');

        rows.forEach(row => {
          const poNumber = row.querySelector('td:nth-child(1)').textContent.toLowerCase();
          const supplier = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
          const statusSpan = row.querySelector('td:nth-child(6) span');
          const status = statusSpan ? statusSpan.textContent.toLowerCase().trim() : '';

          const matchesSearch = poNumber.includes(searchValue) || supplier.includes(searchValue);
          const matchesStatus = selectedStatus === "" || status === selectedStatus;

          row.style.display = (matchesSearch && matchesStatus) ? "" : "none";
        });
      }

      poSearchInput.addEventListener('input', filterPurchaseOrders);
      poStatusFilter.addEventListener('change', filterPurchaseOrders);
    });
  </script>
</body>

</html>