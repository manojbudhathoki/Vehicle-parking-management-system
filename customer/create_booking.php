<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/customer_auth.php';
requireCustomerLogin();
$uid = (int) $_SESSION['user']['id'];
$v = $pdo->prepare("SELECT id,vehicle_number,vehicle_type FROM vehicles WHERE customer_id=? AND status='active' ORDER BY vehicle_number");
$v->execute([$uid]);
$vehicles = $v->fetchAll();
$selectedVehicle = (int) ($_POST['vehicle_id'] ?? 0);
$selectedDate = $_POST['booking_date'] ?? date('Y-m-d');
$slots = [];
if ($selectedVehicle) {
    $s = $pdo->prepare("SELECT vehicle_type FROM vehicles WHERE id=? AND customer_id=? AND status='active'");
    $s->execute([$selectedVehicle, $uid]);
    $vehicle = $s->fetch();
    if ($vehicle) {
        $s = $pdo->prepare("SELECT ps.id,ps.slot_number,ps.vehicle_type FROM parking_slots ps WHERE ps.status='available' AND ps.vehicle_type=? ORDER BY ps.slot_number");
        $s->execute([$vehicle['vehicle_type']]);
        $slots = $s->fetchAll();
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_booking'])) {
    verifyCsrf($_POST['csrf_token'] ?? null);
    $vehicleId = (int) $_POST['vehicle_id'];
    $slotId = (int) $_POST['slot_id'];
    $date = $_POST['booking_date'] ?? '';
    $entry = $_POST['entry_time'] ?? '';
    $exit = $_POST['exit_time'] ?? '';
    $s = $pdo->prepare("SELECT vehicle_type FROM vehicles WHERE id=? AND customer_id=? AND status='active'");
    $s->execute([$vehicleId, $uid]);
    $vehicle = $s->fetch();
    if (!$vehicle || !$date || !$entry || !$exit || $exit <= $entry || $date < date('Y-m-d')) {
        flash('danger', 'Please enter a valid future date, vehicle and time range.');
    } else {
        try {
            $pdo->beginTransaction();
            $s = $pdo->prepare("SELECT id FROM parking_slots WHERE id=? AND status='available' AND vehicle_type=? FOR UPDATE");
            $s->execute([$slotId, $vehicle['vehicle_type']]);
            if (!$s->fetch())
                throw new Exception('Selected slot is not available for this vehicle type.');
            $s = $pdo->prepare("SELECT id FROM bookings WHERE slot_id=? AND booking_date=? AND status IN ('pending','confirmed','active') AND expected_entry_time < ? AND expected_exit_time > ? LIMIT 1");
            $s->execute([$slotId, $date, $exit, $entry]);
            if ($s->fetch())
                throw new Exception('Selected slot is already booked for that time.');
            $num = 'BK-' . date('YmdHis') . '-' . random_int(100, 999);
            $s = $pdo->prepare("INSERT INTO bookings(booking_number,customer_id,vehicle_id,slot_id,booking_date,expected_entry_time,expected_exit_time,status) VALUES(?,?,?,?,?,?,?,'pending')");
            $s->execute([$num, $uid, $vehicleId, $slotId, $date, $entry, $exit]);
            $pdo->prepare("INSERT INTO notifications(user_id,title,message) VALUES(?,?,?)")->execute([$uid, 'Booking created', 'Your booking ' . $num . ' is pending staff confirmation.']);
            $pdo->commit();
            flash('success', 'Booking created: ' . $num);
            redirect('customer/bookings.php');
        } catch (Throwable $e) {
            if ($pdo->inTransaction())
                $pdo->rollBack();
            flash('danger', $e->getMessage());
        }
    }
}
$pageTitle = 'Book Parking';
require __DIR__ . '/../includes/panel_layout.php'; ?>
<div class="row justify-content-center">
    <div class="col-xl-9">
        <div class="panel-card">
            <div class="panel-card-header">
                <h6>Create Parking Booking</h6><span class="small text-muted">Choose a compatible slot and time.</span>
            </div>
            <div class="panel-card-body"><?php if (!$vehicles): ?>
                    <div class="alert alert-info">Add a vehicle before creating a booking. <a
                            href="<?= BASE_URL ?>customer/add_vehicle.php">Add vehicle</a></div><?php else: ?>
                    <form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                        <div class="row g-3">
                            <div class="col-md-6"><label class="form-label">Vehicle</label><select name="vehicle_id"
                                    class="form-select" required onchange="this.form.submit()">
                                    <option value="">Select vehicle</option><?php foreach ($vehicles as $x): ?>
                                        <option value="<?= $x['id'] ?>" <?= $selectedVehicle === $x['id'] ? 'selected' : '' ?>>
                                            <?= e($x['vehicle_number']) ?> — <?= e($x['vehicle_type']) ?></option>
                                    <?php endforeach; ?>
                                </select></div>
                            <div class="col-md-6"><label class="form-label">Booking Date</label><input type="date"
                                    name="booking_date" class="form-control" min="<?= date('Y-m-d') ?>"
                                    value="<?= e($selectedDate) ?>" required></div>
                            <div class="col-md-6"><label class="form-label">Entry Time</label><input type="time"
                                    name="entry_time" class="form-control" value="<?= e($_POST['entry_time'] ?? '09:00') ?>"
                                    required></div>
                            <div class="col-md-6"><label class="form-label">Expected Exit Time</label><input type="time"
                                    name="exit_time" class="form-control" value="<?= e($_POST['exit_time'] ?? '10:00') ?>"
                                    required></div>
                            <div class="col-12"><label class="form-label">Available Slot</label><select name="slot_id"
                                    class="form-select" required>
                                    <option value="">Select slot</option><?php foreach ($slots as $s): ?>
                                        <option value="<?= $s['id'] ?>"><?= e($s['slot_number']) ?> — <?= e($s['vehicle_type']) ?>
                                        </option><?php endforeach; ?>
                                </select><?php if ($selectedVehicle && !$slots): ?>
                                    <div class="small text-danger mt-1">No compatible available slots found for this date.</div>
                                <?php endif; ?>
                            </div>
                            <div class="col-12"><button name="submit_booking" value="1" class="btn btn-primary"
                                    <?= (!$slots ? 'disabled' : '') ?>>Create Booking</button><a
                                    href="<?= BASE_URL ?>customer/bookings.php"
                                    class="btn btn-outline-secondary ms-2">Cancel</a></div>
                        </div>
                    </form><?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../includes/panel_footer.php'; ?>