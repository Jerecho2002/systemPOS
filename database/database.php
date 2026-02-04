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
        if ($this->conn instanceof PDO) {
            return $this->conn;
        }

        try {
            $this->conn = new PDO(
                $this->serverName,
                $this->userName,
                $this->userPass,
                $this->fetchDefault
            );

            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            return $this->conn;
        } catch (PDOException $e) {
            die("Database connection error: " . $e->getMessage());
        }
    }


    public function login_session()
    {
        if (!isset($_SESSION['login-success'])) {
            header("Location: index .php");
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

    public function getTotalSuppliersCount($search = '')
    {
        $sql = "SELECT COUNT(*) as total FROM (
            SELECT s.supplier_id
            FROM suppliers s
            LEFT JOIN purchase_orders po ON s.supplier_id = po.supplier_id
            WHERE s.status = 1";

        $params = [];

        if ($search !== '') {
            $sql .= " AND s.supplier_name LIKE :search";
            $params[':search'] = '%' . $search . '%';
        }

        $sql .= " GROUP BY s.supplier_id
        ) as counted_suppliers";

        $stmt = $this->conn()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function select_suppliers_paginated($offset, $perPage, $search = '')
    {
        $sql = "SELECT 
            s.*,
            COUNT(DISTINCT po.purchase_order_id) as order_count,
            MAX(po.date) as last_order_date,
            COALESCE(SUM(po.grand_total), 0) as total_spent
        FROM suppliers s
        LEFT JOIN purchase_orders po ON s.supplier_id = po.supplier_id
        WHERE s.status = 1";

        $params = [];

        if ($search !== '') {
            $sql .= " AND s.supplier_name LIKE :search";
            $params[':search'] = '%' . $search . '%';
        }

        $sql .= " GROUP BY s.supplier_id
            ORDER BY s.supplier_name ASC 
            LIMIT :offset, :perPage";

        $stmt = $this->conn()->prepare($sql);

        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v, PDO::PARAM_STR);
        }
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':perPage', $perPage, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function select_all_suppliers_for_stats()
    {
        $sql = "SELECT 
            s.*,
            COUNT(DISTINCT po.purchase_order_id) as order_count,
            MAX(po.date) as last_order_date,
            COALESCE(SUM(po.grand_total), 0) as total_spent
        FROM suppliers s
        LEFT JOIN purchase_orders po ON s.supplier_id = po.supplier_id
        GROUP BY s.supplier_id
        ORDER BY s.supplier_name ASC";

        $stmt = $this->conn()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create_item()
    {
        $errors = [];

        if (isset($_POST['create_item'])) {
            $item_name = $_POST['item_name'];
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
            $item_name = $_POST['item_name'];
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

    public function archive_item()
    {
        if (isset($_POST['archive_item'])) {
            $item_id = filter_input(INPUT_POST, 'archive_item_id', FILTER_SANITIZE_NUMBER_INT);

            $archive = $this->conn()->prepare("UPDATE items SET is_deleted = 1 WHERE item_id = ?");
            $archive->execute([$item_id]);

            $_SESSION['create-success'] = "Archived product successfully.";
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

    public function getTotalItemsCount($search = '', $categoryFilter = '', $priceFilter = '')
    {
        $sql = "SELECT COUNT(*) as total FROM items
            LEFT JOIN categories ON items.category_id = categories.category_id
            LEFT JOIN suppliers ON items.supplier_id = suppliers.supplier_id
            WHERE items.quantity > 0";

        $params = [];

        // Search filter
        if ($search !== '') {
            $sql .= " AND (items.item_name LIKE :search 
                  OR items.barcode LIKE :search 
                  OR suppliers.supplier_name LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }

        // Category filter
        if ($categoryFilter !== '') {
            $sql .= " AND categories.category_name = :category";
            $params[':category'] = $categoryFilter;
        }

        // Price filter
        if ($priceFilter === 'below') {
            $sql .= " AND items.selling_price <= 5000";
        } elseif ($priceFilter === 'above') {
            $sql .= " AND items.selling_price > 5000";
        }

        $stmt = $this->conn()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    // Get paginated items with filters
    public function select_items_paginated($offset, $perPage, $search = '', $categoryFilter = '', $priceFilter = '')
    {
        $sql = "SELECT 
            items.*, 
            categories.category_name,
            suppliers.supplier_name
            FROM items
            LEFT JOIN categories ON items.category_id = categories.category_id
            LEFT JOIN suppliers ON items.supplier_id = suppliers.supplier_id
            WHERE items.quantity > 0
            AND items.is_deleted = 0";

        $params = [];

        // Search filter
        if ($search !== '') {
            $sql .= " AND (items.item_name LIKE :search 
                  OR items.barcode LIKE :search 
                  OR suppliers.supplier_name LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }

        // Category filter
        if ($categoryFilter !== '') {
            $sql .= " AND categories.category_name = :category";
            $params[':category'] = $categoryFilter;
        }

        // Price filter
        if ($priceFilter === 'below') {
            $sql .= " AND items.selling_price <= 5000";
        } elseif ($priceFilter === 'above') {
            $sql .= " AND items.selling_price > 5000";
        }

        $sql .= " ORDER BY items.item_name ASC LIMIT :offset, :perPage";

        $stmt = $this->conn()->prepare($sql);

        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v, PDO::PARAM_STR);
        }
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':perPage', $perPage, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
            $archivePO = $this->conn()->prepare("UPDATE purchase_orders SET is_deleted = 1 WHERE purchase_order_id = ?");
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

    public function getTotalPurchaseOrdersCount($search = '', $statusFilter = '')
    {
        $sql = "SELECT COUNT(*) as total FROM purchase_orders po
            LEFT JOIN suppliers s ON po.supplier_id = s.supplier_id
            WHERE po.is_deleted = 0";

        $params = [];

        // Search filter
        if ($search !== '') {
            $sql .= " AND (po.po_number LIKE :search 
                  OR s.supplier_name LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }

        // Status filter
        if ($statusFilter !== '') {
            $sql .= " AND po.status = :status";
            $params[':status'] = $statusFilter;
        }

        $stmt = $this->conn()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function list_purchase_orders_paginated($offset, $perPage, $search = '', $statusFilter = '')
    {
        $sql = "SELECT 
            po.purchase_order_id,
            po.po_number,
            po.supplier_id,
            po.status,
            po.grand_total,
            po.date,
            po.created_by,
            s.supplier_name,
            GROUP_CONCAT(
                JSON_OBJECT(
                    'item_name', i.item_name,
                    'quantity', poi.quantity,
                    'unit_cost', poi.unit_cost,
                    'line_total', poi.line_total
                )
            ) as items
        FROM purchase_orders po
        LEFT JOIN suppliers s ON po.supplier_id = s.supplier_id
        LEFT JOIN purchase_order_items poi ON po.purchase_order_id = poi.purchase_order_id
        LEFT JOIN items i ON poi.item_id = i.item_id
        WHERE po.is_deleted = 0";

        $params = [];

        // Search filter
        if ($search !== '') {
            $sql .= " AND (po.po_number LIKE :search 
                  OR s.supplier_name LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }

        // Status filter
        if ($statusFilter !== '') {
            $sql .= " AND po.status = :status";
            $params[':status'] = $statusFilter;
        }

        $sql .= " GROUP BY po.purchase_order_id
            ORDER BY po.date DESC 
            LIMIT :offset, :perPage";

        $stmt = $this->conn()->prepare($sql);

        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v, PDO::PARAM_STR);
        }
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':perPage', $perPage, PDO::PARAM_INT);

        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($results as &$po) {
            if ($po['items']) {
                $po['items'] = json_decode('[' . $po['items'] . ']', true);
            } else {
                $po['items'] = [];
            }
        }

        return $results;
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
            $pc_builder_id = filter_input(INPUT_POST, 'pc_builder_id', FILTER_SANITIZE_NUMBER_INT);
            $quantity = filter_input(INPUT_POST, 'quantity', FILTER_SANITIZE_NUMBER_INT) ?? 1;

            if ($quantity <= 0) {
                $_SESSION['sale-error'] = "Invalid quantity.";
                return;
            }

            if (!isset($_SESSION['cart'])) {
                $_SESSION['cart'] = [];
            }

            // ✅ CASE 1: Add a PC Builder to cart
            if ($pc_builder_id) {
                $pc = $this->getPCBuilderById($pc_builder_id);

                if (!$pc) {
                    $_SESSION['sale-error'] = "PC Builder not found.";
                    return;
                }

                $cartKey = 'pcb_' . $pc_builder_id;

                if (isset($_SESSION['cart'][$cartKey])) {
                    $_SESSION['cart'][$cartKey]['quantity'] += $quantity;
                } else {
                    $_SESSION['cart'][$cartKey] = [
                        'pc_builder_id' => $pc_builder_id,
                        'name' => $pc['pc_builder_name'],
                        'quantity' => $quantity,
                        'unit_price' => $pc['total_price'],
                        'line_total' => $pc['total_price'] * $quantity,
                        'is_pc_builder' => true
                    ];
                }

                // Recalculate line total
                $_SESSION['cart'][$cartKey]['line_total'] =
                    $_SESSION['cart'][$cartKey]['unit_price'] * $_SESSION['cart'][$cartKey]['quantity'];

                return;
            }

            // ✅ CASE 2: Add a regular item to cart
            if ($item_id) {
                $stmt = $this->conn()->prepare("SELECT item_id, item_name, selling_price, quantity as stock FROM items WHERE item_id = ?");
                $stmt->execute([$item_id]);
                $item = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$item) {
                    $_SESSION['sale-error'] = "Item not found.";
                    return;
                }

                $cartKey = 'item_' . $item_id;

                if (isset($_SESSION['cart'][$cartKey])) {
                    $_SESSION['cart'][$cartKey]['quantity'] += $quantity;
                } else {
                    $_SESSION['cart'][$cartKey] = [
                        'item_id' => $item_id,
                        'name' => $item['item_name'],
                        'quantity' => $quantity,
                        'unit_price' => $item['selling_price'],
                        'line_total' => $item['selling_price'] * $quantity,
                        'is_pc_builder' => false
                    ];
                }

                // Recalculate line total
                $_SESSION['cart'][$cartKey]['line_total'] =
                    $_SESSION['cart'][$cartKey]['unit_price'] * $_SESSION['cart'][$cartKey]['quantity'];
            } else {
                $_SESSION['sale-error'] = "No item or PC Builder selected.";
            }
        }
    }


    public function process_sale()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $cart = $_SESSION['cart'] ?? [];
            $cash_received = filter_input(INPUT_POST, 'cash_received', FILTER_VALIDATE_FLOAT);
            $customer_name = filter_input(INPUT_POST, 'customer', FILTER_SANITIZE_STRING);
            $payment_method = filter_input(INPUT_POST, 'payment_method', FILTER_SANITIZE_STRING);
            $user_id = $_SESSION['user_id'] ?? null;

            if (empty($cart)) {
                $_SESSION['sale-error'] = "Cart is empty.";
                return;
            }

            if (!$cash_received || $cash_received <= 0) {
                $_SESSION['sale-error'] = "Invalid cash received.";
                return;
            }

            // Compute totals
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

                // ✅ Check stock availability before processing
                foreach ($cart as $item) {
                    // For PC Builder
                    if (isset($item['is_pc_builder']) && $item['is_pc_builder'] === true) {
                        $pc = $this->getPCBuilderById($item['pc_builder_id']);

                        $componentIds = [
                            $pc['cpu_id'] ?? null,
                            $pc['gpu_id'] ?? null,
                            $pc['ram_id'] ?? null,
                            $pc['motherboard_id'] ?? null,
                            $pc['storage_id'] ?? null,
                            $pc['psu_id'] ?? null,
                            $pc['case_id'] ?? null
                        ];

                        foreach ($componentIds as $compId) {
                            if ($compId) {
                                $checkStock = $conn->prepare("SELECT quantity FROM items WHERE item_id = ?");
                                $checkStock->execute([$compId]);
                                $stock = $checkStock->fetchColumn();

                                if ($stock === false) {
                                    $_SESSION['sale-error'] = "Component not found in inventory.";
                                    $conn->rollBack();
                                    return;
                                }

                                if ($stock <= 0) {
                                    // Fetch component name
                                    $getName = $conn->prepare("SELECT item_name FROM items WHERE item_id = ?");
                                    $getName->execute([$compId]);
                                    $componentName = $getName->fetchColumn();

                                    $_SESSION['sale-error'] = "Cannot proceed. Component '{$componentName}' is out of stock.";
                                    $conn->rollBack();
                                    return;
                                }


                                if ($stock < $item['quantity']) {
                                    $_SESSION['sale-error'] = "Not enough stock for one or more PC Builder components.";
                                    $conn->rollBack();
                                    return;
                                }
                            }
                        }
                    }
                    // For regular items
                    else {
                        $checkStock = $conn->prepare("SELECT quantity FROM items WHERE item_id = ?");
                        $checkStock->execute([$item['item_id']]);
                        $stock = $checkStock->fetchColumn();

                        if ($stock === false) {
                            $_SESSION['sale-error'] = "Item not found in inventory.";
                            $conn->rollBack();
                            return;
                        }

                        if ($stock <= 0) {
                            $_SESSION['sale-error'] = "Cannot proceed. One or more items are out of stock.";
                            $conn->rollBack();
                            return;
                        }

                        if ($stock < $item['quantity']) {
                            $_SESSION['sale-error'] = "Not enough stock for one or more items.";
                            $conn->rollBack();
                            return;
                        }
                    }
                }

                // ✅ Generate unique transaction ID
                $checkTransaction = $conn->prepare("SELECT COUNT(*) FROM sales WHERE transaction_id = ?");
                $attempt = 0;
                do {
                    $randomSuffix = rand(1000, 9999);
                    $year = date('y');
                    $month = date('m');
                    $transaction_id = "TXN-{$year}{$month}-{$randomSuffix}";
                    $checkTransaction->execute([$transaction_id]);
                    $exists = $checkTransaction->fetchColumn() > 0;
                    $attempt++;
                } while ($exists && $attempt < 10);

                if ($attempt >= 10) {
                    $_SESSION['sale-error'] = "Failed to generate a unique transaction ID.";
                    return;
                }

                date_default_timezone_set('Asia/Manila');
                $philippineDateTime = date('Y-m-d H:i:s');

                // ✅ Insert into `sales`
                $stmt = $conn->prepare("
                INSERT INTO sales (transaction_id, customer_name, grand_total, cash_received, cash_change, payment_method, date, sold_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
                $stmt->execute([
                    $transaction_id,
                    $customer_name,
                    $grand_total,
                    $cash_received,
                    $change,
                    $payment_method,
                    $philippineDateTime,
                    $user_id
                ]);

                $sale_id = $conn->lastInsertId();

                // ✅ Prepare statements
                $itemStmt = $conn->prepare("
                INSERT INTO sale_items (sale_id, item_id, quantity, unit_price, line_total, created_at)
                VALUES (?, ?, ?, ?, ?, ?)
            ");

                $stockUpdate = $conn->prepare("
                UPDATE items SET quantity = quantity - ? WHERE item_id = ?
            ");

                $pcBuilderStmt = $conn->prepare("
                INSERT INTO sale_pc_builders (sale_id, pc_builder_id, pc_builder_name, selling_price, quantity, line_total, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");

                // ✅ Loop through cart
                foreach ($cart as $item) {
                    // CASE 1: PC Builder
                    if (isset($item['is_pc_builder']) && $item['is_pc_builder'] === true) {

                        // Fetch PC Builder details including component IDs
                        $pc = $this->getPCBuilderById($item['pc_builder_id']);

                        $pcBuilderStmt->execute([
                            $sale_id,
                            $item['pc_builder_id'],
                            $item['name'],
                            $item['unit_price'], // PC Builder total price per unit
                            $item['quantity'],
                            $item['line_total'],
                            $philippineDateTime
                        ]);

                        // ✅ Deduct stock for each PC component
                        $componentIds = [
                            $pc['cpu_id'] ?? null,
                            $pc['gpu_id'] ?? null,
                            $pc['ram_id'] ?? null,
                            $pc['motherboard_id'] ?? null,
                            $pc['storage_id'] ?? null,
                            $pc['psu_id'] ?? null,
                            $pc['case_id'] ?? null
                        ];

                        foreach ($componentIds as $compId) {
                            if ($compId) {
                                $stockUpdate->execute([
                                    $item['quantity'],
                                    $compId
                                ]);
                            }
                        }
                    }
                    // CASE 2: Regular Item
                    else {
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
                }

                // ✅ Commit transaction
                $conn->commit();
                unset($_SESSION['cart']);

                $_SESSION['sale-success'] = "Sale processed successfully. Change: ₱" . number_format($change, 2);
            } catch (PDOException $e) {
                if ($conn->inTransaction()) {
                    $conn->rollBack();
                }
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
                'period' => 'Daily',
                'transaction_count' => $todayCount,
                'total_sales' => $todayRevenue,
                'avg_transaction' => $avgTransaction,
                'growth_percent' => round($growthPercent)
            ];
        } catch (PDOException $e) {
            return [
                'period' => 'Daily',
                'transaction_count' => 0,
                'today_revenue' => 0,
                'avg_transaction' => 0,
                'growth_percent' => 0
            ];
        }
    }

    public function getWeeklySalesStats()
    {
        date_default_timezone_set('Asia/Manila');

        $startOfWeek = date('Y-m-d', strtotime('monday this week')) . ' 00:00:00';
        $endOfWeek = date('Y-m-d', strtotime('sunday this week')) . ' 23:59:59';

        $startOfLastWeek = date('Y-m-d', strtotime('monday last week')) . ' 00:00:00';
        $endOfLastWeek = date('Y-m-d', strtotime('sunday last week')) . ' 23:59:59';

        try {
            $conn = $this->conn();

            $stmt = $conn->prepare("
                SELECT 
                    SUM(CASE WHEN date BETWEEN :start AND :end THEN grand_total ELSE 0 END) AS current_sales,
                    COUNT(CASE WHEN date BETWEEN :start AND :end THEN 1 END) AS current_count,
                    SUM(CASE WHEN date BETWEEN :lastStart AND :lastEnd THEN grand_total ELSE 0 END) AS last_sales
                FROM sales
            ");
            $stmt->execute([
                ':start' => $startOfWeek,
                ':end' => $endOfWeek,
                ':lastStart' => $startOfLastWeek,
                ':lastEnd' => $endOfLastWeek
            ]);

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            $currentCount = (int) $result['current_count'];
            $currentSales = (float) $result['current_sales'];
            $lastSales = (float) $result['last_sales'];

            $avg = $currentCount > 0 ? $currentSales / $currentCount : 0;
            $growth = $lastSales > 0 ? (($currentSales - $lastSales) / $lastSales) * 100 : ($currentSales > 0 ? 100 : 0);

            return [
                'period' => 'This Week',
                'transaction_count' => $currentCount,
                'total_sales' => $currentSales,
                'avg_transaction' => $avg,
                'growth_percent' => round($growth)
            ];
        } catch (PDOException $e) {
            return [
                'period' => 'This Week',
                'transaction_count' => 0,
                'total_sales' => 0,
                'avg_transaction' => 0,
                'growth_percent' => 0
            ];
        }
    }

    public function getMonthlySalesStats()
    {
        date_default_timezone_set('Asia/Manila');

        $currentMonthStart = date('Y-m-01') . ' 00:00:00';
        $currentMonthEnd = date('Y-m-t') . ' 23:59:59';

        $previousMonthStart = date('Y-m-01', strtotime('first day of last month')) . ' 00:00:00';
        $previousMonthEnd = date('Y-m-t', strtotime('last day of last month')) . ' 23:59:59';

        try {
            $conn = $this->conn();

            $stmt = $conn->prepare("
                SELECT 
                    -- Current month
                    SUM(CASE WHEN date BETWEEN :currentMonthStart AND :currentMonthEnd THEN grand_total ELSE 0 END) AS current_revenue,
                    COUNT(CASE WHEN date BETWEEN :currentMonthStart AND :currentMonthEnd THEN 1 END) AS current_count,

                    -- Previous month
                    SUM(CASE WHEN date BETWEEN :previousMonthStart AND :previousMonthEnd THEN grand_total ELSE 0 END) AS previous_revenue
                FROM sales
            ");

            $stmt->execute([
                ':currentMonthStart' => $currentMonthStart,
                ':currentMonthEnd' => $currentMonthEnd,
                ':previousMonthStart' => $previousMonthStart,
                ':previousMonthEnd' => $previousMonthEnd
            ]);

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            $currentRevenue = (float) $result['current_revenue'];
            $currentCount = (int) $result['current_count'];
            $previousRevenue = (float) $result['previous_revenue'];

            $avgTransaction = $currentCount > 0 ? $currentRevenue / $currentCount : 0;

            // Growth percentage
            if ($previousRevenue > 0) {
                $growthPercent = (($currentRevenue - $previousRevenue) / $previousRevenue) * 100;
            } else {
                $growthPercent = $currentRevenue > 0 ? 100 : 0;
            }

            return [
                'total_sales' => $currentRevenue,
                'transaction_count' => $currentCount,
                'avg_transaction' => round($avgTransaction, 2),
                'growth_percent' => round($growthPercent, 2),
                'period' => date('F Y') // e.g. "October 2025"
            ];
        } catch (PDOException $e) {
            return [
                'total_sales' => 0,
                'transaction_count' => 0,
                'avg_transaction' => 0,
                'growth_percent' => 0,
                'period' => date('F Y')
            ];
        }
    }

    public function getYearlySalesStats()
    {
        date_default_timezone_set('Asia/Manila');

        $startOfYear = date('Y') . '-01-01 00:00:00';
        $endOfYear = date('Y') . '-12-31 23:59:59';

        $lastYear = date('Y', strtotime('-1 year'));
        $startOfLastYear = $lastYear . '-01-01 00:00:00';
        $endOfLastYear = $lastYear . '-12-31 23:59:59';

        try {
            $conn = $this->conn();

            $stmt = $conn->prepare("
                SELECT 
                    SUM(CASE WHEN date BETWEEN :start AND :end THEN grand_total ELSE 0 END) AS current_sales,
                    COUNT(CASE WHEN date BETWEEN :start AND :end THEN 1 END) AS current_count,
                    SUM(CASE WHEN date BETWEEN :lastStart AND :lastEnd THEN grand_total ELSE 0 END) AS last_sales
                FROM sales
            ");

            $stmt->execute([
                ':start' => $startOfYear,
                ':end' => $endOfYear,
                ':lastStart' => $startOfLastYear,
                ':lastEnd' => $endOfLastYear
            ]);

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            $currentCount = (int) $result['current_count'];
            $currentSales = (float) $result['current_sales'];
            $lastSales = (float) $result['last_sales'];

            $avg = $currentCount > 0 ? $currentSales / $currentCount : 0;
            $growth = $lastSales > 0 ? (($currentSales - $lastSales) / $lastSales) * 100 : ($currentSales > 0 ? 100 : 0);

            return [
                'period' => 'This Year',
                'transaction_count' => $currentCount,
                'total_sales' => $currentSales,
                'avg_transaction' => $avg,
                'growth_percent' => round($growth)
            ];
        } catch (PDOException $e) {
            return [
                'period' => 'This Year',
                'transaction_count' => 0,
                'total_sales' => 0,
                'avg_transaction' => 0,
                'growth_percent' => 0
            ];
        }
    }

    public function getTotalSalesCount($search = '')
    {
        $sql = "SELECT COUNT(*) as total FROM sales WHERE is_deleted = 0";
        $params = [];

        if ($search !== '') {
            $sql .= " AND (transaction_id LIKE :search 
              OR customer_name LIKE :search 
              OR payment_method LIKE :search 
              OR CAST(grand_total AS CHAR) LIKE :search)";
            $like = '%' . $search . '%';
            $params[':search'] = $like;
        }

        $stmt = $this->conn()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function select_sales_paginated($offset, $perPage, $search = '')
    {
        $sql = "
        SELECT 
            sale_id, transaction_id, customer_name, grand_total, payment_method,
            DATE_FORMAT(date, '%Y-%m-%d') as date,
            DATE_FORMAT(date, '%H:%i:%s') as time
        FROM sales
        WHERE is_deleted = 0
    ";
        $params = [];

        if ($search !== '') {
            $sql .= " AND (transaction_id LIKE :search 
              OR customer_name LIKE :search 
              OR payment_method LIKE :search 
              OR CAST(grand_total AS CHAR) LIKE :search)";
            $like = '%' . $search . '%';
            $params[':search'] = $like;
        }

        $sql .= " ORDER BY date DESC LIMIT :offset, :perPage";

        $stmt = $this->conn()->prepare($sql);

        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v, PDO::PARAM_STR);
        }
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':perPage', $perPage, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function archiveSale($saleId)
    {
        $sql = "UPDATE sales SET is_deleted = 1 WHERE sale_id = :sale_id";
        $stmt = $this->conn()->prepare($sql);
        $stmt->bindValue(':sale_id', $saleId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function getTopSellingProducts($limit = 5)
    {
        $conn = $this->conn();

        $stmt = $conn->prepare("
            SELECT 
                i.item_name,
                SUM(si.quantity) AS total_quantity
            FROM sale_items si
            JOIN items i ON si.item_id = i.item_id
            GROUP BY si.item_id
            ORDER BY total_quantity DESC
            LIMIT ?
        ");
        $stmt->bindValue(1, (int) $limit, PDO::PARAM_INT);
        $stmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Calculate total of all quantities for percentage
        $totalQty = array_sum(array_column($results, 'total_quantity'));

        foreach ($results as &$row) {
            $row['percentage'] = $totalQty > 0
                ? round(($row['total_quantity'] / $totalQty) * 100, 2)
                : 0;
        }

        return $results;
    }

    public function getPeakSalesHours()
    {
        $conn = $this->conn();

        $stmt = $conn->prepare("
            SELECT 
                HOUR(date) as hour,
                COUNT(*) as total_sales
            FROM sales
            GROUP BY HOUR(date)
            ORDER BY total_sales DESC
        ");
        $stmt->execute();
        $rawData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Define time blocks (2-hour range)
        $timeBlocks = [
            '8:00 AM - 10:00 AM' => [8, 9],
            '10:00 AM - 12:00 PM' => [10, 11],
            '12:00 PM - 2:00 PM' => [12, 13],
            '2:00 PM - 4:00 PM' => [14, 15],
            '4:00 PM - 6:00 PM' => [16, 17],
            '6:00 PM - 8:00 PM' => [18, 19],
            '8:00 PM - 10:00 PM' => [20, 21],
        ];

        // Initialize counts
        $blockSales = [];
        foreach ($timeBlocks as $label => $range) {
            $blockSales[$label] = 0;
        }

        // Aggregate sales count into time blocks
        foreach ($rawData as $row) {
            $hour = (int) $row['hour'];
            $count = (int) $row['total_sales'];

            foreach ($timeBlocks as $label => $range) {
                if ($hour >= $range[0] && $hour <= $range[1]) {
                    $blockSales[$label] += $count;
                    break;
                }
            }
        }

        // Sort by most sales
        arsort($blockSales);

        // Get top 3 blocks and assign levels
        $levels = ['Peak', 'High', 'Medium'];
        $colors = ['red', 'orange', 'yellow'];
        $result = [];

        $i = 0;
        foreach ($blockSales as $label => $count) {
            if ($count > 0 && $i < 3) {
                $result[] = [
                    'time_range' => $label,
                    'level' => $levels[$i],
                    'color' => $colors[$i],
                    'count' => $count
                ];
                $i++;
            }
        }

        return $result;
    }

    public function getTopProductsByValue($limit = 5)
    {
        $conn = $this->conn();

        $sql = $conn->prepare("
            SELECT 
                i.item_name,
                SUM(poi.quantity) AS total_qty,
                i.cost_price,
                i.selling_price,
                (i.cost_price * SUM(poi.quantity)) AS total_value,
                ROUND(((i.selling_price - i.cost_price) / i.selling_price) * 100, 1) AS margin_percentage
            FROM purchase_order_items poi
            JOIN items i ON poi.item_id = i.item_id
            JOIN purchase_orders po ON poi.purchase_order_id = po.purchase_order_id
            WHERE po.status != 'Cancelled' AND po.is_active = 1
            GROUP BY i.item_id
            ORDER BY total_value DESC
            LIMIT $limit
        ");

        $sql->execute();
        return $sql->fetchAll();
    }


    public function remove_from_cart()
    {
        $removeId = $_POST['remove_item_id'] ?? null;
        if (!$removeId)
            return;

        if (isset($_SESSION['cart'][$removeId])) {
            unset($_SESSION['cart'][$removeId]);
            $_SESSION['sale-success'] = "Item removed from cart.";
        } else {
            $_SESSION['sale-error'] = "Item not found in cart.";
        }
    }


    public function create_category()
    {
        if (!isset($_POST['create_category'])) {
            return;
        }

        $errors = [];

        $category_name        = trim($_POST['category_name'] ?? '');
        $category_description = trim($_POST['category_description'] ?? '');

        // checkbox → 1 / 0
        $supports_quantity = isset($_POST['supports_quantity']) ? 1 : 0;

        // select → pc_part | accessory
        $category_type = $_POST['category_type'] ?? '';

        date_default_timezone_set('Asia/Manila');
        $createdAt = date('Y-m-d H:i:s');

        if ($category_name === '') {
            $errors[] = "Category name is required.";
        } elseif (strlen($category_name) > 30) {
            $errors[] = "Category name must not exceed 30 characters.";
        }

        if (!in_array($category_type, ['pc_part', 'accessory'])) {
            $errors[] = "Invalid category type.";
        }

        // unique name
        $stmt = $this->conn()->prepare(
            "SELECT 1 FROM categories WHERE category_name = ?"
        );
        $stmt->execute([$category_name]);

        if ($stmt->fetch()) {
            $errors[] = "Category name already exists.";
        }

        $category_slug = strtolower(trim($category_name));
        $category_slug = preg_replace('/[^a-z0-9]+/', '-', $category_slug);
        $category_slug = trim($category_slug, '-');

        // slug uniqueness
        $slugCheck = $this->conn()->prepare(
            "SELECT COUNT(*) FROM categories WHERE category_slug LIKE ?"
        );
        $slugCheck->execute([$category_slug . '%']);
        $slugCount = (int) $slugCheck->fetchColumn();

        if ($slugCount > 0) {
            $category_slug .= '-' . ($slugCount + 1);
        }

        if (!empty($errors)) {
            $_SESSION['create-error'] = implode("<br><br>", $errors);
            return;
        }

        $stmt = $this->conn()->prepare("
        INSERT INTO categories (
            category_name,
            category_slug,
            category_type,
            category_description,
            supports_quantity,
            created_at
        ) VALUES (?, ?, ?, ?, ?, ?)
    ");

        $stmt->execute([
            $category_name,
            $category_slug,
            $category_type,
            $category_description,
            $supports_quantity,
            $createdAt
        ]);

        $_SESSION['create-success'] =
            "Category '{$category_name}' added successfully.";
    }



    public function update_category()
    {
        if (!isset($_POST['update_category'])) {
            return;
        }

        $errors = [];

        $category_id          = (int) ($_POST['category_id'] ?? 0);
        $category_name        = trim($_POST['category_name'] ?? '');
        $category_description = trim($_POST['category_description'] ?? '');
        $category_type        = $_POST['category_type'] ?? '';
        $supports_quantity    = isset($_POST['supports_quantity']) ? 1 : 0;

        date_default_timezone_set('Asia/Manila');
        $updatedAt = date('Y-m-d H:i:s');

        if ($category_name === '') {
            $errors[] = "Category name is required.";
        } elseif (strlen($category_name) > 30) {
            $errors[] = "Category name must not exceed 30 characters.";
        }

        if (!in_array($category_type, ['pc_part', 'accessory'])) {
            $errors[] = "Invalid category type.";
        }

        // unique name (exclude self)
        $stmt = $this->conn()->prepare(
            "SELECT 1 FROM categories 
         WHERE category_name = ? AND category_id != ?"
        );
        $stmt->execute([$category_name, $category_id]);

        if ($stmt->fetch()) {
            $errors[] = "Category name already exists.";
        }

        // regenerate slug
        $category_slug = strtolower(trim($category_name));
        $category_slug = preg_replace('/[^a-z0-9]+/', '-', $category_slug);
        $category_slug = trim($category_slug, '-');

        // slug uniqueness (exclude self)
        $slugCheck = $this->conn()->prepare(
            "SELECT COUNT(*) FROM categories 
         WHERE category_slug LIKE ? AND category_id != ?"
        );
        $slugCheck->execute([$category_slug . '%', $category_id]);
        $slugCount = (int) $slugCheck->fetchColumn();

        if ($slugCount > 0) {
            $category_slug .= '-' . ($slugCount + 1);
        }

        if (!empty($errors)) {
            $_SESSION['create-error'] = implode("<br><br>", $errors);
            return;
        }

        $stmt = $this->conn()->prepare("
        UPDATE categories SET
            category_name = ?,
            category_slug = ?,
            category_type = ?,
            category_description = ?,
            supports_quantity = ?,
            updated_at = ?
        WHERE category_id = ?
    ");

        $stmt->execute([
            $category_name,
            $category_slug,
            $category_type,
            $category_description,
            $supports_quantity,
            $updatedAt,
            $category_id
        ]);

        $_SESSION['create-success'] =
            "Category '{$category_name}' updated successfully.";
    }

    public function archive_category()
    {
        if (isset($_POST['archive_category'])) {
            $category_id = filter_input(INPUT_POST, 'category_id', FILTER_SANITIZE_NUMBER_INT);

            if (!$category_id) {
                $_SESSION['create-error'] = "Invalid category ID.";
                return;
            }

            $sql = "UPDATE categories SET is_deleted = 1 WHERE category_id = ?";
            $stmt = $this->conn()->prepare($sql);
            $stmt->execute([$category_id]);

            $_SESSION['create-success'] = "Category archived successfully.";
        }
    }


    public function select_categories()
    {
        $sql = $this->conn()->prepare("SELECT * FROM categories");
        $sql->execute();
        $categories = $sql->fetchAll();

        return $categories;
    }

    public function getAllCategories()
    {
        $sql = "
        SELECT
            category_id,
            category_name,
            category_slug,
            category_type,
            supports_quantity
        FROM categories
        ORDER BY category_name
    ";

        return $this->conn()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }


    public function getItemsByCategoryId($categoryId)
    {
        $sql = "SELECT item_id, item_name, selling_price
            FROM items
            WHERE category_id = ?";

        $stmt = $this->conn()->prepare($sql);
        $stmt->execute([$categoryId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalCategoriesCount($search = '')
    {
        $sql = "SELECT COUNT(*) as total FROM categories";
        $params = [];

        if ($search !== '') {
            $sql .= " WHERE category_name LIKE :search";
            $like = '%' . $search . '%';
            $params[':search'] = $like;
        }

        $stmt = $this->conn()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function select_categories_paginated($offset, $perPage, $search = '')
    {
        $sql = "SELECT * FROM categories";
        $params = [];

        if ($search !== '') {
            $sql .= " WHERE category_name LIKE :search";
            $like = '%' . $search . '%';
            $params[':search'] = $like;
        }

        $sql .= " ORDER BY category_name ASC LIMIT :offset, :perPage";

        $stmt = $this->conn()->prepare($sql);

        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v, PDO::PARAM_STR);
        }
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':perPage', $perPage, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createPcBuilder()
    {
        if (!isset($_POST['pc-build-btn'])) {
            return;
        }

        $pdo = $this->conn();

        $pc_builder_name = trim($_POST['pc_builder_name'] ?? '');
        $user_id = $_SESSION['user_id'] ?? null;

        date_default_timezone_set('Asia/Manila');
        $createdAt = date('Y-m-d H:i:s');

        $errors = [];

        if ($pc_builder_name === '') {
            $errors[] = "Build name is required.";
        } elseif (strlen($pc_builder_name) > 100) {
            $errors[] = "Build name must be less than 100 characters.";
        }

        $stmt = $pdo->prepare(
            "SELECT 1 FROM pc_builders WHERE pc_builder_name = ? AND user_id = ?"
        );
        $stmt->execute([$pc_builder_name, $user_id]);

        if ($stmt->fetch()) {
            $errors[] = "You already have a build with this name.";
        }

        if (!empty($errors)) {
            $_SESSION['create-error'] = implode("<br><br>", $errors);
            return;
        }

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("
            INSERT INTO pc_builders (pc_builder_name, user_id, status, created_at)
            VALUES (?, ?, 'Pending', ?)
        ");
            $stmt->execute([$pc_builder_name, $user_id, $createdAt]);

            $pc_builder_id = $pdo->lastInsertId();
            $categories = $this->getAllCategories();

            $insertItem = $pdo->prepare("
    INSERT INTO pc_builder_items
    (pc_builder_id, category_id, item_id, quantity)
    VALUES (?, ?, ?, ?)
");

            foreach ($categories as $category) {
                $catId = $category['category_id'];
                $itemKey = 'category_' . $catId;

                if (!empty($_POST[$itemKey])) {
                    $itemId = (int) $_POST[$itemKey];

                    $quantity = 1;

                    if (!empty($category['supports_quantity'])) {
                        $qtyKey = 'quantity_' . $catId;
                        if (!empty($_POST[$qtyKey]) && (int)$_POST[$qtyKey] > 0) {
                            $quantity = (int) $_POST[$qtyKey];
                        }
                    }


                    $insertItem->execute([
                        $pc_builder_id,
                        $catId,
                        $itemId,
                        $quantity
                    ]);
                }
            }

            $pdo->commit();

            $_SESSION['create-success'] =
                "PC Build '{$pc_builder_name}' has been saved successfully.";
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $_SESSION['create-error'] =
                "Failed to save PC Build. Please try again.";
        }
    }

    public function getPcBuildersByUser($userId)
    {
        $pdo = $this->conn();

        $stmt = $pdo->prepare("
        SELECT 
            pc_builder_id,
            pc_builder_name,
            status,
            created_at
        FROM pc_builders
        WHERE user_id = ?
        ORDER BY created_at DESC
    ");

        $stmt->execute([$userId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPcBuilderDetails($pcBuilderId)
    {
        $pdo = $this->conn();

        // Get PC Builder basic info
        $stmt = $pdo->prepare("
        SELECT 
            pb.pc_builder_id,
            pb.pc_builder_name,
            pb.status,
            pb.created_at,
            pb.user_id,
            u.username
        FROM pc_builders pb
        LEFT JOIN users u ON pb.user_id = u.user_id
        WHERE pb.pc_builder_id = ?
    ");

        $stmt->execute([$pcBuilderId]);
        $builder = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$builder) {
            return null;
        }

        // Get all items in this PC Builder with category and item details
        $stmt = $pdo->prepare("
        SELECT 
            pbi.pc_builder_item_id,
            pbi.quantity,
            c.category_id,
            c.category_name,
            c.category_type,
            i.item_id,
            i.item_name,
            i.selling_price,
            (i.selling_price * pbi.quantity) as line_total
        FROM pc_builder_items pbi
        INNER JOIN categories c ON pbi.category_id = c.category_id
        INNER JOIN items i ON pbi.item_id = i.item_id
        WHERE pbi.pc_builder_id = ?
        ORDER BY c.category_type DESC, c.category_name ASC
    ");

        $stmt->execute([$pcBuilderId]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Calculate grand total
        $grandTotal = 0;
        foreach ($items as $item) {
            $grandTotal += $item['line_total'];
        }

        // Group items by category type
        $pcParts = [];
        $accessories = [];

        foreach ($items as $item) {
            if ($item['category_type'] === 'pc_part') {
                $pcParts[] = $item;
            } else {
                $accessories[] = $item;
            }
        }

        return [
            'builder' => $builder,
            'pc_parts' => $pcParts,
            'accessories' => $accessories,
            'items' => $items,
            'grand_total' => $grandTotal
        ];
    }


    public function getPCBuilders()
    {
        $sql = "
        SELECT 
            pb.pc_builder_id,
            pb.pc_builder_name,
            pb.user_id,

            cpu.item_name AS cpu_name,
            gpu.item_name AS gpu_name,
            ram.item_name AS ram_name,
            mb.item_name AS motherboard_name,
            st.item_name AS storage_name,
            psu.item_name AS psu_name,
            c.item_name AS case_name,

            ROUND(
                COALESCE(cpu.selling_price,0) + COALESCE(gpu.selling_price,0) + COALESCE(ram.selling_price,0) +
                COALESCE(mb.selling_price,0) + COALESCE(st.selling_price,0) + COALESCE(psu.selling_price,0) + COALESCE(c.selling_price,0),
            2) AS total_price
        FROM pc_builders pb
        LEFT JOIN items cpu ON pb.cpu_id = cpu.item_id
        LEFT JOIN items gpu ON pb.gpu_id = gpu.item_id
        LEFT JOIN items ram ON pb.ram_id = ram.item_id
        LEFT JOIN items mb  ON pb.motherboard_id = mb.item_id
        LEFT JOIN items st  ON pb.storage_id = st.item_id
        LEFT JOIN items psu ON pb.psu_id = psu.item_id
        LEFT JOIN items c   ON pb.case_id = c.item_id
        ORDER BY pb.pc_builder_name ASC
    ";

        $stmt = $this->conn()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPCBuilderById($pc_builder_id)
    {
        $conn = $this->conn();

        $stmt = $conn->prepare("
        SELECT 
            pb.pc_builder_id,
            pb.pc_builder_name,
            pb.cpu_id,
            pb.gpu_id,
            pb.ram_id,
            pb.motherboard_id,
            pb.storage_id,
            pb.psu_id,
            pb.case_id,
            ROUND(
                COALESCE(cpu.selling_price,0) + COALESCE(gpu.selling_price,0) +
                COALESCE(ram.selling_price,0) + COALESCE(mb.selling_price,0) +
                COALESCE(st.selling_price,0) + COALESCE(psu.selling_price,0) +
                COALESCE(ca.selling_price,0),
            2) AS total_price
        FROM pc_builders pb
        LEFT JOIN items cpu ON pb.cpu_id = cpu.item_id
        LEFT JOIN items gpu ON pb.gpu_id = gpu.item_id
        LEFT JOIN items ram ON pb.ram_id = ram.item_id
        LEFT JOIN items mb  ON pb.motherboard_id = mb.item_id
        LEFT JOIN items st  ON pb.storage_id = st.item_id
        LEFT JOIN items psu ON pb.psu_id = psu.item_id
        LEFT JOIN items ca  ON pb.case_id = ca.item_id
        WHERE pb.pc_builder_id = ?
    ");
        $stmt->execute([$pc_builder_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
$database = new Database();
