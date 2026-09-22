<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/customer_auth.php';
requireCustomerLogin();
$id = (int) $_SESSION['user']['id'];
$stmt = $pdo->prepare("SELECT COUNT(*) FROM vehicles WHERE customer_id=? AND status='active'");
$stmt->execute([$id]);
$vehicles = (int) $stmt->fetchColumn();
$stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE customer_id=? AND status IN ('pending','confirmed','active')");
$stmt->execute([$id]);
$bookings = (int) $stmt->fetchColumn();
$stmt = $pdo->prepare("SELECT COUNT(*) FROM parking_records WHERE customer_id=? AND status='active'");
$stmt->execute([$id]);
$active = (int) $stmt->fetchColumn();
$stmt = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM payments WHERE customer_id=? AND payment_status='paid'");
$stmt->execute([$id]);
$spent = (float) $stmt->fetchColumn();
$stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0");
$stmt->execute([$id]);
$unread = (int) $stmt->fetchColumn();
$upcoming = $pdo->prepare("SELECT b.booking_number,b.booking_date,b.expected_entry_time,b.expected_exit_time,b.status,v.vehicle_number,ps.slot_number FROM bookings b JOIN vehicles v ON v.id=b.vehicle_id JOIN parking_slots ps ON ps.id=b.slot_id WHERE b.customer_id=? ORDER BY b.booking_date DESC,b.expected_entry_time DESC LIMIT 5");
$upcoming->execute([$id]);
$upcoming = $upcoming->fetchAll();
$history = $pdo->prepare("SELECT pr.parking_number,pr.entry_time,pr.exit_time,pr.calculated_fee,pr.status,v.vehicle_number,ps.slot_number FROM parking_records pr JOIN vehicles v ON v.id=pr.vehicle_id JOIN parking_slots ps ON ps.id=pr.slot_id WHERE pr.customer_id=? ORDER BY pr.id DESC LIMIT 5");
$history->execute([$id]);
$history = $history->fetchAll();
$pageTitle = 'Customer Dashboard';
require __DIR__ . '/../includes/panel_layout.php'; ?>
<div class="mb-4">
    <h2 class="h4 mb-1">Welcome, <?= e($_SESSION['user']['full_name']) ?></h2>
    <p class="text-muted mb-0">Manage your vehicles, bookings and parking activity from one place.</p>
</div>
<div class="row g-3 mb-4">
    <?php foreach ([['My Vehicles', $vehicles, 'car-front', 'primary', 'vehicles.php'], ['My Bookings', $bookings, 'calendar-check', 'info', 'bookings.php'], ['Active Parking', $active, 'p-square', 'success', 'active_parking.php'], ['Total Paid', money($spent), 'cash-stack', 'warning', 'payments.php'], ['Unread Alerts', $unread, 'bell', 'danger', 'notifications.php']] as $c): ?>
        <div class="col-6 col-lg"><?php if ($c[4]): ?><a href="<?= BASE_URL ?>customer/<?= $c[4] ?>"
                    class="text-decoration-none"><?php endif; ?>
                <div class="stat-card h-100">
                    <div class="stat-card-icon bg-<?= $c[3] ?> bg-opacity-10 text-<?= $c[3] ?>"><i class="bi bi-<?= $c[2] ?>"></i>
                    </div>
                    <div class="stat-card-body">
                        <div class="stat-card-value"><?= e((string) $c[1]) ?></div>
                        <div class="stat-card-label"><?= e($c[0]) ?></div>
                    </div>
                </div><?php if ($c[4]): ?>
                </a><?php endif; ?></div><?php endforeach; ?>
</div>
<div class="d-flex flex-wrap gap-2 mb-4"><a class="btn btn-primary" href="<?= BASE_URL ?>customer/add_vehicle.php"><i
            class="bi bi-plus-circle me-1"></i>Add Vehicle</a><a class="btn btn-outline-primary"
        href="<?= BASE_URL ?>customer/create_booking.php"><i class="bi bi-calendar-plus me-1"></i>Book Parking</a><a
        class="btn btn-outline-dark" href="<?= BASE_URL ?>slots.php">Check Slots</a><a class="btn btn-outline-secondary"
        href="<?= BASE_URL ?>rates.php">View Rates</a></div>
<div class="row g-4">
    <div class="col-xl-7">
        <div class="panel-card">
            <div class="panel-card-header">
                <h6>My Recent Bookings</h6><a href="<?= BASE_URL ?>customer/bookings.php"
                    class="btn btn-sm btn-outline-primary">View all</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Booking</th>
                            <th>Vehicle</th>
                            <th>Slot</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody><?php foreach ($upcoming as $r): ?>
                            <tr>
                                <td><?= e($r['booking_number']) ?></td>
                                <td><?= e($r['vehicle_number']) ?></td>
                                <td><?= e($r['slot_number']) ?></td>
                                <td><?= e($r['booking_date']) ?></td>
                                <td><?= e(ucfirst($r['status'])) ?></td>
                            </tr><?php endforeach;
                    if (!$upcoming): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No bookings yet.</td>
                            </tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-xl-5">
        <div class="panel-card">
            <div class="panel-card-header">
                <h6>Recent Parking</h6><a href="<?= BASE_URL ?>customer/history.php"
                    class="btn btn-sm btn-outline-primary">History</a>
            </div>
            <div class="panel-card-body"><?php foreach ($history as $r): ?>
                    <div class="d-flex justify-content-between border-bottom py-2">
                        <div><strong><?= e($r['vehicle_number']) ?></strong>
                            <div class="small text-muted">Slot <?= e($r['slot_number']) ?> · <?= e($r['entry_time']) ?></div>
                        </div>
                        <div class="text-end"><span
                                class="badge bg-<?= $r['status'] === 'active' ? 'success' : 'secondary' ?>"><?= e(ucfirst($r['status'])) ?></span>
                            <div class="small mt-1"><?= money((float) $r['calculated_fee']) ?></div>
                        </div>
                    </div><?php endforeach;
            if (!$history): ?>
                    <div class="text-muted">No parking history yet.</div><?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../includes/panel_footer.php'; ?>