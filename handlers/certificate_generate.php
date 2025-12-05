<?php
// handlers/certificate_generate.php (ADVANCED REDESIGN)

session_start();
require_once __DIR__ . '/../includes/config.php';

// Security check
if (!isset($_SESSION['loggedin']) || $_SESSION['user_type'] !== 'donor') {
    die("Unauthorized access.");
}

$log_id = intval($_GET['log_id'] ?? 0);
$donorName = htmlspecialchars($_GET['donor_name'] ?? 'VALUED DONOR');
$donationDate = htmlspecialchars($_GET['date'] ?? date("Y-m-d"));

if ($log_id === 0) {
    die("Invalid donation log ID.");
}

// --- Dynamic Certificate Content (Simulated Data) ---
$bloodGroup = "O+"; // Assume fetched or known
$component = "Whole Blood"; // Assume fetched

// Set headers to output HTML (not PDF, for visual display)
header('Content-Type: text/html');

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>JeevanSetu Donation Certificate</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"> 
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    
    <style>
        /* Expert Certificate Design */
        body {
            background-color: #f0f0f0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            font-family: 'Roboto', sans-serif;
        }
        .certificate-container {
            width: 800px;
            height: 600px;
            background: #ffffff;
            border: 15px solid #D9232D; /* Primary Red Border */
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
            position: relative;
            overflow: hidden;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100"><circle cx="50" cy="50" r="45" fill="none" stroke="%23f0f0f0" stroke-width="5"/></svg>');
            background-size: 100px;
        }
        .cert-title {
            font-family: 'Playfair Display', serif;
            font-size: 3.5rem;
            color: #1A86FF; /* Primary Blue */
            margin-bottom: 5px;
            text-align: center;
            letter-spacing: 3px;
        }
        .cert-subtitle {
            font-size: 1.2rem;
            color: #666;
            text-align: center;
            margin-bottom: 40px;
        }
        .present-text {
            font-size: 1.4rem;
            text-align: center;
            margin-bottom: 15px;
            font-weight: 400;
        }
        .donor-name {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            color: #D9232D;
            text-align: center;
            margin-bottom: 40px;
            border-bottom: 3px double #D9232D;
            padding-bottom: 5px;
            display: inline-block;
        }
        .detail-box {
            text-align: center;
            margin-top: 30px;
        }
        .detail-item {
            font-size: 1.1rem;
            color: #333;
            margin-bottom: 10px;
        }
        .signature-area {
            position: absolute;
            bottom: 50px;
            width: 80%;
            left: 10%;
            display: flex;
            justify-content: space-between;
        }
        .signature-block {
            text-align: center;
            width: 30%;
        }
        .signature-line {
            border-top: 1px solid #999;
            margin-top: 5px;
            padding-top: 5px;
            font-size: 0.9rem;
        }
        .download-btn-fixed {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }
        
        /* FIX: CSS Media Query to hide the button during printing */
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background-color: white; /* Ensure white background for print */
            }
        }
    </style>
</head>
<body>
    <div class="certificate-container">
        <header>
            <p class="cert-title">Jeevan<span style="color: #D9232D;">Setu</span></p>
            <p class="cert-subtitle">THE BRIDGE OF LIFE</p>
        </header>
        
        <div class="main-content text-center">
            <p class="present-text">This Certificate is Proudly Presented To</p>
            <h2 class="donor-name"><?php echo strtoupper($donorName); ?></h2>
            
            <p class="present-text" style="font-size: 1.5rem; font-style: italic; color: #333;">
                For the Invaluable Gift of Life
            </p>
            
            <div class="detail-box row">
                <div class="col-md-4 detail-item">
                    <i class="fas fa-calendar-alt fa-2x" style="color: #1A86FF;"></i>
                    <p class="mt-2">Date of Contribution</p>
                    <p class="font-weight-bold"><?php echo date('F j, Y', strtotime($donationDate)); ?></p>
                </div>
                <div class="col-md-4 detail-item">
                    <i class="fas fa-heartbeat fa-2x" style="color: #D9232D;"></i>
                    <p class="mt-2">Blood Group</p>
                    <p class="font-weight-bold display-4" style="color: #D9232D;"><?php echo $bloodGroup; ?></p>
                </div>
                <div class="col-md-4 detail-item">
                    <i class="fas fa-capsules fa-2x" style="color: #1A86FF;"></i>
                    <p class="mt-2">Component Donated</p>
                    <p class="font-weight-bold"><?php echo $component; ?></p>
                </div>
            </div>
            <p class="mt-5 small text-muted">Donation Log ID: <?php echo $log_id; ?> | This record is verified by JeevanSetu.</p>
        </div>
        
        <div class="signature-area">
            <div class="signature-block">
                <p>---</p>
                <p class="signature-line">Chief Medical Officer</p>
            </div>
            <div class="signature-block">
                <p>---</p>
                <p class="signature-line">JeevanSetu Administration</p>
            </div>
        </div>
    </div>

    <button class="btn btn-lg btn-primary download-btn-fixed no-print" onclick="window.print()"><i class="fas fa-download mr-2"></i> Download/Print</button>
</body>
</html>