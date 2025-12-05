<?php
// handlers/match_trigger_handler.php

session_start();
require_once __DIR__ . '/../includes/config.php';

// Security Check (Admin only)
if (!isset($_SESSION['loggedin']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: " . BASE_URL . "views/public/login.php?error=unauthorized_access");
    exit;
}

if (isset($_POST['action']) && $_POST['action'] === 'run_match') {
    
    // Include the core algorithm logic
    require_once 'organ_match_algorithm.php'; 

    try {
        // Run the matching function
        $match_output = runOrganMatchingCycle($conn);
        $conn->close();

        $results_json = json_encode($match_output['results'] ?? []);
        $message = $match_output['message'] ?? 'Matching cycle complete.';
        
        // Redirect back with results and message
        header("Location: " . BASE_URL . "views/user_dashboards/match_trigger_console.php?status=success&msg=" . urlencode($message) . "&results_json=" . urlencode($results_json));
        exit;
        
    } catch (Exception $e) {
        $conn->close();
        $error_msg = "Algorithm failed: " . $e->getMessage();
        header("Location: " . BASE_URL . "views/user_dashboards/match_trigger_console.php?status=error&msg=" . urlencode($error_msg));
        exit;
    }

} else {
    header("Location: " . BASE_URL . "views/user_dashboards/match_trigger_console.php");
    exit;
}
?>