<?php
// views/user_dashboards/camp_booking_system.php

include '../templates/dashboard_header.php'; 

$user_id = $_SESSION['user_id'];
$userCity = 'Mumbai'; 

// --- Fetch User City ---
$sql_city = "SELECT city FROM donors WHERE donor_id = ?";
$stmt_city = $conn->prepare($sql_city);
$stmt_city->bind_param("i", $user_id);
$stmt_city->execute();
$userCity = $stmt_city->get_result()->fetch_assoc()['city'] ?? 'Mumbai';
$stmt_city->close();

// --- Fetch Urgent Needs in User's City ---
$urgentNeeds = [];
$needsSql = "SELECT requested_group, SUM(units_needed) AS total_units, urgency_level
             FROM requests r 
             JOIN hospitals h ON r.hospital_id = h.hospital_id
             WHERE h.city = ? AND r.status = 'Pending'
             GROUP BY requested_group, urgency_level
             ORDER BY FIELD(urgency_level, 'Critical', 'Urgent') DESC";
$stmt_needs = $conn->prepare($needsSql);
$stmt_needs->bind_param("s", $userCity);
$stmt_needs->execute();
$urgentNeeds = $stmt_needs->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_needs->close();

// --- Fetch Upcoming Camps Near User's City ---
$campList = [];
$campsSql = "SELECT b.bank_name, c.location_name, c.camp_date, c.camp_id 
             FROM camps c
             JOIN blood_banks b ON c.bank_id = b.bank_id
             WHERE c.status = 'Scheduled' AND b.city = ?
             ORDER BY c.camp_date ASC";
$stmt_camps = $conn->prepare($campsSql);
$stmt_camps->bind_param("s", $userCity);
$stmt_camps->execute();
$campList = $stmt_camps->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_camps->close();

$conn->close();

// Handle status messages
$message = $_GET['msg'] ?? '';
$status_type = $_GET['status'] ?? '';
?>

<style>
/* Specific styles for the Booking Page */
.urgent-need-box {
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
}
.urgent-need-box:hover {
    transform: translateY(-3px);
}
.urgent-need-critical { background: var(--primary-red); color: white; border-color: var(--primary-red); }
.eligibility-tab-content { max-height: 200px; overflow-y: auto; font-size: 0.9rem; }
</style>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="font-weight-bold" style="color: var(--primary-blue);"><i class="fas fa-map-marker-alt mr-2"></i> Find & Book Donation Opportunities</h1>
        <a href="donor_dashboard.php" class="btn btn-secondary">
             <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
        </a>
    </div>
    
    <?php if ($message): ?>
        <div class="alert alert-<?php echo ($status_type == 'success' ? 'success' : 'danger'); ?> text-center mb-4" role="alert">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div class="card themed-card p-4 mb-5" style="border-left: 5px solid var(--primary-red);">
        <h4 style="color: var(--primary-red);"><i class="fas fa-exclamation-triangle mr-2"></i> Urgent Needs in Your Area (<?php echo $userCity; ?>)</h4>
        
        <?php if (!empty($urgentNeeds)): ?>
        <p class="text-muted small">The highest demand for blood components currently posted by local hospitals.</p>
        <div class="row mt-3">
            <?php foreach ($urgentNeeds as $need): 
                $boxClass = $need['urgency_level'] == 'Critical' ? 'urgent-need-critical' : 'bg-warning';
            ?>
                <div class="col-md-4 mb-3">
                    <div class="p-3 rounded urgent-need-box <?php echo $boxClass; ?>">
                        <p class="mb-1 font-weight-bold"><?php echo $need['urgency_level']; ?> Need</p>
                        <h5 class="mb-0"><?php echo $need['requested_group']; ?> - <?php echo $need['total_units']; ?> Units</h5>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
            <div class="alert alert-success text-center mb-0">
                No critical requests found in your area right now. Good supply!
            </div>
        <?php endif; ?>
    </div>

    <div class="card themed-card p-4">
        <h4 style="color: var(--primary-blue);"><i class="fas fa-calendar-alt mr-2"></i> Scheduled Donation Camps Near You</h4>
        
        <?php if (!empty($campList)): ?>
        <p class="text-muted small">Select an upcoming drive to reserve your spot.</p>
        <div class="table-responsive">
            <table class="table table-striped table-hover table-sm">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Location</th>
                        <th>Organized By</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($campList as $camp): ?>
                    <tr>
                        <td><?php echo date('M d, Y', strtotime($camp['camp_date'])); ?></td>
                        <td><?php echo htmlspecialchars($camp['location_name']); ?></td>
                        <td><?php echo htmlspecialchars($camp['bank_name']); ?></td>
                        <td>
                            <button class="btn btn-sm btn-success book-slot-btn" 
                                    data-toggle="modal" 
                                    data-target="#bookingModal"
                                    data-camp-id="<?php echo $camp['camp_id']; ?>"
                                    data-camp-location="<?php echo htmlspecialchars($camp['location_name']); ?>"
                                    data-camp-date="<?php echo $camp['camp_date']; ?>">
                                Book Slot
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <div class="alert alert-warning text-center mb-0">
                No upcoming donation camps scheduled in your city. Check back soon!
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="bookingModal" tabindex="-1" role="dialog" aria-labelledby="bookingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background-color: var(--primary-red); color: white;">
                <h5 class="modal-title" id="bookingModalLabel">Confirm Your Donation Slot</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="../../handlers/booking_process.php" method="POST" id="bookingForm">
                <div class="modal-body">
                    
                    <h5 class="text-primary">Drive Details:</h5>
                    <p>You are booking a slot for the drive at: <span id="modal-location" class="font-weight-bold"></span></p>
                    <p class="small text-muted">On: <span id="modal-date" class="font-weight-bold"></span></p>

                    <input type="hidden" name="camp_id" id="modal-camp-id">
                    <input type="hidden" name="appointment_date" id="modal-appointment-date">
                    
                    <hr>
                    <h5 class="text-danger">Pre-Donation Eligibility Criteria:</h5>
                    <ul class="nav nav-tabs" id="eligibilityTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="blood-tab" data-toggle="tab" href="#blood-eligibility" role="tab" aria-controls="blood" aria-selected="true">Blood Donation</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="organ-tab" data-toggle="tab" href="#organ-eligibility" role="tab" aria-controls="organ" aria-selected="false">Organ Pledge</a>
                        </li>
                    </ul>
                    
                    <div class="tab-content border border-top-0 p-3 eligibility-tab-content">
                        <div class="tab-pane fade show active" id="blood-eligibility" role="tabpanel" aria-labelledby="blood-tab">
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check-circle text-success mr-2"></i> Age: Must be between 18 and 65 years.</li>
                                <li><i class="fas fa-check-circle text-success mr-2"></i> Weight: Minimum weight of 50 kg.</li>
                                <li><i class="fas fa-times-circle text-danger mr-2"></i> Interval: Must have completed 90 days since your last donation.</li>
                                <li><i class="fas fa-times-circle text-danger mr-2"></i> Health: Must be free from cold, fever, infection, or major dental procedures in the last 72 hours.</li>
                                <li><i class="fas fa-times-circle text-danger mr-2"></i> Travel/Risk: Cannot donate if recently received a tattoo/piercing (last 6 months) or engaged in high-risk behavior.</li>
                            </ul>
                        </div>
                        <div class="tab-pane fade" id="organ-eligibility" role="tabpanel" aria-labelledby="organ-tab">
                            <ul class="list-unstyled">
                                <li><i class="fas fa-heartbeat text-success mr-2"></i> Intention: Organs are collected only after death and official declaration of brain death.</li>
                                <li><i class="fas fa-user-shield text-success mr-2"></i> Legal: Registration confirms your willingness; final decision is governed by national law and medical assessment.</li>
                                <li><i class="fas fa-info-circle text-info mr-2"></i> Exclusions: Age/pre-existing conditions do not automatically exclude you; viability is determined by transplant specialists post-mortem.</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="form-group mt-3">
                        <label for="modal-time">Select Preferred Time Slot (Optional)</label>
                        <select class="form-control" id="modal-time" name="appointment_time">
                            <option value="">Any Time Slot</option>
                            <option value="09:00:00">9:00 AM - 10:00 AM</option>
                            <option value="10:00:00">10:00 AM - 11:00 AM</option>
                            <option value="11:00:00">11:00 AM - 12:00 PM</option>
                            <option value="13:00:00">1:00 PM - 2:00 PM</option>
                            <option value="14:00:00">2:00 PM - 3:00 PM</option>
                        </select>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Confirm Slot Booking</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const bookButtons = document.querySelectorAll('.book-slot-btn');
    
    // Function to populate the modal when a 'Book Slot' button is clicked
    bookButtons.forEach(button => {
        button.addEventListener('click', function() {
            const campId = this.getAttribute('data-camp-id');
            const location = this.getAttribute('data-camp-location');
            const date = this.getAttribute('data-camp-date');
            
            // Populate hidden fields for form submission
            document.getElementById('modal-camp-id').value = campId;
            document.getElementById('modal-appointment-date').value = date;
            
            // Populate visible fields for user confirmation
            document.getElementById('modal-location').textContent = location;
            document.getElementById('modal-date').textContent = new Date(date).toDateString();
        });
    });
});
</script>

<?php 
include '../templates/dashboard_footer.php'; 
?>