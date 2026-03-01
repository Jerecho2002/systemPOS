<?php
include "database/database.php";
$database->login_session();
$database->create_item();
$database->update_item();
$database->archive_item();

$categories = $database->select_categories();
$suppliers  = $database->select_suppliers();

// Pagination & filters
$perPage       = 5;
$search        = trim($_GET['search'] ?? '');
$categoryFilter = trim($_GET['category'] ?? '');
$priceFilter   = trim($_GET['price'] ?? '');
$page          = max(1, (int) ($_GET['page'] ?? 1));
$offset        = ($page - 1) * $perPage;

$totalItems = $database->getTotalItemsCount($search, $categoryFilter, $priceFilter);
$totalPages = max(1, ceil($totalItems / $perPage));

$items = $database->select_items_paginated($offset, $perPage, $search, $categoryFilter, $priceFilter);

$isStaff = ($_SESSION['user-role'] ?? '') === 'staff';
$isAdmin = !$isStaff;
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>POS & Inventory - Products</title>
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

    /* Reuse same styles from categories page */
    .card {
      background: var(--surface);
      border: 1.5px solid var(--border);
      border-radius: 16px;
      padding: 24px;
    }

    .card-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 20px;
      flex-wrap: wrap;
      gap: 12px;
    }

    .card-header h3 {
      font-size: 16px;
      font-weight: 700;
    }

    .card-header p {
      font-size: 12px;
      color: var(--text-muted);
      margin-top: 2px;
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

    .data-table .muted {
      color: var(--text-muted);
    }

    .status-pill {
      padding: 2px 10px;
      font-size: 12px;
      font-weight: 600;
      border-radius: 999px;
      white-space: nowrap;
    }

    .status-in {
      background: rgba(67, 211, 146, .15);
      color: var(--success);
    }

    .status-low {
      background: rgba(255, 92, 92, .15);
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

    .btn-action:hover.edit {
      color: var(--success);
      background: rgba(67, 211, 146, .12);
    }

    .btn-action:hover.archive {
      color: var(--danger);
      background: rgba(255, 92, 92, .12);
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
      background: rgba(0, 0, 0, 0.7);
      backdrop-filter: blur(6px);
      z-index: 1000;
      /* higher z-index is usually safer */
      display: flex;
      align-items: center;
      justify-content: center;
      opacity: 0;
      pointer-events: none;
      transition: opacity 0.25s ease;
    }

    .modal-overlay.active {
      opacity: 1;
      pointer-events: all;
    }

    .modal-box {
      background: var(--surface);
      border: 1.5px solid var(--border);
      border-radius: 18px;
      padding: 1.5rem;
      /* 24px — good balance */
      width: 100%;
      max-width: 620px;
      /* slightly wider than 560px — more breathing room */
      min-width: 320px;
      /* prevent it from becoming too narrow on very small screens */
      max-height: 94vh;
      /* ← very important: prevent overflow on small screens */
      overflow-y: auto;
      /* scroll inside modal if content is long */
      box-shadow: 0 20px 50px rgba(0, 0, 0, 0.45);
      transform: translateY(20px);
      /* slight entrance animation support */
      transition: transform 0.25s ease, opacity 0.25s ease;
      opacity: 0.98;
      /* tiny initial opacity for fade-in */
    }

    /* When modal becomes visible → slide up a bit + full opacity */
    .modal-overlay.active .modal-box {
      transform: translateY(0);
      opacity: 1;
    }

    /* ────────────────────────────────────────────────
   Mobile-first adjustments
───────────────────────────────────────────────── */
    @media (max-width: 640px) {
      .modal-box {
        padding: 1.25rem;
        /* slightly less padding on phones */
        max-width: 96%;
        max-height: 92vh;
        /* give more space for browser UI / keyboard */
        border-radius: 14px;
        /* softer corners on small screens */
      }

      /* Optional: make form fields & buttons easier to tap */
      input,
      select,
      textarea,
      button {
        font-size: 1rem;
        /* at least 16px for accessibility */
        padding: 0.75rem 1rem;
      }

      .modal-actions {
        flex-direction: column;
        gap: 0.75rem;
      }

      .btn-cancel,
      .btn-confirm-ok {
        width: 100%;
        padding: 0.9rem;
      }
    }

    /* Very small screens (e.g. old phones) */
    @media (max-width: 360px) {
      .modal-box {
        padding: 1rem;
      }
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

    .form-textarea {
      resize: vertical;
      min-height: 82px;
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

    #edit_cost_price,
    #edit_selling_price,
    #add_cost_price,
    #add_selling_price {
      -moz-appearance: textfield;
    }

    #edit_cost_price::-webkit-outer-spin-button,
    #edit_cost_price::-webkit-inner-spin-button,
    #edit_selling_price::-webkit-outer-spin-button,
    #edit_selling_price::-webkit-inner-spin-button,
    #add_cost_price::-webkit-outer-spin-button,
    #add_cost_price::-webkit-inner-spin-button,
    #add_selling_price::-webkit-outer-spin-button,
    #add_selling_price::-webkit-inner-spin-button {
      -webkit-appearance: none;
      margin: 0;
    }
  </style>
</head>

<body>

  <button class="mobile-toggle" id="sidebar-toggle">☰</button>
  <?php include "sidebar.php"; ?>

  <main style="margin-left:240px;">
    <div class="card">

      <div class="card-header">
        <div>
          <h3>Inventory Items</h3>
          <p>Add, update or archive products</p>
        </div>
        <button id="openAddItemModal" class="btn-primary">+ Add Item</button>
      </div>

      <!-- Filters -->
      <form method="GET" class="flex flex-wrap gap-3 mb-5">
        <div class="search-wrap">
          <svg class="search-icon" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <circle cx="11" cy="11" r="8" />
            <path d="m21 21-4.35-4.35" />
          </svg>
          <input type="text" name="search" id="searchInput" value="<?= htmlspecialchars($search) ?>" placeholder="Search name, barcode, supplier...">
          <?php if ($search !== ''): ?>
            <a href="?" class="search-clear" title="Clear">×</a>
          <?php endif; ?>
        </div>

        <select name="category" class="filter-select">
          <option value="">All Categories</option>
          <?php foreach ($categories as $cat): ?>
            <option value="<?= htmlspecialchars($cat['category_name']) ?>" <?= $categoryFilter === $cat['category_name'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($cat['category_name']) ?>
            </option>
          <?php endforeach; ?>
        </select>

        <select name="price" class="filter-select">
          <option value="">All Prices</option>
          <option value="below" <?= $priceFilter === 'below' ? 'selected' : '' ?>>₱5,000 Below</option>
          <option value="above" <?= $priceFilter === 'above' ? 'selected' : '' ?>>₱5,000 Above</option>
        </select>

        <?php if ($search || $categoryFilter || $priceFilter): ?>
          <a href="?" class="btn-cancel px-5 py-2 text-sm">Clear Filters</a>
        <?php endif; ?>
      </form>

      <!-- Active filters info -->
      <?php if ($search || $categoryFilter || $priceFilter): ?>
        <div style="background:rgba(245,166,35,.08); border:1px solid rgba(245,166,35,.2); border-radius:10px; padding:10px 14px; font-size:13px; color:var(--text-muted); margin-bottom:16px;">
          Showing <strong style="color:var(--text)"><?= $totalItems ?></strong> items
          <?php if ($search): ?> matching "<strong style="color:var(--text)"><?= htmlspecialchars($search) ?></strong>"<?php endif; ?>
            <?php if ($categoryFilter): ?> in category "<strong style="color:var(--text)"><?= htmlspecialchars($categoryFilter) ?></strong>"<?php endif; ?>
              <?php if ($priceFilter): ?>
                priced <strong style="color:var(--text)"><?= $priceFilter === 'below' ? '≤ ₱5,000' : '≥ ₱5,000' ?></strong>
              <?php endif; ?>
        </div>
      <?php endif; ?>

      <!-- Alerts -->
      <?php if (isset($_SESSION['create-success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['create-success'] ?></div>
        <?php unset($_SESSION['create-success']); ?>
      <?php endif; ?>
      <?php if (isset($_SESSION['create-error'])): ?>
        <div class="alert alert-error"><?= $_SESSION['create-error'] ?></div>
        <?php unset($_SESSION['create-error']); ?>
      <?php endif; ?>

      <!-- Table -->
      <div style="overflow-x:auto;">
        <table class="data-table">
          <thead>
            <tr>
              <th>Item</th>
              <th>Category</th>
              <th>Supplier</th>
              <th>Pricing</th>
              <th>Stock</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($items)): ?>
              <?php foreach ($items as $item): ?>
                <?php
                if ((int)$item['quantity'] <= 0) continue;
                $lowStock = (int)$item['quantity'] <= (int)$item['min_stock'];
                ?>
                <tr>
                  <td>
                    <div style="font-weight:600;"><?= htmlspecialchars($item['item_name']) ?></div>
                    <div class="muted" style="font-size:12px; margin-top:2px;"><?= htmlspecialchars($item['barcode'] ?: '—') ?></div>
                  </td>
                  <td class="muted"><?= htmlspecialchars($item['category_name'] ?? '—') ?></td>
                  <td class="muted"><?= htmlspecialchars($item['supplier_name'] ?? '—') ?></td>
                  <td>
                    <div>Cost: ₱<?= number_format($item['cost_price'], 2) ?></div>
                    <div style="font-weight:600;">Sell: ₱<?= number_format($item['selling_price'], 2) ?></div>
                  </td>
                  <td>
                    <div style="font-weight:600;"><?= $item['quantity'] ?></div>
                    <div class="muted" style="font-size:12px;">min: <?= $item['min_stock'] ?></div>
                  </td>
                  <td>
                    <span class="status-pill <?= $lowStock ? 'status-low' : 'status-in' ?>">
                      <?= $lowStock ? 'Low Stock' : 'In Stock' ?>
                    </span>
                  </td>
                  <td>
                    <div style="display:flex; gap:6px;">
                      <button class="btn-action edit openEditItemModal"
                        data-id="<?= $item['item_id'] ?>"
                        data-name="<?= htmlspecialchars($item['item_name']) ?>"
                        data-barcode="<?= htmlspecialchars($item['barcode']) ?>"
                        data-desc="<?= htmlspecialchars($item['description'] ?? '') ?>"
                        data-category="<?= $item['category_id'] ?>"
                        data-supplier="<?= $item['supplier_id'] ?>"
                        data-cost="<?= $item['cost_price'] ?>"
                        data-sell="<?= $item['selling_price'] ?>"
                        data-qty="<?= $item['quantity'] ?>"
                        data-min="<?= $item['min_stock'] ?>"
                        data-image="<?= htmlspecialchars($item['image'] ?? '') ?>"
                        title="Edit">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                      </button>

                      <?php if ($isAdmin): ?>
                        <button class="btn-action archive openArchiveItemModal"
                          data-id="<?= $item['item_id'] ?>"
                          data-name="<?= htmlspecialchars($item['item_name']) ?>"
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
                  <?= $search || $categoryFilter || $priceFilter ? 'No items match the current filters.' : 'No products found.' ?>
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <?php if ($totalPages > 1):
        $q = http_build_query(array_filter([
          'search'   => $search   ?: null,
          'category' => $categoryFilter ?: null,
          'price'    => $priceFilter ?: null,
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

  <!-- Add Item Modal -->
  <div id="addItemModal" class="modal-overlay">
    <div class="modal-box">
      <h3>Add New Item</h3>
      <form method="POST" enctype="multipart/form-data">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="form-group">
            <label>Item Name *</label>
            <input type="text" name="item_name" required class="form-input" placeholder="e.g. RTX 4070 Ti">
          </div>
          <div class="form-group">
            <label>Barcode *</label>
            <input type="text" name="barcode" required class="form-input" placeholder="Scan or type barcode">
          </div>
        </div>

        <div class="form-group">
          <label>Description</label>
          <textarea name="description" class="form-textarea" rows="3" placeholder="Optional"></textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="form-group">
            <label>Category *</label>
            <select name="category_id" required class="form-select">
              <option value="">Select category</option>
              <?php foreach ($categories as $c): ?>
                <option value="<?= $c['category_id'] ?>"><?= htmlspecialchars($c['category_name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Supplier</label>
            <select name="supplier_id" class="form-select">
              <option value="">Select supplier</option>
              <?php foreach ($suppliers as $s): ?>
                <option value="<?= $s['supplier_id'] ?>"><?= htmlspecialchars($s['supplier_name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="form-group">
            <label>Cost Price</label>
            <input type="number" step="0.01" id="add_cost_price" name="cost_price" required value="0.00" class="form-input">
          </div>
          <div class="form-group">
            <label>Selling Price *</label>
            <input type="number" step="0.01" id="add_selling_price" name="selling_price" required value="0.00" class="form-input">
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="form-group">
            <label>Current Stock</label>
            <input type="number" name="quantity" value="0" class="form-input">
          </div>
          <div class="form-group">
            <label>Minimum Stock</label>
            <input type="number" name="min_stock" value="5" class="form-input">
          </div>
        </div>

        <!-- Image Upload + Preview -->
        <div class="form-group mt-6 flex flex-col items-center">
          <label class="mb-3 text-gray-700 font-medium">Product Image</label>

          <div class="w-full max-w-md">
            <!-- Hidden file input -->
            <input
              type="file"
              name="image"
              accept="image/*"
              class="hidden"
              id="itemImageInput">

            <!-- Clickable landscape preview / upload area -->
            <label
              for="itemImageInput"
              class="cursor-pointer block w-full aspect-[4/3] mx-auto border-2 border-dashed border-gray-400 rounded-xl overflow-hidden bg-gray-50 hover:bg-gray-100 transition-colors">
              <div id="previewContainer" class="w-full h-full flex items-center justify-center relative">
                <!-- Preview image (hidden by default) -->
                <img
                  id="imagePreview"
                  src=""
                  alt="Image preview"
                  class="w-full h-full object-cover hidden">

                <!-- Placeholder when no image -->
                <div id="previewPlaceholder" class="text-center px-6 py-8">
                  <div class="text-gray-400 text-5xl mb-3">+</div>
                  <span class="text-base font-medium text-gray-600 block">Click to upload product image</span>
                  <p class="text-sm text-gray-500 mt-2">JPG, PNG • Recommended landscape (e.g. 800×600 or wider)</p>
                  <p class="text-xs text-gray-400 mt-1">Max 5MB</p>
                </div>
              </div>
            </label>

            <!-- Helper text below -->
            <p class="text-xs text-gray-500 text-center mt-3">
              Click the rectangle above to choose an image (landscape works best)
            </p>
          </div>
        </div>

        <div class="modal-actions mt-8">
          <button type="button" class="btn-cancel" id="cancelAddItem">Cancel</button>
          <button type="submit" name="create_item" class="btn-confirm-ok">Add Item</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Edit Item Modal -->
  <div id="editItemModal" class="modal-overlay">
    <div class="modal-box max-h-[94vh] overflow-y-auto"> <!-- scrollable when needed -->

      <h3 class="text-xl font-semibold mb-5">Edit Item</h3>

      <form method="POST" enctype="multipart/form-data" class="space-y-5">
        <input type="hidden" name="item_id" id="edit_item_id">

        <!-- Row 1 -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div class="form-group">
            <label class="block mb-1 text-sm">Item Name *</label>
            <input type="text" name="item_name" id="edit_item_name" required class="form-input">
          </div>
          <div class="form-group">
            <label class="block mb-1 text-sm">Barcode *</label>
            <input type="text" name="barcode" id="edit_barcode" required class="form-input">
          </div>
        </div>

        <!-- Description -->
        <div class="form-group">
          <label class="block mb-1 text-sm">Description</label>
          <textarea name="description" id="edit_description" class="form-textarea" rows="2"></textarea>
        </div>

        <!-- Row 2 -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div class="form-group">
            <label class="block mb-1 text-sm">Category *</label>
            <select name="category_id" id="edit_category_id" required class="form-select">
              <option value="">Select category</option>
              <?php foreach ($categories as $c): ?>
                <option value="<?= $c['category_id'] ?>"><?= htmlspecialchars($c['category_name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label class="block mb-1 text-sm">Supplier</label>
            <select name="supplier_id" id="edit_supplier_id" class="form-select">
              <option value="">Select supplier (optional)</option>
              <?php foreach ($suppliers as $s): ?>
                <option value="<?= $s['supplier_id'] ?>"><?= htmlspecialchars($s['supplier_name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <!-- Row 3: Prices -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div class="form-group">
            <label class="block mb-1 text-sm">Cost Price</label>
            <input type="number" step="0.01" name="cost_price" id="edit_cost_price"
              class="form-input" <?= $isStaff ? 'disabled' : '' ?>>
          </div>
          <div class="form-group">
            <label class="block mb-1 text-sm">Selling Price *</label>
            <input type="number" step="0.01" name="selling_price" id="edit_selling_price" required
              class="form-input" <?= $isStaff ? 'disabled' : '' ?>>
          </div>
        </div>

        <!-- Row 4: Stock -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div class="form-group">
            <label class="block mb-1 text-sm">Current Stock</label>
            <input type="number" name="quantity" id="edit_quantity"
              class="form-input" <?= $isStaff ? 'disabled' : '' ?>>
          </div>
          <div class="form-group">
            <label class="block mb-1 text-sm">Minimum Stock</label>
            <input type="number" name="min_stock" id="edit_min_stock" class="form-input">
          </div>
        </div>

        <!-- Optional: Image Upload / Change (very useful for edit modal) -->
        <div class="form-group pt-2">
          <label class="block mb-2 text-sm font-medium text-gray-700">Product Image</label>

          <div class="w-full">
            <input
              type="file"
              name="image"
              accept="image/*"
              class="hidden"
              id="editItemImageInput">

            <label
              for="editItemImageInput"
              class="cursor-pointer block w-full max-w-2xl mx-auto border-2 border-dashed border-gray-400 rounded-lg overflow-hidden bg-gray-50 hover:border-blue-400 hover:bg-blue-50/30 transition-all">
              <div class="relative w-full" style="aspect-ratio: 16 / 9; max-height: 240px;">
                <!-- Current image preview (if exists) -->
                <img
                  id="editCurrentImage"
                  src=""
                  alt="Current product image"
                  class="absolute inset-0 w-full h-full object-contain bg-black/5">

                <!-- New image preview (when user selects new file) -->
                <img
                  id="editImagePreview"
                  src=""
                  alt="New image preview"
                  class="absolute inset-0 w-full h-full object-contain hidden bg-black/5">

                <div id="editPreviewPlaceholder" class="absolute inset-0 flex flex-col items-center justify-center text-center px-6 <?= !empty($item['image']) ? 'hidden' : '' ?>">
                  <div class="text-gray-400 text-6xl mb-3">+</div>
                  <span class="text-base font-medium text-gray-700">Click to upload new image</span>
                  <p class="text-sm text-gray-500 mt-2">JPG, PNG • Max 5MB</p>
                </div>
              </div>
            </label>

            <p class="text-xs text-gray-500 text-center mt-2">
              Leave unchanged to keep current image
            </p>
          </div>
        </div>

        <!-- Action buttons -->
        <div class="modal-actions flex justify-end gap-3 mt-6 pt-4 border-t">
          <button type="button" class="btn-cancel px-5 py-2" id="cancelEditItem">Cancel</button>
          <button type="submit" name="update_item" class="btn-confirm-ok px-6 py-2">Save Changes</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Archive Confirmation Modal -->
  <div id="archiveItemModal" class="modal-overlay">
    <div class="modal-box" style="max-width:420px;">
      <h3>Archive Item</h3>
      <p style="margin-bottom:24px;">
        Are you sure you want to archive <strong id="archive_item_name" style="color:var(--text)"></strong>?<br>
        This can be undone later.
      </p>
      <form method="POST">
        <input type="hidden" name="archive_item_id" id="archive_item_id">
        <div class="modal-actions">
          <button type="button" class="btn-cancel" id="cancelArchiveItem">Cancel</button>
          <button type="submit" name="archive_item" class="btn-confirm-warning">Archive</button>
        </div>
      </form>
    </div>
  </div>
  <script>
    // ────────────────────────────────────────────────
    // Modal helpers (open/close)
    function openModal(id) {
      const modal = document.getElementById(id);
      if (modal) modal.classList.add('active');
    }

    function closeModal(id) {
      const modal = document.getElementById(id);
      if (modal) modal.classList.remove('active');
    }

    // Close modal when clicking overlay
    document.querySelectorAll('.modal-overlay').forEach(el => {
      el.addEventListener('click', e => {
        if (e.target === el) closeModal(el.id);
      });
    });

    // ────────────────────────────────────────────────
    // Add Item Image Preview
    document.addEventListener('DOMContentLoaded', () => {
      const imageInput = document.getElementById('itemImageInput');
      const imagePreview = document.getElementById('imagePreview');
      const placeholder = document.getElementById('previewPlaceholder');

      if (imageInput && imagePreview && placeholder) {
        imageInput.addEventListener('change', e => {
          const file = e.target.files[0];

          if (file) {
            if (!file.type.startsWith('image/')) {
              alert('Please select an image file');
              imageInput.value = '';
              return;
            }

            const reader = new FileReader();
            reader.onload = event => {
              imagePreview.src = event.target.result;
              imagePreview.classList.remove('hidden');
              placeholder.classList.add('hidden');
            };
            reader.readAsDataURL(file);
          } else {
            imagePreview.src = '';
            imagePreview.classList.add('hidden');
            placeholder.classList.remove('hidden');
          }
        });
      }

      // Open/close add modal
      document.getElementById('openAddItemModal')?.addEventListener('click', () => openModal('addItemModal'));
      document.getElementById('cancelAddItem')?.addEventListener('click', () => closeModal('addItemModal'));
    });

    // ────────────────────────────────────────────────
    // Edit Item Modal – populate fields + image preview
    document.querySelectorAll('.openEditItemModal').forEach(button => {
      button.addEventListener('click', function() {
        // Populate form fields
        document.getElementById('edit_item_id').value = this.dataset.id || '';
        document.getElementById('edit_item_name').value = this.dataset.name || '';
        document.getElementById('edit_barcode').value = this.dataset.barcode || '';
        document.getElementById('edit_description').value = this.dataset.desc || '';
        document.getElementById('edit_category_id').value = this.dataset.category || '';
        document.getElementById('edit_supplier_id').value = this.dataset.supplier || '';
        document.getElementById('edit_cost_price').value = this.dataset.cost || '0.00';
        document.getElementById('edit_selling_price').value = this.dataset.sell || '0.00';
        document.getElementById('edit_quantity').value = this.dataset.qty || '0';
        document.getElementById('edit_min_stock').value = this.dataset.min || '5';

        // ─── Image preview logic ────────────────────────────────────────
        const currentImg = document.getElementById('editCurrentImage');
        const newPreviewImg = document.getElementById('editImagePreview');
        const placeholder = document.getElementById('editPreviewPlaceholder');

        if (!currentImg || !newPreviewImg || !placeholder) return;

        // Reset previous new preview
        newPreviewImg.src = '';
        newPreviewImg.classList.add('hidden');
        currentImg.style.opacity = '1';

        const imageFilename = this.dataset.image || '';

        if (imageFilename.trim() !== '') {
          currentImg.src = 'uploads/products/' + imageFilename;
          currentImg.classList.remove('hidden');
          placeholder.classList.add('hidden');
        } else {
          currentImg.src = '';
          currentImg.classList.add('hidden');
          placeholder.classList.remove('hidden');
        }

        // Open edit modal
        openModal('editItemModal');
      });
    });

    // Edit modal – new image upload preview
    document.addEventListener('DOMContentLoaded', () => {
      const editImageInput = document.getElementById('editItemImageInput');
      const currentImg = document.getElementById('editCurrentImage');
      const newPreviewImg = document.getElementById('editImagePreview');
      const placeholder = document.getElementById('editPreviewPlaceholder');

      if (!editImageInput) return;

      editImageInput.addEventListener('change', e => {
        const file = e.target.files[0];

        if (!file) {
          newPreviewImg.src = '';
          newPreviewImg.classList.add('hidden');
          currentImg.style.opacity = '1';
          return;
        }

        if (!file.type.startsWith('image/')) {
          alert('Please select an image file');
          editImageInput.value = '';
          return;
        }

        const reader = new FileReader();
        reader.onload = event => {
          newPreviewImg.src = event.target.result;
          newPreviewImg.classList.remove('hidden');
          currentImg.style.opacity = '0.35'; // dim current image
          placeholder.classList.add('hidden');
        };
        reader.readAsDataURL(file);
      });

      // Close edit modal
      document.getElementById('cancelEditItem')?.addEventListener('click', () => closeModal('editItemModal'));
    });

    // ────────────────────────────────────────────────
    // Archive modal
    document.querySelectorAll('.openArchiveItemModal').forEach(btn => {
      btn.addEventListener('click', () => {
        document.getElementById('archive_item_id').value = btn.dataset.id || '';
        document.getElementById('archive_item_name').textContent = btn.dataset.name || 'this item';
        openModal('archiveItemModal');
      });
    });

    document.getElementById('cancelArchiveItem')?.addEventListener('click', () => closeModal('archiveItemModal'));

    // ────────────────────────────────────────────────
    // Live search + filters auto-submit
    let timeout;
    const searchEl = document.getElementById('searchInput');
    if (searchEl) {
      searchEl.addEventListener('input', () => {
        clearTimeout(timeout);
        timeout = setTimeout(() => searchEl.closest('form')?.submit(), 450);
      });
    }

    document.querySelectorAll('.filter-select').forEach(sel => {
      sel.addEventListener('change', () => sel.closest('form')?.submit());
    });

    // ────────────────────────────────────────────────
    // Sidebar collapse sync
    const main = document.querySelector('main');

    function syncMainMargin() {
      const collapsed = document.getElementById('main-sidebar')?.classList.contains('collapsed');
      if (main) main.style.marginLeft = collapsed ? '64px' : '240px';
    }

    document.getElementById('sidebarCollapseBtn')?.addEventListener('click', () => setTimeout(syncMainMargin, 100));
    syncMainMargin();

    // ────────────────────────────────────────────────
    // Auto-hide alerts
    document.querySelectorAll('.alert').forEach(el => {
      setTimeout(() => el.style.opacity = '0', 3200);
      setTimeout(() => el.remove(), 4000);
    });
  </script>

</body>

</html>