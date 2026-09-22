<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/customer_auth.php';
requireCustomerLogin();
$uid = (int) $_SESSION['user']['id'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf($_POST['csrf_token'] ?? null);
    $id = (int) ($_POST['id'] ?? 0);
    if ($id) {
        $s = $pdo->prepare("UPDATE notifications SET is_read=1 WHERE id=? AND user_id=?");
        $s->execute([$id, $uid]);
    } else {
        $pdo->prepare("UPDATE notifications SET is_read=1 WHERE user_id=?")->execute([$uid]);
    }
    flash('success', 'Notifications marked as read.');
    redirect('customer/notifications.php');
}
$s = $pdo->prepare("SELECT * FROM notifications WHERE user_id=? ORDER BY id DESC");
$s->execute([$uid]);
$rows = $s->fetchAll();
$pageTitle = 'Notifications';
require __DIR__ . '/../includes/panel_layout.php'; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <p class="text-muted mb-0">Parking updates and account notifications.</p>
    <form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><button
            class="btn btn-sm btn-outline-primary">Mark all as read</button></form>
</div>
<div class="panel-card">
    <div class="panel-card-body p-0"><?php foreach ($rows as $r): ?>
            <div class="p-3 border-bottom <?= $r['is_read'] ? '' : 'bg-light' ?>">
                <div class="d-flex justify-content-between"><strong><?= e($r['title']) ?></strong><?php if (!$r['is_read']): ?>
                        <form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><input
                                type="hidden" name="id" value="<?= $r['id'] ?>"><button class="btn btn-sm btn-link">Mark
                                read</button></form><?php endif; ?>
                </div>
                <div><?= e($r['message']) ?></div><small class="text-muted"><?= e($r['created_at']) ?></small>
            </div><?php endforeach;
    if (!$rows): ?>
            <div class="text-center text-muted py-5">No notifications.</div><?php endif; ?>
    </div>
</div>
<?php require __DIR__ . '/../includes/panel_footer.php'; ?>