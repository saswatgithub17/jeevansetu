<?php
// views/user_dashboards/admin_hospital_profile.php

include '../templates/dashboard_header.php'; 

// Security Check: Only Admin can access this page
if ($_SESSION['user_type'] !== 'admin') {
    header("Location: " . BASE_URL . "views/public/login.php?error=unauthorized_access");
    exit;
}

$target_hospital_id = intval($_GET['hospital_id'] ?? 0);

if ($target_hospital_id === 0) {
    die('<div class="container py-5"><div class="alert alert-danger">Error: No Hospital ID specified.</div><a href="admin_hospital_list.php">Back to Hospital List</a></div>');
}

// --- Data Fetching ---

// 1. Fetch Hospital & User Data (Combined)
$sql_hospital_data = "SELECT 
    h.hospital_name, h.license_number, h.contact_person, h.phone_number, h.address_line_1, h.city, h.pincode,
    u.email, u.status as user_status, u.created_at
FROM hospitals h
JOIN users u ON h.hospital_id = u.user_id
WHERE h.hospital_id = ?";

$stmt = $conn->prepare($sql_hospital_data);
$stmt->bind_param("i", $target_hospital_id);
$stmt->execute();
$hospitalProfile = $stmt->get_result()->fetch_assoc();
$stmt->close();

// 2. Fetch Aggregated Organ Recipient Data (Metrics)
$sql_metrics = "SELECT COUNT(recipient_id) AS total_recipients, 
                       SUM(CASE WHEN urgency_level = 'Critical' THEN 1 ELSE 0 END) AS critical_recipients
                FROM organ_recipients WHERE hospital_id = ?";
$stmt_metrics = $conn->prepare($sql_metrics);
$stmt_metrics->bind_param("i", $target_hospital_id);
$stmt_metrics->execute();
$metrics = $stmt_metrics->get_result()->fetch_assoc();
$stmt_metrics->close();

$conn->close();

if (!$hospitalProfile) {
    die('<div class="container py-5"><div class="alert alert-warning">Hospital profile not found.</div><a href="admin_hospital_list.php">Back to Hospital List</a></div>');
}

$hospitalName = htmlspecialchars($hospitalProfile['hospital_name']);
$statusColor = $hospitalProfile['user_status'] === 'active' ? 'success' : 'danger';
$isVerified = true; // Simulating "Verified" status from hospital_list context
?>

<style>
.profile-card { border-left: 5px solid var(--primary-blue); border-radius: 10px; }
.detail-label { font-weight: 600; color: #6c757d; margin-bottom: 2px; }
.detail-value { margin-bottom: 15px; font-weight: 500; font-size: 1.1rem; }
.metric-box { border-radius: 10px; padding: 15px; text-align: center; }
</style>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="font-weight-bold" style="color: var(--primary-blue);"><i class="fas fa-hospital mr-2"></i> Hospital Profile: <?php echo $hospitalName; ?></h1>
        <a href="admin_hospital_list.php" class="btn btn-secondary">
             <i class="fas fa-arrow-left mr-2"></i> Back to Queue
        </a>
    </div>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="metric-box bg-light" style="border-left: 4px solid var(--primary-blue);">
                <p class="detail-label">Total Recipients</p>
                <p class="display-4 font-weight-bold" style="color: var(--primary-blue);"><?php echo $metrics['total_recipients'] ?? 0; ?></p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="metric-box bg-light" style="border-left: 4px solid var(--primary-red);">
                <p class="detail-label">Critical Waitlist</p>
                <p class="display-4 font-weight-bold" style="color: var(--primary-red);"><?php echo $metrics['critical_recipients'] ?? 0; ?></p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="metric-box bg-light" style="border-left: 4px solid var(--accent-gold);">
                <p class="detail-label">Verification Status</p>
                <p class="lead font-weight-bold mt-3">
                    <span class="badge badge-lg badge-success p-2">VERIFIED</span>
                </p>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card p-4 profile-card h-100">
                <h4 class="mb-4" style="color: var(--primary-blue);">Institution Details</h4>
                
                <p class="detail-label">Hospital Name:</p>
                <p class="detail-value"><?php echo $hospitalName; ?></p>

                <p class="detail-label">License Number:</p>
                <p class="detail-value"><?php echo htmlspecialchars($hospitalProfile['license_number']); ?></p>

                <p class="detail-label">Primary Contact Person:</p>
                <p class="detail-value"><?php echo htmlspecialchars($hospitalProfile['contact_person'] ?? 'N/A'); ?></p>

                <p class="detail-label">Contact Phone:</p>
                <p class="detail-value"><?php echo htmlspecialchars($hospitalProfile['phone_number'] ?? 'N/A'); ?></p>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card p-4 profile-card h-100" style="border-left: 5px solid var(--accent-gold);">
                <h4 class="mb-4" style="color: var(--dark-text);">Account & Address</h4>
                
                <p class="detail-label">Registered Email:</p>
                <p class="detail-value"><?php echo htmlspecialchars($hospitalProfile['email']); ?></p>

                <p class="detail-label">Account Status:</p>
                <p class="detail-value"><span class="badge badge-<?php echo $statusColor; ?> p-2"><?php echo ucfirst($hospitalProfile['user_status']); ?></span></p>

                <p class="detail-label">Address:</p>
                <p class="detail-value mb-1"><?php echo htmlspecialchars($hospitalProfile['address_line_1'] ?? 'N/A'); ?></p>
                <p class="detail-value mb-1"><?php echo htmlspecialchars($hospitalProfile['city']); ?> - <?php echo htmlspecialchars($hospitalProfile['pincode'] ?? 'N/A'); ?></p>
                
                <p class="detail-label">Joined System On:</p>
                <p class="detail-value"><?php echo date('M d, Y', strtotime($hospitalProfile['created_at'])); ?></p>
            </div>
        </div>
    </div>

    <!-- <div class="row">
        <div class="col-12 text-center">
            <a href="#" class="btn btn-lg btn-warning text-dark">
                <i class="fas fa-lock-open mr-2"></i> Toggle Verification/Suspend Account
            </a>
        </div>
    </div> -->
</div>

<?php 
include '../templates/dashboard_footer.php'; 
?>