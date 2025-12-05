// assets/js/main.js

document.addEventListener('DOMContentLoaded', () => {
    
    // --- 1. User Type Selection Logic & Transitions ---
    const selectionCards = document.querySelectorAll('.registration-selection-card');
    const registrationFormsDiv = document.getElementById('registration-forms');
    const formTitle = document.getElementById('form-title');
    const forms = document.querySelectorAll('.registration-form');

    selectionCards.forEach(card => {
        card.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Determine the target form ID based on the link's href
            const targetFormId = this.closest('a').getAttribute('href').substring(1);
            const targetForm = document.getElementById(targetFormId);
            
            // Hide all other forms and remove active class
            forms.forEach(form => form.style.display = 'none');
            selectionCards.forEach(c => c.classList.remove('active-selection'));
            
            // Show the target form with a smooth fade effect
            registrationFormsDiv.style.display = 'block';
            
            setTimeout(() => {
                targetForm.style.display = 'block';
                targetForm.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 100); 

            // Update the form title dynamically
            const userType = targetForm.querySelector('input[name="user_type"]').value;
            formTitle.textContent = `Register as ${userType.charAt(0).toUpperCase() + userType.slice(1)}`;

            // Highlight the selected card
            this.classList.add('active-selection');
        });
    });


    // --- 2. Client-Side Validation (Required for Proper User Experience) ---

    // Function to check password complexity (Minimum 8 chars, 1 upper, 1 lower, 1 number)
    function validatePassword(password) {
        const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{8,}$/;
        return regex.test(password);
    }
    
    // Function to check if the donor is 18+ years old
    function validateAge(dobString) {
        const today = new Date();
        const birthDate = new Date(dobString);
        let age = today.getFullYear() - birthDate.getFullYear();
        const m = today.getMonth() - birthDate.getMonth();
        
        // Adjust age if birthday hasn't occurred this year yet
        if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }
        return age >= 18;
    }


    // Attach validation listeners to the Donor Form
    const donorForm = document.getElementById('donor-form');
    if(donorForm) {
        donorForm.addEventListener('submit', function(e) {
            let isValid = true;
            
            // Validate Password
            const passwordField = document.getElementById('d_password');
            if (!validatePassword(passwordField.value)) {
                passwordField.classList.add('is-invalid');
                document.getElementById('d_password_feedback').textContent = "Password needs 8+ chars, upper, lower, and number.";
                isValid = false;
            } else {
                passwordField.classList.remove('is-invalid');
            }

            // Validate Age (Date of Birth)
            const dobField = document.getElementById('d_dob');
            if (!validateAge(dobField.value)) {
                dobField.classList.add('is-invalid');
                document.getElementById('d_dob_feedback').textContent = "Donors must be 18 years or older.";
                isValid = false;
            } else {
                dobField.classList.remove('is-invalid');
            }

            if (!isValid) {
                e.preventDefault(); // Stop form submission if validation fails
                document.getElementById('status-message').className = 'alert alert-danger text-center';
                document.getElementById('status-message').textContent = 'Please correct the highlighted errors before submitting.';
                document.getElementById('status-message').classList.remove('d-none');
            }
        });
    }

    // Function to handle the Donor Availability Toggle
    function handleDonorAvailability() {
        const toggleButton = document.getElementById('availability-toggle');
        if (!toggleButton) return; // Exit if not on the donor dashboard

        // --- TEMPORARY STATE MANAGEMENT (Replace with AJAX later) ---
        // Simulating fetching initial state (e.g., from a database field)
        let isAvailable = localStorage.getItem('isAvailable') === 'true'; 

        function updateButtonState() {
            if (isAvailable) {
                toggleButton.classList.remove('unavailable');
                toggleButton.classList.add('available');
                toggleButton.innerHTML = '<i class="fas fa-hand-holding-heart mr-2"></i> I AM available for emergency calls';
            } else {
                toggleButton.classList.remove('available');
                toggleButton.classList.add('unavailable');
                toggleButton.innerHTML = '<i class="fas fa-hand-paper mr-2"></i> I am NOT available for emergency calls';
            }
            // Save the temporary state
            localStorage.setItem('isAvailable', isAvailable);
        }

        // Initial load
        updateButtonState();

        // Click handler
        toggleButton.addEventListener('click', function() {
            isAvailable = !isAvailable; // Flip the state
            updateButtonState();

            // In a real system, an AJAX call would go here:
            // fetch('handlers/update_availability.php', { method: 'POST', body: JSON.stringify({ available: isAvailable }) });
        });
    }

    // Ensure this function is called when the DOM content is loaded
    document.addEventListener('DOMContentLoaded', () => {
        // ... (Existing registration and validation logic remains here) ...
        
        // Call the new dashboard function
        handleDonorAvailability();
    });

    // Function to handle Hospital Dashboard interactions (e.g., submitting new requests)
    function handleHospitalDashboard() {
        const requestForm = document.getElementById('new-blood-request-form');
        if (!requestForm) return;

        requestForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // FUTURE: This is where we would implement AJAX to send the request data 
            // to handlers/request_blood_process.php
            
            alert("Blood Request Submitted! In a full system, this would now contact nearby Blood Banks.");
            
            // Reset form or show success message
            requestForm.reset();
        });
    }

    // Ensure this function is called when the DOM content is loaded
    document.addEventListener('DOMContentLoaded', () => {
        // ... (Existing registration and donor dashboard logic remains here) ...
        
        // Call the new hospital dashboard function
        handleHospitalDashboard();
    });

    // Function to handle Blood Bank Dashboard interactions
function handleBankDashboard() {
    const campForm = document.getElementById('new-camp-form');
    
    // 1. Camp Scheduling Submission
    if (campForm) {
        campForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // FUTURE: Implement AJAX submission to handlers/schedule_camp_process.php
            const location = this.querySelector('input[name="camp_location"]').value;
            const date = this.querySelector('input[name="camp_date"]').value;
            
            alert(`New Camp Scheduled! Location: ${location}, Date: ${date}. This would now be saved to the database.`);
            
            this.reset();
        });
    }
}

    // Global function placeholder for Inventory Update (called by the table buttons)
    function openInventoryModal(bloodGroup) {
        // FUTURE: This would open a Bootstrap Modal with a form to add/remove units
        alert(`Opening Inventory Update for Blood Group: ${bloodGroup}`);
    }


    // Ensure the new function is called when the DOM content is loaded
    document.addEventListener('DOMContentLoaded', () => {
        // ... (Existing registration, donor, and hospital dashboard logic remains here) ...
        
        // Call the new bank dashboard function
        handleBankDashboard();
    });

    // Function to handle Blood Bank Inventory Submission via AJAX
    function handleInventoryUpdate() {
        const updateForm = document.getElementById('inventory-update-form');
        const statusMsg = document.getElementById('inventory-status-msg');
        
        if (!updateForm) return;

        updateForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = {
                blood_group: this.querySelector('select[name="blood_group"]').value,
                units: this.querySelector('input[name="units"]').value,
                // component_type remains default 'Whole Blood' for simplicity here
            };
            
            statusMsg.classList.remove('d-none', 'alert-success', 'alert-danger');
            statusMsg.classList.add('alert-info');
            statusMsg.textContent = 'Submitting update...';

            fetch('../../handlers/inventory_process.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    statusMsg.className = 'alert alert-success text-center small mt-2';
                    statusMsg.textContent = `Success! ${formData.units} units of ${formData.blood_group} added. New total: ${data.new_stock}`;
                    updateForm.reset();
                    
                    // FUTURE: Reload the inventory table dynamically here
                } else {
                    statusMsg.className = 'alert alert-danger text-center small mt-2';
                    statusMsg.textContent = `Update Failed: ${data.message}`;
                }
            })
            .catch(error => {
                statusMsg.className = 'alert alert-danger text-center small mt-2';
                statusMsg.textContent = 'Network error during submission.';
                console.error('Fetch error:', error);
            });
        });
    }

    // Ensure the new function is called when the DOM content is loaded
    document.addEventListener('DOMContentLoaded', () => {
        // ... (Existing registration, donor, and hospital dashboard logic remains here) ...
        
        handleBankDashboard(); // Existing function call
        handleInventoryUpdate(); // NEW function call
    });
});