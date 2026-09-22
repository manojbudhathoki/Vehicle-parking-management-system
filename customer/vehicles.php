<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/customer_auth.php';
requireCustomerLogin();
$uid = (int) $_SESSION['user']['id'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf($_POST['csrf_token'] ?? null);
    $id = (int) ($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? '';
    if ($action === 'delete') {
        $s = $pdo->prepare("DELETE FROM vehicles WHERE id=? AND customer_id=?");
        $s->execute([$id, $uid]);
        flash($s->rowCount() ? 'success' : 'danger', $s->rowCount() ? 'Vehicle deleted.' : 'Vehicle not found or already in use.');
    }
    redirect('customer/vehicles.php');
}
$rows = $pdo->prepare("SELECT * FROM vehicles WHERE customer_id=? ORDER BY id DESC");
$rows->execute([$uid]);
$rows = $rows->fetchAll();
$pageTitle = 'My Vehicles';
require __DIR__ . '/../includes/panel_layout.php'; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <p class="text-muted mb-0">Manage the vehicles you use for parking.</p><a class="btn btn-primary"
        href="<?= BASE_URL ?>customer/add_vehicle.php"><i class="bi bi-plus-circle me-1"></i>Add Vehicle</a>
</div>
<div class="row g-3"><?php foreach ($rows as $r): ?>
        <div class="col-md-6 col-xl-4">
            <div class="panel-card h-100">
                <div class="panel-card-body">
                    <div class="d-flex justify-content-between">
                        <h5 class="mb-1"><?= e($r['vehicle_number']) ?></h5><span
                            class="badge bg-<?= $r['status'] === 'active' ? 'success' : 'secondary' ?>"><?= e(ucfirst($r['status'])) ?></span>
                    </div>
                    <div class="text-muted mb-3"><?= e($r['vehicle_type']) ?> · <?= e($r['brand']) ?>     <?= e($r['model']) ?> ·
                        <?= e($r['color']) ?></div><a href="<?= BASE_URL ?>customer/add_vehicle.php?edit=<?= $r['id'] ?>"
                        class="btn btn-sm btn-outline-primary">Edit</a><?php if ($r['status'] === 'active'): ?>
                        <form method="post" class="d-inline" data-confirm="Delete this vehicle?"><input type="hidden"
                                name="csrf_token" value="<?= e(csrfToken()) ?>"><input type="hidden" name="id"
                                value="<?= $r['id'] ?>"><button name="action" value="delete"
                                class="btn btn-sm btn-outline-danger">Delete</button></form><?php endif; ?>
                </div>
            </div>
        </div><?php endforeach;
if (!$rows): ?>
        <div class="col-12">
            <div class="panel-card">
                <div class="panel-card-body text-center text-muted py-5">No vehicles added yet.</div>
            </div>
        </div><?php endif; ?>
</div>
<?php require __DIR__ . '/../includes/panel_footer.php'; ?>