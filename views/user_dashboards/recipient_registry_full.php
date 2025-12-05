<?php
// views/user_dashboards/recipient_registry_full.php (Advanced Feature)

include '../templates/dashboard_header.php'; 

$user_id = $_SESSION['user_id'];
$recipientRegistryData = [];

// --- Fetch ALL Recipients (Active and Inactive) for this Hospital ---
$sql = "SELECT recipient_name, required_organ, blood_group, urgency_level, tissue_type_hla, waitlist_date, status
        FROM organ_recipients 
        WHERE hospital_id = ? 
        ORDER BY FIELD(status, 'Active', 'Matched', 'Transplanted') DESC, waitlist_date ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$recipientRegistryData = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
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
        <h1 class="font-weight-bold" style="color: var(--primary-blue);"><i class="fas fa-heartbeat mr-2"></i> Full Organ Recipient Registry</h1>
        <a href="hospital_dashboard.php" class="btn btn-secondary">
             <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
        </a>
    </div>

    <div class="card themed-card p-4">
        <h5 class="mb-4 text-muted">Complete list of patients managed by <?php echo htmlspecialchars($hospital_name); ?> awaiting organ transplantation.</h5>
        
        <!-- Add Recipient Button (Future Edit/Add functionality) -->
        <a href="#" class="btn btn-success mb-4" style="width: fit-content;"><i class="fas fa-plus-circle mr-2"></i> Add New Recipient</a>

        <?php if (!empty($recipientRegistryData)): ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover table-sm">
                <thead>
                    <tr>
                        <th>Patient Name</th>
                        <th>Organ</th>
                        <th>Group</th>
                        <th>HLA Type</th>
                        <th>Urgency</th>
                        <th>Wait Since</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recipientRegistryData as $recipient): 
                        $statusClass = 'badge-secondary';
                        if ($recipient['status'] === 'Active') $statusClass = 'badge-primary';
                        if ($recipient['status'] === 'Critical') $statusClass = 'badge-danger';
                        
                        $urgencyClass = '';
                        if ($recipient['urgency_level'] === 'Critical') $urgencyClass = 'text-danger font-weight-bold';
                        if ($recipient['urgency_level'] === 'Urgent') $urgencyClass = 'text-warning font-weight-bold';
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($recipient['recipient_name']); ?></td>
                        <td><?php echo htmlspecialchars($recipient['required_organ']); ?></td>
                        <td><span class="badge badge-dark"><?php echo htmlspecialchars($recipient['blood_group']); ?></span></td>
                        <td><?php echo htmlspecialchars($recipient['tissue_type_hla'] ?? 'N/A'); ?></td>
                        <td class="<?php echo $urgencyClass; ?>"><?php echo htmlspecialchars($recipient['urgency_level']); ?></td>
                        <td><?php echo date('M d, Y', strtotime($recipient['waitlist_date'])); ?></td>
                        <td><span class="badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars($recipient['status']); ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <div class="alert alert-info text-center mb-0">
                The registry is currently empty. Add your first patient to the waiting list!
            </div>
        <?php endif; ?>
    </div>
</div>

<?php 
include '../templates/dashboard_footer.php'; 
?>