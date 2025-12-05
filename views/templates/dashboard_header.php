<?php
// views/templates/dashboard_header.php (Dedicated Dashboard Header)

// Start the session (ensures session variables are available)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include configuration file
require_once __DIR__ . '/../../includes/config.php';

// --- Global Security Check ---
// Redirect if not logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: " . BASE_URL . "views/public/login.php?error=session_expired");
    exit;
}

$user_type_display = ucfirst(str_replace('_', ' ', $_SESSION['user_type']));

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | JeevanSetu - <?php echo $user_type_display; ?></title>
    
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    
    <link rel="stylesheet" href="../../assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"> 
    
    <style>
        body { padding-top: 65px; background-color: var(--light-bg); }
        .dashboard-navbar {
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    
    <nav class="navbar navbar-expand-lg navbar-light fixed-top bg-white dashboard-navbar">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?php echo BASE_URL; ?>views/user_dashboards/<?php echo $_SESSION['user_type']; ?>_dashboard.php">
                <span style="color: var(--primary-red); font-weight:900;">Jeevan</span><span style="color: var(--primary-blue);">Setu</span>
                <span class="badge badge-secondary ml-2"><?php echo $user_type_display; ?> Portal</span>
            </a>
            
            <div class="collapse navbar-collapse" id="dashboardNav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a class="btn btn-danger text-white font-weight-bold" href="<?php echo BASE_URL; ?>handlers/logout_process.php">
                            <i class="fas fa-sign-out-alt mr-2"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>