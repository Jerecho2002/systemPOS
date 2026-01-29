<?php
include "database/database.php";

$database->createPcBuilder();
$categories = $database->getAllCategories();
$userId = $_SESSION['user_id'];
$pcBuilders = $database->getPcBuildersByUser($userId);
$itemsByCategory = [];

foreach ($categories as $category) {
    $itemsByCategory[] = [
        'id'    => $category['category_id'],
        'name'  => $category['category_name'],
        'slug'  => $category['category_slug'],
        'category_type'     => $category['category_type'],
        'supports_quantity' => (int) $category['supports_quantity'],
        'items' => $database->getItemsByCategoryId($category['category_id']),
    ];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Custom PC Builder</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="assets/tailwind.min.css" rel="stylesheet" />
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
                    <p class="text-sm text-gray-500">
                        Manage and preview your saved PC quotations
                    </p>
                </div>

                <button
                    id="openBuilderModal"
                    class="bg-blue-600 text-white px-4 py-2 rounded-lg shadow hover:bg-blue-700 transition">
                    + New Quotation
                </button>
            </div>


            <table class="w-full border-collapse text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="p-3 text-left">Quotation Name</th>
                        <th class="p-3 text-left">Status</th>
                        <th class="p-3 text-left">Created</th>
                        <th class="p-3 text-center">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($pcBuilders as $builder): ?>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="p-3 font-medium">
                                <?= htmlspecialchars($builder['pc_builder_name']) ?>
                            </td>

                            <td class="p-3">
                                <span class="px-2 py-1 rounded text-xs bg-yellow-100 text-yellow-800">
                                    <?= htmlspecialchars($builder['status']) ?>
                                </span>
                            </td>

                            <td class="p-3">
                                <?= date('M d, Y', strtotime($builder['created_at'])) ?>
                            </td>

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
        </div>

        <!-- REDESIGNED MODAL - Single Column Layout -->
        <div id="pcBuilderModal"
            class="fixed inset-0 z-50 hidden bg-black bg-opacity-50 flex items-center justify-center p-2">

            <div class="bg-white rounded-lg w-full max-w-3xl flex flex-col shadow-2xl my-4" style="height: 90vh;">

                <!-- Modal Header -->
                <div class="flex justify-between items-center px-4 py-3 border-b bg-gray-50 rounded-t-lg flex-shrink-0">
                    <h3 class="text-base font-bold text-gray-800">New Quotation</h3>
                    <button id="closeBuilderModal"
                        class="text-gray-400 hover:text-gray-600 text-2xl leading-none">
                        &times;
                    </button>
                </div>

                <!-- Modal Body - Scrollable Content -->
                <div class="flex-1 overflow-y-auto p-4">

                    <form id="pc-builder-form" method="POST" class="space-y-3">

                        <!-- Quotation Name -->
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                            <label class="block text-sm font-semibold text-blue-900 mb-1.5">
                                Quotation Name *
                            </label>
                            <input
                                type="text"
                                name="pc_builder_name"
                                id="pc_builder_name"
                                placeholder="Enter a name for this build..."
                                required
                                class="w-full p-2 border border-blue-300 rounded text-sm">
                        </div>

                        <!-- Component Selection -->
                        <!-- Component Selection -->
                        <div class="space-y-4">
                            <?php
                            // Group categories by type
                            $pcParts = array_filter($itemsByCategory, fn($cat) => $cat['category_type'] === 'pc_part');
                            $accessories = array_filter($itemsByCategory, fn($cat) => $cat['category_type'] === 'accessory');
                            ?>

                            <!-- PC PARTS Section -->
                            <?php if (!empty($pcParts)): ?>
                                <div>
                                    <h5 class="text-sm font-bold text-gray-800 mb-3 pb-2 border-b-2 border-blue-500">
                                        PC PARTS
                                    </h5>
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                        <?php foreach ($pcParts as $category): ?>
                                            <div class="bg-white border border-gray-200 rounded-lg p-3">
                                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">
                                                    <?= htmlspecialchars($category['name']) ?>
                                                </label>

                                                <select
                                                    name="category_<?= $category['id'] ?>"
                                                    data-category-id="<?= $category['id'] ?>"
                                                    class="w-full p-2 border border-gray-300 rounded bg-white text-sm">

                                                    <option value="">Select</option>

                                                    <?php foreach ($category['items'] as $item): ?>
                                                        <option
                                                            value="<?= $item['item_id'] ?>"
                                                            data-price="<?= $item['selling_price'] ?>">
                                                            <?= htmlspecialchars($item['item_name']) ?> - ₱<?= number_format($item['selling_price'], 2) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>

                                                <?php if ($category['supports_quantity']): ?>
                                                    <div class="mt-2 flex items-center gap-2">
                                                        <label class="text-xs text-gray-600">Qty:</label>
                                                        <input
                                                            type="number"
                                                            name="quantity_<?= $category['id'] ?>"
                                                            min="1"
                                                            value="1"
                                                            class="w-16 p-1.5 border border-gray-300 rounded text-sm">
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- ACCESSORIES Section -->
                            <?php if (!empty($accessories)): ?>
                                <div>
                                    <h5 class="text-sm font-bold text-gray-800 mb-3 pb-2 border-b-2 border-green-500">
                                        ACCESSORIES
                                    </h5>
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                        <?php foreach ($accessories as $category): ?>
                                            <div class="bg-white border border-gray-200 rounded-lg p-3">
                                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">
                                                    <?= htmlspecialchars($category['name']) ?>
                                                </label>

                                                <select
                                                    name="category_<?= $category['id'] ?>"
                                                    data-category-id="<?= $category['id'] ?>"
                                                    class="w-full p-2 border border-gray-300 rounded bg-white text-sm">

                                                    <option value="">Select</option>

                                                    <?php foreach ($category['items'] as $item): ?>
                                                        <option
                                                            value="<?= $item['item_id'] ?>"
                                                            data-price="<?= $item['selling_price'] ?>">
                                                            <?= htmlspecialchars($item['item_name']) ?> - ₱<?= number_format($item['selling_price'], 2) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>

                                                <?php if ($category['supports_quantity']): ?>
                                                    <div class="mt-2 flex items-center gap-2">
                                                        <label class="text-xs text-gray-600">Qty:</label>
                                                        <input
                                                            type="number"
                                                            name="quantity_<?= $category['id'] ?>"
                                                            min="1"
                                                            value="1"
                                                            class="w-16 p-1.5 border border-gray-300 rounded text-sm">
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                    </form>

                    <!-- Build Preview Section -->
                    <div class="mt-4 bg-gradient-to-br from-gray-50 to-gray-100 rounded-lg p-4 border border-gray-200">
                        <h4 class="font-bold text-sm mb-3 text-gray-800">Build Summary</h4>

                        <div class="bg-white rounded-lg p-3 mb-3 border border-gray-200">
                            <div class="space-y-2 text-xs">

                                <!-- Build Name Preview -->
                                <div class="pb-2 border-b">
                                    <p class="text-xs text-gray-500 uppercase mb-0.5">Build Name</p>
                                    <p class="font-semibold text-gray-800" data-part="pc_builder_name">--</p>
                                </div>

                                <!-- Components Preview -->
                                <?php foreach ($categories as $category): ?>
                                    <div class="pb-1.5 border-b border-gray-100 last:border-0">
                                        <p class="text-gray-500 mb-0.5"><?= htmlspecialchars($category['category_name']) ?></p>
                                        <p class="text-gray-700" data-part="category_<?= $category['category_id'] ?>">
                                            Not selected
                                        </p>
                                    </div>
                                <?php endforeach; ?>

                            </div>
                        </div>

                        <!-- Total -->
                        <div class="bg-blue-600 text-white rounded-lg p-3">
                            <p class="text-xs uppercase mb-0.5 opacity-90">Total Price</p>
                            <p class="text-xl font-bold" id="totalAmount">₱0.00</p>
                        </div>

                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="flex-shrink-0 border-t bg-gray-50 px-4 py-3 rounded-b-lg flex justify-end gap-2">
                    <button
                        type="button"
                        id="cancelButton"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded hover:bg-gray-100 text-sm">
                        Cancel
                    </button>
                    <button
                        type="submit"
                        form="pc-builder-form"
                        name="pc-build-btn"
                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
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

    <!-- Modal Toggle Scripts -->
    <script>
        const openBuilderModal = document.getElementById('openBuilderModal');
        const pcBuilderModal = document.getElementById('pcBuilderModal');
        const closeBuilderModal = document.getElementById('closeBuilderModal');
        const cancelButton = document.getElementById('cancelButton');

        const openModal = () => {
            pcBuilderModal.classList.remove('hidden');
            document.body.style.overflow = 'hidden'; // prevent background scroll
        };

        const closeModal = () => {
            pcBuilderModal.classList.add('hidden');
            document.body.style.overflow = ''; // restore scroll
        };

        openBuilderModal.addEventListener('click', openModal);
        closeBuilderModal.addEventListener('click', closeModal);
        cancelButton.addEventListener('click', closeModal);

        // Close on backdrop click
        window.addEventListener('click', (e) => {
            if (e.target === pcBuilderModal) {
                closeModal();
            }
        });

        // Close on Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !pcBuilderModal.classList.contains('hidden')) {
                closeModal();
            }
        });
    </script>

    <!-- Total Calculator -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('pc-builder-form');
            const totalEl = document.getElementById('totalAmount');

            const formatPeso = (num) =>
                '₱' + num.toLocaleString('en-PH', {
                    minimumFractionDigits: 2
                });

            const calculateTotal = () => {
                let total = 0;
                const selects = form.querySelectorAll('select[data-category-id]');

                selects.forEach(select => {
                    const option = select.options[select.selectedIndex];
                    if (!option || !option.dataset.price) return;

                    const price = parseFloat(option.dataset.price) || 0;
                    const categoryId = select.dataset.categoryId;

                    const qtyInput = form.querySelector(`input[name="quantity_${categoryId}"]`);
                    const quantity = qtyInput ? Math.max(1, parseInt(qtyInput.value) || 1) : 1;

                    total += price * quantity;
                });

                totalEl.textContent = formatPeso(total);
            };

            form.addEventListener('change', calculateTotal);
            form.addEventListener('input', calculateTotal);
            calculateTotal();
        });
    </script>

    <!-- Live Preview Updates -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const showOrDash = (str) => (str && str.trim() ? str : 'Not selected');

            // Build name preview
            const buildNameInput = document.getElementById('pc_builder_name');
            const buildNamePreview = document.querySelector('[data-part="pc_builder_name"]');

            if (buildNameInput && buildNamePreview) {
                buildNamePreview.textContent = showOrDash(buildNameInput.value);
                buildNameInput.addEventListener('input', (e) => {
                    buildNamePreview.textContent = showOrDash(e.target.value);
                });
            }

            // Component previews
            const selects = document.querySelectorAll('#pc-builder-form select');
            selects.forEach(select => {
                const name = select.name;
                const previewEl = document.querySelector(`[data-part="${name}"]`);
                if (!previewEl) return;

                const initialOption = select.options[select.selectedIndex];
                previewEl.textContent = showOrDash(initialOption ? initialOption.textContent : '');

                select.addEventListener('change', (e) => {
                    const selected = e.target.options[e.target.selectedIndex];
                    previewEl.textContent = showOrDash(selected ? selected.textContent : '');
                });
            });
        });
    </script>

    <!-- Sidebar Toggle -->
    <script>
        const sidebar = document.getElementById('mobile-sidebar');
        const toggleBtn = document.getElementById('sidebar-toggle');

        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('-translate-x-full');
        });
    </script>

    <!-- Sidebar Close -->
    <script>
        const closeBtn = document.getElementById('sidebar-close');
        closeBtn.addEventListener('click', () => {
            sidebar.classList.add('-translate-x-full');
        });
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