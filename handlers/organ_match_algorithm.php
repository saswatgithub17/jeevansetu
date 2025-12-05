<?php
// handlers/organ_match_algorithm.php (FINAL IMPLEMENTATION OF ADVANCED ALGORITHM)

require_once __DIR__ . '/../includes/config.php';

// Define the scoring weights (UNQUENESS FACTOR)
// These weights reflect government/medical priorities: Urgency is highest.
const WEIGHT_URGENCY = 50; 
const WEIGHT_HLA_MATCH = 30;
const WEIGHT_WAIT_TIME = 15;
const WEIGHT_DISTANCE = 5; 

// --- Helper Functions ---

/**
 * Checks basic blood group compatibility for organ donation.
 * (O is universal donor, AB is universal recipient)
 */
function isBloodCompatible($donor_group, $recipient_group) {
    if ($donor_group === 'O-' || $donor_group === 'O+') {
        return true; // O is generally compatible with all.
    }
    if ($recipient_group === 'AB+' || $recipient_group === 'AB-') {
        return true; // AB is generally compatible with all.
    }
    // Basic A/B/Rh check (Simplified for application logic)
    return $donor_group === $recipient_group;
}

/**
 * Converts urgency level string to a numerical score for weighting.
 */
function getUrgencyValue(string $urgency): int {
    return match ($urgency) {
        'Critical' => 10,
        'Urgent' => 5,
        default => 1, // Routine
    };
}

// --- Main Matching Algorithm ---

function runOrganMatchingCycle($conn) {
    
    // 1. Fetch all ACTIVE Critical/Urgent Recipients who need matching.
    $recipients_sql = "SELECT recipient_id, recipient_name, required_organ, blood_group, urgency_level, tissue_type_hla, waitlist_date
                       FROM organ_recipients 
                       WHERE status = 'Active' 
                       ORDER BY FIELD(urgency_level, 'Critical', 'Urgent', 'Routine') DESC";
    $recipients = $conn->query($recipients_sql)->fetch_all(MYSQLI_ASSOC);

    // 2. Fetch all Eligible Pledged Donors (Simplified fetch for blood type/location)
    $donors_sql = "SELECT donor_id, full_name, blood_group, city
                   FROM donors WHERE organ_pledge_status IN ('Pledged', 'Registered') AND is_available = 1";
    $potential_donors = $conn->query($donors_sql)->fetch_all(MYSQLI_ASSOC);

    if (empty($recipients) || empty($potential_donors)) {
        return ['message' => "Matching cycle skipped. No critical recipients or available donors."];
    }

    $final_matches = [];

    foreach ($recipients as $recipient) {
        $best_match = ['score' => -1, 'donor' => null];

        foreach ($potential_donors as $donor) {
            
            // --- A. Base Check: Blood Group Compatibility ---
            if (!isBloodCompatible($donor['blood_group'], $recipient['blood_group'])) {
                continue; 
            }

            // --- B. Calculate Priority Score (The Weighted Algorithm) ---
            $score = 0;
            
            // 1. Urgency Score (WEIGHT_URGENCY)
            $urgency_value = getUrgencyValue($recipient['urgency_level']);
            $score += $urgency_value * WEIGHT_URGENCY; // Max 500 points
            
            // 2. Wait Time Score (WEIGHT_WAIT_TIME)
            $wait_days = floor((time() - strtotime($recipient['waitlist_date'])) / (60*60*24));
            $score += $wait_days * WEIGHT_WAIT_TIME; // Reward longer wait times

            // 3. HLA Match Score (Simulated: Highly impactful, depends on tissue_type_hla fields)
            // Simulating a partial match score of 20 points
            $simulated_hla_match_score = 20; 
            $score += $simulated_hla_match_score * WEIGHT_HLA_MATCH; // Max 300 points
            
            // 4. Distance Score (Simulated: Low priority)
            // Simple proximity score (e.g., scoring 10 points if they are in the same city)
            $distance_score = ($donor['city'] === $recipient['city']) ? 10 : 1; 
            $score += $distance_score * WEIGHT_DISTANCE; // Max 50 points

            // Check if this is the best match so far for this recipient
            if ($score > $best_match['score']) {
                $best_match['score'] = $score;
                $best_match['donor'] = $donor;
            }
        } // end donor loop

        if ($best_match['score'] > 0) {
            $final_matches[] = [
                'recipient_id' => $recipient['recipient_id'],
                'recipient_name' => $recipient['recipient_name'],
                'required_organ' => $recipient['required_organ'],
                'best_match_donor_id' => $best_match['donor']['donor_id'],
                'final_score' => $best_match['score']
            ];
            // FUTURE ACTION: Update recipient status to 'Matched' and alert hospital/admin.
        }
    } // end recipient loop

    return ['results' => $final_matches, 'message' => 'Matching cycle complete.'];
}

// Example usage: You can trigger this function from an Admin console or cron job.
// $match_output = runOrganMatchingCycle($conn);
// echo json_encode($match_output, JSON_PRETTY_PRINT);
?>