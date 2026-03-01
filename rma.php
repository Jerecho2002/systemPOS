<?php
include "database/database.php";
$database->login_session();
$database->createRma();
$database->updateRma();

$search  = trim($_GET['search'] ?? '');
$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;
$offset  = ($page - 1) * $perPage;

$totalRecords = $database->getRmaCount($search);
$totalPages   = max(1, ceil($totalRecords / $perPage));
$rmaList      = $database->getRmaPaginated($search, $offset, $perPage);

$sales = $database->getSalesForRma();
$items = $database->getAllActiveItems();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>RMA — Return Merchandise Authorization</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="assets/tailwind.min.css" rel="stylesheet" />
    <link href="assets/fonts.css" rel="stylesheet">
    <style>
        :root {
            --bg: #0f1117;
            --surface: #1a1d27;
            --surface2: #22263a;
            --border: #2e3347;
            --accent: #f5a623;
            --accent2: #ff6b6b;
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

        h1,
        h2,
        h3,
        h4 {
            font-family: 'Syne', sans-serif;
        }

        main {
            flex: 1;
            margin-left: 240px;
            padding: 28px 32px;
            min-height: 100vh;
        }

        /* ── PAGE HEADER ── */
        .page-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 28px;
            gap: 16px;
            flex-wrap: wrap;
        }

        .page-header-left h1 {
            font-size: 22px;
            font-weight: 700;
            letter-spacing: -0.5px;
            margin-bottom: 4px;
        }

        .page-header-left p {
            font-size: 13px;
            color: var(--text-muted);
        }

        /* ── CARD ── */
        .card {
            background: var(--surface);
            border: 1.5px solid var(--border);
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 28px;
        }

        .card-title {
            font-family: 'Syne', sans-serif;
            font-size: 15px;
            font-weight: 700;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-title-dot {
            width: 8px;
            height: 8px;
            background: var(--accent);
            border-radius: 50%;
        }

        /* ── FORM ── */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 16px;
            margin-bottom: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .form-group.full {
            grid-column: 1 / -1;
        }

        .form-label {
            font-size: 11px;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }

        .form-input,
        .form-select,
        .form-textarea {
            background: var(--bg);
            border: 1.5px solid var(--border);
            border-radius: 10px;
            padding: 10px 14px;
            color: var(--text);
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            outline: none;
            transition: border-color .2s, box-shadow .2s;
            width: 100%;
        }

        .form-input:focus,
        .form-select:focus,
        .form-textarea:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(245, 166, 35, .1);
        }

        .form-select option {
            background: var(--surface2);
            color: var(--text);
        }

        .form-textarea {
            resize: vertical;
            min-height: 90px;
        }

        /* ── STATUS BADGE ── */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 11px;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-pending {
            background: rgba(245, 166, 35, .12);
            color: var(--accent);
        }

        .status-sent {
            background: rgba(78, 205, 196, .12);
            color: #4ecdc4;
        }

        .status-resolved {
            background: rgba(67, 211, 146, .12);
            color: var(--success);
        }

        .condition-badge {
            display: inline-flex;
            align-items: center;
            font-size: 11px;
            font-weight: 600;
            padding: 3px 8px;
            border-radius: 6px;
        }

        .condition-defective {
            background: rgba(255, 92, 92, .12);
            color: var(--danger);
        }

        .condition-damaged {
            background: rgba(245, 158, 11, .12);
            color: var(--warning);
        }

        .condition-wrong {
            background: rgba(124, 58, 237, .12);
            color: #a78bfa;
        }

        /* ── TOOLBAR ── */
        .toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .search-wrap {
            position: relative;
            flex: 1 1 300px;
            min-width: 260px;
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
            transition: border-color .2s;
        }

        .search-wrap input:focus {
            border-color: var(--accent);
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
            text-decoration: none;
            font-size: 18px;
            line-height: 1;
        }

        /* ── TABLE ── */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        .data-table thead th {
            padding: 10px 16px;
            text-align: left;
            font-size: 11px;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid var(--border);
        }

        .data-table tbody tr {
            border-bottom: 1px solid var(--border);
            transition: background .15s;
        }

        .data-table tbody tr:hover {
            background: rgba(255, 255, 255, .02);
        }

        .data-table tbody tr:last-child {
            border-bottom: none;
        }

        .data-table td {
            padding: 13px 16px;
            vertical-align: middle;
        }

        /* ── BUTTONS ── */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 9px 18px;
            border-radius: 10px;
            font-family: 'DM Sans', sans-serif;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: opacity .2s, transform .1s;
        }

        .btn:hover {
            opacity: .88;
            transform: translateY(-1px);
        }

        .btn:active {
            transform: translateY(0);
        }

        .btn-primary {
            background: var(--accent);
            color: #111;
        }

        .btn-danger {
            background: rgba(255, 92, 92, .12);
            color: var(--danger);
            border: 1.5px solid rgba(255, 92, 92, .25);
        }

        .btn-action {
            width: 34px;
            height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            border: 1.5px solid var(--border);
            background: var(--surface2);
            color: var(--text-muted);
            cursor: pointer;
            transition: all .2s;
        }

        .btn-action:hover {
            border-color: var(--accent);
            color: var(--accent);
        }

        .btn-action.danger:hover {
            border-color: var(--danger);
            color: var(--danger);
        }

        /* ── ALERTS ── */
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

        /* ── PAGINATION ── */
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
            color: var(--accent);
            background: rgba(245, 166, 35, .08);
        }

        .page-btn.active {
            background: var(--accent);
            color: #111;
            border-color: var(--accent);
        }

        .page-btn.disabled {
            opacity: .4;
            pointer-events: none;
        }

        /* ── MODAL ── */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .75);
            backdrop-filter: blur(4px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-box {
            background: var(--surface);
            border: 1.5px solid var(--border);
            border-radius: 18px;
            padding: 28px;
            width: 90%;
            max-width: 480px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, .4);
        }

        .modal-box h3 {
            font-family: 'Syne', sans-serif;
            font-size: 17px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .modal-box p {
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 20px;
            line-height: 1.6;
        }

        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }

        .btn-cancel {
            background: var(--surface2);
            border: 1.5px solid var(--border);
            color: var(--text-muted);
            border-radius: 10px;
            padding: 9px 20px;
            font-family: 'DM Sans', sans-serif;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all .2s;
        }

        .btn-cancel:hover {
            color: var(--text);
            border-color: #555;
        }

        /* ── EMPTY STATE ── */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-muted);
        }

        .empty-state .empty-icon {
            font-size: 48px;
            margin-bottom: 12px;
            opacity: .4;
        }

        .empty-state p {
            font-size: 14px;
        }

        /* ── RMA NUMBER ── */
        .rma-number {
            font-family: 'Syne', sans-serif;
            font-size: 13px;
            font-weight: 700;
            color: var(--accent);
        }

        /* ── DIVIDER ── */
        .section-divider {
            height: 1px;
            background: var(--border);
            margin: 20px 0;
        }

        .item-selector-display {
            background: var(--bg);
            border: 1.5px solid var(--border);
            border-radius: 10px;
            padding: 10px 14px;
            color: var(--text);
            font-size: 14px;
            cursor: pointer;
            transition: border-color .2s;
            min-height: 40px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .item-selector-display:hover {
            border-color: var(--accent);
        }

        .item-selector-placeholder {
            color: var(--text-muted);
            font-style: italic;
            font-size: 13px;
        }

        .item-selector-chosen {
            color: var(--text);
            font-weight: 600;
            font-size: 13px;
        }

        .item-selector-chevron {
            color: var(--text-muted);
            flex-shrink: 0;
            margin-left: 8px;
        }

        .cat-pill {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            border: 1.5px solid var(--border);
            background: var(--surface2);
            color: var(--text-muted);
            cursor: pointer;
            transition: all .15s;
            white-space: nowrap;
        }

        .cat-pill:hover {
            border-color: var(--accent);
            color: var(--accent);
        }

        .cat-pill-active {
            background: var(--accent);
            border-color: var(--accent);
            color: #111;
        }
    </style>
</head>

<body>

    <button class="mobile-toggle" id="sidebar-toggle">☰</button>
    <?php include "sidebar.php"; ?>

    <main>

        <!-- Page Header -->
        <div class="page-header">
            <div class="page-header-left">
                <h1>Return Merchandise Authorization</h1>
                <p>Manage and document product returns to suppliers</p>
            </div>
        </div>

        <!-- Alerts -->
        <?php if (isset($_SESSION['rma-success'])): ?>
            <div class="alert alert-success" id="successAlert">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <?= $_SESSION['rma-success'];
                unset($_SESSION['rma-success']); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['rma-error'])): ?>
            <div class="alert alert-error" id="errorAlert">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01" />
                </svg>
                <?= $_SESSION['rma-error'];
                unset($_SESSION['rma-error']); ?>
            </div>
        <?php endif; ?>

        <!-- Create RMA Form -->
        <div class="card">
            <div class="card-title">
                <div class="card-title-dot"></div>
                New RMA Request
            </div>

            <form method="POST" id="rmaForm">
                <div class="form-grid">

                    <div class="form-group">
                        <label class="form-label">Customer Name <span style="color:var(--danger)">*</span></label>
                        <input type="text" name="customer_name" class="form-input" placeholder="e.g. Juan Dela Cruz" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Condition <span style="color:var(--danger)">*</span></label>
                        <select name="condition" class="form-select" required>
                            <option value="Defective">Defective</option>
                            <option value="Damaged">Damaged</option>
                            <option value="Wrong Item">Wrong Item</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Related Transaction</label>
                        <div class="item-selector-wrap">
                            <input type="hidden" name="sale_id" id="selectedSaleId" required>
                            <div class="item-selector-display" id="saleSelectorDisplay" onclick="openSaleModal()">
                                <span class="item-selector-placeholder">— No linked transaction —</span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="Pending">Pending</option>
                            <option value="Sent to Supplier">Sent to Supplier</option>
                            <option value="Resolved">Resolved</option>
                        </select>
                    </div>

                    <div class="form-group full">
                        <label class="form-label">Reason <span style="color:var(--danger)">*</span></label>
                        <textarea name="reason" class="form-textarea" placeholder="Describe the issue with the item…" required></textarea>
                    </div>

                </div>

                <!-- Items Section -->
                <div style="margin-bottom:16px;">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px;">
                        <span class="form-label">Items <span style="color:var(--danger)">*</span></span>
                        <button type="button" id="addItemRowBtn" class="btn btn-primary" style="padding:6px 14px; font-size:12px;">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                            </svg>
                            Add Item
                        </button>
                    </div>

                    <!-- Item Rows -->
                    <div id="rmaItemsContainer">
                        <!-- Row 1 (default) -->
                        <div class="rma-item-row" style="display:grid; grid-template-columns:1fr 100px 36px; gap:10px; margin-bottom:10px; align-items:center;">
                            <div class="item-selector-wrap">
                                <input type="hidden" name="item_id[]" class="selected-item-id" required>
                                <div class="item-selector-display" onclick="openItemModal(this)">
                                    <span class="item-selector-placeholder">— Select item —</span>
                                </div>
                            </div>
                            <input type="number" name="quantity[]" class="form-input" min="1" value="1" placeholder="Qty" required>
                            <button type="button" class="remove-item-row-btn" style="
        width:36px; height:36px;
        background:rgba(255,92,92,.1);
        border:1.5px solid rgba(255,92,92,.25);
        border-radius:8px;
        color:var(--danger);
        cursor:pointer;
        display:flex;
        align-items:center;
        justify-content:center;
        flex-shrink:0;
    " title="Remove row">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div style="display:flex; justify-content:flex-end;">
                    <button type="submit" name="rma-submit-btn" class="btn btn-primary">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        Create RMA
                    </button>
                </div>
            </form>
        </div>

        <!-- RMA Records -->
        <div class="card">
            <div class="toolbar">
                <div class="search-wrap">
                    <svg class="search-icon" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="8" />
                        <path d="m21 21-4.35-4.35" />
                    </svg>
                    <form method="GET" id="rmaSearchForm" style="margin:0;">
                        <input type="text" name="search" id="rmaSearchInput"
                            value="<?= htmlspecialchars($search) ?>"
                            placeholder="Search by RMA number or customer…"
                            class="form-input">
                        <?php if ($search !== ''): ?>
                            <a href="rma.php" class="search-clear">×</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <div style="overflow-x:auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>RMA Number</th>
                            <th>Customer</th>
                            <th>Item</th>
                            <th>Condition</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th style="text-align:center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($rmaList)): ?>
                            <?php foreach ($rmaList as $rma): ?>
                                <tr>
                                    <td><span class="rma-number"><?= htmlspecialchars($rma['rma_number']) ?></span></td>
                                    <td><?= htmlspecialchars($rma['customer_name']) ?></td>
                                    <td style="color:var(--text-muted);">
                                        <?= htmlspecialchars($rma['item_names']) ?>
                                        <span style="font-size:11px; color:var(--text-muted);">
                                            (<?= $rma['item_count'] ?> item<?= $rma['item_count'] != 1 ? 's' : '' ?>)
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                        $condClass = match ($rma['condition']) {
                                            'Defective'  => 'condition-defective',
                                            'Damaged'    => 'condition-damaged',
                                            'Wrong Item' => 'condition-wrong',
                                            default      => 'condition-defective'
                                        };
                                        ?>
                                        <span class="condition-badge <?= $condClass ?>"><?= $rma['condition'] ?></span>
                                    </td>
                                    <td>
                                        <?php
                                        $statusClass = match ($rma['status']) {
                                            'Pending'          => 'status-pending',
                                            'Sent to Supplier' => 'status-sent',
                                            'Resolved'         => 'status-resolved',
                                            default            => 'status-pending'
                                        };
                                        ?>
                                        <span class="status-badge <?= $statusClass ?>"><?= $rma['status'] ?></span>
                                    </td>
                                    <td style="color:var(--text-muted);"><?= date('M d, Y', strtotime($rma['date'])) ?></td>
                                    <td style="text-align:center;">
                                        <div style="display:flex; gap:6px; justify-content:center;">
                                            <!-- Print PDF -->
                                            <button class="btn-action preview-rma-btn"
                                                data-id="<?= $rma['rma_id'] ?>"
                                                title="Generate PDF">
                                                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9.5a2 2 0 00-.586-1.414l-4.5-4.5A2 2 0 0015.5 3H5a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2z" />
                                                </svg>
                                            </button>
                                            <!-- Edit -->
                                            <button class="btn-action edit-rma-btn"
                                                data-id="<?= $rma['rma_id'] ?>"
                                                title="Edit"
                                                style="color:var(--accent); border-color:rgba(245,166,35,.3);">
                                                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </button>
                                            <!-- Archive -->
                                            <button class="btn-action danger archive-rma-btn"
                                                data-id="<?= $rma['rma_id'] ?>"
                                                data-number="<?= htmlspecialchars($rma['rma_number']) ?>"
                                                title="Archive">
                                                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v0a2 2 0 01-2 2M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8">
                                    <div class="empty-state">
                                        <div class="empty-icon">📦</div>
                                        <p><?= $search ? 'No RMA records found matching your search.' : 'No RMA records yet. Create one above.' ?></p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1):
                $q = $search ? '&search=' . urlencode($search) : '';
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

    </main>

    <!-- MODAL: Archive RMA -->
    <div id="archiveRmaModal" class="modal-overlay">
        <div class="modal-box">
            <h3 style="color:var(--danger);">Archive RMA</h3>
            <p>Are you sure you want to archive <strong id="archiveRmaNumber" style="color:var(--text);"></strong>? It will be hidden from the list.</p>
            <form method="POST">
                <input type="hidden" name="rma_id" id="archiveRmaId">
                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="closeArchiveRmaModal()">Cancel</button>
                    <button type="submit" name="archive-rma-btn" class="btn btn-danger">Archive</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Item Picker Modal -->
    <div id="itemPickerModal" class="modal-overlay">
        <div class="modal-box" style="max-width:640px; width:90%; max-height:80vh; display:flex; flex-direction:column;">

            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:16px; flex-shrink:0;">
                <h3>Select Item</h3>
                <button type="button" onclick="closeItemModal()" style="background:none; border:none; color:var(--text-muted); font-size:26px; cursor:pointer; line-height:1;">×</button>
            </div>

            <!-- Search -->
            <div style="position:relative; margin-bottom:12px; flex-shrink:0;">
                <svg style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:var(--text-muted);" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8" />
                    <path d="m21 21-4.35-4.35" />
                </svg>
                <input type="text" id="itemPickerSearch" placeholder="Search items…" style="
                width:100%;
                background:var(--bg);
                border:1.5px solid var(--border);
                border-radius:10px;
                padding:9px 14px 9px 38px;
                color:var(--text);
                font-size:13px;
                outline:none;
            ">
            </div>

            <!-- Category Filter -->
            <div id="itemPickerCategories" style="display:flex; gap:8px; flex-wrap:wrap; margin-bottom:14px; flex-shrink:0;"></div>

            <!-- Item List -->
            <div id="itemPickerList" style="overflow-y:auto; flex:1; display:flex; flex-direction:column; gap:6px;"></div>

        </div>
    </div>

    <!-- Edit RMA Modal -->
    <div id="editRmaModal" class="modal-overlay">
        <div class="modal-box" style="max-width:580px; width:90%; max-height:90vh; overflow-y:auto;">

            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">
                <h3>Edit RMA — <span id="editRmaNumber" style="color:var(--accent);"></span></h3>
                <button type="button" onclick="closeEditRmaModal()" style="background:none; border:none; color:var(--text-muted); font-size:26px; cursor:pointer; line-height:1;">×</button>
            </div>

            <form method="POST" id="editRmaForm">
                <input type="hidden" name="rma_id" id="editRmaId">

                <div class="form-grid" style="margin-bottom:16px;">

                    <div class="form-group">
                        <label class="form-label">Customer Name <span style="color:var(--danger)">*</span></label>
                        <input type="text" name="customer_name" id="editCustomerName" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Related Transaction <span style="color:var(--danger)">*</span></label>
                        <div class="item-selector-wrap">
                            <input type="hidden" name="sale_id" id="editSelectedSaleId" required>
                            <div class="item-selector-display" id="editSaleSelectorDisplay" onclick="openEditSaleModal()">
                                <span class="item-selector-placeholder">— Select transaction —</span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Condition <span style="color:var(--danger)">*</span></label>
                        <select name="condition" id="editCondition" class="form-select" required>
                            <option value="Defective">Defective</option>
                            <option value="Damaged">Damaged</option>
                            <option value="Wrong Item">Wrong Item</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select name="status" id="editStatus" class="form-select">
                            <option value="Pending">Pending</option>
                            <option value="Sent to Supplier">Sent to Supplier</option>
                            <option value="Resolved">Resolved</option>
                        </select>
                    </div>

                    <div class="form-group full">
                        <label class="form-label">Reason <span style="color:var(--danger)">*</span></label>
                        <textarea name="reason" id="editReason" class="form-textarea" required></textarea>
                    </div>

                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="closeEditRmaModal()">Cancel</button>
                    <button type="submit" name="edit-rma-btn" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Sale Picker Modal -->
    <div id="salePickerModal" class="modal-overlay">
        <div class="modal-box" style="max-width:600px; width:90%; max-height:80vh; display:flex; flex-direction:column;">

            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:16px; flex-shrink:0;">
                <h3>Select Transaction</h3>
                <button type="button" onclick="closeSaleModal()" style="background:none; border:none; color:var(--text-muted); font-size:26px; cursor:pointer; line-height:1;">×</button>
            </div>

            <!-- Search -->
            <div style="position:relative; margin-bottom:14px; flex-shrink:0;">
                <svg style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:var(--text-muted); pointer-events:none;" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8" />
                    <path d="m21 21-4.35-4.35" />
                </svg>
                <input type="text" id="salePickerSearch" placeholder="Search by transaction ID or customer…" style="
                width:100%;
                background:var(--bg);
                border:1.5px solid var(--border);
                border-radius:10px;
                padding:9px 14px 9px 38px;
                color:var(--text);
                font-size:13px;
                outline:none;
                font-family:'DM Sans', sans-serif;
            ">
            </div>

            <!-- Clear selection -->
            <div style="margin-bottom:10px; flex-shrink:0;">
                <button type="button" onclick="clearSaleSelection()" style="
                background:none;
                border:none;
                color:var(--text-muted);
                font-size:12px;
                cursor:pointer;
                padding:0;
                text-decoration:underline;
            ">Clear selection</button>
            </div>

            <!-- Sale List -->
            <div id="salePickerList" style="overflow-y:auto; flex:1; display:flex; flex-direction:column; gap:6px;"></div>

        </div>
    </div>

    <script>
        function openEditSaleModal() {
            document.getElementById('salePickerSearch').value = '';
            renderSales('');

            // Override selectSale temporarily for edit context
            window._salePickerTarget = 'edit';
            document.getElementById('salePickerModal').classList.add('active');
            setTimeout(() => document.getElementById('salePickerSearch').focus(), 100);
        }
    </script>

    <script>
        const allSales = <?= json_encode(array_map(function ($sale) {
                                return [
                                    'sale_id'        => $sale['sale_id'],
                                    'transaction_id' => $sale['transaction_id'],
                                    'customer_name'  => $sale['customer_name'],
                                    'date'           => date('M d, Y', strtotime($sale['date'])),
                                ];
                            }, $sales)) ?>;

        function openSaleModal() {
            document.getElementById('salePickerSearch').value = '';
            renderSales('');
            document.getElementById('salePickerModal').classList.add('active');
            setTimeout(() => document.getElementById('salePickerSearch').focus(), 100);
        }

        function closeSaleModal() {
            document.getElementById('salePickerModal').classList.remove('active');
        }

        function clearSaleSelection() {
            document.getElementById('selectedSaleId').value = '';
            document.getElementById('saleSelectorDisplay').innerHTML = `
        <span class="item-selector-placeholder">— No linked transaction —</span>
    `;
            closeSaleModal();
        }

        function renderSales(search) {
            const list = document.getElementById('salePickerList');
            list.innerHTML = '';

            const q = search.toLowerCase().trim();

            const filtered = allSales.filter(sale =>
                q === '' ||
                sale.transaction_id.toLowerCase().includes(q) ||
                sale.customer_name.toLowerCase().includes(q)
            );

            if (filtered.length === 0) {
                list.innerHTML = `<div style="text-align:center; padding:32px; color:var(--text-muted); font-size:13px;">No transactions found.</div>`;
                return;
            }

            filtered.forEach(sale => {
                const row = document.createElement('div');
                row.style.cssText = `
            display:flex;
            align-items:center;
            justify-content:space-between;
            background:var(--surface2);
            border:1.5px solid var(--border);
            border-radius:10px;
            padding:10px 14px;
            cursor:pointer;
            transition:border-color .15s;
        `;

                row.innerHTML = `
            <div>
                <div style="font-weight:700; font-size:13px; color:var(--accent);">${sale.transaction_id}</div>
                <div style="font-size:12px; color:var(--text-muted); margin-top:2px;">${sale.customer_name} &mdash; ${sale.date}</div>
            </div>
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="color:var(--accent); flex-shrink:0;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4"/>
                <circle cx="12" cy="12" r="10"/>
            </svg>
        `;

                row.onmouseenter = () => row.style.borderColor = 'var(--accent)';
                row.onmouseleave = () => row.style.borderColor = 'var(--border)';

                row.onclick = () => selectSale(sale);
                list.appendChild(row);
            });
        }

        function selectSale(sale) {
            if (window._salePickerTarget === 'edit') {
                document.getElementById('editSelectedSaleId').value = sale.sale_id;
                document.getElementById('editSaleSelectorDisplay').innerHTML = `
            <span class="item-selector-chosen">${sale.transaction_id} — ${sale.customer_name}</span>
            <svg class="item-selector-chevron" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
            </svg>
        `;
            } else {
                document.getElementById('selectedSaleId').value = sale.sale_id;
                document.getElementById('saleSelectorDisplay').innerHTML = `
            <span class="item-selector-chosen">${sale.transaction_id} — ${sale.customer_name}</span>
            <svg class="item-selector-chevron" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
            </svg>
        `;
            }
            window._salePickerTarget = null;
            closeSaleModal();
        }

        document.getElementById('salePickerSearch').addEventListener('input', function() {
            renderSales(this.value);
        });

        document.getElementById('salePickerModal').addEventListener('click', function(e) {
            if (e.target === this) closeSaleModal();
        });
    </script>

    <script>
        // Edit modal
        document.querySelectorAll('.edit-rma-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.dataset.id;

                fetch(`rma_get.php?id=${id}`)
                    .then(r => r.json())
                    .then(data => {
                        document.getElementById('editRmaId').value = data.rma_id;
                        document.getElementById('editRmaNumber').textContent = data.rma_number;
                        document.getElementById('editCustomerName').value = data.customer_name;
                        document.getElementById('editCondition').value = data.condition;
                        document.getElementById('editStatus').value = data.status;
                        document.getElementById('editReason').value = data.reason;

                        // Pre-fill sale selector
                        if (data.sale_id && data.transaction_id) {
                            document.getElementById('editSelectedSaleId').value = data.sale_id;
                            document.getElementById('editSaleSelectorDisplay').innerHTML = `
                        <span class="item-selector-chosen">${data.transaction_id} — ${data.customer_name}</span>
                        <svg class="item-selector-chevron" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                    `;
                        } else {
                            document.getElementById('editSelectedSaleId').value = '';
                            document.getElementById('editSaleSelectorDisplay').innerHTML = `
                        <span class="item-selector-placeholder">— Select transaction —</span>
                    `;
                        }

                        document.getElementById('editRmaModal').classList.add('active');
                    });
            });
        });

        function closeEditRmaModal() {
            document.getElementById('editRmaModal').classList.remove('active');
        }

        document.getElementById('editRmaModal').addEventListener('click', function(e) {
            if (e.target === this) closeEditRmaModal();
        });
    </script>

    <script>
        // Sidebar toggle
        const sidebar = document.getElementById('mobile-sidebar');
        const toggleBtn = document.getElementById('sidebar-toggle');
        if (toggleBtn && sidebar) {
            toggleBtn.addEventListener('click', () => sidebar.classList.toggle('-translate-x-full'));
        }

        // Auto-dismiss alerts
        ['successAlert', 'errorAlert'].forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                setTimeout(() => {
                    el.style.opacity = '0';
                    el.style.transition = 'opacity .5s';
                }, 3000);
                setTimeout(() => el.remove(), 3500);
            }
        });

        // Search debounce
        let searchTimeout;
        document.getElementById('rmaSearchInput')?.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => this.closest('form').submit(), 500);
        });

        // PDF preview
        document.querySelectorAll('.preview-rma-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                window.open(`preview_rma_pdf.php?id=${btn.dataset.id}`, '_blank');
            });
        });

        // Archive modal
        document.querySelectorAll('.archive-rma-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.getElementById('archiveRmaId').value = btn.dataset.id;
                document.getElementById('archiveRmaNumber').textContent = btn.dataset.number;
                document.getElementById('archiveRmaModal').classList.add('active');
            });
        });

        function closeArchiveRmaModal() {
            document.getElementById('archiveRmaModal').classList.remove('active');
        }

        document.getElementById('archiveRmaModal').addEventListener('click', function(e) {
            if (e.target === this) closeArchiveRmaModal();
        });
    </script>

    <script>
        // Build item options HTML once from PHP
        const itemOptions = `<option value="">— Select item —</option>` +
            <?= json_encode(implode('', array_map(function ($item) {
                return '<option value="' . $item['item_id'] . '">' . htmlspecialchars($item['item_name']) . '</option>';
            }, $items))) ?>;

        // All items from PHP
        const allItems = <?= json_encode(array_map(function ($item) {
                                return [
                                    'item_id'       => $item['item_id'],
                                    'item_name'     => $item['item_name'],
                                    'category_id'   => $item['category_id'],
                                    'category_name' => $item['category_name'],
                                ];
                            }, $items)) ?>;

        // All categories derived from items
        const allCategories = (() => {
            const map = {};
            allItems.forEach(item => {
                if (!map[item.category_id]) {
                    map[item.category_id] = item.category_name;
                }
            });
            return Object.entries(map).map(([id, name]) => ({
                id,
                name
            }));
        })();

        let activeItemDisplay = null; // the .item-selector-display that triggered the modal
        let activeHiddenInput = null; // the hidden input to update
        let activeCategoryFilter = 'all';

        /* ── OPEN MODAL ── */
        function openItemModal(displayEl) {
            activeItemDisplay = displayEl;
            activeHiddenInput = displayEl.closest('.item-selector-wrap').querySelector('.selected-item-id');
            activeCategoryFilter = 'all';

            document.getElementById('itemPickerSearch').value = '';
            renderCategories();
            renderItems('', 'all');

            document.getElementById('itemPickerModal').classList.add('active');
            setTimeout(() => document.getElementById('itemPickerSearch').focus(), 100);
        }

        /* ── CLOSE MODAL ── */
        function closeItemModal() {
            document.getElementById('itemPickerModal').classList.remove('active');
            activeItemDisplay = null;
            activeHiddenInput = null;
        }

        /* ── RENDER CATEGORY PILLS ── */
        function renderCategories() {
            const container = document.getElementById('itemPickerCategories');
            container.innerHTML = '';

            const all = document.createElement('button');
            all.type = 'button';
            all.textContent = 'All';
            all.className = 'cat-pill' + (activeCategoryFilter === 'all' ? ' cat-pill-active' : '');
            all.onclick = () => {
                activeCategoryFilter = 'all';
                renderCategories();
                renderItems(document.getElementById('itemPickerSearch').value, 'all');
            };
            container.appendChild(all);

            allCategories.forEach(cat => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.textContent = cat.name;
                btn.className = 'cat-pill' + (activeCategoryFilter === cat.id ? ' cat-pill-active' : '');
                btn.onclick = () => {
                    activeCategoryFilter = cat.id;
                    renderCategories();
                    renderItems(document.getElementById('itemPickerSearch').value, cat.id);
                };
                container.appendChild(btn);
            });
        }

        function buildItemRow() {
            const row = document.createElement('div');
            row.className = 'rma-item-row';
            row.style.cssText = 'display:grid; grid-template-columns:1fr 100px 36px; gap:10px; margin-bottom:10px; align-items:center;';
            row.innerHTML = `
        <div class="item-selector-wrap">
            <input type="hidden" name="item_id[]" class="selected-item-id">
            <div class="item-selector-display" onclick="openItemModal(this)">
                <span class="item-selector-placeholder">— Select item —</span>
            </div>
        </div>
        <input type="number" name="quantity[]" class="form-input" min="1" value="1" placeholder="Qty" required>
        <button type="button" class="remove-item-row-btn" style="
            width:36px; height:36px;
            background:rgba(255,92,92,.1);
            border:1.5px solid rgba(255,92,92,.25);
            border-radius:8px;
            color:var(--danger);
            cursor:pointer;
            display:flex;
            align-items:center;
            justify-content:center;
            flex-shrink:0;
        " title="Remove row">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    `;
            return row;
        }

        /* ── RENDER ITEM LIST ── */
        function renderItems(search, categoryId) {
            const list = document.getElementById('itemPickerList');
            list.innerHTML = '';

            const q = search.toLowerCase().trim();

            const filtered = allItems.filter(item => {
                const matchSearch = q === '' || item.item_name.toLowerCase().includes(q);
                const matchCategory = categoryId === 'all' || String(item.category_id) === String(categoryId);
                return matchSearch && matchCategory;
            });

            if (filtered.length === 0) {
                list.innerHTML = `<div style="text-align:center; padding:32px; color:var(--text-muted); font-size:13px;">No items found.</div>`;
                return;
            }

            filtered.forEach(item => {
                const row = document.createElement('div');
                row.style.cssText = `
            display:flex;
            align-items:center;
            justify-content:space-between;
            background:var(--surface2);
            border:1.5px solid var(--border);
            border-radius:10px;
            padding:10px 14px;
            cursor:pointer;
            transition:border-color .15s;
        `;

                row.innerHTML = `
            <div>
                <div style="font-weight:600; font-size:13px;">${item.item_name}</div>
                <div style="font-size:11px; color:var(--text-muted); margin-top:2px;">${item.category_name}</div>
            </div>
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="color:var(--accent); flex-shrink:0;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4"/>
                <circle cx="12" cy="12" r="10"/>
            </svg>
        `;

                row.onmouseenter = () => row.style.borderColor = 'var(--accent)';
                row.onmouseleave = () => row.style.borderColor = 'var(--border)';

                row.onclick = () => selectItem(item);
                list.appendChild(row);
            });
        }

        /* ── SELECT ITEM ── */
        function selectItem(item) {
            if (!activeHiddenInput || !activeItemDisplay) return;

            activeHiddenInput.value = item.item_id;

            activeItemDisplay.innerHTML = `
        <span class="item-selector-chosen">${item.item_name}</span>
        <svg class="item-selector-chevron" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
        </svg>
    `;

            closeItemModal();
        }

        /* ── SEARCH INPUT ── */
        document.getElementById('itemPickerSearch').addEventListener('input', function() {
            renderItems(this.value, activeCategoryFilter);
        });

        /* ── CLOSE ON BACKDROP ── */
        document.getElementById('itemPickerModal').addEventListener('click', function(e) {
            if (e.target === this) closeItemModal();
        });

        // Add row
        document.getElementById('addItemRowBtn').addEventListener('click', () => {
            const container = document.getElementById('rmaItemsContainer');
            container.appendChild(buildItemRow());
            updateRemoveButtons();
        });

        // Remove row (event delegation)
        document.getElementById('rmaItemsContainer').addEventListener('click', function(e) {
            const btn = e.target.closest('.remove-item-row-btn');
            if (!btn) return;
            const rows = document.querySelectorAll('.rma-item-row');
            if (rows.length <= 1) return; // keep at least 1 row
            btn.closest('.rma-item-row').remove();
            updateRemoveButtons();
        });

        // Hide remove button if only 1 row remains
        function updateRemoveButtons() {
            const rows = document.querySelectorAll('.rma-item-row');
            rows.forEach(row => {
                const btn = row.querySelector('.remove-item-row-btn');
                if (btn) btn.style.visibility = rows.length <= 1 ? 'hidden' : 'visible';
            });
        }

        // Init
        updateRemoveButtons();
    </script>

</body>

</html>