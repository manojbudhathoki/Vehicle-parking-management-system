<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/admin_auth.php';
requireAdminLogin();
$daily = $pdo->query("SELECT DATE(paid_at) day, COUNT(*) payments, SUM(amount) revenue FROM payments WHERE payment_status='paid' GROUP BY DATE(paid_at) ORDER BY day DESC LIMIT 30")->fetchAll();
$pageTitle = 'Reports';
require __DIR__ . '/../includes/panel_layout.php'; ?>
<h1>Reports</h1>
<div class="table-responsive card p-3">
    <table class="table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Payments</th>
                <th>Revenue</th>
            </tr>
        </thead>
        <tbody><?php foreach ($daily as $r): ?>
                <tr>
                    <td><?= e($r['day']) ?></td>
                    <td><?= e((string) $r['payments']) ?></td>
                    <td><?= money((float) $r['revenue']) ?></td>
                </tr><?php endforeach; ?>
        </tbody>
    </table>
</div><?php require __DIR__ . '/../includes/panel_footer.php'; ?>