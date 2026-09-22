<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/customer_auth.php';
requireCustomerLogin();
$id = (int) $_SESSION['user']['id'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf($_POST['csrf_token'] ?? null);
    $name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    if ($name) {
        $s = $pdo->prepare("UPDATE users SET full_name=?,phone=? WHERE id=? AND role='customer'");
        $s->execute([$name, $phone, $id]);
        $_SESSION['user']['full_name'] = $name;
        flash('success', 'Profile updated.');
        redirect('customer/profile.php');
    }
}
$s = $pdo->prepare("SELECT full_name,email,phone FROM users WHERE id=? AND role='customer'");
$s->execute([$id]);
$u = $s->fetch();
$pageTitle = 'Profile';
require __DIR__ . '/../includes/panel_layout.php'; ?>
<div class="card p-4">
    <h1>My Profile</h1>
    <form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
        <div class="mb-3"><label>Full Name</label><input name="full_name" value="<?= e($u['full_name']) ?>"
                class="form-control" required></div>
        <div class="mb-3"><label>Email</label><input value="<?= e($u['email']) ?>" class="form-control" disabled></div>
        <div class="mb-3"><label>Phone</label><input name="phone" value="<?= e($u['phone']) ?>" class="form-control">
        </div><button class="btn btn-primary">Update</button>
    </form>
</div>
<?php require __DIR__ . '/../includes/panel_footer.php'; ?>