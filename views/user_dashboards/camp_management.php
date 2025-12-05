<?php
// views/user_dashboards/camp_management.php

// Includes the security check, session start, and config
include '../templates/dashboard_header.php'; 

$user_id = $_SESSION['user_id'];
$campList = [];
$message = $_GET['msg'] ?? '';
$status_type = $_GET['status'] ?? '';

// --- Fetch ALL Camps for this Bank ---
$sql = "SELECT camp_id, location_name, camp_date, target_units, status FROM camps WHERE bank_id = ? ORDER BY camp_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$campList = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();

?>

<style>
.camp-status-badge { padding: 0.5em 0.8em; }
.card-header-main { background-color: var(--primary-blue); color: white; }
.table-actions button { margin-right: 5px; }
</style>

<div class="container py-5">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="font-weight-bold" style="color: var(--primary-red);"><i class="fas fa-calendar-check mr-2"></i> Camp Management Overview</h1>
        <a href="bank_dashboard.php" class="btn btn-secondary">
             <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
        </a>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?php echo ($status_type == 'success' ? 'success' : 'danger'); ?> text-center mb-4" role="alert">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div class="card themed-card">
        <div class="card-header card-header-main">
            <h4 class="mb-0 text-white">All Scheduled & Past Drives</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <?php if (!empty($campList)): ?>
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Location</th>
                            <th>Date</th>
                            <th>Target Units</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($campList as $camp): ?>
                            <?php 
                                $statusClass = '';
                                switch ($camp['status']) {
                                    case 'Scheduled': $statusClass = 'badge-info'; break;
                                    case 'Completed': $statusClass = 'badge-success'; break;
                                    case 'Cancelled': $statusClass = 'badge-danger'; break;
                                }
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($camp['location_name']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($camp['camp_date'])); ?></td>
                                <td><?php echo $camp['target_units']; ?></td>
                                <td><span class="badge camp-status-badge <?php echo $statusClass; ?>"><?php echo $camp['status']; ?></span></td>
                                <td class="table-actions">
                                    <button class="btn btn-sm btn-primary edit-camp-btn" 
                                            data-toggle="modal" 
                                            data-target="#editCampModal"
                                            data-id="<?php echo $camp['camp_id']; ?>"
                                            data-location="<?php echo htmlspecialchars($camp['location_name']); ?>"
                                            data-date="<?php echo $camp['camp_date']; ?>"
                                            data-units="<?php echo $camp['target_units']; ?>"
                                            data-status="<?php echo $camp['status']; ?>"
                                            title="Edit Camp Details">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    
                                    <form action="../../handlers/camp_actions.php" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this camp?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="camp_id" value="<?php echo $camp['camp_id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete Camp">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <div class="alert alert-warning text-center mb-0">
                        No camps found for this Blood Bank. Use the dashboard to schedule your first drive!
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editCampModal" tabindex="-1" role="dialog" aria-labelledby="editCampModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background-color: var(--primary-blue); color: white;">
                <h5 class="modal-title" id="editCampModalLabel">Edit Camp Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="../../handlers/camp_actions.php" method="POST" id="editCampForm">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="camp_id" id="edit-camp-id">
                    
                    <div class="form-group">
                        <label for="edit-location-name">Location Name</label>
                        <input type="text" class="form-control" id="edit-location-name" name="location_name" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-camp-date">Date</label>
                        <input type="date" class="form-control" id="edit-camp-date" name="camp_date" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-target-units">Target Units</label>
                        <input type="number" class="form-control" id="edit-target-units" name="target_units" min="0" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-status">Status</label>
                        <select class="form-control" id="edit-status" name="status" required>
                            <option value="Scheduled">Scheduled</option>
                            <option value="Completed">Completed</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const editButtons = document.querySelectorAll('.edit-camp-btn');
    const modal = document.getElementById('editCampModal');
    
    // Function to populate the modal fields with the current camp data
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Retrieve data attributes from the clicked button
            const id = this.getAttribute('data-id');
            const location = this.getAttribute('data-location');
            const date = this.getAttribute('data-date');
            const units = this.getAttribute('data-units');
            const status = this.getAttribute('data-status');
            
            // Populate the modal form fields
            document.getElementById('edit-camp-id').value = id;
            document.getElementById('edit-location-name').value = location;
            document.getElementById('edit-camp-date').value = date;
            document.getElementById('edit-target-units').value = units;
            document.getElementById('edit-status').value = status;
        });
    });
});
</script>

<?php 
// Close connection and include dashboard footer
include '../templates/dashboard_footer.php'; 
?>