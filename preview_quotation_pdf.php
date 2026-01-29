<?php
ob_start(); // Start output buffering to prevent headers already sent error

require_once 'database/database.php';
require_once 'vendor/autoload.php'; // If using composer for TCPDF

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_GET['id']) || !isset($_SESSION['user_id'])) {
    die('Invalid request');
}

$pcBuilderId = $_GET['id'];
$database = new Database();

// Get PC Builder details
$data = $database->getPcBuilderDetails($pcBuilderId);

if (!$data) {
    die('Quotation not found');
}

class PDF extends FPDF
{
    private $shopName = "Hanging Parrot Digital Solutions";
    private $tagline = "Your Trusted Computer Solutions Partner";
    private $contactInfo = "Email: sales@hpds.ph | Phone: (02) 123-4567 | Website: www.hpds.ph";
    private $address = "123 Tech Street, Makati City, Metro Manila";

    function Header()
    {
        // Add logo if exists
        $logoPath = 'assets/images/HPDS-logo.png';
        if (file_exists($logoPath)) {
            $this->Image($logoPath, 10, 8, 40); // Logo at top-left
        }

        // Shop name and tagline
        $this->SetFont('Arial', 'B', 20);
        $this->SetTextColor(0, 51, 102); // Dark blue
        $this->SetXY(55, 10);
        $this->Cell(0, 8, $this->shopName, 0, 1);

        $this->SetFont('Arial', 'I', 10);
        $this->SetTextColor(102, 102, 102); // Gray
        $this->SetX(55);
        $this->Cell(0, 5, $this->tagline, 0, 1);

        // Contact info
        $this->SetFont('Arial', '', 8);
        $this->SetTextColor(0, 0, 0);
        $this->SetX(55);
        $this->Cell(0, 5, $this->contactInfo, 0, 1);

        $this->SetX(55);
        $this->Cell(0, 5, $this->address, 0, 1);

        $this->Ln(20);
    }

    function Footer()
    {
        $this->SetY(-20);

        // Decorative footer line
        $this->SetDrawColor(0, 102, 204);
        $this->SetLineWidth(0.5);
        $this->Line(10, $this->GetY(), 200, $this->GetY());

        // Footer content
        $this->SetFont('Arial', 'I', 9);
        $this->SetTextColor(102, 102, 102);
    }

    function QuotationInfo($data)
    {
        // Title with computer theme
        $this->SetFont('Arial', 'B', 18);
        $this->SetTextColor(0, 51, 102);
        $this->SetFillColor(240, 240, 245); // Light gray background
        $this->Cell(0, 10, 'COMPUTER SYSTEM QUOTATION', 0, 1, 'C', true);
        $this->Ln(5);

        // Quotation header box
        $this->SetDrawColor(0, 102, 204);
        $this->SetFillColor(245, 245, 255);
        $this->SetLineWidth(0.3);
        $this->Rect(10, $this->GetY(), 190, 30, 'DF');

        $this->SetFont('Arial', 'B', 12);
        $this->SetTextColor(255, 102, 0); // Orange
        $this->SetXY(15, $this->GetY() + 5);
        $this->Cell(0, 8, 'QUOTATION DETAILS', 0, 1);

        // System Name and Created on same line
        $this->SetFont('Arial', '', 10);
        $this->SetTextColor(0, 0, 0);

        // Left side: System Name - Reduced cell width
        $this->SetXY(15, $this->GetY());
        $this->Cell(25, 6, 'System Name:', 0, 0); // Changed from 40 to 25
        $this->SetFont('Arial', 'B', 10);
        $this->SetTextColor(0, 51, 102);
        $this->Cell(80, 6, $data['builder']['pc_builder_name'], 0, 0); // Increased to 80

        // Right side: Created - Reduced cell width
        $this->SetFont('Arial', '', 10);
        $this->SetTextColor(0, 0, 0);
        $this->SetXY(120, $this->GetY()); // Moved left from 130 to 120
        $this->Cell(20, 6, 'Created:', 0, 0); // Changed from 40 to 20
        $this->SetFont('Arial', 'B', 10);
        $this->SetTextColor(0, 0, 0);
        $this->Cell(0, 6, date('M d, Y', strtotime($data['builder']['created_at'])), 0, 1);

        // Created by below Created - Reduced cell width
        $this->SetFont('Arial', '', 10);
        $this->SetTextColor(0, 0, 0);
        $this->SetX(120); // Moved left from 130 to 120
        $this->Cell(20, 6, 'Created by:', 0, 0); // Changed from 40 to 20
        $this->SetFont('Arial', 'B', 10);
        $this->SetTextColor(0, 51, 102);
        $this->Cell(0, 6, $data['builder']['username'], 0, 1);

        $this->Ln(10);
    }

    function ItemsTable($title, $items)
    {
        if (empty($items)) {
            return;
        }

        // Section Title with computer icon indicator
        $this->SetFont('Arial', 'B', 14);
        $this->SetTextColor(0, 51, 102);
        $this->SetFillColor(230, 240, 255); // Light blue background
        $this->Cell(0, 10, ' ' . $title, 0, 1, 'L', true);
        $this->Ln(2);

        // Table Header with gradient effect
        $this->SetFont('Arial', 'B', 10);
        $this->SetFillColor(0, 51, 102); // Dark blue
        $this->SetTextColor(255, 255, 255);
        $this->Cell(80, 8, 'COMPONENT', 1, 0, 'L', true);
        $this->Cell(70, 8, 'SPECIFICATION / DESCRIPTION', 1, 0, 'L', true);
        $this->Cell(15, 8, 'QTY', 1, 0, 'C', true);
        $this->Cell(25, 8, 'PRICE', 1, 1, 'R', true);

        // Table Body
        $this->SetFont('Arial', '', 9);
        $fill = false;
        foreach ($items as $index => $item) {
            // Set alternating row colors
            if ($fill) {
                $this->SetFillColor(248, 248, 255); // Light blue
            } else {
                $this->SetFillColor(255, 255, 255); // White
            }
            $this->SetTextColor(0, 0, 0);

            // Alternate row colors
            $this->Cell(80, 7, $item['category_name'], 1, 0, 'L', $fill);
            $this->Cell(70, 7, $item['item_name'], 1, 0, 'L', $fill);
            $this->Cell(15, 7, $item['quantity'], 1, 0, 'C', $fill);
            $this->Cell(25, 7, 'PHP ' . number_format($item['line_total'], 2), 1, 1, 'R', $fill);

            $fill = !$fill;
        }

        $this->Ln(5);
    }

    function TotalSection($total)
    {
        // Summary section title
        $this->SetFont('Arial', 'B', 12);
        $this->SetTextColor(0, 51, 102);
        $this->SetFillColor(240, 240, 245);
        $this->Cell(0, 8, 'PRICE SUMMARY', 0, 1, 'L', true);
        $this->Ln(3);

        // Horizontal line
        $this->SetDrawColor(0, 102, 204);
        $this->SetLineWidth(0.5);
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        $this->Ln(5);

        // Grand Total - All aligned left
        $this->SetFont('Arial', 'B', 14);
        $this->SetTextColor(0, 51, 102);
        $this->Cell(40, 8, 'Grand Total:', 0, 0, 'L');

        $this->SetFont('Arial', 'B', 16);
        $this->SetTextColor(0, 0, 0); // Black font
        $this->Cell(0, 10, 'PHP ' . number_format($total, 2), 0, 1, 'L'); // No background

        $this->Ln(10);

        // Terms and conditions - Left aligned with proper indentation
        $this->SetFont('Arial', 'B', 10);
        $this->SetTextColor(0, 51, 102);
        $this->Cell(0, 6, 'Terms & Conditions:', 0, 1, 'L');

        $this->SetFont('Arial', 'I', 9);
        $this->SetTextColor(102, 102, 102);

        $terms = array(
            "1. Prices are subject to change based on component availability",
            "2. Warranty applies to all components as per manufacturer specifications",
            "3. Assembly and testing services included",
            "4. Free technical support for 30 days",
            "5. Installation of operating system and drivers included"
        );

        foreach ($terms as $term) {
            $this->Cell(10, 5, '', 0, 0); // Indentation
            $this->Cell(0, 5, $term, 0, 1, 'L');
        }
    }
}

// Create PDF
$pdf = new PDF();
$pdf->AddPage();

// Add quotation info
$pdf->QuotationInfo($data);

// Add PC Parts
$pdf->ItemsTable('PC COMPONENTS & HARDWARE', $data['pc_parts']);

// Add Accessories
$pdf->ItemsTable('PERIPHERALS & ACCESSORIES', $data['accessories']);

// Add Total
$pdf->TotalSection($data['grand_total']);

// Clear any previous output and send PDF
ob_end_clean(); // Clean the output buffer
$pdf->Output('I', 'HPDS_Quotation_' . $data['builder']['pc_builder_name'] . '.pdf');
