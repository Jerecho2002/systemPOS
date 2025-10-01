<?php
session_start();
class Database
{
    private $serverName = ("mysql:host=localhost;dbname=computer_store");
    private $userName = ("root");
    private $userPass = ("");
    private $fetchDefault = array(PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC);
    protected $conn;

    public function conn()
    {
        try {
            $this->conn = new PDO($this->serverName, $this->userName, $this->userPass, $this->fetchDefault);
            return $this->conn;
        } catch (PDOException $e) {
            echo "Error : " . $e->getMessage();
            exit;
        }
    }

    public function login_session()
    {
        if (!isset($_SESSION['login-success'])) {
            header("Location: login.php");
        }
    }

    public function register()
    {
        $errors = [];
        if (isset($_POST['register'])) {
            $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
            $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
            $role = filter_input(INPUT_POST, 'role', FILTER_SANITIZE_STRING);

            $query = $this->conn()->prepare("SELECT username FROM users WHERE username = ?");
            $query->execute([$username]);
            $check_username = $query->fetch();

            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            if (empty($username) || empty($password)) {
                $errors[] = "Do not leave the field empty";
            } else if (strlen($username) > 15) {
                $errors[] = "Username is too long, cannot be exceed to 15 characters";
            } else if (strlen($password) > 10) {
                $errors[] = "Password is too long, cannot be exceed to 10 characters";
            } else if (!preg_match("/^[a-zA-Z\s]+$/", $username)) {
                $errors[] = "Username cannot contain numbers";
            } else if (!preg_match("/^[a-zA-Z0-9\s]+$/", $password)) {
                $errors[] = "Password is invalid, contains numbers & letters only";
            }

            if ($check_username) {
                $errors[] = "Username is already taken.";
            }

            if (!empty($errors)) {
                $_SESSION['register-error'] = implode("<br><br>", $errors);
            } else {
                $sql = $this->conn()->prepare("INSERT INTO users (`username`, `password`, `role`) VALUES (?,?,?)");
                $sql->execute([$username, $hashedPassword, $role]);
                $_SESSION['register-success'] = "Successfully register " . $username . " you can now login";
            }
        }
    }

    public function login()
    {
        $errors = [];
        if (isset($_POST['login'])) {
            $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
            $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);

            $sql = $this->conn()->prepare("SELECT password, role, user_id FROM users WHERE username = ?");
            $sql->execute([$username]);
            $user = $sql->fetch();

            if ($user) {
                if (password_verify($password, $user['password'])) {
                    $_SESSION['login-success'] = $username;
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['user-role'] = $user['role'];
                    header("Location: dashboard.php");
                } else {
                    $errors[] = "Wrong password";
                }
            } else {
                $errors[] = "Wrong username";
            }

            if (empty($user) && empty($password)) {
                $errors[] = "Do not leave the field empty";
            }

            if (!empty($errors)) {
                $_SESSION['login-error'] = implode("<br>", $errors);
            }
        }
    }

    public function create_supplier()
    {
        $errors = [];
        if (isset($_POST['create_supplier'])) {
            $supplier_name = filter_input(INPUT_POST, 'supplier_name', FILTER_SANITIZE_STRING);
            $contact_number = filter_input(INPUT_POST, 'contact_number', FILTER_SANITIZE_STRING);
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_STRING);
            $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);

            $query = $this->conn()->prepare("SELECT supplier_name FROM suppliers WHERE supplier_name = ?");
            $query->execute([$supplier_name]);
            $check_supplier_name = $query->fetch();

            if (empty($supplier_name) || empty($contact_number) || empty($email) || empty($status)) {
                $errors[] = "Do not leave the field empty";
            } else if (strlen($supplier_name) > 30) {
                $errors[] = "Supplier company name is too long, cannot be exceed to 30 characters";
            } else if (!preg_match("/^\+?[0-9\- ]{7,20}$/", $contact_number)) {
                $errors[] = "Contact number format is invalid.";
            } else if ($status !== "1" && $status !== "0") {
                $errors[] = "Invalid status value.";
            }

            if ($check_supplier_name) {
                $errors[] = "Supplier company name is already taken.";
            }

            if (!empty($errors)) {
                $_SESSION['create-error'] = implode("<br><br>", $errors);
            } else {
                $sql = $this->conn()->prepare("INSERT INTO suppliers (`supplier_name`, `contact_number`, `email`, `status`) VALUES (?,?,?,?)");
                $sql->execute([$supplier_name, $contact_number, $email, $status]);
                $_SESSION['create-success'] = "Successfully added " . $supplier_name . " supplier";
            }
        }
    }

    public function update_supplier()
    {
        $errors = [];

        if (isset($_POST['update_supplier'])) {
            $supplier_id = filter_input(INPUT_POST, 'supplier_id', FILTER_SANITIZE_NUMBER_INT);
            $supplier_name = filter_input(INPUT_POST, 'supplier_name', FILTER_SANITIZE_STRING);
            $contact_number = filter_input(INPUT_POST, 'contact_number', FILTER_SANITIZE_STRING);
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_STRING);
            $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);

            if (empty($supplier_name) || empty($contact_number) || empty($email) || $status === '') {
                $errors[] = "Do not leave the field empty";
            } elseif (strlen($supplier_name) > 30) {
                $errors[] = "Supplier company name is too long. Max 30 characters allowed.";
            } elseif (!preg_match("/^\+?[0-9\- ]{7,20}$/", $contact_number)) {
                $errors[] = "Contact number format is invalid.";
            } elseif ($status !== "1" && $status !== "0") {
                $errors[] = "Invalid status value.";
            }

            $query = $this->conn()->prepare("SELECT supplier_name FROM suppliers WHERE supplier_name = ? AND supplier_id != ?");
            $query->execute([$supplier_name, $supplier_id]);
            $check_supplier_name = $query->fetch();

            if ($check_supplier_name) {
                $errors[] = "Supplier company name is already taken.";
            }

            if (!empty($errors)) {
                $_SESSION['create-error'] = implode("<br><br>", $errors);
            } else {
                $sql = $this->conn()->prepare("
                UPDATE suppliers 
                SET supplier_name = ?, contact_number = ?, email = ?, status = ?
                WHERE supplier_id = ?
            ");
                $sql->execute([$supplier_name, $contact_number, $email, $status, $supplier_id]);

                $_SESSION['create-success'] = "Successfully updated '{$supplier_name}' supplier.";
            }
        }
    }

    public function archive_supplier()
    {
        if (isset($_POST['archive_supplier'])) {
            $id = $_POST['supplier_id'];

            // Update the supplier's status to 0 (archived)
            $sql = $this->conn()->prepare("UPDATE suppliers SET status = 0 WHERE supplier_id = ?");
            $sql->execute([$id]);

            $_SESSION['create-success'] = "Supplier archived successfully.";
        }
    }



    public function select_suppliers()
    {
        $sql = $this->conn()->prepare("
        SELECT 
            s.*,
            COUNT(po.purchase_order_id) AS order_count,
            COALESCE(SUM(CASE WHEN po.status = 'Received' THEN po.grand_total ELSE 0 END), 0) AS total_spent,
            MAX(po.date) AS last_order_date
        FROM suppliers s
        LEFT JOIN purchase_orders po ON s.supplier_id = po.supplier_id
        GROUP BY s.supplier_id
    ");

        $sql->execute();
        $suppliers = $sql->fetchAll(PDO::FETCH_ASSOC);

        return $suppliers;
    }



    public function create_item()
    {
        $errors = [];

        if (isset($_POST['create_item'])) {
            $item_name = filter_input(INPUT_POST, 'item_name', FILTER_SANITIZE_STRING);
            $barcode = filter_input(INPUT_POST, 'barcode', FILTER_SANITIZE_STRING);
            $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
            $category_id = filter_input(INPUT_POST, 'category_id', FILTER_SANITIZE_NUMBER_INT);
            $supplier_id = filter_input(INPUT_POST, 'supplier_id', FILTER_SANITIZE_NUMBER_INT);
            $cost_price = filter_input(INPUT_POST, 'cost_price', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            $selling_price = filter_input(INPUT_POST, 'selling_price', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            $quantity = filter_input(INPUT_POST, 'quantity', FILTER_SANITIZE_NUMBER_INT);
            $min_stock = filter_input(INPUT_POST, 'min_stock', FILTER_SANITIZE_NUMBER_INT);

            date_default_timezone_set('Asia/Manila');
            $philippineDateTime = date('Y-m-d H:i:s');

            if (
                empty($item_name) || empty($barcode) || empty($category_id) || empty($supplier_id) ||
                $cost_price === null || $selling_price === null || $quantity === null || $min_stock === null
            ) {
                $errors[] = "All fields marked with * are required.";
            }

            if (!empty($item_name) && strlen($item_name) > 30) {
                $errors[] = "Item name cannot exceed 30 characters.";
            }

            if (!preg_match("/^[a-zA-Z0-9\- ]+$/", $barcode)) {
                $errors[] = "Barcode format is invalid. Only letters, numbers, spaces, and hyphens allowed.";
            }

            if (!is_numeric($cost_price) || $cost_price < 0) {
                $errors[] = "Cost price must be a positive number.";
            }

            if (!is_numeric($selling_price) || $selling_price <= 0) {
                $errors[] = "Selling price must be greater than 0.";
            }

            if (!is_numeric($quantity) || $quantity < 0) {
                $errors[] = "Quantity must be a non-negative number.";
            }

            if (!is_numeric($min_stock) || $min_stock < 0) {
                $errors[] = "Minimum stock must be a non-negative number.";
            }

            $query = $this->conn()->prepare("SELECT barcode FROM items WHERE barcode = ?");
            $query->execute([$barcode]);
            if ($query->fetch()) {
                $errors[] = "Barcode is already taken.";
            }

            if (!empty($errors)) {
                $_SESSION['create-error'] = implode("<br>", $errors);
                return;
            }

            $sql = $this->conn()->prepare("INSERT INTO items (item_name, barcode, description, category_id, supplier_id, cost_price, selling_price, quantity, min_stock, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $sql->execute([$item_name, $barcode, $description, $category_id, $supplier_id, $cost_price, $selling_price, $quantity, $min_stock, $philippineDateTime]);

            $_SESSION['create-success'] = "Successfully added item: " . htmlspecialchars($item_name);
        }
    }

    public function update_item()
    {
        $errors = [];

        if (isset($_POST['update_item'])) {
            $item_id = filter_input(INPUT_POST, 'item_id', FILTER_SANITIZE_NUMBER_INT);
            $item_name = filter_input(INPUT_POST, 'item_name', FILTER_SANITIZE_STRING);
            $barcode = filter_input(INPUT_POST, 'barcode', FILTER_SANITIZE_STRING);
            $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
            $category_id = filter_input(INPUT_POST, 'category_id', FILTER_SANITIZE_NUMBER_INT);
            $supplier_id = filter_input(INPUT_POST, 'supplier_id', FILTER_SANITIZE_NUMBER_INT);
            $cost_price = filter_input(INPUT_POST, 'cost_price', FILTER_VALIDATE_FLOAT);
            $selling_price = filter_input(INPUT_POST, 'selling_price', FILTER_VALIDATE_FLOAT);
            $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
            $min_stock = filter_input(INPUT_POST, 'min_stock', FILTER_VALIDATE_INT);

            date_default_timezone_set('Asia/Manila');
            $philippineDateTime = date('Y-m-d H:i:s');

            if (
                empty($item_name) || empty($barcode) || $selling_price === false ||
                $category_id === false || $supplier_id === false ||
                $cost_price === false || $quantity === false || $min_stock === false
            ) {
                $errors[] = "All fields marked with * are required and must be valid.";
            } elseif (strlen($item_name) > 100) {
                $errors[] = "Item name is too long. Max 100 characters allowed.";
            } elseif (strlen($barcode) > 50) {
                $errors[] = "Barcode is too long. Max 50 characters allowed.";
            }

            $stmt = $this->conn()->prepare("SELECT barcode FROM items WHERE barcode = ? AND item_id != ?");
            $stmt->execute([$barcode, $item_id]);
            if ($stmt->fetch()) {
                $errors[] = "Another item already uses this barcode.";
            }

            if (!empty($errors)) {
                $_SESSION['update-error'] = implode("<br><br>", $errors);
            } else {
                $sql = $this->conn()->prepare("
                UPDATE items 
                SET item_name = ?, barcode = ?, description = ?, category_id = ?, supplier_id = ?, cost_price = ?, selling_price = ?, quantity = ?, min_stock = ?, updated_at = ?
                WHERE item_id = ?
            ");
                $sql->execute([$item_name, $barcode, $description, $category_id, $supplier_id, $cost_price, $selling_price, $quantity, $min_stock, $philippineDateTime, $item_id]);
                $_SESSION['create-success'] = "Item '{$item_name}' has been updated successfully.";
            }
        }
    }

    public function delete_item()
    {
        if (isset($_POST['delete_item'])) {
            $item_id = filter_input(INPUT_POST, 'delete_item_id', FILTER_SANITIZE_NUMBER_INT);

            $delete = $this->conn()->prepare("DELETE FROM items WHERE item_id = ?");
            $delete->execute([$item_id]);

            $_SESSION['create-success'] = "Deleted product successfully.";
        }
    }

    public function select_items()
    {
        $sql = $this->conn()->prepare("SELECT 
        items.*, 
        categories.category_name,
        suppliers.supplier_name
        FROM items
        LEFT JOIN categories ON items.category_id = categories.category_id
        LEFT JOIN suppliers ON items.supplier_id = suppliers.supplier_id
        ");
        $sql->execute();
        $items = $sql->fetchAll();

        return $items;
    }

    public function create_purchase_order()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $conn = $this->conn();
            $supplier_id = filter_input(INPUT_POST, 'supplier_id', FILTER_SANITIZE_NUMBER_INT);
            $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);
            $item_ids = $_POST['item_id'] ?? [];
            $quantities = $_POST['quantity'] ?? [];
            $created_by = $_SESSION['user_id'] ?? null;

            $errors = [];

            if (!$supplier_id) {
                $errors[] = "Supplier is required.";
            }

            if (!$created_by) {
                $errors[] = "User not authenticated.";
            }

            if (empty($item_ids) || empty($quantities) || count($item_ids) !== count($quantities)) {
                $errors[] = "Invalid item entries.";
            }

            if (!empty($errors)) {
                $_SESSION['create-po-error'] = implode("<br>", $errors);
                return;
            }

            // Generate a unique PO number
            $checkPo = $conn->prepare("SELECT COUNT(*) FROM purchase_orders WHERE po_number = ?");
            $attempt = 0;
            do {
                $randomSuffix = rand(1000, 9999);
                $year = date('y');
                $month = date('m');
                $po_number = "PO-{$year}-{$month}-{$randomSuffix}";

                $checkPo->execute([$po_number]);
                $exists = $checkPo->fetchColumn() > 0;
                $attempt++;
            } while ($exists && $attempt < 10);

            if ($attempt >= 10) {
                $_SESSION['create-po-error'] = "Failed to generate unique PO number. Please try again.";
                return;
            }

            try {
                $conn->beginTransaction();

                $grand_total = 0;
                $po_items = [];

                $itemStmt = $conn->prepare("SELECT item_id, cost_price FROM items WHERE item_id = ?");

                for ($i = 0; $i < count($item_ids); $i++) {
                    $item_id = filter_var($item_ids[$i], FILTER_SANITIZE_NUMBER_INT);
                    $quantity = filter_var($quantities[$i], FILTER_SANITIZE_NUMBER_INT);

                    if ($item_id && $quantity > 0) {
                        $itemStmt->execute([$item_id]);
                        $item = $itemStmt->fetch();

                        if ($item) {
                            $unit_cost = $item['cost_price'];
                            $line_total = $unit_cost * $quantity;
                            $grand_total += $line_total;

                            $po_items[] = [
                                'item_id' => $item_id,
                                'quantity' => $quantity,
                                'unit_cost' => $unit_cost,
                                'line_total' => $line_total
                            ];
                        }
                    }
                }

                // Insert into purchase_orders
                $poStmt = $conn->prepare("
                INSERT INTO purchase_orders (po_number, supplier_id, grand_total, status, date, created_by)
                VALUES (?, ?, ?, ?, NOW(), ?)
            ");
                $poStmt->execute([$po_number, $supplier_id, $grand_total, $status, $created_by]);
                $purchase_order_id = $conn->lastInsertId();

                // Insert purchase_order_items
                $itemInsertStmt = $conn->prepare("
                INSERT INTO purchase_order_items (purchase_order_id, item_id, quantity, unit_cost, line_total)
                VALUES (?, ?, ?, ?, ?)
            ");

                foreach ($po_items as $po_item) {
                    $itemInsertStmt->execute([
                        $purchase_order_id,
                        $po_item['item_id'],
                        $po_item['quantity'],
                        $po_item['unit_cost'],
                        $po_item['line_total']
                    ]);
                }

                $conn->commit();
                $_SESSION['create-success'] = "Purchase order {$po_number} created successfully.";

            } catch (PDOException $e) {
                $conn->rollBack();
                $_SESSION['create-error'] = "Failed to create PO: " . $e->getMessage();
            }
        }
    }

    public function list_purchase_orders()
    {
        $conn = $this->conn();

        $stmt = $conn->prepare("
        SELECT po.*, s.supplier_name, u.username AS created_by
        FROM purchase_orders po
        JOIN suppliers s ON po.supplier_id = s.supplier_id
        JOIN users u ON po.created_by = u.user_id
        ORDER BY po.date DESC
    ");
        $stmt->execute();
        $purchaseOrders = $stmt->fetchAll();

        foreach ($purchaseOrders as &$po) {
            $itemStmt = $conn->prepare("
            SELECT 
                i.item_name, 
                poi.quantity, 
                poi.unit_cost, 
                poi.line_total
            FROM purchase_order_items poi
            JOIN items i ON poi.item_id = i.item_id
            WHERE poi.purchase_order_id = ?
        ");
            $itemStmt->execute([$po['purchase_order_id']]);
            $po['items'] = $itemStmt->fetchAll();
        }

        return $purchaseOrders;
    }

    public function receive_purchase_order()
    {
        if (isset($_POST['receive_po'])) {
            $po_id = $_POST['receive_po_id'];
            $conn = $this->conn();

            $statusCheck = $conn->prepare("SELECT status FROM purchase_orders WHERE purchase_order_id = ?");
            $statusCheck->execute([$po_id]);
            $currentStatus = $statusCheck->fetchColumn();

            if (!$currentStatus) {
                $_SESSION['create-error'] = "Purchase order not found.";
                return;
            }

            if (strtolower($currentStatus) === 'received') {
                $_SESSION['create-error'] = "Purchase order #{$po_id} has already been received.";
                return;
            }

            $stmt = $conn->prepare("
            SELECT item_id, quantity
            FROM purchase_order_items
            WHERE purchase_order_id = ?
        ");
            $stmt->execute([$po_id]);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($items as $item) {
                $itemId = $item['item_id'];
                $quantityToAdd = $item['quantity'];

                $updateStmt = $conn->prepare("
                UPDATE items
                SET quantity = quantity + ?
                WHERE item_id = ?
            ");
                $updateStmt->execute([$quantityToAdd, $itemId]);
            }

            $updatePo = $conn->prepare("
            UPDATE purchase_orders
            SET status = 'Received'
            WHERE purchase_order_id = ?
        ");
            $updatePo->execute([$po_id]);

            $_SESSION['create-success'] = "Inventory restocked successfully from PO #{$po_id}.";
        }
    }

    public function cancel_purchase_order()
    {
        if (isset($_POST['cancel_po'])) {
            $po_id = $_POST['cancel_po_id'];
            $conn = $this->conn();

            $stmt = $conn->prepare("UPDATE purchase_orders SET status = 'Cancelled' WHERE purchase_order_id = ?");
            $stmt->execute([$po_id]);

            $_SESSION['create-success'] = "Purchase order #{$po_id} cancelled successfully.";
        }
    }

    public function archive_purchase_order()
    {
        if (isset($_POST['archive_purchase_order'])) {
            $id = $_POST['purchase_order_id'];

            // Update the status to 0 (archived)
            $archivePO = $this->conn()->prepare("UPDATE purchase_orders SET is_active = 0 WHERE purchase_order_id = ?");
            $archivePO->execute([$id]);

            $_SESSION['create-success'] = "Purchase order archived successfully.";
        }
    }



    public function select_purchase_orders()
    {
        $sql = $this->conn()->prepare("
            SELECT 
                po.*, 
                s.supplier_name 
            FROM 
                purchase_orders po
            JOIN 
                suppliers s ON po.supplier_id = s.supplier_id
        ");

        $sql->execute();
        $purchase_orders = $sql->fetchAll();

        return $purchase_orders;
    }


    public function select_purchase_order_items()
    {
        $sql = $this->conn()->prepare("SELECT * from purchase_order_items");
        $sql->execute();
        $purchase_order_items = $sql->fetchAll();

        return $purchase_order_items;
    }

    public function item_stock_adjust()
    {
        $errors = [];

        if (isset($_POST['adjust_stock_submit'])) {
            $item_id = filter_input(INPUT_POST, 'item_id', FILTER_VALIDATE_INT);
            $adjust_qty = filter_input(INPUT_POST, 'adjust_qty', FILTER_VALIDATE_INT);
            $reason = filter_input(INPUT_POST, 'reason_adjustment', FILTER_SANITIZE_STRING);
            $adjust_by = $_SESSION['user_id'] ?? null;

            date_default_timezone_set('Asia/Manila');
            $philippineDateTime = date('Y-m-d H:i:s');

            if (!$item_id || $adjust_qty === false || empty($reason) || !$adjust_by) {
                $errors[] = "Please fill in all required fields correctly.";
            } else {
                $conn = $this->conn();

                // Fetch current quantity
                $stmt = $conn->prepare("SELECT quantity FROM items WHERE item_id = ?");
                $stmt->execute([$item_id]);
                $item = $stmt->fetch();

                if (!$item) {
                    $errors[] = "Item not found.";
                } else {
                    $previous_quantity = (int) $item['quantity'];
                    $new_quantity = $previous_quantity + $adjust_qty;

                }
            }

            if (!empty($errors)) {
                $_SESSION['adjust-error'] = implode("<br><br>", $errors);
            } else {
                try {
                    $conn->beginTransaction();

                    // Insert adjustment record
                    $stmt = $conn->prepare("
                    INSERT INTO item_stock_adjustment 
                    (item_id, previous_quantity, new_quantity, reason_adjustment, adjust_by, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                    $stmt->execute([$item_id, $previous_quantity, $new_quantity, $reason, $adjust_by, $philippineDateTime]);

                    // Update item quantity
                    $stmt = $conn->prepare("UPDATE items SET quantity = ? WHERE item_id = ?");
                    $stmt->execute([$new_quantity, $item_id]);

                    $conn->commit();

                    $_SESSION['create-success'] = "Stock successfully adjusted.";
                } catch (PDOException $e) {
                    if ($conn->inTransaction()) {
                        $conn->rollBack();
                    }
                    $_SESSION['create-error'] = "Database error: " . $e->getMessage();
                }
            }
        }
    }

    public function select_stock_adjustment()
    {
        $sql = $this->conn()->prepare("
            SELECT 
                isa.*, 
                i.item_name,
                u.username 
            FROM 
                item_stock_adjustment isa
            JOIN 
                items i ON isa.item_id = i.item_id
            JOIN 
                users u ON isa.adjust_by = u.user_id
            ORDER BY isa.created_at DESC LIMIT 3
        ");

        $sql->execute();
        $stock_adjustment = $sql->fetchAll();

        return $stock_adjustment;
    }

    public function add_to_cart()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $item_id = filter_input(INPUT_POST, 'item_id', FILTER_SANITIZE_NUMBER_INT);
            $quantity = filter_input(INPUT_POST, 'quantity', FILTER_SANITIZE_NUMBER_INT) ?? 1;

            if (!$item_id || $quantity <= 0) {
                $_SESSION['sale-error'] = "Invalid item or quantity.";
                return;
            }

            $stmt = $this->conn()->prepare("SELECT item_id, item_name, selling_price, quantity as stock FROM items WHERE item_id = ?");
            $stmt->execute([$item_id]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$item) {
                $_SESSION['sale-error'] = "Item not found.";
                return;
            }

            if (!isset($_SESSION['cart'])) {
                $_SESSION['cart'] = [];
            }

            // Check if already in cart
            if (isset($_SESSION['cart'][$item_id])) {
                $_SESSION['cart'][$item_id]['quantity'] += $quantity;
            } else {
                $_SESSION['cart'][$item_id] = [
                    'item_id' => $item_id,
                    'name' => $item['item_name'],
                    'quantity' => $quantity,
                    'unit_price' => $item['selling_price'],
                    'line_total' => $item['selling_price'] * $quantity
                ];
            }

            // Recalculate line total
            $_SESSION['cart'][$item_id]['line_total'] = $_SESSION['cart'][$item_id]['unit_price'] * $_SESSION['cart'][$item_id]['quantity'];
        }
    }

    public function process_sale()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $cart = $_SESSION['cart'] ?? [];
            $cash_received = filter_input(INPUT_POST, 'cash_received', FILTER_VALIDATE_FLOAT);
            $user_id = $_SESSION['user_id'] ?? null;

            if (empty($cart)) {
                $_SESSION['sale-error'] = "Cart is empty.";
                return;
            }

            if (!$cash_received || $cash_received <= 0) {
                $_SESSION['sale-error'] = "Invalid cash received.";
                return;
            }

            $total = 0;
            foreach ($cart as $item) {
                $total += $item['line_total'];
            }

            $grand_total = $total;
            $change = $cash_received - $grand_total;

            if ($change < 0) {
                $_SESSION['sale-error'] = "Insufficient cash received.";
                return;
            }

            try {
                $conn = $this->conn();
                $conn->beginTransaction();

                // Generate a unique transaction ID (TXN-YYMM-RAND)
                $checkTransaction = $conn->prepare("SELECT COUNT(*) FROM sales WHERE transaction_id = ?");
                $attempt = 0;
                do {
                    $randomSuffix = rand(1000, 9999); // 4-digit random number
                    $year = date('y'); // Last two digits of the year
                    $month = date('m'); // Current month
                    $transaction_id = "TXN-{$year}{$month}-{$randomSuffix}"; // Example: TXN-2509-1234

                    // Check if the generated transaction_id already exists
                    $checkTransaction->execute([$transaction_id]);
                    $exists = $checkTransaction->fetchColumn() > 0;
                    $attempt++;
                } while ($exists && $attempt < 10); // Try up to 10 times to generate a unique ID

                if ($attempt >= 10) {
                    $_SESSION['sale-error'] = "Failed to generate a unique transaction ID. Please try again.";
                    return;
                }

                date_default_timezone_set('Asia/Manila');
                $philippineDateTime = date('Y-m-d H:i:s');

                // Insert into sales table, including transaction_id
                $stmt = $conn->prepare("
                INSERT INTO sales (transaction_id, customer_name, grand_total, cash_received, cash_change, date, sold_by)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
                $stmt->execute([
                    $transaction_id, // Unique transaction_id
                    'Walk-in',       // Customer name
                    $grand_total,    // Grand total
                    $cash_received,  // Cash received
                    $change,         // Cash change
                    $philippineDateTime,
                    $user_id         // Sold by (user ID)
                ]);

                $sale_id = $conn->lastInsertId();

                // Insert sale items
                $itemStmt = $conn->prepare("
                INSERT INTO sale_items (sale_id, item_id, quantity, unit_price, line_total, created_at)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
                $stockUpdate = $conn->prepare("
                UPDATE items SET quantity = quantity - ? WHERE item_id = ?
            ");

                foreach ($cart as $item) {
                    $itemStmt->execute([
                        $sale_id,
                        $item['item_id'],
                        $item['quantity'],
                        $item['unit_price'],
                        $item['line_total'],
                        $philippineDateTime
                    ]);

                    $stockUpdate->execute([
                        $item['quantity'],
                        $item['item_id']
                    ]);
                }

                $conn->commit();
                unset($_SESSION['cart']);
                $_SESSION['sale-success'] = "Sale processed successfully. Change: ₱" . number_format($change, 2);

            } catch (PDOException $e) {
                $conn->rollBack();
                $_SESSION['sale-error'] = "Transaction failed: " . $e->getMessage();
            }
        }
    }

    public function getTodaysSalesStats()
    {
        date_default_timezone_set('Asia/Manila');

        $todayStart = date('Y-m-d') . ' 00:00:00';
        $todayEnd = date('Y-m-d') . ' 23:59:59';

        $yesterdayStart = date('Y-m-d', strtotime('-1 day')) . ' 00:00:00';
        $yesterdayEnd = date('Y-m-d', strtotime('-1 day')) . ' 23:59:59';

        try {
            $conn = $this->conn();

            $stmt = $conn->prepare("
            SELECT 
                -- Today's
                SUM(CASE WHEN date BETWEEN :todayStart AND :todayEnd THEN grand_total ELSE 0 END) AS today_revenue,
                COUNT(CASE WHEN date BETWEEN :todayStart AND :todayEnd THEN 1 END) AS today_count,
                
                -- Yesterday's
                SUM(CASE WHEN date BETWEEN :yesterdayStart AND :yesterdayEnd THEN grand_total ELSE 0 END) AS yesterday_revenue
            FROM sales
        ");

            $stmt->execute([
                ':todayStart' => $todayStart,
                ':todayEnd' => $todayEnd,
                ':yesterdayStart' => $yesterdayStart,
                ':yesterdayEnd' => $yesterdayEnd
            ]);

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            $todayCount = (int) $result['today_count'];
            $todayRevenue = (float) $result['today_revenue'];
            $yesterdayRevenue = (float) $result['yesterday_revenue'];

            $avgTransaction = $todayCount > 0 ? $todayRevenue / $todayCount : 0;

            // Calculate growth from yesterday
            if ($yesterdayRevenue > 0) {
                $growthPercent = (($todayRevenue - $yesterdayRevenue) / $yesterdayRevenue) * 100;
            } else {
                $growthPercent = $todayRevenue > 0 ? 100 : 0;
            }

            return [
                'transaction_count' => $todayCount,
                'today_revenue' => $todayRevenue,
                'avg_transaction' => $avgTransaction,
                'growth_percent' => round($growthPercent)
            ];

        } catch (PDOException $e) {
            return [
                'transaction_count' => 0,
                'today_revenue' => 0,
                'avg_transaction' => 0,
                'growth_percent' => 0
            ];
        }
    }




    public function remove_from_cart()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_item_id'])) {
            $item_id = filter_input(INPUT_POST, 'remove_item_id', FILTER_SANITIZE_NUMBER_INT);

            if ($item_id && isset($_SESSION['cart'][$item_id])) {
                unset($_SESSION['cart'][$item_id]);
                $_SESSION['sale-success'] = "Item removed from cart.";
            }
        }
    }


    public function create_category()
    {
        $errors = [];
        if (isset($_POST['create_category'])) {
            $category_name = filter_input(INPUT_POST, 'category_name', FILTER_SANITIZE_STRING);

            $query = $this->conn()->prepare("SELECT category_name FROM categories WHERE category_name = ?");
            $query->execute([$category_name]);
            $check_category_name = $query->fetch();

            if (empty($category_name)) {
                $errors[] = "Do not leave the field empty";
            } else if (strlen($category_name) > 30) {
                $errors[] = "Category name is too long, cannot be exceed to 30 characters";
            }

            if ($check_category_name) {
                $errors[] = "Category name is already taken.";
            }

            if (!empty($errors)) {
                $_SESSION['create-error'] = implode("<br><br>", $errors);
            } else {
                $sql = $this->conn()->prepare("INSERT INTO categories (`category_name`) VALUES (?)");
                $sql->execute([$category_name]);
                $_SESSION['create-success'] = "Successfully added " . $category_name . " to category";
            }
        }
    }

    public function select_categories()
    {
        $sql = $this->conn()->prepare("SELECT * FROM categories");
        $sql->execute();
        $categories = $sql->fetchAll();

        return $categories;
    }
}
$database = new Database();