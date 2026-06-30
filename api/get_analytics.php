<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Sahi path config folder ke liye
require_once '../config/config.php'; 

try {
    // Check karein agar config file ka $pdo variable sahi tarah load hua hai
    if (!isset($pdo)) {
        throw new Exception("Database connection variable ($pdo) is missing.");
    }

    // 1. Total Orders aur Revenue (PDO Syntax)
    $salesQuery = "SELECT 
                    COUNT(id) as total_orders, 
                    SUM(payable_amount) as total_revenue, 
                    SUM(discount) as total_discount 
                   FROM sales";
    $salesResult = $pdo->query($salesQuery);
    $salesData = $salesResult->fetch(); // PDO default fetch mode config mein set hai

    $total_orders   = (int)($salesData['total_orders'] ?? 0);
    $total_revenue  = (float)($salesData['total_revenue'] ?? 0);
    $total_discount = (float)($salesData['total_discount'] ?? 0);

    // 2. Gross Profit from sale_items (PDO Syntax)
    $profitQuery = "SELECT SUM((unit_price - cost_price) * quantity) as gross_profit FROM sale_items";
    $profitResult = $pdo->query($profitQuery);
    $profitData = $profitResult->fetch();
    $gross_profit = (float)($profitData['gross_profit'] ?? 0);

    // 3. Total Products Count (PDO Syntax)
    $productsCountQuery = "SELECT COUNT(id) as total_products FROM products";
    $productsCountResult = $pdo->query($productsCountQuery);
    $productsCountData = $productsCountResult->fetch();
    $total_products = (int)($productsCountData['total_products'] ?? 0);

    // Net Profit Calculation
    $net_profit = $gross_profit - $total_discount;
    if ($net_profit < 0) $net_profit = 0;

    // CLEAN FLAT RESPONSE
    echo json_encode([
        'status'         => 'success',
        'total_orders'   => $total_orders,
        'total_products' => $total_products,
        'total_revenue'  => number_format($total_revenue, 2, '.', ''),
        'total_discount' => number_format($total_discount, 2, '.', ''),
        'net_profit'     => number_format($net_profit, 2, '.', '')
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status'  => 'error',
        'message' => $e->getMessage()
    ]);
}
?>