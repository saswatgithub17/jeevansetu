<?php
// handlers/fetch_public_needs.php (REVISED for Accurate Thresholds)

require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json');

$criticalThreshold = 10; // Red Alert
$safetyThreshold = 50;   // General Low Stock Alert

// Query to find blood groups where the total inventory is below the general safety threshold (50 units)
$sql = "SELECT i.blood_group, SUM(i.units_available) as total_stock
        FROM inventory i
        GROUP BY i.blood_group
        HAVING total_stock <= ?
        ORDER BY total_stock ASC
        LIMIT 5";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $safetyThreshold); // Check against 50 units (safety threshold)
$stmt->execute();
$result = $stmt->get_result();

$critical_needs = [];
$needs_found = false;

while ($row = $result->fetch_assoc()) {
    $urgency = 'LOW';
    
    // Classify urgency based on stock level
    if ($row['total_stock'] <= $criticalThreshold) {
        $urgency = 'CRITICAL';
    } elseif ($row['total_stock'] <= $safetyThreshold) {
        $urgency = 'HIGH';
    }

    // Only include groups that are HIGH or CRITICAL
    if ($urgency !== 'LOW') {
        $critical_needs[] = [
            'group' => $row['blood_group'],
            'stock' => $row['total_stock'],
            'urgency' => $urgency
        ];
        $needs_found = true;
    }
}

$stmt->close();
$conn->close();

// Fallback if no genuine needs are found (show a positive message)
if (!$needs_found) {
    // If the database returns no low stock groups, display a positive message for good UX.
    echo json_encode(['success' => true, 'needs' => [['group' => 'A+', 'stock' => 60, 'urgency' => 'NORMAL']]]);
    exit;
}

echo json_encode(['success' => true, 'needs' => $critical_needs]);