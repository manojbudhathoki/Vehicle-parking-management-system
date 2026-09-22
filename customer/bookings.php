<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/customer_auth.php';
requireCustomerLogin();
$id = (int) $_SESSION['user']['id'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf($_POST['csrf_token'] ?? null);
    $bid = (int) ($_POST['id'] ?? 0);
    $s = $pdo->prepare("UPDATE bookings SET status='cancelled' WHERE id=? AND customer_id=? AND status IN ('pending','confirmed')");
    $s->execute([$bid, $id]);
    flash('success', 'Booking cancelled if it was eligible.');
    redirect('customer/bookings.php');
}
$s = $pdo->prepare("SELECT b.*,v.vehicle_number,ps.slot_number FROM bookings b JOIN vehicles v ON v.id=b.vehicle_id JOIN parking_slots ps ON ps.id=b.slot_id WHERE b.customer_id=? ORDER BY b.id DESC");
$s->execute([$id]);
$rows = $s->fetchAll();
$pageTitle = 'My Bookings';
require __DIR__ . '/../includes/panel_layout.php'; ?>
<div class="d-flex justify-content-between align-items-center">
    <h1>My Bookings</h1>
    <a href="<?= BASE_URL ?>customer/create_booking.php" class="btn btn-primary btn-new-booking">New Booking</a>
</div>
<div class="table-responsive card p-3 mt-3">
    <table class="table">
        <thead>
            <tr>
                <th>Booking</th>
                <th>Vehicle</th>
                <th>Slot</th>
                <th>Date</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody><?php foreach ($rows as $r): ?>
                <tr>
                    <td><?= e($r['booking_number']) ?></td>
                    <td><?= e($r['vehicle_number']) ?></td>
                    <td><?= e($r['slot_number']) ?></td>
                    <td><?= e($r['booking_date']) ?></td>
                    <td><?= e(ucfirst($r['status'])) ?></td>
                    <td><?php if (in_array($r['status'], ['pending', 'confirmed'], true)): ?>
                            <form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><input
                                    type="hidden" name="id" value="<?= $r['id'] ?>"><button
                                    class="btn btn-sm btn-outline-danger">Cancel</button></form><?php endif; ?>
                    </td>
                </tr><?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require __DIR__ . '/../includes/panel_footer.php'; ?>