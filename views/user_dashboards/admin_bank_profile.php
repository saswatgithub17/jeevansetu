<?php
// views/user_dashboards/admin_bank_profile.php

include '../templates/dashboard_header.php'; 

// Security Check: Only Admin can access this page
if ($_SESSION['user_type'] !== 'admin') {
    header("Location: " . BASE_URL . "views/public/login.php?error=unauthorized_access");
    exit;
}

$target_bank_id = intval($_GET['bank_id'] ?? 0);

if ($target_bank_id === 0) {
    die('<div class="container py-5"><div class="alert alert-danger">Error: No Bank ID specified.</div><a href="admin_dashboard.php">Back to Admin Dashboard</a></div>');
}

// --- Data Fetching ---

// 1. Fetch Bank & User Data (Combined)
$sql_bank_data = "SELECT 
    b.bank_name, b.license_number, b.contact_person, b.phone_number, b.address_line_1, b.city, b.pincode,
    u.email, u.status as user_status, u.created_at
FROM blood_banks b
JOIN users u ON b.bank_id = u.user_id
WHERE b.bank_id = ?";

$stmt = $conn->prepare($sql_bank_data);
$stmt->bind_param("i", $target_bank_id);
$stmt->execute();
$bankProfile = $stmt->get_result()->fetch_assoc();
$stmt->close();

// 2. Fetch Operational Metrics (Inventory & Camps)
// Total Inventory Count
$sql_inventory = "SELECT SUM(units_available) AS total_units FROM inventory WHERE bank_id = ?";
$stmt_inv = $conn->prepare($sql_inventory);
$stmt_inv->bind_param("i", $target_bank_id);
$stmt_inv->execute();
$totalInventory = $stmt_inv->get_result()->fetch_assoc()['total_units'] ?? 0;
$stmt_inv->close();

// Camp Counts
$sql_camps = "SELECT 
    SUM(CASE WHEN status = 'Scheduled' THEN 1 ELSE 0 END) AS scheduled_camps,
    SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) AS completed_camps
FROM camps WHERE bank_id = ?";
$stmt_camps = $conn->prepare($sql_camps);
$stmt_camps->bind_param("i", $target_bank_id);
$stmt_camps->execute();
$campMetrics = $stmt_camps->get_result()->fetch_assoc();
$stmt_camps->close();

$conn->close();

if (!$bankProfile) {
    die('<div class="container py-5"><div class="alert alert-warning">Blood Bank profile not found.</div><a href="admin_dashboard.php">Back to Admin Dashboard</a></div>');
}

$bankName = htmlspecialchars($bankProfile['bank_name']);
$statusColor = $bankProfile['user_status'] === 'active' ? 'success' : 'danger';
?>

<style>
.profile-card { border-left: 5px solid var(--accent-gold); border-radius: 10px; }
.detail-label { font-weight: 600; color: #6c757d; margin-bottom: 2px; }
.detail-value { margin-bottom: 15px; font-weight: 500; font-size: 1.1rem; }
.metric-box { border-radius: 10px; padding: 15px; text-align: center; }
</style>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="font-weight-bold" style="color: var(--accent-gold);"><i class="fas fa-warehouse mr-2"></i> Blood Bank Profile: <?php echo $bankName; ?></h1>
        <a href="admin_dashboard.php" class="btn btn-secondary">
             <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
        </a>
    </div>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="metric-box bg-light" style="border-left: 4px solid var(--primary-red);">
                <p class="detail-label">Total Inventory Stock</p>
                <p class="display-4 font-weight-bold" style="color: var(--primary-red);"><?php echo $totalInventory; ?></p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="metric-box bg-light" style="border-left: 4px solid var(--primary-blue);">
                <p class="detail-label">Scheduled Camps</p>
                <p class="display-4 font-weight-bold" style="color: var(--primary-blue);"><?php echo $campMetrics['scheduled_camps'] ?? 0; ?></p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="metric-box bg-light" style="border-left: 4px solid var(--accent-gold);">
                <p class="detail-label">Completed Drives</p>
                <p class="display-4 font-weight-bold" style="color: var(--accent-gold);"><?php echo $campMetrics['completed_camps'] ?? 0; ?></p>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card p-4 profile-card h-100">
                <h4 class="mb-4" style="color: var(--primary-blue);">Institution Details</h4>
                
                <p class="detail-label">Bank Name:</p>
                <p class="detail-value"><?php echo $bankName; ?></p>

                <p class="detail-label">License Number:</p>
                <p class="detail-value"><?php echo htmlspecialchars($bankProfile['license_number']); ?></p>

                <p class="detail-label">Primary Contact Person:</p>
                <p class="detail-value"><?php echo htmlspecialchars($bankProfile['contact_person'] ?? 'N/A'); ?></p>

                <p class="detail-label">Contact Phone:</p>
                <p class="detail-value"><?php echo htmlspecialchars($bankProfile['phone_number'] ?? 'N/A'); ?></p>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card p-4 profile-card h-100" style="border-left: 5px solid var(--dark-text);">
                <h4 class="mb-4" style="color: var(--dark-text);">Account & Address</h4>
                
                <p class="detail-label">Registered Email:</p>
                <p class="detail-value"><?php echo htmlspecialchars($bankProfile['email']); ?></p>

                <p class="detail-label">Account Status:</p>
                <p class="detail-value"><span class="badge badge-<?php echo $statusColor; ?> p-2"><?php echo ucfirst($bankProfile['user_status']); ?></span></p>

                <p class="detail-label">Address:</p>
                <p class="detail-value mb-1"><?php echo htmlspecialchars($bankProfile['address_line_1'] ?? 'N/A'); ?></p>
                <p class="detail-value mb-1"><?php echo htmlspecialchars($bankProfile['city']); ?> - <?php echo htmlspecialchars($bankProfile['pincode'] ?? 'N/A'); ?></p>
                
                <p class="detail-label">Joined System On:</p>
                <p class="detail-value"><?php echo date('M d, Y', strtotime($bankProfile['created_at'])); ?></p>
            </div>
        </div>
    </div>

    <!-- <div class="row">
        <div class="col-12 text-center">
            <a href="#" class="btn btn-lg btn-warning text-dark">
                <i class="fas fa-lock-open mr-2"></i> Suspend/Activate Account
            </a>
        </div>
    </div> -->
</div>

<?php 
include '../templates/dashboard_footer.php'; 
?>