<?php
include "database/database.php";

$database->createPcBuilder();
$categories = $database->getAllCategories();
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
    <div class="flex flex-wrap gap-6">

      <!-- PC BUILDER -->
      <section class="bg-white rounded-xl shadow p-5 flex-1 min-w-[380px] max-w-[600px]">
        <h3 class="font-semibold text-lg mt-4">PC Parts</h3>

        <?php if (isset($_SESSION['create-success'])): ?>
          <div id="successAlert"
            class="mb-3 px-3 py-2 bg-green-100 border border-green-400 text-green-700 text-sm rounded-lg">
            <?= $_SESSION['create-success']; ?>
          </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['create-error'])): ?>
          <div id="errorAlert"
            class="mb-3 px-3 py-2 bg-red-100 border border-red-400 text-red-700 text-sm rounded-lg">
            <?= $_SESSION['create-error']; ?>
          </div>
        <?php endif; ?>

        <form id="pc-builder-form" method="POST" class="grid grid-cols-2 gap-3">

          <!-- Build Name -->
          <div class="col-span-2">
            <label class="block text-sm font-medium mb-1">Build Name</label>
            <input type="text" name="pc_builder_name" id="pc_builder_name"
              class="w-full p-2 border rounded focus:ring-2 focus:ring-blue-500">
          </div>

          <!-- PC PARTS -->
          <?php foreach ($itemsByCategory as $category): ?>
            <?php if ($category['category_type'] !== 'pc_part') continue; ?>

            <div class="mb-4">
              <label class="block text-sm font-medium mb-1">
                <?= htmlspecialchars($category['name']) ?>
              </label>

              <select name="category_<?= $category['id'] ?>" class="w-full p-2 border rounded">
                <option value="">Choose</option>

                <?php foreach ($category['items'] as $item): ?>
                  <option value="<?= $item['item_id'] ?>" data-price="<?= $item['selling_price'] ?>">
                    ₱<?= number_format($item['selling_price'], 2) ?> <?= htmlspecialchars($item['item_name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>

              <?php if ($category['supports_quantity']): ?>
                <input type="number" name="quantity_<?= $category['id'] ?>" min="1" value="1"
                  class="mt-2 w-20 p-2 border rounded">
              <?php endif; ?>
            </div>
          <?php endforeach; ?>

          <!-- -->
          <h3 class="font-semibold text-lg mt-6">Accessories</h3>

          <?php foreach ($itemsByCategory as $category): ?>
            <?php if ($category['category_type'] !== 'accessory') continue; ?>

            <div class="mb-4">
              <label class="block text-sm font-medium mb-1">
                <?= htmlspecialchars($category['name']) ?>
              </label>

              <select name="category_<?= $category['id'] ?>" class="w-full p-2 border rounded">
                <option value="">Choose</option>

                <?php foreach ($category['items'] as $item): ?>
                  <option value="<?= $item['item_id'] ?>" data-price="<?= $item['selling_price'] ?>">
                    ₱<?= number_format($item['selling_price'], 2) ?> <?= htmlspecialchars($item['item_name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>

              <?php if ($category['supports_quantity']): ?>
                <input type="number" name="quantity_<?= $category['id'] ?>" min="1" value="1"
                  class="mt-2 w-20 p-2 border rounded">
              <?php endif; ?>
            </div>
          <?php endforeach; ?>



          <!-- Save -->
          <div class="col-span-2 text-right">
            <button type="submit" name="pc-build-btn"
              class="bg-blue-600 text-white px-5 py-2 rounded hover:bg-blue-700">
              Save Build
            </button>
          </div>

        </form>
      </section>

      <!--  BUILD PREVIEW -->
      <section class="bg-white rounded-xl shadow p-5 flex-1 min-w-[380px] max-w-[500px]">
        <h3 class="text-xl font-semibold mb-3">Build Preview</h3>
        <ul class="space-y-2 text-sm">
          <li><strong>Build Name:</strong> <span data-part="pc_builder_name">--</span></li>

          <?php foreach ($categories as $category): ?>
            <li>
              <strong><?= htmlspecialchars($category['category_name']) ?>:</strong>
              <span data-part="category_<?= $category['category_id'] ?>">--</span>

            </li>

          <?php endforeach; ?>
          <li class="pt-2 border-t mt-2 text-base font-semibold">
            <strong>Total:</strong>
            <span id="totalAmount">₱0.00</span>
          </li>



        </ul>
      </section>

    </div>
  </main>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const selects = document.querySelectorAll('#pc-builder-form select');
      const totalEl = document.getElementById('totalAmount');

      const formatPeso = (num) =>
        '₱' + num.toLocaleString('en-PH', {
          minimumFractionDigits: 2
        });

      const calculateTotal = () => {
        let total = 0;

        selects.forEach(select => {
          const option = select.options[select.selectedIndex];
          if (!option) return;

          const price = parseFloat(option.dataset.price || 0);
          total += price;
        });

        totalEl.textContent = formatPeso(total);
      };

      // initial calc
      calculateTotal();

      // recalc on change
      selects.forEach(select => {
        select.addEventListener('change', calculateTotal);
      });
    });
  </script>

  <!-- BugerBar Toggle -->
  <script>
    const sidebar = document.getElementById('mobile-sidebar');
    const toggleBtn = document.getElementById('sidebar-toggle');

    toggleBtn.addEventListener('click', () => {
      sidebar.classList.toggle('-translate-x-full');
    });
  </script>

  <!-- BugerBar Close -->
  <script>
    const closeBtn = document.getElementById('sidebar-close');

    closeBtn.addEventListener('click', () => {
      sidebar.classList.add('-translate-x-full'); // close sidebar
    });
  </script>

  <!-- Save scroll before reload/submit -->
  <script>
    window.addEventListener('beforeunload', () => {
      sessionStorage.setItem('scrollPos', window.scrollY);
    });

    // Restore scroll on load
    window.addEventListener('load', () => {
      const scrollPos = sessionStorage.getItem('scrollPos');
      if (scrollPos) {
        window.scrollTo(0, parseInt(scrollPos));
        sessionStorage.removeItem('scrollPos');
      }
    });
  </script>

  <!-- Unset Alert -->
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

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      // helpers
      const showOrDash = (str) => (str && str.trim() ? str : '--');

      // build name
      const buildNameInput = document.getElementById('pc_builder_name');
      const buildNamePreview = document.querySelector('[data-part="pc_builder_name"]');
      if (buildNameInput && buildNamePreview) {
        // seed initial value
        buildNamePreview.textContent = showOrDash(buildNameInput.value);

        // update on input
        buildNameInput.addEventListener('input', (e) => {
          buildNamePreview.textContent = showOrDash(e.target.value);
        });
      }

      // selects — target all selects inside the PC builder form so class isn't required
      const selects = document.querySelectorAll('#pc-builder-form select');

      selects.forEach(select => {
        const name = select.name; // e.g. "CPU", "GPU", etc.
        const previewEl = document.querySelector(`[data-part="${name}"]`);
        if (!previewEl) return; // nothing to update for this select

        // seed initial preview from current selection (handles page reload with preselected values)
        const initialOption = select.options[select.selectedIndex];
        previewEl.textContent = showOrDash(initialOption ? initialOption.textContent : '');

        // update when changed
        select.addEventListener('change', (e) => {
          const selected = e.target.options[e.target.selectedIndex];
          previewEl.textContent = showOrDash(selected ? selected.textContent : '');
        });
      });
    });
  </script>

</body>

</html>