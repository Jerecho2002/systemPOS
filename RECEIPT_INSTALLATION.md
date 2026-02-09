# Thermal Receipt System - Installation Guide

## 📋 Overview
This system generates thermal receipt PDFs (80mm width) for your HPDS POS system, designed to work with thermal printers.

## 🔧 Installation Steps

### 1. Install Python Dependencies
```bash
pip install reportlab --break-system-packages
```

### 2. Upload Files to Your Server

Upload these files to your web root directory:
- `generate_receipt.py` - Python script for PDF generation
- `print_receipt.php` - PHP handler for receipt requests
- `sales.php` - Updated sales page with print button
- `database_receipt_method.php` - Contains the new database method

### 3. Add Database Method

Add the method from `database_receipt_method.php` to your existing `Database` class in `database/database.php`.

The method signature is:
```php
public function getSaleReceiptData(int $saleId)
```

This method:
- Fetches sale information
- Gets all items in the sale
- Joins with the `users` table to get cashier name
- Returns formatted data for the receipt

### 4. Set File Permissions

Make the Python script executable:
```bash
chmod +x /path/to/your/webroot/generate_receipt.py
```

### 5. Update File Paths (if needed)

In `print_receipt.php`, update the path to the Python script if it's not in the web root:
```php
$command = "python3 /var/www/html/generate_receipt.py {$escapedData} {$escapedOutput} 2>&1";
```

Change `/var/www/html/` to your actual web root path.

## 📄 Receipt Features

### Receipt Layout (80mm thermal width)
- ✅ Store header with name, address, phone
- ✅ Transaction ID and date/time
- ✅ Cashier name
- ✅ Customer name
- ✅ Itemized list with quantity, price, and totals
- ✅ Grand total
- ✅ Payment method and change (for cash)
- ✅ Professional footer message

### Store Information
Currently set to:
- **Name:** HPDS (Hanging Parrot Digital Solutions)
- **Address:** 123 Tech Avenue, Cebu City
- **Phone:** (032) 234-5678

To change this, edit the header section in `generate_receipt.py` (around line 97-100).

## 🖨️ Usage

### From Sales Table
1. Go to the Sales page
2. Find the transaction you want to print
3. Click the printer icon in the Actions column
4. Receipt PDF will open in a new tab

### Programmatic Usage
```php
// Redirect to print receipt
header("Location: print_receipt.php?sale_id=123");

// Or open in new window with JavaScript
window.open('print_receipt.php?sale_id=123', '_blank');
```

## 📐 Receipt Dimensions

- **Width:** 80mm (standard thermal receipt width)
- **Height:** Variable (adjusts automatically based on content)
- **Margins:** 5mm on all sides
- **Content Width:** 70mm

## 🎨 Customization

### Change Store Info
Edit lines 97-100 in `generate_receipt.py`:
```python
draw_centered("HPDS", TITLE_SIZE, bold=True)
draw_centered("Hanging Parrot Digital Solutions", HEADER_SIZE)
draw_centered("123 Tech Avenue, Cebu City", SMALL_SIZE)
draw_centered("Phone: (032) 234-5678", SMALL_SIZE)
```

### Change Footer Message
Edit lines 212-215 in `generate_receipt.py`:
```python
draw_centered("Thank you for your purchase!", NORMAL_SIZE)
draw_centered("Please come again!", SMALL_SIZE)
draw_centered("This serves as your official receipt", SMALL_SIZE)
```

### Adjust Font Sizes
Modify these constants at the top of `generate_receipt.py`:
```python
TITLE_SIZE = 12
HEADER_SIZE = 8
NORMAL_SIZE = 7
SMALL_SIZE = 6
```

## 🔍 Troubleshooting

### Receipt Not Generating
1. Check if Python script is executable: `ls -la generate_receipt.py`
2. Verify reportlab is installed: `python3 -c "import reportlab; print('OK')"`
3. Check PHP error logs for the actual error message

### Missing Sale Data
- Ensure the `getSaleReceiptData()` method is added to your Database class
- Verify the sale exists and is not deleted
- Check that sale_items records exist for the sale

### Font Issues
If you see boxes instead of characters:
- The script uses Helvetica (built-in font)
- Make sure you're not using special Unicode characters

### Path Issues
Update paths in `print_receipt.php` to match your server:
```php
$command = "python3 /your/actual/path/generate_receipt.py ...";
```

## 📱 Printing from Mobile/Web

The generated PDF can be:
- **Printed directly** from browser print dialog
- **Downloaded** and sent to thermal printer
- **Emailed** to customers
- **Saved** for records

For actual thermal printing, you may need to:
1. Use browser's print dialog and select your thermal printer
2. Or use specialized thermal printer software/drivers
3. Or integrate with a print server

## 🚀 Future Enhancements

Possible additions:
- Barcode/QR code for transaction ID
- VAT/Tax calculations
- Senior/PWD discount display
- Logo image support
- Multiple receipt copies
- Direct thermal printer integration via ESC/POS commands

## 📝 Notes

- Receipts are saved temporarily in `/mnt/user-data/outputs/`
- You can uncomment the `unlink()` line in `print_receipt.php` to auto-delete after serving
- Receipt filename format: `receipt_[transaction_id]_[timestamp].pdf`
