<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
$rates = $pdo->query("SELECT * FROM parking_rates WHERE is_active=1 ORDER BY vehicle_type")->fetchAll();
$pageTitle = 'Parking Rates';
require __DIR__ . '/includes/header.php';
?>
<h1>Parking Rates</h1>
<div class="row g-4"><?php foreach ($rates as $r): ?>
        <div class="col-md-4">
            <div class="card p-4">
                <h4><?= e($r['vehicle_type']) ?></h4>
                <p>First hour: <strong><?= money((float) $r['first_hour_rate']) ?></strong></p>
                <p>Each additional hour: <strong><?= money((float) $r['additional_hour_rate']) ?></strong></p>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>