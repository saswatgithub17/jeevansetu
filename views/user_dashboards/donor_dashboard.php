<?php
// views/user_dashboards/donor_dashboard.php (FINAL EXPERT REDESIGN & MAP INTEGRATION)

// Includes the security check, session start, and config
include '../templates/dashboard_header.php'; 

$user_id = $_SESSION['user_id'];
$donorData = null;
$userStatus = 'Unknown'; 
$nearestBanks = []; // Array to store nearest blood banks

// --- Data Fetching ---

// 1.1 Fetch donor-specific data from the 'donors' table
$sql_donor = "SELECT full_name, blood_group, city, last_donation_date, organ_pledge_status, is_available 
              FROM donors 
              WHERE donor_id = ?";
$stmt_donor = $conn->prepare($sql_donor);
$stmt_donor->bind_param("i", $user_id);
$stmt_donor->execute();
$result_donor = $stmt_donor->get_result();

if ($result_donor->num_rows > 0) {
    $donorData = $result_donor->fetch_assoc();
} else {
    die("Error: Donor profile data not found.");
}
$stmt_donor->close();

// 1.2 FETCH STATUS from the 'users' table
$sql_status = "SELECT status FROM users WHERE user_id = ?";
$stmt_status = $conn->prepare($sql_status);
$stmt_status->bind_param("i", $user_id);
$stmt_status->execute();
$result_status = $stmt_status->get_result();

if ($result_status->num_rows > 0) {
    $userStatus = $result_status->fetch_assoc()['status'];
}
$stmt_status->close();


// --- Assign Dynamic Variables ---
$donorName = htmlspecialchars($donorData['full_name']);
$bloodGroup = htmlspecialchars($donorData['blood_group']);
$donorCity = htmlspecialchars($donorData['city']); // Donor's City
$lastDonation = $donorData['last_donation_date'];
$organPledge = htmlspecialchars($donorData['organ_pledge_status']);
$isAvailable = $donorData['is_available']; // 1 or 0 (dynamic toggle state)

// --- Calculate Derived Metrics (Simulated for now) ---
$totalDonations = 5; 
$livesSaved = 15;    

// Calculate Next Eligible Donation Date
$nextEligibleDate = 'N/A';
$daysRemaining = 'N/A';
$isEligible = false;

// Assuming DONATION_DAYS_GAP is defined in config.php
if ($lastDonation) {
    $lastDonationTimestamp = strtotime($lastDonation);
    $nextEligibleTimestamp = strtotime("+" . DONATION_DAYS_GAP . " days", $lastDonationTimestamp); 
    $nextEligibleDate = date('Y-m-d', $nextEligibleTimestamp);
    
    $today = time();
    if ($today < $nextEligibleTimestamp) {
        $timeDiff = $nextEligibleTimestamp - $today;
        $daysRemaining = floor($timeDiff / (60 * 60 * 24));
    } else {
        $daysRemaining = 0; 
        $isEligible = true;
    }
} else {
    $isEligible = true; // First-time donor is eligible
}

// --- GEOGRAPHICAL INTEGRATION LOGIC (NEW) ---

$donorCoords = getGeoCoordinates($donorCity);

if ($donorCoords) {
    $donorLat = $donorCoords['lat'];
    $donorLon = $donorCoords['lon'];

    // 1. Fetch all Blood Banks
    $sql_banks = "SELECT bank_id, bank_name, city FROM blood_banks";
    $result_banks = $conn->query($sql_banks);

    if ($result_banks) {
        while ($bank = $result_banks->fetch_assoc()) {
            $bankCoords = getGeoCoordinates($bank['city']);
            
            if ($bankCoords) {
                $distance = calculateDistance($donorLat, $donorLon, $bankCoords['lat'], $bankCoords['lon']);
                
                $nearestBanks[] = [
                    'name' => $bank['bank_name'],
                    'city' => $bank['city'],
                    'distance' => $distance
                ];
            }
        }
    }
    
    // Sort by distance (find nearest 3)
    usort($nearestBanks, function($a, $b) {
        return $a['distance'] <=> $b['distance'];
    });
    $nearestBanks = array_slice($nearestBanks, 0, 3);
}

// Close connection before including footer
$conn->close(); 
?>

<style>
/* --- EXPERT REDESIGN STYLES (Donor Theme) --- */

:root {
    --primary-red: #D9232D;      /* Vibrant Blood Red */
    --primary-blue: #1A86FF;     /* Deep Trust Blue */
    --accent-gold: #FFC107;      /* Urgency/Highlight Gold */
    --light-bg: #EBEBEB;         /* Soft Background */
    --dark-text: #212529;
    --card-shadow-3d: 0 18px 40px rgba(0, 0, 0, 0.35);
}

/* 1. ANIMATED HEADER BACKGROUND (Primary Red/Life Theme) */
.dashboard-header {
    background: linear-gradient(135deg, var(--primary-red) 0%, #E04D54 100%);
    color: white;
    padding: 80px 0;
    margin-bottom: 40px;
    border-radius: 0 0 40px 40px;
    position: relative;
    overflow: hidden;
    animation: gradientShift 10s ease infinite alternate;
}
@keyframes gradientShift {
    0% { background-position: 0% 50%; }
    100% { background-position: 100% 50%; }
}
.header-icon { font-size: 3rem; color: rgba(255, 255, 255, 0.8); }

/* 2. STAT CARD: Hybrid Design */
.stat-card {
    background: #ffffff; 
    border: none;
    border-radius: 20px;
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.08), inset 0 0 0 1px rgba(255, 255, 255, 0.5); 
    transition: all 0.5s cubic-bezier(0.165, 0.84, 0.44, 1);
    padding: 35px 20px;
    text-align: center;
    position: relative;
    overflow: hidden;
    height: 100%;
}

.stat-card:hover {
    transform: translateY(-8px);
    box-shadow: var(--card-shadow-3d);
    filter: brightness(1.05); 
}

.stat-value {
    font-size: 4rem;
    font-weight: 900;
    line-height: 1;
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
}

/* 3. Action Card Headers (Blue for Trust/Secondary Actions) */
.action-card-header {
    background: linear-gradient(90deg, var(--primary-blue) 0%, #59A7FF 100%);
    padding: 1.2rem;
    border-radius: 20px 20px 0 0;
    color: white;
}
.themed-card { border-radius: 20px; }
.themed-card h3 { font-weight: 900; }

/* Status Toggle Styling */
#availability-toggle {
    color: white;
    font-weight: 700;
    border: none;
    transition: all 0.3s ease;
    min-height: 50px;
    border-radius: 10px;
}
#availability-toggle.available { background-color: #28a745; }
#availability-toggle.unavailable { background-color: #dc3545; }

/* Eligibility Card Styling */
.eligibility-card {
    border-left: 5px solid <?php echo $isEligible ? '#28a745' : '#FFC107'; ?>;
    background-color: <?php echo $isEligible ? '#e9f7e9' : '#fff9e6'; ?>;
    border-radius: 8px;
    padding: 15px;
}
.eligibility-date {
    font-weight: 700;
    color: var(--primary-red);
}
</style>

<header class="dashboard-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="display-4 font-weight-bold text-white">
                    <i class="fas fa-tint mr-3 header-icon"></i> Donor Impact Portal
                </h1>
                <p class="lead text-white-50">Welcome back, <?php echo $donorName; ?>. Your actions save lives.</p>
            </div>
            <div class="col-md-4 text-right">
                <p class="mb-0 font-weight-bold text-white">Blood Group: <span class="badge badge-light p-2"><?php echo $bloodGroup; ?></span></p>
                <p class="mb-0 font-weight-bold">Status: <span class="badge badge-success p-2"><?php echo htmlspecialchars(ucfirst($userStatus)); ?></span></p>
            </div>
        </div>
    </div>
</header>

<div class="container">
    
    <div id="status-message" class="alert d-none text-center mb-4" role="alert"></div>

    <div class="row mb-5">
        
        <div class="col-md-3">
            <div class="stat-card bg-white position-relative">
                <div class="card-icon-bg" style="color: rgba(217, 35, 45, 0.1);"><i class="fas fa-heart"></i></div>
                <p class="text-muted mb-1">Total Donations</p>
                <div class="stat-value" style="color: var(--primary-red);"><?php echo $totalDonations; ?></div>
                <small class="text-secondary">Units Contributed</small>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="stat-card bg-white position-relative">
                <div class="card-icon-bg" style="color: rgba(26, 134, 255, 0.1);"><i class="fas fa-hands-helping"></i></div>
                <p class="text-muted mb-1">Lives Estimated Saved</p>
                <div class="stat-value" style="color: var(--primary-blue);"><?php echo $livesSaved; ?></div>
                <small class="text-secondary">Measured Impact</small>
            </div>
        </div>

        <div class="col-md-6">
            <div class="stat-card h-100 d-flex flex-column justify-content-center">
                <p class="font-weight-bold mb-3">Instant Emergency Availability</p>
                <button id="availability-toggle" class="btn btn-lg" data-current-status="<?php echo $isAvailable; ?>">
                    <i class="fas fa-hand-paper mr-2"></i> Initializing Status...
                </button>
                <small class="text-muted mt-2">Toggle this if you are able to donate immediately for an urgent, local request.</small>
            </div>
        </div>
    </div>
    
    <div class="row mb-5">
        <div class="col-lg-6">
            <div class="themed-card p-0 bg-white h-100">
                <div class="action-card-header">
                    <h3 class="mb-0 text-white"><i class="fas fa-calendar-check mr-2"></i> Next Donation Eligibility</h3>
                </div>
                <div class="p-4">
                    <p class="mb-2">Last Donation: <?php echo $lastDonation ? date('d M, Y', strtotime($lastDonation)) : 'N/A'; ?></p>
                    
                    <div class="eligibility-card p-3 my-3">
                        <?php if ($daysRemaining === 0): ?>
                            <p class="mb-0 font-weight-bold text-success">
                                <i class="fas fa-check-circle mr-2"></i> You are currently ELIGIBLE to donate!
                            </p>
                        <?php elseif ($daysRemaining > 0): ?>
                            <p class="mb-0 font-weight-bold">
                                <i class="fas fa-exclamation-circle mr-2"></i> Next Eligible Date: 
                                <span class="eligibility-date">
                                    <?php echo date('d M, Y', strtotime($nextEligibleDate)); ?>
                                </span>
                            </p>
                            <small class="text-muted">Approximately <?php echo $daysRemaining; ?> days left until eligibility.</small>
                        <?php else: ?>
                            <p class="mb-0 font-weight-bold text-info">
                                 <i class="fas fa-info-circle mr-2"></i> Donate for the first time to start your cycle.
                            </p>
                        <?php endif; ?>
                    </div>

                    <h5 class="mt-4"><i class="fas fa-list-alt mr-2"></i> Your Donation History</h5>
                    <p class="text-muted small">View all your past contributions, units donated, and where your blood was utilized.</p>
                    <a href="<?php echo BASE_URL; ?>views/user_dashboards/donation_history_full.php" class="btn btn-sm btn-outline-secondary">View Full History Log</a>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
             <div class="themed-card p-0 bg-white h-100">
                <div class="action-card-header" style="background: linear-gradient(90deg, var(--primary-red) 0%, #E04D54 100%);">
                    <h3 class="mb-0 text-white"><i class="fas fa-map-marker-alt mr-2"></i> Local Needs & Scheduling</h3>
                </div>
                <div class="p-4">
                    <h5 class="font-weight-bold" style="color: var(--primary-red);">Nearest Blood Banks (from <?php echo $donorCity; ?>)</h5>
                    <p class="text-muted small">Based on your location, here are the three closest donation centers.</p>
                    
                    <?php if (!empty($nearestBanks)): ?>
                        <ul class="list-group list-group-flush mb-4 small">
                            <?php foreach ($nearestBanks as $bank): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <i class="fas fa-search-location mr-2" style="color: var(--primary-blue);"></i>
                                    <?php echo htmlspecialchars($bank['name']); ?> (<?php echo htmlspecialchars($bank['city']); ?>)
                                    <span class="badge badge-secondary badge-pill"><?php echo $bank['distance']; ?> km</span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="alert alert-warning small">Could not calculate distances. Ensure your city is mapped.</div>
                    <?php endif; ?>

                    
                    <h5 class="mt-4"><i class="fas fa-calendar-alt mr-2"></i> Book Appointment</h5>
                    <p class="text-muted small">Schedule your next visit at a convenient time.</p>
                    
                    <a href="<?php echo BASE_URL; ?>views/user_dashboards/camp_booking_system.php" class="btn btn-sm btn-primary">Find & Book Camp/Bank</a>
                    
                    <hr class="my-4">

                    <h5 class="font-weight-bold" style="color: var(--accent-gold);"><i class="fas fa-heartbeat mr-2"></i> Organ Pledge Status</h5>
                    <p class="lead small">Current status: <?php echo $organPledge; ?>.</p>
                    <p class="text-muted small mb-0">Thank you for your life-saving intent. Manage your pledge details here.</p>
                </div>
            </div>
        </div>
    </div>
    
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    
    const toggleButton = document.getElementById('availability-toggle');
    const statusMsg = document.getElementById('status-message');
    if (!toggleButton) return; 

    // Initial state from PHP (data-current-status attribute)
    let isAvailable = parseInt(toggleButton.getAttribute('data-current-status')) === 1; 

    function updateButtonUI() {
        if (isAvailable) {
            toggleButton.classList.remove('unavailable', 'btn-danger');
            toggleButton.classList.add('available', 'btn-success');
            toggleButton.innerHTML = '<i class="fas fa-hand-holding-heart mr-2"></i> I AM available for emergency calls';
        } else {
            toggleButton.classList.remove('available', 'btn-success');
            toggleButton.classList.add('unavailable', 'btn-danger');
            toggleButton.innerHTML = '<i class="fas fa-hand-paper mr-2"></i> I am NOT available for emergency calls';
        }
    }

    // Initial load
    updateButtonUI();

    // Click handler with AJAX
    toggleButton.addEventListener('click', function() {
        const newStatus = isAvailable ? 0 : 1; // Calculate the desired status
        
        // Show loading message
        statusMsg.className = 'alert alert-info text-center mb-4';
        statusMsg.textContent = 'Updating availability status...';

        fetch('../../handlers/donor_availability_process.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ is_available: newStatus })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                isAvailable = data.is_available === 1; // Update state based on DB response
                updateButtonUI(); // Update button appearance
                
                statusMsg.className = 'alert alert-success text-center mb-4';
                statusMsg.textContent = isAvailable ? 
                    'Status set to AVAILABLE. You are now visible for local emergency requests.' : 
                    'Status set to NOT AVAILABLE. You are hidden from emergency lists.';
            } else {
                statusMsg.className = 'alert alert-danger text-center mb-4';
                statusMsg.textContent = `Error: ${data.message}`;
            }
        })
        .catch(error => {
            statusMsg.className = 'alert alert-danger text-center mb-4';
            statusMsg.textContent = 'Network error: Could not connect to the server.';
            console.error('Fetch error:', error);
        });
    });
});
</script>


<?php 
// Close connection and include footer
// Connection was closed before, ensuring clean operation.
include '../templates/dashboard_footer.php'; 
?>