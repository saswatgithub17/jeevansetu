<?php
// handlers/booking_process.php

session_start();
require_once __DIR__ . '/../includes/config.php';

// Security check: Must be a logged-in donor
if (!isset($_SESSION['loggedin']) || $_SESSION['user_type'] !== 'donor') {
    header("Location: " . BASE_URL . "views/public/login.php?error=unauthorized_access");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $donor_id = $_SESSION['user_id'];
    $camp_id = intval($_POST['camp_id'] ?? 0);
    $bank_id = intval($_POST['bank_id'] ?? 0); // If booking directly at a bank (not a camp)
    $appointment_date = trim($_POST['appointment_date'] ?? '');
    $appointment_time = trim($_POST['appointment_time'] ?? NULL);
    
    // Determine target ID
    $target_id = $camp_id > 0 ? $camp_id : $bank_id;
    $target_type = $camp_id > 0 ? 'camp_id' : 'bank_id';

    if ($target_id === 0 || empty($appointment_date)) {
        header("Location: " . BASE_URL . "views/user_dashboards/camp_booking_system.php?status=error&msg=" . urlencode("Invalid booking details."));
        exit;
    }

    try {
        // Simple insertion (assuming a slot is always available for now)
        $sql = "INSERT INTO appointments (donor_id, $target_type, appointment_date, appointment_time) VALUES (?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiss", $donor_id, $target_id, $appointment_date, $appointment_time);

        if (!$stmt->execute()) {
            throw new Exception("Database error: Could not book slot.");
        }
        $stmt->close();
        $conn->close();

        header("Location: " . BASE_URL . "views/user_dashboards/camp_booking_system.php?status=success&msg=" . urlencode("Slot booked successfully! See you soon."));
        exit;

    } catch (Exception $e) {
        $conn->close();
        header("Location: " . BASE_URL . "views/user_dashboards/camp_booking_system.php?status=error&msg=" . urlencode("Booking Failed: " . $e->getMessage()));
        exit;
    }

} else {
    header("Location: " . BASE_URL . "views/user_dashboards/camp_booking_system.php");
    exit;
}
?>