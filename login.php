<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn())
    redirectAfterLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf($_POST['csrf_token'] ?? null);
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if (filter_var($email, FILTER_VALIDATE_EMAIL) && loginUser($pdo, $email, $password)) {
        redirectAfterLogin();
    }
    flash('danger', 'Invalid email or password.');
}
$pageTitle = 'Login';
require __DIR__ . '/includes/header.php';
?>
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card p-4">
            <h2 class="mb-4">Login</h2>
            <form method="post">
                <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email"
                        class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Password</label><input type="password" name="password"
                        class="form-control" required></div>
                <button class="btn btn-primary w-100">Login</button>
            </form>
            <p class="mt-3 mb-0">No account? <a href="<?= BASE_URL ?>register.php">Register</a></p>
        </div>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>