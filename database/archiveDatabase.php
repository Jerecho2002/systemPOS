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
        }
    }

    private array $archiveTables = [
        'categories' => [
            'table' => 'categories',
            'primary' => 'category_id',
            'search_column' => 'category_name',
            'label' => 'Categories',
            'display_columns' => [
                'category_name' => 'Category Name',
                'category_type' => 'Type',
                'category_description' => 'Description',
                'supports_quantity' => 'Supports Quantity'
            ]
        ],
        'items' => [
            'table' => 'items',
            'primary' => 'item_id',
            'search_column' => 'item_name',
            'label' => 'Items',
            'display_columns' => [
                'barcode' => 'Barcode',
                'item_name' => 'Item Name',
                'cost_price' => 'Cost Price',
                'selling_price' => 'Selling Price',
                'quantity' => 'Stock'
            ]
        ],
        'suppliers' => [
            'table' => 'suppliers',
            'primary' => 'supplier_id',
            'search_column' => 'supplier_name',
            'label' => 'Suppliers',
            'display_columns' => [
                'supplier_name' => 'Supplier Name',
                'contact_number' => 'Contact Number',
                'email' => 'Email',
                'status' => 'Status'
            ]
        ],
        'purchase_orders' => [
            'table' => 'purchase_orders',
            'primary' => 'purchase_order_id',
            'search_column' => 'po_number',
            'label' => 'Purchase Orders',
            'display_columns' => [
                'po_number' => 'PO Number',
                'grand_total' => 'Grand Total',
                'status' => 'Status',
                'date' => 'Date'
            ]
        ],
        'sales' => [
            'table' => 'sales',
            'primary' => 'sale_id',
            'search_column' => 'transaction_id',
            'label' => 'Sales',
            'display_columns' => [
                'transaction_id' => 'Transaction ID',
                'customer_name' => 'Customer Name',
                'grand_total' => 'Grand Total',
                'payment_method' => 'Payment Method',
                'date' => 'Date'
            ]
        ],
    ];

    public function getTableConfig(string $type): ?array
    {
        return $this->archiveTables[$type] ?? null;
    }

    public function restoreArchived(string $type, int $id): bool
    {
        if (!isset($this->archiveTables[$type])) {
            return false;
        }

        $cfg = $this->archiveTables[$type];

        $sql = "UPDATE {$cfg['table']}
            SET is_deleted = 0
            WHERE {$cfg['primary']} = :id";

        $stmt = $this->conn()->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    public function permanentDelete(string $type, int $id): bool
    {
        if (!isset($this->archiveTables[$type])) {
            return false;
        }

        $cfg = $this->archiveTables[$type];

        $sql = "DELETE FROM {$cfg['table']}
            WHERE {$cfg['primary']} = :id AND is_deleted = 1";

        $stmt = $this->conn()->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    public function clearAllArchives(string $type): bool
    {
        if (!isset($this->archiveTables[$type])) {
            return false;
        }

        $cfg = $this->archiveTables[$type];

        $sql = "DELETE FROM {$cfg['table']} WHERE is_deleted = 1";

        $stmt = $this->conn()->prepare($sql);
        return $stmt->execute();
    }

    public function getArchivedTotal(string $type, string $search = ''): int
    {
        if (!isset($this->archiveTables[$type])) {
            return 0;
        }

        $cfg = $this->archiveTables[$type];

        $sql = "SELECT COUNT(*) FROM {$cfg['table']} WHERE is_deleted = 1";
        $params = [];

        if ($search !== '') {
            $sql .= " AND {$cfg['search_column']} LIKE :search";
            $params[':search'] = "%$search%";
        }

        $stmt = $this->conn()->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }


    public function getArchivedPaginated(
        string $type,
        int $offset,
        int $perPage,
        string $search = ''
    ): array {
        if (!isset($this->archiveTables[$type])) {
            return [];
        }

        $cfg = $this->archiveTables[$type];

        $sql = "SELECT * FROM {$cfg['table']} WHERE is_deleted = 1";
        $params = [];

        if ($search !== '') {
            $sql .= " AND {$cfg['search_column']} LIKE :search";
            $params[':search'] = "%$search%";
        }

        $sql .= " ORDER BY {$cfg['primary']} DESC LIMIT :offset, :perPage";

        $stmt = $this->conn()->prepare($sql);

        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v, PDO::PARAM_STR);
        }

        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':perPage', $perPage, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

$database = new Database();
