<?php
// handlers/camp_actions.php

session_start();
require_once __DIR__ . '/../includes/config.php';

// Security check: Only Blood Banks can modify camps
if (!isset($_SESSION['loggedin']) || $_SESSION['user_type'] !== 'blood_bank') {
    header("Location: " . BASE_URL . "views/public/login.php?error=unauthorized_access");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $bank_id = $_SESSION['user_id'];
    $action = $_POST['action'] ?? '';
    $camp_id = intval($_POST['camp_id'] ?? 0);

    if ($camp_id === 0 || empty($action)) {
        $error_msg = "Invalid camp ID or action.";
    }

    try {
        if ($action === 'delete') {
            // --- DELETE ACTION ---
            $sql = "DELETE FROM camps WHERE camp_id = ? AND bank_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $camp_id, $bank_id);
            
            if (!$stmt->execute()) {
                throw new Exception("Database error during deletion.");
            }
            $stmt->close();
            $message = "Camp successfully deleted.";
            
        } elseif ($action === 'update') {
            // --- UPDATE ACTION (Edit Functionality) ---
            
            $location = trim($_POST['location_name']);
            $date = trim($_POST['camp_date']);
            $units = intval($_POST['target_units']);
            $status = trim($_POST['status']);
            
            if (empty($location) || empty($date) || !in_array($status, ['Scheduled', 'Completed', 'Cancelled'])) {
                throw new Exception("Invalid update data provided.");
            }

            $sql = "UPDATE camps SET location_name = ?, camp_date = ?, target_units = ?, status = ? WHERE camp_id = ? AND bank_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssisii", $location, $date, $units, $status, $camp_id, $bank_id);
            
            if (!$stmt->execute()) {
                 throw new Exception("Database error during update.");
            }
            $stmt->close();
            $message = "Camp details updated successfully.";

        } else {
             throw new Exception("Invalid action specified.");
        }
        
        $conn->close();
        header("Location: " . BASE_URL . "views/user_dashboards/camp_management.php?status=success&msg=" . urlencode($message));
        exit;

    } catch (Exception $e) {
        $conn->close();
        $error_msg = $e->getMessage();
        header("Location: " . BASE_URL . "views/user_dashboards/camp_management.php?status=error&msg=" . urlencode($error_msg));
        exit;
    }

} else {
    header("Location: " . BASE_URL . "views/user_dashboards/camp_management.php");
    exit;
}
?>