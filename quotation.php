<?php
include "database/database.php";

$database->createPcBuilder(); // or login_session(), whatever this does
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
$perPage     = 5;
$offset      = ($page - 1) * $perPage;

$totalRecords = $database->getPcBuildersCount($search);
$totalPages   = max(1, ceil($totalRecords / $perPage));

$pcBuilders   = $database->getPcBuildersPaginated($search, $offset, $perPage);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Custom PC Builder</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="assets/tailwind.min.css" rel="stylesheet" />
    <link href="assets/fonts.css" rel="stylesheet" />
    <link href="assets/quotation.css" rel="stylesheet" />
</head>

<body class="bg-gray-100 min-h-screen flex">

    <!-- Sidebar Toggle -->
    <button id="sidebar-toggle"
        class="md:hidden p-3 fixed top-4 left-4 z-60 text-white rounded shadow-lg"
        style="background-color: rgba(170,170,170,.82);">
        ☰
    </button>

    <?php include "sidebar.php"; ?>


    <main class="flex-1 ml-0 md:ml-64 p-6 flex flex-col gap-6">
        <div class="bg-white rounded-xl shadow p-5 mb-6">

            <!-- Page Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Quotations</h1>
                    <p class="text-sm text-gray-500">Manage and preview your saved PC quotations</p>
                </div>
                <button id="openBuilderModal"
                    class="px-4 py-2 text-sm bg-black text-white rounded-lg hover:bg-gray-800">
                    + New Quotation
                </button>
            </div>

            <!-- Search Bar -->
            <div class="mb-4">
                <form method="GET" action="quotation.php" class="flex gap-2">
                    <input
                        type="text"
                        name="search"
                        id="searchInput"
                        value="<?= htmlspecialchars($_GET['search'] ?? '') ?>"
                        placeholder="Search quotations..."
                        class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <!-- Hidden submit allows Enter key to work -->
                    <button type="submit" class="hidden"></button>
                </form>

                <?php if ($search !== ''): ?>
                    <p class="mt-2 text-sm text-gray-500">
                        Showing results for "<span class="font-medium text-gray-700"><?= htmlspecialchars($search) ?></span>"
                        (<?= $totalRecords ?> <?= $totalRecords === 1 ? 'result' : 'results' ?>)
                        <a href="quotation.php" class="text-blue-600 hover:underline ml-1">Clear search</a>
                    </p>
                <?php endif; ?>
            </div>

            <!-- Table -->
            <table class="w-full border-collapse text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="p-3 text-left">Quotation Name</th>
                        <th class="p-3 text-left">Created By</th>
                        <th class="p-3 text-left">Created</th>
                        <th class="p-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pcBuilders as $builder): ?>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="p-3 font-medium"><?= htmlspecialchars($builder['pc_builder_name']) ?></td>
                            <td class="p-3 text-gray-600"><?= htmlspecialchars($builder['created_by'] ?? 'Unknown') ?></td>
                            <td class="p-3"><?= date('M d, Y', strtotime($builder['created_at'])) ?></td>
                            <td class="p-3 text-center">
                                <button class="preview-quote-btn inline-flex items-center justify-center p-1 hover:opacity-80 transition-opacity text-red-600"
                                    data-id="<?= $builder['pc_builder_id'] ?>"
                                    title="Generate PDF">
                                    <?php include 'assets/images/pdf-icon.php'; ?>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="mt-6 flex flex-col sm:flex-row items-center justify-between gap-4 border-t pt-4">
                    <div class="text-sm text-gray-700">
                        Showing page <span class="font-medium"><?= $page ?></span> of <span class="font-medium"><?= $totalPages ?></span>
                        — <?= $totalRecords ?> total quotations
                    </div>

                    <nav class="flex items-center space-x-1">
                        <?php $searchParam = $search !== '' ? '&search=' . urlencode($search) : ''; ?>

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
                        $end   = min($totalPages, $page + $range);
                        if ($start > 1): ?>
                            <a href="?page=1<?= $searchParam ?>"
                                class="px-3 py-2 rounded-md text-sm font-medium border border-gray-300 hover:bg-gray-50">1</a>
                            <?php if ($start > 2): ?>
                                <span class="px-3 py-2 text-sm text-gray-500">...</span>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php for ($i = $start; $i <= $end; $i++): ?>
                            <?php if ($i === $page): ?>
                                <span class="px-3 py-2 rounded-md text-sm font-medium bg-blue-600 text-white"><?= $i ?></span>
                            <?php else: ?>
                                <a href="?page=<?= $i ?><?= $searchParam ?>"
                                    class="px-3 py-2 rounded-md text-sm font-medium border border-gray-300 hover:bg-gray-50">
                                    <?= $i ?>
                                </a>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <?php if ($end < $totalPages): ?>
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
        </div>

        <!-- PC Builder Modal -->
        <div id="pcBuilderModal"
            class="fixed inset-0 z-50 bg-black bg-opacity-50 flex items-center justify-center p-3"
            style="display:none;">

            <div class="qm-shell">

                <!-- Header -->
                <div class="qm-header">
                    <div class="qm-header-left">
                        <div class="qm-header-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18" />
                            </svg>
                        </div>
                        <div>
                            <h3>New Quotation</h3>
                            <p>Configure components and generate a price quote</p>
                        </div>
                    </div>
                    <button id="closeBuilderModal" class="qm-close">&times;</button>
                </div>

                <!-- Progress bar -->
                <div class="qm-progress-wrap">
                    <div class="qm-progress-bar" id="qmProgressBar"></div>
                </div>

                <!-- Body -->
                <div class="qm-body">

                    <!-- LEFT: Form -->
                    <div class="qm-left">

                        <form id="pc-builder-form" method="POST">

                            <!-- Build Name -->
                            <div class="qm-name-card">
                                <label class="qm-label" for="pc_builder_name">Quotation Name</label>
                                <input
                                    type="text"
                                    name="pc_builder_name"
                                    id="pc_builder_name"
                                    placeholder="e.g. Gaming Beast Pro, Budget Office PC..."
                                    required
                                    class="qm-name-input"
                                    autocomplete="off">
                            </div>

                            <?php
                            $pcParts    = array_filter($itemsByCategory, fn($cat) => $cat['category_type'] === 'pc_part');
                            $accessories = array_filter($itemsByCategory, fn($cat) => $cat['category_type'] === 'accessory');
                            ?>

                            <!-- PC Parts -->
                            <?php if (!empty($pcParts)): ?>
                                <div>
                                    <div class="qm-section-label">
                                        <span class="pill pill-blue">PC Parts</span>
                                        <div class="qm-section-line"></div>
                                    </div>
                                    <div class="qm-grid">
                                        <?php foreach ($pcParts as $category): ?>
                                            <div class="qm-comp-card" data-card="category_<?= $category['id'] ?>">
                                                <div class="qm-comp-title">
                                                    <span class="qm-comp-dot"></span>
                                                    <?= htmlspecialchars($category['name']) ?>
                                                </div>
                                                <select
                                                    name="category_<?= $category['id'] ?>"
                                                    data-category-id="<?= $category['id'] ?>"
                                                    class="qm-select qm-component-select">
                                                    <option value="">— Select —</option>
                                                    <?php foreach ($category['items'] as $item): ?>
                                                        <option value="<?= $item['item_id'] ?>" data-price="<?= $item['selling_price'] ?>">
                                                            <?= htmlspecialchars($item['item_name']) ?> — ₱<?= number_format($item['selling_price'], 2) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <?php if ($category['supports_quantity']): ?>
                                                    <div class="qm-qty-row">
                                                        <span class="qm-qty-label">Qty</span>
                                                        <input type="number" name="quantity_<?= $category['id'] ?>" min="1" value="1" class="qm-qty-input qm-qty" data-category-id="<?= $category['id'] ?>">
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Accessories -->
                            <?php if (!empty($accessories)): ?>
                                <div>
                                    <div class="qm-section-label">
                                        <span class="pill pill-green">Accessories</span>
                                        <div class="qm-section-line"></div>
                                    </div>
                                    <div class="qm-grid">
                                        <?php foreach ($accessories as $category): ?>
                                            <div class="qm-comp-card accessory-card" data-card="category_<?= $category['id'] ?>">
                                                <div class="qm-comp-title">
                                                    <span class="qm-comp-dot"></span>
                                                    <?= htmlspecialchars($category['name']) ?>
                                                </div>
                                                <select
                                                    name="category_<?= $category['id'] ?>"
                                                    data-category-id="<?= $category['id'] ?>"
                                                    class="qm-select qm-component-select">
                                                    <option value="">— Select —</option>
                                                    <?php foreach ($category['items'] as $item): ?>
                                                        <option value="<?= $item['item_id'] ?>" data-price="<?= $item['selling_price'] ?>">
                                                            <?= htmlspecialchars($item['item_name']) ?> — ₱<?= number_format($item['selling_price'], 2) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <?php if ($category['supports_quantity']): ?>
                                                    <div class="qm-qty-row">
                                                        <span class="qm-qty-label">Qty</span>
                                                        <input type="number" name="quantity_<?= $category['id'] ?>" min="1" value="1" class="qm-qty-input qm-qty" data-category-id="<?= $category['id'] ?>">
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                        </form>
                    </div>

                    <!-- RIGHT: Live Summary Panel -->
                    <div class="qm-right">
                        <div class="qm-summary-header">Build Summary</div>

                        <div class="qm-summary-name">
                            <div class="slabel">Quotation Name</div>
                            <div class="sval" id="qm-preview-name">—</div>
                        </div>

                        <div class="qm-summary-items" id="qm-summary-items">
                            <?php foreach ($categories as $category): ?>
                                <div class="qm-sitem" id="qm-sitem-<?= $category['category_id'] ?>">
                                    <span class="si-cat"><?= htmlspecialchars($category['category_name']) ?></span>
                                    <span class="si-name">Not selected</span>
                                    <span class="si-price"></span>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="qm-total-block">
                            <div class="qm-total-label">Total Price</div>
                            <div class="qm-total-val" id="totalAmount">₱0.00</div>
                            <div class="qm-item-count" id="qm-item-count">0 components selected</div>
                        </div>
                    </div>

                </div><!-- /body -->

                <!-- Footer -->
                <div class="qm-footer">
                    <button type="button" id="cancelButton" class="qm-btn qm-btn-cancel">Cancel</button>
                    <button type="submit" form="pc-builder-form" name="pc-build-btn" class="qm-btn qm-btn-save">
                        Save Quotation
                    </button>
                </div>

            </div>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Get all preview buttons
            const previewButtons = document.querySelectorAll('.preview-quote-btn');

            previewButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();

                    const pcBuilderId = this.getAttribute('data-id');

                    if (!pcBuilderId) {
                        alert('Invalid quotation ID');
                        return;
                    }

                    // Open PDF in new tab
                    window.open(`preview_quotation_pdf.php?id=${pcBuilderId}`, '_blank');
                });
            });
        });
    </script>

    <!-- Modal Toggle -->
    <script>
        const openBuilderModal = document.getElementById('openBuilderModal');
        const pcBuilderModal = document.getElementById('pcBuilderModal');
        const closeBuilderModal = document.getElementById('closeBuilderModal');
        const cancelButton = document.getElementById('cancelButton');

        const openModal = () => {
            pcBuilderModal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        };

        const closeModal = () => {
            pcBuilderModal.style.display = 'none';
            document.body.style.overflow = '';
        };

        openBuilderModal.addEventListener('click', openModal);
        closeBuilderModal.addEventListener('click', closeModal);
        cancelButton.addEventListener('click', closeModal);

        window.addEventListener('click', (e) => {
            if (e.target === pcBuilderModal) closeModal();
        });

        // FIXED: was checking classList.contains('hidden') but modal now uses style.display
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && pcBuilderModal.style.display === 'flex') closeModal();
        });
    </script>

    <script>
        (function() {
            const nameInput = document.getElementById('pc_builder_name');
            const previewName = document.getElementById('qm-preview-name');
            const totalEl = document.getElementById('totalAmount');
            const countEl = document.getElementById('qm-item-count');
            const progressBar = document.getElementById('qmProgressBar');
            const selects = document.querySelectorAll('.qm-component-select');
            const totalSelects = selects.length;

            // Name preview
            nameInput?.addEventListener('input', function() {
                previewName.textContent = this.value.trim() || '—';
            });

            // Component select change
            selects.forEach(select => {
                select.addEventListener('change', function() {
                    const catId = this.dataset.categoryId;
                    const card = document.querySelector(`[data-card="category_${catId}"]`);
                    const sitem = document.getElementById(`qm-sitem-${catId}`);
                    const qtyInput = document.querySelector(`.qm-qty[data-category-id="${catId}"]`);

                    const selected = this.options[this.selectedIndex];
                    const price = parseFloat(selected.dataset.price) || 0;
                    const qty = qtyInput ? parseInt(qtyInput.value) || 1 : 1;

                    if (this.value) {
                        // Mark card selected
                        card?.classList.add('selected');

                        // Update summary
                        if (sitem) {
                            sitem.classList.add('filled');
                            sitem.querySelector('.si-name').textContent = selected.text.split(' — ')[0];
                            sitem.querySelector('.si-price').textContent = qty > 1 ?
                                `₱${(price * qty).toLocaleString('en-PH', {minimumFractionDigits:2})} (×${qty})` :
                                `₱${price.toLocaleString('en-PH', {minimumFractionDigits:2})}`;
                        }
                    } else {
                        card?.classList.remove('selected');
                        if (sitem) {
                            sitem.classList.remove('filled');
                            sitem.querySelector('.si-name').textContent = 'Not selected';
                            sitem.querySelector('.si-price').textContent = '';
                        }
                    }

                    recalc();
                });

                // Qty changes
                const catId = select.dataset.categoryId;
                const qtyInput = document.querySelector(`.qm-qty[data-category-id="${catId}"]`);
                qtyInput?.addEventListener('input', () => {
                    // Trigger select change to refresh price
                    select.dispatchEvent(new Event('change'));
                });
            });

            function recalc() {
                let total = 0,
                    count = 0;

                selects.forEach(sel => {
                    if (!sel.value) return;
                    const price = parseFloat(sel.options[sel.selectedIndex].dataset.price) || 0;
                    const catId = sel.dataset.categoryId;
                    const qty = parseInt(document.querySelector(`.qm-qty[data-category-id="${catId}"]`)?.value) || 1;
                    total += price * qty;
                    count++;
                });

                totalEl.textContent = '₱' + total.toLocaleString('en-PH', {
                    minimumFractionDigits: 2
                });
                countEl.textContent = count + ' component' + (count !== 1 ? 's' : '') + ' selected';

                // Progress bar
                const pct = totalSelects > 0 ? (count / totalSelects) * 100 : 0;
                progressBar.style.width = pct + '%';
            }
        })();
    </script>

    <!-- Sidebar Toggle & Search -->
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

        // Auto-submit search form after user stops typing
        let searchTimeout;
        const searchInput = document.getElementById('searchInput');

        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    this.form.submit();
                }, 500);
            });
        }
    </script>

    <!-- Save scroll position -->
    <script>
        window.addEventListener('beforeunload', () => {
            sessionStorage.setItem('scrollPos', window.scrollY);
        });

        window.addEventListener('load', () => {
            const scrollPos = sessionStorage.getItem('scrollPos');
            if (scrollPos) {
                window.scrollTo(0, parseInt(scrollPos));
                sessionStorage.removeItem('scrollPos');
            }
        });
    </script>

    <!-- Alert handlers -->
    <script>
        const successAlert = document.getElementById('successAlert');
        if (successAlert) {
            setTimeout(() => {
                successAlert.style.display = 'none';
                fetch('unset_alert.php');
            }, 3000);
        }

        const errorAlert = document.getElementById('errorAlert');
        if (errorAlert) {
            setTimeout(() => {
                errorAlert.style.display = 'none';
                fetch('unset_alert.php');
            }, 3000);
        }
    </script>

</body>

</html>