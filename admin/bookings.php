<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/admin_auth.php';
requireAdminLogin();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf($_POST['csrf_token'] ?? null);
    $id = (int) ($_POST['id'] ?? 0);
    $status = $_POST['status'] ?? '';
    if (in_array($status, ['confirmed', 'cancelled', 'expired'], true)) {
        $s = $pdo->prepare("UPDATE bookings SET status=? WHERE id=? AND status IN ('pending','confirmed')");
        $s->execute([$status, $id]);
        flash('success', 'Booking status updated.');
    }
    redirect('admin/bookings.php');
}
$rows = $pdo->query("SELECT b.*,u.full_name,v.vehicle_number,ps.slot_number FROM bookings b JOIN users u ON u.id=b.customer_id JOIN vehicles v ON v.id=b.vehicle_id JOIN parking_slots ps ON ps.id=b.slot_id ORDER BY b.id DESC")->fetchAll();
$pageTitle = 'Bookings';
require __DIR__ . '/../includes/panel_layout.php'; ?>
<div class="panel-card">
    <div class="panel-card-header">
        <h6>All Bookings</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Booking</th>
                    <th>Customer</th>
                    <th>Vehicle</th>
                    <th>Slot</th>
                    <th>Schedule</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody><?php foreach ($rows as $r): ?>
                    <tr>
                        <td><?= e($r['booking_number']) ?></td>
                        <td><?= e($r['full_name']) ?></td>
                        <td><?= e($r['vehicle_number']) ?></td>
                        <td><?= e($r['slot_number']) ?></td>
                        <td><?= e($r['booking_date']) ?><br><small><?= e($r['expected_entry_time']) ?> -
                                <?= e($r['expected_exit_time']) ?></small></td>
                        <td><?= e(ucfirst($r['status'])) ?></td>
                        <td class="text-end"><?php if (in_array($r['status'], ['pending', 'confirmed'], true)): ?>
                                <form method="post" class="d-inline"><input type="hidden" name="csrf_token"
                                        value="<?= e(csrfToken()) ?>"><input type="hidden" name="id" value="<?= $r['id'] ?>"><button
                                        name="status" value="confirmed"
                                        class="btn btn-sm btn-outline-success">Confirm</button><button name="status"
                                        value="cancelled" class="btn btn-sm btn-outline-danger">Cancel</button></form>
                            <?php endif; ?>
                        </td>
                    </tr><?php endforeach;
            if (!$rows): ?>
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">No bookings found.</td>
                    </tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require __DIR__ . '/../includes/panel_footer.php'; ?>