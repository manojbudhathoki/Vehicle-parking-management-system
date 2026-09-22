<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/admin_auth.php';
requireAdminLogin();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf($_POST['csrf_token'] ?? null);
    foreach (['parking_name', 'address', 'phone', 'email', 'operating_hours'] as $k) {
        $v = trim($_POST[$k] ?? '');
        $s = $pdo->prepare("INSERT INTO system_settings(setting_key,setting_value) VALUES(?,?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)");
        $s->execute([$k, $v]);
    }
    flash('success', 'Settings saved.');
    redirect('admin/settings.php');
}
$rows = $pdo->query("SELECT setting_key,setting_value FROM system_settings")->fetchAll();
$settings = [];
foreach ($rows as $r)
    $settings[$r['setting_key']] = $r['setting_value'];
$pageTitle = 'Settings';
require __DIR__ . '/../includes/panel_layout.php'; ?>
<div class="card p-4">
    <h1>System Settings</h1>
    <form method="post"><input type="hidden" name="csrf_token"
            value="<?= e(csrfToken()) ?>"><?php foreach (['parking_name' => 'Parking Name', 'address' => 'Address', 'phone' => 'Phone', 'email' => 'Email', 'operating_hours' => 'Operating Hours'] as $k => $label): ?>
            <div class="mb-3"><label><?= e($label) ?></label><input name="<?= $k ?>" value="<?= e($settings[$k] ?? '') ?>"
                    class="form-control"></div><?php endforeach; ?><button class="btn btn-primary">Save Settings</button>
    </form>
</div><?php require __DIR__ . '/../includes/panel_footer.php'; ?>