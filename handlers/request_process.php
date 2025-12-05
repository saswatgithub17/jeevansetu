<?php
// handlers/request_process.php

session_start();
require_once __DIR__ . '/../includes/config.php';

// Security check: Only Hospitals can access this handler
if (!isset($_SESSION['loggedin']) || $_SESSION['user_type'] !== 'hospital') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Data is assumed to come from the Hospital Dashboard form submission
    $hospital_id = $_SESSION['user_id'];
    $requested_group = trim($_POST['blood_group_request'] ?? '');
    $units_needed = intval($_POST['units_needed'] ?? 0);
    $urgency_level = trim($_POST['urgency_level'] ?? 'Routine'); // Assuming a hidden field or default value
    
    if (empty($requested_group) || $units_needed <= 0) {
        header("Location: " . BASE_URL . "views/user_dashboards/hospital_dashboard.php?request_error=invalid_data");
        exit;
    }

    // --------------------------------------------------------
    // 1. INSERT Request into the 'requests' table
    // --------------------------------------------------------
    
    $sql = "INSERT INTO requests (hospital_id, requested_group, units_needed, urgency_level, status)
            VALUES (?, ?, ?, ?, 'Pending')";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isss", $hospital_id, $requested_group, $units_needed, $urgency_level);

    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        
        // FUTURE: In the advanced phase, this successful insertion would trigger:
        // 1. An alert to nearby Blood Banks (the 'notifications' system).
        // 2. An update to the public '#needs' page.
        
        header("Location: " . BASE_URL . "views/user_dashboards/hospital_dashboard.php?request_success=true");
        exit;
        
    } else {
        $stmt->close();
        $conn->close();
        header("Location: " . BASE_URL . "views/user_dashboards/hospital_dashboard.php?request_error=db_fail");
        exit;
    }

} else {
    header("Location: " . BASE_URL . "views/user_dashboards/hospital_dashboard.php");
    exit;
}
?>