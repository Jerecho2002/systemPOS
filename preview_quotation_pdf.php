<?php
ob_start();
ini_set('pcre.backtrack_limit', '10000000');
require_once 'database/database.php';
require_once 'vendor/autoload.php';

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

if (!isset($_GET['id']) || !isset($_SESSION['user_id'])) {
  die('Invalid request');
}

$pcBuilderId = $_GET['id'];
$database    = new Database();
$data        = $database->getPcBuilderDetails($pcBuilderId);

if (!$data) {
  die('Quotation not found');
}

use Mpdf\Mpdf;

$mpdf = new Mpdf([
  'margin_left'   => 0,
  'margin_right'  => 0,
  'margin_top'    => 0,
  'margin_bottom' => 0,
]);

$mpdf->SetTitle('Quotation - ' . $data['builder']['pc_builder_name']);
$mpdf->SetAuthor('Hanging Parrot Digital Solutions');

// Build table rows
function buildRows(array $items): string
{
  $html = '';
  foreach ($items as $i => $item) {
    $bg = ($i % 2 === 0) ? '#ffffff' : '#f9fafb';

    $hasCustom = isset($item['unit_price']) && abs((float)$item['unit_price'] - (float)$item['selling_price']) > 0.001;

    $priceDisplay = 'PHP ' . number_format($item['line_total'], 2);

    if ($hasCustom) {
      $priceDisplay .= '<br><span style="text-decoration:line-through; color:#999; font-size:9px;">PHP ' . number_format($item['selling_price'] * $item['quantity'], 2) . '</span>';
    }

    $html .= '
        <tr style="background:' . $bg . ';">
            <td class="td-cat">'   . htmlspecialchars($item['category_name']) . '</td>
            <td class="td-name">'  . htmlspecialchars($item['item_name'])     . '</td>
            <td class="td-qty">'   . (int)$item['quantity']                  . '</td>
            <td class="td-price">' . $priceDisplay                           . '</td>
        </tr>';
  }
  return $html;
}

$builderName = htmlspecialchars($data['builder']['pc_builder_name']);
$createdDate = date('F d, Y', strtotime($data['builder']['created_at']));
$createdBy   = htmlspecialchars($data['builder']['username']);
$grandTotal  = number_format($data['grand_total'], 2);

$partsSection = '';
if (!empty($data['pc_parts'])) {
  $partsSection = '
    <div class="section-label">PC Components &amp; Hardware</div>
    <table class="items-table">
        <thead><tr>
            <th class="th-cat">Category</th>
            <th class="th-name">Component / Description</th>
            <th class="th-qty">Qty</th>
            <th class="th-price">Amount</th>
        </tr></thead>
        <tbody>' . buildRows($data['pc_parts']) . '</tbody>
    </table>';
}

$accessoriesSection = '';
if (!empty($data['accessories'])) {
  $accessoriesSection = '
    <div class="section-label">Peripherals &amp; Accessories</div>
    <table class="items-table">
        <thead><tr>
            <th class="th-cat">Category</th>
            <th class="th-name">Item / Description</th>
            <th class="th-qty">Qty</th>
            <th class="th-price">Amount</th>
        </tr></thead>
        <tbody>' . buildRows($data['accessories']) . '</tbody>
    </table>';
}

$html = '
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>

  * { box-sizing: border-box; margin: 0; padding: 0; }

  body {
    font-family: Arial, sans-serif;
    font-size: 9pt;
    color: #2d2d2d;
    background: #fff;
  }

  /* ── BODY CONTENT ────────────────────────────── */
  .body-wrap { padding: 20px 28px 28px 28px; }

  /* ── META CARDS ──────────────────────────────── */
  .meta-row {
    display: table;
    width: 100%;
    margin-bottom: 20px;
    border-bottom: 1px solid #e5e9f0;
    padding-bottom: 16px;
  }
  .meta-cell {
    display: table-cell;
    vertical-align: top;
    padding-right: 20px;
  }
  .meta-cell:last-child { padding-right: 0; text-align: right; }
  .meta-key {
    font-size: 7pt;
    color: #999;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    margin-bottom: 3px;
  }
  .meta-value {
    font-size: 10pt;
    font-weight: bold;
    color: #1c2f5e;
  }

  /* ── SECTION LABEL ───────────────────────────── */
  .section-label {
    font-size: 8.5pt;
    font-weight: bold;
    color: #1c2f5e;
    text-transform: uppercase;
    letter-spacing: 1px;
    border-bottom: 2px solid #1c2f5e;
    padding-bottom: 4px;
    margin: 18px 0 0 0;
  }

  /* ── TABLE ───────────────────────────────────── */
  .items-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 8.5pt;
    margin-top: 0;
  }
  .items-table th {
    background: #f0f3f9;
    color: #1c2f5e;
    padding: 7px 10px;
    text-align: left;
    font-size: 7.5pt;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    border-bottom: 1px solid #d0d8ea;
  }
  .items-table td {
    padding: 6px 10px;
    border-bottom: 1px solid #eff1f5;
    vertical-align: middle;
  }
  .th-cat   { width: 24%; }
  .th-name  { width: 46%; }
  .th-qty   { width: 8%; text-align: center; }
  .th-price { width: 22%; text-align: right; }
  .td-cat   { font-weight: bold; color: #3a3a3a; }
  .td-name  { color: #555; }
  .td-qty   { text-align: center; color: #666; }
  .td-price { text-align: right; font-weight: bold; color: #1c2f5e; }

  /* ── BOTTOM SECTION ──────────────────────────── */
  .bottom-wrap {
    display: table;
    width: 100%;
    margin-top: 24px;
    border-top: 1px solid #e5e9f0;
    padding-top: 18px;
  }
  .terms-col { display: table-cell; vertical-align: top; padding-right: 20px; }
  .total-col { display: table-cell; vertical-align: middle; width: 190px; }

  .terms-title {
    font-size: 8pt;
    font-weight: bold;
    color: #888;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    margin-bottom: 7px;
  }
  .terms-list {
    font-size: 7.5pt;
    color: #888;
    padding-left: 12px;
    line-height: 1.9;
  }

  .total-card {
    background: #1c2f5e;
    border-radius: 5px;
    padding: 18px 16px;
    text-align: center;
  }
  .total-label  { font-size: 7.5pt; color: #8aaad0; text-transform: uppercase; letter-spacing: 1px; }
  .total-amount { font-size: 17pt; font-weight: bold; color: #ffffff; margin-top: 5px; line-height: 1.2; }
  .total-note   { font-size: 7pt; color: #6a8aba; margin-top: 6px; }

  /* ── FOOTER ──────────────────────────────────── */
  .footer {
    background: #f5f7fb;
    border-top: 1px solid #e0e5ef;
    padding: 10px 28px;
    text-align: center;
    font-size: 7.5pt;
    color: #999;
    margin-top: 24px;
  }

</style>
</head>
<body>

<!-- HEADER -->
<table width="100%" cellpadding="0" cellspacing="0" style="background:#3b5998; padding:20px 32px; text-align:center;">
  <tr>
    <td valign="middle">
      <!-- Main Shop Name -->
      <div style="font-size:18pt; font-weight:bold; color:#ffffff;">
        Hanging Parrot Digital Solutions
      </div>

      <!-- Tagline -->
      <div style="font-size:9pt; color:#ffffff; margin-top:6px;">
        Your Trusted Computer Solutions Partner
      </div>

      <!-- Contact Info -->
      <div style="font-size:8.5pt; color:#ffffff; margin-top:10px; line-height:1.6;">
        (032) 479-8933 &nbsp;/&nbsp; +63 919-955-55666<br>
        Ground Floor, ESC Building, Corner Sanciangko &amp; Janquera St., Cebu City
      </div>
    </td>
  </tr>
</table>

<!-- BODY -->
<div class="body-wrap">

  <!-- META -->
  <div class="meta-row">
    <div class="meta-cell">
      <div class="meta-key">System Name</div>
      <div class="meta-value">' . $builderName . '</div>
    </div>
    <div class="meta-cell">
      <div class="meta-key">Date Issued</div>
      <div class="meta-value">' . $createdDate . '</div>
    </div>
  </div>

  <!-- PC PARTS -->
  ' . $partsSection . '

  <!-- ACCESSORIES -->
  ' . $accessoriesSection . '

 <!-- TOTAL -->
<table width="100%" cellpadding="0" cellspacing="0" style="margin-top:30px;">
  <tr>
    <td align="right">
      <table cellpadding="0" cellspacing="0">
        <tr>
          <td style="
            font-size:9pt;
            color:#666;
            text-transform:uppercase;
            letter-spacing:1px;
            padding-bottom:6px;
            text-align:right;
          ">
            Grand Total
          </td>
        </tr>
        <tr>
          <td style="
            font-size:20pt;
            font-weight:bold;
            color:#1c2f5e;
            text-align:right;
          ">
            PHP ' . $grandTotal . '
          </td>
        </tr>
        <tr>
          <td style="
            font-size:8pt;
            color:#888;
            padding-top:4px;
            text-align:right;
          ">
            Inclusive of assembly &amp; testing
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>

<!-- DIVIDER -->
<hr style="margin:18px 0 20px 0; border:none; border-top:1px solid #e5e9f0;">

<!-- TERMS -->
<div>
  <div style="
    font-size:8.5pt;
    font-weight:bold;
    color:#666;
    text-transform:uppercase;
    letter-spacing:1px;
    margin-bottom:8px;
  ">
    Terms &amp; Conditions
  </div>

  <ul style="
    font-size:8pt;
    color:#777;
    padding-left:14px;
    line-height:1.8;
  ">
    <li>Prices are subject to change based on component availability.</li>
    <li>Warranty applies to all components per manufacturer specifications.</li>
    <li>Assembly and testing services are included.</li>
    <li>Free technical support for 30 days after purchase.</li>
    <li>Installation of operating system and drivers is included.</li>
  </ul>
</div>
</div>

<!-- FOOTER -->
<div class="footer">
  Thank you for choosing <strong>Hanging Parrot Digital Solutions</strong> &nbsp;&mdash;&nbsp; (032) 479-8933 &nbsp;&mdash;&nbsp; Cebu City
</div>

</body>
</html>';

ob_end_clean();

$mpdf->WriteHTML($html);
$mpdf->Output('HPDS_Quotation_' . $data['builder']['pc_builder_name'] . '.pdf', 'I');
