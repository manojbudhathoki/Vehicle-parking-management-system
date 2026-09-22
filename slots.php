<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
$slots = $pdo->query("SELECT * FROM parking_slots WHERE status != 'inactive' ORDER BY zone, slot_number")->fetchAll();
$pageTitle = 'Parking Slots';
require __DIR__ . '/includes/header.php';
?>
<h1>Parking Slots</h1>
<!-- <p class="text-muted">Availability is loaded directly from MySQL.</p> -->
<div class="row g-3">
    <?php foreach ($slots as $slot): ?>
        <div class="col-6 col-md-3">
            <div class="slot slot-<?= e($slot['status']) ?>">
                <?= e($slot['slot_number']) ?><br><small><?= e(ucfirst($slot['status'])) ?></small><br><small><?= e($slot['vehicle_type']) ?></small>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>