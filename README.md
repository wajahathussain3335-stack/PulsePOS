# 🚀 PulsePOS - Premium Web-Based Point of Sale System

[![Fiverr Portfolio](https://img.shields.io/badge/Portfolio-Fiverr-green?style=for-the-badge)](https://www.fiverr.com/)
[![Tech Stack](https://img.shields.io/badge/Stack-PHP%20%7C%20PDO%20%7C%20MySQL%20%7C%20Tailwind-blue?style=for-the-badge)](https://github.com/)

PulsePOS is an ultra-premium, full-stack, multi-tenant Point of Sale system built with a clean, modern minimalist design. It features comprehensive user isolation, real-time advanced business analytics, secure PDO transactions, and a highly responsive dashboard optimized for seamless business workflows.

---

## ✨ Key Features

- **Multi-Tenant User Isolation:** Deep session-based filtering (`user_id`) ensuring every registered merchant handles their own products, sales ledger, and telemetry independently.
- **Live Net Profit Analytics Engine:** Computes complex dynamic business profit margins in real-time, accurately offsetting custom item margins against global invoice discounts.
- **Premium UI/UX Theme:** Designed with a sleek, minimalist glassmorphic interface favoring a premium white canvas with deep blue functional accents.
- **Secured Backend Infrastructure:** Powered entirely by robust PHP Data Objects (PDO) with parameter binding to eliminate SQL injection vectors.
- **Data Export Utilities:** Integrated seamless client-side CSV processing to let merchants export localized sales datasets instantaneously.

---

## 🛠️ Tech Stack

- **Frontend:** HTML5, JavaScript (ES6+ Asynchronous Core), Tailwind CSS (Utility-first styling grid)
- **Backend Architecture:** PHP (Object-Oriented, PDO Structure)
- **Database Layer:** MySQL (Relational management with foreign constraint mapping)

- 


---
🧑‍💻 Author
Email:** [wajahathussain3335@gmail.com](mailto:wajahathussain3335@gmail.com)
- **🌐 Portfolio Website:** [https://codecraft.infy.click/](https://codecraft.infy.click/)
- **🌐 Live Website:** [https://pulsepos.infy.click/](https://pulsepos.infy.click/)
## 🗄️ Database Setup & Schema

Execute the following SQL commands in your local database manager (e.g., **phpMyAdmin**) to instantly map out the core relational structure for the ecosystem.

```sql
CREATE DATABASE IF NOT EXISTS `pulse_pos` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `pulse_pos`;

-- 1. Products Catalog Table (Equipped with strict User Isolation Mapping)
CREATE TABLE IF NOT EXISTS `products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `barcode` VARCHAR(50) NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `cost_price` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
  `retail_price` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
  `stock_quantity` INT NOT NULL DEFAULT '0',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_user_products` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Master Sales Invoices Table
CREATE TABLE IF NOT EXISTS `sales` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `payable_amount` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
  `discount` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_user_sales` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Granular Sale Items Ledger (Includes historic cost tracing for dynamic profit computation)
CREATE TABLE IF NOT EXISTS `sale_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `sale_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `unit_price` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
  `cost_price` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
  `quantity` INT NOT NULL DEFAULT '1',
  FOREIGN KEY (`sale_id`) REFERENCES `sales`(`id`) ON DELETE CASCADE,
  INDEX `idx_sale_items_lookup` (`sale_id`, `product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
⚙️ Installation & Architecture Configuration
1. Environment Setup
Clone this codebase structure and host it inside your running server environment root (e.g., /xampp/htdocs/pulsepos).

2. Connection Initialization
Verify that your global runtime config parameters match your local server environment constraints. Modify config/config.php to include your target PDO instance definition parameters:

PHP
<?php
// config/config.php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'pulse_pos');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}
?>
3. File System Map
For optimal routing operations, maintain this architectural layout:

Plaintext
pulsepos/
├── config/
│   └── config.php          # Secure PDO Connection
├── api/
│   ├── fetch_products.php  # Filtered User Products Fetcher
│   └── get_analytics.php   # Live Metrics Computation
├── assets/
│   └── js/
│       └── dashboard_engine.js  # Async Interface Layer
├── index.php               # POS Cashier Terminal Screen
└── dashboard.php           # Analytics Overview Engine


