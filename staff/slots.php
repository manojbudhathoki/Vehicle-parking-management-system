<?php require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/staff_auth.php';
requireStaffLogin();
$rows = $pdo->query("SELECT * FROM parking_slots ORDER BY zone,slot_number")->fetchAll();
$pageTitle = 'Slots';
require __DIR__ . '/../includes/panel_layout.php'; ?>
<h1>Parking Slots</h1>
<div class="row g-3"><?php foreach ($rows as $r): ?>
        <div class="col-6 col-md-3">
            <div class="slot slot-<?= e($r['status']) ?>">
                <?= e($r['slot_number']) ?><br><small><?= e($r['status']) ?></small>
            </div>
        </div><?php endforeach; ?>
</div><?php require __DIR__ . '/../includes/panel_footer.php'; ?>