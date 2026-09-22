</main>

<footer class="site-footer mt-5">
  <div class="container">
    <div class="row g-4">

      <!-- Brand Column -->
      <div class="col-lg-4">
        <div class="footer-brand">
          <i class="bi bi-p-square-fill text-primary me-2"></i>SmartPark
        </div>
        <p class="footer-desc mt-2">
          Smart &amp; convenient vehicle parking. Reserve your slot, track sessions and get digital receipts — all in
          one place.
        </p>
      </div>

      <!-- Navigation -->
      <div class="col-6 col-lg-2">
        <h6>Navigation</h6>
        <ul>
          <li><a href="<?= BASE_URL ?>"><i class="bi bi-house me-1"></i>Home</a></li>
          <li><a href="<?= BASE_URL ?>slots.php"><i class="bi bi-grid-3x3-gap me-1"></i>Slots</a></li>
          <li><a href="<?= BASE_URL ?>rates.php"><i class="bi bi-tag me-1"></i>Rates</a></li>
          <li><a href="<?= BASE_URL ?>how-it-works.php"><i class="bi bi-info-circle me-1"></i>How It Works</a></li>
        </ul>
      </div>

      <!-- More -->
      <div class="col-6 col-lg-2">
        <h6>More</h6>
        <ul>
          <li><a href="<?= BASE_URL ?>about.php"><i class="bi bi-building me-1"></i>About</a></li>
          <li><a href="<?= BASE_URL ?>contact.php"><i class="bi bi-envelope me-1"></i>Contact</a></li>
          <li><a href="<?= BASE_URL ?>login.php"><i class="bi bi-box-arrow-in-right me-1"></i>Login</a></li>
          <li><a href="<?= BASE_URL ?>register.php"><i class="bi bi-person-plus me-1"></i>Register</a></li>
        </ul>
      </div>

      <!-- Contact Info -->
      <div class="col-lg-4">
        <h6>Contact</h6>
        <ul>
          <li><i class="bi bi-geo-alt me-2"></i>Your Parking Facility, City</li>
          <li><i class="bi bi-telephone me-2"></i>+977 000-000-0000</li>
          <li><i class="bi bi-clock me-2"></i>Open 24 / 7</li>
        </ul>
      </div>

    </div>

    <hr class="footer-divider">
    <div class="text-center footer-bottom">
      &copy; <?= date('Y') ?> <?= e(APP_NAME) ?> &mdash; All rights reserved.
    </div>
  </div>
</footer>

<script src="<?= BASE_URL ?>assets/js/main.js"></script>
</body>

</html>