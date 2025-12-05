<?php
// views/user_dashboards/admin_dashboard.php

// Includes the security check, session start, and config
include '../templates/dashboard_header.php'; 

// Security check: Ensure the user is logged in AND is an 'admin'
if ($_SESSION['user_type'] !== 'admin') {
    header("Location: " . BASE_URL . "views/public/login.php?error=unauthorized_access");
    exit;
}

$adminName = 'System Administrator'; 
$totalDonors = 0;
$totalHospitals = 0;
$totalBloodBanks = 0;
$criticalThreshold = 10; // Use actual threshold for alerts
$criticalStockAlerts = 0; 

// --- Data Fetching: Centralized Metrics ---

// 1. Fetch User Counts
$sql_counts = "SELECT user_type, COUNT(user_id) as count FROM users WHERE user_type IN ('donor', 'hospital', 'blood_bank') GROUP BY user_type";
$result_counts = $conn->query($sql_counts);

if ($result_counts) {
    while ($row = $result_counts->fetch_assoc()) {
        if ($row['user_type'] == 'donor') {
            $totalDonors = $row['count'];
        } elseif ($row['user_type'] == 'hospital') {
            $totalHospitals = $row['count'];
        } elseif ($row['user_type'] == 'blood_bank') {
            $totalBloodBanks = $row['count'];
        }
    }
}

// 2. Fetch LIVE Critical Stock Alerts (Updated to use DB)
// NOTE: This logic ensures it's dynamic based on inventory.
$sql_alerts = "SELECT COUNT(DISTINCT blood_group) as alert_count FROM inventory GROUP BY bank_id HAVING SUM(units_available) <= ?";
$stmt_alerts = $conn->prepare($sql_alerts);
$stmt_alerts->bind_param("i", $criticalThreshold);
$stmt_alerts->execute();
$alert_result = $stmt_alerts->get_result()->fetch_assoc();
$criticalStockAlerts = $alert_result['alert_count'] ?? 0;
$stmt_alerts->close();


// 3. Data for Graph (Simulated Data Structure for Chart.js)
$graphDataLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];
$graphDataValues = [250, 400, 320, 500, 450, 600]; 

// Close connection before including footer
$conn->close();
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js"></script>


<style>
/* Admin Dashboard Styles (ORIGINAL CODE STYLES) */
.dashboard-header {
    background-color: var(--dark-text); /* Dark/Neutral for Admin authority */
    color: white;
    padding: 60px 0;
    margin-bottom: 30px;
    border-radius: 0 0 30px 30px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.4);
}

.admin-stat-card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease;
    padding: 30px;
    text-align: center;
}
.admin-stat-card:hover {
    transform: translateY(-5px);
}

.stat-value {
    font-size: 3.5rem;
    font-weight: 900;
}
.donor-text { /* Assuming this class is defined in main.css for color */
    color: var(--primary-red);
}
.hospital-text { /* Assuming this class is defined in main.css for color */
    color: var(--primary-blue);
}
</style>

<header class="dashboard-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="display-4 font-weight-bold">
                    <i class="fas fa-user-shield mr-3" style="color: var(--accent-gold);"></i> System Administration Panel
                </h1>
                <p class="lead">Welcome, <?php echo $adminName; ?>. Centralized oversight of the JeevanSetu network.</p>
            </div>
            <div class="col-md-4 text-right">
                <p class="mb-0 font-weight-bold">Role: <span class="badge badge-warning p-2">Super Admin</span></p>
            </div>
        </div>
    </div>
</header>

<div class="container">
    
    <div class="row mb-5">
        
        <div class="col-md-3">
            <div class="admin-stat-card bg-white" style="border-left: 5px solid var(--primary-red);">
                <p class="text-muted mb-1">Registered Donors</p>
                <div class="stat-value donor-text"><?php echo $totalDonors; ?></div>
                <small class="text-secondary">Individuals Ready to Help</small>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="admin-stat-card bg-white" style="border-left: 5px solid var(--primary-blue);">
                <p class="text-muted mb-1">Hospitals Connected</p>
                <div class="stat-value hospital-text"><?php echo $totalHospitals; ?></div>
                <small class="text-secondary">Requesting Institutions</small>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="admin-stat-card bg-white" style="border-left: 5px solid var(--accent-gold);">
                <p class="text-muted mb-1">Blood Banks Active</p>
                <div class="stat-value" style="color: var(--accent-gold);"><?php echo $totalBloodBanks; ?></div>
                <small class="text-secondary">Inventory Managers</small>
            </div>
        </div>

        <div class="col-md-3">
            <div class="admin-stat-card bg-white" style="border-left: 5px solid #dc3545;">
                <p class="text-muted mb-1">Critical Stock Alerts</p>
                <div class="stat-value text-danger"><?php echo $criticalStockAlerts; ?></div>
                <small class="text-secondary">Blood Groups < <?php echo $criticalThreshold; ?> Units</small>
            </div>
        </div>
    </div>
    
    <div class="row mb-5">
        <div class="col-lg-12">
            <div class="themed-card p-4 bg-white">
                <h3 class="font-weight-bold" style="color: var(--primary-blue);"><i class="fas fa-users-cog mr-2"></i> User and Institution Management</h3>
                <p class="text-muted">Review, verify, and manage all user accounts across the platform.</p>
                
                <ul class="nav nav-tabs mt-4" id="userTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="donor-tab" data-toggle="tab" href="#donor-list" role="tab">Donors</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="hospital-tab" data-toggle="tab" href="#hospital-list" role="tab">Hospitals</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="bank-tab" data-toggle="tab" href="#bank-list" role="tab">Blood Banks</a>
                    </li>
                </ul>

                <div class="tab-content pt-3">
                    <div class="tab-pane fade show active" id="donor-list" role="tabpanel">
                        <p class="lead">Listing the latest 5 registered donors:</p>
                        <table class="table table-striped table-sm">
                            <thead><tr><th>Name</th><th>Blood Group</th><th>Pledge Status</th><th>Joined</th><th>Action</th></tr></thead>
                            <tbody>
                                <tr><td>Priya Sharma</td><td>O+</td><td class="text-success">Pledged</td><td>2025-09-01</td><td><a href="#">View/Edit</a></td></tr>
                                <tr><td>Rahul Verma</td><td>A-</td><td class="text-muted">Not Pledged</td><td>2025-08-15</td><td><a href="#">View/Edit</a></td></tr>
                                <tr><td>Sneha Jain</td><td>O-</td><td class="text-primary">Registered</td><td>2025-10-05</td><td><a href="#">View/Edit</a></td></tr>
                                </tbody>
                        </table>
                        <a href="<?php echo BASE_URL; ?>views/user_dashboards/admin_donor_list.php" class="btn btn-sm btn-outline-secondary">View All Donors</a>
                    </div>
                    
                    <div class="tab-pane fade" id="hospital-list" role="tabpanel">
                        <p class="lead">Review pending hospital license verifications.</p>
                        <a href="<?php echo BASE_URL; ?>views/user_dashboards/admin_hospital_list.php" class="btn btn-sm btn-primary">Go to Hospital Verification Queue</a>
                    </div>
                    
                    <div class="tab-pane fade" id="bank-list" role="tabpanel">
                        <p class="lead">Manage Blood Bank access and credentials.</p>
                        <a href="<?php echo BASE_URL; ?>views/user_dashboards/admin_bank_list.php" class="btn btn-sm btn-primary">Go to Blood Bank Management</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mb-5">
        <div class="col-lg-12">
             <div class="themed-card p-4 bg-white">
                <h3 class="font-weight-bold" style="color: var(--primary-red);"><i class="fas fa-chart-bar mr-2"></i> System Analytics & Health</h3>
                <p class="text-muted">Monitor key system performance indicators and traffic.</p>
                                <div style="height: 250px;">
                    <canvas id="monthlyRequestChart"></canvas>
                </div>
                                <a href="<?php echo BASE_URL; ?>views/user_dashboards/admin_system_logs.php" class="btn btn-sm btn-outline-danger mt-3">View Detailed System Logs</a>
            </div>
        </div>
    </div>
    
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Graph Integration (using Chart.js and simulated data)

    const ctx = document.getElementById('monthlyRequestChart').getContext('2d');
    
    // PHP data injection 
    const labels = <?php echo json_encode($graphDataLabels); ?>;
    const data = <?php echo json_encode($graphDataValues); ?>;
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Total Units Requested',
                data: data,
                backgroundColor: 'rgba(26, 134, 255, 0.2)', 
                borderColor: 'rgba(26, 134, 255, 1)',
                borderWidth: 3,
                pointBackgroundColor: 'rgba(217, 35, 45, 1)', 
                pointRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true,
                        callback: function(value) { return value + ' Units'; }
                    }
                }]
            },
            legend: {
                display: false
            },
            title: {
                display: true,
                text: 'Monthly Request Volume (Last 6 Months)'
            }
        }
    });
});
</script>

<?php 
// Final close and footer include
include '../templates/dashboard_footer.php';
?>