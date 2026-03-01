<?php
include "database/database.php";
$database->login_session();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';
  switch ($action) {
    case 'add_to_cart':
      $database->add_to_cart();
      break;
    case 'process_sale':
      $database->process_sale();
      break;
    case 'remove_item':
      $database->remove_from_cart();
      break;
    case 'decrease_qty':
      $database->decrease_cart_quantity();
      break;
    case 'save_as_pc_build':
      $database->saveCartAsPcBuild();
      break;
    case 'increase_qty':
      $database->increase_cart_quantity();
      break;
    case 'set_custom_price':
      $database->set_custom_price();
      break;
    case 'remove_all':
      unset($_SESSION['cart']);
      $_SESSION['sale-success'] = "All items removed from cart.";
      break;
    default:
      break;
  }
  if (!isset($_SESSION['sale-duplicate'])) {
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
  }
}
$perPage = 12;
$search  = trim($_GET['search'] ?? '');
$page    = max(1, (int) ($_GET['page'] ?? 1));
$offset  = ($page - 1) * $perPage;

$totalItems = $database->getTotalItemsCountPOS($search);
$totalPages = max(1, ceil($totalItems / $perPage));

$items = $database->select_items_paginated_POS($offset, $perPage, $search);
$cart = $_SESSION['cart'] ?? [];
$subtotal = 0;
foreach ($cart as $item) {
  $subtotal += $item['line_total'];
}
$grand_total = $subtotal;

//PC Builder Paginate
$pcPerPage = 8;
$pcSearch  = trim($_GET['pc_search'] ?? '');
$pcPage    = max(1, (int) ($_GET['pc_page'] ?? 1));
$pcOffset  = ($pcPage - 1) * $pcPerPage;

$totalPcBuilders = $database->getTotalPcBuildersCountPOS($pcSearch);
$totalPcPages    = max(1, ceil($totalPcBuilders / $pcPerPage));
$pcBuilders      = $database->getPcBuildersPOSPaginated($pcOffset, $pcPerPage, $pcSearch);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>POS — Process Sales</title>
  <link rel="stylesheet" href="assets/tailwind.min.css">
  <link href="assets/fonts.css" rel="stylesheet">
  <style>
    :root {
      --bg: #0f1117;
      --surface: #1a1d27;
      --surface2: #22263a;
      --border: #2e3347;
      --accent: #f5a623;
      --accent2: #ff6b6b;
      --accent3: #4ecdc4;
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

    .main h1,
    .main h2,
    .main h3,
    .main h4 {
      font-family: 'Syne', sans-serif;
    }

    body {
      background: var(--bg);
    }

    /* Main content */
    .main {
      margin-left: 240px;
      font-family: 'DM Sans', sans-serif;
      flex: 1;
      display: flex;
      flex-direction: column;
      min-height: 100vh;
      background: var(--bg);
      color: var(--text);
    }

    /* ─── TOP BAR ─── */
    .topbar {
      padding: 18px 32px;
      border-bottom: 1px solid var(--border);
      background: var(--surface);
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 16px;
    }

    .topbar-title h2 {
      font-size: 16px;
      font-weight: 600;
      letter-spacing: -0.5px;
    }

    .search-wrap {
      flex: 1;
      max-width: 460px;
    }

    .search-input {
      width: 100%;
      background: var(--bg);
      border: 1.5px solid var(--border);
      border-radius: 12px;
      padding: 11px 18px 11px 44px;
      color: var(--text);
      font-family: 'DM Sans', sans-serif;
      font-size: 14px;
      outline: none;
      transition: border-color .2s, box-shadow .2s;
      position: relative;
    }

    .search-input:focus {
      border-color: var(--accent);
      box-shadow: 0 0 0 3px rgba(245, 166, 35, .12);
    }

    .search-wrap-inner {
      position: relative;
    }

    .search-icon {
      position: absolute;
      left: 14px;
      top: 50%;
      transform: translateY(-50%);
      color: var(--text-muted);
      pointer-events: none;
    }

    /* ─── LAYOUT ─── */
    .pos-layout {
      display: grid;
      grid-template-columns: 1fr 380px;
      gap: 0;
      flex: 1;
      min-height: 0;
    }

    /* ─── LEFT PANEL ─── */
    .products-panel {
      padding: 24px 28px;
      overflow-y: auto;
      border-right: 1px solid var(--border);
    }

    .section-label {
      font-size: 11px;
      font-weight: 700;
      letter-spacing: 1.5px;
      text-transform: uppercase;
      color: var(--text-muted);
      margin-bottom: 16px;
    }

    /* Alert */
    .alert {
      padding: 12px 16px;
      border-radius: 10px;
      font-size: 13px;
      margin-bottom: 16px;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .alert-success {
      background: rgba(67, 211, 146, .1);
      border: 1px solid rgba(67, 211, 146, .3);
      color: var(--success);
    }

    .alert-error {
      background: rgba(255, 92, 92, .1);
      border: 1px solid rgba(255, 92, 92, .3);
      color: var(--danger);
    }

    /* Product grid */
    .product-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
      gap: 12px;
    }

    .product-card {
      background: var(--surface);
      border: 1.5px solid var(--border);
      border-radius: 14px;
      padding: 16px;
      cursor: pointer;
      transition: border-color .2s, transform .15s, box-shadow .2s;
      text-align: left;
      width: 100%;
      position: relative;
      overflow: hidden;
    }

    .product-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 3px;
      background: var(--accent);
      transform: scaleX(0);
      transform-origin: left;
      transition: transform .2s;
    }

    .product-card:hover::before {
      transform: scaleX(1);
    }

    .product-card:hover {
      border-color: var(--accent);
      transform: translateY(-2px);
      box-shadow: 0 8px 24px rgba(0, 0, 0, .35);
    }

    .product-card.out-of-stock {
      opacity: 0.4;
      cursor: not-allowed;
      pointer-events: none;
    }

    .product-card .pname {
      font-weight: 600;
      font-size: 13px;
      line-height: 1.4;
      margin-bottom: 6px;
      color: var(--text);
    }

    .product-card .pprice {
      font-family: 'Syne', sans-serif;
      font-size: 16px;
      font-weight: 700;
      color: var(--accent);
      margin-bottom: 4px;
    }

    .product-card .pbarcode {
      font-size: 10px;
      color: var(--text-muted);
      margin-bottom: 8px;
    }

    .stock-badge {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      font-size: 10px;
      font-weight: 600;
      padding: 3px 8px;
      border-radius: 20px;
    }

    .stock-ok {
      background: rgba(67, 211, 146, .12);
      color: var(--success);
    }

    .stock-low {
      background: rgba(245, 166, 35, .12);
      color: var(--accent);
    }

    .stock-out {
      background: rgba(255, 92, 92, .12);
      color: var(--danger);
    }

    /* ─── RIGHT PANEL (Cart) ─── */
    .cart-panel {
      display: flex;
      flex-direction: column;
      background: var(--surface);
    }

    .cart-header {
      padding: 20px 24px 16px;
      border-bottom: 1px solid var(--border);
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .cart-header h3 {
      font-size: 16px;
      font-weight: 600;
    }

    .cart-count {
      background: var(--accent);
      color: #111;
      border-radius: 20px;
      font-size: 11px;
      font-weight: 600;
      padding: 2px 10px;
    }

    .btn-clear {
      background: none;
      border: 1.5px solid rgba(255, 92, 92, .35);
      color: var(--danger);
      font-size: 12px;
      font-weight: 600;
      border-radius: 8px;
      padding: 5px 12px;
      cursor: pointer;
      font-family: 'DM Sans', sans-serif;
      transition: background .2s, border-color .2s;
    }

    .btn-clear:hover {
      background: rgba(255, 92, 92, .1);
      border-color: var(--danger);
    }

    /* Cart items list */
    .cart-items {
      flex: 1;
      overflow-y: auto;
      padding: 12px 0;
    }

    .cart-empty {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      height: 100%;
      color: var(--text-muted);
      gap: 12px;
      padding: 40px;
    }

    .cart-empty .empty-icon {
      font-size: 48px;
      opacity: 0.3;
    }

    .cart-empty p {
      font-size: 14px;
    }

    .cart-item {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 12px 24px;
      border-bottom: 1px solid var(--border);
      transition: background .15s;
    }

    .cart-item:hover {
      background: rgba(255, 255, 255, .02);
    }

    .cart-item-num {
      width: 26px;
      height: 26px;
      background: var(--surface2);
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 11px;
      font-weight: 700;
      color: var(--text-muted);
      flex-shrink: 0;
    }

    .cart-item-info {
      flex: 1;
      min-width: 0;
    }

    .cart-item-info .iname {
      font-size: 13px;
      font-weight: 600;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .cart-item-info .idetail {
      font-size: 11px;
      color: var(--text-muted);
      margin-top: 2px;
    }

    .cart-item-total {
      font-family: 'Syne', sans-serif;
      font-size: 14px;
      font-weight: 700;
      color: var(--accent);
      white-space: nowrap;
    }

    .btn-remove-item {
      background: none;
      border: none;
      cursor: pointer;
      color: var(--text-muted);
      padding: 4px;
      border-radius: 6px;
      transition: color .2s, background .2s;
      display: flex;
      align-items: center;
    }

    .btn-remove-item:hover {
      color: var(--danger);
      background: rgba(255, 92, 92, .1);
    }

    /* ─── ORDER SUMMARY ─── */
    .order-summary {
      border-top: 1px solid var(--border);
      padding: 20px 24px;
      background: var(--surface2);
    }

    .summary-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-size: 13px;
      color: var(--text-muted);
      margin-bottom: 8px;
    }

    .summary-row.total {
      font-family: 'Syne', sans-serif;
      font-size: 22px;
      font-weight: 600;
      color: var(--text);
      border-top: 1px solid var(--border);
      padding-top: 12px;
      margin-top: 8px;
      margin-bottom: 16px;
    }

    .summary-row.total .total-amount {
      color: var(--accent);
    }

    /* Payment section */
    .payment-section {
      margin-bottom: 16px;
    }

    .payment-label {
      font-size: 11px;
      font-weight: 700;
      letter-spacing: 1.2px;
      text-transform: uppercase;
      color: var(--text-muted);
      margin-bottom: 10px;
    }

    .payment-methods {
      display: flex;
      gap: 8px;
    }

    .payment-option {
      flex: 1;
      background: var(--surface);
      border: 1.5px solid var(--border);
      border-radius: 10px;
      padding: 10px 8px;
      cursor: pointer;
      transition: border-color .2s, background .2s;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 5px;
    }

    .payment-option input[type="radio"] {
      display: none;
    }

    .payment-option:has(input:checked),
    .payment-option.selected {
      border-color: var(--accent);
      background: rgba(245, 166, 35, .08);
    }

    .payment-option .pay-icon {
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--text-muted);
      transition: color .2s;
    }

    .payment-option:has(input:checked) .pay-icon,
    .payment-option.selected .pay-icon {
      color: var(--accent);
    }

    .payment-option .pay-label {
      font-size: 11px;
      font-weight: 600;
      color: var(--text);
    }

    /* Inputs row */
    .inputs-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 10px;
      margin-bottom: 14px;
    }

    .input-group label {
      font-size: 11px;
      color: var(--text-muted);
      display: block;
      margin-bottom: 5px;
      font-weight: 500;
    }

    .pos-input {
      width: 100%;
      background: var(--surface);
      border: 1.5px solid var(--border);
      border-radius: 10px;
      padding: 10px 14px;
      color: var(--text);
      font-family: 'DM Sans', sans-serif;
      font-size: 14px;
      outline: none;
      transition: border-color .2s;
    }

    .pos-input:focus {
      border-color: var(--accent);
    }

    .qty-input,
    #cashReceivedInput {
      -moz-appearance: textfield;
    }

    .qty-input::-webkit-outer-spin-button,
    .qty-input::-webkit-inner-spin-button,
    #cashReceivedInput::-webkit-outer-spin-button,
    #cashReceivedInput::-webkit-inner-spin-button {
      -webkit-appearance: none;
    }

    /* Change display */
    .change-display {
      background: rgba(67, 211, 146, .08);
      border: 1.5px solid rgba(67, 211, 146, .2);
      border-radius: 10px;
      padding: 10px 14px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 16px;
    }

    .change-display .change-label {
      font-size: 12px;
      color: var(--text-muted);
    }

    .change-display .change-amount {
      font-family: 'Syne', sans-serif;
      font-size: 18px;
      font-weight: 600;
      color: var(--success);
    }

    /* Process button */
    .btn-process {
      width: 100%;
      background: var(--accent);
      color: #111;
      border: none;
      border-radius: 12px;
      padding: 15px;
      font-family: 'Syne', sans-serif;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      letter-spacing: 0.3px;
      transition: opacity .2s, transform .1s, box-shadow .2s;
      box-shadow: 0 4px 20px rgba(245, 166, 35, .3);
    }

    .btn-process:hover {
      opacity: 0.9;
      transform: translateY(-1px);
      box-shadow: 0 6px 28px rgba(245, 166, 35, .4);
    }

    .btn-process:active {
      transform: translateY(0);
    }

    /* ─── MODALS ─── */
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

    .modal-box h3 {
      font-size: 18px;
      font-weight: 600;
      margin-bottom: 8px;
    }

    .modal-box p {
      font-size: 14px;
      color: var(--text-muted);
      line-height: 1.6;
      margin-bottom: 20px;
    }

    .modal-actions {
      display: flex;
      gap: 10px;
      justify-content: flex-end;
    }

    .modal-box .form-control {
      width: 100%;
      padding: 10px 14px;
      font-family: 'DM Sans', sans-serif;
      font-size: 14px;
      color: var(--text);
      background: var(--surface2);
      border: 1.5px solid var(--border);
      border-radius: 10px;
      outline: none;
      transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
    }

    .modal-box .form-control:focus {
      border-color: var(--accent);
      box-shadow: 0 0 0 3px rgba(245, 166, 35, 0.12);
      background: var(--surface);
    }

    /* Modal label style */
    .modal-box label {
      display: block;
      margin-bottom: 6px;
      font-size: 12px;
      font-weight: 600;
      color: var(--text-muted);
    }

    .btn-cancel {
      background: var(--surface2);
      border: 1.5px solid var(--border);
      color: var(--text-muted);
      border-radius: 10px;
      padding: 10px 20px;
      font-family: 'DM Sans', sans-serif;
      font-size: 14px;
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
      padding: 10px 20px;
      font-family: 'DM Sans', sans-serif;
      font-size: 14px;
      font-weight: 700;
      cursor: pointer;
      transition: opacity .2s;
    }

    .btn-confirm-danger:hover {
      opacity: .85;
    }

    .btn-confirm-ok {
      background: var(--accent);
      border: none;
      color: #111;
      border-radius: 10px;
      padding: 10px 24px;
      font-family: 'Syne', sans-serif;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      transition: opacity .2s;
    }

    .btn-confirm-ok:hover {
      opacity: .85;
    }

    .btn-confirm-save {
      background: var(--accent);
      border: none;
      color: #111;
      border-radius: 10px;
      padding: 10px 24px;
      font-family: 'Syne', sans-serif;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      transition: opacity .2s;
    }

    .btn-confirm-save:hover {
      opacity: .85;
    }

    /* Quantity modal specific */
    .qty-controls {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 20px;
    }

    .qty-btn {
      width: 40px;
      height: 40px;
      background: var(--surface2);
      border: 1.5px solid var(--border);
      border-radius: 10px;
      color: var(--text);
      font-size: 20px;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: border-color .2s, background .2s;
      flex-shrink: 0;
    }

    .qty-btn:hover {
      border-color: var(--accent);
      background: rgba(245, 166, 35, .08);
      color: var(--accent);
    }

    .qty-input {
      flex: 1;
      background: var(--bg);
      border: 1.5px solid var(--border);
      border-radius: 10px;
      padding: 10px 14px;
      color: var(--text);
      font-family: 'Syne', sans-serif;
      font-size: 22px;
      font-weight: 700;
      outline: none;
      text-align: center;
      transition: border-color .2s;
    }

    .qty-input:focus {
      border-color: var(--accent);
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
      transition: border-color .2s, background .2s;
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
    }

    .modal-box {
      background: var(--surface);
      border: 1.5px solid var(--border);
      width: 560px;
      max-width: 92%;
      border-radius: 18px;
      padding: 32px 28px;
      box-shadow: 0 25px 60px rgba(0, 0, 0, 0.18);
      font-family: 'Segoe UI', sans-serif;
    }

    .modal-product {
      display: flex;
      gap: 24px;
      align-items: flex-start;
    }

    .product-image {
      width: 220px;
      height: 220px;
      border-radius: 16px;
      overflow: hidden;
      background: #ffffff;
      flex-shrink: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
    }

    .product-image img {
      width: 100%;
      height: 100%;
      object-fit: contain;
      padding: 12px;
    }

    .product-info {
      flex: 1;
    }

    .product-info h3 {
      font-size: 22px;
      font-weight: 600;
      margin: 0;
    }

    .product-description {
      margin-top: 12px;
      font-size: 15px;
      color: #555;
      line-height: 1.6;
      max-height: 160px;
      overflow-y: auto;
    }

    .lightbox-overlay {
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.85);
      display: none;
      justify-content: center;
      align-items: center;
      z-index: 2000;
    }

    .lightbox-overlay.active {
      display: flex;
    }

    .lightbox-overlay img {
      max-width: 90%;
      max-height: 90%;
      object-fit: contain;
      border-radius: 12px;
      box-shadow: 0 30px 80px rgba(0, 0, 0, 0.6);
      animation: zoomIn 0.25s ease;
    }

    .lightbox-close {
      position: absolute;
      top: 25px;
      right: 35px;
      font-size: 28px;
      color: white;
      cursor: pointer;
      font-weight: bold;
    }

    @keyframes zoomIn {
      from {
        transform: scale(0.9);
        opacity: 0;
      }

      to {
        transform: scale(1);
        opacity: 1;
      }
    }

    .btn-save-quotation {
      background: var(--accent);
      color: #111;
      font-family: 'Syne', sans-serif;
      font-weight: 600;
      font-size: 12px;
      padding: 6px 14px;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      transition: opacity 0.2s, transform 0.1s, box-shadow 0.2s;
      box-shadow: 0 3px 12px rgba(245, 166, 35, 0.25);
      white-space: nowrap;
    }

    .btn-save-quotation:hover {
      opacity: 0.95;
      box-shadow: 0 4px 16px rgba(245, 166, 35, 0.35);
    }
  </style>
</head>

<body>

  <button class="mobile-toggle" id="sidebar-toggle">☰</button>

  <?php include "sidebar.php"; ?>

  <div class="main">

    <!-- TOP BAR -->
    <div class="topbar">
      <div class="topbar-title">
        <h2>Tap a product to add it to the cart</h2>
      </div>
      <div class="search-wrap">
        <div class="search-wrap-inner">
          <svg class="search-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <circle cx="11" cy="11" r="8" />
            <path d="m21 21-4.35-4.35" />
          </svg>
          <form method="GET" action="" id="productSearchForm">
            <?php if ($pcSearch !== ''): ?>
              <input type="hidden" name="pc_search" value="<?= htmlspecialchars($pcSearch) ?>">
            <?php endif; ?>
            <?php if ($pcPage > 1): ?>
              <input type="hidden" name="pc_page" value="<?= $pcPage ?>">
            <?php endif; ?>
            <div class="search-wrap">
              <div class="search-wrap-inner">
                <svg class="search-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <circle cx="11" cy="11" r="8" />
                  <path d="m21 21-4.35-4.35" />
                </svg>
                <input
                  type="text"
                  name="search"
                  id="productSearchInput"
                  class="search-input"
                  placeholder="Search product or scan barcode…"
                  value="<?= htmlspecialchars($search) ?>">
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- POS LAYOUT -->
    <div class="pos-layout">

      <!-- LEFT: Products -->
      <div class="products-panel">
        <div class="section-label">Quick Add Products</div>

        <?php if (isset($_SESSION['sale-success'])): ?>
          <div id="successAlert" class="alert alert-success">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <?= $_SESSION['sale-success'];
            unset($_SESSION['sale-success']); ?>
          </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['sale-error'])): ?>
          <div id="errorAlert" class="alert alert-error">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <circle cx="12" cy="12" r="10" />
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01" />
            </svg>
            <?= $_SESSION['sale-error'];
            unset($_SESSION['sale-error']); ?>
          </div>
        <?php endif; ?>


        <div class="product-grid">
          <?php if (empty($items)): ?>
            <p style="font-size:16px;" class="text-red-500">No products found.</p>
          <?php else: ?>
            <?php foreach ($items as $item): ?>
              <?php if ($item['quantity'] < 0) continue; ?>

              <?php
              $jsItemName = addslashes($item['item_name']);
              $jsDescription = addslashes($item['description'] ?? '');
              $jsImage = addslashes($item['image'] ?? '');
              ?>

              <button type="button"
                class="product-card <?= $item['quantity'] <= 0 ? 'out-of-stock' : '' ?> product-item"
                <?= $item['quantity'] > 0
                  ? "onclick=\"openQuantityModal(
            {$item['item_id']},
            '{$jsItemName}',
            '{$jsDescription}',
            '{$jsImage}',
            {$item['quantity']}
          )\""
                  : 'disabled' ?>>

                <div class="pname"><?= htmlspecialchars($item['item_name']); ?></div>
                <div class="pprice">₱<?= number_format($item['selling_price']); ?></div>
                <div class="pbarcode product-barcode"><?= $item['barcode']; ?></div>

                <?php if ($item['quantity'] <= 0): ?>
                  <span class="stock-badge stock-out">✕ Out of Stock</span>
                <?php elseif ($item['quantity'] <= $item['min_stock']): ?>
                  <span class="stock-badge stock-low">⚠ Low: <?= $item['quantity']; ?></span>
                <?php else: ?>
                  <span class="stock-badge stock-ok">✓ <?= $item['quantity']; ?> left</span>
                <?php endif; ?>

              </button>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
        <?php if ($totalPages > 1): ?>
          <?php
          $queryString = $search !== '' ? '&search=' . urlencode($search) : '';
          ?>
          <div class="mt-6 flex items-center justify-between gap-4" style="border-top: 1px solid var(--border); padding-top: 16px;">
            <div style="font-size:12px; color:var(--text-muted);">
              Page <strong style="color:var(--text)"><?= $page ?></strong> of <strong style="color:var(--text)"><?= $totalPages ?></strong>
            </div>

            <nav style="display:flex; align-items:center; gap:6px;">

              <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?><?= $queryString ?>" class="page-btn">‹ Prev</a>
              <?php else: ?>
                <span class="page-btn disabled">‹ Prev</span>
              <?php endif; ?>

              <?php
              $range = 2;
              $start = max(1, $page - $range);
              $end   = min($totalPages, $page + $range);
              ?>

              <?php if ($start > 1): ?>
                <a href="?page=1<?= $queryString ?>" class="page-btn">1</a>
                <?php if ($start > 2): ?><span class="page-btn disabled">…</span><?php endif; ?>
              <?php endif; ?>

              <?php for ($i = $start; $i <= $end; $i++): ?>
                <?php if ($i === $page): ?>
                  <span class="page-btn active"><?= $i ?></span>
                <?php else: ?>
                  <a href="?page=<?= $i ?><?= $queryString ?>" class="page-btn"><?= $i ?></a>
                <?php endif; ?>
              <?php endfor; ?>

              <?php if ($end < $totalPages): ?>
                <?php if ($end < $totalPages - 1): ?><span class="page-btn disabled">…</span><?php endif; ?>
                <a href="?page=<?= $totalPages ?><?= $queryString ?>" class="page-btn"><?= $totalPages ?></a>
              <?php endif; ?>

              <?php if ($page < $totalPages): ?>
                <a href="?page=<?= $page + 1 ?><?= $queryString ?>" class="page-btn">Next ›</a>
              <?php else: ?>
                <span class="page-btn disabled">Next ›</span>
              <?php endif; ?>

            </nav>
          </div>
        <?php endif; ?>

        <!-- Quotataions Section -->
        <div class="section-label" style="margin-top: 28px;">Quotations</div>

        <!-- Quotations Search -->
        <form method="GET" action="" id="pcSearchForm" style="margin-bottom:16px;">
          <?php if ($search !== ''): ?>
            <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
          <?php endif; ?>
          <div class="search-wrap-inner">
            <svg class="search-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <circle cx="11" cy="11" r="8" />
              <path d="m21 21-4.35-4.35" />
            </svg>
            <input
              type="text"
              name="pc_search"
              id="pcSearchInput"
              class="search-input"
              placeholder="Search Quotation by name…"
              value="<?= htmlspecialchars($pcSearch) ?>">
            <input type="hidden" name="pc_page" value="1">
          </div>
        </form>

        <?php if (empty($pcBuilders)): ?>
          <p style="font-size:16px;" class="text-red-500">No Quotaion found.</p>
        <?php else: ?>
          <div class="product-grid">
            <?php foreach ($pcBuilders as $build):
              $buildItems = $database->getPcBuilderItems($build['pc_builder_id']);
              $hasOutOfStock = false;
              $outOfStockNames = [];
              foreach ($buildItems as $bi) {
                if ($bi['stock'] <= 0) {
                  $hasOutOfStock = true;
                  $outOfStockNames[] = $bi['item_name'];
                }
              }
              $jsBuildName = addslashes($build['pc_builder_name']);
              $jsOutOfStock = $hasOutOfStock ? addslashes(implode(', ', $outOfStockNames)) : '';
            ?>
              <button type="button"
                class="product-card <?= $hasOutOfStock ? 'out-of-stock' : '' ?>"
                <?= !$hasOutOfStock
                  ? "onclick=\"addPcBuilderToCart({$build['pc_builder_id']}, '{$jsBuildName}')\""
                  : 'disabled' ?>>

                <div class="pname"><?= htmlspecialchars($build['pc_builder_name']) ?></div>
                <div class="pprice">₱<?= number_format($build['total_price']) ?></div>
                <div class="pbarcode"><?= $build['item_count'] ?> component<?= $build['item_count'] != 1 ? 's' : '' ?></div>
                <div class="pbarcode" style="margin-top:2px;">by <?= htmlspecialchars($build['username']) ?></div>

                <?php if ($hasOutOfStock): ?>
                  <span class="stock-badge stock-out">✕ Has Out-of-Stock Parts</span>
                <?php else: ?>
                  <span class="stock-badge stock-ok">✓ All Parts Available</span>
                <?php endif; ?>

              </button>
            <?php endforeach; ?>
          </div>

          <!-- PC Builder Pagination -->
          <?php if ($totalPcPages > 1):
            $pcQueryString = ($pcSearch !== '' ? '&pc_search=' . urlencode($pcSearch) : '')
              . ($search   !== '' ? '&search='    . urlencode($search)   : '');
          ?>
            <div style="margin-top:16px; border-top:1px solid var(--border); padding-top:16px; display:flex; align-items:center; justify-content:space-between;">
              <div style="font-size:12px; color:var(--text-muted);">
                Page <strong style="color:var(--text)"><?= $pcPage ?></strong> of <strong style="color:var(--text)"><?= $totalPcPages ?></strong>
              </div>
              <nav style="display:flex; align-items:center; gap:6px;">
                <?php if ($pcPage > 1): ?>
                  <a href="?pc_page=<?= $pcPage - 1 ?><?= $pcQueryString ?>" class="page-btn">‹ Prev</a>
                <?php else: ?>
                  <span class="page-btn disabled">‹ Prev</span>
                <?php endif; ?>

                <?php
                $pcRange = 2;
                $pcStart = max(1, $pcPage - $pcRange);
                $pcEnd   = min($totalPcPages, $pcPage + $pcRange);
                ?>

                <?php if ($pcStart > 1): ?>
                  <a href="?pc_page=1<?= $pcQueryString ?>" class="page-btn">1</a>
                  <?php if ($pcStart > 2): ?><span class="page-btn disabled">…</span><?php endif; ?>
                <?php endif; ?>

                <?php for ($i = $pcStart; $i <= $pcEnd; $i++): ?>
                  <?php if ($i === $pcPage): ?>
                    <span class="page-btn active"><?= $i ?></span>
                  <?php else: ?>
                    <a href="?pc_page=<?= $i ?><?= $pcQueryString ?>" class="page-btn"><?= $i ?></a>
                  <?php endif; ?>
                <?php endfor; ?>

                <?php if ($pcEnd < $totalPcPages): ?>
                  <?php if ($pcEnd < $totalPcPages - 1): ?><span class="page-btn disabled">…</span><?php endif; ?>
                  <a href="?pc_page=<?= $totalPcPages ?><?= $pcQueryString ?>" class="page-btn"><?= $totalPcPages ?></a>
                <?php endif; ?>

                <?php if ($pcPage < $totalPcPages): ?>
                  <a href="?pc_page=<?= $pcPage + 1 ?><?= $pcQueryString ?>" class="page-btn">Next ›</a>
                <?php else: ?>
                  <span class="page-btn disabled">Next ›</span>
                <?php endif; ?>
              </nav>
            </div>
          <?php endif; ?>
        <?php endif; ?>
      </div>

      <!-- RIGHT: Cart + Summary -->
      <div class="cart-panel">

        <!-- Cart Header -->
        <div class="cart-header">
          <div style="display:flex;align-items:center;gap:10px;">
            <h3>Cart</h3>
            <span class="cart-count"><?= count($cart); ?> item<?= count($cart) !== 1 ? 's' : ''; ?></span>
          </div>
          <?php if (!empty($cart)): ?>
            <div style="display:flex;align-items:center;gap:8px;">
              <button type="button" class="btn-save-quotation" onclick="openSaveBuildModal()">Save as Quotation</button>
              <button type="button" class="btn-clear" onclick="openRemoveAllModal()">Clear All</button>
            </div>
          <?php endif; ?>
        </div>

        <!-- Save as PC Build Modal -->
        <div id="saveBuildModal" class="modal-overlay">
          <div class="modal-box">
            <h4>Save as Quotation</h4>
            <p>Enter a name for this Quotation to save the current cart items.</p>

            <form method="POST" action="">
              <input type="hidden" name="action" value="save_as_pc_build">

              <div class="form-group" style="margin: 16px 0;">
                <label for="pc_builder_name">Quotation Name</label>
                <input
                  type="text"
                  id="pc_builder_name"
                  name="pc_builder_name"
                  class="form-control"
                  maxlength="100"
                  placeholder="e.g. Gaming PC Build"
                  required />
              </div>

              <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:16px;">
                <button type="button" class="btn-cancel" onclick="closeSaveBuildModal()">Cancel</button>
                <button type="submit" class="btn-confirm-save">Save</button>
              </div>
            </form>
          </div>
        </div>

        <!-- Cart Items -->
        <div id="cart-container" class="cart-items">
          <?php $cart = $_SESSION['cart'] ?? []; ?>
          <?php if (empty($cart)): ?>
            <div class="cart-empty">
              <div class="empty-icon">🛒</div>
              <p>Cart is empty</p>
              <p style="font-size:12px;">Tap a product to get started</p>
            </div>
          <?php else: ?>
            <?php $i = 1;
            foreach ($cart as $cartKey => $item): ?>
              <?php
              $hasCustom      = isset($item['custom_unit_price']) && $item['custom_unit_price'] > 0;
              $displayPrice   = $hasCustom ? $item['custom_unit_price'] : $item['unit_price'];
              $displayTotal   = $displayPrice * $item['quantity'];
              $jsItemName     = addslashes($item['name']);
              $jsOriginalPrice = $item['unit_price'];
              $jsCustomPrice  = $hasCustom ? $item['custom_unit_price'] : 0;
              ?>
              <div class="cart-item">
                <div class="cart-item-num"><?= $i++; ?></div>
                <div class="cart-item-info"
                  style="cursor:pointer;"
                  onclick="openCustomPriceModal('<?= $cartKey ?>', '<?= $jsItemName ?>', <?= $jsOriginalPrice ?>, <?= $jsCustomPrice ?>)"
                  title="Click to set custom price">
                  <div class="iname">
                    <?= htmlspecialchars($item['name']); ?>
                    <?php if ($hasCustom): ?>
                      <span style="font-size:10px; background:rgba(245,166,35,.15); color:var(--accent); border-radius:4px; padding:1px 6px; margin-left:4px;">Custom</span>
                    <?php endif; ?>
                  </div>
                  <div class="idetail">
                    <?= $item['quantity']; ?> ×
                    <?php if ($hasCustom): ?>
                      <span style="text-decoration:line-through; color:var(--text-muted); font-size:10px;">₱<?= number_format($item['unit_price'], 2) ?></span>
                      <span style="color:var(--accent);">₱<?= number_format($item['custom_unit_price'], 2) ?></span>
                    <?php else: ?>
                      ₱<?= number_format($item['unit_price'], 2); ?>
                    <?php endif; ?>
                  </div>
                </div>
                <div class="cart-item-total">₱<?= number_format($displayTotal, 2); ?></div>

                <!-- increase/decrease/remove buttons — unchanged -->
                <form method="post" action="<?= $_SERVER['PHP_SELF']; ?>" style="display:inline;">
                  <input type="hidden" name="action" value="increase_qty">
                  <input type="hidden" name="increase_item_id" value="<?= $cartKey ?>">
                  <button type="submit" class="btn-increase-item" title="Increase quantity">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14" />
                    </svg>
                  </button>
                </form>
                <form method="POST" action="<?= $_SERVER['PHP_SELF']; ?>" style="display:inline">
                  <input type="hidden" name="action" value="decrease_qty">
                  <input type="hidden" name="remove_item_id" value="<?= $cartKey; ?>">
                  <button type="submit" class="btn-decrease-item" title="Decrease quantity">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14" />
                    </svg>
                  </button>
                </form>
                <button type="button" class="btn-remove-item"
                  onclick="openRemoveItemModal('<?= $cartKey; ?>', '<?= htmlspecialchars($item['name'], ENT_QUOTES); ?>')"
                  title="Remove">
                  <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7L5 7M10 11v6M14 11v6M5 7l1 12c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2l1-12M9 7V4h6v3" />
                  </svg>
                </button>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <!-- Order Summary + Payment -->
        <div class="order-summary">
          <?php
          $subtotal = 0;
          foreach ($cart as $item) {
            $subtotal += $item['line_total'];
          }
          $grand_total = $subtotal;
          ?>

          <div class="summary-row">
            <span>Subtotal</span>
            <span>₱<?= number_format($subtotal, 2); ?></span>
          </div>
          <div class="summary-row total">
            <span>Total</span>
            <span class="total-amount" id="grandTotalDisplay">₱<?= number_format($grand_total, 2); ?></span>
          </div>

          <form method="POST" action="<?= $_SERVER['PHP_SELF']; ?>" id="processSaleForm">
            <input type="hidden" name="action" value="process_sale">

            <!-- Payment Method -->
            <div class="payment-section">
              <div class="payment-label">Payment Method</div>
              <div class="payment-methods">
                <label class="payment-option selected" id="pay-cash">
                  <input type="radio" name="payment_method" value="Cash" checked onchange="updatePaymentStyle()">
                  <div class="pay-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                      <rect x="2" y="6" width="20" height="12" rx="2" />
                      <circle cx="12" cy="12" r="3" />
                      <path stroke-linecap="round" d="M6 12h.01M18 12h.01" />
                    </svg>
                  </div>
                  <div class="pay-label">Cash</div>
                </label>
                <label class="payment-option" id="pay-card">
                  <input type="radio" name="payment_method" value="Credit Card" onchange="updatePaymentStyle()">
                  <div class="pay-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                      <rect x="2" y="5" width="20" height="14" rx="2" />
                      <path stroke-linecap="round" d="M2 10h20" />
                      <path stroke-linecap="round" stroke-width="2" d="M6 15h4" />
                    </svg>
                  </div>
                  <div class="pay-label">Card</div>
                </label>
                <label class="payment-option" id="pay-gcash">
                  <input type="radio" name="payment_method" value="Gcash" onchange="updatePaymentStyle()">
                  <div class="pay-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                      <rect x="7" y="2" width="10" height="20" rx="2" />
                      <path stroke-linecap="round" d="M11 18h2" />
                      <path stroke-linecap="round" d="M10 7h4M10 10.5h4M10 13.5h2" />
                    </svg>
                  </div>
                  <div class="pay-label">GCash</div>
                </label>
              </div>

              <!-- Reference Number (Card / GCash only) -->
              <div id="refNumberWrap" style="
    display: none;
    margin-top: 10px;
    overflow: hidden;
    transition: all .25s ease;
  ">
                <label class="input-group">
                  <span style="font-size:11px; color:var(--text-muted); display:block; margin-bottom:5px; font-weight:500;">
                    Reference Number <span style="color:var(--danger)">*</span>
                  </span>
                  <input type="text" name="ref_number" id="refNumberInput" class="pos-input"
                    placeholder="Enter transaction reference…" autocomplete="off">
                </label>
              </div>
            </div>

            <!-- Inputs -->
            <div class="inputs-row">
              <div class="input-group">
                <label>Cash Received</label>
                <input id="cashReceivedInput" name="cash_received" type="number" step="0.01" min="0"
                  value="<?= $grand_total; ?>" class="pos-input" required>
              </div>
              <div class="input-group">
                <label>Customer</label>
                <input type="text" name="customer" id="customerInput" value="walk in" class="pos-input" placeholder="Walk-in">
              </div>
            </div>

            <!-- Change -->
            <div class="change-display">
              <span class="change-label">Change Due</span>
              <span class="change-amount" id="cashChangeDisplay">₱0.00</span>
            </div>

            <button type="submit" class="btn-process" <?= empty($cart) ? 'disabled style="opacity:.4;cursor:not-allowed;"' : ''; ?>>
              Process Sale
            </button>
          </form>
        </div>

      </div>
    </div>
  </div>

  <!-- MODAL: Remove Item -->
  <div id="removeItemModal" class="modal-overlay">
    <div class="modal-box">
      <h3 style="color:var(--danger)">Remove Item</h3>
      <p>Remove <strong id="removeItemName" style="color:var(--text)"></strong> from the cart?</p>
      <form method="POST" id="removeItemForm">
        <input type="hidden" name="action" value="remove_item">
        <input type="hidden" name="remove_item_id" id="removeItemId">
        <div class="modal-actions">
          <button type="button" class="btn-cancel" onclick="closeModal('removeItemModal')">Cancel</button>
          <button type="submit" class="btn-confirm-danger">Remove</button>
        </div>
      </form>
    </div>
  </div>

  <!-- MODAL: Remove All -->
  <div id="removeAllModal" class="modal-overlay">
    <div class="modal-box">
      <h3 style="color:var(--danger)">Clear Cart?</h3>
      <p>This will remove all items from your cart. This action cannot be undone.</p>
      <form method="POST" action="<?= $_SERVER['PHP_SELF']; ?>" id="removeAllForm">
        <input type="hidden" name="action" value="remove_all">
        <div class="modal-actions">
          <button type="button" class="btn-cancel" onclick="closeModal('removeAllModal')">Cancel</button>
          <button type="submit" class="btn-confirm-danger">Clear All</button>
        </div>
      </form>
    </div>
  </div>

  <!-- MODAL: Quantity -->
  <div id="quantityModal" class="modal-overlay">
    <div class="modal-box">

      <!-- Product Preview Section -->
      <div class="modal-product">
        <div class="product-image">
          <img id="modalItemImage" src="" alt="Product Image" onclick="openLightbox(this.src)">
        </div>

        <div class="product-info">
          <h3 id="modalItemName" class="text-white"></h3>
          <p id="modalItemDescription" class="product-description"></p>
        </div>
      </div>

      <!-- Divider -->
      <div class="modal-divider"></div>

      <!-- Quantity Form -->
      <form method="POST" id="quantityForm" action="<?= $_SERVER['PHP_SELF']; ?>">
        <input type="hidden" name="action" value="add_to_cart">
        <input type="hidden" name="item_id" id="modalItemId" value="">

        </br>

        <div class="qty-controls">
          <button type="button" class="qty-btn" onclick="adjustQty(-1)">−</button>
          <input type="number" id="modalQuantity" name="quantity" min="1" value="1" class="qty-input" required>
          <button type="button" class="qty-btn" onclick="adjustQty(1)">+</button>
        </div>

        <div class="modal-actions">
          <button type="button" class="btn-cancel" onclick="closeModal('quantityModal')">
            Cancel
          </button>
          <button type="submit" class="btn-confirm-ok">
            Add to Cart
          </button>
        </div>
      </form>

    </div>
  </div>

  <!-- MODAL: Custom Price -->
  <div id="customPriceModal" class="modal-overlay">
    <div class="modal-box" style="max-width:400px;">
      <h3>Set Custom Price</h3>
      <p>Override the unit price for <strong id="customPriceItemName" style="color:var(--text)"></strong>.</p>

      <form method="POST" action="<?= $_SERVER['PHP_SELF']; ?>">
        <input type="hidden" name="action" value="set_custom_price">
        <input type="hidden" name="cart_key" id="customPriceCartKey">

        <div style="margin-bottom:14px;">
          <label style="font-size:11px;color:var(--text-muted);display:block;margin-bottom:5px;font-weight:600;">
            Original Price
          </label>
          <div id="customPriceOriginal" style="font-family:'Syne',sans-serif;font-size:18px;font-weight:700;color:var(--text-muted);"></div>
        </div>

        <div style="margin-bottom:20px;">
          <label for="customUnitPriceInput" style="font-size:11px;color:var(--text-muted);display:block;margin-bottom:5px;font-weight:600;">
            Custom Price (₱)
          </label>
          <input
            type="number"
            id="customUnitPriceInput"
            name="custom_unit_price"
            class="pos-input"
            step="0.01"
            min="0"
            placeholder="Enter custom price…"
            required
            style="font-size:18px;font-weight:700;font-family:'Syne',sans-serif;">
        </div>

        <!-- Reset link -->
        <div style="margin-bottom:16px;">
          <button type="submit" name="custom_unit_price" value="0"
            style="background:none;border:none;color:var(--text-muted);font-size:12px;cursor:pointer;text-decoration:underline;padding:0;"
            onclick="document.getElementById('customUnitPriceInput').removeAttribute('required')">
            Reset to original price
          </button>
        </div>

        <div class="modal-actions">
          <button type="button" class="btn-cancel" onclick="closeModal('customPriceModal')">Cancel</button>
          <button type="submit" class="btn-confirm-ok">Apply</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Override Build Modal -->
  <div id="overrideBuildModal" class="modal-overlay <?= isset($_SESSION['sale-duplicate']) ? 'active' : '' ?>">
    <div class="modal-box" style="max-width:420px;">
      <h3 style="margin-bottom:8px;">Quotation Already Exists</h3>
      <p style="font-size:13px; color:var(--text-muted); margin-bottom:20px;">
        A quotation named <strong style="color:var(--text);">
          <?= htmlspecialchars($_SESSION['sale-duplicate'] ?? '') ?>
        </strong> already exists. Do you want to override it with the current cart?
        <br><br>
        <span style="color:var(--danger); font-size:12px;">This will replace all existing items in that quotation.</span>
      </p>
      <form method="POST">
        <input type="hidden" name="action" value="save_as_pc_build">
        <input type="hidden" name="pc_builder_name" value="<?= htmlspecialchars($_SESSION['sale-duplicate'] ?? '') ?>">
        <input type="hidden" name="override_build" value="1">
        <div style="display:flex; justify-content:flex-end; gap:10px;">
          <button type="button" class="btn-cancel" onclick="closeOverrideModal()">Cancel</button>
          <button type="submit" name="save-cart-as-build-btn" class="btn btn-primary">Yes, Override</button>
        </div>
      </form>
    </div>
  </div>
  <?php unset($_SESSION['sale-duplicate']); ?>

  <div id="imageLightbox" class="lightbox-overlay">
    <span class="lightbox-close" onclick="closeLightbox()">✕</span>
    <img id="lightboxImage" src="" alt="Preview">
  </div>
</body>

</html>

<script>
  function closeOverrideModal() {
    document.getElementById('overrideBuildModal').classList.remove('active');
  }

  document.getElementById('overrideBuildModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeOverrideModal();
  });
</script>

<script>
  function openCustomPriceModal(cartKey, itemName, originalPrice, currentCustomPrice) {
    document.getElementById('customPriceCartKey').value = cartKey;
    document.getElementById('customPriceItemName').textContent = itemName;
    document.getElementById('customPriceOriginal').textContent = '₱' + parseFloat(originalPrice).toLocaleString('en-PH', {
      minimumFractionDigits: 2
    });

    const input = document.getElementById('customUnitPriceInput');
    input.value = currentCustomPrice > 0 ? currentCustomPrice : originalPrice;

    openModal('customPriceModal');

    // Select all text for quick editing
    setTimeout(() => input.select(), 100);
  }
</script>
<!-- Open Save Quotation -->
<script>
  function openSaveBuildModal() {
    openModal('saveBuildModal');
  }

  function closeSaveBuildModal() {
    closeModal('saveBuildModal');
    document.getElementById('pc_builder_name').value = '';
  }

  document.getElementById('saveBuildModal').addEventListener('click', function(e) {
    if (e.target === this) closeSaveBuildModal();
  });
</script>

<!-- Sidebar -->
<script>
  const sidebar = document.getElementById('mobile-sidebar');
  const toggleBtn = document.getElementById('sidebar-toggle');
  if (toggleBtn && sidebar) {
    toggleBtn.addEventListener('click', () => sidebar.classList.toggle('-translate-x-full'));
  }
  const closeBtn = document.getElementById('sidebar-close');
  if (closeBtn) closeBtn.addEventListener('click', () => sidebar.classList.add('-translate-x-full'));

  // ── Modals ──
  function openModal(id) {
    document.getElementById(id).classList.add('active');
  }

  function closeModal(id) {
    document.getElementById(id).classList.remove('active');
  }

  document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', function(e) {
      if (e.target === this) this.classList.remove('active');
    });
  });

  // ── Quantity Modal ──
  let _maxQty = 999;

  function openQuantityModal(id, name, description, image, stock) {

    document.getElementById('modalItemId').value = id;
    document.getElementById('modalItemName').innerText = name;
    document.getElementById('modalItemDescription').innerText =
      description || 'No description available';

    document.getElementById('modalItemImage').src =
      image ? 'uploads/products/' + image : 'uploads/products/image_not_available.png';

    const qtyInput = document.getElementById('modalQuantity');
    qtyInput.value = 1;
    qtyInput.max = stock;

    _maxQty = stock;

    openModal('quantityModal');
  }

  function adjustQty(delta) {
    const input = document.getElementById('modalQuantity');
    let val = parseInt(input.value) || 1;
    val = Math.min(_maxQty, Math.max(1, val + delta));
    input.value = val;
  }

  // ── Remove Item Modal ──
  function openRemoveItemModal(itemId, itemName) {
    document.getElementById('removeItemId').value = itemId;
    document.getElementById('removeItemName').textContent = itemName;
    openModal('removeItemModal');
  }

  function openRemoveAllModal() {
    openModal('removeAllModal');
  }

  function openAddItemModal(itemId, itemName) {
    document.getElementById('removeItemId').value = itemId;
    document.getElementById('removeItemName').textContent = itemName;
    openModal('addItemModal');
  }

  // ── Payment Style + Ref Number ──
  function updatePaymentStyle() {
    document.querySelectorAll('.payment-option').forEach(opt => opt.classList.remove('selected'));
    const checked = document.querySelector('.payment-option input:checked');
    if (checked) checked.closest('.payment-option').classList.add('selected');

    const method = checked?.value;
    const wrap = document.getElementById('refNumberWrap');
    const refInput = document.getElementById('refNumberInput');
    const needsRef = method === 'Credit Card' || method === 'Gcash';

    if (needsRef) {
      wrap.style.display = 'block';
      refInput.required = true;
    } else {
      wrap.style.display = 'none';
      refInput.required = false;
      refInput.value = '';
    }
  }

  // ── Cash change ──
  document.addEventListener("DOMContentLoaded", function() {
    const cashInput = document.getElementById("cashReceivedInput");
    const changeDisplay = document.getElementById("cashChangeDisplay");
    const grandTotal = <?= $grand_total; ?>;

    function updateChange() {
      const cash = parseFloat(cashInput.value);
      const change = isNaN(cash) ? 0 : Math.max(0, cash - grandTotal);
      changeDisplay.textContent = `₱${change.toFixed(2)}`;
    }

    if (cashInput) cashInput.addEventListener("input", updateChange);
    updateChange();
  });

  // ── Search debounce ──
  document.addEventListener("DOMContentLoaded", function() {
    const searchInput = document.getElementById('productSearchInput');
    if (!searchInput) return;
    let searchTimeout;
    searchInput.addEventListener('input', function() {
      clearTimeout(searchTimeout);
      searchTimeout = setTimeout(() => {
        document.getElementById('productSearchForm').submit();
      }, 500);
    });
  });

  // ── Focus search ──
  document.addEventListener('DOMContentLoaded', function() {
    const productSearch = document.getElementById('productSearchInput');
    const pcSearch = document.getElementById('pcSearchInput');

    const search = window.location.search;
    const hasPcSearch = search.includes('pc_search=') && !search.includes('pc_search=&') && !search.endsWith('pc_search=');

    if (hasPcSearch) {
      if (pcSearch) pcSearch.focus();
    } else {
      if (productSearch) productSearch.focus();
    }
  });


  // ── Scroll persistence ──
  window.addEventListener('beforeunload', () => sessionStorage.setItem('scrollPos', window.scrollY));
  window.addEventListener('load', () => {
    const p = sessionStorage.getItem('scrollPos');
    if (p) {
      window.scrollTo(0, parseInt(p));
      sessionStorage.removeItem('scrollPos');
    }
  });

  // ── Auto-hide alerts ──
  ['successAlert', 'errorAlert'].forEach(id => {
    const el = document.getElementById(id);
    if (el) setTimeout(() => {
      el.style.opacity = '0';
      el.style.transition = 'opacity .5s';
      setTimeout(() => el.remove(), 500);
    }, 3000);
  });
</script>
<script>
  function openLightbox(src) {
    const lightbox = document.getElementById('imageLightbox');
    const img = document.getElementById('lightboxImage');
    img.src = src;
    lightbox.classList.add('active');
  }

  function closeLightbox() {
    document.getElementById('imageLightbox').classList.remove('active');
  }

  // Close when clicking outside image
  document.getElementById('imageLightbox').addEventListener('click', function(e) {
    if (e.target === this) closeLightbox();
  });
</script>
<script>
  function addPcBuilderToCart(pcBuilderId, buildName) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '';

    const fields = {
      action: 'add_to_cart',
      pc_builder_id: pcBuilderId,
      quantity: 1
    };

    for (const [key, val] of Object.entries(fields)) {
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = key;
      input.value = val;
      form.appendChild(input);
    }

    document.body.appendChild(form);
    form.submit();
  }

  // PC builder search debounce
  document.addEventListener('DOMContentLoaded', function() {
    const pcSearch = document.getElementById('pcSearchInput');
    if (!pcSearch) return;
    let t;
    pcSearch.addEventListener('input', function() {
      clearTimeout(t);
      t = setTimeout(() => pcSearch.closest('form').submit(), 500);
    });
  });
</script>