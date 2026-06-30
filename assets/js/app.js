// assets/js/app.js

// Global State
let cart = [];
let localProducts = [];
let selectedPaymentMethod = 'cash';

// DOM Elements
const statusBadge = document.getElementById('status-badge');
const statusText = document.getElementById('status-text');
const syncIndicator = document.getElementById('sync-indicator');
const productsGrid = document.getElementById('products-grid');
const cartItemsContainer = document.getElementById('cart-items');
const subtotalEl = document.getElementById('summary-subtotal');
const totalEl = document.getElementById('summary-total');
const discountInput = document.getElementById('discount-input');
const checkoutBtn = document.getElementById('btn-checkout');
const clearCartBtn = document.getElementById('clear-cart');

// 1. Service Worker Registration
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('./sw.js')
            .then(reg => console.log('Service Worker Registered Successfully.'))
            .catch(err => console.error('Service Worker Registration Failed.', err));
    });
}

// 2. Online/Offline Status Handling
function updateOnlineStatus() {
    if (navigator.onLine) {
        // Online State Style (Premium Emerald Green)
        statusBadge.className = "flex items-center space-x-2 px-3 py-1.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 transition-all duration-300";
        statusText.innerText = "Online Mode";
        
        // Trigger Background Sync
        syncOfflineSales();
    } else {
        // Offline State Style (Premium Amber/Orange)
        statusBadge.className = "flex items-center space-x-2 px-3 py-1.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200 transition-all duration-300";
        statusText.innerText = "Offline Mode";
    }
}

window.addEventListener('online', updateOnlineStatus);
window.addEventListener('offline', updateOnlineStatus);

// 3. Load Products (Online vs Offline)
// 3. Load Products (Online vs Offline) - Integrated with Live MySQL Database
async function loadProducts() {
    setTimeout(async () => {
        if (navigator.onLine) {
            try {
                // Live PHP API se products data fetch karein
                const response = await fetch('api/fetch_products.php');
                const result = await response.json();
                
                if (result.success && result.data.length > 0) {
                    // IndexedDB mein data cache karein future offline use ke liye
                    await saveToLocalStore('products', result.data);
                    localProducts = result.data;
                } else {
                    // Agar database khali hai to local check karein
                    localProducts = await getAllFromLocalStore('products');
                }
            } catch (err) {
                console.error("Network success but API failed, switching to local database", err);
                localProducts = await getAllFromLocalStore('products');
            }
        } else {
            // Pure offline mode: Direct IndexedDB se data uthao
            localProducts = await getAllFromLocalStore('products');
        }
        renderProducts(localProducts);
    }, 500);
}

function renderProducts(productsList) {
    productsGrid.innerHTML = '';
    if (productsList.length === 0) {
        productsGrid.innerHTML = `<p class="text-slate-400 text-sm col-span-3 text-center py-8">No products found.</p>`;
        return;
    }

    productsList.forEach(product => {
        const card = document.createElement('div');
        
        // 1. CHANGES HERE: Shuru me 'product-card' class add ki hai
        card.className = "product-card bg-white border border-slate-100 p-4 rounded-2xl shadow-sm hover:shadow-md hover:border-blue-100 transition-all cursor-pointer flex flex-col justify-between group";
        
        // 2. CHANGES HERE: Search filter ke liye name aur barcode attributes set kiye hain
        card.setAttribute('data-id', product.id);
        card.setAttribute('data-name', product.name);
        card.setAttribute('data-code', product.barcode); 
        
        card.onclick = () => addToCart(product.id);
        
        // Tumhara baqi ka premium design bilkul same to same wahi hai
        card.innerHTML = `
            <div>
                <span class="text-[10px] uppercase tracking-wider font-semibold ${product.stock_quantity > 5 ? 'text-blue-600 bg-blue-50' : 'text-rose-600 bg-rose-50'} px-2 py-0.5 rounded-md">
                    ${product.stock_quantity > 5 ? 'In Stock' : 'Low Stock'}
                </span>
                <h3 class="font-semibold text-slate-800 mt-2 group-hover:text-blue-600 transition-colors">${product.name}</h3>
                <p class="text-xs text-slate-400 mt-0.5">Barcode: ${product.barcode}</p>
            </div>
            <div class="flex items-center justify-between mt-5 pt-3 border-t border-slate-50">
                <span class="text-lg font-bold text-slate-900">Rs. ${parseFloat(product.retail_price).toFixed(2)}</span>
                <div class="w-8 h-8 bg-slate-50 group-hover:bg-blue-600 group-hover:text-white rounded-lg flex items-center justify-center text-slate-400 transition-all">
                    <i class="fa-solid fa-plus text-xs"></i>
                </div>
            </div>
        `;
        productsGrid.appendChild(card);
    });
}

// 5. Cart Logic
function addToCart(productId) {
    const product = localProducts.find(p => p.id === productId);
    if (!product) return;

    const existingItem = cart.find(item => item.id === productId);
    if (existingItem) {
        existingItem.quantity += 1;
    } else {
        cart.push({ ...product, quantity: 1 });
    }
    renderCart();
}

function updateQuantity(productId, amount) {
    const item = cart.find(item => item.id === productId);
    if (!item) return;

    item.quantity += amount;
    if (item.quantity <= 0) {
        cart = cart.filter(item => item.id !== productId);
    }
    renderCart();
}

function removeFromCart(productId) {
    cart = cart.filter(item => item.id !== productId);
    renderCart();
}

function renderCart() {
    cartItemsContainer.innerHTML = '';
    let subtotal = 0;

    cart.forEach(item => {
        const itemSubtotal = item.retail_price * item.quantity;
        subtotal += itemSubtotal;

        const cartItemRow = document.createElement('div');
        cartItemRow.className = "flex items-center justify-between bg-slate-50 p-3 rounded-xl border border-slate-100";
        cartItemRow.innerHTML = `
            <div class="flex-1 pr-3">
                <h4 class="text-sm font-semibold text-slate-800 line-clamp-1">${item.name}</h4>
                <p class="text-xs text-slate-400 mt-0.5">Rs. ${parseFloat(item.retail_price).toFixed(2)} × ${item.quantity}</p>
            </div>
            <div class="flex items-center space-x-2">
                <div class="flex items-center bg-white border border-slate-200 rounded-lg overflow-hidden">
                    <button onclick="updateQuantity(${item.id}, -1)" class="px-2 py-1 text-slate-500 hover:bg-slate-100 text-xs font-bold">-</button>
                    <span class="px-3 text-xs font-bold text-slate-800">${item.quantity}</span>
                    <button onclick="updateQuantity(${item.id}, 1)" class="px-2 py-1 text-slate-500 hover:bg-slate-100 text-xs font-bold">+</button>
                </div>
                <button onclick="removeFromCart(${item.id})" class="text-slate-400 hover:text-rose-500 transition-colors p-1">
                    <i class="fa-solid fa-trash-can text-xs"></i>
                </button>
            </div>
        `;
        cartItemsContainer.appendChild(cartItemRow);
    });

    // Totals Calculation
    const discount = parseFloat(discountInput.value) || 0;
    const total = Math.max(0, subtotal - discount);

    subtotalEl.innerText = `Rs. ${subtotal.toFixed(2)}`;
    totalEl.innerText = `Rs. ${total.toFixed(2)}`;
}

// 6. Checkout Logic (Offline-First with Auto-Print)
checkoutBtn.onclick = async () => {
    if (cart.length === 0) {
        alert("Cart is empty!");
        return;
    }

    const discount = parseFloat(discountInput.value) || 0;
    const subtotal = cart.reduce((acc, item) => acc + (item.retail_price * item.quantity), 0);
    
    // Create Sale Object
    const saleData = {
        offline_id: 'OFFLINE_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9),
        invoice_no: 'INV-' + Date.now(),
        customer_id: document.getElementById('customer-select').value,
        total_amount: subtotal,
        discount: discount,
        payable_amount: Math.max(0, subtotal - discount),
        payment_method: selectedPaymentMethod,
        items: cart.map(item => ({
            product_id: item.id,
            quantity: item.quantity,
            unit_price: item.retail_price,
            subtotal: item.retail_price * item.quantity
        }))
    };

    // 🔥 MAGIC MOMENT: Order ka data bante hi sabh se pehle Receipt Print trigger karein
    triggerReceiptPrint(saleData);

    // Ab check karein ke online save karna hai ya offline queue mein daalna hai
    if (navigator.onLine) {
        // Online: Direct server pe bhejo
        sendSaleToServer(saleData);
    } else {
        // Offline: IndexedDB queue me dalo
        try {
            await saveToLocalStore('offline_sales', saleData);
            alert("Order saved locally (Offline Mode). It will sync automatically when internet is back!");
            clearCart();
        } catch (err) {
            alert("Error saving order locally!");
        }
    }
};

// Main server sync call
async function sendSaleToServer(sale) {
    try {
        const response = await fetch('api/sync_sales.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(sale)
        });
        const result = await response.json();
        if (result.success) {
            alert("Order placed successfully and synced with MySQL!");
            clearCart();
        } else {
            throw new Error(result.message);
        }
    } catch (err) {
        console.error("Failed to send to server. Queueing locally.", err);
        await saveToLocalStore('offline_sales', sale);
        alert("Server error. Order saved locally instead!");
        clearCart();
    }
}

// 7. Background Sync Logic
async function syncOfflineSales() {
    const offlineSales = await getAllFromLocalStore('offline_sales');
    if (offlineSales.length === 0) return;

    syncIndicator.classList.remove('hidden'); // Show sync spinner

    for (const sale of offlineSales) {
        try {
            const response = await fetch('api/sync_sales.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(sale)
            });
            const result = await response.json();
            if (result.success) {
                // Sync hone ke baad IndexedDB se delete kardo
                await deleteFromLocalStore('offline_sales', sale.offline_id);
            }
        } catch (err) {
            console.error("Sync failed for invoice: " + sale.invoice_no, err);
            break; // Agar ek fail ho jaye to baqi internet stable hone par check karenge
        }
    }

    syncIndicator.classList.add('hidden'); // Hide sync spinner
}

function clearCart() {
    cart = [];
    discountInput.value = 0;
    renderCart();
}

clearCartBtn.onclick = clearCart;
discountInput.oninput = renderCart;

// App Start
updateOnlineStatus();
loadProducts();
// --- Customer Modal Logic ---
const customerSelect = document.getElementById('customer-select');
const customerModal = document.getElementById('customer-modal');
const closeModalBtn = document.getElementById('close-modal-btn');
const cancelModalBtn = document.getElementById('cancel-modal-btn');
const customerForm = document.getElementById('customer-form');

// Dropdown change listener
customerSelect.addEventListener('change', function() {
    if (this.value === '2') { // 'Add New Customer (+)' select hua hai
        openCustomerModal();
    }
});

function openCustomerModal() {
    customerModal.classList.remove('hidden');
    // Modal scale up animation layer trigger karne ke liye
    setTimeout(() => {
        customerModal.querySelector('.transform').classList.remove('scale-95');
        customerModal.querySelector('.transform').classList.add('scale-100');
    }, 10);
    document.getElementById('modal-cust-name').focus();
}

function closeCustomerModal() {
    customerModal.querySelector('.transform').classList.remove('scale-100');
    customerModal.querySelector('.transform').classList.add('scale-95');
    setTimeout(() => {
        customerModal.classList.add('hidden');
        customerForm.reset();
        // Dropdown ko wapas default 'Walk-in Customer' pe set kar do agar bina save kiye band kiya
        if (customerSelect.value === '2') {
            customerSelect.value = '1';
        }
    }, 150);
}

closeModalBtn.onclick = closeCustomerModal;
cancelModalBtn.onclick = closeCustomerModal;

// Form Submission handling (AJAX Call to PHP Backend)
customerForm.onsubmit = async (e) => {
    e.preventDefault();

    const name = document.getElementById('modal-cust-name').value.trim();
    const phone = document.getElementById('modal-cust-phone').value.trim();

    if (!name) return;

    // Portfolio touch: Agar internet nahi hai, toh customer ko offline register karne ki restriction alert dein
    if (!navigator.onLine) {
        alert("Customer registration requires an active internet connection to ensure accounts don't overlap.");
        return;
    }

    try {
        const response = await fetch('api/add_customer.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ name: name, phone: phone })
        });
        
        const result = await response.json();

        if (result.success) {
            // Naya customer dropdown mein inject (add) karein dynamically
            const newOption = document.createElement('option');
            newOption.value = result.id;
            newOption.text = `${result.name} (${phone ? phone : 'No Phone'})`;
            
            // Dropdown mein insertion 'Add New Customer' option se pehle
            customerSelect.insertBefore(newOption, customerSelect.options[customerSelect.options.length - 1]);
            
            // Auto select direct the new customer
            customerSelect.value = result.id;
            
            alert("Customer registered successfully!");
            closeCustomerModal();
        } else {
            alert("Error: " + result.message);
        }
    } catch (err) {
        console.error("Failed to register customer:", err);
        alert("Server error, could not save customer.");
    }
};
// --- Thermal Receipt Printer Integration ---
function triggerReceiptPrint(saleData) {
    // 1. Basic details inject karein
    document.getElementById('print-invoice-no').innerText = `Invoice: ${saleData.invoice_no}`;
    document.getElementById('print-date').innerText = `Date: ${new Date().toLocaleString()}`;
    
    // Customer name select box se nikalyein
    const custSelect = document.getElementById('customer-select');
    const custName = custSelect.options[custSelect.selectedIndex].text;
    document.getElementById('print-customer').innerText = `Customer: ${custName}`;

    // 2. Items loop chala kar raw rows insert karein
    const printBody = document.getElementById('print-items-body');
    printBody.innerHTML = ''; // Pehle se clean karein

    // Cart array se direct names uthane ke liye hum cart list use karenge jo filhal checkout me bani thi
    cart.forEach(item => {
        const row = document.createElement('tr');
        row.style.borderBottom = "1px dotted #000";
        row.innerHTML = `
            <td style="padding: 4px 0; text-align: left;">${item.name}</td>
            <td style="padding: 4px 0; text-align: center;">${item.quantity}</td>
            <td style="padding: 4px 0; text-align: right;">${parseFloat(item.retail_price).toFixed(2)}</td>
            <td style="padding: 4px 0; text-align: right;">${(item.retail_price * item.quantity).toFixed(2)}</td>
        `;
        printBody.appendChild(row);
    });

    // 3. Totals updates
    document.getElementById('print-subtotal').innerText = `Rs. ${parseFloat(saleData.total_amount).toFixed(2)}`;
    document.getElementById('print-discount').innerText = `Rs. ${parseFloat(saleData.discount).toFixed(2)}`;
    document.getElementById('print-total').innerText = `Rs. ${parseFloat(saleData.payable_amount).toFixed(2)}`;

    // 4. Fire Browser Print System!
    // Thoda delay taake DOM update ho jaye phir window trigger ho
    setTimeout(() => {
        window.print();
    }, 250);
}
// --- Real-time Search & Barcode Scanner Emulation ---
const searchInput = document.getElementById('product-search');

if (searchInput) {
    searchInput.addEventListener('input', function(e) {
        const query = e.target.value.toLowerCase().trim();
        const productCards = document.querySelectorAll('.product-card'); // Check karein aapke product div par yeh class ho

        productCards.forEach(card => {
            const productName = card.getAttribute('data-name')?.toLowerCase() || card.innerText.toLowerCase();
            const productCode = card.getAttribute('data-code')?.toLowerCase() || '';

            if (productName.includes(query) || productCode.includes(query)) {
                card.style.display = 'block'; // Match ho gaya toh dikhao
            } else {
                card.style.display = 'none';  // Nahi mila toh chupao
            }
        });
    });

    // Barcode Scanner 'Enter' Key Event Capture
    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault(); // Page reload hone se rokein
            const query = e.target.value.trim();
            
            if (query === '') return;

            // Products array mein search karein (jo db.js ya app.js me load hote hain)
            // Farz karein aapke array ka naam 'products' hai
            const foundProduct = products.find(p => p.product_code === query || p.barcode === query || p.name.toLowerCase() === query.toLowerCase());

            if (foundProduct) {
                // Agar product mil jaye, toh hamara cart wala function trigger karein
                addToCart(foundProduct.id); 
                
                // Field ko khali karein taake agla scan ho sake
                e.target.value = '';
                
                // Baqi cards ko wapas normal show kar dein
                document.querySelectorAll('.product-card').forEach(c => c.style.display = 'block');
            } else {
                alert("Product not found for this code/name!");
            }
        }
    });
}