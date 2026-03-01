<?php
include "database/database.php";
$database->login_session();
$database->admin_session();

$database->create_category();
$database->update_category();
$database->archive_category();

$perPage = 5;
$search  = trim($_GET['search'] ?? '');
$page    = max(1, (int) ($_GET['page'] ?? 1));
$offset  = ($page - 1) * $perPage;

$totalCategories = $database->getTotalCategoriesCount($search);
$totalPages      = max(1, ceil($totalCategories / $perPage));
$categories      = $database->select_categories_paginated($offset, $perPage, $search);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS & Inventory - Categories</title>
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
            font-family: 'DM Sans', sans-serif;
            transition: margin-left .3s ease;
        }

        /* ── Page header ── */
        .page-header {
            margin-bottom: 24px;
        }

        .page-header h2 {
            font-size: 22px;
            font-weight: 700;
            color: var(--text);
        }

        .page-header p {
            font-size: 13px;
            color: var(--text-muted);
            margin-top: 2px;
        }

        /* ── Card ── */
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
            color: var(--text);
        }

        .card-header p {
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 2px;
        }

        /* ── Btn primary ── */
        .btn-primary {
            background: var(--accent);
            color: #111;
            border: none;
            border-radius: 10px;
            padding: 9px 18px;
            font-family: 'DM Sans', sans-serif;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: opacity .2s;
            white-space: nowrap;
        }

        .btn-primary:hover {
            opacity: .88;
        }

        /* ── Search ── */
        .search-wrap {
            position: relative;
            width: 300px;
        }

        .search-wrap input {
            width: 100%;
            background: var(--bg);
            border: 1.5px solid var(--border);
            border-radius: 10px;
            padding: 9px 36px 9px 38px;
            color: var(--text);
            font-family: 'DM Sans', sans-serif;
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
            text-decoration: none;
            font-size: 16px;
            line-height: 1;
            transition: color .2s;
        }

        .search-clear:hover {
            color: var(--danger);
        }

        /* ── Search info ── */
        .search-info {
            background: rgba(245, 166, 35, .08);
            border: 1px solid rgba(245, 166, 35, .2);
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .search-info a {
            color: var(--accent);
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
        }

        .search-info a:hover {
            text-decoration: underline;
        }

        /* ── Alerts ── */
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

        /* ── Table ── */
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

        .data-table tbody tr:last-child {
            border-bottom: none;
        }

        .data-table tbody tr:hover {
            background: rgba(255, 255, 255, .02);
        }

        .data-table tbody td {
            padding: 13px 16px;
            color: var(--text);
        }

        .data-table tbody td.muted {
            color: var(--text-muted);
        }

        .data-table .empty-row td {
            text-align: center;
            padding: 40px;
            color: var(--text-muted);
            font-size: 14px;
        }

        /* ── Action buttons ── */
        .btn-action {
            background: none;
            border: none;
            cursor: pointer;
            padding: 5px;
            border-radius: 7px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: background .15s, color .15s;
            color: var(--text-muted);
        }

        .btn-action.edit:hover {
            background: rgba(67, 211, 146, .1);
            color: var(--success);
        }

        .btn-action.archive:hover {
            background: rgba(255, 92, 92, .1);
            color: var(--danger);
        }

        /* ── Pagination ── */
        .pagination {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 20px;
            padding-top: 16px;
            border-top: 1px solid var(--border);
            flex-wrap: wrap;
            gap: 10px;
        }

        .pagination-info {
            font-size: 12px;
            color: var(--text-muted);
        }

        .pagination-nav {
            display: flex;
            align-items: center;
            gap: 4px;
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
            transition: border-color .2s, background .2s, color .2s;
            white-space: nowrap;
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
            color: var(--text-muted);
            cursor: not-allowed;
            pointer-events: none;
            opacity: .5;
        }

        .page-ellipsis {
            padding: 6px 8px;
            font-size: 12px;
            color: var(--text-muted);
        }

        /* ── Modal overlay ── */
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

        .modal-box h3 {
            font-size: 17px;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 20px;
        }

        .modal-box p {
            font-size: 14px;
            color: var(--text-muted);
            line-height: 1.6;
            margin-bottom: 22px;
        }

        .modal-box p strong {
            color: var(--text);
        }

        /* ── Form elements ── */
        .form-group {
            margin-bottom: 16px;
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
            font-family: 'DM Sans', sans-serif;
            font-size: 13px;
            outline: none;
            transition: border-color .2s;
        }

        .form-input:focus,
        .form-select:focus,
        .form-textarea:focus {
            border-color: var(--accent);
        }

        .form-input::placeholder,
        .form-textarea::placeholder {
            color: var(--text-muted);
        }

        .form-textarea {
            resize: none;
        }

        .form-select {
            cursor: pointer;
        }

        .form-select option {
            background: var(--surface2);
            color: var(--text);
        }

        .form-checkbox-row {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 16px;
        }

        .form-checkbox-row input[type="checkbox"] {
            width: 16px;
            height: 16px;
            cursor: pointer;
            accent-color: var(--accent);
        }

        .form-checkbox-row label {
            font-size: 13px;
            color: var(--text-muted);
            cursor: pointer;
        }

        /* ── Modal actions ── */
        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 22px;
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
            transition: border-color .2s, color .2s;
        }

        .btn-cancel:hover {
            color: var(--text);
            border-color: #555;
        }

        .btn-confirm-ok {
            background: var(--accent);
            border: none;
            color: #111;
            border-radius: 10px;
            padding: 9px 20px;
            font-family: 'DM Sans', sans-serif;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: opacity .2s;
        }

        .btn-confirm-ok:hover {
            opacity: .88;
        }

        .btn-confirm-warning {
            background: var(--warning);
            border: none;
            color: #111;
            border-radius: 10px;
            padding: 9px 20px;
            font-family: 'DM Sans', sans-serif;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: opacity .2s;
        }

        .btn-confirm-warning:hover {
            opacity: .88;
        }

        /* Scrollbar */
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

    <main class="flex-1 p-6" style="margin-left:240px;">

        <div class="page-header">
            <h2>Manage your product categories</h2>
        </div>

        <div class="card">

            <!-- Card Header -->
            <div class="card-header">
                <div>
                    <h3>Category Management</h3>
                    <p>Add, edit, or archive categories</p>
                </div>
                <button id="openAddCategoryModal" class="btn-primary">+ Add Category</button>
            </div>

            <!-- Search -->
            <form method="GET" action="" style="margin-bottom:16px;">
                <div class="search-wrap">
                    <svg class="search-icon" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="8" />
                        <path d="m21 21-4.35-4.35" />
                    </svg>
                    <input
                        type="text"
                        name="search"
                        id="searchInput"
                        value="<?= htmlspecialchars($search) ?>"
                        placeholder="Search categories…">
                    <?php if ($search !== ''): ?>
                        <a href="?" class="search-clear" title="Clear">✕</a>
                    <?php endif; ?>
                </div>
            </form>

            <!-- Alerts -->
            <?php if (isset($_SESSION['create-success'])): ?>
                <div id="successAlert" class="alert alert-success">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <?= $_SESSION['create-success'] ?>
                </div>
                <?php unset($_SESSION['create-success']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['create-error'])): ?>
                <div id="errorAlert" class="alert alert-error">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01" />
                    </svg>
                    <?= $_SESSION['create-error'] ?>
                </div>
                <?php unset($_SESSION['create-error']); ?>
            <?php endif; ?>

            <?php if ($search !== ''): ?>
                <div class="search-info">
                    <span>Results for "<strong style="color:var(--text)"><?= htmlspecialchars($search) ?></strong>" — <?= $totalCategories ?> result<?= $totalCategories !== 1 ? 's' : '' ?></span>
                    <a href="?">Clear search</a>
                </div>
            <?php endif; ?>

            <!-- Table -->
            <div style="overflow-x:auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Category Name</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($categories)): ?>
                            <?php foreach ($categories as $cat): ?>
                                <?php if ((int)$cat['is_deleted'] === 1) continue; ?>
                                <tr>
                                    <td style="font-weight:600;"><?= htmlspecialchars($cat['category_name']) ?></td>
                                    <td class="muted"><?= $cat['category_description'] ? htmlspecialchars($cat['category_description']) : '—' ?></td>
                                    <td>
                                        <div style="display:flex; align-items:center; gap:4px;">
                                            <button class="btn-action edit openEditCategoryModal"
                                                data-id="<?= $cat['category_id'] ?>"
                                                data-name="<?= htmlspecialchars($cat['category_name']) ?>"
                                                data-description="<?= htmlspecialchars($cat['category_description']) ?>"
                                                data-category-type="<?= htmlspecialchars($cat['category_type']) ?>"
                                                data-supports-quantity="<?= (int)$cat['supports_quantity'] ?>"
                                                title="Edit Category">
                                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </button>
                                            <button class="btn-action archive openArchiveCategoryModal"
                                                data-id="<?= $cat['category_id'] ?>"
                                                data-name="<?= htmlspecialchars($cat['category_name']) ?>"
                                                title="Archive Category">
                                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr class="empty-row">
                                <td colspan="3">
                                    <?= $search !== '' ? 'No categories found matching your search.' : 'No categories found.' ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1):
                $searchParam = $search !== '' ? '&search=' . urlencode($search) : '';
            ?>
                <div class="pagination">
                    <div class="pagination-info">
                        Page <strong style="color:var(--text)"><?= $page ?></strong> of <strong style="color:var(--text)"><?= $totalPages ?></strong>
                    </div>
                    <nav class="pagination-nav">

                        <?php if ($page > 1): ?>
                            <a href="?page=<?= $page - 1 ?><?= $searchParam ?>" class="page-btn">‹ Prev</a>
                        <?php else: ?>
                            <span class="page-btn disabled">‹ Prev</span>
                        <?php endif; ?>

                        <?php
                        $range = 2;
                        $start = max(1, $page - $range);
                        $end   = min($totalPages, $page + $range);
                        ?>

                        <?php if ($start > 1): ?>
                            <a href="?page=1<?= $searchParam ?>" class="page-btn">1</a>
                            <?php if ($start > 2): ?><span class="page-ellipsis">…</span><?php endif; ?>
                        <?php endif; ?>

                        <?php for ($i = $start; $i <= $end; $i++): ?>
                            <?php if ($i === $page): ?>
                                <span class="page-btn active"><?= $i ?></span>
                            <?php else: ?>
                                <a href="?page=<?= $i ?><?= $searchParam ?>" class="page-btn"><?= $i ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <?php if ($end < $totalPages): ?>
                            <?php if ($end < $totalPages - 1): ?><span class="page-ellipsis">…</span><?php endif; ?>
                            <a href="?page=<?= $totalPages ?><?= $searchParam ?>" class="page-btn"><?= $totalPages ?></a>
                        <?php endif; ?>

                        <?php if ($page < $totalPages): ?>
                            <a href="?page=<?= $page + 1 ?><?= $searchParam ?>" class="page-btn">Next ›</a>
                        <?php else: ?>
                            <span class="page-btn disabled">Next ›</span>
                        <?php endif; ?>

                    </nav>
                </div>
            <?php endif; ?>

        </div>
    </main>

    <!-- Add Category Modal -->
    <div id="addCategoryModal" class="modal-overlay">
        <div class="modal-box">
            <h3>Add New Category</h3>
            <form method="POST">
                <div class="form-group">
                    <label>Category Name</label>
                    <input type="text" name="category_name" class="form-input" required placeholder="e.g. Graphics Cards">
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="category_description" class="form-textarea" rows="3" placeholder="Optional"></textarea>
                </div>
                <div class="form-group">
                    <label>Category Type</label>
                    <select name="category_type" class="form-select" required>
                        <option value="">Select type</option>
                        <option value="pc_part">PC Part</option>
                        <option value="accessory">Accessory</option>
                    </select>
                </div>
                <div class="form-checkbox-row">
                    <input type="checkbox" name="supports_quantity" id="supports_quantity">
                    <label for="supports_quantity">Supports quantity (RAM, Storage, Accessories)</label>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-cancel" id="cancelCategoryModalBtn">Cancel</button>
                    <button type="submit" name="create_category" class="btn-confirm-ok">Save Category</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Category Modal -->
    <div id="editCategoryModal" class="modal-overlay">
        <div class="modal-box">
            <h3>Update Category</h3>
            <form method="POST">
                <input type="hidden" name="category_id" id="edit_category_id">
                <div class="form-group">
                    <label>Category Name</label>
                    <input type="text" name="category_name" id="edit_category_name" class="form-input" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="category_description" id="edit_category_description" class="form-textarea" rows="3" placeholder="Optional"></textarea>
                </div>
                <div class="form-group">
                    <label>Category Type</label>
                    <select name="category_type" id="edit_category_type" class="form-select" required>
                        <option value="">Select type</option>
                        <option value="pc_part">PC Part</option>
                        <option value="accessory">Accessory</option>
                    </select>
                </div>
                <div class="form-checkbox-row">
                    <input type="checkbox" name="supports_quantity" id="edit_supports_quantity">
                    <label for="edit_supports_quantity">Supports quantity (RAM, Storage, Accessories)</label>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-cancel" id="cancelEditCategoryModalBtn">Cancel</button>
                    <button type="submit" name="update_category" class="btn-confirm-ok">Update Category</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Archive Category Modal -->
    <div id="archiveCategoryModal" class="modal-overlay">
        <div class="modal-box">
            <h3>Archive Category</h3>
            <p>Are you sure you want to archive <strong id="archive_category_name"></strong>? This action can be undone later.</p>
            <form method="POST">
                <input type="hidden" name="category_id" id="archive_category_id">
                <div class="modal-actions">
                    <button type="button" class="btn-cancel" id="cancelArchiveCategory">Cancel</button>
                    <button type="submit" name="archive_category" class="btn-confirm-warning">Archive</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Sidebar
        const sidebar = document.getElementById('mobile-sidebar');
        const toggleBtn = document.getElementById('sidebar-toggle');
        const closeBtn = document.getElementById('sidebar-close');
        if (toggleBtn) toggleBtn.addEventListener('click', () => sidebar?.classList.toggle('-translate-x-full'));
        if (closeBtn) closeBtn.addEventListener('click', () => sidebar?.classList.add('-translate-x-full'));

        // Sync sidebar collapse margin
        const mainEl = document.querySelector('main');
        const collapseBtn = document.getElementById('sidebarCollapseBtn');
        const mainSidebar = document.getElementById('main-sidebar');

        function syncMargin() {
            const collapsed = mainSidebar && mainSidebar.classList.contains('collapsed');
            if (mainEl) mainEl.style.marginLeft = collapsed ? '64px' : '240px';
        }
        if (collapseBtn) collapseBtn.addEventListener('click', () => setTimeout(syncMargin, 50));
        syncMargin();

        // Modal helpers
        function openModal(id) {
            document.getElementById(id).classList.add('active');
        }

        function closeModal(id) {
            document.getElementById(id).classList.remove('active');
        }

        // Close on backdrop click
        document.querySelectorAll('.modal-overlay').forEach(overlay => {
            overlay.addEventListener('click', e => {
                if (e.target === overlay) overlay.classList.remove('active');
            });
        });

        // Add Category Modal
        document.getElementById('openAddCategoryModal').addEventListener('click', () => openModal('addCategoryModal'));
        document.getElementById('cancelCategoryModalBtn').addEventListener('click', () => closeModal('addCategoryModal'));

        // Edit Category Modal
        document.querySelectorAll('.openEditCategoryModal').forEach(btn => {
            btn.addEventListener('click', () => {
                document.getElementById('edit_category_id').value = btn.dataset.id;
                document.getElementById('edit_category_name').value = btn.dataset.name;
                document.getElementById('edit_category_description').value = btn.dataset.description || '';
                document.getElementById('edit_category_type').value = btn.dataset.categoryType || '';
                document.getElementById('edit_supports_quantity').checked =
                    btn.dataset.supportsQuantity === '1' || btn.dataset.supportsQuantity === 'true';
                openModal('editCategoryModal');
            });
        });
        document.getElementById('cancelEditCategoryModalBtn').addEventListener('click', () => closeModal('editCategoryModal'));

        // Archive Category Modal
        document.querySelectorAll('.openArchiveCategoryModal').forEach(btn => {
            btn.addEventListener('click', () => {
                document.getElementById('archive_category_id').value = btn.dataset.id;
                document.getElementById('archive_category_name').textContent = btn.dataset.name;
                openModal('archiveCategoryModal');
            });
        });
        document.getElementById('cancelArchiveCategory').addEventListener('click', () => closeModal('archiveCategoryModal'));

        // Search debounce
        let searchTimeout;
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => this.form.submit(), 500);
            });
        }

        // Auto-hide alerts
        ['successAlert', 'errorAlert'].forEach(id => {
            const el = document.getElementById(id);
            if (el) setTimeout(() => el.style.display = 'none', 3000);
        });
    </script>

</body>

</html>