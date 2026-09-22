<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/staff_auth.php';
requireStaffLogin();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf($_POST['csrf_token'] ?? null);
    $id = (int) ($_POST['id'] ?? 0);
    $status = $_POST['status'] ?? '';
    if (in_array($status, ['confirmed', 'cancelled'], true)) {
        $s = $pdo->prepare("UPDATE bookings SET status=? WHERE id=? AND status='pending'");
        $s->execute([$status, $id]);
        if ($s->rowCount()) {
            $n = $pdo->prepare("SELECT customer_id,booking_number FROM bookings WHERE id=?");
            $n->execute([$id]);
            $booking = $n->fetch();
            if ($booking) {
                $title = $status === 'confirmed' ? 'Booking confirmed' : 'Booking cancelled';
                $message = 'Your booking ' . $booking['booking_number'] . ' has been ' . $status . '.';
                $pdo->prepare("INSERT INTO notifications(user_id,title,message) VALUES(?,?,?)")->execute([$booking['customer_id'], $title, $message]);
            }
        }
        flash('success', 'Booking updated.');
        redirect('staff/bookings.php');
    }
}
$rows = $pdo->query("SELECT b.*,u.full_name,v.vehicle_number,ps.slot_number FROM bookings b JOIN users u ON u.id=b.customer_id JOIN vehicles v ON v.id=b.vehicle_id JOIN parking_slots ps ON ps.id=b.slot_id ORDER BY b.id DESC")->fetchAll();
$pageTitle = 'Bookings';
require __DIR__ . '/../includes/panel_layout.php'; ?>
<h1>Bookings</h1>
<div class="table-responsive card p-3">
    <table class="table">
        <thead>
            <tr>
                <th>Booking</th>
                <th>Customer</th>
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
                    <td><?= e($r['full_name']) ?></td>
                    <td><?= e($r['vehicle_number']) ?></td>
                    <td><?= e($r['slot_number']) ?></td>
                    <td><?= e($r['booking_date']) ?></td>
                    <td><?= e($r['status']) ?></td>
                    <td><?php if ($r['status'] === 'pending'): ?>
                            <form method="post" class="d-flex gap-1"><input type="hidden" name="csrf_token"
                                    value="<?= e(csrfToken()) ?>"><input type="hidden" name="id" value="<?= $r['id'] ?>"><button
                                    name="status" value="confirmed" class="btn btn-sm btn-success">Confirm</button><button
                                    name="status" value="cancelled" class="btn btn-sm btn-danger">Cancel</button></form>
                        <?php endif; ?>
                    </td>
                </tr><?php endforeach; ?>
        </tbody>
    </table>
</div><?php require __DIR__ . '/../includes/panel_footer.php'; ?>