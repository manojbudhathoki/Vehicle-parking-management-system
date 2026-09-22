<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/admin_auth.php';
requireAdminLogin();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf($_POST['csrf_token'] ?? null);
    $id = (int) ($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? '';
    if ($action === 'status' && $id) {
        $s = $pdo->prepare("UPDATE users SET status=CASE WHEN status='active' THEN 'disabled' ELSE 'active' END WHERE id=? AND role='customer'");
        $s->execute([$id]);
        flash('success', 'Customer status updated.');
    } elseif ($action === 'delete' && $id) {
        try {
            $s = $pdo->prepare("DELETE FROM users WHERE id=? AND role='customer'");
            $s->execute([$id]);
            flash('success', 'Customer deleted.');
        } catch (PDOException $e) {
            flash('danger', 'Customer cannot be deleted because related parking records/bookings exist. Disable the account instead.');
        }
    }
    redirect('admin/customers.php');
}
$rows = $pdo->query("SELECT u.id,u.full_name,u.email,u.phone,u.status,u.created_at,(SELECT COUNT(*) FROM vehicles v WHERE v.customer_id=u.id) vehicle_count,(SELECT COUNT(*) FROM bookings b WHERE b.customer_id=u.id) booking_count FROM users u WHERE u.role='customer' ORDER BY u.id DESC")->fetchAll();
$pageTitle = 'Customers';
require __DIR__ . '/../includes/panel_layout.php'; ?>
<div class="panel-card">
    <div class="panel-card-header">
        <h6>Customer Accounts</h6><span class="text-muted small"><?= count($rows) ?> customers</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Contact</th>
                    <th>Vehicles</th>
                    <th>Bookings</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody><?php foreach ($rows as $r): ?>
                    <tr>
                        <td><strong><?= e($r['full_name']) ?></strong>
                            <div class="small text-muted">Joined <?= e($r['created_at']) ?></div>
                        </td>
                        <td><?= e($r['email']) ?><br><?= e($r['phone']) ?></td>
                        <td><?= $r['vehicle_count'] ?></td>
                        <td><?= $r['booking_count'] ?></td>
                        <td><span
                                class="badge bg-<?= $r['status'] === 'active' ? 'success' : 'secondary' ?>"><?= e(ucfirst($r['status'])) ?></span>
                        </td>
                        <td class="text-end">
                            <form method="post" class="d-inline"><input type="hidden" name="csrf_token"
                                    value="<?= e(csrfToken()) ?>"><input type="hidden" name="id" value="<?= $r['id'] ?>"><button
                                    name="action" value="status"
                                    class="btn btn-sm btn-outline-<?= $r['status'] === 'active' ? 'warning' : 'success' ?>"><?= $r['status'] === 'active' ? 'Disable' : 'Enable' ?></button>
                            </form>
                            <form method="post" class="d-inline"
                                data-confirm="Delete this customer? This only works if no related records exist."><input
                                    type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><input type="hidden"
                                    name="id" value="<?= $r['id'] ?>"><button name="action" value="delete"
                                    class="btn btn-sm btn-outline-danger">Delete</button></form>
                        </td>
                    </tr><?php endforeach;
            if (!$rows): ?>
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">No customers found.</td>
                    </tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require __DIR__ . '/../includes/panel_footer.php'; ?>