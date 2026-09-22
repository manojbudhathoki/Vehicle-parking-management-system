<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/staff_auth.php';
requireStaffLogin();
$id = (int) ($_GET['id'] ?? 0);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf($_POST['csrf_token'] ?? null);
    $id = (int) ($_POST['id'] ?? 0);
    $method = $_POST['payment_method'] ?? '';
    if (!in_array($method, ['cash', 'online'], true)) {
        flash('danger', 'Invalid payment method.');
    } else {
        try {
            $pdo->beginTransaction();
            $s = $pdo->prepare("SELECT pr.*,v.vehicle_type,ps.slot_number FROM parking_records pr JOIN vehicles v ON v.id=pr.vehicle_id JOIN parking_slots ps ON ps.id=pr.slot_id WHERE pr.id=? AND pr.status='active' FOR UPDATE");
            $s->execute([$id]);
            $r = $s->fetch();
            if (!$r)
                throw new Exception('Active parking record not found.');
            $exitTime = new DateTime();
            $entry = new DateTime($r['entry_time']);
            $mins = max(1, (int) floor(($exitTime->getTimestamp() - $entry->getTimestamp()) / 60));
            $fee = calculateParkingFee($pdo, $r['vehicle_type'], $mins);
            $pdo->prepare("UPDATE parking_records SET exit_time=?,parking_duration_minutes=?,calculated_fee=?,status='completed' WHERE id=?")->execute([$exitTime->format('Y-m-d H:i:s'), $mins, $fee, $id]);
            $pdo->prepare("UPDATE parking_slots SET status='available' WHERE id=?")->execute([$r['slot_id']]);
            $pdo->prepare("UPDATE bookings SET status='completed' WHERE id=?")->execute([$r['booking_id']]);
            $pdo->prepare("INSERT INTO payments(parking_record_id,customer_id,amount,payment_method,payment_status,transaction_reference,paid_at,recorded_by) VALUES(?,?,?,?,'paid',?,NOW(),?)")->execute([$id, $r['customer_id'], $fee, $method, trim($_POST['transaction_reference'] ?? ''), $_SESSION['user']['id']]);
            $paymentId = (int) $pdo->lastInsertId();
            $receipt = 'REC-' . date('YmdHis') . '-' . random_int(100, 999);
            $pdo->prepare("INSERT INTO receipts(receipt_number,parking_record_id,payment_id,customer_id,issued_at) VALUES(?,?,?,?,NOW())")->execute([$receipt, $id, $paymentId, $r['customer_id']]);
            // IMPORTANT: capture the receipt ID immediately. Any INSERT after this
            // (for example, the notification below) changes PDO::lastInsertId().
            $receiptId = (int) $pdo->lastInsertId();
            if ($receiptId <= 0) {
                throw new Exception('Receipt could not be created.');
            }
            $pdo->prepare("INSERT INTO notifications(user_id,title,message) VALUES(?,?,?)")->execute([$r['customer_id'], 'Parking completed', 'Parking completed. Receipt ' . $receipt . ' was generated for ' . money($fee) . '.']);
            audit($pdo, (int) $_SESSION['user']['id'], 'vehicle_exit', 'parking_records', $id, 'Vehicle exit, payment and receipt completed.');
            $pdo->commit();
            flash('success', 'Exit completed and payment receipt generated: ' . $receipt);
            redirect('receipt.php?id=' . $receiptId . '&autoprint=1');
        } catch (Throwable $e) {
            if ($pdo->inTransaction())
                $pdo->rollBack();
            flash('danger', $e->getMessage());
        }
    }
}
$r = null;
if ($id) {
    $s = $pdo->prepare("SELECT pr.*,u.full_name,v.vehicle_number,v.vehicle_type,ps.slot_number FROM parking_records pr JOIN users u ON u.id=pr.customer_id JOIN vehicles v ON v.id=pr.vehicle_id JOIN parking_slots ps ON ps.id=pr.slot_id WHERE pr.id=? AND pr.status='active'");
    $s->execute([$id]);
    $r = $s->fetch();
}
if (!$r) {
    $r = $pdo->query("SELECT pr.*,u.full_name,v.vehicle_number,v.vehicle_type,ps.slot_number FROM parking_records pr JOIN users u ON u.id=pr.customer_id JOIN vehicles v ON v.id=pr.vehicle_id JOIN parking_slots ps ON ps.id=pr.slot_id WHERE pr.status='active' ORDER BY pr.entry_time LIMIT 1")->fetch();
}
$pageTitle = 'Vehicle Exit';
require __DIR__ . '/../includes/panel_layout.php'; ?>
<h1>Vehicle Exit</h1><?php if (!$r): ?>
    <div class="alert alert-info">No active parking sessions.</div><?php else: ?>
    <div class="card p-4">
        <h4><?= e($r['parking_number']) ?></h4>
        <p>Customer: <?= e($r['full_name']) ?> | Vehicle: <?= e($r['vehicle_number']) ?> | Slot: <?= e($r['slot_number']) ?>
        </p>
        <form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><input type="hidden"
                name="id" value="<?= $r['id'] ?>">
            <div class="mb-3"><label>Payment Method</label><select name="payment_method" class="form-select" required>
                    <option value="cash">Cash</option>
                    <option value="online">Online</option>
                </select></div>
            <div class="mb-3"><label>Online Transaction/Reference (optional)</label><input name="transaction_reference"
                    class="form-control"></div><button class="btn btn-success">Complete Exit & Payment</button>
        </form>
    </div><?php endif; ?><?php require __DIR__ . '/../includes/panel_footer.php'; ?>