<?php
include "database/database.php";
$database->login_session();
$database->createPcBuilder();
$database->archiveQuotation();
$categories = $database->getAllCategories();

$itemsByCategory = [];
foreach ($categories as $category) {
    $itemsByCategory[] = [
        'id'                => $category['category_id'],
        'name'              => $category['category_name'],
        'slug'              => $category['category_slug'],
        'category_type'     => $category['category_type'],
        'supports_quantity' => (int) $category['supports_quantity'],
        'items'             => $database->getItemsByCategoryId($category['category_id']),
    ];
}

$search      = trim($_GET['search'] ?? '');
$page        = max(1, (int)($_GET['page'] ?? 1));
$perPage     = 8;
$offset      = ($page - 1) * $perPage;

$totalRecords = $database->getPcBuildersCount($search);
$totalPages   = max(1, ceil($totalRecords / $perPage));

$pcBuilders   = $database->getPcBuildersPaginated($search, $offset, $perPage);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>POS & Inventory - Quotations</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="assets/tailwind.min.css" rel="stylesheet" />
    <link href="assets/fonts.css" rel="stylesheet" />
    <link href="assets/quotation.css" rel="stylesheet" />

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
            margin-bottom: 32px;
        }

        .card .qm-btn-cancel {
            background: var(--surface2);
            color: var(--text);
            margin-right: 12px;
            transition: background 0.2s, color 0.2s;
        }

        .card .qm-btn-cancel:hover {
            background: #33354a;
            color: var(--text);
        }

        .card .qm-btn-cancel:active {
            background: #2e3347;
            color: var(--text);
        }

        .card:focus-within .qm-btn-cancel {
            background: var(--surface2);
            color: var(--text);
        }

        .card input[type="number"]:focus {
            background: #2e3347;
            color: var(--text);
            outline: none;
            border-color: var(--accent);
        }

        .card input[type="number"]:active {
            background: #2a2d3f;
            color: var(--text);
        }

        .card input[type="text"]:focus {
            background: #2e3347;
            color: var(--text);
            outline: none;
            border-color: var(--accent);
        }

        .card input[type="text"]:active {
            background: #2a2d3f;
            color: var(--text);
        }

        .qm-body {
            display: flex;
            gap: 32px;
            flex-wrap: wrap;
        }

        .qm-left {
            flex: 3;
            min-width: 340px;
        }

        .qm-name-input,
        .qm-select,
        .qm-qty-input {
            width: 100%;
            background: var(--bg);
            border: 1.5px solid var(--border);
            border-radius: 8px;
            padding: 10px 14px;
            color: var(--text);
            font-size: 14px;
        }

        .qm-section-label {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 32px 0 16px;
        }

        .qm-section-line {
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        .pill {
            padding: 6px 14px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
        }

        .pill-blue {
            background: rgba(59, 130, 246, .2);
            color: #60a5fa;
        }

        .pill-green {
            background: rgba(34, 197, 94, .2);
            color: #4ade80;
        }

        .qm-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 16px;
        }

        .qm-comp-card {
            background: var(--surface2);
            border: 1.5px solid var(--border);
            border-radius: 12px;
            padding: 16px;
        }

        .qm-comp-title {
            font-weight: 600;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .qm-comp-dot {
            width: 10px;
            height: 10px;
            background: var(--accent);
            border-radius: 50%;
        }

        .qm-qty-row {
            margin-top: 10px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .qm-qty-label {
            font-size: 13px;
            color: var(--text-muted);
        }

        .qm-qty-input {
            width: 80px;
            text-align: center;
        }

        .qm-footer {
            margin-top: 32px;
            text-align: left;
            background: var(--surface);
            padding: 16px;
            border-top: 1px solid var(--border);
            border-radius: 0 0 16px 16px;
        }

        .qm-btn {
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            border: none;
        }

        .qm-btn-save {
            background: var(--accent);
            color: #111;
        }

        /* ────────────────────────────────────────────── */
        /* Keep your original toolbar, table, pagination etc styles */
        .toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 24px;
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
        }

        .search-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
        }

        .search-clear {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
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

        .data-table thead th {
            padding: 10px 16px;
            text-align: left;
            font-size: 11px;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
        }

        .data-table tbody tr:hover {
            background: rgba(255, 255, 255, .02);
        }

        .data-table td {
            padding: 13px 16px;
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
        }

        .page-btn:hover {
            border-color: var(--accent);
            background: rgba(245, 166, 35, .08);
            color: var(--accent);
        }

        .page-btn.active {
            background: var(--accent);
            color: #111;
        }

        .page-btn.disabled {
            opacity: .5;
            pointer-events: none;
        }

        /* New / adjusted for card selector */
        .selected-comp-card {
            background: var(--surface2);
            border: 1.5px solid var(--border);
            border-radius: 12px;
            padding: 16px;
            cursor: pointer;
            transition: border-color 0.2s;
        }

        .selected-comp-card:hover,
        .selected-comp-card:focus-within {
            border-color: var(--accent);
        }

        .selected-placeholder {
            color: var(--text-muted);
            font-style: italic;
        }

        .choose-btn {
            margin-top: 12px;
            background: var(--accent);
            color: #111;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
        }

        .choose-btn:hover {
            opacity: 0.9;
        }

        /* Modal for item selection */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.7);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-content {
            background: var(--surface);
            border: 1.5px solid var(--border);
            border-radius: 16px;
            width: 90%;
            max-width: 1000px;
            max-height: 85vh;
            overflow-y: auto;
            padding: 24px;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .modal-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 16px;
        }

        .item-card {
            background: var(--surface2);
            border: 1.5px solid var(--border);
            border-radius: 12px;
            padding: 12px;
            text-align: center;
            transition: all 0.2s;
        }

        .item-card:hover {
            border-color: var(--accent);
            transform: translateY(-4px);
        }

        .item-card img {
            width: 100%;
            height: 120px;
            object-fit: contain;
            background: #0002;
            border-radius: 8px;
            margin-bottom: 10px;
        }

        .item-price {
            color: var(--accent);
            font-weight: 700;
            margin: 8px 0 12px;
        }

        .select-item-btn {
            background: var(--accent);
            color: #111;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }

        /* Hide original selects */
        .hidden-select {
            display: none;
        }

        .selected-comp-card {
            background: var(--surface2);
            border: 1.5px solid var(--border);
            border-radius: 12px;
            padding: 14px;
            margin: 12px 0;
            min-height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            transition: all 0.18s;
        }

        .selected-comp-card:hover {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(245, 166, 35, 0.15);
        }

        .selected-placeholder {
            color: var(--text-muted);
            font-style: italic;
        }

        .choose-btn {
            background: var(--accent);
            color: #111;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 10px;
            width: 100%;
        }

        .choose-btn:hover {
            opacity: 0.92;
        }

        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.75);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-content {
            background: var(--surface);
            border: 1.5px solid var(--border);
            border-radius: 16px;
            width: 90%;
            max-width: 1100px;
            max-height: 85vh;
            overflow-y: auto;
            padding: 24px;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .close-modal {
            background: none;
            border: none;
            color: var(--text-muted);
            font-size: 32px;
            cursor: pointer;
        }

        .modal-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 16px;
        }

        .modal-item-card {
            background: var(--surface2);
            border: 1.5px solid var(--border);
            border-radius: 12px;
            padding: 12px;
            text-align: center;
            transition: all 0.2s;
        }

        .modal-item-card:hover {
            border-color: var(--accent);
            transform: translateY(-3px);
        }

        .modal-item-name {
            font-weight: 600;
            margin: 8px 0 6px;
            min-height: 40px;
        }

        .modal-item-price {
            color: var(--accent);
            font-weight: 700;
        }

        .modal-select-btn {
            margin-top: 10px;
            background: var(--accent);
            color: #111;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }
    </style>
</head>

<body>

    <button class="mobile-toggle" id="sidebar-toggle">☰</button>
    <?php include "sidebar.php"; ?>

    <main style="margin-left:240px;">

        <!-- ─── New Quotation Form (always visible) ─── -->
        <div class="card">

            <div class="qm-body" style="display: block;">

                <div class="qm-left">
                    <form id="pc-builder-form" method="POST">

                        <div style="margin-bottom:24px;">
                            <label style="display:block; margin-bottom:8px; font-weight:600;">Quotation Name</label>
                            <input type="text" name="pc_builder_name" id="pc_builder_name" required placeholder="e.g. Gaming PC 2025, Office Workstation..." class="qm-name-input">
                        </div>

                        <?php
                        $pcParts    = array_filter($itemsByCategory, fn($cat) => $cat['category_type'] === 'pc_part');
                        $accessories = array_filter($itemsByCategory, fn($cat) => $cat['category_type'] === 'accessory');
                        ?>

                        <?php if (!empty($pcParts)): ?>
                            <div class="qm-section-label">
                                <span class="pill pill-blue">PC Parts</span>
                                <div class="qm-section-line"></div>
                            </div>
                            <div class="qm-grid">
                                <?php foreach ($pcParts as $category): ?>
                                    <div class="qm-comp-card">
                                        <div class="qm-comp-title">
                                            <span class="qm-comp-dot"></span>
                                            <?= htmlspecialchars($category['name']) ?>
                                        </div>

                                        <div class="selected-comp-card" id="selected-card-<?= $category['id'] ?>" data-category-id="<?= $category['id'] ?>">
                                            <div class="selected-placeholder">Not selected</div>
                                        </div>

                                        <button type="button" class="choose-btn"
                                            onclick="openComponentSelector(<?= $category['id'] ?>, '<?= addslashes(htmlspecialchars($category['name'])) ?>')">
                                            Choose / Change
                                        </button>

                                        <input type="hidden" name="category_<?= $category['id'] ?>" id="hidden-<?= $category['id'] ?>" value="">

                                        <?php if ($category['supports_quantity']): ?>
                                            <div class="qm-qty-row" style="margin-top: 12px;">
                                                <span class="qm-qty-label">Qty</span>
                                                <input type="number" name="quantity_<?= $category['id'] ?>" min="1" value="1" class="qm-qty-input">
                                            </div>
                                        <?php endif; ?>

                                        <button type="button" class="reset-item-btn" data-category-id="<?= $category['id'] ?>" style="display:none; margin-top:8px;">
                                            Reset
                                        </button>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($accessories)): ?>
                            <div class="qm-section-label">
                                <span class="pill pill-green">Accessories</span>
                                <div class="qm-section-line"></div>
                            </div>
                            <div class="qm-grid">
                                <?php foreach ($accessories as $category): ?>
                                    <div class="qm-comp-card">
                                        <div class="qm-comp-title">
                                            <span class="qm-comp-dot"></span>
                                            <?= htmlspecialchars($category['name']) ?>
                                        </div>

                                        <!-- Selected preview -->
                                        <div class="selected-comp-card" id="selected-card-<?= $category['id'] ?>" data-category-id="<?= $category['id'] ?>">
                                            <div class="selected-placeholder">Not selected</div>
                                        </div>

                                        <!-- Choose / Change button -->
                                        <button type="button" class="choose-btn"
                                            onclick="openComponentSelector(<?= $category['id'] ?>, '<?= addslashes(htmlspecialchars($category['name'])) ?>')">
                                            Choose / Change
                                        </button>

                                        <!-- Hidden input -->
                                        <input type="hidden" name="category_<?= $category['id'] ?>" id="hidden-<?= $category['id'] ?>" value="">

                                        <!-- Quantity row (optional) -->
                                        <?php if ($category['supports_quantity']): ?>
                                            <div class="qm-qty-row" style="margin-top: 12px;">
                                                <span class="qm-qty-label">Qty</span>
                                                <input type="number" name="quantity_<?= $category['id'] ?>" min="1" value="1" class="qm-qty-input">
                                                <!-- Reset button next to quantity -->
                                                <button type="button" class="reset-item-btn" data-category-id="<?= $category['id'] ?>" style="display:none; margin-left:8px;">
                                                    Reset
                                                </button>
                                            </div>
                                        <?php else: ?>
                                            <!-- Reset button below preview if no quantity -->
                                            <button type="button" class="reset-item-btn" data-category-id="<?= $category['id'] ?>" style="display:none; margin-top:8px;">
                                                Reset
                                            </button>
                                        <?php endif; ?>

                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                    </form>
                </div>

            </div>

            <div class="qm-footer">
                <button type="button" class="qm-btn qm-btn-cancel" id="resetFormBtn">Reset All</button>
                <button type="submit" form="pc-builder-form" name="pc-build-btn" class="qm-btn qm-btn-save">
                    Save Quotation
                </button>
            </div>

        </div>

        <!-- Saved Quotations Table -->
        <div class="card">

            <div class="toolbar">
                <div class="search-wrap">
                    <svg class="search-icon" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="8" />
                        <path d="m21 21-4.35-4.35" />
                    </svg>
                    <form method="GET" id="quotationFilterForm" style="margin:0;">
                        <input type="text" name="search" id="searchInput" value="<?= htmlspecialchars($search) ?>" placeholder="Search quotations..." class="form-input">
                        <?php if ($search !== ''): ?>
                            <a href="quotation.php" class="search-clear">×</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

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
                            <th>Quotation Name</th>
                            <th>Created By</th>
                            <th>Total</th>
                            <th>Created</th>
                            <th style="text-align:center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($pcBuilders)): ?>
                            <?php foreach ($pcBuilders as $builder): ?>
                                <tr>
                                    <td style="font-weight:600;"><?= htmlspecialchars($builder['pc_builder_name']) ?></td>
                                    <td class="muted"><?= htmlspecialchars($builder['created_by'] ?? '—') ?></td>
                                    <td style="color:var(--accent); font-weight:700;">
                                        ₱<?= number_format($builder['total_price'] ?? 0, 2) ?>
                                    </td>
                                    <td class="muted"><?= date('M d, Y', strtotime($builder['created_at'])) ?></td>
                                    <td style="text-align:center; display:flex; gap:8px; justify-content:center;">
                                        <button class="btn-action preview-quote-btn"
                                            data-id="<?= $builder['pc_builder_id'] ?>"
                                            title="Generate PDF">
                                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9.5a2 2 0 00-.586-1.414l-4.5-4.5A2 2 0 0015.5 3H5a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2z" />
                                            </svg>
                                        </button>
                                        <button class="btn-action archive-quote-btn"
                                            data-id="<?= $builder['pc_builder_id'] ?>"
                                            data-name="<?= htmlspecialchars($builder['pc_builder_name']) ?>"
                                            title="Archive"
                                            style="color:var(--danger); border-color:rgba(255,92,92,.3);">
                                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v0a2 2 0 01-2 2M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align:center; padding:60px 20px; color:var(--text-muted); font-size:15px;">
                                    <?= $search ? 'No matching quotations found.' : 'No saved quotations yet.' ?>
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

        <!-- Component Selection Modal -->
        <div id="componentModal" class="modal-overlay">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 id="modalCategoryTitle">Select Component</h3>
                    <button type="button" class="close-modal" onclick="closeComponentModal()">×</button>
                </div>
                <div id="modalItemGrid" class="modal-grid"></div>
            </div>
        </div>

        <!-- Archive Modal -->
        <div id="archiveQuoteModal" class="modal-overlay">
            <div class="modal-content" style="max-width:420px;">
                <div class="modal-header">
                    <h3 style="color:var(--danger);">Archive Quotation</h3>
                    <button type="button" class="close-modal" onclick="closeArchiveModal()">×</button>
                </div>
                <p style="font-size:14px; color:var(--text-muted); margin-bottom:20px;">
                    Are you sure you want to archive <strong id="archiveQuoteName" style="color:var(--text);"></strong>?
                    It will be hidden from the list.
                </p>
                <form method="POST" id="archiveQuoteForm">
                    <input type="hidden" name="action" value="archive_quotation">
                    <input type="hidden" name="pc_builder_id" id="archiveQuoteId">
                    <div style="display:flex; justify-content:flex-end; gap:10px;">
                        <button type="button" class="qm-btn qm-btn-cancel" onclick="closeArchiveModal()">Cancel</button>
                        <button type="submit" name="archive-quote-btn" class="qm-btn" style="background:var(--danger); color:#fff;">Archive</button>
                    </div>
                </form>
            </div>
        </div>

    </main>

    <script>
        // Archive modal
        document.querySelectorAll('.archive-quote-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.getElementById('archiveQuoteId').value = btn.dataset.id;
                document.getElementById('archiveQuoteName').textContent = btn.dataset.name;
                document.getElementById('archiveQuoteModal').classList.add('active');
            });
        });

        function closeArchiveModal() {
            document.getElementById('archiveQuoteModal').classList.remove('active');
        }

        document.getElementById('archiveQuoteModal').addEventListener('click', function(e) {
            if (e.target === this) closeArchiveModal();
        });
    </script>
    <script>
        // Preview PDF
        document.querySelectorAll('.preview-quote-btn').forEach(btn => {
            btn.addEventListener('click', e => {
                e.preventDefault();
                const id = btn.dataset.id;
                if (id) window.open(`preview_quotation_pdf.php?id=${id}`, '_blank');
            });
        });

        // Live search auto-submit
        let timeout;
        document.getElementById('searchInput')?.addEventListener('input', function() {
            clearTimeout(timeout);
            timeout = setTimeout(() => this.closest('form').submit(), 450);
        });

        // Sidebar margin sync
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

        // Simple form reset (you can expand this later for summary reset)
        document.getElementById('resetFormBtn')?.addEventListener('click', () => {
            document.getElementById('pc-builder-form').reset();
        });
    </script>
    <script>
        /* ================================
   CATEGORY ITEMS (FROM PHP)
================================= */
        const categoryItems = <?= json_encode(
                                    array_map(function ($cat) {
                                        return [
                                            'id'    => $cat['id'],
                                            'name'  => $cat['name'],
                                            'items' => array_map(function ($item) {
                                                return [
                                                    'item_id'       => $item['item_id'],
                                                    'item_name'     => $item['item_name'],
                                                    'selling_price' => (float)$item['selling_price'],
                                                    'image' => !empty($item['image'])
                                                        ? 'uploads/products/' . $item['image']
                                                        : 'uploads/products/image_not_available.png'
                                                ];
                                            }, $cat['items'] ?? [])
                                        ];
                                    }, $itemsByCategory),
                                    JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
                                ) ?>;

        let currentCategoryId = null;

        /* ================================
           OPEN MODAL
        ================================= */
        function openComponentSelector(categoryId, categoryName) {

            currentCategoryId = categoryId;

            const modal = document.getElementById('componentModal');
            const title = document.getElementById('modalCategoryTitle');
            const grid = document.getElementById('modalItemGrid');

            title.textContent = `Select ${categoryName}`;
            grid.innerHTML = '';

            const category = categoryItems.find(cat => cat.id == categoryId);

            if (!category || category.items.length === 0) {
                grid.innerHTML = `
            <div style="text-align:center; padding:40px; color:var(--text-muted);">
                No items available
            </div>`;
            } else {
                category.items.forEach(item => {

                    const card = document.createElement('div');
                    card.className = 'modal-item-card';

                    card.innerHTML = `
                <img src="${item.image}" 
                     style="width:100%; height:140px; object-fit:contain; background:#0002; border-radius:8px;">
                <div class="modal-item-name">${item.item_name}</div>
                <div class="modal-item-price">
                    ₱${Number(item.selling_price).toLocaleString('en-PH',{minimumFractionDigits:2})}
                </div>
                <button class="modal-select-btn">
                    Select
                </button>
            `;

                    card.querySelector('.modal-select-btn').addEventListener('click', function() {
                        selectComponent(categoryId, item.item_id, item.item_name, item.selling_price);
                    });

                    grid.appendChild(card);
                });
            }

            modal.classList.add('active');
        }

        /* ================================
           CLOSE MODAL
        ================================= */
        function closeComponentModal() {
            document.getElementById('componentModal').classList.remove('active');
            currentCategoryId = null;
        }
        /* ================================
           CLOSE WHEN CLICK OUTSIDE
        ================================= */
        document.getElementById('componentModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeComponentModal();
            }
        });

        document.getElementById('resetFormBtn')?.addEventListener('click', () => {
            const form = document.getElementById('pc-builder-form');
            form.reset(); // resets text and number inputs

            // Reset hidden fields and preview cards
            document.querySelectorAll('.selected-comp-card').forEach(card => {
                card.innerHTML = `<div class="selected-placeholder">Not selected</div>`;
            });

            document.querySelectorAll('input[type="hidden"]').forEach(hidden => {
                hidden.value = '';
            });
        });
        /* ================================
           SELECT COMPONENT
        ================================= */

        function selectComponent(categoryId, itemId, itemName, price) {
            const preview = document.getElementById('selected-card-' + categoryId);
            const hiddenInput = document.getElementById('hidden-' + categoryId);

            // Update preview card
            preview.innerHTML = `
        <div style="font-weight:600; margin-bottom:4px;">
            ${itemName}
        </div>
        <div style="color:var(--accent); font-size:14px;">
            ₱${Number(price).toLocaleString('en-PH',{minimumFractionDigits:2})}
        </div>
    `;

            // Set hidden input
            hiddenInput.value = itemId;

            // Show reset button
            const resetBtn = preview.closest('.qm-comp-card').querySelector('.reset-item-btn');
            if (resetBtn) resetBtn.style.display = 'inline-block';

            closeComponentModal();
        }

        // Reset button click
        document.querySelectorAll('.reset-item-btn').forEach(btn => {
            btn.addEventListener('click', e => {
                const categoryId = btn.dataset.categoryId;
                const preview = document.getElementById('selected-card-' + categoryId);
                const hiddenInput = document.getElementById('hidden-' + categoryId);
                const qtyInput = document.querySelector(`input[name="quantity_${categoryId}"]`);

                // Reset preview
                preview.innerHTML = `<div class="selected-placeholder">Not selected</div>`;

                // Reset hidden input
                hiddenInput.value = '';

                // Reset quantity to 1 if exists
                if (qtyInput) qtyInput.value = 1;

                // Hide this reset button
                btn.style.display = 'none';
            });
        });
    </script>

</body>

</html>