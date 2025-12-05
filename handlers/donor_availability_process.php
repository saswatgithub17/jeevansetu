<?php
// handlers/donor_availability_process.php

session_start();
require_once __DIR__ . '/../includes/config.php';

// Set JSON header for API response
header('Content-Type: application/json');

// Security check: Must be a logged-in donor
if (!isset($_SESSION['loggedin']) || $_SESSION['user_type'] !== 'donor') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $user_id = $_SESSION['user_id'];
    
    // Get data from the AJAX request body
    $data = json_decode(file_get_contents("php://input"), true);
    $new_status = intval($data['is_available'] ?? 0); // Should be 0 (false) or 1 (true)
    
    // Validate status value
    if ($new_status !== 0 && $new_status !== 1) {
        echo json_encode(['success' => false, 'message' => 'Invalid status value provided.']);
        exit;
    }

    try {
        // Update the is_available field in the donors table
        $sql = "UPDATE donors SET is_available = ? WHERE donor_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $new_status, $user_id);

        if (!$stmt->execute()) {
            throw new Exception("Database error during status update.");
        }
        $stmt->close();
        
        // Update the last activity time or similar metric here if needed

        echo json_encode([
            'success' => true, 
            'is_available' => $new_status,
            'message' => 'Availability status updated successfully.'
        ]);

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }

    $conn->close();

} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>