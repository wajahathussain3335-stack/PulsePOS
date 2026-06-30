<?php
// api/auth.php
header('Content-Type: application/json');
require_once '../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$action = $_GET['action'] ?? '';

try {
    // 1. REGISTRATION LOGIC
    if ($action === 'register' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if (empty($username) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'All fields are required!']);
            exit();
        }

        // Check karein username pehle se toh nahi hai
        $checkStmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $checkStmt->execute([$username]);
        if ($checkStmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Username already taken!']);
            exit();
        }

        // Password hash karein aur secure save karein (Default role will be admin for their own shop)
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'admin')");
        $stmt->execute([$username, $hashedPassword]);

        echo json_encode(['success' => true, 'message' => 'Account created successfully! Please login.']);
    }
    
    // 2. LOGIN LOGIC
    elseif ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');

        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            echo json_encode([
                'success' => true, 
                'redirect' => 'dashboard.php'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid username or password!']);
        }
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>