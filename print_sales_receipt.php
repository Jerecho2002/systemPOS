<?php
require_once 'database/database.php';
require_once 'vendor/autoload.php'; // mPDF

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

if (!isset($_GET['id']) || !isset($_SESSION['user_id'])) {
  die('Invalid request');
}

$saleId = $_GET['id'];
$database = new Database();
$data = $database->getSaleDetails($saleId);

if (!$data) {
  die('Sale not found.');
}

$sale = $data['sale'];
$items = $data['items'];

$referenceRow = '';

$refNumber = isset($sale['ref_number'])
  ? trim($sale['ref_number'])
  : '';

if ($refNumber !== '') {
  $referenceRow = '
    <tr>
      <td>Reference No:</td>
      <td>' . htmlspecialchars($refNumber) . '</td>
    </tr>';
}

// Start HTML content for mPDF
$html = '
<html>
<head>
<style>
body {
    font-family: Courier, monospace;
    margin: 0;
    padding: 0;
}
.container {
    width: 76mm;
    padding: 2mm;
}
.center {
    text-align: center;
}
.dashed {
    border-bottom: 1px dashed #000;
    margin: 2px 0;
}
.header-title {
    font-size: 13pt;
    font-weight: bold;
}
.header-subtitle {
    font-size: 11pt;
}
.store-info {
    font-size: 8pt;
}
.receipt-title {
    font-size: 11pt;
    font-weight: bold;
}
.item-name {
    font-size: 9pt;
    font-weight: bold;
}
.item-info {
    font-size: 8pt;
}
.total-label {
    text-align: right;
    font-size: 9pt;
}
.total-value {
    text-align: right;
    font-size: 9pt;
    font-weight: bold;
}
.footer {
    margin-top: 5px;
    text-align: center;
    font-size: 8pt;
}
</style>
</head>
<body>
<div class="container">

  <!-- Header -->
  <div class="center header-title">HANGING PARROT</div>
  <div class="center header-subtitle">Digital Solutions</div>
  <div class="center store-info">
    Ground Floor, ESC Building<br>
    Corner Sanciangko & Janquera St., Cebu City<br>
    (032) 479-8933 / 0919-95555666
  </div>
  <div class="dashed"></div>

  <!-- Receipt Title -->
  <div class="center receipt-title">SALES RECEIPT</div>
  <div class="dashed"></div>

  <!-- Transaction Info -->
  <table width="100%">
    <tr>
      <td>Receipt No:</td>
      <td>' . $sale['transaction_id'] . '</td>
    </tr>
    <tr>
      <td>Date:</td>
      <td>' . date('M d, Y h:i A', strtotime($sale['date'] ?? 'now')) . '</td>
    </tr>
    <tr>
      <td>Customer:</td>
      <td>' . $sale['customer_name'] . '</td>
    </tr>
    <tr>
      <td>Cashier:</td>
      <td>' . $sale['username'] . '</td>
    </tr>
      ' . $referenceRow . '
  </table>
  <div class="dashed"></div>

  <!-- Items -->
';

foreach ($items as $item) {
  $hasCustom     = isset($item['custom_unit_price']) && $item['custom_unit_price'] > 0;
  $displayPrice  = $hasCustom ? $item['custom_unit_price'] : $item['unit_price'];

  $html .= '
        <div class="item-name">' . htmlspecialchars($item['item_name']) . '</div>
        <div class="item-info">' . $item['quantity'] . ' x PHP ' . number_format($displayPrice, 2);

  if ($hasCustom) {
    $html .= ' <span style="text-decoration:line-through; color:#999; font-size:10px;">PHP ' . number_format($item['unit_price'], 2) . '</span>';
  }

  $html .= '</div>';
}

$html .= '<div class="dashed"></div>';

// VAT Calculation
$vatRate = 0.12;
$vatAmount = $sale['grand_total'] * $vatRate;

$html .= '
<table width="100%">
  <tr>
    <td class="total-label">VAT:</td>
    <td class="total-value">PHP ' . number_format($vatAmount, 2) . '</td>
  </tr>
  <tr>
    <td class="total-label">Total:</td>
    <td class="total-value">PHP ' . number_format($sale['grand_total'], 2) . '</td>
  </tr>
  <tr>
    <td class="total-label">Payment:</td>
    <td class="total-value">' . strtoupper($sale['payment_method']) . '</td>
  </tr>
  <tr>
    <td class="total-label">Cash Received:</td>
    <td class="total-value">PHP ' . number_format($sale['cash_received'], 2) . '</td>
  </tr>
  <tr>
    <td class="total-label">Change:</td>
    <td class="total-value">PHP ' . number_format($sale['cash_change'], 2) . '</td>
  </tr>
</table>

<div class="dashed"></div>

<!-- Footer -->
<div class="footer">
  THANK YOU!<br>
  Please come again
</div>

</div>
</body>
</html>
';

// Create mPDF
$mpdf = new \Mpdf\Mpdf([
  'mode' => 'utf-8',
  'format' => [80, 200 + (count($items) * 8)], // 80mm wide, dynamic height
  'margin_left' => 2,
  'margin_right' => 2,
  'margin_top' => 4,
  'margin_bottom' => 4,
]);

$mpdf->WriteHTML($html);
$mpdf->Output('Receipt_' . $sale['transaction_id'] . '.pdf', 'I');
