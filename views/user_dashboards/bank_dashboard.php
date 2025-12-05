<?php
// views/user_dashboards/bank_dashboard.php (FINAL DYNAMIC VERSION)

// Includes the security check, session start, and config
include '../templates/dashboard_header.php'; 

$user_id = $_SESSION['user_id'];
$bankData = null;
$lowStockAlerts = 0;
$pendingHospitalRequests = 0;
$pendingCampCount = 0;
$completedCampCount = 0; 
$lastCompletedCamps = [];
$criticalThreshold = 10; // Defined critical level for inventory

// --- Data Fetching ---

// 1. Fetch Blood Bank-specific data
$sql_bank = "SELECT bank_name, city FROM blood_banks WHERE bank_id = ?";
$stmt_bank = $conn->prepare($sql_bank);
$stmt_bank->bind_param("i", $user_id);
$stmt_bank->execute();
$result_bank = $stmt_bank->get_result();

if ($result_bank->num_rows > 0) {
    $bankData = $result_bank->fetch_assoc();
} else {
    // If bank data is not found, it's a critical error
    die("Error: Blood Bank profile data not found.");
}
$stmt_bank->close();

$bankName = htmlspecialchars($bankData['bank_name']);
$bankCity = htmlspecialchars($bankData['city']);


// 2. Fetch REAL Inventory Data and calculate Low Stock Alerts
$inventorySql = "SELECT blood_group, units_available, component_type 
                 FROM inventory 
                 WHERE bank_id = ? 
                 ORDER BY units_available ASC";
$stmt_inventory = $conn->prepare($inventorySql);
$stmt_inventory->bind_param("i", $user_id);
$stmt_inventory->execute();
$inventoryData = $stmt_inventory->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_inventory->close();

// Calculate low stock alerts from fetched data
foreach($inventoryData as $item) {
    if ($item['units_available'] <= $criticalThreshold) {
        $lowStockAlerts++;
    }
}

// 3. Fetch REAL Pending Hospital Requests (Simulating check for requests local to the bank)
$requestSql = "SELECT COUNT(r.request_id) AS pending_count 
               FROM requests r
               JOIN hospitals h ON r.hospital_id = h.hospital_id
               WHERE r.status IN ('Pending', 'In Progress') 
               AND h.city = ?"; 
$stmt_request = $conn->prepare($requestSql);
$stmt_request->bind_param("s", $bankCity);
$stmt_request->execute();
$pendingHospitalRequests = $stmt_request->get_result()->fetch_assoc()['pending_count'] ?? 0;
$stmt_request->close();


// 4. Fetch All Camp Data for Logged-in Bank ID (Calculates counts and history dynamically)
$allCampsSql = "SELECT location_name, camp_date, status FROM camps WHERE bank_id = ? ORDER BY camp_date DESC";
$stmt_all_camps = $conn->prepare($allCampsSql);
$stmt_all_camps->bind_param("i", $user_id);
$stmt_all_camps->execute();
$allCamps = $stmt_all_camps->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_all_camps->close();

// Calculate counts and populate the history array
foreach ($allCamps as $camp) {
    if ($camp['status'] === 'Scheduled') {
        $pendingCampCount++;
    } elseif ($camp['status'] === 'Completed') {
        $completedCampCount++;
        // Add to last completed array if space allows (we only want 3 recent ones)
        if (count($lastCompletedCamps) < 3) {
            $lastCompletedCamps[] = $camp;
        }
    }
}


// --- Handle Success/Error Messages from Handlers ---
$message = '';
$message_type = '';

if (isset($_GET['camp_success'])) {
    $message = "New donation camp scheduled successfully!";
    $message_type = 'success';
} elseif (isset($_GET['camp_error'])) {
    $message = "Camp scheduling failed. Please try again.";
    $message_type = 'danger';
}
?>

<style>
/* --- EXPERT REDESIGN STYLES --- */

:root {
    --primary-red: #D9232D;      /* Vibrant Blood Red */
    --primary-blue: #1A86FF;     /* Deep Trust Blue */
    --accent-gold: #FFC107;      /* Urgency/Highlight Gold */
    --light-bg: #EBEBEB;         /* Soft Background */
    --dark-text: #212529;
    --card-shadow-3d: 0 18px 40px rgba(0, 0, 0, 0.35); /* Deeper Shadow */
    --glass-effect: rgba(255, 255, 255, 0.15); /* Light glass tint */
}

/* 1. ANIMATED HEADER BACKGROUND (SIMULATED LIQUID EFFECT) */
.dashboard-header {
    background: linear-gradient(135deg, var(--primary-blue) 0%, var(--accent-gold) 100%);
    color: white;
    padding: 80px 0;
    margin-bottom: 40px;
    border-radius: 0 0 40px 40px;
    position: relative;
    overflow: hidden;
    animation: gradientShift 10s ease infinite alternate; /* Subtle background animation */
}
@keyframes gradientShift {
    0% { background-position: 0% 50%; }
    100% { background-position: 100% 50%; }
}
.header-icon { font-size: 3rem; color: rgba(255, 255, 255, 0.8); }

/* 2. STAT CARD: GLASSMORHPISM/NEUMORPHISM HYBRID */
.stat-card {
    background: #ffffff; /* Default background */
    border: none;
    border-radius: 20px;
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.08), inset 0 0 0 1px rgba(255, 255, 255, 0.5); /* Soft outer, subtle inner border */
    transition: all 0.5s cubic-bezier(0.165, 0.84, 0.44, 1);
    padding: 35px 20px;
    text-align: center;
    position: relative;
    overflow: hidden;
    height: 100%;
}

.stat-card:hover {
    transform: translateY(-8px);
    box-shadow: var(--card-shadow-3d);
    filter: brightness(1.05); /* Subtle glowing effect */
}

/* CRITICAL ALERT Visual Style */
.alert-critical-card {
    background: var(--primary-red);
    color: white;
    box-shadow: 0 10px 30px rgba(217, 35, 45, 0.6); /* Red highlight shadow */
}
.alert-critical-card .stat-value, .alert-critical-card p { color: white !important; }


/* Animated Thematic Icon Overlay */
.card-icon-bg {
    position: absolute;
    top: -10px;
    right: -10px;
    font-size: 6em;
    opacity: 0.07;
    transition: transform 0.6s ease;
}
.stat-card:hover .card-icon-bg {
    transform: scale(1.1) rotate(-5deg);
}

.stat-value {
    font-size: 4rem;
    font-weight: 900;
    line-height: 1;
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
}

/* Custom Colors for Stat Values */
.color-critical { color: var(--primary-red); }
.color-pending { color: var(--primary-blue); }
.color-success { color: #28a745; }
.color-gold { color: var(--accent-gold); }

/* Inventory Table Styling for Data Integrity */
.inventory-table th { font-weight: 700; color: var(--dark-text); }
.inventory-table td { vertical-align: middle; }
.inventory-status-chip {
    padding: 0.3rem 0.6rem;
    border-radius: 50px;
    font-weight: 600;
}
.chip-critical { background-color: var(--primary-red); color: white; }
.chip-low { background-color: var(--accent-gold); color: var(--dark-text); }
.chip-ok { background-color: #d4edda; color: #155724; }


/* Action Card Visuals */
.themed-card { border-radius: 20px; }
.themed-card h3 { font-weight: 900; }
.action-card-header {
    background: linear-gradient(90deg, var(--primary-red) 0%, #E04D54 100%);
    padding: 1.2rem;
    border-radius: 20px 20px 0 0;
    color: white;
}
.camp-card-header {
    background: linear-gradient(90deg, var(--primary-blue) 0%, #59A7FF 100%);
}
.action-card-body { padding: 20px; }

/* History List */
.list-group-item { transition: background-color 0.3s ease; }
.list-group-item:hover { background-color: #f0f0f0; }
</style>

<header class="dashboard-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="display-4 font-weight-bold text-white">
                    <i class="fas fa-warehouse mr-3 header-icon"></i> Operations Command
                </h1>
                <p class="lead text-white-50">Welcome, <?php echo $bankName; ?>. Optimized management for resource allocation.</p>
            </div>
            <div class="col-md-4 text-right">
                <p class="mb-0 font-weight-bold text-white">Location: <span class="badge badge-light p-2"><?php echo $bankCity; ?></span></p>
                <p class="mb-0 font-weight-bold text-white">Role: <span class="badge badge-info p-2">Blood Bank</span></p>
            </div>
        </div>
    </div>
</header>

<div class="container">
    
    <?php if ($message): ?>
        <div class="alert alert-<?php echo ($message_type == 'success' ? 'success' : 'danger'); ?> text-center mb-4" role="alert">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>
    
    <div class="row mb-5">
        
        <div class="col-md-3">
            <div class="stat-card position-relative alert-critical-card">
                <div class="card-icon-bg color-light"><i class="fas fa-exclamation-triangle"></i></div>
                <p class="mb-1 font-weight-bold text-white-75">CRITICAL ALERTS</p>
                <div class="stat-value text-white"><?php echo $lowStockAlerts; ?></div>
                <small class="text-white-50">Groups below <?php echo $criticalThreshold; ?> Units</small>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="stat-card bg-white position-relative">
                <div class="card-icon-bg color-pending"><i class="fas fa-hospital-alt"></i></div>
                <p class="text-muted mb-1">Pending Requests</p>
                <div class="stat-value color-pending"><?php echo $pendingHospitalRequests; ?></div>
                <small class="text-secondary">Awaiting Allocation/Delivery (in <?php echo $bankCity; ?>)</small>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="stat-card bg-white position-relative">
                <div class="card-icon-bg color-success"><i class="fas fa-calendar-alt"></i></div>
                <p class="text-muted mb-1">Upcoming Camps</p>
                <div class="stat-value color-success"><?php echo $pendingCampCount; ?></div>
                <small class="text-secondary">Scheduled Collection Drives</small>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="stat-card bg-white history-card position-relative">
                <div class="card-icon-bg color-gold"><i class="fas fa-history"></i></div>
                <p class="text-muted mb-1">Completed Drives</p>
                <div class="stat-value color-gold"><?php echo $completedCampCount; ?></div>
                <small class="text-secondary">Total Successes Recorded</small>
            </div>
        </div>
    </div>
    
    <div class="row mb-5">
        
        <div class="col-lg-6">
            <div class="themed-card p-0 bg-white h-100">
                <div class="action-card-header">
                    <h3 class="mb-0 text-white"><i class="fas fa-cubes mr-2"></i> Real-Time Inventory Stock</h3>
                </div>
                <div class="action-card-body">
                    <p class="text-muted small mb-3">View all component stock levels. Immediate update form below.</p>
                    
                    <form id="inventory-update-form" class="mb-4 p-3 bg-light border rounded">
                        <h5 class="mb-3 small font-weight-bold">Quick Stock Update (Add Units)</h5>
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <select class="form-control form-control-sm" name="blood_group" required>
                                    <option value="">Group</option>
                                    <?php 
                                    $groups = ['O+', 'O-', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-'];
                                    foreach ($groups as $g) { echo "<option value=\"$g\">$g</option>"; }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group col-md-5">
                                <input type="number" class="form-control form-control-sm" name="units" placeholder="Units Added" min="1" required>
                            </div>
                            <div class="form-group col-md-3">
                                <button type="submit" class="btn btn-sm btn-info btn-block">Update</button>
                            </div>
                        </div>
                        <div id="inventory-status-msg" class="text-center small mt-2 d-none"></div>
                    </form>
                    
                    <div class="table-responsive">
                        <table class="table table-striped table-hover inventory-table table-sm">
                            <thead>
                                <tr><th>Group/Component</th><th>Units</th><th>Status</th></tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($inventoryData)): ?>
                                    <?php foreach ($inventoryData as $item): ?>
                                        <?php
                                        $statusText = "OK";
                                        $statusChip = "chip-ok";
                                        if ($item['units_available'] <= $criticalThreshold) {
                                            $statusText = "CRITICAL";
                                            $statusChip = "chip-critical";
                                        } elseif ($item['units_available'] <= 20) {
                                            $statusText = "LOW";
                                            $statusChip = "chip-low";
                                        }
                                        ?>
                                        <tr>
                                            <td><?php echo $item['blood_group']; ?> (<?php echo substr($item['component_type'], 0, 4); ?>)</td>
                                            <td><?php echo $item['units_available']; ?></td>
                                            <td><span class="inventory-status-chip <?php echo $statusChip; ?>"><?php echo $statusText; ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="3" class="text-center text-muted">No inventory records found for this bank.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="themed-card p-0 bg-white h-100">
                <div class="action-card-header camp-card-header">
                    <h3 class="mb-0 text-white"><i class="fas fa-calendar-alt mr-2"></i> Camp Scheduling & Outreach</h3>
                </div>
                <div class="action-card-body">
                    <p class="text-muted small">Manage the calendar for all upcoming blood donation drives.</p>
                    
                    <h5 class="mt-4"><i class="fas fa-plus-circle mr-2"></i> New Donation Camp</h5>
                    <form id="new-camp-form" class="mt-3" action="../../handlers/camp_process.php" method="POST">
                        <div class="form-group">
                            <input type="text" class="form-control" name="camp_location" placeholder="Location (e.g., City Hall, School Gym)" required>
                        </div>
                        <div class="form-group">
                            <input type="date" class="form-control" name="camp_date" required>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-calendar-plus mr-2"></i> Schedule Drive
                        </button>
                    </form>

                    <hr class="my-4">

                    <h5 class="mb-3"><i class="fas fa-history mr-2"></i> Recent History</h5>
                    <p class="text-muted small">You have <?php echo $pendingCampCount; ?> scheduled and <?php echo $completedCampCount; ?> completed collection drive(s).</p>
                    
                    <?php if (!empty($lastCompletedCamps)): ?>
                        <ul class="list-group list-group-flush small">
                            <?php foreach ($lastCompletedCamps as $camp): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?php echo htmlspecialchars($camp['location_name']); ?>
                                    <span class="badge badge-success badge-pill"><?php echo date('M d, Y', strtotime($camp['camp_date'])); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-muted small">No completed campaigns recorded yet for this bank.</p>
                    <?php endif; ?>
                    
                    <a href="<?php echo BASE_URL; ?>views/user_dashboards/camp_management.php" class="btn btn-sm btn-outline-secondary mt-3">View/Manage All Camps</a>
                </div>
            </div>
        </div>
    </div>
    
</div>

<?php 
// Close connection and include footer
$conn->close(); 
include '../templates/dashboard_footer.php'; 
?>