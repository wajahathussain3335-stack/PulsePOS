<?php
// index.php ke bilkul TOP par (Line 1) yeh code lagayein

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security Check: Agar koi bina login kiye direct index.php khole, toh usay login page par phenk do
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!-- Aapka baqi ka index.php ka HTML code yahan se shuru hoga -->
<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PulsePOS - Premium POS System</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome for Premium Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap');
    body { font-family: 'Plus Jakarta Sans', sans-serif; }

    /* --- PREMIUM THERMAL PRINT LOGIC --- */
    #receipt-print-area { display: none; } /* Normal screen par hidden rahega */

    @media print {
        /* Screen ke saare normal elements ko chupa do */
        body *, html * { visibility: hidden; height: auto; overflow: visible; }
        
        /* Sirf receipt area ko active aur visible karo */
        #receipt-print-area, #receipt-print-area * { visibility: visible; }
        
        #receipt-print-area {
            display: block !important;
            position: absolute;
            left: 0;
            top: 0;
            width: 80mm; /* Standard Thermal Paper Width */
            font-family: 'Courier New', Courier, monospace; /* Authentic receipt vibe */
            color: #000 !important;
            font-size: 12px;
            line-height: 1.4;
            padding: 5mm;
        }
        
        /* Margins aur headers/footers clear karne ke liye */
        @page { size: auto; margin: 0mm; }
    }
</style>
</head>
<body class="h-full overflow-hidden text-slate-800">

    <!-- Main Container -->
    <div class="flex flex-col h-screen">
        
        <!-- 1. Top Navbar -->
        <header class="bg-white border-b border-slate-100 px-6 py-4 flex items-center justify-between shadow-sm shrink-0">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center shadow-lg shadow-blue-200">
                    <i class="fa-solid fa-bolt text-white text-lg"></i>
                </div>
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-slate-900">Pulse<span class="text-blue-600">POS</span></h1>
                    <p class="text-xs text-slate-400 font-medium">v1.0 (Portfolio Edition)</p>
                </div>
            </div>
            <!-- PulsePOS Premium Navbar Dashboard Link -->
<?php if (isset($_SESSION['user_id'])): ?>
    <a href="dashboard.php" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 active:bg-blue-800 rounded-lg transition-all duration-200 shadow-sm shadow-blue-100 backdrop-blur-md">
        <!-- Dashboard Elegant Icon -->
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V19.5a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25v-3.75ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V19.5A2.25 2.25 0 0 1 18 21.75h-2.25a2.25 2.25 0 0 1-2.25-2.25v-3.75?" />
        </svg>
        <span>Dashboard</span>
    </a>
<?php endif; ?>
            <!-- Connection & Sync Indicators -->
            <div class="flex items-center space-x-4">
                <!-- Status Badge -->
                <div id="status-badge" class="flex items-center space-x-2 px-3 py-1.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 transition-all duration-300">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span id="status-text">Online Mode</span>
                </div>

                <!-- Syncing Loader (Hidden by default) -->
                <div id="sync-indicator" class="hidden flex items-center space-x-1.5 text-xs font-medium text-blue-600">
                    <i class="fa-solid fa-arrow-rotate-right animate-spin"></i>
                    <span>Syncing...</span>
                </div>

                <div class="h-6 w-px bg-slate-200"></div>

                <!-- Cashier Profile -->
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
            </div>
        </header>

        <!-- 2. Main Workspace Layout -->
        <main class="flex flex-1 overflow-hidden">
            
            <!-- LEFT SIDE: Product Browser & Search -->
            <section class="w-2/3 p-6 flex flex-col space-y-6 overflow-y-auto">
                
                <!-- Search & Action Bar -->
                <div class="flex space-x-4">
                    <!-- Product Search -->
                    <div class="relative flex-1">
                        <i class="fa-solid fa-magnifying-glass absolute left-4 top-3.5 text-slate-400"></i>
                        <input type="text" id="product-search" placeholder="Search products by name or barcode..." 
                               class="w-full bg-white border border-slate-200 rounded-xl pl-11 pr-4 py-3 text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all placeholder-slate-400">
                    </div>
                    <!-- Quick Customer Select -->
                    <div class="w-64 relative">
                        <i class="fa-solid fa-user absolute left-4 top-3.5 text-slate-400"></i>
                        <select id="customer-select" class="w-full bg-white border border-slate-200 rounded-xl pl-11 pr-4 py-3 text-sm appearance-none focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all text-slate-600">
                            <option value="1">Walk-in Customer</option>
                            <option value="2">Add New Customer (+)</option>
                        </select>
                        <i class="fa-solid fa-chevron-down absolute right-4 top-4 text-slate-400 text-xs pointer-events-none"></i>
                    </div>
                </div>

                <!-- Category Tabs (Minimalist) -->
                <div class="flex space-x-2 border-b border-slate-200 pb-px shrink-0">
                    <button class="px-4 py-2 text-sm font-medium border-b-2 border-blue-600 text-blue-600 transition-all">All Items</button>
                    <button class="px-4 py-2 text-sm font-medium text-slate-400 hover:text-slate-600 transition-all">Beverages</button>
                    <button class="px-4 py-2 text-sm font-medium text-slate-400 hover:text-slate-600 transition-all">Snacks</button>
                    <button class="px-4 py-2 text-sm font-medium text-slate-400 hover:text-slate-600 transition-all">Groceries</button>
                </div>

                <!-- Products Grid -->
                <div id="products-grid" class="grid grid-cols-3 gap-4">
                    <!-- Sample Product Card (Yeh JS se dynamically populate hoga) -->
                    <div class="bg-white border border-slate-100 p-4 rounded-2xl shadow-sm hover:shadow-md hover:border-blue-100 transition-all cursor-pointer flex flex-col justify-between group">
                        <div>
                            <span class="text-[10px] uppercase tracking-wider font-semibold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-md">In Stock</span>
                            <h3 class="font-semibold text-slate-800 mt-2 group-hover:text-blue-600 transition-colors">Premium Mineral Water</h3>
                            <p class="text-xs text-slate-400 mt-0.5">Barcode: 89012345</p>
                        </div>
                        <div class="flex items-center justify-between mt-5 pt-3 border-t border-slate-50">
                            <span class="text-lg font-bold text-slate-900">Rs. 60.00</span>
                            <div class="w-8 h-8 bg-slate-50 group-hover:bg-blue-600 group-hover:text-white rounded-lg flex items-center justify-center text-slate-400 transition-all">
                                <i class="fa-solid fa-plus text-xs"></i>
                            </div>
                        </div>
                    </div>
                    <!-- End Sample -->
                </div>
            </section>

            <!-- RIGHT SIDE: Cart & Checkout Summary -->
            <section class="w-1/3 bg-white border-l border-slate-100 flex flex-col shadow-2xl shadow-slate-100">
                <!-- Cart Header -->
                <div class="p-6 border-b border-slate-100 flex items-center justify-between shrink-0">
                    <div class="flex items-center space-x-2">
                        <i class="fa-solid fa-cart-shopping text-blue-600"></i>
                        <h2 class="font-bold text-slate-900 text-base">Current Cart</h2>
                    </div>
                    <button id="clear-cart" class="text-xs font-medium text-rose-500 hover:text-rose-600 bg-rose-50 px-2.5 py-1 rounded-md transition-all">
                        Clear All
                    </button>
                </div>

                <!-- Cart Items List (Scrollable) -->
                <div id="cart-items" class="flex-1 p-6 overflow-y-auto space-y-4">
                    <!-- Sample Cart Item -->
                    <div class="flex items-center justify-between bg-slate-50 p-3 rounded-xl border border-slate-100">
                        <div class="flex-1 pr-3">
                            <h4 class="text-sm font-semibold text-slate-800 line-clamp-1">Premium Mineral Water</h4>
                            <p class="text-xs text-slate-400 mt-0.5">Rs. 60.00 × 2</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="flex items-center bg-white border border-slate-200 rounded-lg overflow-hidden">
                                <button class="px-2 py-1 text-slate-500 hover:bg-slate-100 text-xs">-</button>
                                <span class="px-3 text-xs font-bold text-slate-800">2</span>
                                <button class="px-2 py-1 text-slate-500 hover:bg-slate-100 text-xs">+</button>
                            </div>
                            <button class="text-slate-400 hover:text-rose-500 transition-colors p-1"><i class="fa-solid fa-trash-can text-xs"></i></button>
                        </div>
                    </div>
                    <!-- End Sample -->
                </div>

                <!-- Checkout Calculation Summary -->
                <div class="p-6 border-t border-slate-100 bg-slate-50/50 space-y-4 shrink-0">
                    <div class="space-y-2">
                        <div class="flex justify-between text-sm text-slate-500">
                            <span>Subtotal</span>
                            <span id="summary-subtotal" class="font-medium text-slate-800">Rs. 120.00</span>
                        </div>
                        <div class="flex justify-between text-sm text-slate-500">
                            <span>Discount</span>
                            <input type="number" id="discount-input" value="0" class="w-16 bg-white border border-slate-200 text-right px-1.5 py-0.5 rounded text-xs focus:outline-none focus:border-blue-500">
                        </div>
                    </div>
                    
                    <div class="h-px bg-slate-200 my-2"></div>
                    
                    <div class="flex justify-between items-baseline">
                        <span class="text-sm font-bold text-slate-900">Total Payable</span>
                        <span id="summary-total" class="text-2xl font-black text-blue-600">Rs. 120.00</span>
                    </div>

                    <!-- Payment Methods selection -->
                    <div class="grid grid-cols-3 gap-2 pt-2">
                        <button class="py-2.5 rounded-xl border-2 border-blue-600 bg-blue-50 text-blue-600 font-semibold text-xs flex flex-col items-center justify-center space-y-1 shadow-sm">
                            <i class="fa-solid fa-money-bill-wave text-sm"></i>
                            <span>Cash</span>
                        </button>
                        <button class="py-2.5 rounded-xl border border-slate-200 bg-white text-slate-600 font-medium text-xs flex flex-col items-center justify-center space-y-1 hover:border-slate-300">
                            <i class="fa-solid fa-credit-card text-sm"></i>
                            <span>Card</span>
                        </button>
                        <button class="py-2.5 rounded-xl border border-slate-200 bg-white text-slate-600 font-medium text-xs flex flex-col items-center justify-center space-y-1 hover:border-slate-300">
                            <i class="fa-solid fa-book-open text-sm"></i>
                            <span>Khata</span>
                        </button>
                    </div>

                    <!-- Main Pay Action -->
                    <button id="btn-checkout" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 rounded-xl text-sm transition-all shadow-lg shadow-blue-200 flex items-center justify-center space-x-2 mt-2">
                        <i class="fa-solid fa-print"></i>
                        <span>Place Order & Print Invoice</span>
                    </button>
                </div>
            </section>
        </main>
    </div>
<!-- Add Customer Modal -->
<div id="customer-modal" class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-sm flex items-center justify-center z-50 transition-all duration-300">
    <!-- Modal Box -->
    <div class="bg-white rounded-2xl shadow-2xl border border-slate-100 w-full max-w-md p-6 overflow-hidden transform scale-95 transition-transform duration-300">
        <!-- Modal Header -->
        <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-4">
            <div class="flex items-center space-x-2.5">
                <div class="w-8 h-8 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center">
                    <i class="fa-solid fa-user-plus text-sm"></i>
                </div>
                <h3 class="font-bold text-slate-900 text-base">Register New Customer</h3>
            </div>
            <button id="close-modal-btn" class="text-slate-400 hover:text-slate-600 transition-colors p-1">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <!-- Modal Form -->
        <form id="customer-form" class="space-y-4">
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Customer Name *</label>
                <input type="text" id="modal-cust-name" required placeholder="e.g. Ali Ahmed" 
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Phone Number</label>
                <input type="text" id="modal-cust-phone" placeholder="e.g. 03001234567" 
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all">
            </div>

            <!-- Modal Actions -->
            <div class="flex space-x-3 pt-4 border-t border-slate-100 mt-6">
                <button type="button" id="cancel-modal-btn" class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-600 font-semibold py-3 rounded-xl text-sm transition-all">
                    Cancel
                </button>
                <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-xl text-sm transition-all shadow-lg shadow-blue-100 flex items-center justify-center space-x-2">
                    <i class="fa-solid fa-floppy-disk text-xs"></i>
                    <span>Save Customer</span>
                </button>
            </div>
        </form>
    </div>
</div>
<!-- Hidden Print Area for Thermal Receipt -->
<div id="receipt-print-area">
    <div style="text-align: center; margin-bottom: 10px;">
        <h2 style="font-size: 16px; font-weight: bold; margin: 0;">PulsePOS</h2>
       <h2 style="font-size: 18px; font-weight: bold; text-transform: uppercase; margin: 0;">
            <?php echo htmlspecialchars($_SESSION['shop_name'] ?? 'PulsePOS Station'); ?>
        </h2>
        <p style="margin: 10px 0 5px 0;">--------------------------------</p>
        <strong style="font-size: 13px;">SALE RECEIPT</strong>
        <p style="margin: 5px 0 0 0; text-align: left; font-size: 11px;" id="print-invoice-no">Invoice: </p>
        <p style="margin: 2px 0 0 0; text-align: left; font-size: 11px;" id="print-date">Date: </p>
        <p style="margin: 2px 0 10px 0; text-align: left; font-size: 11px;" id="print-customer">Customer: </p>
        <p style="margin: 0;">--------------------------------</p>
    </div>

    <!-- Items Table -->
    <table style="width: 100%; font-size: 11px; border-collapse: collapse;">
        <thead>
            <tr style="border-b: 1px dashed #000;">
                <th style="text-align: left; width: 45%;">Item</th>
                <th style="text-align: center; width: 15%;">Qty</th>
                <th style="text-align: right; width: 20%;">Price</th>
                <th style="text-align: right; width: 20%;">Total</th>
            </tr>
        </thead>
        <tbody id="print-items-body">
            <!-- JavaScript dynamically inserts rows here -->
        </tbody>
    </table>

    <!-- Totals -->
    <div style="margin-top: 10px; font-size: 11px;">
        <p style="margin: 0;">--------------------------------</p>
        <div style="display: flex; justify-content: space-between; margin: 3px 0;">
            <span>Subtotal:</span> <span id="print-subtotal">Rs. 0.00</span>
        </div>
        <div style="display: flex; justify-content: space-between; margin: 3px 0;">
            <span>Discount:</span> <span id="print-discount">Rs. 0.00</span>
        </div>
        <div style="display: flex; justify-content: space-between; margin: 5px 0; font-weight: bold; font-size: 13px;">
            <span>Net Payable:</span> <span id="print-total">Rs. 0.00</span>
        </div>
        <p style="margin: 0; text-align: center;">--------------------------------</p>
    </div>

    <!-- Footer -->
    <div style="text-align: center; margin-top: 15px; font-size: 11px;">
        <p style="margin: 0; font-weight: bold;">Thank You For Your Business!</p>
        <p style="margin: 3px 0 0 0; font-size: 9px;">Powered by PulsePOS & CodeCraft</p>
    </div>
</div>
    <!-- Scripts Link -->
    <script src="assets/js/db.js"></script>
    <script src="assets/js/app.js"></script>
</body>
</html>