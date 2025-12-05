<?php
// handlers/register_process.php (FINAL VERSION: PLAIN TEXT PASSWORD STORAGE)

// Corrected Path: Stepping up one directory level (from handlers/)
require_once __DIR__ . '/../includes/config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // --------------------------------------------------------
    // 1. INPUT SANITIZATION AND VALIDATION (GENERAL)
    // --------------------------------------------------------
    
    $user_type = isset($_POST['user_type']) ? trim($_POST['user_type']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    if (empty($user_type) || empty($email) || empty($password)) {
        header("Location: " . BASE_URL . "views/public/register.php?error=missing_fields");
        exit;
    }

    // *** CRITICAL CHANGE: STORING PLAIN TEXT PASSWORD ***
    // The database column name is still 'password_hash' but holds the plain text.
    $plain_password = $password; 

    // Start transaction for atomic insertion
    $conn->begin_transaction();

    try {
        // --------------------------------------------------------
        // 2. INSERT INTO users TABLE 
        // --------------------------------------------------------
        // Inserting plain_password into the column historically named password_hash
        $sql_user = "INSERT INTO users (email, password_hash, user_type) VALUES (?, ?, ?)";
        $stmt_user = $conn->prepare($sql_user);
        $stmt_user->bind_param("sss", $email, $plain_password, $user_type);

        if (!$stmt_user->execute()) {
            // Check for duplicate email error (code 1062 in MySQL)
            if ($conn->errno === 1062) {
                 throw new Exception("This email address is already registered.");
            }
            throw new Exception("Error inserting into users table: " . $stmt_user->error);
        }

        $user_id = $conn->insert_id;
        $stmt_user->close();

        // --------------------------------------------------------
        // 3. INSERT INTO SPECIFIC TABLE
        // --------------------------------------------------------
        
        $success = false;
        
        if ($user_type === 'donor') {
            
            $full_name = trim($_POST['full_name']);
            $blood_group = trim($_POST['blood_group']);
            $date_of_birth = trim($_POST['date_of_birth']);
            $organ_pledge_status = trim($_POST['organ_pledge_status']); 
            
            // Note: phone_number, address_line_1, city, pincode, gender are optional at this step
            
            if (empty($full_name) || empty($blood_group) || empty($date_of_birth)) {
                 throw new Exception("Missing donor details.");
            }

            $sql_donor = "INSERT INTO donors (donor_id, full_name, blood_group, date_of_birth, organ_pledge_status) 
                          VALUES (?, ?, ?, ?, ?)";
            $stmt_donor = $conn->prepare($sql_donor);
            $stmt_donor->bind_param("issss", $user_id, $full_name, $blood_group, $date_of_birth, $organ_pledge_status);

            if (!$stmt_donor->execute()) {
                throw new Exception("Error inserting into donors table: " . $stmt_donor->error);
            }
            $stmt_donor->close();
            $success = true;

        } elseif ($user_type === 'hospital') {
            
            $hospital_name = trim($_POST['hospital_name']);
            $license_number = trim($_POST['license_number']);
            // Placeholder contact details (not collected in the form yet, set to empty)
            $contact_person = ""; 
            $city = ""; 
            $pincode = ""; 
            
            if (empty($hospital_name) || empty($license_number)) {
                 throw new Exception("Missing hospital details.");
            }

            $sql_hospital = "INSERT INTO hospitals (hospital_id, hospital_name, license_number, contact_person, city, pincode) 
                             VALUES (?, ?, ?, ?, ?, ?)";
            $stmt_hospital = $conn->prepare($sql_hospital);
            $stmt_hospital->bind_param("isssss", $user_id, $hospital_name, $license_number, $contact_person, $city, $pincode);

            if (!$stmt_hospital->execute()) {
                throw new Exception("Error inserting into hospitals table: " . $stmt_hospital->error);
            }
            $stmt_hospital->close();
            $success = true;

        } elseif ($user_type === 'blood_bank') {
            
            $bank_name = trim($_POST['bank_name']);
            $license_number = trim($_POST['license_number']);
            // Placeholder contact details (not collected in the form yet, set to empty)
            $contact_person = ""; 
            $city = ""; 
            $pincode = ""; 
            
            if (empty($bank_name) || empty($license_number)) {
                 throw new Exception("Missing blood bank details.");
            }

            $sql_bank = "INSERT INTO blood_banks (bank_id, bank_name, license_number, contact_person, city, pincode) 
                         VALUES (?, ?, ?, ?, ?, ?)";
            $stmt_bank = $conn->prepare($sql_bank);
            $stmt_bank->bind_param("isssss", $user_id, $bank_name, $license_number, $contact_person, $city, $pincode);

            if (!$stmt_bank->execute()) {
                throw new Exception("Error inserting into blood_banks table: " . $stmt_bank->error);
            }
            $stmt_bank->close();
            $success = true;
        }

        if ($success) {
            $conn->commit();
            header("Location: " . BASE_URL . "views/public/login.php?registration=success");
            exit;
        } else {
             throw new Exception("Invalid user type specified.");
        }

    } catch (Exception $e) {
        $conn->rollback();
        // Use error logging instead of exposing error messages directly
        // error_log("Registration Error: " . $e->getMessage()); 
        
        $error_code = $e->getMessage() === "This email address is already registered." ? "email_exists" : "registration_failed";
        header("Location: " . BASE_URL . "views/public/register.php?error=" . $error_code);
        exit;
    }

    $conn->close();

} else {
    header("Location: " . BASE_URL . "views/public/register.php");
    exit;
}
?>