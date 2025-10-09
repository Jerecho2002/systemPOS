<?php
include "database/database.php";
$database->login_session();
$stats = $database->getTodaysSalesStats();
$items = $database->select_items();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>POS & Inventory - Dashboard</title>
  <link rel="stylesheet" href="assets/tailwind.min.css">
</head>

<body class="bg-gray-50 flex">

  <!-- Mobile Sidebar Toggle Button -->
  <button id="sidebar-toggle" class="md:hidden p-3 fixed top-4 left-4 z-60 text-white rounded shadow-lg"
    style="background-color: rgba(170, 170, 170, 0.82);">
    ☰
  </button>


  <?php include "sidebar.php"; ?>

  <!-- Main Content -->
  <main class="flex-1 ml-0 md:ml-64 p-6">
    <h1 class="text-2xl font-bold mb-4">Dashboard</h1>
    <p class="text-gray-500">Welcome back, <?php echo $_SESSION['login-success']; ?>! Here's an overview of your
      business today.</p>
    </header>

    <!-- POS Overview -->
    <section>
      <h3 class="text-lg font-semibold mb-4">Point of Sale Overview</h3>
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow p-4">
          <p class="text-gray-500 text-sm">Today's Sales</p>
          <h4 class="text-2xl font-bold"><?= $stats['transaction_count']; ?></h4>
          <p class="text-xs text-gray-500">transactions</p>
        </div>
        <div class="bg-white rounded-xl shadow p-4">
          <p class="text-gray-500 text-sm">Today's Revenue</p>
          <h4 class="text-2xl font-bold">₱<?= number_format($stats['total_sales'], 2) ?></h4>
          <p class="text-xs text-green-600"><?= $stats['growth_percent'] ?>% from yesterday</p>
        </div>
        <div class="bg-white rounded-xl shadow p-4">
          <p class="text-gray-500 text-sm">Avg. Transaction</p>
          <h4 class="text-2xl font-bold">₱<?= number_format($stats['avg_transaction'], 2) ?></h4>
          <p class="text-xs text-gray-500">per sale</p>
        </div>
        <div class="bg-white rounded-xl shadow p-4">
          <p class="text-gray-500 text-sm">Total Items</p>
          <h4 class="text-2xl font-bold"><?= count($items) ?></h4>
          <p class="text-xs text-gray-500">in catalog</p>
        </div>
      </div>
    </section>

    <!-- Inventory Overview -->
    <section class="mt-8">
      <h3 class="text-lg font-semibold mb-4">Inventory Overview</h3>
    </section>

    <!-- Bottom Row -->
    <section class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-4">

      <!-- Top-Selling Items -->
      <div class="bg-white rounded-xl shadow p-4">
        <h4 class="font-semibold">Top-Selling Items</h4>
        <p class="text-sm text-gray-500 mb-4">Based on recent sales</p>
        <ul class="space-y-3 text-sm">
          <li class="flex justify-between">
            <span>Wireless Mouse</span>
            <span class="font-medium">124 sold</span>
          </li>
          <li class="flex justify-between">
            <span>USB-C Cable</span>
            <span class="font-medium">102 sold</span>
          </li>
          <li class="flex justify-between">
            <span>Bluetooth Speaker</span>
            <span class="font-medium">87 sold</span>
          </li>
        </ul>
      </div>
      <?php
      usort($items, function ($a, $b) {
        return $b['created_at'] <=> $a['created_at'];
      });
      $items = array_slice($items, 0, 3);
      ?>

      <!-- Recently Added Items -->
      <div class="bg-white rounded-xl shadow p-4">
        <h4 class="font-semibold">Recently Added Items</h4>
        <p class="text-sm text-gray-500 mb-4">Newly added to inventory</p>
        <ul class="space-y-3 text-sm">
          <?php foreach ($items as $item): ?>
            <li class="flex justify-between">
              <span><?= $item['item_name']; ?></span>
              <span class="text-gray-500 text-xs"><?= date('F j, Y, g:i A', strtotime($item['created_at'])); ?></span>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </section>

    <!-- Additional Section -->
    <section class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-4">

      <!-- Activity Log -->
      <div class="bg-white rounded-xl shadow p-4">
        <h4 class="font-semibold">Activity Log</h4>
        <p class="text-sm text-gray-500 mb-4">Recent admin actions</p>
        <ul class="space-y-2 text-sm">
          <li><span class="text-green-600">[+]</span> Added item "Desk Lamp" (by Admin)</li>
          <li><span class="text-blue-600">[~]</span> Updated stock for "HDMI Cable"</li>
          <li><span class="text-red-600">[-]</span> Removed item "Old Mouse Model"</li>
        </ul>
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
</body>

</html>