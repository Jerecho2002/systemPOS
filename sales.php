<?php
include "database/database.php";
$database->login_session();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['archive_sale'])) {
    header('Content-Type: application/json');
    $saleId = (int) $_POST['sale_id'];
    try {
        $success = $database->archiveSale($saleId);
        echo json_encode(
            $success
                ? ['success' => true,  'message' => 'Transaction archived successfully']
                : ['success' => false, 'message' => 'Failed to archive transaction']
        );
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

$search   = trim($_GET['search'] ?? '');
$page     = max(1, (int) ($_GET['page'] ?? 1));
$perPage  = 5;
$offset   = ($page - 1) * $perPage;

$totalSales = $database->getTotalSalesCount($search);
$totalPages = max(1, ceil($totalSales / $perPage));
$sales      = $database->select_sales_paginated($offset, $perPage, $search);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS & Inventory - Recent Transactions</title>
    <script src="assets/print.min.js"></script>
    <link rel="stylesheet" href="assets/tailwind.min.css">
    <link rel="stylesheet" href="assets/print.min.css">
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

        /* ── Search result info ── */
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
            white-space: nowrap;
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

        /* ── Payment badge ── */
        .pay-badge {
            display: inline-flex;
            align-items: center;
            font-size: 11px;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 20px;
        }

        .pay-cash {
            background: rgba(67, 211, 146, .1);
            color: var(--success);
        }

        .pay-gcash {
            background: rgba(78, 205, 196, .1);
            color: #4ecdc4;
        }

        .pay-card {
            background: rgba(99, 102, 241, .12);
            color: #818cf8;
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
        }

        .btn-action.archive {
            color: var(--text-muted);
        }

        .btn-action.archive:hover {
            background: rgba(255, 92, 92, .1);
            color: var(--danger);
        }

        .btn-action.receipt {
            color: var(--text-muted);
        }

        .btn-action.receipt:hover {
            background: rgba(67, 211, 146, .1);
            color: var(--success);
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

        /* ── Modal ── */
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
            width: 440px;
            max-width: 92%;
            box-shadow: 0 25px 60px rgba(0, 0, 0, .4);
        }

        .modal-box h3 {
            font-size: 17px;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 8px;
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

        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
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

        .btn-confirm-danger {
            background: var(--danger);
            border: none;
            color: #fff;
            border-radius: 10px;
            padding: 9px 20px;
            font-family: 'DM Sans', sans-serif;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: opacity .2s;
        }

        .btn-confirm-danger:hover {
            opacity: .85;
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

        <!-- Header -->
        <div class="page-header">
            <h2>Transaction History</h2>
            <p>View and manage all sales transactions</p>
        </div>

        <div class="card">

            <!-- Search -->
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; flex-wrap:wrap; gap:12px;">
                <form method="GET" action="">
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
                            placeholder="Search by customer, ID, payment…">
                        <?php if ($search !== ''): ?>
                            <a href="?" class="search-clear" title="Clear">✕</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <?php if ($search !== ''): ?>
                <div class="search-info">
                    <span>Showing results for "<strong style="color:var(--text)"><?= htmlspecialchars($search) ?></strong>" — <?= $totalSales ?> result<?= $totalSales !== 1 ? 's' : '' ?></span>
                    <a href="?">Clear search</a>
                </div>
            <?php endif; ?>

            <!-- Table -->
            <div style="overflow-x:auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Transaction ID</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Payment</th>
                            <th>Time</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($sales)): ?>
                            <?php foreach ($sales as $sale): ?>
                                <tr>
                                    <td style="font-weight:600;"><?= htmlspecialchars($sale['transaction_id']) ?></td>
                                    <td class="muted"><?= htmlspecialchars($sale['customer_name'] ?: '—') ?></td>
                                    <td style="font-weight:600; color:var(--accent);">₱<?= number_format($sale['grand_total'], 2) ?></td>
                                    <td>
                                        <?php
                                        $method = $sale['payment_method'] ?? '—';
                                        $cls = match ($method) {
                                            'Cash'        => 'pay-cash',
                                            'Gcash'       => 'pay-gcash',
                                            'Credit Card' => 'pay-card',
                                            default       => ''
                                        };
                                        ?>
                                        <span class="pay-badge <?= $cls ?>"><?= htmlspecialchars($method) ?></span>
                                    </td>
                                    <td class="muted"><?= date('g:i A', strtotime($sale['time'])) ?></td>
                                    <td class="muted"><?= date('M j, Y', strtotime($sale['date'])) ?></td>
                                    <td>
                                        <div style="display:flex; align-items:center; gap:4px;">
                                            <?php if ($isAdmin): ?>
                                                <button class="btn-action archive openArchiveSaleModal"
                                                    data-id="<?= $sale['sale_id'] ?>"
                                                    data-name="<?= htmlspecialchars($sale['transaction_id']) ?>"
                                                    title="Archive Transaction">
                                                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                                                    </svg>
                                                </button>
                                            <?php endif; ?>
                                            <button class="btn-action receipt preview-sale-btn"
                                                data-id="<?= $sale['sale_id'] ?>"
                                                title="Generate Receipt">
                                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr class="empty-row">
                                <td colspan="7">
                                    <?= $search !== '' ? 'No transactions found matching your search.' : 'No transactions found.' ?>
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

    <!-- Archive Modal -->
    <div id="archiveSaleModal" class="modal-overlay">
        <div class="modal-box">
            <h3>Archive Transaction</h3>
            <p>Are you sure you want to archive transaction <strong id="saleNameDisplay"></strong>? This action can be undone later.</p>
            <div class="modal-actions">
                <button id="cancelArchiveSale" class="btn-cancel">Cancel</button>
                <button id="confirmArchiveSale" class="btn-confirm-danger">Archive</button>
            </div>
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

        // Search debounce
        let searchTimeout;
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => this.form.submit(), 500);
            });
        }

        // Archive modal
        const archiveModal = document.getElementById('archiveSaleModal');
        const saleNameDisplay = document.getElementById('saleNameDisplay');
        const confirmBtn = document.getElementById('confirmArchiveSale');
        const cancelBtn = document.getElementById('cancelArchiveSale');
        let currentSaleId = null;

        function openModal(id) {
            document.getElementById(id).classList.add('active');
        }

        function closeModal(id) {
            document.getElementById(id).classList.remove('active');
        }

        document.querySelectorAll('.openArchiveSaleModal').forEach(btn => {
            btn.addEventListener('click', function() {
                currentSaleId = this.dataset.id;
                saleNameDisplay.textContent = this.dataset.name;
                openModal('archiveSaleModal');
            });
        });

        cancelBtn.addEventListener('click', () => {
            closeModal('archiveSaleModal');
            currentSaleId = null;
        });

        archiveModal.addEventListener('click', e => {
            if (e.target === archiveModal) {
                closeModal('archiveSaleModal');
                currentSaleId = null;
            }
        });

        confirmBtn.addEventListener('click', () => {
            if (!currentSaleId) return;
            fetch(window.location.href, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'archive_sale=1&sale_id=' + currentSaleId
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert('Error: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(() => alert('An error occurred while archiving the transaction.'));
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {

            const previewButtons = document.querySelectorAll('.preview-sale-btn');

            // Detect if running inside Electron
            const isElectron = typeof window !== 'undefined' &&
                window.require &&
                window.process &&
                window.process.versions && window.process.versions.electron;

            if (isElectron) {
                // Electron: use ipcRenderer
                const {
                    ipcRenderer
                } = require('electron');
                previewButtons.forEach(button => {
                    button.addEventListener('click', function(e) {
                        e.preventDefault();
                        const saleId = this.getAttribute('data-id');
                        if (!saleId) {
                            alert('Invalid sale ID');
                            return;
                        }
                        ipcRenderer.send('print-receipt', saleId);
                    });
                });
            } else {
                // Browser / web: detect mobile and handle via Print.js or PDF
                const isMobile = /Android|iPhone|iPad|iPod|Opera Mini|IEMobile|WPDesktop/i.test(navigator.userAgent);

                previewButtons.forEach(button => {
                    button.addEventListener('click', function(e) {
                        e.preventDefault();
                        const saleId = this.getAttribute('data-id');
                        if (!saleId) {
                            alert('Invalid sale ID');
                            return;
                        }

                        if (isMobile) {
                            // Mobile: open PDF in new tab
                            window.open(`print_sales_receipt.php?id=${saleId}`, '_blank');
                            return;
                        }

                        // Desktop browser: use Print.js
                        fetch(`print_sales_receipt.php?id=${saleId}`)
                            .then(response => {
                                if (!response.ok) throw new Error('Failed to fetch receipt');
                                return response.blob();
                            })
                            .then(blob => {
                                const reader = new FileReader();
                                reader.onloadend = function() {
                                    const base64 = reader.result.split(',')[1];
                                    printJS({
                                        printable: base64,
                                        type: 'pdf',
                                        base64: true
                                    });
                                };
                                reader.readAsDataURL(blob);
                            })
                            .catch(err => {
                                console.error(err);
                                alert('Could not load receipt. Please try again.');
                            });
                    });
                });
            }
        });
    </script>

</body>

</html>