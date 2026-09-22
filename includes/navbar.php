<?php
$user = currentUser();
$currentPage = basename($_SERVER['PHP_SELF']);

// Helper: active nav class
function navActive(string $page, string $current): string
{
  return $page === $current ? ' active" aria-current="page' : '';
}
?>
<nav class="navbar navbar-dark site-navbar" id="siteNavbar">
  <div class="container navbar-container">

    <!-- Brand -->
    <a class="navbar-brand d-flex align-items-center" href="<?= BASE_URL ?>">
      <span class="brand-icon">
        <i class="bi bi-p-square-fill text-white"></i>
      </span>
      SmartPark
    </a>

    <!-- Toggler -->
    <button class="navbar-toggler" type="button" id="navbarToggler" aria-controls="nav" aria-expanded="false"
      aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Links -->
    <div class="navbar-collapse" id="nav">
      <ul class="navbar-nav me-auto gap-1">
        <li class="nav-item">
          <a class="nav-link<?= navActive('index.php', $currentPage) ?>" href="<?= BASE_URL ?>">
            <i class="bi bi-house me-1"></i>Home
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link<?= navActive('slots.php', $currentPage) ?>" href="<?= BASE_URL ?>slots.php">
            <i class="bi bi-grid-3x3-gap me-1"></i>Slots
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link<?= navActive('rates.php', $currentPage) ?>" href="<?= BASE_URL ?>rates.php">
            <i class="bi bi-tag me-1"></i>Rates
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link<?= navActive('how-it-works.php', $currentPage) ?>" href="<?= BASE_URL ?>how-it-works.php">
            <i class="bi bi-info-circle me-1"></i>How It Works
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link<?= navActive('about.php', $currentPage) ?>" href="<?= BASE_URL ?>about.php">
            <i class="bi bi-building me-1"></i>About
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link<?= navActive('contact.php', $currentPage) ?>" href="<?= BASE_URL ?>contact.php">
            <i class="bi bi-envelope me-1"></i>Contact
          </a>
        </li>
      </ul>

      <!-- Auth Buttons -->
      <div class="d-flex gap-2 ms-2">
        <?php if (!$user): ?>
          <a class="btn btn-outline-light btn-sm" href="<?= BASE_URL ?>login.php">
            <i class="bi bi-box-arrow-in-right me-1"></i>Login
          </a>
          <a class="btn btn-primary btn-sm" href="<?= BASE_URL ?>register.php">
            <i class="bi bi-person-plus me-1"></i>Register
          </a>
        <?php else: ?>
          <?php
          $dash = $user['role'] === ROLE_ADMIN ? 'admin/dashboard.php' :
            ($user['role'] === ROLE_STAFF ? 'staff/dashboard.php' : 'customer/dashboard.php');
          ?>
          <span class="navbar-text text-white-50 d-none d-lg-inline small me-1">
            <i class="bi bi-person-circle me-1"></i><?= e($user['full_name']) ?>
          </span>
          <a class="btn btn-outline-light btn-sm" href="<?= BASE_URL . $dash ?>">
            <i class="bi bi-speedometer2 me-1"></i>Dashboard
          </a>
          <a class="btn btn-danger btn-sm" href="<?= BASE_URL ?>logout.php">
            <i class="bi bi-box-arrow-right me-1"></i>Logout
          </a>
        <?php endif; ?>
      </div>
    </div>

  </div>
</nav>