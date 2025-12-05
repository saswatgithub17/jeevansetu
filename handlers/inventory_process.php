<?php
// handlers/inventory_process.php

session_start();
require_once __DIR__ . '/../includes/config.php';

// Security check: Only Blood Banks can access this handler
if (!isset($_SESSION['loggedin']) || $_SESSION['user_type'] !== 'blood_bank') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Using json_decode because updates are typically sent via AJAX/Fetch
    $data = json_decode(file_get_contents("php://input"), true);
    
    $bank_id = $_SESSION['user_id'];
    $blood_group = trim($data['blood_group'] ?? '');
    $units = intval($data['units'] ?? 0);
    $component = trim($data['component_type'] ?? 'Whole Blood'); // Default to Whole Blood if not specified

    if (empty($blood_group) || $units < 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid data provided.']);
        exit;
    }

    // Use INSERT ... ON DUPLICATE KEY UPDATE for atomic inventory management
    $sql = "INSERT INTO inventory (bank_id, blood_group, component_type, units_available)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE units_available = units_available + VALUES(units_available), 
                                    last_updated = NOW()";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isss", $bank_id, $blood_group, $component, $units);

    if ($stmt->execute()) {
        // Fetch the new updated stock level for confirmation (optional, but good for feedback)
        $new_stock_sql = "SELECT units_available FROM inventory WHERE bank_id = ? AND blood_group = ? AND component_type = ?";
        $stmt_stock = $conn->prepare($new_stock_sql);
        $stmt_stock->bind_param("iss", $bank_id, $blood_group, $component);
        $stmt_stock->execute();
        $new_units = $stmt_stock->get_result()->fetch_assoc()['units_available'] ?? $units;
        $stmt_stock->close();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Inventory updated successfully.', 
            'new_stock' => $new_units
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
    }

    $stmt->close();
    $conn->close();

} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>