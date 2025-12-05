<?php
// includes/config.php

// ------------------------------------------------------------------
// 1. DATABASE CONFIGURATION
// ------------------------------------------------------------------

// Define database credentials
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');       // Change this to your actual DB user
define('DB_PASSWORD', '');          // Change this to your actual DB password
define('DB_NAME', 'jeevansetu');   // Must match the database name you create

// Attempt to connect to MySQL database 
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if($conn === false){
    die("ERROR: Could not connect. " . $conn->connect_error);
}

// ------------------------------------------------------------------
// 2. PROJECT CONSTANTS & GLOBAL SETTINGS
// ------------------------------------------------------------------

// INCLUDE UTILITY FUNCTIONS (NEW LINE)
require_once __DIR__ . '/functions.php';

// Base URL for the project (important for internal links and redirects)
define('BASE_URL', 'http://localhost/JeevanSetu/');

// Default titles
define('SITE_NAME', 'JeevanSetu - The Bridge of Life');

// Donation eligibility rule (e.g., minimum 90 days between blood donations)
define('DONATION_DAYS_GAP', 90);

?>