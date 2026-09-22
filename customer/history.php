<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/customer_auth.php';
requireCustomerLogin();
$s = $pdo->prepare("SELECT pr.*,v.vehicle_number,ps.slot_number,p.amount,p.payment_method,p.payment_status FROM parking_records pr JOIN vehicles v ON v.id=pr.vehicle_id JOIN parking_slots ps ON ps.id=pr.slot_id LEFT JOIN payments p ON p.parking_record_id=pr.id WHERE pr.customer_id=? ORDER BY pr.id DESC");
$s->execute([(int) $_SESSION['user']['id']]);
$rows = $s->fetchAll();
$pageTitle = 'Parking History';
require __DIR__ . '/../includes/panel_layout.php'; ?>
<h1>Parking History</h1>
<div class="table-responsive card p-3">
    <table class="table">
        <thead>
            <tr>
                <th>Parking</th>
                <th>Vehicle</th>
                <th>Slot</th>
                <th>Entry</th>
                <th>Exit</th>
                <th>Fee</th>
                <th>Payment</th>
            </tr>
        </thead>
        <tbody><?php foreach ($rows as $r): ?>
                <tr>
                    <td><?= e($r['parking_number']) ?></td>
                    <td><?= e($r['vehicle_number']) ?></td>
                    <td><?= e($r['slot_number']) ?></td>
                    <td><?= e($r['entry_time']) ?></td>
                    <td><?= e($r['exit_time']) ?></td>
                    <td><?= money((float) $r['calculated_fee']) ?></td>
                    <td><?= e($r['payment_method'] ?? '—') ?></td>
                </tr><?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require __DIR__ . '/../includes/panel_footer.php'; ?>