<?php
include "database/database.php";
$database->login_session();
$database->admin_session();
$database->create_supplier();
$database->update_supplier();
$database->archive_supplier();

$perPage = 5;
$search  = trim($_GET['search'] ?? '');
$page    = max(1, (int) ($_GET['page'] ?? 1));

$offset = ($page - 1) * $perPage;

$totalSuppliers = $database->getTotalSuppliersCount($search);
$totalPages = max(1, ceil($totalSuppliers / $perPage));

$suppliers = $database->select_suppliers_paginated($offset, $perPage, $search);
$purchase_orders = $database->select_purchase_orders();

$allSuppliers = $database->select_all_suppliers_for_stats();
$grandTotal = array_sum(array_column($allSuppliers, 'total_spent'));
$active_count = count(array_filter($allSuppliers, fn($sp) => $sp['status'] == 1));
$inactive_count = count(array_filter($allSuppliers, fn($sp) => $sp['status'] == 0));

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
  <title>POS & Inventory - Suppliers</title>
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
      white-space: nowrap;
    }

    .btn-primary:hover {
      opacity: .88;
    }

    .search-wrap {
      position: relative;
      min-width: 260px;
      flex: 1 1 260px;
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

    .status-active {
      background: rgba(67, 211, 146, .18);
      color: var(--success);
    }

    .status-inactive {
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
      color: #60a5fa;
      background: rgba(96, 165, 250, .12);
    }

    .btn-action:hover.edit {
      color: var(--success);
      background: rgba(67, 211, 146, .12);
    }

    .btn-action:hover.archive {
      color: var(--danger);
      background: rgba(255, 92, 92, .12);
    }

    .actions-wrap {
      display: flex;
      flex-direction: row;
      align-items: center;
      justify-content: flex-start;
      gap: 8px;
      flex-wrap: nowrap;
      min-width: 110px;
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

    .recent-list,
    .top-list {
      margin-top: 12px;
    }

    .recent-item,
    .top-item {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      padding: 12px 0;
      border-bottom: 1px solid var(--border);
    }

    .recent-item:last-child,
    .top-item:last-child {
      border-bottom: none;
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
      width: 480px;
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
    .form-select {
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
    .form-select:focus {
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
  </style>
</head>

<body>

  <button class="mobile-toggle" id="sidebar-toggle">☰</button>
  <?php include "sidebar.php"; ?>

  <main style="margin-left:240px;">

    <div class="card">

      <div class="stats-grid">
        <div class="stat-card">
          <p>Total Suppliers</p>
          <h4><?= count($allSuppliers) ?></h4>
          <p>registered</p>
        </div>
        <div class="stat-card">
          <p>Total Spent</p>
          <h4><?= formatCompactCurrency($grandTotal) ?></h4>
          <p>all time purchases</p>
        </div>
        <div class="stat-card">
          <p>Active</p>
          <h4 style="color:var(--success);"><?= $active_count ?></h4>
          <p>currently active</p>
        </div>
        <div class="stat-card">
          <p>Inactive</p>
          <h4 style="color:var(--danger);"><?= $inactive_count ?></h4>
          <p>currently inactive</p>
        </div>
      </div>

      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; flex-wrap:wrap; gap:16px;">
        <h3 style="font-size:16px; font-weight:700; margin:0;">Supplier Database</h3>

        <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
          <div class="search-wrap">
            <svg class="search-icon" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <circle cx="11" cy="11" r="8" />
              <path d="m21 21-4.35-4.35" />
            </svg>
            <form method="GET" id="filterForm" style="margin:0;">
              <input type="text" name="search" id="searchInput" value="<?= htmlspecialchars($search) ?>" placeholder="Search by name...">
              <?php if ($search !== ''): ?>
                <a href="?" class="search-clear">×</a>
              <?php endif; ?>
            </form>
          </div>

          <button id="openAddSupplierModal" class="btn-primary">+ Add Supplier</button>
        </div>
      </div>

      <?php if ($search !== ''): ?>
        <div style="background:rgba(245,166,35,.08); border:1px solid rgba(245,166,35,.2); border-radius:10px; padding:10px 14px; font-size:13px; color:var(--text-muted); margin-bottom:16px;">
          Results for "<strong style="color:var(--text)"><?= htmlspecialchars($search) ?></strong>" — <?= $totalSuppliers ?> supplier<?= $totalSuppliers !== 1 ? 's' : '' ?>
          <a href="?" style="color:var(--accent); margin-left:12px; font-weight:600;">Clear</a>
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
              <th>Supplier</th>
              <th>Orders</th>
              <th>Total Spent</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($suppliers)): ?>
              <?php foreach ($suppliers as $sp): ?>
                <tr>
                  <td style="font-weight:600;"><?= htmlspecialchars($sp['supplier_name']) ?></td>
                  <td>
                    <?php  ?>
                    <div style="font-weight:600;"><?= $sp['order_count'] > 1 ? $sp['order_count'] . ' orders' : $sp['order_count'] . ' order' ?></div>
                    <div class="muted" style="font-size:12px; margin-top:2px;">
                      <?= $sp['last_order_date'] ? 'Last: ' . date('M j, Y g:i A', strtotime($sp['last_order_date'])) : '' ?>
                    </div>
                  </td>
                  <td>₱<?= number_format($sp['total_spent'], 2) ?></td>
                  <td>
                    <span class="status-pill <?= $sp['status'] == 1 ? 'status-active' : 'status-inactive' ?>">
                      <?= $sp['status'] == 1 ? 'Active' : 'Inactive' ?>
                    </span>
                  </td>
                  <td>
                    <div class="actions-wrap">
                      <button class="btn-action view openViewSupplierModal"
                        data-id="<?= $sp['supplier_id'] ?>"
                        data-name="<?= htmlspecialchars($sp['supplier_name']) ?>"
                        data-contact="<?= htmlspecialchars($sp['contact_number'] ?? '') ?>"
                        data-email="<?= htmlspecialchars($sp['email'] ?? '') ?>"
                        data-status="<?= $sp['status'] ?>"
                        title="View">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                          <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                      </button>

                      <button class="btn-action edit openEditSupplierModal"
                        data-id="<?= $sp['supplier_id'] ?>"
                        data-name="<?= htmlspecialchars($sp['supplier_name']) ?>"
                        data-contact="<?= htmlspecialchars($sp['contact_number'] ?? '') ?>"
                        data-email="<?= htmlspecialchars($sp['email'] ?? '') ?>"
                        data-status="<?= $sp['status'] ?>"
                        title="Edit">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                      </button>

                      <button class="btn-action archive openArchiveSupplierModal"
                        data-id="<?= $sp['supplier_id'] ?>"
                        data-name="<?= htmlspecialchars($sp['supplier_name']) ?>"
                        title="Archive">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                        </svg>
                      </button>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="5" style="text-align:center; padding:60px 20px; color:var(--text-muted); font-size:15px;">
                  <?= $search ? 'No matching suppliers found.' : 'No suppliers registered yet.' ?>
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

    <!-- Recent Orders & Top Suppliers -->
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:24px; margin-top:32px;">
      <!-- Recent Orders -->
      <div style="background:var(--surface); border:1.5px solid var(--border); border-radius:14px; padding:20px;">
        <h4 style="font-size:15px; font-weight:700; margin-bottom:12px;">Recent Orders</h4>
        <p style="font-size:12.5px; color:var(--text-muted); margin-bottom:16px;">Latest purchase activity</p>

        <div class="recent-list">
          <?php
          $recent = array_filter($purchase_orders, fn($po) => in_array($po['status'], ['Received', 'Ordered']));
          $recent = array_slice($recent, 0, 5);
          ?>
          <?php if ($recent): ?>
            <?php foreach ($recent as $po): ?>
              <div class="recent-item">
                <div>
                  <p style="font-weight:600;"><?= htmlspecialchars($po['po_number']) ?></p>
                  <p style="color:var(--text-muted); font-size:12px; margin-top:2px;"><?= htmlspecialchars($po['supplier_name']) ?></p>
                </div>
                <div style="text-align:right;">
                  <div style="font-weight:600;">₱<?= number_format($po['grand_total'], 2) ?></div>
                  <span class="status-pill <?= $po['status'] === 'Received' ? 'status-active' : 'status-ordered' ?>">
                    <?= $po['status'] ?>
                  </span>
                  <div style="color:var(--text-muted); font-size:11px; margin-top:4px;">
                    <?= date('M j, Y', strtotime($po['date'])) ?>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div style="text-align:center; color:var(--text-muted); padding:30px 0;">
              No recent orders yet.
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Top Suppliers -->
      <div style="background:var(--surface); border:1.5px solid var(--border); border-radius:14px; padding:20px;">
        <h4 style="font-size:15px; font-weight:700; margin-bottom:12px;">Top Suppliers</h4>
        <p style="font-size:12.5px; color:var(--text-muted); margin-bottom:16px;">By total spending</p>

        <div class="top-list">
          <?php
          usort($allSuppliers, fn($a, $b) => $b['total_spent'] <=> $a['total_spent']);
          $top = array_slice($allSuppliers, 0, 5);
          ?>
          <?php if ($top): ?>
            <?php foreach ($top as $i => $sp): ?>
              <div class="top-item">
                <div style="display:flex; align-items:center; gap:10px;">
                  <span style="font-size:15px; font-weight:700; color:var(--text-muted); min-width:24px;"><?= $i + 1 ?>.</span>
                  <span style="font-weight:600;"><?= htmlspecialchars($sp['supplier_name']) ?></span>
                </div>
                <span style="font-weight:600;">₱<?= number_format($sp['total_spent'], 0) ?></span>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div style="text-align:center; color:var(--text-muted); padding:30px 0;">
              No spending data yet.
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

  </main>

  <!-- Add Supplier Modal -->
  <div id="addSupplierModal" class="modal-overlay">
    <div class="modal-box">
      <h3>Add New Supplier</h3>

      <form method="POST">
        <div class="form-group">
          <label>Supplier Name *</label>
          <input type="text" name="supplier_name" required class="form-input" placeholder="e.g. NZXT Philippines">
        </div>
        <div class="form-group">
          <label>Contact Number</label>
          <input type="text" name="contact_number" class="form-input" placeholder="+63 912 345 6789">
        </div>
        <div class="form-group">
          <label>Email Address</label>
          <input type="email" name="email" class="form-input" placeholder="contact@supplier.com">
        </div>
        <div class="form-group">
          <label>Status</label>
          <select name="status" class="form-select">
            <option value="1" selected>Active</option>
            <option value="0">Inactive</option>
          </select>
        </div>

        <div class="modal-actions">
          <button type="button" class="btn-cancel" id="cancelAddSupplier">Cancel</button>
          <button type="submit" name="create_supplier" class="btn-confirm-ok">Add Supplier</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Edit Supplier Modal -->
  <div id="editSupplierModal" class="modal-overlay">
    <div class="modal-box">
      <h3>Edit Supplier</h3>

      <form method="POST">
        <input type="hidden" name="supplier_id" id="edit_supplier_id">

        <div class="form-group">
          <label>Supplier Name *</label>
          <input type="text" name="supplier_name" id="edit_supplier_name" required class="form-input">
        </div>
        <div class="form-group">
          <label>Contact Number</label>
          <input type="text" name="contact_number" id="edit_contact_number" class="form-input">
        </div>
        <div class="form-group">
          <label>Email Address</label>
          <input type="email" name="email" id="edit_email" class="form-input">
        </div>
        <div class="form-group">
          <label>Status</label>
          <select name="status" id="edit_status" class="form-select">
            <option value="1">Active</option>
            <option value="0">Inactive</option>
          </select>
        </div>

        <div class="modal-actions">
          <button type="button" class="btn-cancel" id="cancelEditSupplier">Cancel</button>
          <button type="submit" name="update_supplier" class="btn-confirm-ok">Save Changes</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Archive Confirmation Modal -->
  <div id="archiveSupplierModal" class="modal-overlay">
    <div class="modal-box" style="max-width:420px;">
      <h3>Archive Supplier</h3>
      <p style="margin:20px 0;">
        Archive <strong id="archive_supplier_name" style="color:var(--text)"></strong>?<br>
        This can be undone later.
      </p>
      <form method="POST">
        <input type="hidden" name="supplier_id" id="archive_supplier_id">
        <div class="modal-actions">
          <button type="button" class="btn-cancel" id="cancelArchiveSupplier">Cancel</button>
          <button type="submit" name="archive_supplier" class="btn-confirm-warning">Confirm Archive</button>
        </div>
      </form>
    </div>
  </div>

  <!-- View Supplier Modal -->
  <div id="viewSupplierModal" class="modal-overlay">
    <div class="modal-box">
      <h3>Supplier Details</h3>

      <div style="margin:20px 0; font-size:13.5px; line-height:1.6;">
        <div><strong>Name:</strong> <span id="view_supplier_name"></span></div>
        <div><strong>Contact:</strong> <span id="view_contact_number"></span></div>
        <div><strong>Email:</strong> <span id="view_email"></span></div>
        <div><strong>Status:</strong> <span id="view_status" class="status-pill"></span></div>
      </div>

      <div class="modal-actions">
        <button type="button" class="btn-cancel" id="closeViewSupplier">Close</button>
        <button type="button" class="btn-confirm-ok openEditSupplierModal" id="viewToEditBtn">Edit</button>
      </div>
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

    // Add Supplier
    document.getElementById('openAddSupplierModal')?.addEventListener('click', () => openModal('addSupplierModal'));
    document.getElementById('cancelAddSupplier')?.addEventListener('click', () => closeModal('addSupplierModal'));

    // Edit Supplier
    document.querySelectorAll('.openEditSupplierModal').forEach(btn => {
      btn.addEventListener('click', () => {
        const data = btn.dataset;
        document.getElementById('edit_supplier_id').value = data.id;
        document.getElementById('edit_supplier_name').value = data.name;
        document.getElementById('edit_contact_number').value = data.contact || '';
        document.getElementById('edit_email').value = data.email || '';
        document.getElementById('edit_status').value = data.status;
        openModal('editSupplierModal');
      });
    });
    document.getElementById('cancelEditSupplier')?.addEventListener('click', () => closeModal('editSupplierModal'));

    // Archive Supplier
    document.querySelectorAll('.openArchiveSupplierModal').forEach(btn => {
      btn.addEventListener('click', () => {
        document.getElementById('archive_supplier_id').value = btn.dataset.id;
        document.getElementById('archive_supplier_name').textContent = btn.dataset.name;
        openModal('archiveSupplierModal');
      });
    });
    document.getElementById('cancelArchiveSupplier')?.addEventListener('click', () => closeModal('archiveSupplierModal'));

    // View Supplier + Edit from View
    document.querySelectorAll('.openViewSupplierModal').forEach(btn => {
      btn.addEventListener('click', () => {
        const data = btn.dataset;
        document.getElementById('view_supplier_name').textContent = data.name;
        document.getElementById('view_contact_number').textContent = data.contact || '—';
        document.getElementById('view_email').textContent = data.email || '—';

        const statusEl = document.getElementById('view_status');
        statusEl.textContent = data.status == "1" ? "Active" : "Inactive";
        statusEl.className = `status-pill ${data.status == "1" ? 'status-active' : 'status-inactive'}`;

        // Pass data to edit button inside view modal
        const editBtn = document.getElementById('viewToEditBtn');
        editBtn.dataset.id = data.id;
        editBtn.dataset.name = data.name;
        editBtn.dataset.contact = data.contact || '';
        editBtn.dataset.email = data.email || '';
        editBtn.dataset.status = data.status;

        openModal('viewSupplierModal');
      });
    });
    document.getElementById('closeViewSupplier')?.addEventListener('click', () => closeModal('viewSupplierModal'));

    // Live search
    let timeout;
    document.getElementById('searchInput')?.addEventListener('input', function() {
      clearTimeout(timeout);
      timeout = setTimeout(() => this.closest('form').submit(), 450);
    });

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