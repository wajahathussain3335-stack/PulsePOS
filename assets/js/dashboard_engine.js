// assets/js/dashboard_engine.js

let salesChartInstance = null;

document.addEventListener("DOMContentLoaded", () => {
    // Default 7 days configuration setting
    const today = new Date().toISOString().split('T')[0];
    const lastWeek = new Date();
    lastWeek.setDate(lastWeek.getDate() - 7);
    const lastWeekStr = lastWeek.toISOString().split('T')[0];

    document.getElementById('filter-start').value = lastWeekStr;
    document.getElementById('filter-end').value = today;

    // 🔥 FIXED: Function ka naam fetchDashboardAnalytics se badal kar loadDashboardAnalytics kar diya
    loadDashboardAnalytics(lastWeekStr, today);
});

function filterAnalytics() {
    const start = document.getElementById('filter-start').value;
    const end = document.getElementById('filter-end').value;
    if (start && end) {
        loadDashboardAnalytics(start, end);
    }
}

// Analytics Load karne wale function ke andar jahan text update ho raha hai
async function loadDashboardAnalytics(startDate, endDate) {
    try {
        const response = await fetch(`api/get_analytics.php?start_date=${startDate}&end_date=${endDate}`);
        const res = await response.json();
        
        // 🔥 FIXED: Flat response ke mutabiq conditions aur keys set kar di hain
        if (res.status === 'success') {
            
            // Total Revenue Card setup
            if(document.getElementById('card-sales')) {
                document.getElementById('card-sales').innerText = `Rs. ${res.total_revenue}`;
            }
            
            // Total Orders Card setup
            if(document.getElementById('card-orders')) {
                document.getElementById('card-orders').innerText = res.total_orders;
            }
            
            // Total Products/Items Card setup (Dono IDs handle kar li hain safety ke liye)
            const itemsElement = document.getElementById('card-items') || document.getElementById('card-products');
            if (itemsElement) {
                itemsElement.innerText = res.total_products;
            }
            
            // 🔥 FIXED: Net Profit Card ko flat live data se hydrate karein
            if(document.getElementById('card-profit')) {
                document.getElementById('card-profit').innerText = `Rs. ${res.net_profit}`;
            }

            // Trend lines/charts hydrate karne ke liye placeholders (agar backend static data de raha hai filhal)
            // Agay chalkar agar dynamic chart data aaye toh labels aur values ko array pass kar sakte hain
            const dummyLabels = [startDate, endDate];
            const dummyValues = [0, parseFloat(res.total_revenue)];
            renderSalesChart(dummyLabels, dummyValues);

        } else {
            console.error("Backend returned error:", res.message);
        }
    } catch (err) {
        console.error("Failed to load dashboard metrics:", err);
    }
}

function renderSalesChart(labels, values) {
    const chartCanvas = document.getElementById('salesTrendsChart');
    if (!chartCanvas) return; // Safety exit agar dashboard par chart element na ho

    const ctx = chartCanvas.getContext('2d');
    
    if (salesChartInstance) {
        salesChartInstance.destroy();
    }

    const blueGradient = ctx.createLinearGradient(0, 0, 0, 300);
    blueGradient.addColorStop(0, 'rgba(37, 99, 235, 0.25)');
    blueGradient.addColorStop(1, 'rgba(37, 99, 235, 0.0)');

    salesChartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Revenue (Rs.)',
                data: values,
                borderColor: '#2563eb',
                borderWidth: 3,
                backgroundColor: blueGradient,
                fill: true,
                tension: 0.35,
                pointBackgroundColor: '#2563eb',
                pointHoverRadius: 7
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    grid: { color: '#f1f5f9' },
                    ticks: {
                        callback: value => 'Rs. ' + value,
                        font: { size: 11 }
                    }
                },
                x: { grid: { display: false }, ticks: { font: { size: 11 } } }
            }
        }
    });
}

// CSV Data exporter mechanism
function exportSalesToCSV() {
    const start = document.getElementById('filter-start').value;
    const end = document.getElementById('filter-end').value;
    
    let csvContent = "Report Title, PulsePOS Sales Ledger Export\n";
    csvContent += `Duration, ${start} to ${end}\n\n`;
    csvContent += "Date, Total Revenue (Rs.)\n";
    
    if (salesChartInstance && salesChartInstance.data.labels.length > 0) {
        const labels = salesChartInstance.data.labels;
        const dataValues = salesChartInstance.data.datasets[0].data;
        
        labels.forEach((label, index) => {
            csvContent += `${label}, ${dataValues[index]}\n`;
        });
        
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        
        const link = document.createElement("a");
        link.setAttribute("href", url);
        link.setAttribute("download", `PulsePOS_Sales_Report_${start}_to_${end}.csv`);
        link.style.visibility = 'hidden';
        
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        URL.revokeObjectURL(url);
    } else {
        alert("Pehle graph mein data load hone dein, uske baad export karein!");
    }
}