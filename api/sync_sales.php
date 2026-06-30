<?php
// api/sync_sales.php
header('Content-Type: application/json');
require_once '../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized! Please login again.']);
    exit;
}

$user_id = $_SESSION['user_id']; 
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid or empty JSON payload']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Idempotency check (Duplicate orders block)
    $checkQuery = "SELECT id FROM sales WHERE offline_id = ? OR invoice_no = ?";
    $checkStmt = $pdo->prepare($checkQuery);
    $checkStmt->execute([$data['offline_id'], $data['invoice_no']]);
    
    if ($checkStmt->fetch()) {
        $pdo->rollBack();
        echo json_encode(['success' => true, 'message' => 'Transaction already synced previously.']);
        exit;
    }

    $customer_id = (!empty($data['customer_id']) && $data['customer_id'] != "1") ? $data['customer_id'] : null;

    // Sales table entry
    $saleQuery = "INSERT INTO sales (invoice_no, user_id, customer_id, total_amount, discount, payable_amount, payment_method, offline_id) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $saleStmt = $pdo->prepare($saleQuery);
    $saleStmt->execute([
        $data['invoice_no'],
        $user_id,
        $customer_id,
        $data['total_amount'],
        $data['discount'],
        $data['payable_amount'],
        $data['payment_method'],
        $data['offline_id']
    ]);

    $sale_id = $pdo->lastInsertId();

    // Sale Items & Inventory Update Queries
    $itemQuery = "INSERT INTO sale_items (sale_id, product_id, cost_price, quantity, unit_price, subtotal) VALUES (?, ?, ?, ?, ?, ?)";
    $itemStmt = $pdo->prepare($itemQuery);

    $stockQuery = "UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ? AND user_id = ?";
    $stockStmt = $pdo->prepare($stockQuery);

    // Har product ki cost price live database se nikalne ki query
    $costQuery = "SELECT cost_price FROM products WHERE id = ? AND user_id = ?";
    $costStmt = $pdo->prepare($costQuery);

    foreach ($data['items'] as $item) {
        // Safe check: Product ki current cost price backend par fetch karein
        $costStmt->execute([$item['product_id'], $user_id]);
        $prodData = $costStmt->fetch(PDO::FETCH_ASSOC);
        $current_cost_price = $prodData['cost_price'] ?? 0.00;

        // Sale item save karein cost_price ke sath
        $itemStmt->execute([
            $sale_id,
            $item['product_id'],
            $current_cost_price,
            $item['quantity'],
            $item['unit_price'],
            $item['subtotal']
        ]);

        // Stock levels kam karein
        $stockStmt->execute([
            $item['quantity'],
            $item['product_id'],
            $user_id
        ]);
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Sale recorded successfully with cost parameters.']);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'message' => 'Database Sync Failed: ' . $e->getMessage()]);
}
?>