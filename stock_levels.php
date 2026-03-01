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

$low_stock    = count(array_filter($all_items, fn($i) => $i['quantity'] > 0 && $i['quantity'] <= $i['min_stock']));
$out_of_stock = count(array_filter($all_items, fn($i) => $i['quantity'] <= 0));
$total_value  = array_sum(array_map(fn($i) => $i['selling_price'] * $i['quantity'], $all_items));

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
  <title>POS & Inventory - Stock Levels</title>
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

    .status-out {
      background: rgba(255, 92, 92, .18);
      color: var(--danger);
    }

    .status-low {
      background: rgba(245, 166, 35, .18);
      color: var(--warning);
    }

    .status-in {
      background: rgba(67, 211, 146, .18);
      color: var(--success);
    }

    .btn-action {
      background: var(--accent);
      color: #111;
      border: none;
      border-radius: 8px;
      padding: 7px 14px;
      font-size: 12.5px;
      font-weight: 600;
      cursor: pointer;
      transition: opacity .2s;
    }

    .btn-action:hover {
      opacity: .9;
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

    .recent-list {
      margin-top: 12px;
    }

    .recent-item {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      padding: 12px 0;
      border-bottom: 1px solid var(--border);
    }

    .recent-item:last-child {
      border-bottom: none;
    }

    .recent-item .info p:first-child {
      font-weight: 600;
      font-size: 13.5px;
    }

    .recent-item .info p:nth-child(2) {
      font-size: 12px;
      color: var(--text-muted);
      margin-top: 2px;
    }

    .recent-item .info p:nth-child(3) {
      font-size: 11.5px;
      color: var(--text-muted);
      margin-top: 4px;
    }

    .recent-item .change {
      text-align: right;
      min-width: 80px;
    }

    .change-value {
      font-size: 17px;
      font-weight: 700;
    }

    .change-positive {
      color: var(--success);
    }

    .change-negative {
      color: var(--danger);
    }

    .change-time {
      font-size: 11px;
      color: var(--text-muted);
      margin-top: 4px;
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
      width: 460px;
      max-width: 92%;
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
    .form-textarea:focus {
      border-color: var(--accent);
    }

    .qty-adjust-row {
      display: flex;
      align-items: center;
      gap: 8px;
      margin-top: 6px;
    }

    .qty-btn {
      width: 38px;
      height: 38px;
      background: var(--surface2);
      border: 1.5px solid var(--border);
      border-radius: 10px;
      font-size: 18px;
      font-weight: 600;
      color: var(--text);
      cursor: pointer;
      transition: all .15s;
    }

    .qty-btn:hover {
      background: var(--accent);
      color: #111;
      border-color: var(--accent);
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

    /* Remove arrows in Chrome, Edge, Safari */
    input[type=number]::-webkit-outer-spin-button,
    input[type=number]::-webkit-inner-spin-button {
      -webkit-appearance: none;
      margin: 0;
    }

    /* Remove arrows in Firefox */
    input[type=number] {
      -moz-appearance: textfield;
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
          <p>Total Items</p>
          <h4><?= count($all_items) ?></h4>
          <p>products in catalog</p>
        </div>
        <div class="stat-card">
          <p>Low Stock</p>
          <h4 style="color:var(--warning)"><?= $low_stock ?></h4>
          <p>need reordering</p>
        </div>
        <div class="stat-card">
          <p>Out of Stock</p>
          <h4 style="color:var(--danger)"><?= $out_of_stock ?></h4>
          <p>urgent action required</p>
        </div>
        <div class="stat-card">
          <p>Inventory Value</p>
          <h4><?= formatCompactCurrency($total_value) ?></h4>
          <p>estimated total value</p>
        </div>
      </div>

      <div style="display:grid; grid-template-columns:1fr 360px; gap:24px;">
        <div>

          <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:12px;">
            <h3 style="font-size:16px; font-weight:700;">Inventory Levels</h3>

            <div class="search-wrap">
              <svg class="search-icon" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="11" cy="11" r="8" />
                <path d="m21 21-4.35-4.35" />
              </svg>
              <form method="GET">
                <input type="text" name="search" id="searchInput" value="<?= htmlspecialchars($search) ?>" placeholder="Search name or barcode...">
                <?php if ($search !== ''): ?>
                  <a href="?" class="search-clear">×</a>
                <?php endif; ?>
              </form>
            </div>
          </div>

          <?php if (isset($_SESSION['adjust-success'])): ?>
            <div class="alert alert-success"><?= $_SESSION['adjust-success'] ?></div>
            <?php unset($_SESSION['adjust-success']); ?>
          <?php endif; ?>

          <?php if (isset($_SESSION['adjust-error'])): ?>
            <div class="alert alert-error"><?= $_SESSION['adjust-error'] ?></div>
            <?php unset($_SESSION['adjust-error']); ?>
          <?php endif; ?>

          <?php if ($search !== ''): ?>
            <div style="background:rgba(245,166,35,.08); border:1px solid rgba(245,166,35,.2); border-radius:10px; padding:10px 14px; font-size:13px; color:var(--text-muted); margin-bottom:16px;">
              Results for "<strong style="color:var(--text)"><?= htmlspecialchars($search) ?></strong>" — <?= $totalItems ?> item<?= $totalItems !== 1 ? 's' : '' ?>
              <a href="?" style="color:var(--accent); margin-left:12px; font-weight:600; text-decoration:none;">Clear</a>
            </div>
          <?php endif; ?>

          <p style="font-size:13px; color:var(--text-muted); margin-bottom:12px;">
            Ordered by priority: Out of Stock → Low Stock → In Stock
          </p>

          <div style="overflow-x:auto;">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Product</th>
                  <th>Current Stock</th>
                  <th>Min Stock</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!empty($items)): ?>
                  <?php foreach ($items as $item): ?>
                    <?php
                    $qty = (int)$item['quantity'];
                    $min = (int)$item['min_stock'];
                    if ($qty <= 0)     $status = ['Out of Stock', 'status-out'];
                    elseif ($qty <= $min)  $status = ['Low Stock',   'status-low'];
                    else                   $status = ['In Stock',     'status-in'];
                    ?>
                    <tr>
                      <td>
                        <div style="font-weight:600;"><?= htmlspecialchars($item['item_name']) ?></div>
                        <div style="color:var(--text-muted); font-size:12px; margin-top:2px;">
                          <?= htmlspecialchars($item['barcode'] ?: '—') ?>
                        </div>
                      </td>
                      <td style="font-weight:600;"><?= $qty ?></td>
                      <td style="color:var(--text-muted);">min: <?= $min ?></td>
                      <td><span class="status-pill <?= $status[1] ?>"><?= $status[0] ?></span></td>
                      <td>
                        <button class="btn-action adjustBtn"
                          data-id="<?= $item['item_id'] ?>"
                          data-name="<?= htmlspecialchars($item['item_name']) ?>"
                          data-qty="<?= $qty ?>">
                          Adjust
                        </button>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="5" style="text-align:center; padding:60px 20px; color:var(--text-muted); font-size:15px;">
                      <?= $search ? 'No matching items found.' : 'No stock items to display.' ?>
                    </td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>

          <?php if ($totalPages > 1):
            $q = $search ? '&search=' . urlencode($search) : '';
          ?>
            <div class="pagination">
              <div style="font-size:12px; color:var(--text-muted);">
                Page <strong style="color:var(--text)"><?= $page ?></strong> of <strong style="color:var(--text)"><?= $totalPages ?></strong>
              </div>
              <div style="display:flex; gap:4px; flex-wrap:wrap;">
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
                  <?php if ($start > 2): ?><span style="padding:6px 8px; color:var(--text-muted);">…</span><?php endif; ?>
                <?php endif; ?>

                <?php for ($i = $start; $i <= $end; $i++): ?>
                  <?php if ($i === $page): ?>
                    <span class="page-btn active"><?= $i ?></span>
                  <?php else: ?>
                    <a href="?page=<?= $i ?><?= $q ?>" class="page-btn"><?= $i ?></a>
                  <?php endif; ?>
                <?php endfor; ?>

                <?php if ($end < $totalPages): ?>
                  <?php if ($end < $totalPages - 1): ?><span style="padding:6px 8px; color:var(--text-muted);">…</span><?php endif; ?>
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

        <!-- Recent Adjustments Sidebar -->
        <div style="background:var(--surface); border:1.5px solid var(--border); border-radius:14px; padding:20px;">
          <h4 style="font-size:15px; font-weight:700; margin-bottom:12px;">Recent Adjustments</h4>
          <p style="font-size:12.5px; color:var(--text-muted); margin-bottom:16px;">Latest stock changes</p>

          <div class="recent-list">
            <?php foreach (array_slice($stock_adjustment, 0, 10) as $sa): ?>
              <?php
              $delta = $sa['new_quantity'] - $sa['previous_quantity'];
              $class = $delta >= 0 ? 'change-positive' : 'change-negative';
              $sign  = $delta > 0 ? '+' : '';
              ?>
              <div class="recent-item">
                <div class="info">
                  <p><?= htmlspecialchars($sa['item_name']) ?></p>
                  <p><?= htmlspecialchars($sa['reason_adjustment']) ?></p>
                  <p>by <?= htmlspecialchars($sa['username']) ?></p>
                </div>
                <div class="change">
                  <div class="change-value <?= $class ?>"><?= $sign . $delta ?></div>
                  <div class="change-time"><?= date('M j, g:i A', strtotime($sa['created_at'])) ?></div>
                </div>
              </div>
            <?php endforeach; ?>

            <?php if (empty($stock_adjustment)): ?>
              <div style="text-align:center; color:var(--text-muted); padding:30px 0; font-size:13.5px;">
                No adjustments recorded yet.
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

    </div>
  </main>

  <!-- Stock Adjustment Modal -->
  <div id="adjustModal" class="modal-overlay">
    <div class="modal-box">
      <h3>Adjust Stock Level</h3>

      <form method="POST" id="adjustForm">
        <input type="hidden" name="item_id" id="modalItemId">

        <div class="form-group">
          <label>Item</label>
          <div style="font-size:14px; font-weight:600;" id="modalItemName"></div>
        </div>

        <div class="form-group">
          <label>Current Quantity</label>
          <div style="font-size:15px; font-weight:600;" id="modalCurrentQty"></div>
        </div>

        <div class="form-group">
          <label>Adjustment Quantity</label>
          <div class="qty-adjust-row">
            <button type="button" class="qty-btn" id="decrementBtn">−</button>
            <input type="number" id="adjustQty" name="adjust_qty" required min="-9999" max="9999" step="1" value="1" class="form-input" style="text-align:center;">
            <button type="button" class="qty-btn" id="incrementBtn">+</button>
          </div>
        </div>

        <div class="form-group">
          <label>Reason for Adjustment</label>
          <textarea name="reason_adjustment" required rows="3" class="form-textarea" placeholder="e.g. Damaged items, received shipment, miscount..."></textarea>
        </div>

        <div class="modal-actions">
          <button type="button" class="btn-cancel" id="cancelAdjust">Cancel</button>
          <button type="submit" name="adjust_stock_submit" class="btn-confirm-ok">Confirm Adjustment</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    // Modal controls
    const adjustModal = document.getElementById('adjustModal');

    function openAdjustModal() {
      adjustModal.classList.add('active');
    }

    function closeAdjustModal() {
      adjustModal.classList.remove('active');
    }

    adjustModal.addEventListener('click', e => {
      if (e.target === adjustModal) closeAdjustModal();
    });
    document.getElementById('cancelAdjust')?.addEventListener('click', closeAdjustModal);

    // Open modal from buttons
    document.querySelectorAll('.adjustBtn').forEach(btn => {
      btn.addEventListener('click', () => {
        document.getElementById('modalItemId').value = btn.dataset.id;
        document.getElementById('modalItemName').textContent = btn.dataset.name;
        document.getElementById('modalCurrentQty').textContent = btn.dataset.qty;
        document.getElementById('adjustQty').value = 1;
        document.getElementById('adjustForm').reset();
        openAdjustModal();
      });
    });

    // + / − buttons logic + skip 0
    const qtyInput = document.getElementById('adjustQty');
    const decrementBtn = document.getElementById('decrementBtn');
    const incrementBtn = document.getElementById('incrementBtn');

    // Helper: set value and jump over 0
    function setQtySafe(newValue) {
      let num = Number(newValue);

      // If result would be 0 → jump to -1 when decreasing, +1 when increasing
      if (num === 0) {
        // Determine direction from previous value
        let prev = Number(qtyInput.value) || 1;
        num = (newValue < prev) ? -1 : 1;
      }

      // Clamp to reasonable range
      num = Math.max(-9999, Math.min(9999, num));

      qtyInput.value = num;
    }

    // Decrement
    decrementBtn?.addEventListener('click', () => {
      let current = Number(qtyInput.value) || 1;
      setQtySafe(current - 1);
    });

    // Increment
    incrementBtn?.addEventListener('click', () => {
      let current = Number(qtyInput.value) || 1;
      setQtySafe(current + 1);
    });

    qtyInput?.addEventListener('input', (e) => {
      let val = e.target.value.trim();

      if (val === '' || val === '-') return;

      let num = Number(val);

      if (num === 0 || isNaN(num)) {
        let prev = Number(qtyInput.dataset.lastValue || '1');
        e.target.value = (num < prev || num === 0) ? '-1' : '1';
      } else {
        qtyInput.dataset.lastValue = num;
      }
    });

    qtyInput?.addEventListener('blur', () => {
      let val = qtyInput.value.trim();
      if (val === '' || val === '-' || Number(val) === 0) {
        qtyInput.value = '1';
      }
    });
    qtyInput.value = '1';

    // Live search
    let timeout;
    document.getElementById('searchInput')?.addEventListener('input', function() {
      clearTimeout(timeout);
      timeout = setTimeout(() => this.closest('form').submit(), 480);
    });

    // Sidebar margin sync (if collapsible sidebar exists)
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