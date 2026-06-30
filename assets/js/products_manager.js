// assets/js/products_manager.js

let allProductsCache = [];

document.addEventListener("DOMContentLoaded", () => {
    fetchInventoryTable();
});

// 1. Read: Database se live table load karo
async function fetchInventoryTable() {
    try {
        const response = await fetch('api/manage_products.php?action=fetch');
        const res = await response.json();
        
        if (res.success) {
            allProductsCache = res.data;
            renderInventoryTable(res.data);
        }
    } catch (err) {
        console.error("Error loading inventory layout:", err);
    }
}

function renderInventoryTable(products) {
    const tbody = document.getElementById('inventory-table-body');
    tbody.innerHTML = '';

    if (products.length === 0) {
        tbody.innerHTML = `<tr><td colspan="5" class="p-8 text-center text-slate-400 text-sm">No inventory items inside the system.</td></tr>`;
        return;
    }

    products.forEach(p => {
        const isLowStock = parseInt(p.stock_quantity) <= 5;
        const tr = document.createElement('tr');
        tr.className = "hover:bg-slate-50/50 transition-colors";
        
        tr.innerHTML = `
            <td class="p-4 font-semibold text-slate-800">${p.name}</td>
            <td class="p-4 text-xs font-mono text-slate-500">${p.barcode}</td>
            <td class="p-4 font-medium text-slate-900">Rs. ${parseFloat(p.retail_price).toFixed(2)}</td>
            <td class="p-4">
                <span class="text-[10px] uppercase font-bold tracking-wider px-2.5 py-1 rounded-lg ${isLowStock ? 'bg-rose-50 text-rose-600' : 'bg-emerald-50 text-emerald-600'}">
                    ${p.stock_quantity} Items (${isLowStock ? 'Low Stock' : 'Healthy'})
                </span>
            </td>
            <td class="p-4 text-center">
                <div class="flex items-center justify-center space-x-2">
                    <button onclick="openProductModal('edit', ${p.id})" class="w-8 h-8 text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg flex items-center justify-center transition-colors">
                        <i class="fa-solid fa-pen text-xs"></i>
                    </button>
                    <button onclick="deleteProduct(${p.id})" class="w-8 h-8 text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-lg flex items-center justify-center transition-colors">
                        <i class="fa-solid fa-trash text-xs"></i>
                    </button>
                </div>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

// 2. Modals Interface Control Engine
function openProductModal(mode, id = null) {
    const modal = document.getElementById('productModal');
    const form = document.getElementById('productForm');
    const title = document.getElementById('modalTitle');
    
    form.reset();
    document.getElementById('prod-id').value = '';
    modal.classList.remove('hidden');
    modal.classList.add('flex');

    if (mode === 'add') {
        title.innerText = "Add New Product";
    } else if (mode === 'edit' && id) {
        title.innerText = "Modify Product Context";
        const product = allProductsCache.find(p => p.id == id);
        if (product) {
            document.getElementById('prod-id').value = product.id;
            document.getElementById('prod-name').value = product.name;
            document.getElementById('prod-barcode').value = product.barcode;
            
            // 🔥 NEW: Edit mode mein database/cache se Cost Price load karein
            document.getElementById('prod-cost-price').value = product.cost_price;
            
            // ⚡ NOTE: Yahan 'prod-price' ko badal kar 'prod-retail-price' kar diya hai
            // taake jo premium HTML form humne pichle step mein banaya hai, yeh uski ID se match ho jaye.
            document.getElementById('prod-retail-price').value = product.retail_price;
            
            document.getElementById('prod-stock').value = product.stock_quantity;
        }
    }
}

function closeProductModal() {
    const modal = document.getElementById('productModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

// 3. Create & Update Orchestrator
async function saveProduct(e) {
    e.preventDefault();
    const id = document.getElementById('prod-id').value;
    const actionType = id ? 'update' : 'add';
    
    const formData = new FormData(document.getElementById('productForm'));

    try {
        const response = await fetch(`api/manage_products.php?action=${actionType}`, {
            method: 'POST',
            body: formData
        });
        const res = await response.json();
        
        if (res.success) {
            closeProductModal();
            fetchInventoryTable(); // Refresh current layout matrix
            if (typeof fetchDashboardAnalytics === "function") fetchDashboardAnalytics(); // Sync cards and graph metrics
        } else {
            alert("Action breakdown: " + res.message);
        }
    } catch (err) {
        console.error("System configuration error:", err);
    }
}

// 4. Delete Operation Execution
async function deleteProduct(id) {
    if (!confirm("Are you sure you want to completely erase this product from inventory matrix?")) return;

    const formData = new FormData();
    formData.append('id', id);

    try {
        const response = await fetch('api/manage_products.php?action=delete', {
            method: 'POST',
            body: formData
        });
        const res = await response.json();
        
        if (res.success) {
            fetchInventoryTable();
            if (typeof fetchDashboardAnalytics === "function") fetchDashboardAnalytics();
        } else {
            alert("Error isolating structural entity: " + res.message);
        }
    } catch (err) {
        console.error("Critical server breakdown during operation:", err);
    }
}