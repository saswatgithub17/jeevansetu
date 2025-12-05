<?php 
// views/public/register.php
include '../templates/header.php'; 
?>

<style>
/* Specific styling for the selection cards */
.registration-selection-card {
    min-height: 280px;
    padding: 40px 20px;
    border: 3px solid transparent;
    cursor: pointer;
    transition: all 0.3s ease-in-out;
    border-radius: 20px;
    background-color: white;
    /* Ensure cards are visible by default before JS kicks in, for safety */
    opacity: 1; 
}

/* Hover and active state effects */
.registration-selection-card:hover {
    transform: translateY(-8px) scale(1.02);
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
}

.registration-selection-card.donor-card:hover {
    border-color: var(--primary-red);
}
.registration-selection-card.hospital-card:hover {
    border-color: var(--primary-blue);
}
.registration-selection-card.bank-card:hover {
    border-color: var(--accent-gold);
}

.selection-icon {
    font-size: 4rem;
    margin-bottom: 15px;
    transition: transform 0.4s ease;
}

.registration-selection-card:hover .selection-icon {
    transform: rotateY(180deg); /* 3D flip effect on icon */
}

/* Specific thematic text colors */
.donor-text { color: var(--primary-red); }
.hospital-text { color: var(--primary-blue); }
.bank-text { color: var(--accent-gold); }

/* Animation for the card entrance */
.card-entrance {
    opacity: 0;
    transform: translateY(30px);
    animation: fadeInSlideUp 0.8s forwards ease-out;
}

@keyframes fadeInSlideUp {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>


<div class="container py-5 mt-5">
    <div class="text-center mb-5">
        <h1 class="display-4 font-weight-bold" style="color: var(--dark-text); animation: fadeIn 1s ease-out;">
            Welcome to JeevanSetu Registration
        </h1>
        <p class="lead text-muted mt-3">
            Please select the type of account you wish to register. Your category determines your dashboard access and features.
        </p>
    </div>

    <div class="row justify-content-center text-center">
        
        <div class="col-lg-4 col-md-6 mb-4">
            <a href="#donor-form" id="select-donor" class="text-decoration-none">
                <div class="themed-card registration-selection-card donor-card card-entrance" style="animation-delay: 0.1s;">
                    <div class="selection-icon donor-text"><i class="fas fa-tint"></i></div>
                    <h3 class="donor-text font-weight-bold">Individual Donor</h3>
                    <p class="text-muted small mt-3">Pledge blood and/or organs. Manage your donation schedule, history, and impact.</p>
                </div>
            </a>
        </div>

        <div class="col-lg-4 col-md-6 mb-4">
            <a href="#hospital-form" id="select-hospital" class="text-decoration-none">
                <div class="themed-card registration-selection-card hospital-card card-entrance" style="animation-delay: 0.2s;">
                    <div class="selection-icon hospital-text"><i class="fas fa-hospital"></i></div>
                    <h3 class="hospital-text font-weight-bold">Hospital / Clinic</h3>
                    <p class="text-muted small mt-3">Submit urgent blood and organ requests. Access predictive reports and manage patient data securely.</p>
                </div>
            </a>
        </div>

        <div class="col-lg-4 col-md-6 mb-4">
            <a href="#bank-form" id="select-bank" class="text-decoration-none">
                <div class="themed-card registration-selection-card bank-card card-entrance" style="animation-delay: 0.3s;">
                    <div class="selection-icon bank-text"><i class="fas fa-warehouse"></i></div>
                    <h3 class="bank-text font-weight-bold">Blood Bank / Center</h3>
                    <p class="text-muted small mt-3">Real-time inventory management, camp scheduling, and automated low-stock alerts.</p>
                </div>
            </a>
        </div>
    </div>
    
    <hr class="my-5">
    
    <div id="registration-forms" style="animation: fadeIn 1s ease-out;" class="py-4">
        <h2 id="form-title" class="text-center mb-4 font-weight-bold" style="color: var(--primary-blue);"></h2>
        
        <div id="status-message" class="alert d-none" role="alert"></div>

        <form id="donor-form" class="registration-form mx-auto" style="max-width: 600px; display: none;" action="../../handlers/register_process.php" method="POST">
            <input type="hidden" name="user_type" value="donor">
            
            <h5 class="donor-text font-weight-bold mb-3"><i class="fas fa-user-circle mr-2"></i> Account Details</h5>
            <div class="form-group">
                <label for="d_email">Email Address <span class="donor-text">*</span></label>
                <input type="email" class="form-control" id="d_email" name="email" required>
                <div class="invalid-feedback" id="d_email_feedback">Valid email required.</div>
            </div>
            <div class="form-group">
                <label for="d_password">Password <span class="donor-text">*</span></label>
                <input type="password" class="form-control" id="d_password" name="password" required>
                <small class="form-text text-muted">Minimum 8 characters, including upper/lower case and a number.</small>
                <div class="invalid-feedback" id="d_password_feedback">Password must meet complexity requirements.</div>
            </div>
            <hr>
            <h5 class="donor-text font-weight-bold mb-3"><i class="fas fa-id-card-alt mr-2"></i> Personal Information</h5>
            <div class="form-group">
                <label for="d_full_name">Full Name <span class="donor-text">*</span></label>
                <input type="text" class="form-control" id="d_full_name" name="full_name" required>
            </div>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="d_blood_group">Blood Group <span class="donor-text">*</span></label>
                    <select id="d_blood_group" name="blood_group" class="form-control" required>
                        <option value="">Choose...</option>
                        <option value="O+">O+</option>
                        <option value="O-">O- (Universal Donor)</option>
                        <option value="A+">A+</option>
                        <option value="A-">A-</option>
                        <option value="B+">B+</option>
                        <option value="B-">B-</option>
                        <option value="AB+">AB+</option>
                        <option value="AB-">AB- (Universal Recipient)</option>
                    </select>
                </div>
                 <div class="form-group col-md-6">
                    <label for="d_dob">Date of Birth <span class="donor-text">*</span></label>
                    <input type="date" class="form-control" id="d_dob" name="date_of_birth" required>
                     <div class="invalid-feedback" id="d_dob_feedback">Must be 18 years or older to donate blood.</div>
                </div>
            </div>
            <hr>
            <h5 class="donor-text font-weight-bold mb-3"><i class="fas fa-brain mr-2"></i> Organ Donation Pledge</h5>
            <div class="form-group">
                <label for="d_organ_pledge">Are you willing to pledge your organs for donation? <span class="donor-text">*</span></label>
                <select id="d_organ_pledge" name="organ_pledge_status" class="form-control" required>
                    <option value="Not Pledged">No, Not at this time</option>
                    <option value="Pledged">Yes, I wish to pledge my organs</option>
                </select>
                <small class="form-text text-muted">Your intent is registered securely. You may formally register later.</small>
            </div>
            
            <div class="form-group mt-4 text-center">
                <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-user-plus mr-2"></i> Complete Donor Registration</button>
            </div>
        </form>

        <form id="hospital-form" class="registration-form mx-auto" style="max-width: 600px; display: none;" action="../../handlers/register_process.php" method="POST">
             <input type="hidden" name="user_type" value="hospital">
             
             <h5 class="hospital-text font-weight-bold mb-3"><i class="fas fa-user-circle mr-2"></i> Account Details</h5>
             <div class="form-group">
                <label for="h_email">Official Email Address <span class="hospital-text">*</span></label>
                <input type="email" class="form-control" id="h_email" name="email" required>
            </div>
            <div class="form-group">
                <label for="h_password">Password <span class="hospital-text">*</span></label>
                <input type="password" class="form-control" id="h_password" name="password" required>
            </div>
            <hr>
            <h5 class="hospital-text font-weight-bold mb-3"><i class="fas fa-id-card-alt mr-2"></i> Institution Details (For Verification)</h5>
            <div class="form-group">
                <label for="h_name">Hospital Name <span class="hospital-text">*</span></label>
                <input type="text" class="form-control" id="h_name" name="hospital_name" required>
            </div>
            <div class="form-group">
                <label for="h_license">Government License Number <span class="hospital-text">*</span></label>
                <input type="text" class="form-control" id="h_license" name="license_number" required>
                <small class="form-text text-muted">Used for official verification by JeevanSetu administration.</small>
            </div>
             <div class="form-group mt-4 text-center">
                <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-user-plus mr-2"></i> Complete Hospital Registration</button>
            </div>
        </form>
        
        <form id="bank-form" class="registration-form mx-auto" style="max-width: 600px; display: none;" action="../../handlers/register_process.php" method="POST">
             <input type="hidden" name="user_type" value="blood Bank">
             
             <h5 class="bank-text font-weight-bold mb-3"><i class="fas fa-user-circle mr-2"></i> Account Details</h5>
             <div class="form-group">
                <label for="b_email">Official Email Address <span class="bank-text">*</span></label>
                <input type="email" class="form-control" id="b_email" name="email" required>
            </div>
            <div class="form-group">
                <label for="b_password">Password <span class="bank-text">*</span></label>
                <input type="password" class="form-control" id="b_password" name="password" required>
            </div>
            <hr>
            <h5 class="bank-text font-weight-bold mb-3"><i class="fas fa-warehouse mr-2"></i> Institution Details (For Verification)</h5>
            <div class="form-group">
                <label for="b_name">Blood Bank Name <span class="bank-text">*</span></label>
                <input type="text" class="form-control" id="b_name" name="bank_name" required>
            </div>
            <div class="form-group">
                <label for="b_license">Government License Number <span class="bank-text">*</span></label>
                <input type="text" class="form-control" id="b_license" name="license_number" required>
            </div>
             <div class="form-group mt-4 text-center">
                <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-user-plus mr-2"></i> Complete Blood Bank Registration</button>
            </div>
        </form>
        
    </div>
</div>

<?php 
include '../templates/footer.php'; 
?>