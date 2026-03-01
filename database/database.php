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
            header("Location: index.php");
            exit;
        }

        if (!$this->conn()) {
            die("Database connection not initialized. Cannot proceed.");
        }
    }

    public function admin_session()
    {
        if (!isset($_SESSION['login-success'])) {
            header("Location: index.php");
            exit;
        }

        if ($_SESSION['user-role'] !== 'admin') {
            header("Location: dashboard.php");
            exit;
        }
    }

    public function superadmin_session()
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['user-role'] !== 'superadmin') {
            header("Location: index.php");
            exit;
        }
    }

    public function register()
    {
        $errors = [];
        if (isset($_POST['register'])) {
            $pdo = $this->conn();
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';

            $role = 'staff';

            $query = $pdo->prepare("SELECT user_id FROM users WHERE username = ?");
            $query->execute([$username]);
            $check_username = $query->fetch();

            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            if (empty($username) || empty($password)) {
                $errors[] = "Do not leave any field empty";
            } else if (strlen($username) > 15) {
                $errors[] = "Username cannot exceed 15 characters";
            } else if (!preg_match("/^[a-zA-Z\s]+$/", $username)) {
                $errors[] = "Username cannot contain numbers or special characters";
            } else if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W]).{8,20}$/', $password)) {
                $errors[] = "Password must be 8-20 characters and include uppercase, lowercase, number, and special character";
            }

            if ($check_username) {
                $errors[] = "Username is already taken.";
            }

            if (!empty($errors)) {
                $_SESSION['register-error'] = implode("<br><br>", $errors);
            } else {
                $sql = $pdo->prepare("INSERT INTO users (`username`, `password`, `role`) VALUES (?,?,?)");
                $sql->execute([$username, $hashedPassword, $role]);
                $_SESSION['register-success'] = "Successfully registered " . $username . " as staff. You can now login.";
            }
        }
    }

    public function login()
    {
        $errors = [];
        if (isset($_POST['login'])) {
            $username = trim(filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING));
            $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);

            if (empty($username) || empty($password)) {
                $errors[] = "Do not leave the fields empty";
            } else {
                $sql = $this->conn()->prepare("SELECT password, role, user_id, is_active FROM users WHERE username = ?");
                $sql->execute([$username]);
                $user = $sql->fetch();

                if ($user) {
                    if ($user['is_active'] == 0) {
                        $errors[] = "This user account is deactivated.";
                    } else if (password_verify($password, $user['password'])) {
                        $_SESSION['login-success'] = $username;
                        $_SESSION['user_id']       = $user['user_id'];
                        $_SESSION['user-role']     = $user['role'];

                        if ($user['role'] === 'superadmin') {
                            header("Location: super_admin_page.php");
                        } else {
                            header("Location: dashboard.php");
                        }
                        exit;
                    } else {
                        $errors[] = "Wrong password";
                    }
                } else {
                    $errors[] = "Wrong username";
                }
            }

            if (!empty($errors)) {
                $_SESSION['login-error'] = implode("<br>", $errors);
            }
        }
    }

    public function update_staff()
    {
        if (isset($_POST['update_staff'])) {
            $pdo = $this->conn();

            $errors = [];

            $user_id = $_POST['user_id'];
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($username)) {
                $errors[] = "Username cannot be empty.";
            } else if (strlen($username) > 15) {
                $errors[] = "Username cannot exceed 15 characters.";
            } else if (!preg_match("/^[a-zA-Z\s]+$/", $username)) {
                $errors[] = "Username cannot contain numbers or special characters.";
            }

            if (!empty($password) && !preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W]).{8,20}$/', $password)) {
                $errors[] = "Password must be 8-20 characters and include uppercase, lowercase, number, and special character.";
            }

            $query = $pdo->prepare("SELECT user_id FROM users WHERE username = ? AND user_id != ?");
            $query->execute([$username, $user_id]);
            $check_username = $query->fetch();

            if ($check_username) {
                $errors[] = "Username is already taken.";
            }

            if (!empty($errors)) {
                $_SESSION['register-error'] = implode("<br><br>", $errors);
            } else {
                if (!empty($password)) {
                    $hashed = password_hash($password, PASSWORD_BCRYPT);
                    $sql = $pdo->prepare("UPDATE users SET username = ?, password = ? WHERE user_id = ?");
                    $sql->execute([$username, $hashed, $user_id]);
                } else {
                    $sql = $pdo->prepare("UPDATE users SET username = ? WHERE user_id = ?");
                    $sql->execute([$username, $user_id]);
                }

                $_SESSION['register-success'] = "Account " . $username . " successfully updated.";
                header("Location: " . $_SERVER['REQUEST_URI']);
                exit;
            }
        }
    }

    public function deactivate_staff()
    {
        if (isset($_POST['deactivate_staff'])) {
            $pdo = $this->conn();
            $user_id = $_POST['user_id'];

            $sql = $pdo->prepare("UPDATE users SET is_active = 0 WHERE user_id = ?");
            $sql->execute([$user_id]);

            $_SESSION['register-success'] = "Staff successfully deactivated.";
            header("Location: " . $_SERVER['REQUEST_URI']);
            exit;
        }
    }

    public function activate_staff()
    {
        if (isset($_POST['activate_staff'])) {
            $pdo = $this->conn();
            $user_id = $_POST['user_id'];

            $sql = $pdo->prepare("UPDATE users SET is_active = 1 WHERE user_id = ?");
            $sql->execute([$user_id]);

            $_SESSION['register-success'] = "Staff successfully activated.";
            header("Location: " . $_SERVER['REQUEST_URI']);
            exit;
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

            header("Location: " . $_SERVER['REQUEST_URI']);
            exit;
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

            header("Location: " . $_SERVER['REQUEST_URI']);
            exit;
        }
    }

    public function archive_supplier()
    {
        if (isset($_POST['archive_supplier'])) {
            $id = $_POST['supplier_id'];

            $sql = $this->conn()->prepare("UPDATE suppliers SET is_deleted = 1 WHERE supplier_id = ?");
            $sql->execute([$id]);

            $_SESSION['create-success'] = "Supplier archived successfully.";
            header("Location: " . $_SERVER['REQUEST_URI']);
            exit;
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
        WHERE s.is_deleted = 0";

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
            $item_name     = trim($_POST['item_name'] ?? '');
            $barcode       = trim($_POST['barcode'] ?? '');
            $description   = trim($_POST['description'] ?? '');
            $category_id   = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
            $supplier_id   = filter_input(INPUT_POST, 'supplier_id', FILTER_VALIDATE_INT);
            $cost_price    = filter_input(INPUT_POST, 'cost_price', FILTER_VALIDATE_FLOAT);
            $selling_price = filter_input(INPUT_POST, 'selling_price', FILTER_VALIDATE_FLOAT);
            $quantity      = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
            $min_stock     = filter_input(INPUT_POST, 'min_stock', FILTER_VALIDATE_INT);

            date_default_timezone_set('Asia/Manila');
            $now = date('Y-m-d H:i:s');

            // ─── Validation ────────────────────────────────────────────────
            if (empty($item_name)) {
                $errors[] = "Item name is required.";
            }
            if (strlen($item_name) > 100) {
                $errors[] = "Item name is too long (max 100 characters).";
            }
            if (empty($barcode)) {
                $errors[] = "Barcode is required.";
            }
            if (!preg_match('/^[a-zA-Z0-9\- ]{4,50}$/', $barcode)) {
                $errors[] = "Invalid barcode format (letters, numbers, spaces, hyphens only).";
            }
            if ($category_id === false || $category_id <= 0) {
                $errors[] = "Valid category is required.";
            }
            if ($selling_price === false || $selling_price <= 0) {
                $errors[] = "Selling price must be greater than 0.";
            }
            if ($cost_price === false || $cost_price < 0) {
                $errors[] = "Cost price cannot be negative.";
            }
            if ($quantity === false || $quantity < 0) {
                $errors[] = "Quantity cannot be negative.";
            }
            if ($min_stock === false || $min_stock < 0) {
                $errors[] = "Minimum stock cannot be negative.";
            }

            // Barcode uniqueness check
            $stmt = $this->conn()->prepare("SELECT 1 FROM items WHERE barcode = ?");
            $stmt->execute([$barcode]);
            if ($stmt->fetch()) {
                $errors[] = "This barcode is already in use.";
            }

            // ─── Image handling ─────────────────────────────────────────────
            $image_filename = null;

            if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                $max_size = 5 * 1024 * 1024; // 5MB

                $file = $_FILES['image'];

                if ($file['size'] > $max_size) {
                    $errors[] = "Image file is too large (maximum 5MB).";
                }
                if (!in_array($file['type'], $allowed_types)) {
                    $errors[] = "Only JPG, PNG, GIF, WebP images are allowed.";
                }

                if (empty($errors)) {
                    $original_name = $file['name'];
                    $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));

                    $safe_base = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($original_name, PATHINFO_FILENAME));
                    $safe_base = substr($safe_base, 0, 80); // prevent very long names

                    $image_filename = $safe_base . '.' . $ext;

                    // Avoid overwriting existing files
                    $upload_path = 'uploads/products/' . $image_filename;
                    $counter = 1;
                    while (file_exists($upload_path)) {
                        $image_filename = $safe_base . '-' . $counter . '.' . $ext;
                        $upload_path = 'uploads/products/' . $image_filename;
                        $counter++;
                    }

                    if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
                        $errors[] = "Failed to save uploaded image. Check folder permissions.";
                    }
                }
            }

            if (!empty($errors)) {
                $_SESSION['create-error'] = implode("<br>", $errors);
                return;
            }

            // ─── Insert into database ──────────────────────────────────────
            $sql = $this->conn()->prepare("
            INSERT INTO items 
            (item_name, barcode, description, category_id, supplier_id, 
             cost_price, selling_price, quantity, min_stock, image, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
            $sql->execute([
                $item_name,
                $barcode,
                $description,
                $category_id,
                $supplier_id,
                $cost_price,
                $selling_price,
                $quantity,
                $min_stock,
                $image_filename,
                $now
            ]);

            $_SESSION['create-success'] = "Item added successfully: " . htmlspecialchars($item_name);
            header("Location: " . $_SERVER['REQUEST_URI']);
            exit;
        }
    }

    public function update_item()
    {
        $errors = [];

        if (isset($_POST['update_item'])) {
            $item_id       = filter_input(INPUT_POST, 'item_id', FILTER_VALIDATE_INT);
            $item_name     = trim($_POST['item_name'] ?? '');
            $barcode       = trim($_POST['barcode'] ?? '');
            $description   = trim($_POST['description'] ?? '');
            $category_id   = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
            $supplier_id   = filter_input(INPUT_POST, 'supplier_id', FILTER_VALIDATE_INT);
            $min_stock     = filter_input(INPUT_POST, 'min_stock', FILTER_VALIDATE_INT);

            date_default_timezone_set('Asia/Manila');
            $now = date('Y-m-d H:i:s');

            $isStaff = ($_SESSION['user-role'] ?? '') === 'staff';

            if (!$isStaff) {
                $cost_price    = filter_input(INPUT_POST, 'cost_price', FILTER_VALIDATE_FLOAT);
                $selling_price = filter_input(INPUT_POST, 'selling_price', FILTER_VALIDATE_FLOAT);
                $quantity      = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
            }

            // ─── Validation ────────────────────────────────────────────────
            if (!$item_id) {
                $errors[] = "Invalid item ID.";
            }
            if (empty($item_name)) {
                $errors[] = "Item name is required.";
            }
            if (strlen($item_name) > 100) {
                $errors[] = "Item name is too long (max 100 characters).";
            }
            if (empty($barcode)) {
                $errors[] = "Barcode is required.";
            }
            if (strlen($barcode) > 50) {
                $errors[] = "Barcode is too long (max 50 characters).";
            }
            if ($category_id === false || $category_id <= 0) {
                $errors[] = "Valid category is required.";
            }
            if ($selling_price === false || $selling_price <= 0) {
                $errors[] = "Selling price must be greater than 0.";
            }
            if (!$isStaff) {
                if ($cost_price === false || $cost_price < 0) {
                    $errors[] = "Cost price cannot be negative.";
                }
                if ($quantity === false || $quantity < 0) {
                    $errors[] = "Quantity cannot be negative.";
                }
            }
            if ($min_stock === false || $min_stock < 0) {
                $errors[] = "Minimum stock cannot be negative.";
            }

            // Barcode unique (exclude current item)
            $stmt = $this->conn()->prepare("SELECT 1 FROM items WHERE barcode = ? AND item_id != ?");
            $stmt->execute([$barcode, $item_id]);
            if ($stmt->fetch()) {
                $errors[] = "This barcode is already used by another item.";
            }

            // ─── Image handling ─────────────────────────────────────────────
            $image_filename = null;

            if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                $max_size = 5 * 1024 * 1024;

                $file = $_FILES['image'];

                if ($file['size'] > $max_size) {
                    $errors[] = "Image file is too large (maximum 5MB).";
                }
                if (!in_array($file['type'], $allowed_types)) {
                    $errors[] = "Only JPG, PNG, GIF, WebP images are allowed.";
                }

                if (empty($errors)) {
                    $original_name = $file['name'];
                    $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));

                    $safe_base = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($original_name, PATHINFO_FILENAME));
                    $safe_base = substr($safe_base, 0, 80);

                    $image_filename = $safe_base . '.' . $ext;

                    $upload_path = 'uploads/products/' . $image_filename;
                    $counter = 1;
                    while (file_exists($upload_path)) {
                        $image_filename = $safe_base . '-' . $counter . '.' . $ext;
                        $upload_path = 'uploads/products/' . $image_filename;
                        $counter++;
                    }

                    if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
                        $errors[] = "Failed to save new image. Check folder permissions.";
                    }
                }
            }

            if (!empty($errors)) {
                $_SESSION['update-error'] = implode("<br>", $errors);
                header("Location: " . $_SERVER['REQUEST_URI']);
                exit;
            }

            // ─── Delete old image if new one is uploaded ───────────────────
            if ($image_filename !== null) {
                $old = $this->conn()->prepare("SELECT image FROM items WHERE item_id = ?");
                $old->execute([$item_id]);
                $old_image = $old->fetchColumn();

                if ($old_image && file_exists('uploads/products/' . $old_image)) {
                    @unlink('uploads/products/' . $old_image);
                }
            }

            // ─── Build update query ────────────────────────────────────────
            if ($isStaff) {
                $sql = $this->conn()->prepare("
                UPDATE items SET
                    item_name = ?, barcode = ?, description = ?,
                    category_id = ?, supplier_id = ?, min_stock = ?,
                    " . ($image_filename !== null ? "image = ?, " : "") . "
                    updated_at = ?
                WHERE item_id = ?
            ");

                $params = [
                    $item_name,
                    $barcode,
                    $description,
                    $category_id,
                    $supplier_id,
                    $min_stock
                ];

                if ($image_filename !== null) {
                    $params[] = $image_filename;
                }

                $params[] = $now;
                $params[] = $item_id;
            } else {
                $sql = $this->conn()->prepare("
                UPDATE items SET
                    item_name = ?, barcode = ?, description = ?,
                    category_id = ?, supplier_id = ?,
                    cost_price = ?, selling_price = ?, quantity = ?,
                    min_stock = ?,
                    " . ($image_filename !== null ? "image = ?, " : "") . "
                    updated_at = ?
                WHERE item_id = ?
            ");

                $params = [
                    $item_name,
                    $barcode,
                    $description,
                    $category_id,
                    $supplier_id,
                    $cost_price,
                    $selling_price,
                    $quantity,
                    $min_stock
                ];

                if ($image_filename !== null) {
                    $params[] = $image_filename;
                }

                $params[] = $now;
                $params[] = $item_id;
            }

            $sql->execute($params);

            $_SESSION['create-success'] = "Item updated successfully: " . htmlspecialchars($item_name);
            header("Location: " . $_SERVER['REQUEST_URI']);
            exit;
        }
    }
    public function archive_item()
    {
        if (isset($_POST['archive_item'])) {
            $item_id = filter_input(INPUT_POST, 'archive_item_id', FILTER_SANITIZE_NUMBER_INT);

            $archive = $this->conn()->prepare("UPDATE items SET is_deleted = 1 WHERE item_id = ?");
            $archive->execute([$item_id]);

            $_SESSION['create-success'] = "Archived product successfully.";

            header("Location: " . $_SERVER['REQUEST_URI']);
            exit;
        }
    }

    public function select_items()
    {
        $sql = $this->conn()->prepare("
        SELECT 
            items.*, 
            categories.category_name,
            suppliers.supplier_name
        FROM items
        LEFT JOIN categories ON items.category_id = categories.category_id
        LEFT JOIN suppliers ON items.supplier_id = suppliers.supplier_id
        WHERE items.is_deleted = 0
    ");
        $sql->execute();
        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalItemsCountPOS($search = '')
    {
        $sql = "SELECT COUNT(*) as total FROM items
            LEFT JOIN categories ON items.category_id = categories.category_id
            LEFT JOIN suppliers ON items.supplier_id = suppliers.supplier_id
            WHERE items.is_deleted = 0
            AND items.quantity >= 0";

        $params = [];

        if ($search !== '') {
            $sql .= " AND (items.item_name LIKE :search OR items.barcode LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }

        $stmt = $this->conn()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function select_items_paginated_POS($offset, $perPage, $search = '')
    {
        $sql = "SELECT 
                items.*, 
                categories.category_name,
                suppliers.supplier_name
            FROM items
            LEFT JOIN categories ON items.category_id = categories.category_id
            LEFT JOIN suppliers ON items.supplier_id = suppliers.supplier_id
            WHERE items.is_deleted = 0
            AND items.quantity >= 0";

        $params = [];

        if ($search !== '') {
            $sql .= " AND (items.item_name LIKE :search OR items.barcode LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }

        $sql .= " ORDER BY items.created_at DESC LIMIT :offset, :perPage";

        $stmt = $this->conn()->prepare($sql);

        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v, PDO::PARAM_STR);
        }
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':perPage', $perPage, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalStockItemsCount($search = '')
    {
        $sql = "SELECT COUNT(*) as total FROM items WHERE is_deleted = 0";
        $params = [];

        if ($search !== '') {
            $sql .= " AND (items.item_name LIKE :search OR items.barcode LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }

        $stmt = $this->conn()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function select_stock_items_paginated($offset, $perPage, $search = '')
    {
        $sql = "SELECT 
        items.*, 
        categories.category_name,
        suppliers.supplier_name,
        CASE 
            WHEN items.quantity <= 0 THEN 1
            WHEN items.quantity > 0 AND items.quantity <= items.min_stock THEN 2
            ELSE 3
        END as stock_priority
    FROM items
    LEFT JOIN categories ON items.category_id = categories.category_id
    LEFT JOIN suppliers ON items.supplier_id = suppliers.supplier_id
    WHERE items.is_deleted = 0";

        $params = [];

        if ($search !== '') {
            $sql .= " AND (items.item_name LIKE :search OR items.barcode LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }

        $sql .= " ORDER BY stock_priority ASC, items.item_name ASC 
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

    public function getTotalItemsCount($search = '', $categoryFilter = '', $priceFilter = '')
    {
        $sql = "SELECT COUNT(*) as total FROM items
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

        $sql .= " ORDER BY items.created_at DESC LIMIT :offset, :perPage";

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
                header("Location: " . $_SERVER['REQUEST_URI']);
                exit;
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
            header("Location: " . $_SERVER['REQUEST_URI']);
            exit;
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
            header("Location: " . $_SERVER['REQUEST_URI']);
            exit;
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
            header("Location: " . $_SERVER['REQUEST_URI']);
            exit;
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
            u.username,
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
        LEFT JOIN users u ON po.created_by = u.user_id
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

                    // Validation: prevent stock from going below 0
                    if ($new_quantity < 0) {
                        $errors[] = "Adjustment invalid: stock cannot go below 0.";
                    }
                }
            }

            if (!empty($errors)) {
                $_SESSION['adjust-error'] = implode("<br><br>", $errors);
                header("Location: " . $_SERVER['REQUEST_URI']);
                exit;
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

                    $_SESSION['adjust-success'] = "Stock successfully adjusted.";

                    header("Location: " . $_SERVER['REQUEST_URI']);
                    exit;
                } catch (PDOException $e) {
                    if ($conn->inTransaction()) {
                        $conn->rollBack();
                    }
                    $_SESSION['adjust-error'] = "Database error: " . $e->getMessage();
                    header("Location: " . $_SERVER['REQUEST_URI']);
                    exit;
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
            items i ON isa.item_id = i.item_id AND i.is_deleted = 0
        JOIN 
            users u ON isa.adjust_by = u.user_id
        ORDER BY isa.created_at DESC LIMIT 5
    ");

        $sql->execute();
        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    public function add_to_cart()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $item_id       = filter_input(INPUT_POST, 'item_id', FILTER_SANITIZE_NUMBER_INT);
        $pc_builder_id = filter_input(INPUT_POST, 'pc_builder_id', FILTER_SANITIZE_NUMBER_INT);
        $quantity      = (int) (filter_input(INPUT_POST, 'quantity', FILTER_SANITIZE_NUMBER_INT) ?? 1);

        if ($quantity <= 0) {
            $_SESSION['sale-error'] = "Invalid quantity.";
            return;
        }

        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        // ── PC BUILDER ──
        if ($pc_builder_id) {
            $pcItems = $this->getPcBuilderItems($pc_builder_id);

            if (empty($pcItems)) {
                $_SESSION['sale-error'] = "PC Build not found or has no items.";
                return;
            }

            // Stock check per component
            foreach ($pcItems as $component) {
                if ($component['stock'] <= 0) {
                    $_SESSION['sale-error'] = "Cannot add to cart. '{$component['item_name']}' is out of stock.";
                    return;
                }
                if ($component['stock'] < $component['quantity']) {
                    $_SESSION['sale-error'] = "Not enough stock for '{$component['item_name']}'. Only {$component['stock']} left.";
                    return;
                }
            }

            // Add each component as an individual cart item
            foreach ($pcItems as $component) {
                $cartKey = 'item_' . $component['item_id'];

                $currentQty     = isset($_SESSION['cart'][$cartKey]) ? $_SESSION['cart'][$cartKey]['quantity'] : 0;
                $requestedTotal = $currentQty + $component['quantity'];

                if ($requestedTotal > $component['stock']) {
                    $_SESSION['sale-error'] = "Not enough stock for '{$component['item_name']}'. Only {$component['stock']} left.";
                    return;
                }

                if (isset($_SESSION['cart'][$cartKey])) {
                    $_SESSION['cart'][$cartKey]['quantity'] += $component['quantity'];
                } else {
                    $fetchFull = $this->conn()->prepare("SELECT description, image FROM items WHERE item_id = ?");
                    $fetchFull->execute([$component['item_id']]);
                    $fullItem = $fetchFull->fetch(PDO::FETCH_ASSOC);

                    $livePrice  = (float) $component['selling_price'];  // i.selling_price
                    $savedPrice = (float) $component['unit_price'];     // pbi.unit_price
                    $hasCustom  = abs($savedPrice - $livePrice) > 0.001;

                    $_SESSION['cart'][$cartKey] = [
                        'item_id'           => $component['item_id'],
                        'name'              => $component['item_name'],
                        'description'       => $fullItem['description'] ?? '',
                        'image'             => $fullItem['image'] ?? '',
                        'quantity'          => $component['quantity'],
                        'unit_price'        => $livePrice,
                        'custom_unit_price' => $hasCustom ? $savedPrice : 0,
                        'line_total'        => ($hasCustom ? $savedPrice : $livePrice) * $component['quantity'],
                        'is_pc_builder'     => false
                    ];
                }

                // Recalculate line total using effective price
                $effectivePrice = isset($_SESSION['cart'][$cartKey]['custom_unit_price']) && $_SESSION['cart'][$cartKey]['custom_unit_price'] > 0
                    ? $_SESSION['cart'][$cartKey]['custom_unit_price']
                    : $_SESSION['cart'][$cartKey]['unit_price'];

                $_SESSION['cart'][$cartKey]['line_total'] =
                    $effectivePrice * $_SESSION['cart'][$cartKey]['quantity'];
            }

            $_SESSION['sale-success'] = "PC Build components added to cart.";
            return;
        }

        // ── REGULAR ITEM ──
        if ($item_id) {
            $stmt = $this->conn()->prepare("
            SELECT 
                item_id,
                item_name,
                description,
                image,
                selling_price,
                quantity AS stock
            FROM items
            WHERE item_id = ? AND is_deleted = 0
        ");
            $stmt->execute([$item_id]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$item) {
                $_SESSION['sale-error'] = "Item not found.";
                return;
            }

            if ($item['stock'] <= 0) {
                $_SESSION['sale-error'] = "'{$item['item_name']}' is out of stock.";
                return;
            }

            $cartKey        = 'item_' . $item_id;
            $currentQty     = isset($_SESSION['cart'][$cartKey]) ? $_SESSION['cart'][$cartKey]['quantity'] : 0;
            $requestedTotal = $currentQty + $quantity;

            if ($requestedTotal > $item['stock']) {
                $_SESSION['sale-error'] = "Not enough stock for '{$item['item_name']}'. Only {$item['stock']} left.";
                return;
            }

            if (isset($_SESSION['cart'][$cartKey])) {
                $_SESSION['cart'][$cartKey]['quantity'] += $quantity;
            } else {
                $_SESSION['cart'][$cartKey] = [
                    'item_id'       => $item_id,
                    'name'          => $item['item_name'],
                    'description'   => $item['description'],
                    'image'         => $item['image'],
                    'quantity'      => $quantity,
                    'unit_price'    => $item['selling_price'],
                    'line_total'    => $item['selling_price'] * $quantity,
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

    public function process_sale()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $cart = $_SESSION['cart'] ?? [];
            $cash_received = filter_input(INPUT_POST, 'cash_received', FILTER_VALIDATE_FLOAT);
            $customer_name = filter_input(INPUT_POST, 'customer', FILTER_SANITIZE_STRING);
            $payment_method = filter_input(INPUT_POST, 'payment_method', FILTER_SANITIZE_STRING);
            $ref_number = filter_input(INPUT_POST, 'ref_number', FILTER_SANITIZE_STRING) ?: null;
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
                $effective = isset($item['custom_unit_price']) && $item['custom_unit_price'] > 0
                    ? $item['custom_unit_price']
                    : $item['unit_price'];
                $total += $effective * $item['quantity'];
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

                foreach ($cart as $item) {
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
                    } else {
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

                $stmt = $conn->prepare("
                INSERT INTO sales (transaction_id, customer_name, grand_total, cash_received, cash_change, payment_method, ref_number, date, sold_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
                $stmt->execute([
                    $transaction_id,
                    $customer_name,
                    $grand_total,
                    $cash_received,
                    $change,
                    $payment_method,
                    $ref_number,
                    $philippineDateTime,
                    $user_id
                ]);

                $sale_id = $conn->lastInsertId();

                $itemStmt = $conn->prepare("
                INSERT INTO sale_items (sale_id, item_id, quantity, unit_price, custom_unit_price, line_total, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");

                $stockUpdate = $conn->prepare("
                UPDATE items SET quantity = quantity - ? WHERE item_id = ?
            ");

                $pcBuilderStmt = $conn->prepare("
                INSERT INTO sale_pc_builders (sale_id, pc_builder_id, pc_builder_name, selling_price, quantity, line_total, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");

                foreach ($cart as $item) {
                    if (isset($item['is_pc_builder']) && $item['is_pc_builder'] === true) {
                        $pc = $this->getPCBuilderById($item['pc_builder_id']);

                        $pcBuilderStmt->execute([
                            $sale_id,
                            $item['pc_builder_id'],
                            $item['name'],
                            $item['unit_price'],
                            $item['quantity'],
                            $item['line_total'],
                            $philippineDateTime
                        ]);

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
                                $stockUpdate->execute([$item['quantity'], $compId]);
                            }
                        }
                    } else {
                        $effectivePrice   = isset($item['custom_unit_price']) && $item['custom_unit_price'] > 0
                            ? $item['custom_unit_price']
                            : $item['unit_price'];
                        $customUnitPrice  = $item['custom_unit_price'] ?? 0.00;

                        $itemStmt->execute([
                            $sale_id,
                            $item['item_id'],
                            $item['quantity'],
                            $item['unit_price'],          // always the original selling price
                            $customUnitPrice,             // 0.00 if not overridden
                            $effectivePrice * $item['quantity'], // actual charged line_total
                            $philippineDateTime
                        ]);

                        $stockUpdate->execute([$item['quantity'], $item['item_id']]);
                    }
                }

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

    public function set_custom_price()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $cart_key         = filter_input(INPUT_POST, 'cart_key', FILTER_SANITIZE_STRING);
        $custom_unit_price = filter_input(INPUT_POST, 'custom_unit_price', FILTER_VALIDATE_FLOAT);

        if (!$cart_key || !isset($_SESSION['cart'][$cart_key])) {
            $_SESSION['sale-error'] = "Item not found in cart.";
            return;
        }

        if ($custom_unit_price === false || $custom_unit_price < 0) {
            $_SESSION['sale-error'] = "Invalid custom price.";
            return;
        }

        // Store custom price and recalculate line_total
        $qty = $_SESSION['cart'][$cart_key]['quantity'];
        $_SESSION['cart'][$cart_key]['custom_unit_price'] = $custom_unit_price;
        $_SESSION['cart'][$cart_key]['line_total']        = $custom_unit_price * $qty;
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

    public function getSalesChartData($period = 'week')
    {
        date_default_timezone_set('Asia/Manila');

        try {
            $conn = $this->conn();

            if ($period === 'month') {
                $startDate = date('Y-m-d', strtotime('-29 days')) . ' 00:00:00';
                $endDate   = date('Y-m-d') . ' 23:59:59';
                $format = '%Y-%m-%d';
            } else {
                $startDate = date('Y-m-d', strtotime('-6 days')) . ' 00:00:00';
                $endDate   = date('Y-m-d') . ' 23:59:59';
                $format = '%a';
            }

            $sql = "
    SELECT 
        DATE_FORMAT(`date`, '$format') as label,
        SUM(grand_total) as revenue,
        COUNT(sale_id) as transactions
    FROM sales
    WHERE `date` BETWEEN :startDate AND :endDate
    AND is_deleted = 0
    GROUP BY DATE(`date`)
    ORDER BY MIN(`date`) ASC
";

            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':startDate' => $startDate,
                ':endDate'   => $endDate
            ]);

            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $indexed = [];
            foreach ($results as $row) {
                $indexed[$row['label']] = $row;
            }

            $labels = [];
            $revenues = [];
            $transactions = [];

            $days = ($period === 'month') ? 29 : 6;

            for ($i = $days; $i >= 0; $i--) {
                $date = date('Y-m-d', strtotime("-$i days"));
                $label = ($period === 'month') ? $date : date('D', strtotime($date));

                $labels[]       = $label;
                $revenues[]     = isset($indexed[$label]) ? (float) $indexed[$label]['revenue'] : 0.0;
                $transactions[] = isset($indexed[$label]) ? (int)   $indexed[$label]['transactions'] : 0;
            }

            return [
                'labels'       => $labels,
                'revenues'     => $revenues,
                'transactions' => $transactions
            ];
        } catch (PDOException $e) {
            return [
                'labels'       => [],
                'revenues'     => [],
                'transactions' => []
            ];
        }
    }

    public function getTopSellingItems($limit = 5)
    {
        try {
            $conn = $this->conn();

            $sql = "
            SELECT 
                i.item_name,
                SUM(si.quantity) as total_sold,
                SUM(si.line_total) as total_revenue
            FROM sale_items si
            JOIN items i ON si.item_id = i.item_id
            JOIN sales s ON si.sale_id = s.sale_id
            WHERE s.is_deleted = 0
            AND i.is_deleted = 0
            GROUP BY si.item_id, i.item_name
            ORDER BY total_sold DESC
            LIMIT :limit
        ";

            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
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
        sale_id, 
        transaction_id, 
        customer_name, 
        grand_total, 
        payment_method,
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

        $sql .= " ORDER BY sales.date DESC LIMIT :offset, :perPage";

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

    public function decrease_cart_quantity()
    {
        $removeId = $_POST['remove_item_id'] ?? null;
        if (!$removeId) return;

        if (!isset($_SESSION['cart'][$removeId])) {
            $_SESSION['sale-error'] = "Item not found in cart.";
            return;
        }

        $_SESSION['cart'][$removeId]['quantity'] -= 1;

        if ($_SESSION['cart'][$removeId]['quantity'] <= 0) {
            unset($_SESSION['cart'][$removeId]);
            $_SESSION['sale-success'] = "Item removed from cart.";
            return;
        }

        // Use custom price if set
        $effectivePrice = isset($_SESSION['cart'][$removeId]['custom_unit_price']) && $_SESSION['cart'][$removeId]['custom_unit_price'] > 0
            ? $_SESSION['cart'][$removeId]['custom_unit_price']
            : $_SESSION['cart'][$removeId]['unit_price'];

        $_SESSION['cart'][$removeId]['line_total'] = $effectivePrice * $_SESSION['cart'][$removeId]['quantity'];

        $_SESSION['sale-success'] = "Item quantity decreased.";
    }

    public function increase_cart_quantity()
    {
        $itemId = $_POST['increase_item_id'] ?? null;
        if (!$itemId) return;

        if (!isset($_SESSION['cart'][$itemId])) {
            $_SESSION['sale-error'] = "Item not found in cart.";
            return;
        }

        $_SESSION['cart'][$itemId]['quantity'] += 1;

        // Use custom price if set
        $effectivePrice = isset($_SESSION['cart'][$itemId]['custom_unit_price']) && $_SESSION['cart'][$itemId]['custom_unit_price'] > 0
            ? $_SESSION['cart'][$itemId]['custom_unit_price']
            : $_SESSION['cart'][$itemId]['unit_price'];

        $_SESSION['cart'][$itemId]['line_total'] = $effectivePrice * $_SESSION['cart'][$itemId]['quantity'];

        $_SESSION['sale-success'] = "Item quantity increased.";
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

        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    }

    public function saveCartAsPcBuild()
    {

        $cart            = $_SESSION['cart'] ?? [];
        $user_id         = $_SESSION['user_id'] ?? null;
        $pc_builder_name = trim($_POST['pc_builder_name'] ?? '');
        $override        = isset($_POST['override_build']) && $_POST['override_build'] === '1';

        $errors = [];

        if ($pc_builder_name === '') {
            $errors[] = "Build name is required.";
        } elseif (strlen($pc_builder_name) > 100) {
            $errors[] = "Build name must be less than 100 characters.";
        }

        if (empty($cart)) {
            $errors[] = "Cart is empty. Add items before saving a build.";
        }

        if (!empty($errors)) {
            $_SESSION['sale-error'] = implode("<br><br>", $errors);
            return;
        }

        $pdo = $this->conn();

        // Check for duplicate
        $stmt = $pdo->prepare("SELECT pc_builder_id FROM pc_builders WHERE pc_builder_name = ? AND user_id = ? AND is_deleted = 0");
        $stmt->execute([$pc_builder_name, $user_id]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        // Duplicate found and no override confirmation
        if ($existing && !$override) {
            $_SESSION['sale-duplicate'] = $pc_builder_name;
            return;
        }

        date_default_timezone_set('Asia/Manila');
        $createdAt = date('Y-m-d H:i:s');

        try {
            $pdo->beginTransaction();

            if ($existing && $override) {
                // Delete old items and update the existing build
                $pc_builder_id = $existing['pc_builder_id'];

                $pdo->prepare("DELETE FROM pc_builder_items WHERE pc_builder_id = ?")
                    ->execute([$pc_builder_id]);

                $pdo->prepare("UPDATE pc_builders SET created_at = ? WHERE pc_builder_id = ?")
                    ->execute([$createdAt, $pc_builder_id]);
            } else {
                // Insert new build
                $stmt = $pdo->prepare("
                INSERT INTO pc_builders (pc_builder_name, user_id, created_at)
                VALUES (?, ?, ?)
            ");
                $stmt->execute([$pc_builder_name, $user_id, $createdAt]);
                $pc_builder_id = $pdo->lastInsertId();
            }

            // Insert cart items
            $insertItem = $pdo->prepare("
            INSERT INTO pc_builder_items (pc_builder_id, category_id, item_id, quantity, unit_price)
            VALUES (?, ?, ?, ?, ?)
        ");

            foreach ($cart as $cartItem) {
                if (!empty($cartItem['is_pc_builder'])) continue;

                $fetchItem = $pdo->prepare("SELECT category_id FROM items WHERE item_id = ?");
                $fetchItem->execute([$cartItem['item_id']]);
                $itemData = $fetchItem->fetch(PDO::FETCH_ASSOC);

                if (!$itemData) continue;

                $effectivePrice = isset($cartItem['custom_unit_price']) && $cartItem['custom_unit_price'] > 0
                    ? $cartItem['custom_unit_price']
                    : $cartItem['unit_price'];

                $insertItem->execute([
                    $pc_builder_id,
                    $itemData['category_id'],
                    $cartItem['item_id'],
                    $cartItem['quantity'],
                    $effectivePrice
                ]);
            }

            $pdo->commit();

            $action = ($existing && $override) ? 'updated' : 'saved';
            $_SESSION['sale-success'] = "PC Build '{$pc_builder_name}' {$action} successfully.";
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $_SESSION['sale-error'] = "Failed to save PC Build. Please try again.";
        }
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

        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
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

            header("Location: " . $_SERVER['REQUEST_URI']);
            exit;
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
        $sql = "SELECT item_id, item_name, selling_price, image
            FROM items
            WHERE category_id = ?";

        $stmt = $this->conn()->prepare($sql);
        $stmt->execute([$categoryId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalCategoriesCount($search = '')
    {
        $sql = "SELECT COUNT(*) as total FROM categories WHERE is_deleted = 0";
        $params = [];

        if ($search !== '') {
            $sql .= " AND category_name LIKE :search";
            $params[':search'] = '%' . $search . '%';
        }

        $stmt = $this->conn()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function select_categories_paginated($offset, $perPage, $search = '')
    {
        $sql = "SELECT * FROM categories WHERE is_deleted = 0";
        $params = [];

        if ($search !== '') {
            $sql .= " AND category_name LIKE :search";
            $params[':search'] = '%' . $search . '%';
        }

        $sql .= " ORDER BY created_at DESC LIMIT :offset, :perPage";

        $stmt = $this->conn()->prepare($sql);

        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v, PDO::PARAM_STR);
        }
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':perPage', $perPage, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalStaffsCount($search = '')
    {
        $sql = "SELECT COUNT(*) as total FROM users WHERE role = 'staff'";
        $params = [];

        if ($search !== '') {
            $sql .= " AND username LIKE :search";  // note AND because we already have WHERE role
            $like = '%' . $search . '%';
            $params[':search'] = $like;
        }

        $stmt = $this->conn()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function select_staffs_paginated($offset, $perPage, $search = '')
    {
        $sql = "SELECT * FROM users WHERE role != 'admin'";
        $params = [];

        if ($search !== '') {
            $sql .= " AND username LIKE :search";
            $like = '%' . $search . '%';
            $params[':search'] = $like;
        }

        $sql .= " ORDER BY created_at DESC LIMIT :offset, :perPage";

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
            INSERT INTO pc_builders (pc_builder_name, user_id, created_at)
            VALUES (?, ?, ?)
        ");
            $stmt->execute([$pc_builder_name, $user_id, $createdAt]);

            $pc_builder_id = $pdo->lastInsertId();
            $categories = $this->getAllCategories();

            $insertItem = $pdo->prepare("
            INSERT INTO pc_builder_items
            (pc_builder_id, category_id, item_id, quantity, unit_price)
            VALUES (?, ?, ?, ?, ?)
        ");

            foreach ($categories as $category) {
                $catId   = $category['category_id'];
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

                    // Fetch selling_price to store as unit_price
                    $priceStmt = $pdo->prepare("SELECT selling_price FROM items WHERE item_id = ?");
                    $priceStmt->execute([$itemId]);
                    $unitPrice = (float) ($priceStmt->fetchColumn() ?: 0);

                    $insertItem->execute([
                        $pc_builder_id,
                        $catId,
                        $itemId,
                        $quantity,
                        $unitPrice
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

    public function archiveQuotation()
    {
        if (isset($_POST['archive-quote-btn'])) {

            $pcBuilderId = (int) ($_POST['pc_builder_id'] ?? 0);
            $userId      = $_SESSION['user_id'] ?? null;

            $stmt = $this->conn()->prepare("
        UPDATE pc_builders 
        SET is_deleted = 1 
        WHERE pc_builder_id = ? AND user_id = ?
    ");
            $stmt->execute([$pcBuilderId, $userId]);
            $_SESSION['create-success'] = "Quotation archived successfully.";
            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit;
        }
    }

    public function getSaleDetails($saleId)
    {
        $sqlSale = "
        SELECT 
            s.sale_id,
            s.transaction_id,
            s.customer_name,
            s.grand_total,
            s.cash_received,
            s.cash_change,
            s.payment_method,
            s.ref_number,
            s.date,
            u.username
        FROM sales s
        LEFT JOIN users u ON s.sold_by = u.user_id
        WHERE s.sale_id = :sale_id
          AND s.is_deleted = 0
        LIMIT 1
    ";

        $stmt = $this->conn()->prepare($sqlSale);
        $stmt->bindValue(':sale_id', $saleId, PDO::PARAM_INT);
        $stmt->execute();

        $sale = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$sale) {
            return false;
        }

        $sqlItems = "
        SELECT 
            i.item_name,
            si.quantity,
            si.unit_price,
            si.custom_unit_price,
            si.line_total
        FROM sale_items si
        INNER JOIN items i ON si.item_id = i.item_id
        WHERE si.sale_id = :sale_id
        ORDER BY si.sale_item_id ASC
    ";

        $stmtItems = $this->conn()->prepare($sqlItems);
        $stmtItems->bindValue(':sale_id', $saleId, PDO::PARAM_INT);
        $stmtItems->execute();

        $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

        return [
            'sale' => $sale,
            'items' => $items,
            'grand_total' => $sale['grand_total']
        ];
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
            pbi.unit_price,
            c.category_id,
            c.category_name,
            c.category_type,
            i.item_id,
            i.item_name,
            i.selling_price,
            (pbi.unit_price * pbi.quantity) as line_total
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
        $pcParts     = [];
        $accessories = [];

        foreach ($items as $item) {
            if ($item['category_type'] === 'pc_part') {
                $pcParts[] = $item;
            } else {
                $accessories[] = $item;
            }
        }

        return [
            'builder'     => $builder,
            'pc_parts'    => $pcParts,
            'accessories' => $accessories,
            'items'       => $items,
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

    public function getPcBuildersCount($search = '')
    {
        $sql = "SELECT COUNT(*) FROM pc_builders WHERE 1=1";
        $params = [];

        if ($search !== '') {
            $sql .= " AND pc_builder_name LIKE :search";
            $params[':search'] = '%' . $search . '%';
        }

        $stmt = $this->conn()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function getPcBuildersPaginated($search = '', $offset = 0, $limit = 5)
    {
        $sql = "SELECT pb.*, 
                u.username AS created_by,
                COUNT(pbi.pc_builder_item_id) AS item_count,
                SUM(pbi.unit_price * pbi.quantity) AS total_price
            FROM pc_builders pb
            LEFT JOIN users u ON u.user_id = pb.user_id
            LEFT JOIN pc_builder_items pbi ON pb.pc_builder_id = pbi.pc_builder_id
            WHERE pb.is_deleted = 0";
        $params = [];

        if ($search !== '') {
            $sql .= " AND pb.pc_builder_name LIKE :search";
            $params[':search'] = '%' . $search . '%';
        }

        $sql .= " GROUP BY pb.pc_builder_id ORDER BY pb.created_at DESC LIMIT :offset, :limit";

        $stmt = $this->conn()->prepare($sql);

        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v, PDO::PARAM_STR);
        }
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStockAlertCounts()
    {
        $stmt = $this->conn->prepare("
        SELECT 
            SUM(CASE WHEN quantity = 0 THEN 1 ELSE 0 END) AS out_of_stock,
            SUM(CASE WHEN quantity > 0 AND quantity <= min_stock THEN 1 ELSE 0 END) AS low_stock
        FROM items
        WHERE is_deleted = 0
    ");
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getTotalPcBuildersCountPOS($search = '')
    {
        $sql = "SELECT COUNT(*) FROM pc_builders WHERE is_deleted = 0";
        $params = [];

        if ($search !== '') {
            $sql .= " AND pc_builder_name LIKE :search";
            $params[':search'] = '%' . $search . '%';
        }

        $stmt = $this->conn()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function getPcBuildersPOSPaginated($offset, $perPage, $search = '')
    {
        $sql = "SELECT pb.*,
            u.username,
            COUNT(pbi.pc_builder_item_id) AS item_count,
            SUM(pbi.unit_price * pbi.quantity) AS total_price
        FROM pc_builders pb
        LEFT JOIN users u ON pb.user_id = u.user_id
        LEFT JOIN pc_builder_items pbi ON pb.pc_builder_id = pbi.pc_builder_id
        WHERE pb.is_deleted = 0";

        $params = [];

        if ($search !== '') {
            $sql .= " AND pb.pc_builder_name LIKE :search";
            $params[':search'] = '%' . $search . '%';
        }

        $sql .= " GROUP BY pb.pc_builder_id ORDER BY pb.created_at DESC LIMIT :offset, :perPage";

        $stmt = $this->conn()->prepare($sql);

        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v, PDO::PARAM_STR);
        }
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':perPage', $perPage, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPcBuilderItems($pc_builder_id)
    {
        $stmt = $this->conn()->prepare("
        SELECT 
            pbi.*,
            i.item_name,
            i.selling_price,
            i.quantity AS stock,
            i.min_stock,
            c.category_name
        FROM pc_builder_items pbi
        JOIN items i ON pbi.item_id = i.item_id
        JOIN categories c ON pbi.category_id = c.category_id
        WHERE pbi.pc_builder_id = ?
    ");
        $stmt->execute([$pc_builder_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createRma()
    {
        if (!isset($_POST['rma-submit-btn'])) return;

        $customerName = trim($_POST['customer_name'] ?? '');
        $saleId       = !empty($_POST['sale_id']) ? (int) $_POST['sale_id'] : null;
        $condition    = trim($_POST['condition'] ?? 'Defective');
        $status       = trim($_POST['status'] ?? 'Pending');
        $reason       = trim($_POST['reason'] ?? '');
        $userId       = $_SESSION['user_id'] ?? null;
        $itemIds      = $_POST['item_id'] ?? [];
        $quantities   = $_POST['quantity'] ?? [];

        $errors = [];

        if ($customerName === '') $errors[] = "Customer name is required.";
        if ($reason === '')       $errors[] = "Reason is required.";
        if (empty($itemIds))      $errors[] = "At least one item is required.";
        if (!$saleId) $errors[] = "Please select a related transaction.";

        // Validate each item row
        foreach ($itemIds as $index => $itemId) {
            if (empty($itemId)) {
                $errors[] = "Please select an item for row " . ($index + 1) . ".";
            }
            $qty = (int) ($quantities[$index] ?? 0);
            if ($qty <= 0) {
                $errors[] = "Invalid quantity for row " . ($index + 1) . ".";
            }
        }

        if (!empty($errors)) {
            $_SESSION['rma-error'] = implode("<br>", $errors);
            return;
        }

        $pdo = $this->conn();

        // Generate unique RMA number
        $checkRma = $pdo->prepare("SELECT COUNT(*) FROM rma WHERE rma_number = ?");
        $attempt  = 0;
        do {
            $randomSuffix = rand(1000, 9999);
            $year         = date('y');
            $month        = date('m');
            $rmaNumber    = "RMA-{$year}{$month}-{$randomSuffix}";
            $checkRma->execute([$rmaNumber]);
            $exists  = $checkRma->fetchColumn() > 0;
            $attempt++;
        } while ($exists && $attempt < 10);

        if ($attempt >= 10) {
            $_SESSION['rma-error'] = "Failed to generate a unique RMA number.";
            return;
        }

        date_default_timezone_set('Asia/Manila');
        $date = date('Y-m-d H:i:s');

        try {
            $pdo->beginTransaction();

            // Insert RMA header
            $stmt = $pdo->prepare("
            INSERT INTO rma
                (rma_number, sale_id, customer_name, `condition`, `status`, reason, `date`, created_by)
            VALUES
                (?, ?, ?, ?, ?, ?, ?, ?)
        ");
            $stmt->execute([
                $rmaNumber,
                $saleId,
                $customerName,
                $condition,
                $status,
                $reason,
                $date,
                $userId
            ]);

            $rmaId = $pdo->lastInsertId();

            // Insert RMA items
            $itemStmt = $pdo->prepare("
            INSERT INTO rma_items (rma_id, item_id, quantity)
            VALUES (?, ?, ?)
        ");

            foreach ($itemIds as $index => $itemId) {
                $itemId  = (int) $itemId;
                $qty     = (int) ($quantities[$index] ?? 1);
                $itemStmt->execute([$rmaId, $itemId, $qty]);
            }

            $pdo->commit();

            $_SESSION['rma-success'] = "RMA {$rmaNumber} created successfully.";
            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit;
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $_SESSION['rma-error'] = "Failed to create RMA: " . $e->getMessage();
        }
    }

    public function getRmaCount($search = '')
    {
        $sql    = "SELECT COUNT(*) FROM rma WHERE is_deleted = 0";
        $params = [];

        if ($search !== '') {
            $sql .= " AND (rma_number LIKE :search OR customer_name LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }

        $stmt = $this->conn()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function getRmaPaginated($search = '', $offset = 0, $limit = 10)
    {
        $sql = "
        SELECT
            r.*,
            u.username AS created_by,
            COUNT(ri.rma_item_id) AS item_count,
            GROUP_CONCAT(i.item_name SEPARATOR ', ') AS item_names
        FROM rma r
        LEFT JOIN users u ON r.created_by = u.user_id
        LEFT JOIN rma_items ri ON r.rma_id = ri.rma_id
        LEFT JOIN items i ON ri.item_id = i.item_id
        WHERE r.is_deleted = 0
    ";
        $params = [];

        if ($search !== '') {
            $sql .= " AND (r.rma_number LIKE :search OR r.customer_name LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }

        $sql .= " GROUP BY r.rma_id ORDER BY r.date DESC LIMIT :offset, :limit";

        $stmt = $this->conn()->prepare($sql);

        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v, PDO::PARAM_STR);
        }
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSalesForRma()
    {
        $stmt = $this->conn()->prepare("
        SELECT 
            sale_id,
            transaction_id,
            customer_name,
            date
        FROM sales
        WHERE is_deleted = 0
        ORDER BY date DESC
        LIMIT 100
    ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllActiveItems()
    {
        $stmt = $this->conn()->prepare("
        SELECT 
            i.item_id,
            i.item_name,
            i.category_id,
            c.category_name
        FROM items i
        LEFT JOIN categories c ON i.category_id = c.category_id
        WHERE i.is_deleted = 0
        ORDER BY c.category_name ASC, i.item_name ASC
    ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRmaDetails($rmaId)
    {
        $pdo = $this->conn();

        // Get RMA header
        $stmt = $pdo->prepare("
        SELECT
            r.*,
            u.username AS created_by
        FROM rma r
        LEFT JOIN users u ON r.created_by = u.user_id
        WHERE r.rma_id = ? AND r.is_deleted = 0
        LIMIT 1
    ");
        $stmt->execute([$rmaId]);
        $rma = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$rma) return null;

        // Get RMA items
        $itemStmt = $pdo->prepare("
        SELECT
            ri.rma_item_id,
            ri.quantity,
            i.item_id,
            i.item_name,
            i.selling_price
        FROM rma_items ri
        INNER JOIN items i ON ri.item_id = i.item_id
        WHERE ri.rma_id = ?
        ORDER BY ri.rma_item_id ASC
    ");
        $itemStmt->execute([$rmaId]);
        $items = $itemStmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'rma'   => $rma,
            'items' => $items
        ];
    }

    public function updateRma()
    {
        if (!isset($_POST['edit-rma-btn'])) return;

        $rmaId        = (int) ($_POST['rma_id'] ?? 0);
        $customerName = trim($_POST['customer_name'] ?? '');
        $saleId       = !empty($_POST['sale_id']) ? (int) $_POST['sale_id'] : null;
        $condition    = trim($_POST['condition'] ?? 'Defective');
        $status       = trim($_POST['status'] ?? 'Pending');
        $reason       = trim($_POST['reason'] ?? '');

        $errors = [];

        if ($rmaId <= 0)           $errors[] = "Invalid RMA.";
        if ($customerName === '')  $errors[] = "Customer name is required.";
        if (!$saleId)             $errors[] = "Please select a related transaction.";
        if ($reason === '')        $errors[] = "Reason is required.";

        if (!empty($errors)) {
            $_SESSION['rma-error'] = implode("<br>", $errors);
            return;
        }

        $stmt = $this->conn()->prepare("
        UPDATE rma
        SET 
            customer_name = ?,
            sale_id       = ?,
            `condition`   = ?,
            `status`      = ?,
            reason        = ?
        WHERE rma_id = ? AND is_deleted = 0
    ");
        $stmt->execute([
            $customerName,
            $saleId,
            $condition,
            $status,
            $reason,
            $rmaId
        ]);

        $_SESSION['rma-success'] = "RMA updated successfully.";
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }

    public function register_admin()
    {
        if (!isset($_POST['register_admin'])) return;

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $role     = 'admin';

        $errors = [];

        if ($username === '')       $errors[] = "Username is required.";
        if (strlen($username) > 50) $errors[] = "Username must be under 50 characters.";
        if ($password === '')       $errors[] = "Password is required.";
        if (strlen($password) < 6)  $errors[] = "Password must be at least 6 characters.";

        if (!empty($errors)) {
            $_SESSION['register-admin-error'] = implode("<br>", $errors);
            return;
        }

        $pdo = $this->conn();

        // Check duplicate username
        $stmt = $pdo->prepare("SELECT 1 FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $_SESSION['register-admin-error'] = "Username already exists.";
            return;
        }

        $hashed = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("
        INSERT INTO users (username, password, role, is_active)
        VALUES (?, ?, ?, 1)
    ");
        $stmt->execute([$username, $hashed, $role]);

        $_SESSION['register-admin-success'] = "Admin '{$username}' added successfully.";
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }

    public function update_admin()
    {
        if (!isset($_POST['update_admin'])) return;

        $userId   = (int) ($_POST['user_id'] ?? 0);
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        $errors = [];

        if ($userId <= 0)           $errors[] = "Invalid admin.";
        if ($username === '')       $errors[] = "Username is required.";
        if (strlen($username) > 50) $errors[] = "Username must be under 50 characters.";
        if ($password !== '' && strlen($password) < 6) {
            $errors[] = "Password must be at least 6 characters.";
        }

        if (!empty($errors)) {
            $_SESSION['register-admin-error'] = implode("<br>", $errors);
            return;
        }

        $pdo = $this->conn();

        // Check duplicate username excluding current user
        $stmt = $pdo->prepare("SELECT 1 FROM users WHERE username = ? AND user_id != ?");
        $stmt->execute([$username, $userId]);
        if ($stmt->fetch()) {
            $_SESSION['register-admin-error'] = "Username already taken.";
            return;
        }

        if ($password !== '') {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt   = $pdo->prepare("UPDATE users SET username = ?, password = ? WHERE user_id = ? AND role = 'admin'");
            $stmt->execute([$username, $hashed, $userId]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET username = ? WHERE user_id = ? AND role = 'admin'");
            $stmt->execute([$username, $userId]);
        }

        $_SESSION['register-admin-success'] = "Admin '{$username}' updated successfully.";
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }

    public function deactivate_admin()
    {
        if (!isset($_POST['deactivate_admin'])) return;

        $userId = (int) ($_POST['user_id'] ?? 0);

        if ($userId <= 0) {
            $_SESSION['register-admin-error'] = "Invalid admin.";
            return;
        }

        $stmt = $this->conn()->prepare("
        UPDATE users SET is_active = 0 WHERE user_id = ? AND role = 'admin'
    ");
        $stmt->execute([$userId]);

        $_SESSION['register-admin-success'] = "Admin deactivated successfully.";
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }

    public function activate_admin()
    {
        if (!isset($_POST['activate_admin'])) return;

        $userId = (int) ($_POST['user_id'] ?? 0);

        if ($userId <= 0) {
            $_SESSION['register-admin-error'] = "Invalid admin.";
            return;
        }

        $stmt = $this->conn()->prepare("
        UPDATE users SET is_active = 1 WHERE user_id = ? AND role = 'admin'
    ");
        $stmt->execute([$userId]);

        $_SESSION['register-admin-success'] = "Admin activated successfully.";
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }

    public function getTotalAdminsCount($search = '')
    {
        $sql    = "SELECT COUNT(*) FROM users WHERE role = 'admin'";
        $params = [];

        if ($search !== '') {
            $sql .= " AND username LIKE :search";
            $params[':search'] = '%' . $search . '%';
        }

        $stmt = $this->conn()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function select_admins_paginated($offset = 0, $limit = 5, $search = '')
    {
        $sql    = "SELECT user_id, username, role, is_active FROM users WHERE role = 'admin'";
        $params = [];

        if ($search !== '') {
            $sql .= " AND username LIKE :search";
            $params[':search'] = '%' . $search . '%';
        }

        $sql .= " ORDER BY username ASC LIMIT :offset, :limit";

        $stmt = $this->conn()->prepare($sql);

        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v, PDO::PARAM_STR);
        }
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function delete_admin()
    {
        if (!isset($_POST['delete_admin'])) return;

        $userId = (int) ($_POST['user_id'] ?? 0);

        if ($userId <= 0) {
            $_SESSION['register-admin-error'] = "Invalid admin.";
            return;
        }

        $stmt = $this->conn()->prepare("
        DELETE FROM users WHERE user_id = ? AND role = 'admin'
    ");
        $stmt->execute([$userId]);

        $_SESSION['register-admin-success'] = "Admin deleted permanently.";
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }
}
$database = new Database();
