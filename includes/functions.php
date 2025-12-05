<?php
// includes/functions.php (Utility file for helper functions - FINAL UPDATE)

/**
 * Simulates fetching Latitude and Longitude for a given address/city.
 * In production, this calls a service like Google Maps Geocoding API.
 * @param string $city The city name.
 * @return array|null An array containing ['lat', 'lon'] or null on failure.
 */
function getGeoCoordinates(string $city): ?array {
    
    // --- SIMULATION DATA: 35+ Major Cities and Odisha Cities ---
    $coordinates = [
        
        // Tier 1 & Major Metropolitan Cities
        'Mumbai' => ['lat' => 19.0760, 'lon' => 72.8777],
        'Delhi' => ['lat' => 28.7041, 'lon' => 77.1025],
        'Bangalore' => ['lat' => 12.9716, 'lon' => 77.5946],
        'Chennai' => ['lat' => 13.0827, 'lon' => 80.2707],
        'Kolkata' => ['lat' => 22.5726, 'lon' => 88.3639],
        'Hyderabad' => ['lat' => 17.3850, 'lon' => 78.4867],
        'Pune' => ['lat' => 18.5204, 'lon' => 73.8567],
        'Ahmedabad' => ['lat' => 23.0225, 'lon' => 72.5714],
        'Jaipur' => ['lat' => 26.9124, 'lon' => 75.7873],
        'Lucknow' => ['lat' => 26.8467, 'lon' => 80.9462],
        'Bhopal' => ['lat' => 23.2599, 'lon' => 77.4126],
        'Patna' => ['lat' => 25.5941, 'lon' => 85.1376],
        'Indore' => ['lat' => 22.7196, 'lon' => 75.8577],
        'Chandigarh' => ['lat' => 30.7333, 'lon' => 76.7794],
        'Surat' => ['lat' => 21.1702, 'lon' => 72.8311],

        // Major Odisha Cities 
        'Bhubaneswar' => ['lat' => 20.2961, 'lon' => 85.8245],
        'Cuttack' => ['lat' => 20.4625, 'lon' => 85.8830],
        'Rourkela' => ['lat' => 22.2587, 'lon' => 84.8560],
        'Berhampur' => ['lat' => 19.3195, 'lon' => 84.7937],
        'Sambalpur' => ['lat' => 21.4667, 'lon' => 83.9833],
        'Puri' => ['lat' => 19.8135, 'lon' => 85.8315],
        'Balasore' => ['lat' => 21.4933, 'lon' => 86.9200],
        'Bhadrak' => ['lat' => 20.9497, 'lon' => 86.4950],
        'Baripada' => ['lat' => 21.9365, 'lon' => 86.7214],
        'Jeypore' => ['lat' => 18.8471, 'lon' => 82.5694],
        'Khurda' => ['lat' => 20.1700, 'lon' => 85.6500],
        'Jajpur' => ['lat' => 20.8400, 'lon' => 86.3300],
        'Keonjhar' => ['lat' => 21.6300, 'lon' => 85.6000],
        'Rayagada' => ['lat' => 19.1670, 'lon' => 83.4160],
        'Paralakhemundi' => ['lat' => 18.7700, 'lon' => 84.0900],
        'Dhenkanal' => ['lat' => 20.6500, 'lon' => 85.6000],
        'Angul' => ['lat' => 20.8333, 'lon' => 85.1167], 
        'Deogarh' => ['lat' => 21.5300, 'lon' => 84.7200],
    ];
    // --- END SIMULATION DATA ---
    
    // Normalize input to match dictionary keys
    $city = ucwords(strtolower(trim($city)));
    
    if (isset($coordinates[$city])) {
        return $coordinates[$city]; 
    }
    
    return null; 
}


/**
 * Calculates the distance between two sets of coordinates using the Haversine formula.
 * @param float $lat1 Latitude of point 1.
 * @param float $lon1 Longitude of point 1.
 * @param float $lat2 Latitude of point 2.
 * @param float $lon2 Longitude of point 2.
 * @return float Distance in kilometers (km).
 */
function calculateDistance($lat1, $lon1, $lat2, $lon2): float {
    // Earth's radius in kilometers
    $R = 6371; 

    // Convert degrees to radians
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);

    // Haversine formula implementation
    $a = sin($dLat/2) * sin($dLat/2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    $distance = $R * $c; // Distance in km

    return round($distance, 2);
}
?>