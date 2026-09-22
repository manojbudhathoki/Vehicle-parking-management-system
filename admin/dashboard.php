<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/admin_auth.php';
requireAdminLogin();
$counts = [
    'Customers' => (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role='customer'")->fetchColumn(),
    'Staff' => (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role='staff' AND status='active'")->fetchColumn(),
    'Vehicles' => (int) $pdo->query("SELECT COUNT(*) FROM vehicles WHERE status='active'")->fetchColumn(),
    'Total Slots' => (int) $pdo->query("SELECT COUNT(*) FROM parking_slots")->fetchColumn(),
    'Available' => (int) $pdo->query("SELECT COUNT(*) FROM parking_slots WHERE status='available'")->fetchColumn(),
    'Occupied' => (int) $pdo->query("SELECT COUNT(*) FROM parking_slots WHERE status='occupied'")->fetchColumn(),
    'Active Parking' => (int) $pdo->query("SELECT COUNT(*) FROM parking_records WHERE status='active'")->fetchColumn(),
    'Revenue' => (float) $pdo->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE payment_status='paid'")->fetchColumn()
];
$recentBookings = $pdo->query("SELECT b.booking_number,b.booking_date,b.status,u.full_name,v.vehicle_number,ps.slot_number FROM bookings b JOIN users u ON u.id=b.customer_id JOIN vehicles v ON v.id=b.vehicle_id JOIN parking_slots ps ON ps.id=b.slot_id ORDER BY b.id DESC LIMIT 6")->fetchAll();
$recentParking = $pdo->query("SELECT pr.parking_number,pr.entry_time,pr.status,u.full_name,v.vehicle_number,ps.slot_number FROM parking_records pr JOIN users u ON u.id=pr.customer_id JOIN vehicles v ON v.id=pr.vehicle_id JOIN parking_slots ps ON ps.id=pr.slot_id ORDER BY pr.id DESC LIMIT 6")->fetchAll();
$pageTitle = 'Admin Dashboard';
require __DIR__ . '/../includes/panel_layout.php'; ?>
<div class="row g-3 mb-4">
    <?php foreach ([['Customers', $counts['Customers'], 'people', 'primary', 'admin/customers.php'], ['Staff', $counts['Staff'], 'person-badge', 'warning', 'admin/staff.php'], ['Vehicles', $counts['Vehicles'], 'car-front', 'info', 'admin/vehicles.php'], ['Total Slots', $counts['Total Slots'], 'grid-3x3-gap', 'secondary', 'admin/parking_slots.php'], ['Available', $counts['Available'], 'check-circle', 'success', 'admin/parking_slots.php'], ['Occupied', $counts['Occupied'], 'p-square', 'danger', 'admin/parking_records.php'], ['Active Parking', $counts['Active Parking'], 'clock', 'warning', 'admin/parking_records.php'], ['Revenue', money($counts['Revenue']), 'cash-stack', 'success', 'admin/payments.php']] as $c): ?>
        <div class="col-6 col-xl-3"><a href="<?= BASE_URL . $c[4] ?>" class="text-decoration-none">
                <div class="stat-card h-100">
                    <div class="stat-card-icon bg-<?= $c[3] ?> bg-opacity-10 text-<?= $c[3] ?>"><i
                            class="bi bi-<?= $c[2] ?>"></i></div>
                    <div class="stat-card-body">
                        <div class="stat-card-value"><?= e((string) $c[1]) ?></div>
                        <div class="stat-card-label"><?= e($c[0]) ?></div>
                    </div>
                </div>
            </a></div>
    <?php endforeach; ?>
</div>
<div class="row g-4">
    <div class="col-xl-7">
        <div class="panel-card">
            <div class="panel-card-header">
                <h6>Recent Bookings</h6><a class="btn btn-sm btn-outline-primary"
                    href="<?= BASE_URL ?>admin/bookings.php">View all</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Booking</th>
                            <th>Customer</th>
                            <th>Vehicle</th>
                            <th>Slot</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody><?php foreach ($recentBookings as $r): ?>
                            <tr>
                                <td><?= e($r['booking_number']) ?></td>
                                <td><?= e($r['full_name']) ?></td>
                                <td><?= e($r['vehicle_number']) ?></td>
                                <td><?= e($r['slot_number']) ?></td>
                                <td><span class="badge bg-light text-dark"><?= e(ucfirst($r['status'])) ?></span></td>
                            </tr><?php endforeach;
                    if (!$recentBookings): ?>
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
                <h6>Recent Parking Activity</h6><a class="btn btn-sm btn-outline-primary"
                    href="<?= BASE_URL ?>admin/parking_records.php">View all</a>
            </div>
            <div class="panel-card-body"><?php foreach ($recentParking as $r): ?>
                    <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                        <div><strong><?= e($r['parking_number']) ?></strong>
                            <div class="small text-muted"><?= e($r['full_name']) ?> · <?= e($r['vehicle_number']) ?> · Slot
                                <?= e($r['slot_number']) ?></div>
                        </div><span
                            class="badge bg-<?= $r['status'] === 'active' ? 'success' : 'secondary' ?>"><?= e(ucfirst($r['status'])) ?></span>
                    </div><?php endforeach;
            if (!$recentParking): ?>
                    <div class="text-muted">No parking records yet.</div><?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../includes/panel_footer.php'; ?>