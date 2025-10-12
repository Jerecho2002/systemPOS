<?php
include "database/database.php";
$database->createPcBuilder();
$cpu = $database->getItemsByCategoryName("CPU");
$gpu = $database->getItemsByCategoryName("GPU");
$ram = $database->getItemsByCategoryName("RAM");
$motherboard = $database->getItemsByCategoryName("Motherboard");
$storage = $database->getItemsByCategoryName("Storage");
$psu = $database->getItemsByCategoryName("PSU");
$case = $database->getItemsByCategoryName("Case");
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

    <!-- Sidebar Toggle Button -->
    <button id="sidebar-toggle" class="md:hidden p-3 fixed top-4 left-4 z-60 text-white rounded shadow-lg"
        style="background-color: rgba(170, 170, 170, 0.82);">
        ☰
    </button>

    <!-- Sidebar -->
    <?php include "sidebar.php"; ?>

    <main class="flex-1 ml-0 md:ml-64 p-6 flex flex-col gap-6">
  <div class="flex flex-wrap gap-6">

    <!-- 🖥️ Custom PC Builder Form -->
    <section class="bg-white rounded-xl shadow p-5 flex-1 min-w-[380px] max-w-[600px]">
      <h2 class="text-xl font-bold mb-4">🖥️ Custom PC Builder</h2>

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

      <form id="pc-builder-form" method="POST" action="<?= $_SERVER['PHP_SELF']; ?>" class="grid grid-cols-2 gap-3">

        <!-- Build Name -->
        <div class="col-span-2">
          <label for="pc_builder_name" class="block text-sm font-medium text-gray-700 mb-1">Build Name</label>
          <input type="text" name="pc_builder_name" id="pc_builder_name" placeholder="Enter build name"
            class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" />
        </div>

        <!-- CPU -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">CPU</label>
          <select name="CPU"
            class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Choose</option>
            <?php foreach ($cpu as $item): ?>
              <option value="<?= $item['item_id'] ?>"><?= $item['item_name'] ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- GPU -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">GPU</label>
          <select name="GPU"
            class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Choose</option>
            <?php foreach ($gpu as $item): ?>
              <option value="<?= $item['item_id'] ?>"><?= $item['item_name'] ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- RAM -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">RAM</label>
          <select name="RAM"
            class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Choose</option>
            <?php foreach ($ram as $item): ?>
              <option value="<?= $item['item_id'] ?>"><?= $item['item_name'] ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Motherboard -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Motherboard</label>
          <select name="Motherboard"
            class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Choose</option>
            <?php foreach ($motherboard as $item): ?>
              <option value="<?= $item['item_id'] ?>"><?= $item['item_name'] ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Storage -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Storage</label>
          <select name="Storage"
            class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Choose</option>
            <?php foreach ($storage as $item): ?>
              <option value="<?= $item['item_id'] ?>"><?= $item['item_name'] ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- PSU -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Power Supply</label>
          <select name="PSU"
            class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Choose</option>
            <?php foreach ($psu as $item): ?>
              <option value="<?= $item['item_id'] ?>"><?= $item['item_name'] ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Case -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Case</label>
          <select name="Case"
            class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Choose</option>
            <?php foreach ($case as $item): ?>
              <option value="<?= $item['item_id'] ?>"><?= $item['item_name'] ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Save Button -->
        <div class="col-span-2 text-right mt-1">
          <button type="submit" name="pc-build-btn"
            class="bg-blue-600 text-white px-5 py-2 rounded hover:bg-blue-700 transition text-sm">
            💾 Save Build
          </button>
        </div>
      </form>
    </section>

    <!-- 🧩 Build Preview -->
    <section class="bg-white rounded-xl shadow p-5 flex-1 min-w-[380px] max-w-[500px]">
      <h3 class="text-xl font-semibold mb-3">🧩 Build Preview</h3>
      <ul id="build-preview" class="space-y-2 text-gray-700 text-sm">
        <li><strong>Build Name:</strong> <span data-part="pc_builder_name"></span></li>
        <li><strong>CPU:</strong> <span data-part="CPU"></span></li>
        <li><strong>GPU:</strong> <span data-part="GPU"></span></li>
        <li><strong>RAM:</strong> <span data-part="RAM"></span></li>
        <li><strong>Motherboard:</strong> <span data-part="Motherboard"></span></li>
        <li><strong>Storage:</strong> <span data-part="Storage"></span></li>
        <li><strong>PSU:</strong> <span data-part="PSU"></span></li>
        <li><strong>Case:</strong> <span data-part="Case"></span></li>
      </ul>
    </section>

  </div>

  <!-- 🧰 Saved PC Builds Table -->
  <section class="bg-white rounded-xl shadow p-6 mt-4">
    <h3 class="text-xl font-semibold mb-4">🧰 Saved PC Builds</h3>

    <div class="overflow-y-auto max-h-[480px] rounded-lg border border-gray-200">
      <table class="min-w-full text-sm text-left text-gray-700">
        <thead class="bg-gray-100 text-gray-600 uppercase text-xs">
          <tr>
            <th class="px-4 py-3">Build Name</th>
            <th class="px-4 py-3">Components</th>
            <th class="px-4 py-3">Total Price</th>
            <th class="px-4 py-3">Status</th>
            <th class="px-4 py-3">Created At</th>
            <th class="px-4 py-3 text-center">Actions</th>
          </tr>
        </thead>

        <tbody class="divide-y divide-gray-200">
          <?php if (!empty($pc_builders)): ?>
            <?php foreach ($pc_builders as $build): ?>
              <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 font-medium text-gray-900">
                  <?= htmlspecialchars($build['pc_builder_name']); ?>
                </td>
                <td class="px-4 py-3 text-gray-700">
                  <?= htmlspecialchars($build['components'] ?? '—'); ?>
                </td>
                <td class="px-4 py-3 font-medium text-blue-600">
                  ₱<?= number_format($build['total_price'], 2); ?>
                </td>
                <td class="px-4 py-3">
                  <?php if ($build['status'] === 'Completed'): ?>
                    <span class="px-2 py-1 text-xs bg-green-100 text-green-700 rounded-full">Completed</span>
                  <?php elseif ($build['status'] === 'Pending'): ?>
                    <span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-700 rounded-full">Pending</span>
                  <?php else: ?>
                    <span class="px-2 py-1 text-xs bg-gray-100 text-gray-600 rounded-full">Draft</span>
                  <?php endif; ?>
                </td>
                <td class="px-4 py-3 text-gray-500 text-xs">
                  <?= date('M d, Y h:i A', strtotime($build['created_at'])); ?>
                </td>
                <td class="px-4 py-3 flex gap-2 justify-center">
                  <a href="view_build.php?id=<?= $build['pc_builder_id']; ?>"
                    class="px-3 py-1 text-xs bg-blue-100 text-blue-700 rounded hover:bg-blue-200 transition">View</a>
                  <a href="edit_build.php?id=<?= $build['pc_builder_id']; ?>"
                    class="px-3 py-1 text-xs bg-yellow-100 text-yellow-700 rounded hover:bg-yellow-200 transition">Edit</a>
                  <a href="delete_build.php?id=<?= $build['pc_builder_id']; ?>"
                    class="px-3 py-1 text-xs bg-red-100 text-red-700 rounded hover:bg-red-200 transition"
                    onclick="return confirm('Are you sure you want to delete this build?')">Delete</a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="6" class="px-4 py-6 text-center text-gray-400 text-sm">
                No saved builds yet.
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>
</main>


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