<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/admin_auth.php';
requireAdminLogin();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf($_POST['csrf_token'] ?? null);
    $id = (int) ($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? '';
    if ($action === 'status') {
        $s = $pdo->prepare("UPDATE vehicles SET status=CASE WHEN status='active' THEN 'inactive' ELSE 'active' END WHERE id=?");
        $s->execute([$id]);
        flash('success', 'Vehicle status updated.');
    } elseif ($action === 'delete') {
        try {
            $pdo->prepare("DELETE FROM vehicles WHERE id=?")->execute([$id]);
            flash('success', 'Vehicle deleted.');
        } catch (PDOException $e) {
            flash('danger', 'Vehicle cannot be deleted because it is referenced by bookings or parking records.');
        }
    }
    redirect('admin/vehicles.php');
}
$rows = $pdo->query("SELECT v.*,u.full_name FROM vehicles v JOIN users u ON u.id=v.customer_id ORDER BY v.id DESC")->fetchAll();
$pageTitle = 'Vehicles';
require __DIR__ . '/../includes/panel_layout.php'; ?>
<div class="panel-card">
    <div class="panel-card-header">
        <h6>All Customer Vehicles</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Number</th>
                    <th>Customer</th>
                    <th>Type</th>
                    <th>Brand / Model</th>
                    <th>Color</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody><?php foreach ($rows as $r): ?>
                    <tr>
                        <td><strong><?= e($r['vehicle_number']) ?></strong></td>
                        <td><?= e($r['full_name']) ?></td>
                        <td><?= e($r['vehicle_type']) ?></td>
                        <td><?= e(trim(($r['brand'] ?? '') . ' ' . ($r['model'] ?? ''))) ?></td>
                        <td><?= e($r['color']) ?></td>
                        <td><?= e(ucfirst($r['status'])) ?></td>
                        <td class="text-end">
                            <form method="post" class="d-inline"><input type="hidden" name="csrf_token"
                                    value="<?= e(csrfToken()) ?>"><input type="hidden" name="id" value="<?= $r['id'] ?>"><button
                                    name="action" value="status"
                                    class="btn btn-sm btn-outline-warning"><?= $r['status'] === 'active' ? 'Deactivate' : 'Activate' ?></button>
                            </form>
                            <form method="post" class="d-inline" data-confirm="Delete this vehicle?"><input type="hidden"
                                    name="csrf_token" value="<?= e(csrfToken()) ?>"><input type="hidden" name="id"
                                    value="<?= $r['id'] ?>"><button name="action" value="delete"
                                    class="btn btn-sm btn-outline-danger">Delete</button></form>
                        </td>
                    </tr><?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require __DIR__ . '/../includes/panel_footer.php'; ?>