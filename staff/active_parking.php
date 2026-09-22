<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/staff_auth.php';
requireStaffLogin();
$rows = $pdo->query("SELECT pr.*,u.full_name,v.vehicle_number,ps.slot_number FROM parking_records pr JOIN users u ON u.id=pr.customer_id JOIN vehicles v ON v.id=pr.vehicle_id JOIN parking_slots ps ON ps.id=pr.slot_id WHERE pr.status='active' ORDER BY pr.entry_time")->fetchAll();
$pageTitle = 'Active Parking';
require __DIR__ . '/../includes/panel_layout.php'; ?>
<h1>Active Parking</h1>
<div class="table-responsive card p-3">
    <table class="table">
        <thead>
            <tr>
                <th>Parking</th>
                <th>Customer</th>
                <th>Vehicle</th>
                <th>Slot</th>
                <th>Entry</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody><?php foreach ($rows as $r): ?>
                <tr>
                    <td><?= e($r['parking_number']) ?></td>
                    <td><?= e($r['full_name']) ?></td>
                    <td><?= e($r['vehicle_number']) ?></td>
                    <td><?= e($r['slot_number']) ?></td>
                    <td><?= e($r['entry_time']) ?></td>
                    <td><a class="btn btn-sm btn-primary"
                            href="<?= BASE_URL ?>staff/vehicle_exit.php?id=<?= $r['id'] ?>">Exit</a></td>
                </tr><?php endforeach; ?>
        </tbody>
    </table>
</div><?php require __DIR__ . '/../includes/panel_footer.php'; ?>