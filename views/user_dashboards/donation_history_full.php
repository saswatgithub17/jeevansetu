<?php
// views/user_dashboards/donation_history_full.php

include '../templates/dashboard_header.php'; 

$user_id = $_SESSION['user_id'];

// --- Fetch Donor Name and Blood Group ---
$donorName = '';
$bloodGroup = '';
$sql_info = "SELECT full_name, blood_group FROM donors WHERE donor_id = ?";
$stmt_info = $conn->prepare($sql_info);
$stmt_info->bind_param("i", $user_id);
$stmt_info->execute();
$info = $stmt_info->get_result()->fetch_assoc();
$donorName = $info['full_name'] ?? 'Donor';
$bloodGroup = $info['blood_group'] ?? 'Unknown';
$stmt_info->close();

// Close current connection to allow the final close in the footer (good practice)
$conn->close();

// --- SIMULATED Data Fetch from hypothetical 'donations_log' table ---
$historyData = [
    // Use the actual Donor's name/group in the simulation for better feel
    ['date' => '2025-09-01', 'type' => 'Whole Blood', 'units' => 1, 'location' => 'Central Blood Bank, Mumbai', 'log_id' => 101, 'group' => $bloodGroup],
    ['date' => '2025-06-03', 'type' => 'Plasma', 'units' => 1, 'location' => 'Community Drive, Pune', 'log_id' => 102, 'group' => $bloodGroup],
    ['date' => '2025-03-01', 'type' => 'Whole Blood', 'units' => 1, 'location' => 'Apollo Multi-Specialty, Delhi', 'log_id' => 103, 'group' => $bloodGroup],
    ['date' => '2024-11-05', 'type' => 'Platelets', 'units' => 2, 'location' => 'North Zone Regional Bank', 'log_id' => 104, 'group' => $bloodGroup],
    ['date' => '2024-08-10', 'type' => 'Whole Blood', 'units' => 1, 'location' => 'City General Hospital', 'log_id' => 105, 'group' => $bloodGroup],
];
// --- END SIMULATED DATA ---
?>

<style>
/* Local style adjustments for the History Log page */
.card-header-main { background-color: var(--primary-red); color: white; }
.history-table th { background-color: #f8d7da; color: var(--primary-red); }
</style>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="font-weight-bold" style="color: var(--primary-red);"><i class="fas fa-history mr-2"></i> Full Donation History Log</h1>
        <a href="donor_dashboard.php" class="btn btn-secondary">
             <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
        </a>
    </div>

    <div class="card themed-card p-0">
        <div class="card-header card-header-main">
            <h4 class="mb-0 text-white">Contributions by <?php echo htmlspecialchars($donorName); ?></h4>
        </div>
        <div class="card-body">
            
        <h5 class="mb-4 text-muted">Your complete record (Group: <span class="badge badge-danger p-2"><?php echo $bloodGroup; ?></span>).</h5>
        
        <?php if (!empty($historyData)): ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover table-sm history-table">
                <thead>
                    <tr>
                        <th>Donation Date</th>
                        <th>Type Donated</th>
                        <th>Units (L)</th>
                        <th>Location</th>
                        <th>Certificate</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($historyData as $log): ?>
                    <tr>
                        <td><?php echo date('M d, Y', strtotime($log['date'])); ?></td>
                        <td><?php echo htmlspecialchars($log['type']); ?></td>
                        <td><?php echo $log['units']; ?></td>
                        <td><?php echo htmlspecialchars($log['location']); ?></td>
                        <td>
                            <a href="../../handlers/certificate_generate.php?log_id=<?php echo $log['log_id']; ?>&donor_name=<?php echo urlencode($donorName); ?>&date=<?php echo urlencode($log['date']); ?>" 
                               target="_blank" 
                               class="btn btn-sm btn-info">View Certificate</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <div class="alert alert-info text-center mb-0">
                No recorded donation history found. Be the first to save a life!
            </div>
        <?php endif; ?>
    </div>
    </div>
</div>

<?php 
// No database closing needed here as it was closed after data fetch
include '../templates/dashboard_footer.php'; 
?>