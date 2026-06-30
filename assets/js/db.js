// assets/js/db.js

const dbName = "PulsePOS_LocalDB";
const dbVersion = 1;
let db;

// Initialize IndexedDB
function initDB() {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open(dbName, dbVersion);

        request.onerror = (event) => {
            console.error("IndexedDB Error:", event.target.error);
            reject(event.target.error);
        };

        request.onsuccess = (event) => {
            db = event.target.result;
            console.log("IndexedDB Initialized Successfully.");
            resolve(db);
        };

        // Tables (Object Stores) creation if not exists
        request.onupgradeneeded = (event) => {
            const db = event.target.result;
            
            // 1. Products Store (Local Cache)
            if (!db.objectStoreNames.contains("products")) {
                db.createObjectStore("products", { keyPath: "id" });
                console.log("Products Object Store Created.");
            }

            // 2. Offline Sales Store (Queue)
            if (!db.objectStoreNames.contains("offline_sales")) {
                db.createObjectStore("offline_sales", { keyPath: "offline_id" });
                console.log("Offline Sales Object Store Created.");
            }
        };
    });
}

// Helper Function: Save data to a store
function saveToLocalStore(storeName, data) {
    return new Promise((resolve, reject) => {
        const transaction = db.transaction([storeName], "readwrite");
        const store = transaction.objectStore(storeName);
        
        // Agar array hai to loop chalayein, warna single object add karein
        if (Array.isArray(data)) {
            data.forEach(item => store.put(item));
        } else {
            store.put(data);
        }

        transaction.oncomplete = () => resolve(true);
        transaction.onerror = (e) => reject(e.target.error);
    });
}

// Helper Function: Get all data from a store
function getAllFromLocalStore(storeName) {
    return new Promise((resolve, reject) => {
        const transaction = db.transaction([storeName], "readonly");
        const store = transaction.objectStore(storeName);
        const request = store.getAll();

        request.onsuccess = () => resolve(request.result);
        request.onerror = (e) => reject(e.target.error);
    });
}

// Helper Function: Delete a single item (Used after syncing with MySQL)
function deleteFromLocalStore(storeName, key) {
    return new Promise((resolve, reject) => {
        const transaction = db.transaction([storeName], "readwrite");
        const store = transaction.objectStore(storeName);
        const request = store.delete(key);

        request.onsuccess = () => resolve(true);
        request.onerror = (e) => reject(e.target.error);
    });
}

// Automatically start DB on load
initDB().catch(err => console.error("DB Initialization failed", err));