<?php
include "database/database.php";
$database->login_session();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['archive_sale'])) {
    header('Content-Type: application/json');

    $saleId = (int) $_POST['sale_id'];

    try {
        $success = $database->archiveSale($saleId);

        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Transaction archived successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to archive transaction']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

$search  = trim($_GET['search'] ?? '');
$page    = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 5;
$offset = ($page - 1) * $perPage;

$totalSales = $database->getTotalSalesCount($search);
$totalPages = max(1, ceil($totalSales / $perPage));

$sales = $database->select_sales_paginated($offset, $perPage, $search);
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
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>

<body class="bg-gray-50 flex">

    <!-- Mobile Sidebar Toggle -->
    <button id="sidebar-toggle" class="md:hidden p-3 fixed top-4 left-4 z-60 text-white rounded shadow-lg"
        style="background-color: rgba(170, 170, 170, 0.82);">
        ☰
    </button>

    <?php include "sidebar.php"; ?>

    <!-- Main Content -->
    <main class="flex-1 ml-0 md:ml-64 p-6">
        <header class="mb-6">
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold text-gray-800">Recent Transactions</h2>
            </div>
        </header>

        <section class="bg-white rounded-xl shadow-md p-6">

            <!-- Search Bar + Title -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
                <h3 class="text-xl font-bold text-gray-800">Latest Transactions</h3>

                <form method="GET" action="" class="relative w-full sm:w-72">
                    <input
                        type="text"
                        name="search"
                        id="searchInput"
                        value="<?= htmlspecialchars($search) ?>"
                        placeholder="Search by customer, ID, amount, payment..."
                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                    <span class="material-icons absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">search</span>

                    <?php if ($search !== ''): ?>
                        <a href="?" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <span class="material-icons text-sm">close</span>
                        </a>
                    <?php endif; ?>
                </form>
            </div>

            <?php if ($search !== ''): ?>
                <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-800">
                    Showing results for "<strong><?= htmlspecialchars($search) ?></strong>"
                    (<?= $totalSales ?> result<?= $totalSales !== 1 ? 's' : '' ?>)
                    <a href="?" class="ml-2 text-blue-600 hover:underline">Clear search</a>
                </div>
            <?php endif; ?>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table id="transactionsTable" class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">Customer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">Payment</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (!empty($sales)): ?>
                            <?php foreach ($sales as $sale): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= $sale['transaction_id'] ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 hidden md:table-cell"><?= htmlspecialchars($sale['customer_name'] ?: '—') ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 hidden md:table-cell">₱<?= number_format($sale['grand_total'], 2) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 hidden md:table-cell"><?= htmlspecialchars($sale['payment_method'] ?: '—') ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 hidden md:table-cell"><?= date('g:i A', strtotime($sale['time'])) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 hidden md:table-cell"><?= date('M j, Y', strtotime($sale['date'])) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm flex items-center gap-2">
                                        <?php if ($isAdmin): ?>
                                            <button class="text-red-500 hover:text-red-700 openArchiveSaleModal"
                                                data-id="<?= $sale['sale_id'] ?>"
                                                data-name="<?= $sale['transaction_id'] ?>"
                                                title="Archive Transaction">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd"
                                                        d="M4 3a1 1 0 011-1h10a1 1 0 011 1v3a1 1 0 01-1 1H5a1 1 0 01-1-1V3zm3 4h10a1 1 0 011 1v9a1 1 0 01-1 1H5a1 1 0 01-1-1V8a1 1 0 011-1h10V5H7v3z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        <?php endif; ?>
                                        <button class="preview-sale-btn inline-flex items-center justify-center p-1 hover:opacity-80 transition-opacity text-green-600"
                                            data-id="<?= $sale['sale_id'] ?>"
                                            title="Generate Receipt">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                class="w-5 h-5 fill-current"
                                                viewBox="0 0 24 24">
                                                <path d="M6 9V3h12v6h1a3 3 0 013 3v5a3 3 0 01-3 3h-1v4H6v-4H5a3 3 0 01-3-3v-5a3 3 0 013-3h1zm2-4v4h8V5H8zm8 13H8v3h8v-3z" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="px-6 py-10 text-center text-gray-500">
                                    <?= $search !== '' ? 'No transactions found matching your search.' : 'No transactions found.' ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Controls -->
            <?php if ($totalPages > 1): ?>
                <div class="mt-6 flex flex-col sm:flex-row items-center justify-between gap-4 border-t pt-4">
                    <div class="text-sm text-gray-700">
                        Showing page <span class="font-medium"><?= $page ?></span> of <span class="font-medium"><?= $totalPages ?></span>
                    </div>

                    <nav class="flex items-center space-x-1">
                        <?php
                        // Build search parameter for all pagination links
                        $searchParam = $search !== '' ? '&search=' . urlencode($search) : '';
                        ?>

                        <!-- Previous -->
                        <?php if ($page > 1): ?>
                            <a href="?page=<?= $page - 1 ?><?= $searchParam ?>"
                                class="px-3 py-2 rounded-md text-sm font-medium bg-white border border-gray-300 text-gray-700 hover:bg-gray-50">
                                Previous
                            </a>
                        <?php else: ?>
                            <span class="px-3 py-2 rounded-md text-sm font-medium bg-gray-100 text-gray-400 cursor-not-allowed">
                                Previous
                            </span>
                        <?php endif; ?>

                        <?php
                        $range = 2;
                        $start = max(1, $page - $range);
                        $end = min($totalPages, $page + $range);

                        // Show first page and ellipsis if needed
                        if ($start > 1): ?>
                            <a href="?page=1<?= $searchParam ?>"
                                class="px-3 py-2 rounded-md text-sm font-medium border border-gray-300 hover:bg-gray-50">
                                1
                            </a>
                            <?php if ($start > 2): ?>
                                <span class="px-3 py-2 text-sm text-gray-500">...</span>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php
                        // Show page numbers in range
                        for ($i = $start; $i <= $end; $i++):
                            if ($i === $page): ?>
                                <span class="px-3 py-2 rounded-md text-sm font-medium bg-blue-600 text-white">
                                    <?= $i ?>
                                </span>
                            <?php else: ?>
                                <a href="?page=<?= $i ?><?= $searchParam ?>"
                                    class="px-3 py-2 rounded-md text-sm font-medium border border-gray-300 hover:bg-gray-50">
                                    <?= $i ?>
                                </a>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <?php
                        // Show ellipsis and last page if needed
                        if ($end < $totalPages): ?>
                            <?php if ($end < $totalPages - 1): ?>
                                <span class="px-3 py-2 text-sm text-gray-500">...</span>
                            <?php endif; ?>
                            <a href="?page=<?= $totalPages ?><?= $searchParam ?>"
                                class="px-3 py-2 rounded-md text-sm font-medium border border-gray-300 hover:bg-gray-50">
                                <?= $totalPages ?>
                            </a>
                        <?php endif; ?>

                        <!-- Next -->
                        <?php if ($page < $totalPages): ?>
                            <a href="?page=<?= $page + 1 ?><?= $searchParam ?>"
                                class="px-3 py-2 rounded-md text-sm font-medium bg-white border border-gray-300 text-gray-700 hover:bg-gray-50">
                                Next
                            </a>
                        <?php else: ?>
                            <span class="px-3 py-2 rounded-md text-sm font-medium bg-gray-100 text-gray-400 cursor-not-allowed">
                                Next
                            </span>
                        <?php endif; ?>
                    </nav>
                </div>
            <?php endif; ?>

        </section>
    </main>

    <!-- Archive Confirmation Modal -->
    <div id="archiveSaleModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
            <div class="flex items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Archive Sales</h3>
            </div>
            <p class="text-gray-600 mb-6">
                Are you sure you want to archive transaction <strong id="saleNameDisplay"></strong>? This action can be undone later.
            </p>
            <div class="flex justify-end space-x-3">
                <button id="cancelArchiveSale" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                    Cancel
                </button>
                <button id="confirmArchiveSale" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600">
                    Archive
                </button>
            </div>
        </div>
    </div>

    <!-- Sidebar Toggle Script -->
    <script>
        const sidebar = document.getElementById('mobile-sidebar');
        const toggleBtn = document.getElementById('sidebar-toggle');
        const closeBtn = document.getElementById('sidebar-close');

        if (toggleBtn) {
            toggleBtn.addEventListener('click', () => {
                sidebar?.classList.toggle('-translate-x-full');
            });
        }
        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                sidebar?.classList.add('-translate-x-full');
            });
        }

        // Auto-submit search form after user stops typing (optional - for better UX)
        let searchTimeout;
        const searchInput = document.getElementById('searchInput');

        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    this.form.submit();
                }, 500); // Wait 500ms after user stops typing
            });
        }

        // Archive Modal Logic
        const archiveModal = document.getElementById('archiveSaleModal');
        const saleNameDisplay = document.getElementById('saleNameDisplay');
        const confirmArchiveBtn = document.getElementById('confirmArchiveSale');
        const cancelArchiveBtn = document.getElementById('cancelArchiveSale');
        let currentSaleId = null;

        // Open modal
        document.querySelectorAll('.openArchiveSaleModal').forEach(btn => {
            btn.addEventListener('click', function() {
                currentSaleId = this.getAttribute('data-id');
                const saleName = this.getAttribute('data-name');
                saleNameDisplay.textContent = saleName;
                archiveModal.classList.remove('hidden');
            });
        });

        // Cancel archive
        cancelArchiveBtn.addEventListener('click', () => {
            archiveModal.classList.add('hidden');
            currentSaleId = null;
        });

        // Confirm archive
        confirmArchiveBtn.addEventListener('click', () => {
            if (currentSaleId) {
                // Send AJAX request to archive the sale
                fetch(window.location.href, { // Changed from 'archive_sale.php' to current page
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'archive_sale=1&sale_id=' + currentSaleId // Added 'archive_sale=1'
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.location.reload();
                        } else {
                            alert('Error archiving transaction: ' + (data.message || 'Unknown error'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while archiving the transaction.');
                    });
            }
        });

        // Close modal when clicking outside
        archiveModal.addEventListener('click', function(e) {
            if (e.target === archiveModal) {
                archiveModal.classList.add('hidden');
                currentSaleId = null;
            }
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const previewButtons = document.querySelectorAll('.preview-sale-btn');

            // Detect mobile device
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
                        // On mobile, just open the PDF directly in a new tab
                        window.open(`print_sales_receipt.php?id=${saleId}`, '_blank');
                        return;
                    }

                    // Desktop: use Print.js
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
        });
    </script>
</body>

</html>