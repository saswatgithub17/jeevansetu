<?php
// views/user_dashboards/match_trigger_console.php

include '../templates/dashboard_header.php'; 

// Security Check: Ensure only Admin can access this page
if ($_SESSION['user_type'] !== 'admin') {
    header("Location: " . BASE_URL . "views/public/login.php?error=unauthorized_access");
    exit;
}

$message = $_GET['msg'] ?? '';
$status_type = $_GET['status'] ?? '';
$results = null;

// Check if the algorithm was executed and results were passed back
if (isset($_GET['results_json'])) {
    $results = json_decode(urldecode($_GET['results_json']), true);
}

?>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="font-weight-bold" style="color: var(--primary-red);"><i class="fas fa-brain mr-2"></i> Organ Matching Algorithm Console</h1>
        <a href="admin_dashboard.php" class="btn btn-secondary">
             <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
        </a>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?php echo ($status_type == 'success' ? 'success' : 'danger'); ?> text-center mb-4" role="alert">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div class="card themed-card p-4 mb-4">
        <h4 class="mb-3" style="color: var(--primary-blue);">Trigger Matching Cycle</h4>
        <p class="text-muted">This function executes the weighted algorithm to find the best potential organ matches based on urgency, HLA typing, and wait time.</p>
        
        <form action="../../handlers/match_trigger_handler.php" method="POST">
            <button type="submit" name="action" value="run_match" class="btn btn-lg btn-danger mt-3">
                <i class="fas fa-sync-alt mr-2"></i> RUN MATCHING ALGORITHM NOW
            </button>
        </form>
    </div>

    <div class="card themed-card p-4">
        <h4 class="mb-3" style="color: var(--primary-red);">Last Execution Results</h4>

        <?php if ($results): ?>
            <p class="text-success font-weight-bold">Execution Successful: Found <?php echo count($results); ?> potential matches.</p>
            
            <div class="table-responsive">
                <table class="table table-striped table-sm">
                    <thead>
                        <tr><th>Recipient</th><th>Required Organ</th><th>Best Donor ID</th><th>Final Score</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results as $match): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($match['recipient_name']); ?></td>
                            <td><?php echo htmlspecialchars($match['required_organ']); ?></td>
                            <td><span class="badge badge-info"><?php echo $match['best_match_donor_id']; ?></span></td>
                            <td><?php echo round($match['final_score'], 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
             <div class="alert alert-info mb-0">
                Awaiting first execution results. Click the button above to start the cycle.
            </div>
        <?php endif; ?>
    </div>
</div>

<?php 
include '../templates/dashboard_footer.php'; 
?>