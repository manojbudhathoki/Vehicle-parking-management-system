<?php
/**
 * Panel Layout — shared sidebar + topbar for Admin / Staff / Customer
 *
 * Expects before include:
 *   $pageTitle  (string)  — page heading shown in topbar
 *   $panelRole  (string)  — 'admin' | 'staff' | 'customer'  (set automatically per auth file)
 */
$user = currentUser();
$role = $user['role'] ?? '';
$currentFile = basename($_SERVER['PHP_SELF']);

// ── Nav definitions per role ─────────────────────────────────
$navItems = [
  'admin' => [
    ['section' => 'Overview'],
    ['icon' => 'speedometer2', 'label' => 'Dashboard', 'href' => 'admin/dashboard.php'],
    ['section' => 'Users'],
    ['icon' => 'people', 'label' => 'Customers', 'href' => 'admin/customers.php'],
    ['icon' => 'person-badge', 'label' => 'Staff', 'href' => 'admin/staff.php'],
    ['icon' => 'car-front', 'label' => 'Vehicles', 'href' => 'admin/vehicles.php'],
    ['section' => 'Parking'],
    ['icon' => 'grid-3x3-gap', 'label' => 'Parking Slots', 'href' => 'admin/parking_slots.php'],
    ['icon' => 'tag', 'label' => 'Parking Rates', 'href' => 'admin/parking_rates.php'],
    ['icon' => 'calendar-check', 'label' => 'Bookings', 'href' => 'admin/bookings.php'],
    ['icon' => 'clock-history', 'label' => 'Parking Records', 'href' => 'admin/parking_records.php'],
    ['section' => 'Finance'],
    ['icon' => 'credit-card', 'label' => 'Payments', 'href' => 'admin/payments.php'],
    ['icon' => 'receipt', 'label' => 'Receipts', 'href' => 'admin/receipts.php'],
    ['icon' => 'bar-chart-line', 'label' => 'Reports', 'href' => 'admin/reports.php'],
    ['section' => 'System'],
    ['icon' => 'journal-text', 'label' => 'Audit Logs', 'href' => 'admin/audit_logs.php'],
    ['icon' => 'gear', 'label' => 'Settings', 'href' => 'admin/settings.php'],
  ],
  'staff' => [
    ['section' => 'Overview'],
    ['icon' => 'speedometer2', 'label' => 'Dashboard', 'href' => 'staff/dashboard.php'],
    ['section' => 'Operations'],
    ['icon' => 'arrow-bar-down', 'label' => 'Vehicle Entry', 'href' => 'staff/vehicle_entry.php'],
    ['icon' => 'arrow-bar-up', 'label' => 'Vehicle Exit', 'href' => 'staff/vehicle_exit.php'],
    ['icon' => 'p-square', 'label' => 'Active Parking', 'href' => 'staff/active_parking.php'],
    ['icon' => 'calendar-check', 'label' => 'Bookings', 'href' => 'staff/bookings.php'],
    ['section' => 'Records'],
    ['icon' => 'clock-history', 'label' => 'History', 'href' => 'staff/history.php'],
    ['icon' => 'credit-card', 'label' => 'Payments', 'href' => 'staff/payments.php'],
    ['icon' => 'receipt', 'label' => 'Receipts', 'href' => 'staff/receipts.php'],
    ['icon' => 'bar-chart-line', 'label' => 'Reports', 'href' => 'staff/reports.php'],
  ],
  'customer' => [
    ['section' => 'Overview'],
    ['icon' => 'house', 'label' => 'Dashboard', 'href' => 'customer/dashboard.php'],
    ['section' => 'My Parking'],
    ['icon' => 'car-front', 'label' => 'My Vehicles', 'href' => 'customer/vehicles.php'],
    ['icon' => 'calendar-plus', 'label' => 'Book Parking', 'href' => 'customer/create_booking.php'],
    ['icon' => 'calendar-check', 'label' => 'My Bookings', 'href' => 'customer/bookings.php'],
    ['icon' => 'p-square', 'label' => 'Active Parking', 'href' => 'customer/active_parking.php'],
    ['section' => 'History'],
    ['icon' => 'clock-history', 'label' => 'Parking History', 'href' => 'customer/history.php'],
    ['icon' => 'receipt', 'label' => 'Receipts', 'href' => 'customer/receipts.php'],
    ['icon' => 'credit-card', 'label' => 'Payments', 'href' => 'customer/payments.php'],
    ['icon' => 'bell', 'label' => 'Notifications', 'href' => 'customer/notifications.php'],
    ['section' => 'Account'],
    ['icon' => 'person-circle', 'label' => 'My Profile', 'href' => 'customer/profile.php'],
  ],
];

$items = $navItems[$role] ?? [];

// Role badge label & color
$roleBadge = [
  'admin' => ['Admin', 'bg-danger'],
  'staff' => ['Staff', 'bg-warning text-dark'],
  'customer' => ['Customer', 'bg-primary'],
][$role] ?? ['User', 'bg-secondary'];
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($pageTitle ?? 'Panel') ?> | SmartPark</title>
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <!-- SmartPark CSS-first styles -->
  <link href="<?= BASE_URL ?>assets/css/style.css" rel="stylesheet">
  <link href="<?= BASE_URL ?>assets/css/panel.css" rel="stylesheet">
</head>

<body class="panel-body">

  <!-- Sidebar backdrop (mobile) -->
  <div class="panel-sidebar-backdrop" id="sidebarBackdrop"></div>

  <!-- ── SIDEBAR ─────────────────────────────────────────────── -->
  <aside class="panel-sidebar" id="panelSidebar">

    <a href="<?= BASE_URL ?>" class="sidebar-brand">
      <span class="brand-icon"><i class="bi bi-p-square-fill text-white"></i></span>
      SmartPark
    </a>

    <nav class="mt-2 pb-3">
      <?php foreach ($items as $item): ?>
        <?php if (isset($item['section'])): ?>
          <div class="sidebar-section"><?= e($item['section']) ?></div>
        <?php else:
          $isActive = ($currentFile === basename($item['href']));
          ?>
          <ul class="sidebar-nav">
            <li>
              <a href="<?= BASE_URL . e($item['href']) ?>" class="<?= $isActive ? 'active' : '' ?>">
                <i class="bi bi-<?= e($item['icon']) ?>"></i>
                <?= e($item['label']) ?>
              </a>
            </li>
          </ul>
        <?php endif; ?>
      <?php endforeach; ?>
    </nav>

    <!-- Sidebar footer with user info -->
    <div class="sidebar-footer">
      <div class="d-flex align-items-center gap-2 mb-2">
        <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white fw-bold"
          style="width:34px;height:34px;font-size:.85rem;flex-shrink:0;">
          <?= strtoupper(substr($user['full_name'] ?? 'U', 0, 1)) ?>
        </div>
        <div>
          <div class="user-name"><?= e($user['full_name'] ?? '') ?></div>
          <div class="user-role">
            <span class="badge <?= $roleBadge[1] ?>" style="font-size:.62rem;"><?= $roleBadge[0] ?></span>
          </div>
        </div>
      </div>
      <a href="<?= BASE_URL ?>logout.php" class="btn btn-sm btn-outline-danger w-100">
        <i class="bi bi-box-arrow-right me-1"></i>Logout
      </a>
    </div>
  </aside>

  <!-- ── MAIN CONTENT ─────────────────────────────────────────── -->
  <div class="panel-content">

    <!-- Topbar -->
    <div class="panel-topbar">
      <div class="d-flex align-items-center gap-3">
        <button class="sidebar-toggler" id="sidebarToggler">
          <i class="bi bi-list"></i>
        </button>
        <h1 class="page-title"><?= e($pageTitle ?? 'Panel') ?></h1>
      </div>
      <div class="topbar-actions">
        <a href="<?= BASE_URL ?>" class="btn btn-sm btn-outline-secondary" target="_blank">
          <i class="bi bi-box-arrow-up-right me-1"></i>Public Site
        </a>
      </div>
    </div>

    <!-- Flash messages -->
    <div class="panel-flashes">
      <?php foreach (getFlashes() as $f): ?>
        <div class="alert alert-<?= e($f['type']) ?> alert-dismissible" role="alert">
          <i
            class="bi bi-<?= $f['type'] === 'success' ? 'check-circle' : ($f['type'] === 'danger' ? 'exclamation-triangle' : 'info-circle') ?> me-2"></i>
          <?= e($f['message']) ?>
          <button type="button" class="btn-close" data-alert-dismiss aria-label="Close">&times;</button>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Page content injected here -->
    <div class="panel-main">