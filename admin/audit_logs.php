<?php require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/admin_auth.php';
requireAdminLogin();
$rows = $pdo->query("SELECT a.*,u.full_name FROM audit_logs a LEFT JOIN users u ON u.id=a.user_id ORDER BY a.id DESC LIMIT 200")->fetchAll();
$pageTitle = 'Audit Logs';
require __DIR__ . '/../includes/panel_layout.php'; ?>
<h1>Audit Logs</h1>
<div class="table-responsive card p-3">
    <table class="table">
        <thead>
            <tr>
                <th>User</th>
                <th>Action</th>
                <th>Module</th>
                <th>Record</th>
                <th>Description</th>
                <th>IP</th>
                <th>Time</th>
            </tr>
        </thead>
        <tbody><?php foreach ($rows as $r): ?>
                <tr>
                    <td><?= e($r['full_name'] ?? 'System') ?></td>
                    <td><?= e($r['action']) ?></td>
                    <td><?= e($r['module']) ?></td>
                    <td><?= e((string) $r['record_id']) ?></td>
                    <td><?= e($r['description']) ?></td>
                    <td><?= e($r['ip_address']) ?></td>
                    <td><?= e($r['created_at']) ?></td>
                </tr><?php endforeach; ?>
        </tbody>
    </table>
</div><?php require __DIR__ . '/../includes/panel_footer.php'; ?>