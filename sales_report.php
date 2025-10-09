<?php
  include "database/database.php";
  $database->login_session();
  $sales = $database->select_sales();
  $sale_items = $database->select_sale_items();
  $salesTrend = $database->getLast7DaysSalesTrend();

  $period = $_GET['period'] ?? 'daily';

    switch ($period) {
        case 'daily':
            $stats = $database->getTodaysSalesStats();
            break;
        case 'weekly':
            $stats = $database->getWeeklySalesStats();
            break;
        case 'monthly':
            $stats = $database->getMonthlySalesStats();
            break;
        case 'yearly':
            $stats = $database->getYearlySalesStats();
            break;
        default:
            $stats = $database->getMonthlySalesStats();
            break;
    }

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>POS & Inventory - Sales Reports</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <style>
    /* Custom styles to mimic the icons in the image */
    .icon-placeholder {
      width: 24px;
      height: 24px;
      background-color: #d1d5db; /* A light gray */
      border-radius: 50%;
      display: flex;
      justify-content: center;
      align-items: center;
      font-size: 14px;
    }
  </style>
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
    <header class="mb-6">
      <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-800">Pos Reports</h2>
        </div>
    </header>

    <section class="bg-white rounded-xl shadow-md p-6">
      <div class="flex items-center justify-between mb-4">
        <div>
          <h3 class="text-xl font-bold text-gray-800">Sales Reports & Analytics</h3>
          <p class="text-sm text-gray-500">Analyze your sales performance and trends</p>
        </div>
        <div class="flex items-center space-x-2">
         <form method="GET" id="filterForm">
          <select name="period" id="periodSelect" class="px-3 py-2 text-sm border rounded-lg focus:outline-none">
            <option value="daily" <?= ($_GET['period'] ?? '') === 'daily' ? 'selected' : '' ?>>Today</option>
            <option value="weekly" <?= ($_GET['period'] ?? '') === 'weekly' ? 'selected' : '' ?>>This Week</option>
            <option value="monthly" <?= ($_GET['period'] ?? '') === 'monthly' ? 'selected' : '' ?>>This Month</option>
            <option value="yearly" <?= ($_GET['period'] ?? '') === 'yearly' ? 'selected' : '' ?>>This Year</option>
          </select>
        </form>
          <button class="px-4 py-2 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
            Export Report
          </button>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white border rounded-xl p-4 flex items-center justify-between">    
          <div>
            <p>Total Sales (<?= ucfirst(str_replace('_', ' ', $period)) ?>)</p>
            <h4 class="text-2xl font-bold">₱<?= number_format($stats['total_sales'], 2); ?></h4>
            <p class="<?= $stats['growth_percent'] >= 0 ? 'text-xs text-green-600' : 'text-xs text-red-600' ?>">
              <?= $stats['growth_percent'] >= 0 ? '+' : '' ?><?= $stats['growth_percent']; ?>% from last period
            </p>
          </div>
          <div class="icon-placeholder"></div>
        </div>
        <div class="bg-white border rounded-xl p-4 flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500">Transactions</p>
            <h4 class="text-2xl font-bold"><?=$stats['transaction_count'] ?? 0; ?></h4>
            <p class="text-xs text-gray-500">completed sales</p>
          </div>
          <div class="icon-placeholder"></div>
        </div>
        <div class="bg-white border rounded-xl p-4 flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500">Avg. Transaction</p>
            <h4 class="text-2xl font-bold">₱<?= number_format($stats['avg_transaction'] ?? 0, 2); ?></h4>
            <p class="text-xs text-gray-500">per sale</p>
          </div>
          <div class="icon-placeholder"></div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <?php
        $maxSales = max(array_column($salesTrend, 'total_sales')) ?: 1;
        ?>

        <div class="bg-white border rounded-xl p-6">
          <h4 class="text-lg font-semibold mb-2">Daily Sales Trend</h4>
          <p class="text-sm text-gray-500 mb-4">Sales performance over the last 7 days</p>

          <div class="h-64 bg-gray-100 rounded-lg flex items-end justify-between space-x-1">
            <?php foreach ($salesTrend as $date => $data): ?>
              <?php
                $salesHeight = ($data['total_sales'] / $maxSales) * 100;
                $tooltip = date('M j', strtotime($date)) . " — ₱" . number_format($data['total_sales'], 2) . " (" . $data['transaction_count'] . " txn)";
              ?>
              <div class="flex-1 flex flex-col items-center justify-end group relative" style="height: 100%;">
                <!-- Tooltip on hover -->
                <div class="absolute bottom-full mb-2 hidden group-hover:flex items-center justify-center bg-white text-gray-700 text-xs font-medium px-2 py-1 rounded shadow-lg z-10 whitespace-nowrap">
                  <?= $tooltip ?>
                </div>
                <!-- Transaction dot -->
                <?php if ($data['transaction_count'] > 0): ?>
                  <div class="w-2 h-2 bg-green-500 rounded-full mb-1 shadow-sm"></div>
                <?php endif; ?>
                <!-- Sales bar -->
                <div 
                  class="bg-gradient-to-t from-blue-500 to-blue-400 hover:from-blue-600 hover:to-blue-500 rounded-t-md transition-all duration-300 shadow-md"
                  style="width: 60%; height: <?= $salesHeight ?>%;" 
                  title="<?= $tooltip ?>">
                </div>
              </div>
            <?php endforeach; ?>
          </div>
          <!-- Day labels below -->
          <div class="flex justify-between mt-2 text-xs text-gray-600 px-4 font-medium tracking-wide">
            <?php foreach (array_keys($salesTrend) as $date): ?>
              <div class="w-1/7 text-center"><?= date('D', strtotime($date)) ?></div>
            <?php endforeach; ?>
          </div>
          <!-- Legend -->
          <div class="flex justify-center items-center mt-4 text-xs text-gray-500">
            <div class="flex items-center mr-4">
              <span class="w-2 h-2 bg-blue-500 rounded-full mr-1"></span> Sales (₱)
            </div>
            <div class="flex items-center">
              <span class="w-2 h-2 bg-green-500 rounded-full mr-1"></span> Transactions
            </div>
          </div>
        </div>

        <?php
        $topSellingProducts = $database->getTopSellingProducts();
        $colors = ['#3b82f6', '#22c55e', '#ef4444', '#eab308', '#8b5cf6'];

        $gradientParts = [];
        $legendItems = [];
        $start = 0;

        foreach ($topSellingProducts as $index => $product) {
            $color = $colors[$index % count($colors)];
            $end = $start + $product['percentage'];

            $gradientParts[] = "$color {$start}% {$end}%";

            $legendItems[] = sprintf(
                '<span class="font-semibold" style="color: %s">%s <span class="text-gray-800">%.2f%%</span></span>',
                $color,
                $product['item_name'],
                $product['percentage']
            );

            $start = $end;
        }
        ?>
        <div class="bg-white border rounded-xl p-6">
          <h4 class="text-lg font-semibold mb-2">Top Selling Products</h4>
          <p class="text-sm text-gray-500 mb-4">Most popular items by quantity sold</p>

          <div class="flex items-center justify-center h-64">
              <div class="flex-shrink-0 w-40 h-40 relative flex items-center justify-center">
                  <div class="w-full h-full rounded-full"
                      style="background: conic-gradient(<?= implode(', ', $gradientParts) ?>);">
                  </div>
                  <div class="absolute inset-0 flex items-center justify-center">
                      <div class="w-24 h-24 bg-white rounded-full"></div>
                  </div>
              </div>

              <div class="flex flex-col text-xs space-y-2 ml-12">
                  <?php foreach ($legendItems as $legend): ?>
                      <div><?= $legend ?></div>
                  <?php endforeach; ?>
              </div>
          </div>
      </div>
      
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mt-8">
        <div class="bg-white border rounded-xl p-6 col-span-1 lg:col-span-2">
          <h4 class="text-lg font-semibold mb-2">Recent Transactions</h4>
          <p class="text-sm text-gray-500 mb-4">Latest sales processed today</p>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transaction ID</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Method</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <?php
                  // Count how many items (quantities) per sale_id
                  $sale_items_count = [];

                  foreach ($sale_items as $item) {
                      $sale_id = $item['sale_id'];

                      if (!isset($sale_items_count[$sale_id])) {
                          $sale_items_count[$sale_id] = 0;
                      }

                      $sale_items_count[$sale_id] += $item['quantity']; // Sum quantities
                  }
                  ?>
                <?php $sales = array_slice($sales, 0, 5); ?>
                <?php foreach($sales as $sale) : ?>
                <tr>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">#<?= $sale['transaction_id']; ?></td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= $sale['customer_name'] ?: "N/A" ; ?></td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">₱<?= number_format($sale['grand_total'], 2); ?></td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <?php 
                      $count = $sale_items_count[$sale['sale_id']] ?? 0;
                      echo $count . ' item' . ($count !== 1 ? 's' : '');
                    ?>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= $sale['payment_method'] ?: "N/A" ?></td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= date('g:i A', strtotime($sale['time'])); ?></td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= date('F j, Y', strtotime($sale['date'])); ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

          <?php $peakHours = $database->getPeakSalesHours(); ?>
          <div class="bg-white border rounded-xl p-6">
              <h4 class="text-lg font-semibold mb-2">Peak Hours</h4>
              <p class="text-sm text-gray-500 mb-4">Busiest times of the day</p>
              <ul class="space-y-4 text-sm">
                  <?php foreach ($peakHours as $hour): ?>
                      <li class="flex justify-between items-center">
                          <span><?= $hour['time_range'] ?></span>
                          <span class="bg-<?= $hour['color'] ?>-500 text-white text-xs px-2 py-0.5 rounded-full">
                              <?= $hour['level'] ?>
                          </span>
                      </li>
                  <?php endforeach; ?>
              </ul>
          </div>

          <div class="bg-white border rounded-xl p-6">
            <h4 class="text-lg font-semibold mb-2">Payment Methods</h4>
            <p class="text-sm text-gray-500 mb-4">Preferred payment types</p>
            <ul class="space-y-4 text-sm">
              <li class="flex justify-between items-center">
                <span>Cash</span>
                <span class="font-medium">65%</span>
              </li>
              <li class="flex justify-between items-center">
                <span>Credit Card</span>
                <span class="font-medium">30%</span>
              </li>
              <li class="flex justify-between items-center">
                <span>Mobile Payment</span>
                <span class="font-medium">5%</span>
              </li>
            </ul>
          </div>
        </div>
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

<!-- Statistic Button -->
  <script>
  document.getElementById('periodSelect').addEventListener('change', function() {
    document.getElementById('filterForm').submit();
  });
</script>

</body>
</html>