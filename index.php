<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$currentUser = 'User';
if (!empty($_SESSION['full_name'])) {
    $currentUser = $_SESSION['full_name'];
} elseif (!empty($_SESSION['username'])) {
    $currentUser = $_SESSION['username'];
}

$userRole = $_SESSION['role'] ?? 'Staff'; 
$isAdmin = (strtolower($userRole) === 'admin' || strtolower($userRole) === 'system admin');
?>?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ECO Cargo - QR Scanner</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <style>
        #reader { width: 100%; max-width: 500px; margin: 0 auto; }
        .result-card { display: none; }
    </style>
</head>
<body class="bg-light">

<!-- Header Navigation Bar -->
<nav class="navbar navbar-dark bg-dark mb-3">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">ECO Cargo</a>
        <div class="d-flex align-items-center">
            <span id="networkBadge" class="badge bg-success me-3">Online</span>
            <span class="text-white me-3 fw-bold">
                User: <span id="currentUserSpan"><?= htmlspecialchars($currentUser) ?></span> 
                (<span id="userRoleSpan"><?= htmlspecialchars($userRole) ?></span>)
            </span>
            <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
        </div>
    </div>
</nav>

<div class="container py-2" style="max-width: 700px;">

    <!-- Offline Alert -->
    <div id="offlineAlert" class="alert alert-warning d-none text-center" role="alert">
        <strong>Offline Mode Active:</strong> Scans & updates will be saved locally and auto-synced when online.
        <span id="pendingCount" class="badge bg-dark ms-2">0 Pending</span>
    </div>

    <!-- Scanner & Manual Entry Section -->
    <div class="card shadow-sm p-3 mb-4" id="scannerCard">
        <h5 class="text-center fw-bold mb-3">Cargo QR Scanner</h5>
        <div id="reader"></div>
        
        <div class="text-center my-3 text-muted fw-bold">- OR -</div>
        
        <!-- Manual Tracking Code Input -->
        <div class="input-group">
            <input type="text" id="manualTrackingInput" class="form-control" placeholder="Enter Tracking Number manually...">
            <button class="btn btn-outline-primary" type="button" onclick="handleManualSearch()">Verify Code</button>
        </div>
    </div>

    <!-- Scan Results & Status Form -->
    <div id="resultCard" class="card shadow-sm p-4 result-card">
        <h4 class="card-title text-success fw-bold mb-3" id="resTracking"></h4>
        
        <div class="row">
            <div class="col-6 mb-2"><strong>Invoice:</strong> <span id="resInvoice">-</span></div>
            <div class="col-6 mb-2"><strong>Box Info:</strong> <span id="resBoxInfo">-</span></div>
            <div class="col-12 mb-2"><strong>Sender:</strong> <span id="resSender">-</span></div>
            <div class="col-12 mb-2"><strong>Receiver:</strong> <span id="resReceiver">-</span></div>
            <div class="col-12 mb-2"><strong>Receiver Email:</strong> <span id="resReceiverEmail" class="text-primary">-</span></div>
            <div class="col-12 mb-2">
                <strong>Current Status:</strong> 
                <span id="resStatus" class="badge bg-primary fs-6">-</span>
            </div>
            <div class="col-12 mb-1"><small class="text-muted">Last Updated By: <span id="resUpdatedBy">-</span></small></div>
            <div class="col-12 mb-3"><small class="text-muted">Last Updated At: <span id="resUpdatedAt">-</span></small></div>
        </div>

        <hr>

        <?php if ($isAdmin): ?>
            <!-- ADMIN VIEW: Single Unified Dropdown -->
            <div class="mb-3">
                <label for="adminStatusSelect" class="form-label fw-bold">Update Shipment Status (Admin Full Access):</label>
                <select id="adminStatusSelect" class="form-select mb-3">
                    <option value="1. Warehouse">1. Warehouse</option>
                    <option value="2. In Transit to Port">2. In Transit to Port</option>
                    <option value="3. Arrive in to Port">3. Arrive in to Port</option>
                    <option value="4. Shipped to Destination Country">4. Shipped to Destination Country</option>
                    <option value="5. Arrived at Destination Port">5. Arrived at Destination Port</option>
                    <option value="6. Customs Cleared">6. Customs Cleared</option>
                    <option value="7. Out for Delivery">7. Out for Delivery</option>
                    <option value="8. Delivered">8. Delivered</option>
                </select>
            </div>
        <?php else: ?>
            <!-- STAFF VIEW: Two Divided Sections -->
            <div class="mb-3" id="stage1Container">
                <label for="originStatusSelect" class="form-label fw-bold">Origin Country Status (Export Logistics):</label>
                <select id="originStatusSelect" class="form-select mb-2" onchange="lastChangedSelect = 'origin'">
                    <option value="1. Warehouse">1. Warehouse</option>
                    <option value="2. In Transit to Port">2. In Transit to Port</option>
                    <option value="3. Arrive in to Port">3. Arrive in to Port</option>
                    <option value="4. Shipped to Destination Country">4. Shipped to Destination Country</option>
                </select>
            </div>

            <div class="mb-3 d-none" id="stage2Container">
                <label for="destStatusSelect" class="form-label fw-bold text-success">Destination Country Status (Import Logistics):</label>
                <select id="destStatusSelect" class="form-select mb-2" onchange="lastChangedSelect = 'dest'">
                    <option value="5. Arrived at Destination Port">5. Arrived at Destination Port</option>
                    <option value="6. Customs Cleared">6. Customs Cleared</option>
                    <option value="7. Out for Delivery">7. Out for Delivery</option>
                    <option value="8. Delivered">8. Delivered</option>
                </select>
            </div>
        <?php endif; ?>

        <button class="btn btn-success w-100 mb-2" onclick="handleStatusUpdate()">Save New Status</button>
        <button class="btn btn-outline-secondary w-100" onclick="resetScanner()">Scan Next Box</button>
    </div>
</div>

<script>
    let html5QrcodeScanner;
    let currentTrackingNumber = '';
    let lastChangedSelect = 'origin';
    const currentUser = "<?= addslashes($currentUser) ?>";
    const userRole = "<?= addslashes($userRole) ?>";
    const isAdmin = <?= $isAdmin ? 'true' : 'false' ?>;

    const statusOrder = [
        "1. Warehouse",
        "2. In Transit to Port",
        "3. Arrive in to Port",
        "4. Shipped to Destination Country",
        "5. Arrived at Destination Port",
        "6. Customs Cleared",
        "7. Out for Delivery",
        "8. Delivered"
    ];

    // --- INDEXEDDB SETUP FOR OFFLINE SYNC ---
    let db;
    const dbName = "EcoCargoOfflineDB";
    const storeName = "pendingUpdates";

    function initIndexedDB() {
        const request = indexedDB.open(dbName, 1);
        request.onerror = () => console.error("IndexedDB error");
        request.onsuccess = (event) => {
            db = event.target.result;
            updatePendingBadge();
            if (navigator.onLine) {
                syncOfflineQueue();
            }
        };
        request.onupgradeneeded = (event) => {
            db = event.target.result;
            if (!db.objectStoreNames.contains(storeName)) {
                db.createObjectStore(storeName, { keyPath: "id", autoIncrement: true });
            }
        };
    }

    function saveToOfflineQueue(payload) {
        if (!db) return;
        const transaction = db.transaction([storeName], "readwrite");
        const store = transaction.objectStore(storeName);
        store.add(payload);
        transaction.oncomplete = () => {
            updatePendingBadge();
            alert("Network offline: Status update saved locally and will sync when reconnected.");
            resetScanner();
        };
    }

    function updatePendingBadge() {
        if (!db) return;
        const transaction = db.transaction([storeName], "readonly");
        const store = transaction.objectStore(storeName);
        const countRequest = store.count();
        countRequest.onsuccess = () => {
            const count = countRequest.result;
            const badge = document.getElementById('pendingCount');
            if (badge) badge.innerText = `${count} Pending`;
        };
    }

    function syncOfflineQueue() {
        if (!db || !navigator.onLine) return;
        const transaction = db.transaction([storeName], "readwrite");
        const store = transaction.objectStore(storeName);
        const getAllRequest = store.getAll();

        getAllRequest.onsuccess = () => {
            const items = getAllRequest.result;
            if (items.length === 0) return;

            items.forEach(item => {
                fetch('api_update_status.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(item)
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        // Delete successfully synced item
                        const delTx = db.transaction([storeName], "readwrite");
                        delTx.objectStore(storeName).delete(item.id);
                        delTx.oncomplete = () => updatePendingBadge();
                    }
                })
                .catch(err => console.log("Sync retry pending...", err));
            });
        };
    }

    // Network Event Listeners
    window.addEventListener('online', () => {
        document.getElementById('networkBadge').className = "badge bg-success me-3";
        document.getElementById('networkBadge').innerText = "Online";
        document.getElementById('offlineAlert').classList.add('d-none');
        syncOfflineQueue();
    });

    window.addEventListener('offline', () => {
        document.getElementById('networkBadge').className = "badge bg-danger me-3";
        document.getElementById('networkBadge').innerText = "Offline";
        document.getElementById('offlineAlert').classList.remove('d-none');
    });

    // --- APPLICATION LOGIC ---
    function fetchShipmentDetails(trackingNumber) {
        if (!trackingNumber.trim()) {
            alert("Please enter or scan a valid tracking number.");
            return;
        }

        if (!navigator.onLine) {
            // Offline fallback simulation
            alert("Offline Mode: Unable to fetch live server records. Proceeding with manual status notation.");
            return;
        }

        fetch(`api_scan.php?trackingNumber=${encodeURIComponent(trackingNumber.trim())}`)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    const item = data.data;
                    currentTrackingNumber = item.TrackingNumber;
                    document.getElementById('resTracking').innerText = `Tracking: ${item.TrackingNumber}`;
                    document.getElementById('resInvoice').innerText = item.InvoiceNumber;
                    document.getElementById('resBoxInfo').innerText = `${item.BoxSize} (${item.BoxType})`;
                    document.getElementById('resSender').innerText = item.SenderName;
                    document.getElementById('resReceiver').innerText = item.ReceiverName;
                    document.getElementById('resReceiverEmail').innerText = item.ReceiverEmail ? item.ReceiverEmail : 'No Email On File';

                    const currentStatus = item.Status || '1. Warehouse';

                    document.getElementById('resStatus').innerText = currentStatus;
                    document.getElementById('resUpdatedBy').innerText = item.UpdatedBy ? item.UpdatedBy : 'Not Updated Yet';
                    document.getElementById('resUpdatedAt').innerText = item.UpdatedAt ? item.UpdatedAt : 'Not Updated Yet';

                    configureStatusDropdowns(currentStatus);

                    document.getElementById('scannerCard').style.display = 'none';
                    document.getElementById('resultCard').style.display = 'block';
                } else {
                    alert(data.message || "Shipment record not found.");
                }
            })
            .catch(() => alert("Network error connecting to server."));
    }

    function handleManualSearch() {
        const manualCode = document.getElementById('manualTrackingInput').value;
        fetchShipmentDetails(manualCode);
    }

    function configureStatusDropdowns(currentStatus) {
        const currentIndex = statusOrder.indexOf(currentStatus);
        const shippedIndex = statusOrder.indexOf("4. Shipped to Destination Country");

        if (isAdmin) {
            const adminSelect = document.getElementById('adminStatusSelect');
            adminSelect.value = currentStatus;
            Array.from(adminSelect.options).forEach(opt => opt.disabled = false);
        } else {
            const originSelect = document.getElementById('originStatusSelect');
            const destSelect = document.getElementById('destStatusSelect');
            const stage2Container = document.getElementById('stage2Container');

            if (currentIndex >= shippedIndex) {
                originSelect.value = "4. Shipped to Destination Country";
                originSelect.disabled = true;

                stage2Container.classList.remove('d-none');
                destSelect.disabled = false;
                destSelect.value = currentIndex > shippedIndex ? currentStatus : "5. Arrived at Destination Port";
                lastChangedSelect = 'dest';
            } else {
                originSelect.disabled = false;
                originSelect.value = currentStatus;
                stage2Container.classList.add('d-none');
                lastChangedSelect = 'origin';
            }

            Array.from(originSelect.options).forEach(opt => {
                const optIndex = statusOrder.indexOf(opt.value);
                opt.disabled = (optIndex < currentIndex);
            });

            Array.from(destSelect.options).forEach(opt => {
                const optIndex = statusOrder.indexOf(opt.value);
                opt.disabled = (optIndex < currentIndex);
            });
        }
    }

    function handleStatusUpdate() {
        let selectedStatus = '';

        if (isAdmin) {
            selectedStatus = document.getElementById('adminStatusSelect').value;
        } else {
            const originSelect = document.getElementById('originStatusSelect');
            const destSelect = document.getElementById('destStatusSelect');
            const stage2Container = document.getElementById('stage2Container');

            if (lastChangedSelect === 'dest' && !stage2Container.classList.contains('d-none') && !destSelect.disabled) {
                selectedStatus = destSelect.value;
            } else {
                selectedStatus = originSelect.value;
            }
        }

        const now = new Date().toISOString().slice(0, 19).replace('T', ' ');
        const payload = {
            trackingNumber: currentTrackingNumber,
            status: selectedStatus,
            updatedBy: currentUser,
            updatedAt: now
        };

        if (!navigator.onLine) {
            saveToOfflineQueue(payload);
            return;
        }

        fetch('api_update_status.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                document.getElementById('resStatus').innerText = selectedStatus;
                document.getElementById('resUpdatedBy').innerText = data.updatedBy;
                document.getElementById('resUpdatedAt').innerText = data.updatedAt;
                alert("Status updated successfully!");
                configureStatusDropdowns(selectedStatus);
            } else {
                alert(data.message || "Failed to update status.");
            }
        })
        .catch(() => {
            // Fallback if fetch drops mid-request
            saveToOfflineQueue(payload);
        });
    }

    function onScanSuccess(decodedText) {
        if (html5QrcodeScanner) {
            html5QrcodeScanner.clear().catch(err => console.error(err));
        }
        fetchShipmentDetails(decodedText);
    }

    function resetScanner() {
        document.getElementById('resultCard').style.display = 'none';
        document.getElementById('scannerCard').style.display = 'block';
        document.getElementById('manualTrackingInput').value = '';
        currentTrackingNumber = '';
        initScanner();
    }

    function initScanner() {
        try {
            html5QrcodeScanner = new Html5QrcodeScanner("reader", { fps: 10, qrbox: { width: 250, height: 250 } });
            html5QrcodeScanner.render(onScanSuccess);
        } catch (e) {
            console.log("Scanner init note:", e);
        }
    }

    window.onload = () => {
        initIndexedDB();
        initScanner();
    };
</script>

</body>
</html>