<?php
// views/user_dashboards/hospital_dashboard.php (EXPERT REDESIGN & FUNCTIONAL)

// Includes the security check, session start, and config
include '../templates/dashboard_header.php'; 

$user_id = $_SESSION['user_id'];
$hospitalData = null;
$recipientCount = 0;
$criticalRecipientCount = 0;
$pendingRequests = 0; 
$recipientData = [];
$requestLogData = [];

// --- Data Fetching ---

// 1. Fetch Hospital-specific data
$sql_hospital = "SELECT hospital_name, city FROM hospitals WHERE hospital_id = ?";
$stmt_hospital = $conn->prepare($sql_hospital);
$stmt_hospital->bind_param("i", $user_id);
$stmt_hospital->execute();
$result_hospital = $stmt_hospital->get_result();

if ($result_hospital->num_rows > 0) {
    $hospitalData = $result_hospital->fetch_assoc();
} else {
    die("Error: Hospital profile data not found.");
}
$stmt_hospital->close();

$hospitalName = htmlspecialchars($hospitalData['hospital_name']);
$hospitalCity = htmlspecialchars($hospitalData['city']);

// 2. Fetch Recipient Data (Top 5 active and critical)
$sql_recipients = "SELECT recipient_name, required_organ, blood_group, urgency_level, waitlist_date
                   FROM organ_recipients 
                   WHERE hospital_id = ? AND status = 'Active' 
                   ORDER BY FIELD(urgency_level, 'Critical', 'Urgent', 'Routine'), waitlist_date ASC
                   LIMIT 5"; 
$stmt_recipients = $conn->prepare($sql_recipients);
$stmt_recipients->bind_param("i", $user_id);
$stmt_recipients->execute();
$recipientData = $stmt_recipients->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_recipients->close();

// 3. Calculate Key Metrics
$sql_metrics = "SELECT COUNT(recipient_id) AS total_recipients, 
                       SUM(CASE WHEN urgency_level = 'Critical' THEN 1 ELSE 0 END) AS critical_recipients
                FROM organ_recipients WHERE hospital_id = ?";
$stmt_metrics = $conn->prepare($sql_metrics);
$stmt_metrics->bind_param("i", $user_id);
$stmt_metrics->execute();
$metricsResult = $stmt_metrics->get_result()->fetch_assoc();
$recipientCount = $metricsResult['total_recipients'] ?? 0;
$criticalRecipientCount = $metricsResult['critical_recipients'] ?? 0;
$stmt_metrics->close();

// 4. FETCH HOSPITAL'S OWN BLOOD REQUEST LOG
$requestLogSql = "SELECT requested_group, units_needed, urgency_level, request_time, status
                  FROM requests 
                  WHERE hospital_id = ? 
                  ORDER BY request_time DESC 
                  LIMIT 5"; 
$stmt_log = $conn->prepare($requestLogSql);
$stmt_log->bind_param("i", $user_id);
$stmt_log->execute();
$requestLogData = $stmt_log->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_log->close();

// Calculate Pending Requests (for top metric card)
$pendingRequestsSql = "SELECT COUNT(request_id) AS pending_count 
                       FROM requests 
                       WHERE hospital_id = ? AND status IN ('Pending', 'In Progress')";
$stmt_pending = $conn->prepare($pendingRequestsSql);
$stmt_pending->bind_param("i", $user_id);
$stmt_pending->execute();
$pendingRequests = $stmt_pending->get_result()->fetch_assoc()['pending_count'] ?? 0;
$stmt_pending->close();

// --- Handle Success/Error Messages from Handlers ---
$message = '';
$message_type = '';

if (isset($_GET['request_success'])) {
    $message = "Blood request submitted successfully! Nearby blood banks have been notified.";
    $message_type = 'success';
} elseif (isset($_GET['request_error'])) {
    $message = "Blood request submission failed. Please check data and try again.";
    $message_type = 'danger';
}

?>

<style>
/* --- EXPERT REDESIGN STYLES (MATCHING BLOOD BANK) --- */

:root {
    --primary-red: #D9232D;      
    --primary-blue: #1A86FF;     
    --accent-gold: #FFC107;      
    --dark-text: #212529;
    --card-shadow-3d: 0 18px 40px rgba(0, 0, 0, 0.35); 
}

/* 1. ANIMATED HEADER BACKGROUND (Primary Blue/Trust Theme) */
.dashboard-header {
    background: linear-gradient(135deg, var(--primary-blue) 0%, #59A7FF 100%);
    color: white;
    padding: 80px 0;
    margin-bottom: 40px;
    border-radius: 0 0 40px 40px;
    position: relative;
    overflow: hidden;
    animation: gradientShift 10s ease infinite alternate;
}

@keyframes gradientShift {
    0% { background-position: 0% 50%; }
    100% { background-position: 100% 50%; }
}
.header-icon { font-size: 3rem; color: rgba(255, 255, 255, 0.8); }

/* 2. STAT CARD: Hybrid Design */
.stat-card {
    background: #ffffff; 
    border: none;
    border-radius: 20px;
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.08), inset 0 0 0 1px rgba(255, 255, 255, 0.5); 
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
    filter: brightness(1.05); 
}

/* CRITICAL ALERT Visual Style */
.alert-pending-card {
    background: linear-gradient(145deg, var(--primary-red), #FFDDDD);
    color: white;
    box-shadow: 0 10px 30px rgba(217, 35, 45, 0.6);
}
.alert-pending-card p, .alert-pending-card .stat-value { color: white !important; }

.stat-value {
    font-size: 4rem;
    font-weight: 900;
    line-height: 1;
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
}

/* 3. Action Card Headers */
.action-card-blood {
    background: linear-gradient(90deg, var(--primary-red) 0%, #E04D54 100%);
    padding: 1.2rem;
    border-radius: 20px 20px 0 0;
    color: white;
}
.action-card-organ {
    background: linear-gradient(90deg, var(--accent-gold) 0%, #FFD750 100%);
    padding: 1.2rem;
    border-radius: 20px 20px 0 0;
    color: var(--dark-text);
}
.themed-card { border-radius: 20px; }
.themed-card h3 { font-weight: 900; }

.urgency-critical { color: var(--primary-red); font-weight: bold; }
.urgency-urgent { color: var(--accent-gold); font-weight: bold; }
</style>

<!-- ========================================================= -->
<!-- HTML Structure with Dynamic Data (Redesigned) -->
<!-- ========================================================= -->

<!-- Dashboard Header Section -->
<header class="dashboard-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="display-4 font-weight-bold text-white">
                    <i class="fas fa-chart-line mr-3 header-icon"></i> Hospital Operations
                </h1>
                <p class="lead text-white-50">Welcome, <?php echo $hospitalName; ?>. Focused resource allocation and patient management.</p>
            </div>
            <div class="col-md-4 text-right">
                <p class="mb-0 font-weight-bold text-white">Location: <span class="badge badge-light p-2"><?php echo $hospitalCity; ?></span></p>
                <p class="mb-0 font-weight-bold text-white">Verification: <span class="badge badge-success p-2">Verified</span></p>
            </div>
        </div>
    </div>
</header>

<div class="container">
    
    <!-- Status Message Display -->
    <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type; ?> text-center mb-4" role="alert">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>
    
    <!-- 1. Key Metrics Row (Prioritized Visuals) -->
    <div class="row mb-5">
        
        <!-- Metric 1: Pending Blood Requests (Highest Visual Priority) -->
        <div class="col-md-4">
            <div class="stat-card position-relative <?php echo $pendingRequests > 0 ? 'alert-pending-card' : 'bg-white'; ?>">
                 <div class="card-icon-bg" style="color: <?php echo $pendingRequests > 0 ? 'rgba(255,255,255,0.1)' : 'rgba(217, 35, 45, 0.1)'; ?>"><i class="fas fa-hourglass-half"></i></div>
                <p class="mb-1 font-weight-bold text-muted">PENDING BLOOD REQUESTS</p>
                <div class="stat-value <?php echo $pendingRequests > 0 ? 'text-white' : 'color-critical'; ?>"><?php echo $pendingRequests; ?></div>
                <small class="text-secondary">Awaiting Blood Bank Action</small>
            </div>
        </div>
        
        <!-- Metric 2: Total Active Organ Recipients -->
        <div class="col-md-4">
            <div class="stat-card bg-white position-relative">
                 <div class="card-icon-bg" style="color: rgba(26, 134, 255, 0.1);"><i class="fas fa-user-friends"></i></div>
                <p class="text-muted mb-1">TOTAL ACTIVE RECIPIENTS</p>
                <div class="stat-value color-pending"><?php echo $recipientCount; ?></div>
                <small class="text-secondary">Patients on Organ Waitlist</small>
            </div>
        </div>
        
        <!-- Metric 3: Critical Organ Recipients -->
        <div class="col-md-4">
            <div class="stat-card bg-white position-relative">
                <div class="card-icon-bg" style="color: rgba(255, 193, 7, 0.1);"><i class="fas fa-user-injured"></i></div>
                <p class="text-muted mb-1">CRITICAL ORGAN PRIORITY</p>
                <div class="stat-value urgency-urgent"><?php echo $criticalRecipientCount; ?></div>
                <small class="text-secondary">Requires Immediate Matching</small>
            </div>
        </div>
    </div>
    
    <!-- 2. Blood Request & Log Section -->
    <div class="row mb-5">
        <div class="col-lg-6">
            <div class="themed-card p-0 bg-white h-100">
                <div class="action-card-blood">
                    <h3 class="mb-0 text-white"><i class="fas fa-heartbeat mr-2"></i> Blood Request Management</h3>
                </div>
                <div class="p-4 action-card-body">
                    <p class="text-muted small">Initiate new blood requests. This form posts to the database.</p>
                    
                    <form id="new-blood-request-form" class="mt-4" action="../../handlers/request_process.php" method="POST">
                        <h5 class="font-weight-bold mb-3 small">Initiate New Request</h5>
                        
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="bg_req" class="small">Group</label>
                                <select class="form-control form-control-sm" name="blood_group_request" id="bg_req" required>
                                    <option value="">Group...</option>
                                    <option value="O-">O-</option>
                                    <option value="O+">O+</option>
                                    <option value="A-">A-</option>
                                    <option value="A+">A+</option>
                                    <option value="B-">B-</option>
                                    <option value="B+">B+</option>
                                    <option value="AB-">AB-</option>
                                    <option value="AB+">AB+</option>
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                 <label for="units_req" class="small">Units</label>
                                <input type="number" class="form-control form-control-sm" id="units_req" name="units_needed" placeholder="Units" min="1" required>
                            </div>
                            <div class="form-group col-md-4">
                                 <label for="urgency_req" class="small">Urgency</label>
                                <select class="form-control form-control-sm" name="urgency_level" id="urgency_req" required>
                                    <option value="Routine">Routine</option>
                                    <option value="Urgent">Urgent</option>
                                    <option value="Critical">Critical</option>
                                </select>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-paper-plane mr-2"></i> Submit Request
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Hospital's Own Blood Request Log -->
        <div class="col-lg-6">
            <div class="themed-card p-0 bg-white h-100">
                <div class="action-card-blood" style="background: linear-gradient(90deg, var(--primary-blue) 0%, #59A7FF 100%);">
                    <h3 class="mb-0 text-white"><i class="fas fa-list-alt mr-2"></i> Recent Request Log</h3>
                </div>
                <div class="p-4 action-card-body">
                    <p class="text-muted small">Last 5 blood requests submitted by this institution.</p>
                    
                    <div class="table-responsive mt-3">
                        <table class="table table-striped table-sm">
                            <thead>
                                <tr><th>Group</th><th>Units</th><th>Urgency</th><th>Status</th></tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($requestLogData)): ?>
                                    <?php foreach ($requestLogData as $request): 
                                        $statusClass = '';
                                        if ($request['status'] === 'Pending') $statusClass = 'badge-warning';
                                        if ($request['status'] === 'In Progress') $statusClass = 'badge-info';
                                        if ($request['status'] === 'Fulfilled') $statusClass = 'badge-success';
                                    ?>
                                    <tr>
                                        <td><span class="badge badge-secondary"><?php echo htmlspecialchars($request['requested_group']); ?></span></td>
                                        <td><?php echo htmlspecialchars($request['units_needed']); ?></td>
                                        <td><?php echo htmlspecialchars($request['urgency_level']); ?></td>
                                        <td><span class="badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars($request['status']); ?></span></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="4" class="text-center text-muted">No recent requests submitted.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <a href="<?php echo BASE_URL; ?>views/user_dashboards/request_log_full.php" class="btn btn-sm btn-outline-secondary mt-3">View Full Log</a>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Organ Recipient Tracking -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="themed-card p-0 bg-white">
                <div class="action-card-organ">
                    <h3 class="mb-0"><i class="fas fa-user-friends mr-2"></i> Organ Recipient Tracking</h3>
                </div>
                <div class="p-4 action-card-body">
                    <p class="text-muted">Prioritized list of active patients based on urgency and waitlist time.</p>
                    
                    <div class="table-responsive mt-4">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Organ Required</th>
                                    <th>Blood Group</th>
                                    <th>Urgency</th>
                                    <th>Wait Since</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($recipientData)): ?>
                                    <?php foreach ($recipientData as $recipient): 
                                        $urgencyClass = 'urgency-routine';
                                        if ($recipient['urgency_level'] == 'Critical') {
                                            $urgencyClass = 'urgency-critical';
                                        } elseif ($recipient['urgency_level'] == 'Urgent') {
                                            $urgencyClass = 'urgency-urgent';
                                        }
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($recipient['recipient_name']); ?></td>
                                        <td><?php echo htmlspecialchars($recipient['required_organ']); ?></td>
                                        <td><span class="badge badge-secondary p-2"><?php echo htmlspecialchars($recipient['blood_group']); ?></span></td>
                                        <td class="<?php echo $urgencyClass; ?>"><?php echo htmlspecialchars($recipient['urgency_level']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($recipient['waitlist_date'])); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="5" class="text-center text-muted">No active high-priority recipients currently managed by this hospital.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <a href="<?php echo BASE_URL; ?>views/user_dashboards/recipient_registry_full.php" class="btn btn-sm btn-outline-primary mt-3">View Full Recipient Registry (Advanced)</a>
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