<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/staff_auth.php';
requireStaffLogin();
$today = date('Y-m-d');
$q = $pdo->prepare("SELECT COUNT(*) FROM parking_records WHERE DATE(entry_time)=?");
$q->execute([$today]);
$entries = (int) $q->fetchColumn();
$q = $pdo->prepare("SELECT COUNT(*) FROM parking_records WHERE DATE(exit_time)=?");
$q->execute([$today]);
$exits = (int) $q->fetchColumn();
$active = (int) $pdo->query("SELECT COUNT(*) FROM parking_records WHERE status='active'")->fetchColumn();
$available = (int) $pdo->query("SELECT COUNT(*) FROM parking_slots WHERE status='available'")->fetchColumn();
$occupied = (int) $pdo->query("SELECT COUNT(*) FROM parking_slots WHERE status='occupied'")->fetchColumn();
$bookings = (int) $pdo->query("SELECT COUNT(*) FROM bookings WHERE status IN ('pending','confirmed') AND booking_date>=CURDATE()")->fetchColumn();
$revenue = (float) $pdo->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE DATE(paid_at)=CURDATE() AND payment_status='paid'")->fetchColumn();
$activeRows = $pdo->query("SELECT pr.id,pr.parking_number,pr.entry_time,u.full_name,v.vehicle_number,ps.slot_number FROM parking_records pr JOIN users u ON u.id=pr.customer_id JOIN vehicles v ON v.id=pr.vehicle_id JOIN parking_slots ps ON ps.id=pr.slot_id WHERE pr.status='active' ORDER BY pr.entry_time LIMIT 6")->fetchAll();
$pending = $pdo->query("SELECT b.id,b.booking_number,b.booking_date,b.expected_entry_time,u.full_name,v.vehicle_number,ps.slot_number FROM bookings b JOIN users u ON u.id=b.customer_id JOIN vehicles v ON v.id=b.vehicle_id JOIN parking_slots ps ON ps.id=b.slot_id WHERE b.status='pending' ORDER BY b.booking_date,b.expected_entry_time LIMIT 6")->fetchAll();
$pageTitle = 'Staff Dashboard';
require __DIR__ . '/../includes/panel_layout.php'; ?>
<div class="row g-3 mb-4">
    <?php foreach ([['Today Entries', $entries, 'arrow-down-circle', 'primary'], ['Today Exits', $exits, 'arrow-up-circle', 'success'], ['Active Parking', $active, 'p-square', 'danger'], ['Available Slots', $available, 'check-circle', 'success'], ['Occupied Slots', $occupied, 'x-circle', 'warning'], ['Pending/Confirmed', $bookings, 'calendar-check', 'info'], ['Today Revenue', money($revenue), 'cash-stack', 'success']] as $c): ?>
        <div class="col-6 col-md-4 col-xl"><?php $wide = $c[0] === 'Today Revenue'; ?>
            <div class="stat-card h-100">
                <div class="stat-card-icon bg-<?= $c[3] ?> bg-opacity-10 text-<?= $c[3] ?>"><i class="bi bi-<?= $c[2] ?>"></i>
                </div>
                <div class="stat-card-body">
                    <div class="stat-card-value"><?= e((string) $c[1]) ?></div>
                    <div class="stat-card-label"><?= e($c[0]) ?></div>
                </div>
            </div>
        </div><?php endforeach; ?>
</div>
<div class="d-flex flex-wrap gap-2 mb-4"><a class="btn btn-primary" href="<?= BASE_URL ?>staff/vehicle_entry.php"><i
            class="bi bi-arrow-bar-down me-1"></i>Vehicle Entry</a><a class="btn btn-dark"
        href="<?= BASE_URL ?>staff/vehicle_exit.php"><i class="bi bi-arrow-bar-up me-1"></i>Vehicle Exit</a><a
        class="btn btn-outline-primary" href="<?= BASE_URL ?>staff/bookings.php">Manage Bookings</a><a
        class="btn btn-outline-secondary" href="<?= BASE_URL ?>staff/reports.php">Reports</a></div>
<div class="row g-4">
    <div class="col-xl-7">
        <div class="panel-card">
            <div class="panel-card-header">
                <h6>Active Parking</h6><a href="<?= BASE_URL ?>staff/active_parking.php"
                    class="btn btn-sm btn-outline-primary">View all</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Parking</th>
                            <th>Customer</th>
                            <th>Vehicle</th>
                            <th>Slot</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody><?php foreach ($activeRows as $r): ?>
                            <tr>
                                <td><?= e($r['parking_number']) ?></td>
                                <td><?= e($r['full_name']) ?></td>
                                <td><?= e($r['vehicle_number']) ?></td>
                                <td><?= e($r['slot_number']) ?></td>
                                <td><a class="btn btn-sm btn-primary"
                                        href="<?= BASE_URL ?>staff/vehicle_exit.php?id=<?= $r['id'] ?>">Exit</a></td>
                            </tr><?php endforeach;
                    if (!$activeRows): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No active vehicles.</td>
                            </tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-xl-5">
        <div class="panel-card">
            <div class="panel-card-header">
                <h6>Pending Bookings</h6><a href="<?= BASE_URL ?>staff/bookings.php"
                    class="btn btn-sm btn-outline-primary">Manage</a>
            </div>
            <div class="panel-card-body"><?php foreach ($pending as $r): ?>
                    <div class="border-bottom py-2">
                        <div class="d-flex justify-content-between"><strong><?= e($r['booking_number']) ?></strong><span
                                class="badge bg-warning text-dark">Pending</span></div>
                        <div class="small text-muted"><?= e($r['full_name']) ?> · <?= e($r['vehicle_number']) ?> · Slot
                            <?= e($r['slot_number']) ?></div>
                        <div class="small"><?= e($r['booking_date']) ?>     <?= e($r['expected_entry_time']) ?></div>
                    </div><?php endforeach;
            if (!$pending): ?>
                    <div class="text-muted">No pending bookings.</div><?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../includes/panel_footer.php'; ?>