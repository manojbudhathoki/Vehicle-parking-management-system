<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php'; 

// Live parking stats
try {
  $total = (int) $pdo->query("SELECT COUNT(*) FROM parking_slots WHERE status != 'inactive'")->fetchColumn();
  $available = (int) $pdo->query("SELECT COUNT(*) FROM parking_slots WHERE status = 'available'")->fetchColumn();
  $occupied = (int) $pdo->query("SELECT COUNT(*) FROM parking_slots WHERE status = 'occupied'")->fetchColumn();
} catch (Exception $e) {
  $total = $available = $occupied = 0;
}

// Occupancy percentage
$occupancyPct = $total > 0 ? round(($occupied / $total) * 100) : 0;

// Rates preview
try {
  $rates = $pdo->query("SELECT * FROM parking_rates WHERE is_active = 1 ORDER BY vehicle_type")->fetchAll();
} catch (Exception $e) {
  $rates = [];
}

$pageTitle = 'Home';
require __DIR__ . '/includes/header.php';
?>

<!-- ══════════════════════════════════════════
     HERO SECTION
══════════════════════════════════════════ -->
<div class="hero rounded-4 mb-5">
  <div class="container">
    <div class="row align-items-center g-5">

      <!-- Left: Headline & CTAs -->
      <div class="col-lg-7">
        <div class="hero-badge">
          <i class="bi bi-lightning-charge-fill me-1"></i>Single-Facility Smart Parking
        </div>
        <h1 class="display-4 fw-bold">
          Smart &amp; Convenient<br>Parking Management
        </h1>
        <p class="lead mt-3">
          Find available parking slots, reserve in advance, track your session in real-time and receive a professional
          digital receipt.
        </p>
        <div class="d-flex flex-wrap gap-3 mt-4">
          <a class="btn btn-light btn-lg fw-semibold px-4" href="<?= BASE_URL ?>slots.php">
            <i class="bi bi-grid-3x3-gap me-2"></i>View Available Slots
          </a>
          <a class="btn btn-warning btn-lg fw-semibold px-4 text-dark" href="<?= BASE_URL ?>login.php">
            <i class="bi bi-calendar-check me-2"></i>Book Parking
          </a>
        </div>
        <!-- Trust badges -->
        <div class="d-flex flex-wrap gap-3 mt-4">
          <span class="text-white-50 small"><i class="bi bi-shield-check me-1 text-success"></i>Secure Booking</span>
          <span class="text-white-50 small"><i class="bi bi-receipt me-1 text-warning"></i>Digital Receipts</span>
          <span class="text-white-50 small"><i class="bi bi-clock me-1 text-info"></i>Open 24/7</span>
        </div>
      </div>

      <!-- Right: Live Stats Card -->
      <div class="col-lg-5">
        <div class="hero-card">
          <div class="card-label">
            <i class="bi bi-activity me-1 text-success"></i>Live Parking Availability
          </div>
          <div class="row g-3">
            <div class="col-4">
              <div class="stat-item total">
                <div class="stat-number"><?= $total ?></div>
                <div class="stat-label">Total</div>
              </div>
            </div>
            <div class="col-4">
              <div class="stat-item avail">
                <div class="stat-number"><?= $available ?></div>
                <div class="stat-label">Available</div>
              </div>
            </div>
            <div class="col-4">
              <div class="stat-item occupied">
                <div class="stat-number"><?= $occupied ?></div>
                <div class="stat-label">Occupied</div>
              </div>
            </div>
          </div>

          <!-- Occupancy progress bar -->
          <div class="mt-3">
            <div class="d-flex justify-content-between mb-1">
              <small class="text-muted fw-semibold">Occupancy</small>
              <small class="fw-bold <?= $occupancyPct > 80 ? 'text-danger' : 'text-success' ?>">
                <?= $occupancyPct ?>%
              </small>
            </div>
            <div class="occupancy-bar">
              <div class="bar-fill" style="width: <?= $occupancyPct ?>%"></div>
            </div>
          </div>

          <a href="<?= BASE_URL ?>slots.php" class="btn btn-outline-primary w-100 mt-3">
            <i class="bi bi-eye me-1"></i>View All Slots
          </a>
        </div>
      </div>

    </div>
  </div>
</div>

<!-- ══════════════════════════════════════════
     FEATURES SECTION
══════════════════════════════════════════ -->
<section class="py-5">
  <div class="text-center mb-5">
    <h2 class="section-title">Why Choose SmartPark?</h2>
    <p class="section-subtitle mt-2">Everything you need for a smooth parking experience.</p>
  </div>
  <div class="row g-4">
    <div class="col-md-4">
      <div class="feature-card text-center">
        <div class="feature-icon bg-primary bg-opacity-10 mx-auto">
          <i class="bi bi-calendar2-check text-primary"></i>
        </div>
        <h5>Easy Booking</h5>
        <p class="text-muted">Select your vehicle, choose a date &amp; time, and reserve an available parking slot in
          seconds.</p>
      </div>
    </div>
    <div class="col-md-4">
      <div class="feature-card text-center">
        <div class="feature-icon bg-success bg-opacity-10 mx-auto">
          <i class="bi bi-credit-card text-success"></i>
        </div>
        <h5>Simple Payments</h5>
        <p class="text-muted">Pay conveniently by Cash or Online method. Fees are calculated automatically based on your
          duration.</p>
      </div>
    </div>
    <div class="col-md-4">
      <div class="feature-card text-center">
        <div class="feature-icon bg-warning bg-opacity-10 mx-auto">
          <i class="bi bi-receipt text-warning"></i>
        </div>
        <h5>Digital Receipts</h5>
        <p class="text-muted">View, download and print your parking receipts and full history anytime from your
          dashboard.</p>
      </div>
    </div>
  </div>
</section>

<!-- ══════════════════════════════════════════
     HOW IT WORKS SECTION
══════════════════════════════════════════ -->
<section class="py-5">
  <div class="row align-items-center g-5">
    <div class="col-lg-5">
      <h2 class="section-title">How It Works</h2>
      <p class="text-muted mt-2">Get parked in just a few simple steps.</p>
      <a href="<?= BASE_URL ?>how-it-works.php" class="btn btn-outline-primary mt-3">
        <i class="bi bi-arrow-right me-1"></i>Learn More
      </a>
    </div>
    <div class="col-lg-7">
      <div class="card p-4">
        <?php
        $steps = [
          ['icon' => 'person-plus', 'title' => 'Register or Log In', 'desc' => 'Create a free customer account or sign in.'],
          ['icon' => 'car-front', 'title' => 'Add Your Vehicle', 'desc' => 'Add your vehicle details to your profile.'],
          ['icon' => 'grid-3x3-gap', 'title' => 'Check Available Slots', 'desc' => 'Browse real-time slot availability by zone.'],
          ['icon' => 'calendar-check', 'title' => 'Create a Booking', 'desc' => 'Choose your preferred slot and time.'],
          ['icon' => 'person-badge', 'title' => 'Staff Verifies Entry', 'desc' => 'Staff activates your parking session on arrival.'],
          ['icon' => 'arrow-bar-right', 'title' => 'Exit & Pay', 'desc' => 'Staff processes your exit and calculates the fee.'],
          ['icon' => 'receipt', 'title' => 'Get Your Receipt', 'desc' => 'View and print your digital receipt instantly.'],
        ];
        foreach ($steps as $i => $step): ?>
          <div class="step-item <?= $i < count($steps) - 1 ? 'border-bottom' : '' ?>">
            <div class="step-number"><?= $i + 1 ?></div>
            <div class="step-content">
              <h6><i class="bi bi-<?= $step['icon'] ?> me-1 text-primary"></i><?= $step['title'] ?></h6>
              <p><?= $step['desc'] ?></p>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<!-- ══════════════════════════════════════════
     RATES PREVIEW SECTION
══════════════════════════════════════════ -->
<?php if (!empty($rates)): ?>
  <section class="py-5">
    <div class="text-center mb-5">
      <h2 class="section-title">Parking Rates</h2>
      <p class="section-subtitle mt-2">Transparent, affordable rates for all vehicle types.</p>
    </div>
    <?php
    $vehicleIcons = [
      'Motorcycle' => 'bicycle',
      'Car' => 'car-front',
      'Van' => 'truck',
      'Truck' => 'truck-flatbed',
      'Bus' => 'bus-front',
    ];
    ?>
    <div class="row g-4 justify-content-center">
      <?php foreach ($rates as $r):
        $icon = $vehicleIcons[$r['vehicle_type']] ?? 'car-front';
        ?>
        <div class="col-sm-6 col-md-4 col-lg-3">
          <div class="rate-card text-center">
            <div class="vehicle-icon">
              <i class="bi bi-<?= $icon ?> text-primary"></i>
            </div>
            <h5 class="fw-bold"><?= e($r['vehicle_type']) ?></h5>
            <hr class="my-2">
            <div class="rate-price"><?= money((float) $r['first_hour_rate']) ?></div>
            <div class="rate-label">First Hour</div>
            <div class="mt-2 text-muted small">
              + <?= money((float) $r['additional_hour_rate']) ?> / additional hour
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="text-center mt-4">
      <a href="<?= BASE_URL ?>rates.php" class="btn btn-outline-primary">
        <i class="bi bi-tag me-1"></i>View Full Rate Details
      </a>
    </div>
  </section>
<?php endif; ?>

<!-- ══════════════════════════════════════════
     CTA BANNER
══════════════════════════════════════════ -->
<section class="py-4 mb-4">
  <div class="cta-banner">
    <h2><i class="bi bi-p-square-fill me-2"></i>Book Your Parking Spot Today</h2>
    <p>Join SmartPark and enjoy a seamless parking experience with real-time availability and instant confirmation.</p>
    <div class="d-flex flex-wrap justify-content-center gap-3">
      <a class="btn btn-light btn-lg fw-semibold px-4" href="<?= BASE_URL ?>register.php">
        <i class="bi bi-person-plus me-2"></i>Create Free Account
      </a>
      <a class="btn btn-outline-light btn-lg px-4" href="<?= BASE_URL ?>slots.php">
        <i class="bi bi-grid-3x3-gap me-2"></i>View Slots
      </a>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>