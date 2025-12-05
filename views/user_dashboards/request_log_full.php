<?php
// views/user_dashboards/request_log_full.php

include '../templates/dashboard_header.php'; 

$user_id = $_SESSION['user_id'];
$requestLogData = [];

// --- Fetch ALL Requests for this Hospital ---
$sql = "SELECT request_id, requested_group, units_needed, urgency_level, request_time, status, fulfilled_by_bank_id
        FROM requests 
        WHERE hospital_id = ? 
        ORDER BY request_time DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$requestLogData = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// --- Fetch Hospital Name for Display ---
$hospital_name = '';
$sql_name = "SELECT hospital_name FROM hospitals WHERE hospital_id = ?";
$stmt_name = $conn->prepare($sql_name);
$stmt_name->bind_param("i", $user_id);
$stmt_name->execute();
$hospital_name = $stmt_name->get_result()->fetch_assoc()['hospital_name'] ?? 'Your Hospital';
$stmt_name->close();
$conn->close();

?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="font-weight-bold" style="color: var(--primary-red);"><i class="fas fa-list-alt mr-2"></i> Full Blood Request Log</h1>
        <a href="hospital_dashboard.php" class="btn btn-secondary">
             <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
        </a>
    </div>

    <div class="card themed-card p-4">
        <h5 class="mb-4 text-muted">Complete history of requests submitted by <?php echo htmlspecialchars($hospital_name); ?>.</h5>
        
        <?php if (!empty($requestLogData)): ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover table-sm">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Group</th>
                        <th>Units</th>
                        <th>Urgency</th>
                        <th>Requested On</th>
                        <th>Status</th>
                        <th>Fulfilled By</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($requestLogData as $request): 
                        $statusClass = 'badge-secondary';
                        if ($request['status'] === 'Pending') $statusClass = 'badge-warning';
                        if ($request['status'] === 'In Progress') $statusClass = 'badge-info';
                        if ($request['status'] === 'Fulfilled') $statusClass = 'badge-success';
                        
                        $bank_id = $request['fulfilled_by_bank_id'];
                        $bank_info = $bank_id ? "Bank ID: $bank_id" : 'N/A';
                    ?>
                    <tr>
                        <td><?php echo $request['request_id']; ?></td>
                        <td><span class="badge badge-dark"><?php echo htmlspecialchars($request['requested_group']); ?></span></td>
                        <td><?php echo $request['units_needed']; ?></td>
                        <td class="<?php echo $request['urgency_level'] === 'Critical' ? 'text-danger' : ''; ?>"><?php echo htmlspecialchars($request['urgency_level']); ?></td>
                        <td><?php echo date('M d, Y H:i', strtotime($request['request_time'])); ?></td>
                        <td><span class="badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars($request['status']); ?></span></td>
                        <td><?php echo $bank_info; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <div class="alert alert-info text-center mb-0">
                You have not submitted any blood requests yet.
            </div>
        <?php endif; ?>
    </div>
</div>

<?php 
include '../templates/dashboard_footer.php'; 
?>