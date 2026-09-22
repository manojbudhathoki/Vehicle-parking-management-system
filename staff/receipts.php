<?php require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/staff_auth.php';
requireStaffLogin();
$rows = $pdo->query("SELECT r.*,pr.parking_number,u.full_name,p.amount,p.payment_method FROM receipts r JOIN parking_records pr ON pr.id=r.parking_record_id JOIN users u ON u.id=r.customer_id JOIN payments p ON p.id=r.payment_id ORDER BY r.id DESC")->fetchAll();
$pageTitle = 'Receipts';
require __DIR__ . '/../includes/panel_layout.php'; ?>
<h1>Receipts</h1>
<div class="table-responsive card p-3">
    <table class="table">
        <thead>
            <tr>
                <th>Receipt</th>
                <th>Parking</th>
                <th>Customer</th>
                <th>Amount</th>
                <th>Method</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody><?php foreach ($rows as $r): ?>
                <tr>
                    <td><?= e($r['receipt_number']) ?></td>
                    <td><?= e($r['parking_number']) ?></td>
                    <td><?= e($r['full_name']) ?></td>
                    <td><?= money((float) $r['amount']) ?></td>
                    <td><?= e($r['payment_method']) ?></td>
                    <td><a class="btn btn-sm btn-outline-primary" href="<?= BASE_URL ?>receipt.php?id=<?= (int) $r['id'] ?>"
                            target="_blank"><i class="bi bi-printer me-1"></i>Print</a></td>
                </tr><?php endforeach; ?>
        </tbody>
    </table>
</div><?php require __DIR__ . '/../includes/panel_footer.php'; ?>