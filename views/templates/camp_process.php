<?php
// handlers/camp_process.php

session_start();
require_once __DIR__ . '/../includes/config.php';

// Security check: Only Blood Banks can access this handler
if (!isset($_SESSION['loggedin']) || $_SESSION['user_type'] !== 'blood_bank') {
    header("Location: " . BASE_URL . "views/public/login.php?error=unauthorized_access");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $bank_id = $_SESSION['user_id'];
    $location_name = trim($_POST['camp_location'] ?? '');
    $camp_date = trim($_POST['camp_date'] ?? '');
    
    // Simple validation
    if (empty($location_name) || empty($camp_date)) {
        header("Location: " . BASE_URL . "views/user_dashboards/bank_dashboard.php?camp_error=missing_fields");
        exit;
    }

    // Insert new camp record (target_units defaults to 0)
    $sql = "INSERT INTO camps (bank_id, location_name, camp_date) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $bank_id, $location_name, $camp_date);

    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        
        // Success redirect
        header("Location: " . BASE_URL . "views/user_dashboards/bank_dashboard.php?camp_success=true");
        exit;
    } else {
        $stmt->close();
        $conn->close();
        // Database failure redirect
        header("Location: " . BASE_URL . "views/user_dashboards/bank_dashboard.php?camp_error=db_fail");
        exit;
    }

} else {
    header("Location: " . BASE_URL . "views/user_dashboards/bank_dashboard.php");
    exit;
}
?>