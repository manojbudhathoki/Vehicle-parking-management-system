<?php require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/staff_auth.php';
requireStaffLogin();
$revenue = (float) $pdo->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE payment_status='paid'")->fetchColumn();
$sessions = (int) $pdo->query("SELECT COUNT(*) FROM parking_records")->fetchColumn();
$pageTitle = 'Reports';
require __DIR__ . '/../includes/panel_layout.php'; ?>
<h1>Operational Reports</h1>
<div class="row g-4">
    <div class="col-md-6">
        <div class="card p-4">
            <h5>Total Parking Sessions</h5>
            <div class="display-6"><?= $sessions ?></div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card p-4">
            <h5>Total Recorded Revenue</h5>
            <div class="display-6"><?= money($revenue) ?></div>
        </div>
    </div>
</div><?php require __DIR__ . '/../includes/panel_footer.php'; ?>