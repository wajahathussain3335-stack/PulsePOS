<?php
// api/manage_products.php
header('Content-Type: application/json');
require_once '../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access!']);
    exit();
}

$user_id = $_SESSION['user_id']; 
$action = $_GET['action'] ?? '';

try {
    // 1. READ: Products uthatey waqt cost_price bhi laao
    if ($action === 'fetch') {
        $stmt = $pdo->prepare("SELECT id, name, barcode, cost_price, retail_price, stock_quantity FROM products WHERE user_id = ? ORDER BY id DESC");
        $stmt->execute([$user_id]);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $products]);
    }
    
    // 2. CREATE: Naya product cost_price ke sath add karein
    elseif ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = $_POST['name'];
        $barcode = $_POST['barcode'];
        $cost_price = $_POST['cost_price'];
        $retail_price = $_POST['retail_price'];
        $stock_quantity = $_POST['stock_quantity'];

        $stmt = $pdo->prepare("INSERT INTO products (user_id, name, barcode, cost_price, retail_price, stock_quantity) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $name, $barcode, $cost_price, $retail_price, $stock_quantity]);
        
        echo json_encode(['success' => true, 'message' => 'Product added successfully!']);
    }
    
    // 3. UPDATE: Product edit karte waqt cost_price bhi badlein
    elseif ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = $_POST['id'];
        $name = $_POST['name'];
        $barcode = $_POST['barcode'];
        $cost_price = $_POST['cost_price'];
        $retail_price = $_POST['retail_price'];
        $stock_quantity = $_POST['stock_quantity'];

        $stmt = $pdo->prepare("UPDATE products SET name = ?, barcode = ?, cost_price = ?, retail_price = ?, stock_quantity = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$name, $barcode, $cost_price, $retail_price, $stock_quantity, $id, $user_id]);
        
        echo json_encode(['success' => true, 'message' => 'Product updated successfully!']);
    }
    
    // 4. DELETE: Product delete karein
    elseif ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = $_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $user_id]);
        echo json_encode(['success' => true, 'message' => 'Product deleted successfully!']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>