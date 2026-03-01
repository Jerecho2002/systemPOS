<?php
include "database/database.php";
$database->login_session();
$database->admin_session();
$database->create_purchase_order();
$database->cancel_purchase_order();
$database->archive_purchase_order();
$database->receive_purchase_order();

$perPage = 5;
$search  = trim($_GET['search'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');
$page    = max(1, (int) ($_GET['page'] ?? 1));

$offset = ($page - 1) * $perPage;

$totalPOs = $database->getTotalPurchaseOrdersCount($search, $statusFilter);
$totalPages = max(1, ceil($totalPOs / $perPage));

$purchaseOrders = $database->list_purchase_orders_paginated($offset, $perPage, $search, $statusFilter);

$all_purchase_orders = $database->select_purchase_orders();
$purchase_order_items = $database->select_purchase_order_items();

$suppliers = $database->select_suppliers();
$items = $database->select_items();

function formatCompactCurrency($number)
{
  if ($number >= 1_000_000_000) return '₱' . round($number / 1_000_000_000, 1) . 'B';
  if ($number >= 1_000_000)     return '₱' . round($number / 1_000_000, 1) . 'M';
  if ($number >= 1_000)         return '₱' . round($number / 1_000, 1) . 'k';
  return '₱' . number_format($number, 0);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>POS & Inventory - Purchase Orders</title>
  <link rel="stylesheet" href="assets/tailwind.min.css">
  <link href="assets/fonts.css" rel="stylesheet">

  <style>
    :root {
      --bg: #0f1117;
      --surface: #1a1d27;
      --surface2: #22263a;
      --border: #2e3347;
      --accent: #f5a623;
      --text: #e8eaf0;
      --text-muted: #7b82a0;
      --success: #43d392;
      --danger: #ff5c5c;
      --warning: #f59e0b;
      --info: #60a5fa;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      background: var(--bg);
      color: var(--text);
      min-height: 100vh;
      display: flex;
    }

    main {
      flex: 1;
      padding: 24px;
      transition: margin-left .3s ease;
    }

    .card {
      background: var(--surface);
      border: 1.5px solid var(--border);
      border-radius: 16px;
      padding: 24px;
    }

    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 16px;
      margin-bottom: 32px;
    }

    .stat-card {
      background: var(--surface2);
      border: 1.5px solid var(--border);
      border-radius: 14px;
      padding: 20px;
      transition: transform .15s, box-shadow .15s;
    }

    .stat-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 20px rgba(0, 0, 0, .2);
    }

    .stat-card h4 {
      font-size: 26px;
      font-weight: 700;
      margin: 4px 0 2px;
    }

    .stat-card p:first-child {
      font-size: 12px;
      color: var(--text-muted);
      text-transform: uppercase;
      letter-spacing: .6px;
    }

    .stat-card p:last-child {
      font-size: 11px;
      color: var(--text-muted);
      margin-top: 4px;
    }

    .btn-primary {
      background: var(--accent);
      color: #111;
      border: none;
      border-radius: 10px;
      padding: 9px 18px;
      font-size: 13px;
      font-weight: 700;
      cursor: pointer;
      transition: opacity .2s;
      white-space: nowrap;
    }

    .btn-primary:hover {
      opacity: .88;
    }

    .search-wrap {
      position: relative;
      width: 100%;
      max-width: 380px;
    }

    .search-wrap input {
      width: 100%;
      background: var(--bg);
      border: 1.5px solid var(--border);
      border-radius: 10px;
      padding: 9px 36px 9px 38px;
      color: var(--text);
      font-size: 13px;
      outline: none;
      transition: border-color .2s, box-shadow .2s;
    }

    .search-wrap input:focus {
      border-color: var(--accent);
      box-shadow: 0 0 0 3px rgba(245, 166, 35, .1);
    }

    .search-wrap input::placeholder {
      color: var(--text-muted);
    }

    .search-icon {
      position: absolute;
      left: 12px;
      top: 50%;
      transform: translateY(-50%);
      color: var(--text-muted);
      pointer-events: none;
    }

    .search-clear {
      position: absolute;
      right: 10px;
      top: 50%;
      transform: translateY(-50%);
      color: var(--text-muted);
      font-size: 16px;
      line-height: 1;
      text-decoration: none;
      transition: color .2s;
    }

    .search-clear:hover {
      color: var(--danger);
    }

    .filter-select {
      background: var(--bg);
      border: 1.5px solid var(--border);
      border-radius: 10px;
      padding: 9px 14px;
      color: var(--text);
      font-size: 13px;
      min-width: 180px;
    }

    .filter-select:focus {
      border-color: var(--accent);
      outline: none;
    }

    .alert {
      padding: 12px 16px;
      border-radius: 10px;
      font-size: 13px;
      margin-bottom: 16px;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .alert-success {
      background: rgba(67, 211, 146, .1);
      border: 1px solid rgba(67, 211, 146, .3);
      color: var(--success);
    }

    .alert-error {
      background: rgba(255, 92, 92, .1);
      border: 1px solid rgba(255, 92, 92, .3);
      color: var(--danger);
    }

    .data-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 13px;
    }

    .data-table thead tr {
      border-bottom: 1.5px solid var(--border);
    }

    .data-table thead th {
      padding: 10px 16px;
      text-align: left;
      font-size: 11px;
      font-weight: 700;
      letter-spacing: 1.2px;
      text-transform: uppercase;
      color: var(--text-muted);
      white-space: nowrap;
    }

    .data-table tbody tr {
      border-bottom: 1px solid var(--border);
      transition: background .15s;
    }

    .data-table tbody tr:hover {
      background: rgba(255, 255, 255, .02);
    }

    .data-table tbody td {
      padding: 13px 16px;
    }

    .status-pill {
      padding: 3px 10px;
      font-size: 12px;
      font-weight: 600;
      border-radius: 999px;
      white-space: nowrap;
    }

    .status-ordered {
      background: rgba(96, 165, 250, .18);
      color: var(--info);
    }

    .status-received {
      background: rgba(67, 211, 146, .18);
      color: var(--success);
    }

    .status-cancelled {
      background: rgba(255, 92, 92, .18);
      color: var(--danger);
    }

    .btn-action {
      background: none;
      border: none;
      cursor: pointer;
      padding: 6px;
      border-radius: 8px;
      color: var(--text-muted);
      transition: all .15s;
    }

    .btn-action:hover.view {
      color: var(--info);
      background: rgba(96, 165, 250, .12);
    }

    .btn-action:hover.cancel {
      color: var(--danger);
      background: rgba(255, 92, 92, .12);
    }

    .btn-action:hover.receive {
      color: var(--success);
      background: rgba(67, 211, 146, .12);
    }

    .btn-action:hover.archive {
      color: var(--warning);
      background: rgba(245, 166, 35, .12);
    }

    .pagination {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-top: 20px;
      padding-top: 16px;
      border-top: 1px solid var(--border);
      flex-wrap: wrap;
      gap: 12px;
    }

    .page-btn {
      padding: 6px 12px;
      border-radius: 8px;
      font-size: 12px;
      font-weight: 600;
      border: 1.5px solid var(--border);
      background: var(--surface);
      color: var(--text);
      text-decoration: none;
      transition: all .2s;
    }

    .page-btn:hover {
      border-color: var(--accent);
      background: rgba(245, 166, 35, .08);
      color: var(--accent);
    }

    .page-btn.active {
      background: var(--accent);
      color: #111;
      border-color: var(--accent);
    }

    .page-btn.disabled {
      opacity: .5;
      cursor: not-allowed;
      pointer-events: none;
    }

    .modal-overlay {
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, .7);
      backdrop-filter: blur(4px);
      z-index: 100;
      display: flex;
      align-items: center;
      justify-content: center;
      opacity: 0;
      pointer-events: none;
      transition: opacity .2s;
    }

    .modal-overlay.active {
      opacity: 1;
      pointer-events: all;
    }

    .modal-box {
      background: var(--surface);
      border: 1.5px solid var(--border);
      border-radius: 18px;
      padding: 28px;
      width: 580px;
      max-width: 94%;
      box-shadow: 0 25px 60px rgba(0, 0, 0, .4);
    }

    .form-group {
      margin-bottom: 18px;
    }

    .form-group label {
      display: block;
      font-size: 12px;
      font-weight: 600;
      color: var(--text-muted);
      margin-bottom: 6px;
      text-transform: uppercase;
      letter-spacing: .8px;
    }

    .form-input,
    .form-select,
    .form-textarea {
      width: 100%;
      background: var(--bg);
      border: 1.5px solid var(--border);
      border-radius: 10px;
      padding: 10px 14px;
      color: var(--text);
      font-size: 13px;
      outline: none;
      transition: border-color .2s;
    }

    .form-input:focus,
    .form-select:focus,
    .form-textarea:focus {
      border-color: var(--accent);
    }

    .item-row {
      display: flex;
      gap: 12px;
      align-items: center;
      margin-bottom: 12px;
    }

    .item-row select {
      flex: 1;
    }

    .item-row input[type="number"] {
      width: 100px;
      text-align: center;
    }

    .remove-btn {
      background: none;
      border: none;
      color: var(--text-muted);
      font-size: 22px;
      cursor: pointer;
      padding: 4px;
      border-radius: 6px;
      transition: color .15s, background .15s;
    }

    .remove-btn:hover {
      color: var(--danger);
      background: rgba(255, 92, 92, .1);
    }

    .modal-actions {
      display: flex;
      justify-content: flex-end;
      gap: 12px;
      margin-top: 24px;
    }

    .btn-cancel {
      background: var(--surface2);
      border: 1.5px solid var(--border);
      color: var(--text-muted);
      border-radius: 10px;
      padding: 9px 20px;
      font-size: 13px;
      font-weight: 600;
      cursor: pointer;
    }

    .btn-cancel:hover {
      color: var(--text);
      border-color: #555;
    }

    .btn-confirm-ok {
      background: var(--accent);
      color: #111;
      border: none;
      border-radius: 10px;
      padding: 9px 20px;
      font-size: 13px;
      font-weight: 700;
      cursor: pointer;
    }

    .btn-confirm-ok:hover {
      opacity: .88;
    }

    .btn-confirm-success {
      background: var(--success);
      color: #111;
      border: none;
      border-radius: 10px;
      padding: 9px 20px;
      font-size: 13px;
      font-weight: 700;
      cursor: pointer;
    }

    .btn-confirm-success:hover {
      opacity: .88;
    }

    .btn-confirm-danger {
      background: var(--danger);
      color: #fff;
      border: none;
      border-radius: 10px;
      padding: 9px 20px;
      font-size: 13px;
      font-weight: 700;
      cursor: pointer;
    }

    .btn-confirm-danger:hover {
      opacity: .88;
    }

    ::-webkit-scrollbar {
      width: 5px;
    }

    ::-webkit-scrollbar-track {
      background: transparent;
    }

    ::-webkit-scrollbar-thumb {
      background: var(--border);
      border-radius: 5px;
    }
  </style>
</head>

<body>

  <button class="mobile-toggle" id="sidebar-toggle">☰</button>
  <?php include "sidebar.php"; ?>

  <main style="margin-left:240px;">

    <div class="card">

      <div class="stats-grid">
        <div class="stat-card">
          <p>Total POs</p>
          <h4><?= count($all_purchase_orders) ?></h4>
          <p>all time</p>
        </div>
        <div class="stat-card">
          <p>Pending</p>
          <h4 style="color:var(--info);"><?= count(array_filter($all_purchase_orders, fn($po) => $po['status'] === "Ordered")) ?></h4>
          <p>awaiting delivery</p>
        </div>
        <div class="stat-card">
          <p>Received</p>
          <h4 style="color:var(--success);"><?= count(array_filter($all_purchase_orders, fn($po) => $po['status'] === "Received")) ?></h4>
          <p>completed</p>
        </div>
        <div class="stat-card">
          <p>Pending Value</p>
          <?php
          $pendingTotal = array_sum(
            array_column(
              array_filter($all_purchase_orders, fn($po) => $po['status'] === "Ordered"),
              'grand_total'
            )
          );
          ?>
          <h4><?= formatCompactCurrency($pendingTotal) ?></h4>
          <p>ordered amount</p>
        </div>
      </div>

      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; flex-wrap:wrap; gap:16px;">
        <h3 style="font-size:16px; font-weight:700; margin:0;">Purchase Orders</h3>

        <!-- Toolbar: search + filter + buttons -->
        <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">

          <!-- Search (with icon + clear) -->
          <div class="search-wrap" style="position:relative; min-width:260px; flex:1 1 260px;">
            <svg class="search-icon" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:var(--text-muted); pointer-events:none;">
              <circle cx="11" cy="11" r="8" />
              <path d="m21 21-4.35-4.35" />
            </svg>
            <form method="GET" id="filterForm" style="margin:0;">
              <input
                type="text"
                name="search"
                id="poSearchInput"
                value="<?= htmlspecialchars($search) ?>"
                placeholder="PO # or supplier..."
                style="width:100%; padding:9px 36px 9px 38px;">
              <?php if ($search !== ''): ?>
                <a href="?" class="search-clear" style="position:absolute; right:10px; top:50%; transform:translateY(-50%); color:var(--text-muted); font-size:16px; text-decoration:none;">×</a>
              <?php endif; ?>
            </form>
          </div>

          <!-- Status dropdown -->
          <select
            name="status"
            class="filter-select"
            form="filterForm"
            style="min-width:180px;">
            <option value="">All Statuses</option>
            <option value="Ordered" <?= $statusFilter === 'Ordered' ? 'selected' : '' ?>>Ordered</option>
            <option value="Received" <?= $statusFilter === 'Received' ? 'selected' : '' ?>>Received</option>
            <option value="Cancelled" <?= $statusFilter === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
          </select>

          <!-- Clear button (only when needed) -->
          <?php if ($search !== '' || $statusFilter !== ''): ?>
            <a href="?" class="btn-cancel" style="padding:9px 16px; font-size:13px;">Clear</a>
          <?php endif; ?>

          <!-- Create button -->
          <button id="openCreatePoModal" class="btn-primary" style="padding:9px 18px;">
            + Create PO
          </button>
        </div>
      </div>

      <?php if ($search || $statusFilter): ?>
        <div style="background:rgba(245,166,35,.08); border:1px solid rgba(245,166,35,.2); border-radius:10px; padding:10px 14px; font-size:13px; color:var(--text-muted); margin-bottom:16px;">
          Showing <strong style="color:var(--text)"><?= $totalPOs ?></strong> order<?= $totalPOs !== 1 ? 's' : '' ?>
          <?php if ($search): ?> matching "<strong style="color:var(--text)"><?= htmlspecialchars($search) ?></strong>"<?php endif; ?>
            <?php if ($statusFilter): ?> with status "<strong style="color:var(--text)"><?= htmlspecialchars($statusFilter) ?></strong>"<?php endif; ?>
        </div>
      <?php endif; ?>

      <?php if (isset($_SESSION['create-success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['create-success'] ?></div>
        <?php unset($_SESSION['create-success']); ?>
      <?php endif; ?>
      <?php if (isset($_SESSION['create-error'])): ?>
        <div class="alert alert-error"><?= $_SESSION['create-error'] ?></div>
        <?php unset($_SESSION['create-error']); ?>
      <?php endif; ?>

      <div style="overflow-x:auto;">
        <table class="data-table">
          <thead>
            <tr>
              <th>PO Number</th>
              <th>Supplier</th>
              <th>Order Date</th>
              <th>Items</th>
              <th>Total</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $itemCounts = [];
            foreach ($purchase_order_items as $poi) {
              $pid = $poi['purchase_order_id'];
              $itemCounts[$pid] = ($itemCounts[$pid] ?? 0) + (int)$poi['quantity'];
            }
            ?>

            <?php if (!empty($purchaseOrders)): ?>
              <?php foreach ($purchaseOrders as $po): ?>
                <?php
                $status = strtolower($po['status']);
                $pillClass = match ($status) {
                  'ordered'  => 'status-ordered',
                  'received' => 'status-received',
                  'cancelled' => 'status-cancelled',
                  default    => 'status-cancelled',
                };
                ?>
                <tr>
                  <td style="font-weight:600;"><?= htmlspecialchars($po['po_number']) ?></td>
                  <td class="muted"><?= htmlspecialchars($po['supplier_name']) ?></td>
                  <td class="muted"><?= date('M j, Y g:i A', strtotime($po['date'])) ?></td>
                  <td class="muted"><?= ($itemCounts[$po['purchase_order_id']] ?? 0) ?> item<?= ($itemCounts[$po['purchase_order_id']] ?? 0) !== 1 ? 's' : '' ?></td>
                  <td>₱<?= number_format($po['grand_total'], 2) ?></td>
                  <td><span class="status-pill <?= $pillClass ?>"><?= ucfirst($status) ?></span></td>
                  <td>
                    <div style="display:flex; gap:6px;">
                      <button class="btn-action view openViewPoModal"
                        data-id="<?= $po['purchase_order_id'] ?>"
                        data-number="<?= htmlspecialchars($po['po_number']) ?>"
                        data-supplier="<?= htmlspecialchars($po['supplier_name']) ?>"
                        data-date="<?= htmlspecialchars($po['date']) ?>"
                        data-status="<?= htmlspecialchars($po['status']) ?>"
                        data-creator="<?= htmlspecialchars($po['username'] ?? 'Unknown') ?>"
                        data-items='<?= json_encode($po['items'] ?? []) ?>'
                        title="View">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                          <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                      </button>

                      <?php if ($status === 'ordered'): ?>
                        <button class="btn-action cancel openCancelPoModal"
                          data-id="<?= $po['purchase_order_id'] ?>"
                          data-number="<?= htmlspecialchars($po['po_number']) ?>"
                          title="Cancel">
                          <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                          </svg>
                        </button>

                        <button class="btn-action receive openReceivePoModal"
                          data-id="<?= $po['purchase_order_id'] ?>"
                          data-number="<?= htmlspecialchars($po['po_number']) ?>"
                          title="Receive">
                          <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                          </svg>
                        </button>
                      <?php elseif ($status !== 'archived'): ?>
                        <button class="btn-action archive openArchivePoModal"
                          data-id="<?= $po['purchase_order_id'] ?>"
                          data-number="<?= htmlspecialchars($po['po_number']) ?>"
                          title="Archive">
                          <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                          </svg>
                        </button>
                      <?php endif; ?>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="7" style="text-align:center; padding:60px 20px; color:var(--text-muted); font-size:15px;">
                  <?= $search || $statusFilter ? 'No matching purchase orders.' : 'No purchase orders found.' ?>
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <?php if ($totalPages > 1):
        $q = http_build_query(array_filter([
          'search' => $search ?: null,
          'status' => $statusFilter ?: null,
        ]));
        $q = $q ? '&' . $q : '';
      ?>
        <div class="pagination">
          <div style="font-size:12px; color:var(--text-muted);">
            Page <strong style="color:var(--text)"><?= $page ?></strong> of <strong style="color:var(--text)"><?= $totalPages ?></strong>
          </div>
          <div style="display:flex; align-items:center; gap:4px; flex-wrap:wrap;">
            <?php if ($page > 1): ?>
              <a href="?page=<?= $page - 1 ?><?= $q ?>" class="page-btn">‹ Prev</a>
            <?php else: ?>
              <span class="page-btn disabled">‹ Prev</span>
            <?php endif; ?>

            <?php
            $range = 2;
            $start = max(1, $page - $range);
            $end   = min($totalPages, $page + $range);
            if ($start > 1): ?>
              <a href="?page=1<?= $q ?>" class="page-btn">1</a>
              <?php if ($start > 2): ?><span class="page-ellipsis">…</span><?php endif; ?>
            <?php endif; ?>

            <?php for ($i = $start; $i <= $end; $i++): ?>
              <?php if ($i === $page): ?>
                <span class="page-btn active"><?= $i ?></span>
              <?php else: ?>
                <a href="?page=<?= $i ?><?= $q ?>" class="page-btn"><?= $i ?></a>
              <?php endif; ?>
            <?php endfor; ?>

            <?php if ($end < $totalPages): ?>
              <?php if ($end < $totalPages - 1): ?><span class="page-ellipsis">…</span><?php endif; ?>
              <a href="?page=<?= $totalPages ?><?= $q ?>" class="page-btn"><?= $totalPages ?></a>
            <?php endif; ?>

            <?php if ($page < $totalPages): ?>
              <a href="?page=<?= $page + 1 ?><?= $q ?>" class="page-btn">Next ›</a>
            <?php else: ?>
              <span class="page-btn disabled">Next ›</span>
            <?php endif; ?>
          </div>
        </div>
      <?php endif; ?>

    </div>
  </main>

  <!-- Create PO Modal -->
  <div id="createPoModal" class="modal-overlay">
    <div class="modal-box">
      <h3>Create Purchase Order</h3>

      <form method="POST" id="purchaseOrderForm">
        <input type="hidden" name="status" value="Ordered">

        <div class="form-group">
          <label>Supplier *</label>
          <select name="supplier_id" required class="form-select">
            <option value="">Select supplier</option>
            <?php foreach ($suppliers as $s): ?>
              <option value="<?= $s['supplier_id'] ?>"><?= htmlspecialchars($s['supplier_name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label>Items</label>
          <div id="itemsContainer">
            <div class="item-row">
              <select name="item_id[]" required class="form-select">
                <option value="">Select item</option>
                <?php foreach ($items as $it): ?>
                  <option value="<?= $it['item_id'] ?>"><?= htmlspecialchars($it['item_name']) ?></option>
                <?php endforeach; ?>
              </select>
              <input type="number" name="quantity[]" min="1" required value="1" class="form-input">
              <button type="button" class="remove-btn">×</button>
            </div>
          </div>
          <button type="button" id="addItemRow" class="btn-cancel mt-3 px-4 py-2 text-sm">+ Add Item</button>
        </div>

        <div class="modal-actions">
          <button type="button" class="btn-cancel" id="cancelCreatePo">Cancel</button>
          <button type="submit" name="create_purchase_order" class="btn-confirm-ok">Create PO</button>
        </div>
      </form>
    </div>
  </div>

  <!-- View PO Modal -->
  <div id="viewPoModal" class="modal-overlay">
    <div class="modal-box" style="max-width:720px;">
      <h3 id="viewPoTitle"></h3>

      <div style="margin:16px 0; font-size:13.5px; line-height:1.5;">
        <div><strong>PO Number:</strong> <span id="viewPoNumber"></span></div>
        <div><strong>Date:</strong> <span id="viewPoDate"></span></div>
        <div><strong>Supplier:</strong> <span id="viewPoSupplier"></span></div>
        <div><strong>Status:</strong> <span id="viewPoStatus" class="status-pill"></span></div>
        <div><strong>Created by:</strong> <span id="viewPoCreator"></span></div>
      </div>

      <div style="overflow-x:auto; margin:20px 0;">
        <table class="data-table">
          <thead>
            <tr>
              <th>Item</th>
              <th>Quantity</th>
              <th>Unit Cost</th>
              <th>Line Total</th>
            </tr>
          </thead>
          <tbody id="viewPoItems"></tbody>
        </table>
      </div>

      <div class="modal-actions">
        <button type="button" class="btn-cancel" id="closeViewPo">Close</button>
      </div>
    </div>
  </div>

  <!-- Cancel Confirmation Modal -->
  <div id="cancelPoModal" class="modal-overlay">
    <div class="modal-box" style="max-width:420px;">
      <h3>Cancel Purchase Order</h3>
      <p style="margin:20px 0;">
        Are you sure you want to cancel PO <strong id="cancelPoNumber" style="color:var(--text)"></strong>?<br>
        This cannot be undone.
      </p>
      <form method="POST">
        <input type="hidden" name="cancel_po_id" id="cancelPoId">
        <div class="modal-actions">
          <button type="button" class="btn-cancel" id="closeCancelPo">Cancel</button>
          <button type="submit" name="cancel_po" class="btn-confirm-danger">Confirm Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Receive Confirmation Modal -->
  <div id="receivePoModal" class="modal-overlay">
    <div class="modal-box" style="max-width:420px;">
      <h3>Confirm Receipt</h3>
      <p style="margin:20px 0;">
        Has PO <strong id="receivePoNumber" style="color:var(--text)"></strong> been fully received?<br>
        This will update stock levels.
      </p>
      <form method="POST">
        <input type="hidden" name="receive_po_id" id="receivePoId">
        <div class="modal-actions">
          <button type="button" class="btn-cancel" id="closeReceivePo">Cancel</button>
          <button type="submit" name="receive_po" class="btn-confirm-success">Mark as Received</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Archive Confirmation Modal -->
  <div id="archivePoModal" class="modal-overlay">
    <div class="modal-box" style="max-width:420px;">
      <h3>Archive Purchase Order</h3>
      <p style="margin:20px 0;">
        Archive PO <strong id="archivePoNumber" style="color:var(--text)"></strong>?<br>
        This can be undone later.
      </p>
      <form method="POST">
        <input type="hidden" name="purchase_order_id" id="archivePoId">
        <div class="modal-actions">
          <button type="button" class="btn-cancel" id="closeArchivePo">Cancel</button>
          <button type="submit" name="archive_purchase_order" class="btn-confirm-warning">Confirm Archive</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    // Modal helpers
    function openModal(id) {
      document.getElementById(id).classList.add('active');
    }

    function closeModal(id) {
      document.getElementById(id).classList.remove('active');
    }

    document.querySelectorAll('.modal-overlay').forEach(m => {
      m.addEventListener('click', e => {
        if (e.target === m) closeModal(m.id);
      });
    });

    // Create PO
    document.getElementById('openCreatePoModal')?.addEventListener('click', () => openModal('createPoModal'));
    document.getElementById('cancelCreatePo')?.addEventListener('click', () => closeModal('createPoModal'));

    // Add/remove item rows
    const itemsContainer = document.getElementById('itemsContainer');
    document.getElementById('addItemRow')?.addEventListener('click', () => {
      const row = document.createElement('div');
      row.className = 'item-row';
      row.innerHTML = `
                <select name="item_id[]" required class="form-select">
                    <option value="">Select item</option>
                    <?php foreach ($items as $it): ?>
                        <option value="<?= $it['item_id'] ?>"><?= htmlspecialchars($it['item_name'], ENT_QUOTES) ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="number" name="quantity[]" min="1" required value="1" class="form-input">
                <button type="button" class="remove-btn">×</button>
            `;
      itemsContainer.appendChild(row);

      row.querySelector('.remove-btn').onclick = () => row.remove();
    });

    // View PO
    document.querySelectorAll('.openViewPoModal').forEach(btn => {
      btn.addEventListener('click', () => {
        const data = btn.dataset;
        document.getElementById('viewPoTitle').textContent = `PO ${data.number} - ${data.supplier}`;
        document.getElementById('viewPoNumber').textContent = data.number;
        document.getElementById('viewPoDate').textContent = new Date(data.date).toLocaleString('en-US', {
          year: 'numeric',
          month: 'long',
          day: 'numeric',
          hour: 'numeric',
          minute: '2-digit',
          hour12: true
        });
        document.getElementById('viewPoSupplier').textContent = data.supplier;

        const statusEl = document.getElementById('viewPoStatus');
        statusEl.textContent = data.status;
        statusEl.className = 'status-pill ' + {
          'Ordered': 'status-ordered',
          'Received': 'status-received',
          'Cancelled': 'status-cancelled'
        } [data.status] || '';

        document.getElementById('viewPoCreator').textContent = data.creator;

        const tbody = document.getElementById('viewPoItems');
        tbody.innerHTML = '';

        let items = [];
        try {
          items = JSON.parse(data.items || '[]');
        } catch (e) {}

        if (!items.length) {
          tbody.innerHTML = '<tr><td colspan="4" style="text-align:center; padding:20px; color:var(--text-muted);">No items</td></tr>';
        } else {
          items.forEach(it => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                            <td>${it.item_name}</td>
                            <td>${Number(it.quantity).toLocaleString()}</td>
                            <td>₱${Number(it.unit_cost).toLocaleString('en-US', {minimumFractionDigits:2})}</td>
                            <td>₱${Number(it.line_total).toLocaleString('en-US', {minimumFractionDigits:2})}</td>
                        `;
            tbody.appendChild(tr);
          });
        }

        openModal('viewPoModal');
      });
    });
    document.getElementById('closeViewPo')?.addEventListener('click', () => closeModal('viewPoModal'));

    // Cancel PO
    document.querySelectorAll('.openCancelPoModal').forEach(btn => {
      btn.addEventListener('click', () => {
        document.getElementById('cancelPoId').value = btn.dataset.id;
        document.getElementById('cancelPoNumber').textContent = btn.dataset.number;
        openModal('cancelPoModal');
      });
    });
    document.getElementById('closeCancelPo')?.addEventListener('click', () => closeModal('cancelPoModal'));

    // Receive PO
    document.querySelectorAll('.openReceivePoModal').forEach(btn => {
      btn.addEventListener('click', () => {
        document.getElementById('receivePoId').value = btn.dataset.id;
        document.getElementById('receivePoNumber').textContent = btn.dataset.number;
        openModal('receivePoModal');
      });
    });
    document.getElementById('closeReceivePo')?.addEventListener('click', () => closeModal('receivePoModal'));

    // Archive PO
    document.querySelectorAll('.openArchivePoModal').forEach(btn => {
      btn.addEventListener('click', () => {
        document.getElementById('archivePoId').value = btn.dataset.id;
        document.getElementById('archivePoNumber').textContent = btn.dataset.number;
        openModal('archivePoModal');
      });
    });
    document.getElementById('closeArchivePo')?.addEventListener('click', () => closeModal('archivePoModal'));

    // Live filters
    let timeout;
    document.getElementById('poSearchInput')?.addEventListener('input', function() {
      clearTimeout(timeout);
      timeout = setTimeout(() => this.closest('form').submit(), 450);
    });
    document.querySelector('.filter-select')?.addEventListener('change', e => e.target.closest('form').submit());

    // Sidebar sync
    function syncMargin() {
      const collapsed = document.getElementById('main-sidebar')?.classList.contains('collapsed');
      document.querySelector('main').style.marginLeft = collapsed ? '64px' : '240px';
    }
    document.getElementById('sidebarCollapseBtn')?.addEventListener('click', () => setTimeout(syncMargin, 80));
    syncMargin();

    // Auto-dismiss alerts
    document.querySelectorAll('.alert').forEach(el => {
      setTimeout(() => el.style.opacity = '0', 3200);
      setTimeout(() => el.remove(), 4000);
    });
  </script>

</body>

</html>