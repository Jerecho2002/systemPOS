<?php
include "database/database.php";
$database->login_session();

$id   = (int) ($_GET['id'] ?? 0);
$data = $database->getRmaDetails($id);

if (!$data) {
    http_response_code(404);
    echo json_encode(['error' => 'Not found']);
    exit;
}

$rma = $data['rma'];

// Fetch transaction_id if sale_id exists
if (!empty($rma['sale_id'])) {
    $stmt = $database->conn()->prepare("SELECT transaction_id, customer_name FROM sales WHERE sale_id = ?");
    $stmt->execute([$rma['sale_id']]);
    $sale = $stmt->fetch(PDO::FETCH_ASSOC);
    $rma['transaction_id'] = $sale['transaction_id'] ?? '';
}

header('Content-Type: application/json');
echo json_encode($rma);
