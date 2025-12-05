<?php
// views/user_dashboards/admin_donor_list.php

include '../templates/dashboard_header.php'; 

// Security Check: Only Admin can access this page
if ($_SESSION['user_type'] !== 'admin') {
    header("Location: " . BASE_URL . "views/public/login.php?error=unauthorized_access");
    exit;
}

// --- Fetch ALL Donor Data ---
// Joining 'donors' and 'users' tables to get both personal details and login email/user_id
$sql = "SELECT 
    d.donor_id, d.full_name, d.blood_group, d.city, d.last_donation_date, d.organ_pledge_status, 
    u.user_id, u.email
FROM donors d 
JOIN users u ON d.donor_id = u.user_id 
ORDER BY d.full_name ASC";

$donorList = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
$conn->close();
?>

<style>
/* Specific styles for the Admin Donor List */
.table-header-admin {
    background-color: var(--primary-blue);
    color: white;
}
.profile-link-btn {
    transition: background-color 0.3s ease;
}
</style>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="font-weight-bold" style="color: var(--primary-red);"><i class="fas fa-users mr-2"></i> Registered Donor Directory</h1>
        <a href="admin_dashboard.php" class="btn btn-secondary">
             <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
        </a>
    </div>

    <div class="card themed-card p-0">
        <div class="card-header table-header-admin">
            <h4 class="mb-0 text-white">Total Donors Registered: <?php echo count($donorList); ?></h4>
        </div>
        
        <div class="card-body">
            <?php if (!empty($donorList)): ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover table-sm">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Group</th>
                            <th>City</th>
                            <th>Last Donated</th>
                            <th>Organ Pledge</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($donorList as $donor): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($donor['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($donor['email']); ?></td>
                            <td><span class="badge badge-danger"><?php echo htmlspecialchars($donor['blood_group']); ?></span></td>
                            <td><?php echo htmlspecialchars($donor['city'] ?? 'N/A'); ?></td>
                            <td><?php echo $donor['last_donation_date'] ? date('M d, Y', strtotime($donor['last_donation_date'])) : 'N/A'; ?></td>
                            <td><?php echo htmlspecialchars($donor['organ_pledge_status']); ?></td>
                            <td>
                                <a href="admin_donor_profile.php?donor_id=<?php echo $donor['user_id']; ?>" class="btn btn-sm btn-outline-info profile-link-btn">
                                    View Profile
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
                <div class="alert alert-warning text-center mb-0">
                    No donor records found in the database.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php 
include '../templates/dashboard_footer.php'; 
?>