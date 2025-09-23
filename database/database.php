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

    public function delete_supplier()
    {
        if (isset($_POST['delete_supplier'])) {
            $id = $_POST['supplier_id'];

            $sql = $this->conn()->prepare("DELETE FROM suppliers WHERE supplier_id = ?");
            $sql->execute([$id]);

            $_SESSION['create-success'] = "Supplier deleted successfully.";
        }
    }


    public function select_suppliers()
    {
        $sql = $this->conn()->prepare("SELECT * FROM suppliers");
        $sql->execute();
        $suppliers = $sql->fetchAll();

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

            $sql = $this->conn()->prepare("INSERT INTO items (item_name, barcode, description, category_id, supplier_id, cost_price, selling_price, quantity, min_stock) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $sql->execute([$item_name, $barcode, $description, $category_id, $supplier_id, $cost_price, $selling_price, $quantity, $min_stock]);

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
                SET item_name = ?, barcode = ?, description = ?, category_id = ?, supplier_id = ?, cost_price = ?, selling_price = ?, quantity = ?, min_stock = ?
                WHERE item_id = ?
            ");
                $sql->execute([$item_name, $barcode, $description, $category_id, $supplier_id, $cost_price, $selling_price, $quantity, $min_stock, $item_id]);
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

    // public function create_purchase_order()
    // {
    //     if (!isset($_POST['create_po'])) {
    //         error_log("create_po POST not set. Exiting.");
    //         return;
    //     }

    //     $supplier_id = filter_input(INPUT_POST, 'supplier_id', FILTER_VALIDATE_INT);
    //     $created_by = $_SESSION['user_id'] ?? null;

    //     error_log("Supplier ID: " . var_export($supplier_id, true));
    //     error_log("Created by (user_id): " . var_export($created_by, true));

    //     if (!$supplier_id || !$created_by) {
    //         $_SESSION['create-error'] = "Supplier or user info missing.";
    //         error_log("Exiting: Supplier or created_by missing.");
    //         return;
    //     }

    //     $item_ids = $_POST['item_id'] ?? [];
    //     $quantities = $_POST['quantity'] ?? [];

    //     error_log("Received item_ids: " . json_encode($item_ids));
    //     error_log("Received quantities: " . json_encode($quantities));

    //     if (empty($item_ids) || empty($quantities)) {
    //         $_SESSION['create-error'] = "No items submitted.";
    //         error_log("Exiting: No items submitted.");
    //         return;
    //     }

    //     if (count($item_ids) !== count($quantities)) {
    //         $_SESSION['create-error'] = "Mismatch between items and quantities.";
    //         error_log("Exiting: Mismatch between items and quantities.");
    //         return;
    //     }

    //     $grand_total = 0;

    //     // Collect all items data for insertion
    //     $items_data = [];

    //     for ($i = 0; $i < count($item_ids); $i++) {
    //         $item_id = filter_var($item_ids[$i], FILTER_VALIDATE_INT);
    //         $qty = filter_var($quantities[$i], FILTER_VALIDATE_INT);

    //         error_log("Processing item_id: $item_id with quantity: $qty");

    //         if (!$item_id || !$qty || $qty <= 0) {
    //             $_SESSION['create-error'] = "Invalid item or quantity at position " . ($i + 1);
    //             error_log("Exiting: Invalid item or quantity at position " . ($i + 1));
    //             return;
    //         }

    //         // Fetch cost_price for this item
    //         $stmt = $this->conn()->prepare("SELECT cost_price FROM items WHERE item_id = ?");
    //         $stmt->execute([$item_id]);
    //         $item = $stmt->fetch(PDO::FETCH_ASSOC);

    //         if (!$item) {
    //             $_SESSION['create-error'] = "Item not found in DB for item_id: $item_id";
    //             error_log("Exiting: Item not found in DB for item_id: $item_id");
    //             return;
    //         }

    //         $unit_cost = $item['cost_price'];
    //         $line_total = $unit_cost * $qty;
    //         $grand_total += $line_total;

    //         $items_data[] = [
    //             'item_id' => $item_id,
    //             'quantity' => $qty,
    //             'unit_cost' => $unit_cost,
    //             'line_total' => $line_total,
    //         ];

    //         error_log("Item data collected - item_id: $item_id, qty: $qty, unit_cost: $unit_cost, line_total: $line_total");
    //     }

    //     error_log("Total grand_total for PO: $grand_total");

    //     try {
    //         $this->conn()->beginTransaction();

    //         // Insert purchase order
    //         $insert_po = $this->conn()->prepare("INSERT INTO purchase_orders (supplier_id, grand_total, status, date, created_by) VALUES (?, ?, 'Pending', NOW(), ?)");
    //         $insert_po->execute([$supplier_id, $grand_total, $created_by]);

    //         $purchase_order_id = $this->conn()->lastInsertId();
    //         error_log("Inserted purchase_order with ID: $purchase_order_id");

    //         if (!$purchase_order_id) {
    //             throw new Exception("Failed to retrieve purchase order ID");
    //         }

    //         // Insert all purchase_order_items
    //         $insert_item = $this->conn()->prepare("INSERT INTO purchase_order_items (purchase_order_id, item_id, quantity, unit_cost, line_total) VALUES (?, ?, ?, ?, ?)");

    //         foreach ($items_data as $data) {
    //             error_log("Inserting purchase_order_item for item_id: {$data['item_id']}");
    //             $insert_item->execute([
    //                 $purchase_order_id,
    //                 $data['item_id'],
    //                 $data['quantity'],
    //                 $data['unit_cost'],
    //                 $data['line_total']
    //             ]);
    //             error_log("Inserted purchase_order_item for item_id: {$data['item_id']}");
    //         }

    //         $this->conn()->commit();

    //         $_SESSION['create-success'] = "Purchase order #$purchase_order_id created successfully with " . count($items_data) . " item(s).";

    //     } catch (Exception $e) {
    //         if ($this->conn()->inTransaction()) {
    //             $this->conn()->rollBack();
    //             error_log("Transaction rolled back due to error.");
    //         }
    //         $_SESSION['create-error'] = "Error creating purchase order: " . $e->getMessage();
    //         error_log("PO Creation error: " . $e->getMessage());
    //     }
    // }

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