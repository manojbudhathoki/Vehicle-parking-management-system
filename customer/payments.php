<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/customer_auth.php';
requireCustomerLogin();
$s = $pdo->prepare("SELECT p.*,pr.parking_number FROM payments p JOIN parking_records pr ON pr.id=p.parking_record_id WHERE p.customer_id=? ORDER BY p.id DESC");
$s->execute([(int) $_SESSION['user']['id']]);
$rows = $s->fetchAll();
$pageTitle = 'Payments';
require __DIR__ . '/../includes/panel_layout.php'; ?>
<h1>Payments</h1>
<div class="table-responsive card p-3">
    <table class="table">
        <thead>
            <tr>
                <th>Parking</th>
                <th>Amount</th>
                <th>Method</th>
                <th>Status</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody><?php foreach ($rows as $r): ?>
                <tr>
                    <td><?= e($r['parking_number']) ?></td>
                    <td><?= money((float) $r['amount']) ?></td>
                    <td><?= e($r['payment_method']) ?></td>
                    <td><?= e($r['payment_status']) ?></td>
                    <td><?= e($r['paid_at']) ?></td>
                </tr><?php endforeach; ?>
        </tbody>
    </table>
</div><?php require __DIR__ . '/../includes/panel_footer.php'; ?>