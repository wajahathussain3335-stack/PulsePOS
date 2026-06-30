<?php
// api/fetch_products.php

// Browser ko batayein ke response JSON format mein hai
header('Content-Type: application/json');

// 🔥 NEW: Session start karein taake logged-in user ki ID access ki ja sakay
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database connection include karein
require_once '../config/config.php';

try {
    // Current logged-in user ki ID nikalen
    $current_user_id = $_SESSION['user_id'] ?? 0;

    // Security Check: Agar user logged in nahi hai, toh aage query chalane ki zaroorat nahi
    if ($current_user_id === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Unauthorized access. Please login first.'
        ]);
        exit;
    }

    // 🔥 FIXED: Query mein WHERE clause lagaya aur PDO Prepared Statement use kiya
    $query = "SELECT id, barcode, name, cost_price, retail_price, stock_quantity 
              FROM products 
              WHERE user_id = :user_id 
              ORDER BY name ASC";
              
    $stmt = $pdo->prepare($query);
    $stmt->execute(['user_id' => $current_user_id]);
    $products = $stmt->fetchAll();

    // Kamyabi ka response aur data bhejein
    echo json_encode([
        'success' => true,
        'count' => count($products),
        'data' => $products
    ]);

} catch (Exception $e) {
    // Agar koi error aaye to server crash nahi hoga, balkey clean JSON error return karega
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch products from database: ' . $e->getMessage()
    ]);
}
?>