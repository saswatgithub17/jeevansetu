<?php
// views/user_dashboards/admin_hospital_list.php

include '../templates/dashboard_header.php'; 

// Security Check: Only Admin can access this page
if ($_SESSION['user_type'] !== 'admin') {
    header("Location: " . BASE_URL . "views/public/login.php?error=unauthorized_access");
    exit;
}

// --- Fetch ALL Hospital Data ---
$sql = "SELECT 
    h.hospital_id, h.hospital_name, h.license_number, h.city, 
    u.email, u.status as user_status
FROM hospitals h 
JOIN users u ON h.hospital_id = u.user_id 
ORDER BY h.hospital_name ASC";

$hospitalList = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
$conn->close();

// NOTE: In a production system, 'verification_status' would be a separate column (Pending, Verified).
// We simulate VERIFIED status for simplicity.
?>

<style>
/* Specific styles for the Hospital List page */
.table-header-admin {
    background-color: var(--primary-blue);
    color: white;
}
.status-pending { background-color: #f7a9a9; color: var(--primary-red); font-weight: bold; }
.status-verified { background-color: #d4edda; color: #155724; }
</style>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="font-weight-bold" style="color: var(--primary-blue);"><i class="fas fa-hospital mr-2"></i> Hospital Verification Queue</h1>
        <a href="admin_dashboard.php" class="btn btn-secondary">
             <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
        </a>
    </div>

    <div class="card themed-card p-0">
        <div class="card-header table-header-admin">
            <h4 class="mb-0 text-white">Hospitals Registered: <?php echo count($hospitalList); ?></h4>
        </div>
        
        <div class="card-body">
            <p class="text-muted small">All hospitals require license verification before full system privileges are granted.</p>
            
            <?php if (!empty($hospitalList)): ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover table-sm">
                    <thead>
                        <tr>
                            <th>Hospital Name</th>
                            <th>License No.</th>
                            <th>City</th>
                            <th>Email</th>
                            <th>Verification Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($hospitalList as $hospital): ?>
                        <?php
                            // Simulate Verification Status: All are verified by default here
                            $verification_status = 'Verified';
                            $status_class = 'status-verified';
                            $user_status = $hospital['user_status']; // active/inactive
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($hospital['hospital_name']); ?></td>
                            <td><?php echo htmlspecialchars($hospital['license_number']); ?></td>
                            <td><?php echo htmlspecialchars($hospital['city']); ?></td>
                            <td><?php echo htmlspecialchars($hospital['email']); ?></td>
                            <td><span class="badge badge-pill <?php echo $status_class; ?>"><?php echo $verification_status; ?></span></td>
                            <td>
                                <a href="admin_hospital_profile.php?hospital_id=<?php echo $hospital['hospital_id']; ?>" class="btn btn-sm btn-outline-primary">View Details</a>
                                <!-- In a real system, buttons to Toggle Verification status would be here -->
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
                <div class="alert alert-warning text-center mb-0">
                    No hospital records found in the database.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php 
include '../templates/dashboard_footer.php'; 
?>