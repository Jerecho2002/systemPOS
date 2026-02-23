<?php
ob_start(); // Prevent headers already sent

require_once 'database/database.php';
require_once 'vendor/autoload.php'; // FPDF via Composer

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_GET['id']) || !isset($_SESSION['user_id'])) {
    die('Invalid request');
}

$saleId = $_GET['id'];
$database = new Database();

// Fetch sale details with items
$data = $database->getSaleDetails($saleId);

if (!$data) {
    die('Sale not found.');
}

// Use correct variables from $data array
$sale = $data['sale'];
$items = $data['items'];

// Calculate dynamic height - increased footer space
$itemsHeight = count($items) * 8; // 8mm per item
$pageHeight = 40 + $itemsHeight + 35 + 40; // Increased footer from 25 to 30

// Extend FPDF for 80mm thermal receipt
class ReceiptPDF extends FPDF
{
    public $w = 76; // Full 76mm usable width (80mm with 2mm margins each side)

    function Header()
    {
        // Store Name
        $this->SetFont('Courier', 'B', 13);
        $this->Cell($this->w, 5, "HANGING PARROT", 0, 1, 'C');
        $this->SetFont('Courier', '', 11);
        $this->Cell($this->w, 4, "Digital Solutions", 0, 1, 'C');
        $this->Ln(1);

        // Store Details
        $this->SetFont('Courier', '', 8);
        $this->Cell($this->w, 3, "123 Tech Street, Makati City", 0, 1, 'C');
        $this->Cell($this->w, 3, "Metro Manila, Philippines", 0, 1, 'C');
        $this->Cell($this->w, 3, "Tel: (02) 123-4567", 0, 1, 'C');
        $this->Cell($this->w, 3, "Email: sales@hpds.ph", 0, 1, 'C');
        $this->Ln(2);

        // Separator - dashed line like thermal printers
        $this->SetFont('Courier', '', 8);
        $this->Cell($this->w, 3, "- - - - - - - - - - - - - - - - - - - - - - - - -", 0, 1, 'C');
        $this->Ln(1);
    }



    function ReceiptBody($sale, $items)
    {
        // Receipt title
        $this->SetFont('Courier', 'B', 11);
        $this->Cell($this->w, 5, "SALES RECEIPT", 0, 1, 'C');
        $this->Ln(1);

        // Transaction info
        $this->SetFont('Courier', '', 9);
        $this->Cell(24, 3.5, "Receipt No:", 0, 0);
        $this->SetFont('Courier', 'B', 9);
        $this->Cell(52, 3.5, $sale['transaction_id'], 0, 1);

        $this->SetFont('Courier', '', 9);
        $this->Cell(24, 3.5, "Date:", 0, 0);
        $this->Cell(52, 3.5, date('M d, Y h:i A', strtotime($sale['date'] ?? 'now')), 0, 1);

        $this->Cell(24, 3.5, "Customer:", 0, 0);
        $this->Cell(52, 3.5, $sale['customer_name'], 0, 1);

        $this->Cell(24, 3.5, "Cashier:", 0, 0);
        $this->Cell(52, 3.5, $sale['username'], 0, 1);

        $this->Ln(1);

        // Items separator
        $this->SetFont('Courier', '', 8);
        $this->Cell($this->w, 3, "- - - - - - - - - - - - - - - - - - - - - - - - -", 0, 1, 'C');
        $this->Ln(0.5);

        // Items - Simple list
        foreach ($items as $item) {
            $name = $item['item_name'];
            $qty = $item['quantity'];
            $unitPrice = $item['unit_price'];

            // Item name on its own line
            $this->SetFont('Courier', 'B', 9);
            $this->Cell($this->w, 4, $name, 0, 1, 'L');

            // Qty x Price on second line
            $this->SetFont('Courier', '', 8);
            $priceInfo = $qty . " x PHP " . number_format($unitPrice, 2);
            $this->Cell($this->w, 3.5, $priceInfo, 0, 1, 'L');

            $this->Ln(0.5);
        }

        $this->Ln(2);
        $this->SetFont('Courier', '', 8);
        $this->Cell($this->w, 3, "- - - - - - - - - - - - - - - - - - - - - - - - -", 0, 1, 'C');
        $this->Ln(1);

        // VAT Calculation (12%)
        $vatRate = 0.12;
        $vatAmount = $sale['grand_total'] * $vatRate;
        $netOfVat = $sale['grand_total'] - $vatAmount;

        // VAT Breakdown
        $this->SetFont('Courier', '', 9);
        $this->Cell(46, 4, "VAT (12%):", 0, 0, 'R');
        $this->Cell(30, 4, "PHP " . number_format($vatAmount, 2), 0, 1, 'R');

        $this->Cell(46, 4, "Net of VAT:", 0, 0, 'R');
        $this->Cell(30, 4, "PHP " . number_format($netOfVat, 2), 0, 1, 'R');

        $this->Ln(1);

        $this->SetFont('Courier', '', 8);
        $this->Cell($this->w, 3, "- - - - - - - - - - - - - - - - - - - - - - - - -", 0, 1, 'C');
        $this->Ln(1);

        // Total Amount
        $this->SetFont('Courier', 'B', 11);
        $this->Cell(46, 5, "Total:", 0, 0, 'R');
        $this->Cell(30, 5, "PHP " . number_format($sale['grand_total'], 2), 0, 1, 'R');
        $this->Ln(1);

        // Payment method
        $this->SetFont('Courier', '', 9);
        $this->Cell(46, 3.5, "Payment:", 0, 0, 'R');
        $this->Cell(30, 3.5, strtoupper($sale['payment_method']), 0, 1, 'R');

        $this->SetFont('Courier', '', 8);
        $this->Cell($this->w, 3, "- - - - - - - - - - - - - - - - - - - - - - - - -", 0, 1, 'C');
        $this->Ln(1);

        // Cash & Change
        $this->SetFont('Courier', '', 10);
        $this->Cell(46, 4, "Cash Received:", 0, 0, 'R');
        $this->Cell(30, 4, "PHP " . number_format($sale['cash_received'], 2), 0, 1, 'R');

        $this->SetFont('Courier', 'B', 10);
        $this->Cell(46, 5, "Change:", 0, 0, 'R');
        $this->Cell(30, 5, "PHP " . number_format($sale['cash_change'], 2), 0, 1, 'R');
    }

    function Footer()
    {
        // Move to 20mm from bottom
        $this->SetY(-15);

        // Separator
        $this->SetFont('Courier', '', 8);
        $this->Cell($this->w, 3, "- - - - - - - - - - - - - - - - - - - - - - - - -", 0, 1, 'C');
        $this->Ln(1);

        // Footer Text
        $this->SetFont('Courier', 'B', 9);
        $this->Cell($this->w, 4, "THANK YOU!", 0, 1, 'C');

        $this->SetFont('Courier', '', 8);
        $this->Cell($this->w, 4, "Please come again", 0, 1, 'C');
    }
}

// Create PDF with dynamic height
$pdf = new ReceiptPDF('P', 'mm', [80, $pageHeight]);
$pdf->SetMargins(2, 4, 2); // Minimal side margins
$pdf->SetAutoPageBreak(true);
$pdf->AddPage();
$pdf->ReceiptBody($sale, $items);

// Clean buffer and output
ob_end_clean();
$pdf->Output('I', 'Receipt_' . $sale['transaction_id'] . '.pdf');
