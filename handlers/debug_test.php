<?php
// debug_test.php

// 1. Load Configuration
require_once __DIR__ . '/../includes/config.php';

// --- Database Connection Check ---
if ($conn->connect_error) {
    die("❌ CRITICAL FAILURE: Database Connection Failed. Check config.php DB_SERVER/USERNAME/PASSWORD/NAME. Error: " . $conn->connect_error);
}
echo "✅ SUCCESS: Database Connected to " . DB_NAME . "<br><br>";


// --- Password Hash Verification Check ---

$test_email = 'admin@jeevansetu.gov'; // Use the admin test email
$test_password_input = 'password123'; // The password you are typing

$sql = "SELECT user_id, password_hash, user_type FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $test_email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows == 1) {
    $stmt->bind_result($user_id, $hashed_password, $user_type);
    $stmt->fetch();
    
    echo "User Found:<br>";
    echo "Email: " . htmlspecialchars($test_email) . "<br>";
    echo "Stored Hash (Database): " . htmlspecialchars($hashed_password) . "<br>";

    // CRITICAL CHECKPOINT
    if (password_verify($test_password_input, $hashed_password)) {
        echo "<h3 style='color: green;'>✅ SUCCESS: Password HASH Matches 'password123'!</h3>";
        echo "The login script (auth_process.php) should now work fine. <br>The issue might be in session handling or redirection.";
    } else {
        echo "<h3 style='color: red;'>❌ FAILED: Password HASH Mismatch!</h3>";
        echo "The password 'password123' does NOT match the hash above. <br>";
        echo "You MUST re-run the SQL to update the passwords, or the user tried to register with an empty/different password.<br>";

        // Show a hash generated right now for comparison
        echo "<br>Hash generated for 'password123' NOW: " . password_hash($test_password_input, PASSWORD_DEFAULT) . "<br>";
        echo "Compare this hash to the 'Stored Hash' above. If they are different, the stored hash is incorrect.";
    }

} else {
    echo "❌ FAILED: Test user ('admin@jeevansetu.gov') not found in the database. Check your INSERT SQL!";
}

$stmt->close();
$conn->close();
?>