<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/staff_auth.php';
requireStaffLogin();
$types = ['Motorcycle', 'Scooter', 'Car', 'Van', 'Bus', 'Truck', 'Other'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf($_POST['csrf_token'] ?? null);
    $mode = $_POST['mode'] ?? 'booking';
    try {
        $pdo->beginTransaction();
        if ($mode === 'booking') {
            $bid = (int) $_POST['booking_id'];
            $s = $pdo->prepare("SELECT b.*,v.vehicle_type FROM bookings b JOIN vehicles v ON v.id=b.vehicle_id WHERE b.id=? AND b.status IN ('pending','confirmed') FOR UPDATE");
            $s->execute([$bid]);
            $b = $s->fetch();
            if (!$b)
                throw new Exception('Booking unavailable.');
            $slotId = (int) $b['slot_id'];
            $customerId = (int) $b['customer_id'];
            $vehicleId = (int) $b['vehicle_id'];
        } else {
            $customerId = (int) $_POST['customer_id'];
            $vehicleId = (int) $_POST['vehicle_id'];
            $slotId = (int) $_POST['slot_id'];
            $s = $pdo->prepare("SELECT v.vehicle_type FROM vehicles v WHERE v.id=? AND v.customer_id=? AND v.status='active'");
            $s->execute([$vehicleId, $customerId]);
            $v = $s->fetch();
            if (!$v)
                throw new Exception('Customer vehicle not found.');
            $s = $pdo->prepare("SELECT id FROM bookings WHERE customer_id=? AND vehicle_id=? AND status IN ('pending','confirmed') AND booking_date=CURDATE() LIMIT 1");
            $s->execute([$customerId, $vehicleId]);
            $b = $s->fetch();
            if ($b)
                throw new Exception('This vehicle already has a booking for today.');
        }
        $s = $pdo->prepare("SELECT status FROM parking_slots WHERE id=? FOR UPDATE");
        $s->execute([$slotId]);
        $slot = $s->fetch();
        if (!$slot || $slot['status'] !== 'available')
            throw new Exception('Slot is not available.');
        $num = 'PK-' . date('YmdHis') . '-' . random_int(100, 999);
        $s = $pdo->prepare("INSERT INTO parking_records(parking_number,booking_id,customer_id,vehicle_id,slot_id,entry_time,status,staff_id) VALUES(?,?,?,?,?,NOW(),'active',?)");
        $s->execute([$num, $b['id'] ?? null, $customerId, $vehicleId, $slotId, $_SESSION['user']['id']]);
        $pid = (int) $pdo->lastInsertId();
        if (!empty($b['id']))
            $pdo->prepare("UPDATE bookings SET status='active' WHERE id=?")->execute([$b['id']]);
        $pdo->prepare("UPDATE parking_slots SET status='occupied' WHERE id=?")->execute([$slotId]);
        audit($pdo, (int) $_SESSION['user']['id'], 'vehicle_entry', 'parking_records', $pid, 'Vehicle entry recorded.');
        $pdo->commit();
        flash('success', 'Vehicle entry recorded. Parking number: ' . $num);
        redirect('staff/active_parking.php');
    } catch (Throwable $e) {
        if ($pdo->inTransaction())
            $pdo->rollBack();
        flash('danger', $e->getMessage());
    }
}
$bookings = $pdo->query("SELECT b.id,b.booking_number,b.customer_id,b.vehicle_id,b.slot_id,b.booking_date,b.expected_entry_time,v.vehicle_number,u.full_name,ps.slot_number FROM bookings b JOIN users u ON u.id=b.customer_id JOIN vehicles v ON v.id=b.vehicle_id JOIN parking_slots ps ON ps.id=b.slot_id WHERE b.status IN ('pending','confirmed') AND b.booking_date>=CURDATE() ORDER BY b.booking_date,b.expected_entry_time")->fetchAll();
$customers = $pdo->query("SELECT id,full_name,email FROM users WHERE role='customer' AND status='active' ORDER BY full_name")->fetchAll();
$vehicles = $pdo->query("SELECT v.id,v.customer_id,v.vehicle_number,v.vehicle_type,u.full_name FROM vehicles v JOIN users u ON u.id=v.customer_id WHERE v.status='active' AND u.status='active' ORDER BY u.full_name,v.vehicle_number")->fetchAll();
$slots = $pdo->query("SELECT id,slot_number,vehicle_type,zone FROM parking_slots WHERE status='available' ORDER BY slot_number")->fetchAll();
$pageTitle = 'Vehicle Entry';
require __DIR__ . '/../includes/panel_layout.php'; ?>
<div class="panel-card mb-4">
    <div class="panel-card-header">
        <h6>Walk-in Vehicle Entry</h6><span class="small text-muted">Use this when the customer has no booking.</span>
    </div>
    <div class="panel-card-body">
        <form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><input type="hidden"
                name="mode" value="walkin">
            <div class="row g-2">
                <div class="col-md-4"><label class="form-label">Customer</label><select name="customer_id"
                        id="entryCustomer" class="form-select" required>
                        <option value="">Select customer</option><?php foreach ($customers as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= e($c['full_name']) ?> — <?= e($c['email']) ?></option>
                        <?php endforeach; ?>
                    </select></div>
                <div class="col-md-3"><label class="form-label">Vehicle</label><select name="vehicle_id"
                        id="entryVehicle" class="form-select" required>
                        <option value="">Select vehicle</option><?php foreach ($vehicles as $v): ?>
                            <option value="<?= $v['id'] ?>" data-customer="<?= $v['customer_id'] ?>">
                                <?= e($v['vehicle_number']) ?> — <?= e($v['vehicle_type']) ?></option><?php endforeach; ?>
                    </select></div>
                <div class="col-md-3"><label class="form-label">Available Slot</label><select name="slot_id"
                        class="form-select" required>
                        <option value="">Select slot</option><?php foreach ($slots as $s): ?>
                            <option value="<?= $s['id'] ?>"><?= e($s['slot_number']) ?> — <?= e($s['vehicle_type']) ?></option>
                        <?php endforeach; ?>
                    </select></div>
                <div class="col-md-2 d-flex align-items-end"><button class="btn btn-primary w-100">Record Entry</button>
                </div>
            </div>
        </form>
    </div>
</div>
<div class="panel-card">
    <div class="panel-card-header">
        <h6>Booked Vehicle Entry</h6>
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
                    <th></th>
                </tr>
            </thead>
            <tbody><?php foreach ($bookings as $b): ?>
                    <tr>
                        <td><?= e($b['booking_number']) ?></td>
                        <td><?= e($b['full_name']) ?></td>
                        <td><?= e($b['vehicle_number']) ?></td>
                        <td><?= e($b['slot_number']) ?></td>
                        <td><?= e($b['booking_date']) ?><br><?= e($b['expected_entry_time']) ?></td>
                        <td>
                            <form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><input
                                    type="hidden" name="mode" value="booking"><input type="hidden" name="booking_id"
                                    value="<?= $b['id'] ?>"><button class="btn btn-sm btn-success">Confirm Entry</button>
                            </form>
                        </td>
                    </tr><?php endforeach;
            if (!$bookings): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No upcoming bookings.</td>
                    </tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<script>document.getElementById('entryCustomer')?.addEventListener('change', function () { const id = this.value; document.querySelectorAll('#entryVehicle option[data-customer]').forEach(o => { o.hidden = id && o.dataset.customer !== id; o.selected = false; }); document.getElementById('entryVehicle').value = ''; });</script>
<?php require __DIR__ . '/../includes/panel_footer.php'; ?>