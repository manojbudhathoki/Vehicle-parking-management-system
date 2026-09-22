<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

if (isLoggedIn())
    redirect('customer/dashboard.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf($_POST['csrf_token'] ?? null);
    $name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8 || $password !== $confirm) {
        flash('danger', 'Enter valid details. Password must be at least 8 characters and match confirmation.');
    } else {
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);
        if ($check->fetch()) {
            flash('danger', 'Email is already registered.');
        } else {
            $stmt = $pdo->prepare("INSERT INTO users (full_name,email,phone,password_hash,role,status) VALUES (?,?,?,?, 'customer','active')");
            $stmt->execute([$name, $email, $phone, password_hash($password, PASSWORD_DEFAULT)]);
            flash('success', 'Registration successful. Please log in.');
            redirect('login.php');
        }
    }
}
$pageTitle = 'Register';
require __DIR__ . '/includes/header.php';
?>
<div class="row justify-content-center">
    <div class="col-md-7 col-lg-6">
        <div class="card p-4">
            <h2>Create Customer Account</h2>
            <form method="post">
                <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                <div class="mb-3"><label class="form-label">Full Name</label><input name="full_name"
                        class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email"
                        class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Phone</label><input name="phone" class="form-control"></div>
                <div class="mb-3"><label class="form-label">Password</label><input type="password" name="password"
                        class="form-control" minlength="8" required></div>
                <div class="mb-3"><label class="form-label">Confirm Password</label><input type="password"
                        name="confirm_password" class="form-control" minlength="8" required></div>
                <button class="btn btn-primary w-100">Register</button>
            </form>
        </div>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>