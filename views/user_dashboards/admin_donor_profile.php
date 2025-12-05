<?php
// views/user_dashboards/admin_donor_profile.php

include '../templates/dashboard_header.php'; 

// Security Check: Only Admin can access this page
if ($_SESSION['user_type'] !== 'admin') {
    header("Location: " . BASE_URL . "views/public/login.php?error=unauthorized_access");
    exit;
}

$target_donor_id = intval($_GET['donor_id'] ?? 0);

if ($target_donor_id === 0) {
    die('<div class="container py-5"><div class="alert alert-danger">Error: No Donor ID specified.</div><a href="admin_donor_list.php">Back to Donor List</a></div>');
}

// --- Data Fetching ---

// 1. Fetch User & Donor Data (Combined)
$sql_donor_data = "SELECT 
    d.full_name, d.blood_group, d.city, d.date_of_birth, d.last_donation_date, d.organ_pledge_status, d.is_available,
    u.email, u.status as user_status, u.created_at
FROM donors d
JOIN users u ON d.donor_id = u.user_id
WHERE d.donor_id = ?";

$stmt = $conn->prepare($sql_donor_data);
$stmt->bind_param("i", $target_donor_id);
$stmt->execute();
$donorProfile = $stmt->get_result()->fetch_assoc();
$stmt->close();

// 2. Fetch Recent Appointments (Simulated: Requires 'appointments' table)
$sql_appointments = "SELECT appointment_date, status, camp_id, bank_id FROM appointments WHERE donor_id = ? ORDER BY appointment_date DESC LIMIT 3";
$stmt_app = $conn->prepare($sql_appointments);
$stmt_app->bind_param("i", $target_donor_id);
$stmt_app->execute();
$appointments = $stmt_app->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_app->close();

$conn->close();

if (!$donorProfile) {
    die('<div class="container py-5"><div class="alert alert-warning">Donor profile not found.</div><a href="admin_donor_list.php">Back to Donor List</a></div>');
}

$donorName = htmlspecialchars($donorProfile['full_name']);
$age = date_diff(date_create($donorProfile['date_of_birth']), date_create('today'))->y;

?>

<style>
.profile-card { border-left: 5px solid var(--primary-blue); border-radius: 10px; }
.detail-label { font-weight: 600; color: #6c757d; }
.app-attended { background-color: #d4edda; color: #155724; }
.app-booked { background-color: #fff3cd; color: #856404; }
</style>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="font-weight-bold" style="color: var(--primary-red);"><i class="fas fa-user-circle mr-2"></i> Donor Profile: <?php echo $donorName; ?></h1>
        <a href="admin_donor_list.php" class="btn btn-secondary">
             <i class="fas fa-arrow-left mr-2"></i> Back to List
        </a>
    </div>

    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card p-4 profile-card h-100">
                <h4 class="mb-4" style="color: var(--primary-blue);">Personal & Account Info</h4>
                <p class="detail-label">Email Address:</p>
                <p class="lead"><?php echo htmlspecialchars($donorProfile['email']); ?></p>

                <p class="detail-label">Donor Status:</p>
                <p><span class="badge badge-<?php echo $donorProfile['user_status'] === 'active' ? 'success' : 'danger'; ?> p-2">
                    <?php echo ucfirst($donorProfile['user_status']); ?>
                </span></p>

                <p class="detail-label">Joined Date:</p>
                <p><?php echo date('M d, Y', strtotime($donorProfile['created_at'])); ?></p>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card p-4 profile-card h-100" style="border-left: 5px solid var(--primary-red);">
                <h4 class="mb-4" style="color: var(--primary-red);">Donation & Eligibility</h4>
                
                <p class="detail-label">Blood Group:</p>
                <p class="display-4 font-weight-bold" style="color: var(--primary-red);"><?php echo htmlspecialchars($donorProfile['blood_group']); ?></p>
                
                <p class="detail-label">Age / DOB:</p>
                <p><?php echo $age; ?> years old (<?php echo date('M d, Y', strtotime($donorProfile['date_of_birth'])); ?>)</p>

                <p class="detail-label">Organ Pledge Status:</p>
                <p><span class="badge badge-warning p-2"><?php echo htmlspecialchars($donorProfile['organ_pledge_status']); ?></span></p>
                
                <p class="detail-label">Last Donation:</p>
                <p><?php echo $donorProfile['last_donation_date'] ? date('M d, Y', strtotime($donorProfile['last_donation_date'])) : 'Never'; ?></p>

                <p class="detail-label">Emergency Availability:</p>
                <p><span class="badge badge-<?php echo $donorProfile['is_available'] ? 'success' : 'secondary'; ?> p-2">
                    <?php echo $donorProfile['is_available'] ? 'AVAILABLE' : 'OFFLINE/UNAVAILABLE'; ?>
                </span></p>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card p-4 profile-card">
                <h4 class="mb-4" style="color: var(--dark-text);">Recent Appointments</h4>
                
                <?php if (!empty($appointments)): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-sm">
                        <thead>
                            <tr><th>Date</th><th>Type</th><th>Location ID</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($appointments as $app): ?>
                            <tr class="<?php echo $app['status'] === 'Attended' ? 'app-attended' : ($app['status'] === 'Booked' ? 'app-booked' : ''); ?>">
                                <td><?php echo date('M d, Y', strtotime($app['appointment_date'])); ?></td>
                                <td><?php echo $app['camp_id'] ? 'Camp' : 'Blood Bank'; ?></td>
                                <td><?php echo $app['camp_id'] ?? $app['bank_id']; ?></td>
                                <td><span class="badge badge-pill badge-<?php echo $app['status'] === 'Attended' ? 'success' : ($app['status'] === 'Booked' ? 'info' : 'danger'); ?>"><?php echo $app['status']; ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                    <div class="alert alert-info small">No appointments found for this donor.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php 
include '../templates/dashboard_footer.php'; 
?>