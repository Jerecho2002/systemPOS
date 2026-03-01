<?php
include "database/archiveDatabase.php";
$database->login_session();
$database->admin_session();

// Handle Restore
if (isset($_POST['restore_record'])) {
    $restoreId = (int) $_POST['restore_id'];
    $restoreType = $_POST['restore_type'] ?? '';

    if ($database->restoreArchived($restoreType, $restoreId)) {
        $_SESSION['restore-success'] = 'Record successfully restored.';
    } else {
        $_SESSION['restore-error'] = 'Failed to restore record.';
    }

    header('Location: archives.php?type=' . urlencode($restoreType));
    exit;
}

// Handle Permanent Delete
if (isset($_POST['permanent_delete'])) {
    $deleteId = (int) $_POST['delete_id'];
    $deleteType = $_POST['delete_type'] ?? '';

    if ($database->permanentDelete($deleteType, $deleteId)) {
        $_SESSION['delete-success'] = 'Record permanently deleted.';
    } else {
        $_SESSION['delete-error'] = 'Failed to delete record.';
    }

    header('Location: archives.php?type=' . urlencode($deleteType));
    exit;
}

// Handle Clear All
if (isset($_POST['clear_all_archives'])) {
    $clearType = $_POST['clear_type'] ?? '';

    if ($database->clearAllArchives($clearType)) {
        $_SESSION['clear-success'] = 'All archived records cleared permanently.';
    } else {
        $_SESSION['clear-error'] = 'Failed to clear archives.';
    }

    header('Location: archives.php?type=' . urlencode($clearType));
    exit;
}

$type = $_GET['type'] ?? 'categories';
$search = trim($_GET['search'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));

$perPage = 10;
$offset = ($page - 1) * $perPage;

$total = $database->getArchivedTotal($type, $search);
$pages = max(1, ceil($total / $perPage));

$records = $database->getArchivedPaginated($type, $offset, $perPage, $search);
$tableConfig = $database->getTableConfig($type);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Archives - <?= htmlspecialchars($tableConfig['label'] ?? 'Records') ?></title>
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

        .page-header {
            margin-bottom: 24px;
        }

        .page-header h2 {
            font-size: 22px;
            font-weight: 700;
        }

        .card {
            background: var(--surface);
            border: 1.5px solid var(--border);
            border-radius: 16px;
            padding: 24px;
        }

        .toolbar {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 24px;
        }

        .search-wrap {
            position: relative;
            flex: 1 1 280px;
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

        .btn-danger {
            background: var(--danger);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 9px 18px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: opacity .2s;
            white-space: nowrap;
        }

        .btn-danger:hover {
            opacity: .88;
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

        .alert-info {
            background: rgba(96, 165, 250, .1);
            border: 1px solid rgba(96, 165, 250, .3);
            color: var(--info);
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

        .btn-action:hover.restore {
            color: var(--success);
            background: rgba(67, 211, 146, .12);
        }

        .btn-action:hover.delete {
            color: var(--danger);
            background: rgba(255, 92, 92, .12);
        }

        .actions-wrap {
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: flex-end;
            gap: 8px;
            flex-wrap: nowrap;
            min-width: 120px;
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
            width: 460px;
            max-width: 92%;
            box-shadow: 0 25px 60px rgba(0, 0, 0, .4);
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

        .btn-confirm-danger {
            background: var(--danger);
            color: white;
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
    <?php include 'sidebar.php'; ?>

    <main style="margin-left:240px;">

        <div class="page-header">
            <h2>Restore or permanently delete archived records</h2>
        </div>

        <div class="card">

            <!-- Toolbar: Type + Search + Clear All -->
            <div class="toolbar">
                <form method="GET" id="archiveFilterForm" style="display:flex; align-items:center; gap:12px; flex-wrap:wrap; flex:1;">
                    <select name="type" onchange="this.form.submit()" class="filter-select">
                        <option value="categories" <?= $type === 'categories' ? 'selected' : '' ?>>Categories</option>
                        <option value="items" <?= $type === 'items' ? 'selected' : '' ?>>Items</option>
                        <option value="suppliers" <?= $type === 'suppliers' ? 'selected' : '' ?>>Suppliers</option>
                        <option value="purchase_orders" <?= $type === 'purchase_orders' ? 'selected' : '' ?>>Purchase Orders</option>
                        <option value="sales" <?= $type === 'sales' ? 'selected' : '' ?>>Sales</option>
                        <option value="pc_builders" <?= $type === 'pc_builders'     ? 'selected' : '' ?>>Quotations</option>
                    </select>
                    </select>

                    <div class="search-wrap">
                        <svg class="search-icon" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <circle cx="11" cy="11" r="8" />
                            <path d="m21 21-4.35-4.35" />
                        </svg>
                        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search archived records..." class="form-input">
                        <?php if ($search !== ''): ?>
                            <a href="?type=<?= urlencode($type) ?>" class="search-clear">×</a>
                        <?php endif; ?>
                    </div>
                </form>

                <?php if ($total > 0): ?>
                    <button onclick="openClearAllModal()" class="btn-danger">
                        Delete All
                    </button>
                <?php endif; ?>
            </div>

            <!-- Info -->
            <div style="font-size:13px; color:var(--text-muted); margin-bottom:16px;">
                Showing <strong style="color:var(--text)"><?= htmlspecialchars($tableConfig['label'] ?? 'Records') ?></strong> archives
                <span style="color:var(--border);">·</span>
                <strong style="color:var(--text)"><?= $total ?></strong> record<?= $total !== 1 ? 's' : '' ?>
            </div>

            <!-- Alerts -->
            <?php if (isset($_SESSION['restore-success'])): ?>
                <div class="alert alert-success"><?= $_SESSION['restore-success'] ?></div>
                <?php unset($_SESSION['restore-success']); ?>
            <?php endif; ?>
            <?php if (isset($_SESSION['delete-success'])): ?>
                <div class="alert alert-info"><?= $_SESSION['delete-success'] ?></div>
                <?php unset($_SESSION['delete-success']); ?>
            <?php endif; ?>
            <?php if (isset($_SESSION['clear-success'])): ?>
                <div class="alert alert-info"><?= $_SESSION['clear-success'] ?></div>
                <?php unset($_SESSION['clear-success']); ?>
            <?php endif; ?>
            <?php if (isset($_SESSION['restore-error']) || isset($_SESSION['delete-error']) || isset($_SESSION['clear-error'])): ?>
                <div class="alert alert-error">
                    <?= $_SESSION['restore-error'] ?? $_SESSION['delete-error'] ?? $_SESSION['clear-error'] ?>
                </div>
                <?php unset($_SESSION['restore-error'], $_SESSION['delete-error'], $_SESSION['clear-error']); ?>
            <?php endif; ?>

            <!-- Table -->
            <div style="overflow-x:auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <?php foreach ($tableConfig['display_columns'] as $column => $label): ?>
                                <th><?= htmlspecialchars($label) ?></th>
                            <?php endforeach; ?>
                            <th style="text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($records)): ?>
                            <?php foreach ($records as $row): ?>
                                <tr>
                                    <?php foreach ($tableConfig['display_columns'] as $column => $label): ?>
                                        <td>
                                            <?php
                                            $value = $row[$column] ?? '—';

                                            // Price formatting
                                            if (stripos($column, 'price') !== false || stripos($column, 'total') !== false) {
                                                $value = $value !== '—' ? '₱' . number_format((float)$value, 2) : '—';
                                            }
                                            // Status badges
                                            elseif ($column === 'status') {
                                                if (is_numeric($value)) {
                                                    $class = $value == 1 ? 'status-active' : 'status-inactive';
                                                    $text  = $value == 1 ? 'Active' : 'Inactive';
                                                    $value = "<span class='status-pill $class'>$text</span>";
                                                } else {
                                                    $classes = [
                                                        'Ordered'   => 'bg-blue-900/30 text-blue-300',
                                                        'Received'  => 'bg-green-900/30 text-green-300',
                                                        'Cancelled' => 'bg-red-900/30 text-red-300'
                                                    ];
                                                    $class = $classes[$value] ?? 'bg-gray-800/50 text-gray-300';
                                                    $value = "<span class='status-pill $class'>" . ucfirst($value) . "</span>";
                                                }
                                            }
                                            // Category type
                                            elseif ($column === 'category_type') {
                                                $classes = [
                                                    'pc_part'   => 'bg-blue-900/30 text-blue-300',
                                                    'accessory' => 'bg-purple-900/30 text-purple-300'
                                                ];
                                                $class = $classes[$value] ?? 'bg-gray-800/50 text-gray-300';
                                                $display = ucwords(str_replace('_', ' ', $value));
                                                $value = "<span class='status-pill $class'>$display</span>";
                                            }
                                            // Boolean yes/no
                                            elseif ($column === 'supports_quantity') {
                                                $value = $value == 1
                                                    ? '<span style="color:var(--success);">Yes</span>'
                                                    : '<span style="color:var(--text-muted);">No</span>';
                                            }

                                            echo $value;
                                            ?>
                                        </td>
                                    <?php endforeach; ?>

                                    <td>
                                        <div class="actions-wrap">
                                            <button class="btn-action restore openRestoreModal"
                                                data-id="<?= $row[array_key_first($row)] ?>"
                                                data-type="<?= htmlspecialchars($type) ?>"
                                                title="Restore">
                                                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                                </svg>
                                            </button>

                                            <button class="btn-action delete openDeleteModal"
                                                data-id="<?= $row[array_key_first($row)] ?>"
                                                data-type="<?= htmlspecialchars($type) ?>"
                                                title="Delete Permanently">
                                                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5-4h4M9 7v12m6-12v12M10 11v6m4-6v6" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="<?= count($tableConfig['display_columns']) + 1 ?>" style="text-align:center; padding:80px 20px; color:var(--text-muted); font-size:15px;">
                                    No archived records found for this category.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($pages > 1): ?>
                <div class="pagination">
                    <div style="font-size:12px; color:var(--text-muted);">
                        Page <strong style="color:var(--text)"><?= $page ?></strong> of <strong style="color:var(--text)"><?= $pages ?></strong>
                    </div>
                    <div style="display:flex; gap:4px; flex-wrap:wrap;">
                        <?php if ($page > 1): ?>
                            <a href="?type=<?= urlencode($type) ?>&search=<?= urlencode($search) ?>&page=<?= $page - 1 ?>" class="page-btn">‹ Prev</a>
                        <?php else: ?>
                            <span class="page-btn disabled">‹ Prev</span>
                        <?php endif; ?>

                        <?php
                        $range = 2;
                        $start = max(1, $page - $range);
                        $end   = min($pages, $page + $range);
                        if ($start > 1): ?>
                            <a href="?type=<?= urlencode($type) ?>&search=<?= urlencode($search) ?>&page=1" class="page-btn">1</a>
                            <?php if ($start > 2): ?><span style="padding:6px 8px; color:var(--text-muted);">…</span><?php endif; ?>
                        <?php endif; ?>

                        <?php for ($i = $start; $i <= $end; $i++): ?>
                            <?php if ($i === $page): ?>
                                <span class="page-btn active"><?= $i ?></span>
                            <?php else: ?>
                                <a href="?type=<?= urlencode($type) ?>&search=<?= urlencode($search) ?>&page=<?= $i ?>" class="page-btn"><?= $i ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <?php if ($end < $pages): ?>
                            <?php if ($end < $pages - 1): ?><span style="padding:6px 8px; color:var(--text-muted);">…</span><?php endif; ?>
                            <a href="?type=<?= urlencode($type) ?>&search=<?= urlencode($search) ?>&page=<?= $pages ?>" class="page-btn"><?= $pages ?></a>
                        <?php endif; ?>

                        <?php if ($page < $pages): ?>
                            <a href="?type=<?= urlencode($type) ?>&search=<?= urlencode($search) ?>&page=<?= $page + 1 ?>" class="page-btn">Next ›</a>
                        <?php else: ?>
                            <span class="page-btn disabled">Next ›</span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </main>

    <!-- Restore Modal -->
    <div id="restoreModal" class="modal-overlay">
        <div class="modal-box">
            <h3>Restore Record</h3>
            <p style="margin:20px 0;">
                Restore this record back to active list?<br>
                It will appear in the main <?= htmlspecialchars($tableConfig['label'] ?? 'section') ?> again.
            </p>
            <form method="POST">
                <input type="hidden" name="restore_id" id="restore_id">
                <input type="hidden" name="restore_type" id="restore_type">
                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="closeModal('restoreModal')">Cancel</button>
                    <button type="submit" name="restore_record" class="btn-confirm-ok">Restore</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Permanent Delete Modal -->
    <div id="deleteModal" class="modal-overlay">
        <div class="modal-box">
            <h3>Permanent Delete</h3>
            <p style="margin:20px 0; color:var(--danger); font-weight:600;">
                This will permanently delete the record.<br>
                This action cannot be undone.
            </p>
            <form method="POST">
                <input type="hidden" name="delete_id" id="delete_id">
                <input type="hidden" name="delete_type" id="delete_type">
                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="closeModal('deleteModal')">Cancel</button>
                    <button type="submit" name="permanent_delete" class="btn-confirm-danger">Delete Permanently</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Clear All Modal -->
    <div id="clearAllModal" class="modal-overlay">
        <div class="modal-box">
            <h3>Clear All Archives</h3>
            <p style="margin:20px 0;">
                Permanently delete <strong style="color:var(--danger);"><?= $total ?></strong> archived
                <?= htmlspecialchars(strtolower($tableConfig['label'] ?? 'records')) ?>?<br>
                This cannot be undone.
            </p>
            <form method="POST">
                <input type="hidden" name="clear_type" value="<?= htmlspecialchars($type) ?>">
                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="closeModal('clearAllModal')">Cancel</button>
                    <button type="submit" name="clear_all_archives" class="btn-confirm-danger">Clear All</button>
                </div>
            </form>
        </div>
    </div>

    <script>
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

        // Restore buttons
        document.querySelectorAll('.openRestoreModal').forEach(btn => {
            btn.addEventListener('click', () => {
                document.getElementById('restore_id').value = btn.dataset.id;
                document.getElementById('restore_type').value = '<?= htmlspecialchars($type) ?>';
                openModal('restoreModal');
            });
        });

        // Delete buttons
        document.querySelectorAll('.openDeleteModal').forEach(btn => {
            btn.addEventListener('click', () => {
                document.getElementById('delete_id').value = btn.dataset.id;
                document.getElementById('delete_type').value = '<?= htmlspecialchars($type) ?>';
                openModal('deleteModal');
            });
        });

        // Clear all
        function openClearAllModal() {
            openModal('clearAllModal');
        }
    </script>

</body>

</html>