<?php
// dashboard.php ke bilkul TOP par (Line 1) yeh code lagayein

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Security Check: Agar user logged in nahi hai toh login page par phenko
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// 2. Role Authorization: Agar logged-in user 'cashier' hai, toh usay dashboard block karo
// Cashier sirf POS screen (index.php) chalane ka majaz hai!
if ($_SESSION['role'] !== 'admin') {
    echo "<script>
        alert('Access Denied! Cashiers are not allowed to view the Analytics Dashboard.');
        window.location.href = 'index.php';
    </script>";
    exit();
}
?>
<!-- Aapka baqi ka dashboard.php ka UI code yahan se shuru hoga -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PulsePOS - Owner Dashboard</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-slate-50/50 min-h-screen">

    <!-- Top Premium Navbar -->
    <nav class="bg-white border-b border-slate-100 px-8 py-4 flex justify-between items-center sticky top-0 z-10">
        <div class="flex items-center space-x-3">
            <div class="w-9 h-9 bg-blue-600 rounded-xl flex items-center justify-center shadow-lg shadow-blue-100">
                <i class="fa-solid fa-chart-line text-white text-sm"></i>
            </div>
            <div>
                <span class="font-bold text-slate-900 text-lg tracking-tight">PulsePOS</span>
                <span class="text-xs text-slate-400 block -mt-1">Analytics Intelligence</span>
            </div>
        </div>
        <a href="index.php" class="bg-slate-100 hover:bg-slate-200 text-slate-600 px-4 py-2 rounded-xl text-sm font-semibold transition-all flex items-center space-x-2">
            <i class="fa-solid fa-cash-register text-xs"></i>
            <span>Back to POS</span>
        </a>
        <!-- Navbar ya Header ke andar jahan profile/naam show hota hai -->
<div class="flex items-center space-x-3">
    <div class="text-right">
        <!-- Logged-in User ka naam dynamic display hoga -->
        <p class="text-sm font-bold text-slate-800 capitalize">
            <?php echo htmlspecialchars($_SESSION['username']); ?>
        </p>
        <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">
            <?php echo htmlspecialchars($_SESSION['role']); ?>
        </p>
    </div>
    
    <!-- Premium Minimalist Logout Button -->
    <a href="logout.php" class="w-9 h-9 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-xl flex items-center justify-center transition-colors shadow-sm" title="Sign Out">
        <i class="fa-solid fa-power-off text-sm"></i>
    </a>
</div>
    </nav>

    <!-- Main Dashboard Body -->
    <main class="max-w-7xl mx-auto px-8 py-8 space-y-8">
        
        <!-- Welcome Banner -->
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Welcome Back, Owner!</h1>
            <p class="text-sm text-slate-400 mt-1">Here is how your business is performing today.</p>
        </div>

        <!-- Metric Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Card 1: Revenue -->
            <div class="bg-white border border-slate-100 rounded-2xl p-6 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Today's Revenue</span>
                    <h3 id="card-sales" class="text-2xl font-bold text-slate-900 mt-1">Rs. 0.00</h3>
                </div>
                <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center text-lg">
                    <i class="fa-solid fa-wallet"></i>
                </div>
            </div>
<!-- Net Profit Card (Premium Glassmorphic Minimalist Design) -->
<div class="bg-white/80 backdrop-blur-md border border-slate-100 p-6 rounded-2xl shadow-sm flex items-center justify-between hover:shadow-md transition-all duration-300">
    <div>
        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Net Profit</p>
        <h3 id="card-profit" class="text-2xl font-black text-slate-900 mt-1">Rs. 0.00</h3>
    </div>
    <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center shadow-sm border border-emerald-100">
        <i class="fa-solid fa-money-bill-trend-up text-lg"></i>
    </div>
</div>
            <!-- Card 2: Orders -->
            <div class="bg-white border border-slate-100 rounded-2xl p-6 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Orders Processed</span>
                    <h3 id="card-orders" class="text-2xl font-bold text-slate-900 mt-1">0</h3>
                </div>
                <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center text-lg">
                    <i class="fa-solid fa-basket-shopping"></i>
                </div>
            </div>

            <!-- Card 3: Items Sold -->
            <div class="bg-white border border-slate-100 rounded-2xl p-6 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Items Sold</span>
                    <h3 id="card-items" class="text-2xl font-bold text-slate-900 mt-1">0</h3>
                </div>
                <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center text-lg">
                    <i class="fa-solid fa-box-open"></i>
                </div>
            </div>
        </div>

        <!-- Chart Section with Date Filter Row -->
        <div class="bg-white border border-slate-100 rounded-2xl p-6 shadow-sm">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6 pb-5 border-b border-slate-50">
                <div>
                    <h3 class="font-bold text-slate-800 text-base">Sales Revenue Trend</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Filter analytics and export reports into business spreadsheets.</p>
                </div>
                
                <div class="flex flex-wrap items-center gap-3">
                    <div class="flex items-center space-x-2 bg-slate-50 px-3 py-1.5 rounded-xl border border-slate-200">
                        <input type="date" id="filter-start" onchange="filterAnalytics()" class="bg-transparent text-xs text-slate-600 font-medium focus:outline-none">
                        <span class="text-slate-400 text-xs">to</span>
                        <input type="date" id="filter-end" onchange="filterAnalytics()" class="bg-transparent text-xs text-slate-600 font-medium focus:outline-none">
                    </div>
                    
                    <button onclick="exportSalesToCSV()" class="bg-slate-900 hover:bg-slate-800 text-white px-4 py-2.5 rounded-xl text-xs font-semibold transition-all flex items-center space-x-2 shadow-sm">
                        <i class="fa-solid fa-file-csv text-sm text-emerald-400"></i>
                        <span>Export Report</span>
                    </button>
                </div>
            </div>

            <div class="relative w-full h-[350px]">
                <canvas id="salesTrendsChart"></canvas>
            </div>
        </div>

        <!-- Inventory Management Section -->
        <div class="bg-white border border-slate-100 rounded-2xl p-6 shadow-sm">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 class="font-bold text-slate-800 text-base">Live Stock & Product Inventory</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Manage your items, tracking codes, pricing and current stock volumes.</p>
                </div>
                <button onclick="openProductModal('add')" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-xl text-xs font-semibold transition-all flex items-center space-x-2 shadow-lg shadow-blue-100">
                    <i class="fa-solid fa-plus text-[10px]"></i>
                    <span>Add New Product</span>
                </button>
            </div>

            <div class="overflow-x-auto rounded-xl border border-slate-100">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100 text-slate-500 text-xs font-semibold uppercase tracking-wider">
                            <th class="p-4">Product Name</th>
                            <th class="p-4">Barcode</th>
                            <th class="p-4">Price</th>
                            <th class="p-4">Stock Status</th>
                            <th class="p-4 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="inventory-table-body" class="text-sm text-slate-600 divide-y divide-slate-50">
                        <!-- Dynamic Rows Go Here -->
                    </tbody>
                </table>
            </div>
        </div>

    </main>

    <!-- Unified CRUD Modal Backdrop -->
    <div id="productModal" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm hidden items-center justify-center z-50 transition-all">
        <div class="bg-white rounded-2xl w-full max-w-md p-6 shadow-xl border border-slate-100 transform scale-95 transition-transform duration-200">
            <div class="flex justify-between items-center mb-5">
                <h3 id="modalTitle" class="font-bold text-slate-800 text-lg">Add New Product</h3>
                <button onclick="closeProductModal()" class="w-8 h-8 rounded-lg hover:bg-slate-50 flex items-center justify-center text-slate-400 hover:text-slate-600 transition-colors">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            
            <form id="productForm" onsubmit="saveProduct(event)">
    <input type="hidden" id="prod-id" name="id">
    <div class="space-y-4">
        <!-- Row 1: Product Name -->
        <div>
            <label class="text-xs font-semibold text-slate-500 uppercase tracking-wider block mb-1.5">Product Name</label>
            <input type="text" id="prod-name" name="name" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all">
        </div>

        <!-- Row 2: Barcode -->
        <div>
            <label class="text-xs font-semibold text-slate-500 uppercase tracking-wider block mb-1.5">Barcode / Code</label>
            <input type="text" id="prod-barcode" name="barcode" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all">
        </div>

        <!-- Row 3: Prices (Symmetric Responsive Grid) -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Cost Price Input -->
            <div>
                <label class="text-xs font-semibold text-slate-500 uppercase tracking-wider block mb-1.5">Cost Price (Rs.)</label>
                <div class="relative">
                    <span class="absolute left-3 top-2.5 text-slate-400 text-xs font-semibold">Rs.</span>
                    <input type="number" step="0.01" name="cost_price" id="prod-cost-price" required 
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-9 pr-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all" 
                           placeholder="0.00">
                </div>
            </div>

            <!-- Retail Price Input (ID matched with JS) -->
            <div>
                <label class="text-xs font-semibold text-slate-500 uppercase tracking-wider block mb-1.5">Retail Price (Rs.)</label>
                <div class="relative">
                    <span class="absolute left-3 top-2.5 text-slate-400 text-xs font-semibold">Rs.</span>
                    <input type="number" step="0.01" id="prod-retail-price" name="retail_price" required 
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-9 pr-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all" 
                           placeholder="0.00">
                </div>
            </div>
        </div>

        <!-- Row 4: Stock Quantity -->
        <div>
            <label class="text-xs font-semibold text-slate-500 uppercase tracking-wider block mb-1.5">Stock Quantity</label>
            <input type="number" id="prod-stock" name="stock_quantity" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all">
        </div>
    </div>
    
    <!-- Form Buttons -->
    <div class="flex space-x-3 mt-6">
        <button type="button" onclick="closeProductModal()" class="w-1/2 bg-slate-100 hover:bg-slate-200 text-slate-600 font-semibold py-3 rounded-xl text-sm transition-colors">Cancel</button>
        <button type="submit" class="w-1/2 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-xl text-sm shadow-lg shadow-blue-100 transition-colors">Save Product</button>
    </div>
</form>
        </div>
    </div>

    <!-- Scripts Link -->
    <script src="assets/js/dashboard_engine.js"></script>
    <script src="assets/js/products_manager.js"></script>
    
</body>
</html>