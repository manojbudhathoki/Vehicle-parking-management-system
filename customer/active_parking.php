<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/customer_auth.php';
requireCustomerLogin();
$s = $pdo->prepare("SELECT pr.*,v.vehicle_number,ps.slot_number FROM parking_records pr JOIN vehicles v ON v.id=pr.vehicle_id JOIN parking_slots ps ON ps.id=pr.slot_id WHERE pr.customer_id=? AND pr.status='active' ORDER BY pr.entry_time DESC");
$s->execute([(int) $_SESSION['user']['id']]);
$rows = $s->fetchAll();
$pageTitle = 'Active Parking';
require __DIR__ . '/../includes/panel_layout.php'; ?>
<h1>Active Parking</h1>
<div class="table-responsive card p-3 mt-3">
    <table class="table">
        <thead>
            <tr>
                <th>Parking</th>
                <th>Vehicle</th>
                <th>Slot</th>
                <th>Entry</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody><?php foreach ($rows as $r): ?>
                <tr>
                    <td><?= e($r['parking_number']) ?></td>
                    <td><?= e($r['vehicle_number']) ?></td>
                    <td><?= e($r['slot_number']) ?></td>
                    <td><?= e($r['entry_time']) ?></td>
                    <td><?= e(ucfirst($r['status'])) ?></td>
                </tr><?php endforeach; ?>
        </tbody>
    </table>
</div><?php require __DIR__ . '/../includes/panel_footer.php'; ?>