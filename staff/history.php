<?php require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/staff_auth.php';
requireStaffLogin();
$rows = $pdo->query("SELECT pr.*,u.full_name,v.vehicle_number,ps.slot_number FROM parking_records pr JOIN users u ON u.id=pr.customer_id JOIN vehicles v ON v.id=pr.vehicle_id JOIN parking_slots ps ON ps.id=pr.slot_id ORDER BY pr.id DESC")->fetchAll();
$pageTitle = 'Parking History';
require __DIR__ . '/../includes/panel_layout.php'; ?>
<h1>Parking History</h1>
<div class="table-responsive card p-3">
    <table class="table">
        <thead>
            <tr>
                <th>Parking</th>
                <th>Customer</th>
                <th>Vehicle</th>
                <th>Slot</th>
                <th>Entry</th>
                <th>Exit</th>
                <th>Fee</th>
            </tr>
        </thead>
        <tbody><?php foreach ($rows as $r): ?>
                <tr>
                    <td><?= e($r['parking_number']) ?></td>
                    <td><?= e($r['full_name']) ?></td>
                    <td><?= e($r['vehicle_number']) ?></td>
                    <td><?= e($r['slot_number']) ?></td>
                    <td><?= e($r['entry_time']) ?></td>
                    <td><?= e($r['exit_time']) ?></td>
                    <td><?= money((float) $r['calculated_fee']) ?></td>
                </tr><?php endforeach; ?>
        </tbody>
    </table>
</div><?php require __DIR__ . '/../includes/panel_footer.php'; ?>