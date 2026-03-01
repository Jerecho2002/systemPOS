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

$rmaId    = (int) $_GET['id'];
$database = new Database();
$data     = $database->getRmaDetails($rmaId);

if (!$data) {
    die('RMA not found.');
}

use Mpdf\Mpdf;

$mpdf = new Mpdf([
    'margin_left'   => 0,
    'margin_right'  => 0,
    'margin_top'    => 0,
    'margin_bottom' => 0,
]);

$rma   = $data['rma'];
$items = $data['items'];

$mpdf->SetTitle('RMA - ' . $rma['rma_number']);
$mpdf->SetAuthor('Hanging Parrot Digital Solutions');

$rmaNumber    = htmlspecialchars($rma['rma_number']);
$rmaDate      = date('F d, Y', strtotime($rma['date']));
$customer     = htmlspecialchars($rma['customer_name']);
$condition    = htmlspecialchars($rma['condition']);
$status       = htmlspecialchars($rma['status']);
$reason       = nl2br(htmlspecialchars($rma['reason']));
$createdBy    = htmlspecialchars($rma['created_by'] ?? '—');

// Singular or plural items label
$itemCount      = count($items);
$itemsLabel     = $itemCount === 1 ? 'Returned Item' : 'Returned Items';

// Build items table rows
$itemRows = '';
foreach ($items as $i => $item) {
    $bg = ($i % 2 === 0) ? '#ffffff' : '#f9fafb';
    $itemRows .= '
        <tr style="background:' . $bg . ';">
            <td style="width:8%; text-align:center; color:#999; padding:7px 10px;">'  . ($i + 1) . '</td>
            <td style="width:76%; font-weight:600; color:#2d2d2d; padding:7px 10px;">' . htmlspecialchars($item['item_name']) . '</td>
            <td style="width:16%; text-align:center; color:#1c2f5e; font-weight:bold; padding:7px 10px;">'  . (int) $item['quantity'] . '</td>
        </tr>';
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

    .body-wrap { padding: 20px 28px 28px 28px; }

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
        padding-right: 24px;
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

    .section-label {
        font-size: 8.5pt;
        font-weight: bold;
        color: #1c2f5e;
        text-transform: uppercase;
        letter-spacing: 1px;
        border-bottom: 2px solid #1c2f5e;
        padding-bottom: 4px;
        margin: 20px 0 0 0;
    }

    .info-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 9pt;
    }

    .info-table td {
        padding: 8px 12px;
        border-bottom: 1px solid #eff1f5;
        vertical-align: top;
    }

    .info-table tr:last-child td { border-bottom: none; }

    .info-key {
        width: 30%;
        color: #888;
        font-size: 8pt;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: bold;
    }

    .info-val {
        color: #2d2d2d;
        font-weight: 600;
    }

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
        padding: 7px 10px;
        border-bottom: 1px solid #eff1f5;
        vertical-align: middle;
    }

    .td-num  { width: 8%;  text-align: center; color: #999; }
    .td-name { width: 76%; font-weight: 600; color: #2d2d2d; }
    .td-qty  { width: 16%; text-align: center; color: #1c2f5e; font-weight: bold; }

    .sig-row {
        display: table;
        width: 100%;
        margin-top: 60px;
    }

    .sig-cell {
        display: table-cell;
        width: 33%;
        text-align: center;
        padding: 0 24px;
    }

    .sig-line {
        border-top: 1px solid #999;
        margin-bottom: 6px;
    }

    .sig-label {
        font-size: 7.5pt;
        color: #888;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .footer {
        background: #f5f7fb;
        border-top: 1px solid #e0e5ef;
        padding: 10px 28px;
        text-align: center;
        font-size: 7.5pt;
        color: #999;
        margin-top: 28px;
    }
</style>
</head>
<body>

<!-- HEADER -->
<table width="100%" cellpadding="0" cellspacing="0" style="background:#3b5998; padding:20px 32px; text-align:center;">
    <tr>
        <td valign="middle">
            <div style="font-size:18pt; font-weight:bold; color:#ffffff;">
                Hanging Parrot Digital Solutions
            </div>
            <div style="font-size:9pt; color:#ffffff; margin-top:6px;">
                Return Merchandise Authorization
            </div>
            <div style="font-size:8.5pt; color:#ffffff; margin-top:10px; line-height:1.6;">
                (032) 479-8933 &nbsp;/&nbsp; +63 919-955-55666<br>
                Ground Floor, ESC Building, Corner Sanciangko &amp; Janquera St., Cebu City
            </div>
        </td>
    </tr>
</table>

<!-- BODY -->
<div class="body-wrap">

    <!-- META ROW -->
    <div class="meta-row">
        <div class="meta-cell">
            <div class="meta-key">RMA Number</div>
            <div class="meta-value">' . $rmaNumber . '</div>
        </div>
        <div class="meta-cell">
            <div class="meta-key">Date Issued</div>
            <div class="meta-value">' . $rmaDate . '</div>
        </div>
        <div class="meta-cell">
            <div class="meta-key">Status</div>
            <div class="meta-value">' . $status . '</div>
        </div>
        <div class="meta-cell">
            <div class="meta-key">Processed By</div>
            <div class="meta-value">' . $createdBy . '</div>
        </div>
    </div>

    <!-- RMA INFO -->
    <div class="section-label">Return Details</div>
    <table class="info-table">
        <tr>
            <td class="info-key">Customer Name</td>
            <td class="info-val">' . $customer . '</td>
        </tr>
        <tr>
            <td class="info-key">Condition</td>
            <td class="info-val" style="font-weight:700;">' . $condition . '</td>
        </tr>
    </table>

    <!-- ITEMS TABLE -->
    <div class="section-label" style="margin-top:20px;">' . $itemsLabel . '</div>
 <table class="items-table" style="table-layout:fixed; width:100%;">
    <thead>
        <tr>
            <th style="width:8%; text-align:center; background:#f0f3f9; color:#1c2f5e; padding:7px 10px; font-size:7.5pt; font-weight:bold; text-transform:uppercase; letter-spacing:0.4px; border-bottom:1px solid #d0d8ea;">#</th>
            <th style="width:76%; text-align:left; background:#f0f3f9; color:#1c2f5e; padding:7px 10px; font-size:7.5pt; font-weight:bold; text-transform:uppercase; letter-spacing:0.4px; border-bottom:1px solid #d0d8ea;">Item</th>
            <th style="width:16%; text-align:center; background:#f0f3f9; color:#1c2f5e; padding:7px 10px; font-size:7.5pt; font-weight:bold; text-transform:uppercase; letter-spacing:0.4px; border-bottom:1px solid #d0d8ea;">Qty</th>
        </tr>
    </thead>
    <tbody>
        ' . $itemRows . '
    </tbody>
</table>

    <!-- REASON -->
    <div class="section-label" style="margin-top:20px;">Reason for Return</div>
    <p style="font-size:9pt; color:#444; line-height:1.8; margin-top:10px; padding: 0 4px;">' . $reason . '</p>

    <!-- SIGNATURES -->
<table width="100%" cellpadding="0" cellspacing="0" style="margin-top:60px;">
    <tr>
        <td width="5%"></td>
        <td width="26%" style="text-align:center;">
            <div style="border-top:1px solid #999; margin-bottom:6px;"></div>
            <div style="font-size:7.5pt; color:#888; text-transform:uppercase; letter-spacing:0.5px;">Customer Signature</div>
        </td>
        <td width="12%"></td>
        <td width="26%" style="text-align:center;">
            <div style="border-top:1px solid #999; margin-bottom:6px;"></div>
            <div style="font-size:7.5pt; color:#888; text-transform:uppercase; letter-spacing:0.5px;">Received By</div>
        </td>
        <td width="12%"></td>
        <td width="26%" style="text-align:center;">
            <div style="border-top:1px solid #999; margin-bottom:6px;"></div>
            <div style="font-size:7.5pt; color:#888; text-transform:uppercase; letter-spacing:0.5px;">Authorized By</div>
        </td>
        <td width="5%"></td>
    </tr>
</table>

    <!-- NOTES -->
    <hr style="margin:28px 0 16px 0; border:none; border-top:1px solid #e5e9f0;">
    <div>
        <div style="font-size:8.5pt; font-weight:bold; color:#666; text-transform:uppercase; letter-spacing:1px; margin-bottom:8px;">
            Notes
        </div>
        <ul style="font-size:8pt; color:#777; padding-left:14px; line-height:1.8;">
            <li>This document serves as proof of return for warranty or supplier claim purposes.</li>
            <li>Items must be returned in original packaging if available.</li>
            <li>Processing time may vary depending on supplier response.</li>
            <li>Keep this document for your records.</li>
        </ul>
    </div>

</div>

<!-- FOOTER -->
<div class="footer">
    <strong>Hanging Parrot Digital Solutions</strong> &nbsp;&mdash;&nbsp; (032) 479-8933 &nbsp;&mdash;&nbsp; Cebu City
</div>

</body>
</html>';

ob_end_clean();

$mpdf->WriteHTML($html);
$mpdf->Output('RMA_' . $rma['rma_number'] . '.pdf', 'I');
