<?php
include "database/database.php";
$database->login_session();
$stats = $database->getTodaysSalesStats();
$items = $database->select_items();
$period = $_GET['period'] ?? 'week';
$chartData = $database->getSalesChartData($period);
$topItems = $database->getTopSellingItems(5);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>POS & Inventory - Dashboard</title>
  <script src="assets/apexcharts.js"></script>
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

    main h1,
    main h2,
    main h3,
    main h4 {
      font-family: 'DM Sans', sans-serif;
      font-weight: 700;
    }

    .stat-card {
      background: var(--surface2);
      border: 1.5px solid var(--border);
      border-radius: 16px;
      padding: 20px;
      transition: border-color .2s, box-shadow .2s;
    }

    .stat-card:hover {
      border-color: var(--accent);
      box-shadow: 0 8px 24px rgba(0, 0, 0, .3);
    }

    .stat-icon {
      width: 40px;
      height: 40px;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }

    .chart-card {
      background: var(--surface);
      border: 1.5px solid var(--border);
      border-radius: 16px;
      padding: 20px;
    }

    .chart-card h4 {
      font-size: 14px;
      font-weight: 600;
      color: var(--text);
      margin-bottom: 4px;
    }

    .chart-card p {
      font-size: 12px;
      color: var(--text-muted);
      margin-bottom: 16px;
    }

    .period-select {
      background: var(--surface);
      border: 1.5px solid var(--border);
      border-radius: 10px;
      padding: 8px 14px;
      color: var(--text);
      font-family: 'DM Sans', sans-serif;
      font-size: 13px;
      outline: none;
      cursor: pointer;
      transition: border-color .2s;
    }

    .period-select:focus {
      border-color: var(--accent);
    }

    .period-select option {
      background: var(--surface2);
      color: var(--text);
    }

    .list-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 10px 0;
      border-bottom: 1px solid var(--border);
      font-size: 13px;
    }

    .list-item:last-child {
      border-bottom: none;
    }

    .list-item .item-name {
      color: var(--text);
      font-weight: 500;
    }

    .list-item .item-meta {
      font-size: 11px;
      color: var(--text-muted);
      font-weight: 600;
    }

    .list-item .item-badge {
      background: rgba(245, 166, 35, .12);
      color: var(--accent);
      font-size: 11px;
      font-weight: 700;
      padding: 2px 8px;
      border-radius: 20px;
    }
  </style>
</head>

<body>

  <button class="mobile-toggle" id="sidebar-toggle">☰</button>

  <?php include "sidebar.php"; ?>

  <main class="flex-1 p-6" style="margin-left:240px;">

    <!-- Header -->
    <div style="margin-bottom:24px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
      <div>
        <h3 style="margin-top:2px;">
          <?= date('l, F j, Y') ?>
        </h3>
      </div>
      <select onchange="changePeriod(this.value)" class="period-select">
        <option value="week" <?= $period === 'week'  ? 'selected' : '' ?>>Last 7 Days</option>
        <option value="month" <?= $period === 'month' ? 'selected' : '' ?>>Last 30 Days</option>
      </select>
    </div>

    <!-- Stat Cards -->
    <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:16px; margin-bottom:28px;">

      <div class="stat-card">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:16px;">
          <span style="font-size:12px; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px;">Today's Sales</span>
          <div class="stat-icon" style="background:rgba(99,102,241,.15);">
            <svg width="18" height="18" fill="none" stroke="#6366f1" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-1.5 6M17 13l1.5 6M9 19h6" />
            </svg>
          </div>
        </div>
        <div style="font-size:36px; font-weight:800; color:var(--text);">
          <?= $stats['transaction_count'] ?>
        </div>
      </div>

      <div class="stat-card">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:16px;">
          <span style="font-size:12px; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px;">Revenue</span>
          <div class="stat-icon" style="background:rgba(245,166,35,.12);">
            <svg width="18" height="18" fill="none" stroke="var(--accent)" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
        </div>
        <div style="font-size:28px; font-weight:800; color:var(--accent);">
          ₱<?= number_format($stats['total_sales'], 2) ?>
        </div>
      </div>

      <div class="stat-card">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:16px;">
          <span style="font-size:12px; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px;">Catalog</span>
          <div class="stat-icon" style="background:rgba(67,211,146,.1);">
            <svg width="18" height="18" fill="none" stroke="var(--success)" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
            </svg>
          </div>
        </div>
        <div style="font-size:36px; font-weight:800; color:var(--text);">
          <?= count($items) ?>
        </div>
      </div>

    </div>

    <!-- Charts Row -->
    <?php if ($period === 'month'): ?>
      <div style="display:grid; grid-template-columns:1fr; gap:16px; margin-bottom:28px;">
        <div class="chart-card">
          <h4>Revenue</h4>
          <p>Last 30 Days</p>
          <div id="salesChart"></div>
        </div>
      </div>
      <div style="display:grid; grid-template-columns:1fr; gap:16px; margin-bottom:28px;">
        <div class="chart-card">
          <h4>Transactions</h4>
          <p>Last 30 Days</p>
          <div id="transactionsChart"></div>
        </div>
      </div>
    <?php else: ?>
      <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:28px;">
        <div class="chart-card">
          <h4>Revenue</h4>
          <p>Last 7 Days</p>
          <div id="salesChart"></div>
        </div>
        <div class="chart-card">
          <h4>Transactions</h4>
          <p>Last 7 Days</p>
          <div id="transactionsChart"></div>
        </div>
      </div>
    <?php endif; ?>

    <!-- Bottom Row -->
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">

      <!-- Top Selling -->
      <div class="chart-card">
        <h4>Top-Selling Items</h4>
        <p>Based on all-time sales</p>
        <div id="topSellingChart"></div>
        <div style="margin-top:12px;">
          <?php foreach ($topItems as $item): ?>
            <div class="list-item">
              <span class="item-name"><?= htmlspecialchars($item['item_name']) ?></span>
              <span class="item-badge"><?= $item['total_sold'] ?> sold</span>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Recently Added -->
      <?php
      usort($items, fn($a, $b) => $b['created_at'] <=> $a['created_at']);
      $items = array_slice($items, 0, 5);
      ?>
      <div class="chart-card">
        <h4>Recently Added Items</h4>
        <p>Newly added to inventory</p>
        <div id="recentItemsChart"></div>
        <div style="margin-top:12px;">
          <?php foreach ($items as $item): ?>
            <div class="list-item">
              <span class="item-name"><?= htmlspecialchars($item['item_name']) ?></span>
              <span class="item-meta"><?= date('M j, Y', strtotime($item['created_at'])) ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

    </div>

  </main>

  <script>
    function changePeriod(value) {
      window.location.href = "?period=" + value;
    }

    // Sidebar toggle
    const sidebar = document.getElementById('mobile-sidebar');
    const toggleBtn = document.getElementById('sidebar-toggle');
    const closeBtn = document.getElementById('sidebar-close');
    if (toggleBtn && sidebar) toggleBtn.addEventListener('click', () => sidebar.classList.toggle('-translate-x-full'));
    if (closeBtn && sidebar) closeBtn.addEventListener('click', () => sidebar.classList.add('-translate-x-full'));

    // Sync main margin with sidebar collapse
    const mainEl = document.querySelector('main');
    const collapseBtn = document.getElementById('sidebarCollapseBtn');
    const mainSidebar = document.getElementById('main-sidebar');

    function syncMargin() {
      const collapsed = mainSidebar && mainSidebar.classList.contains('collapsed');
      if (mainEl) mainEl.style.marginLeft = collapsed ? '64px' : '240px';
    }

    if (collapseBtn) collapseBtn.addEventListener('click', () => setTimeout(syncMargin, 50));
    syncMargin();
  </script>

  <script>
    var chartLabels = <?= json_encode($chartData['labels']) ?>;
    var revenueData = <?= json_encode($chartData['revenues']) ?>;
    var transactionData = <?= json_encode($chartData['transactions']) ?>;

    const darkTheme = {
      chart: {
        background: 'transparent',
        foreColor: '#7b82a0',
        toolbar: {
          show: false
        }
      },
      grid: {
        borderColor: '#2e3347',
        strokeDashArray: 4
      },
      tooltip: {
        theme: 'dark'
      }
    };

    // Revenue chart
    new ApexCharts(document.querySelector("#salesChart"), {
      ...darkTheme,
      chart: {
        ...darkTheme.chart,
        type: 'area',
        height: 260
      },
      series: [{
        name: 'Revenue',
        data: revenueData
      }],
      xaxis: {
        categories: chartLabels,
        labels: {
          style: {
            colors: '#7b82a0'
          }
        }
      },
      colors: ['#f5a623'],
      stroke: {
        curve: 'smooth',
        width: 2.5
      },
      dataLabels: {
        enabled: false
      },
      fill: {
        type: 'gradient',
        gradient: {
          opacityFrom: 0.35,
          opacityTo: 0.05
        }
      },
      yaxis: {
        labels: {
          style: {
            colors: '#7b82a0'
          },
          formatter: val => "₱" + val.toLocaleString()
        }
      },
      tooltip: {
        theme: 'dark',
        y: {
          formatter: val => "₱" + val.toLocaleString()
        }
      },
      grid: darkTheme.grid
    }).render();

    // Transactions chart
    new ApexCharts(document.querySelector("#transactionsChart"), {
      ...darkTheme,
      chart: {
        ...darkTheme.chart,
        type: 'bar',
        height: 260
      },
      series: [{
        name: 'Transactions',
        data: transactionData
      }],
      xaxis: {
        categories: chartLabels,
        labels: {
          style: {
            colors: '#7b82a0'
          }
        }
      },
      colors: ['#6366f1'],
      plotOptions: {
        bar: {
          borderRadius: 6,
          columnWidth: '50%'
        }
      },
      dataLabels: {
        enabled: false
      },
      grid: darkTheme.grid
    }).render();

    // Top selling chart
    var topSellingNames = <?= json_encode(array_column($topItems, 'item_name')) ?>;
    var topSellingSold = <?= json_encode(array_map('intval', array_column($topItems, 'total_sold'))) ?>;

    new ApexCharts(document.querySelector("#topSellingChart"), {
      ...darkTheme,
      chart: {
        ...darkTheme.chart,
        type: 'bar',
        height: 200
      },
      series: [{
        name: 'Units Sold',
        data: topSellingSold
      }],
      colors: ['#f5a623'],
      plotOptions: {
        bar: {
          borderRadius: 5,
          columnWidth: '50%'
        }
      },
      dataLabels: {
        enabled: false
      },
      grid: darkTheme.grid
    }).render();

    // Recently added chart
    var recentNames = <?= json_encode(array_column($items, 'item_name')) ?>;
    var recentDays = <?= json_encode(array_map(function ($item) {
                        return (int)((time() - strtotime($item['created_at'])) / 86400);
                      }, $items)) ?>;

    new ApexCharts(document.querySelector("#recentItemsChart"), {
      ...darkTheme,
      chart: {
        ...darkTheme.chart,
        type: 'bar',
        height: 200
      },
      series: [{
        name: 'Days Ago Added',
        data: recentDays
      }],
      xaxis: {
        categories: recentNames,
        labels: {
          style: {
            colors: '#7b82a0',
            fontSize: '11px'
          }
        }
      },
      colors: ['#43d392'],
      plotOptions: {
        bar: {
          borderRadius: 5,
          horizontal: true
        }
      },
      dataLabels: {
        enabled: false
      },
      grid: darkTheme.grid,
      tooltip: {
        theme: 'dark',
        y: {
          formatter: val => val + ' days ago'
        }
      }
    }).render();
  </script>

</body>

</html>