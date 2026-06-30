<?php
// api/add_customer.php
header('Content-Type: application/json');
require_once '../config/config.php';

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (empty($data['name'])) {
    echo json_encode(['success' => false, 'message' => 'Customer name is required.']);
    exit;
}

try {
    $name = trim($data['name']);
    $phone = !empty($data['phone']) ? trim($data['phone']) : null;

    // Check karein ke is phone number ka customer pehle se exist to nahi karta
    if ($phone !== null) {
        $check = $pdo->prepare("SELECT id FROM customers WHERE phone = ?");
        $check->execute([$phone]);
        if ($check->fetch()) {
            echo json_encode(['success' => false, 'message' => 'A customer with this phone number already exists.']);
            exit;
        }
    }

    // Insert new customer record
    $query = "INSERT INTO customers (name, phone, balance) VALUES (?, ?, 0.00)";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$name, $phone]);

    $new_id = $pdo->lastInsertId();

    echo json_encode([
        'success' => true,
        'id' => $new_id,
        'name' => $name
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>