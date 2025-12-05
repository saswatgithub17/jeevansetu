<?php
// views/user_dashboards/admin_bank_list.php (Full List View)

include '../templates/dashboard_header.php'; 

// Security Check: Only Admin can access this page
if ($_SESSION['user_type'] !== 'admin') {
    header("Location: " . BASE_URL . "views/public/login.php?error=unauthorized_access");
    exit;
}

// --- Fetch ALL Blood Bank Data ---
$sql = "SELECT 
    b.bank_id, b.bank_name, b.license_number, b.city, 
    u.email, u.status as user_status
FROM blood_banks b 
JOIN users u ON b.bank_id = u.user_id 
ORDER BY b.bank_name ASC";

$bankList = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
$conn->close();
?>

<style>
/* Specific styles for the Blood Bank List page */
.table-header-admin {
    background-color: var(--accent-gold);
    color: var(--dark-text);
}
.status-pending { background-color: #f7a9a9; color: var(--primary-red); font-weight: bold; }
.status-verified { background-color: #d4edda; color: #155724; }
</style>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="font-weight-bold" style="color: var(--accent-gold);"><i class="fas fa-warehouse mr-2"></i> Blood Bank Management Directory</h1>
        <a href="admin_dashboard.php" class="btn btn-secondary">
             <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
        </a>
    </div>

    <div class="card themed-card p-0">
        <div class="card-header table-header-admin">
            <h4 class="mb-0 text-white">Blood Banks Registered: <?php echo count($bankList); ?></h4>
        </div>
        
        <div class="card-body">
            <p class="text-muted small">Manage operational status and verify institutional credentials.</p>
            
            <?php if (!empty($bankList)): ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover table-sm">
                    <thead>
                        <tr>
                            <th>Bank Name</th>
                            <th>License No.</th>
                            <th>City</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bankList as $bank): ?>
                        <?php
                            // Simulate Verification Status: All are verified by default here
                            $verification_status = 'Verified';
                            $status_class = 'status-verified';
                            $user_status = $bank['user_status']; // active/inactive
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($bank['bank_name']); ?></td>
                            <td><?php echo htmlspecialchars($bank['license_number']); ?></td>
                            <td><?php echo htmlspecialchars($bank['city']); ?></td>
                            <td><?php echo htmlspecialchars($bank['email']); ?></td>
                            <td><span class="badge badge-pill <?php echo $status_class; ?>"><?php echo $verification_status; ?></span></td>
                            <td>
                                <a href="admin_bank_profile.php?bank_id=<?php echo $bank['bank_id']; ?>" class="btn btn-sm btn-outline-info">
                                    View Details
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
                <div class="alert alert-warning text-center mb-0">
                    No blood bank records found in the database.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php 
include '../templates/dashboard_footer.php'; 
?>