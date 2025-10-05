<?php
  include "database/database.php";
  $database->login_session();

  $monthlyData = $database->getMonthlyOrdersSalesProfits();
  $months = array_column($monthlyData, 'month');
  $orders = array_column($monthlyData, 'orders');
  $sales = array_column($monthlyData, 'sales');
  $profits = array_column($monthlyData, 'profit');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>POS & Inventory - Inventory Reports</title>
  <link rel="stylesheet" href="assets/tailwind.min.css">
  <script src="assets/chart.min.js"></script>
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <style>
    .status-label {
      padding: 2px 8px;
      font-size: 12px;
      font-weight: 600;
      border-radius: 9999px;
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
      <h2 class="text-2xl font-bold text-gray-800">Inventory Reports</h2>
      <p class="text-sm text-gray-500">Analyze inventory performance, valuation, and trends</p>
    </header>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

      <div class="bg-white border rounded-xl p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h4 class="text-lg font-semibold">Orders, Sales & Profits</h4>
                <p class="text-sm text-gray-500">Monthly performance over time</p>
            </div>
        </div>
        <div class="h-64">
            <canvas id="ordersSalesProfitChart"></canvas>
        </div>
    </div>

    <?php
$topProducts = $database->getTopProductsByValue(5);  // Replace $yourObject with your class instance
?>

<div class="bg-white border rounded-xl p-6">
    <h4 class="text-lg font-semibold mb-4">Top Products by Value</h4>
    <p class="text-sm text-gray-500 mb-4">Highest value inventory items</p>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Value</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Margin</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($topProducts as $product): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium"><?= htmlspecialchars($product['item_name']) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= number_format($product['total_qty']) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">₱<?= number_format($product['total_value'], 2) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= $product['margin_percentage'] ?>%</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>


    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
      
      <div class="bg-white border rounded-xl p-6">
        <h4 class="text-lg font-semibold mb-4">Reorder Alert List</h4>
        <p class="text-sm text-gray-500 mb-4">Items requiring immediate attention</p>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Min</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priority</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">USB Cable - Type C</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">5</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">20</td>
                <td class="px-6 py-4 whitespace-nowrap"><span class="status-label bg-red-100 text-red-800">Critical</span></td>
              </tr>
              <tr>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">Bluetooth Headphones</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">0</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">5</td>
                <td class="px-6 py-4 whitespace-nowrap"><span class="status-label bg-red-100 text-red-800">Critical</span></td>
              </tr>
              <tr>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">Mechanical Keyboard</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">3</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">5</td>
                <td class="px-6 py-4 whitespace-nowrap"><span class="status-label bg-orange-100 text-orange-800">Low</span></td>
              </tr>
              <tr>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">HDMI Cable</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">8</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">15</td>
                <td class="px-6 py-4 whitespace-nowrap"><span class="status-label bg-orange-100 text-orange-800">Low</span></td>
              </tr>
              <tr>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">Wireless Charger</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">2</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">8</td>
                <td class="px-6 py-4 whitespace-nowrap"><span class="status-label bg-orange-100 text-orange-800">Low</span></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

       <div class="bg-white border rounded-xl p-6">
        <h4 class="text-lg font-semibold mb-4">Inventory Insights</h4>
        <p class="text-sm text-gray-500 mb-4">Key recommendations</p>
        <div class="space-y-4">
          <div class="bg-gray-50 border rounded-lg p-4">
            <h5 class="font-medium text-sm">Critical Stock Alert</h5>
            <p class="text-xs text-gray-500">3 items are out of stock and 8 items are below minimum levels.</p>
          </div>
          <div class="bg-gray-50 border rounded-lg p-4">
            <h5 class="font-medium text-sm">Best Performing Category</h5>
            <p class="text-xs text-gray-500">Peripherals lead with $18500.00 in inventory value.</p>
          </div>
          <div class="bg-gray-50 border rounded-lg p-4">
            <h5 class="font-medium text-sm">Supplier Recommendation</h5>
            <p class="text-xs text-gray-500">ElectroSupply has the best delivery performance at 87.5% on-time rate.</p>
          </div>
          <div class="bg-gray-50 border rounded-lg p-4">
            <h5 class="font-medium text-sm">Inventory Turnover</h5>
            <p class="text-xs text-gray-500">Current turnover rate of 4.2x/year indicates healthy stock movement.</p>
          </div>
        </div>
      </div>

    </div>

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

<!-- Line Chart Script -->
 <script>
const ctx = document.getElementById('ordersSalesProfitChart').getContext('2d');

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode($months) ?>, // e.g. ["2025-01", "2025-02", ...]
        datasets: [
            {
                label: 'Orders',
                data: <?= json_encode($orders) ?>,
                backgroundColor: 'rgba(59, 130, 246, 0.7)', // blue
            },
            {
                label: 'Sales',
                data: <?= json_encode($sales) ?>,
                backgroundColor: 'rgba(34, 197, 94, 0.7)', // green
            },
            {
                label: 'Profit',
                data: <?= json_encode($profits) ?>,
                backgroundColor: 'rgba(234, 88, 12, 0.7)', // orange
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            x: {
                stacked: false,
                ticks: {
                    callback: function(value) {
                        const label = this.getLabelForValue(value);
                        const month = label.split('-')[1];
                        const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                        return monthNames[parseInt(month) - 1] || label;
                    }
                }
            },
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '₱' + value.toLocaleString();
                    }
                }
            }
        },
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(context) {
                        let value = context.parsed.y;
                        return context.dataset.label + ': ₱' + value.toLocaleString();
                    }
                }
            }
        }
    }
});
</script>
</body>
</html>